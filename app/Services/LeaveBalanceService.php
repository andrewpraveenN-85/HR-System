<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Leave;
use Carbon\Carbon;

class LeaveBalanceService
{
    /**
     * Calculate no-pay amount based on annual leave balance
     * 
     * @param int $employeeId
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function calculateNoPayForPeriod($employeeId, $startDate, $endDate)
    {
        $employee = Employee::find($employeeId);
        
        if (!$employee) {
            return 0;
        }

        // Get the leave year start date
        $leaveYearStart = $employee->leave_year_start 
            ? Carbon::parse($employee->leave_year_start) 
            : ($employee->employment_start_date ? Carbon::parse($employee->employment_start_date) : Carbon::now()->startOfYear());

        // Annual leave allocation (21 days)
        $annualLeaveTotal = 21;
        // Short leave allocation (36 short leaves = 9 days equivalent)
        $shortLeaveTotal = 36;

        // Calculate leaves used from leave year start until the end of the period
        $leavesUsedInYear = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('start_date', '>=', $leaveYearStart)
            ->where('start_date', '<=', $endDate)
            ->get();

        // Calculate total days and short leaves used
        $annualDaysUsed = 0;
        $shortLeavesUsed = 0;

        foreach ($leavesUsedInYear as $leave) {
            if ($leave->leave_category === 'short_leave') {
                $shortLeavesUsed += $leave->duration;
            } else {
                // Full day or half day leaves count towards annual leave
                $annualDaysUsed += $leave->duration;
            }
        }

        // Calculate remaining balances
        $annualLeaveRemaining = max(0, $annualLeaveTotal - $annualDaysUsed);
        $shortLeaveRemaining = max(0, $shortLeaveTotal - $shortLeavesUsed);

        // Get daily rate for no-pay calculation
        $dailyRate = $this->getDailyRate($employee);

        // Calculate no-pay for leaves in the current period only
        $noPay = 0;
        $currentPeriodLeaves = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get();

        // Track running balance for this period
        $tempAnnualBalance = $annualLeaveRemaining;
        $tempShortBalance = $shortLeaveRemaining;

        foreach ($currentPeriodLeaves as $leave) {
            if ($leave->leave_category === 'short_leave') {
                // Short leave calculation
                if ($leave->duration > $tempShortBalance) {
                    // Excess short leaves trigger no-pay
                    $excessShortLeaves = $leave->duration - $tempShortBalance;
                    // Each short leave is 1/4 of a day
                    $noPay += ($excessShortLeaves / 4) * $dailyRate;
                    $tempShortBalance = 0;
                } else {
                    $tempShortBalance -= $leave->duration;
                }
            } else {
                // Full day or half day leave calculation
                if ($leave->duration > $tempAnnualBalance) {
                    // Excess annual leave triggers no-pay
                    $excessDays = $leave->duration - $tempAnnualBalance;
                    $noPay += $excessDays * $dailyRate;
                    $tempAnnualBalance = 0;
                } else {
                    $tempAnnualBalance -= $leave->duration;
                }
            }
        }

        return round($noPay, 2);
    }

    /**
     * Get employee's daily rate
     * 
     * @param Employee $employee
     * @return float
     */
    private function getDailyRate(Employee $employee)
    {
        $basic = (float) ($employee->basic ?? 0);
        $budgetAllowance = (float) ($employee->budget_allowance ?? 0);
        $grossSalary = $basic + $budgetAllowance;

        if ($grossSalary > 0) {
            return $grossSalary / 30;
        }

        return 1000; // Default daily rate
    }

    /**
     * Get current leave balances for an employee
     * 
     * @param int $employeeId
     * @return array
     */
    public function getLeaveBalances($employeeId)
    {
        $employee = Employee::find($employeeId);
        
        if (!$employee) {
            return [
                'annual_leave_remaining' => 0,
                'short_leave_remaining' => 0,
                'annual_leave_used' => 0,
                'short_leave_used' => 0
            ];
        }

        $leaveYearStart = $employee->leave_year_start 
            ? Carbon::parse($employee->leave_year_start) 
            : ($employee->employment_start_date ? Carbon::parse($employee->employment_start_date) : Carbon::now()->startOfYear());

        $annualLeaveTotal = 21;
        $shortLeaveTotal = 36;

        // Get all approved leaves from the start of the leave year
        $leavesUsedInYear = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('start_date', '>=', $leaveYearStart)
            ->get();

        $annualDaysUsed = 0;
        $shortLeavesUsed = 0;

        foreach ($leavesUsedInYear as $leave) {
            if ($leave->leave_category === 'short_leave') {
                $shortLeavesUsed += $leave->duration;
            } else {
                $annualDaysUsed += $leave->duration;
            }
        }

        return [
            'annual_leave_remaining' => max(0, $annualLeaveTotal - $annualDaysUsed),
            'short_leave_remaining' => max(0, $shortLeaveTotal - $shortLeavesUsed),
            'annual_leave_used' => $annualDaysUsed,
            'short_leave_used' => $shortLeavesUsed,
            'annual_leave_total' => $annualLeaveTotal,
            'short_leave_total' => $shortLeaveTotal
        ];
    }
}
