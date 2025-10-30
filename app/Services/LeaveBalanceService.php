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
            : Carbon::parse($employee->employment_start_date ?? '2024-01-01');

        // Annual leave allocation (21 days)
        $annualLeaveTotal = 21;
        // Short leave allocation (36 short leaves = 9 days equivalent)
        $shortLeaveTotal = 36;

        // Get daily rate for no-pay calculation
        $dailyRate = $this->getDailyRate($employee);

        // Calculate no-pay for leaves in the current period only
        $noPay = 0;

        // Get all leaves from leave year start up to the START of current period
        $leavesBeforePeriod = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('start_date', '>=', $leaveYearStart)
            ->where('start_date', '<', $startDate)
            ->orderBy('start_date', 'asc')
            ->get();

        // Calculate balance at the start of the current period
        $annualDaysUsedBefore = 0;
        $shortLeavesUsedBefore = 0;

        foreach ($leavesBeforePeriod as $leave) {
            if ($leave->leave_category === 'short_leave') {
                $shortLeavesUsedBefore += $leave->duration;
            } else {
                $annualDaysUsedBefore += $leave->duration;
            }
        }

        // Starting balance for the current period
        $remainingAnnualLeave = max(0, $annualLeaveTotal - $annualDaysUsedBefore);
        $remainingShortLeave = max(0, $shortLeaveTotal - $shortLeavesUsedBefore);

        // Get leaves in the current period (selected month)
        $currentPeriodLeaves = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->orderBy('start_date', 'asc')
            ->get();

        // Process each leave in the current period
        foreach ($currentPeriodLeaves as $leave) {
            if ($leave->leave_category === 'short_leave') {
                // Short leave calculation
                if ($leave->duration > $remainingShortLeave) {
                    // Calculate excess short leaves that trigger no-pay
                    $excessShortLeaves = $leave->duration - $remainingShortLeave;
                    // Each short leave is 1/4 of a day
                    $noPay += ($excessShortLeaves / 4) * $dailyRate;
                    $remainingShortLeave = 0;
                } else {
                    $remainingShortLeave -= $leave->duration;
                }
            } else {
                // Full day or half day leave calculation
                if ($leave->duration > $remainingAnnualLeave) {
                    // Calculate excess days that trigger no-pay
                    $excessDays = $leave->duration - $remainingAnnualLeave;
                    $noPay += $excessDays * $dailyRate;
                    $remainingAnnualLeave = 0;
                } else {
                    $remainingAnnualLeave -= $leave->duration;
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
            : Carbon::parse($employee->employment_start_date ?? '2024-01-01');

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
