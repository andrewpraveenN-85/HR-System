<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'age',
        'nic',
        'gender',
        'title',
        'employment_type',
        'image',
        'current',
        'legal_documents',
        'employee_id',
        'education_id',
        'description',
        'probation_start_date',
        'probation_period',
        'department_id',
        'manager_id',
        'employment_start_date',
        'employment_end_date',
        'account_holder_name',
        'bank_name',
        'account_no',
        'branch_name',
        'loan_monthly_instalment',
        'status',
        'epf_no',
        'basic',
        'budget_allowance',
        'transport_allowance',
        'attendance_allowance',
        'phone_allowance',
        'car_allowance',
        'production_bonus',
        'stamp_duty',
        'annual_leave_balance',
        'annual_leave_used',
        'short_leave_balance',
        'short_leave_used',
        'monthly_leaves_used',
        'monthly_half_leaves_used',
        'monthly_short_leaves_used',
        'last_monthly_reset',
        'leave_year_start'
    ];

    protected $dates = ['leave_year_start'];

   

    /**
     * Relationship: Department
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }


    /**
     * Relationship: Manager
     */
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Relationship: Subordinates
     */
    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }
    
    /**
     * Relationship: Education
     */
    public function education()
    {
        return $this->belongsTo(Education::class, 'education_id', 'id');
    }

    /**
     * Relationship: Leaves
     */
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Relationship: Auto Short Leaves
     */
    public function autoShortLeaves()
    {
        return $this->hasMany(AutoShortLeave::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id', 'department_id');
    }

    /**
     * Check if monthly reset is needed
     */
    public function checkMonthlyReset()
    {
        $currentMonth = Carbon::now()->format('Y-m');
        
        if ($this->last_monthly_reset !== $currentMonth) {
            $this->update([
                'monthly_leaves_used' => 0,
                'monthly_half_leaves_used' => 0,
                'monthly_short_leaves_used' => 0,
                'last_monthly_reset' => $currentMonth
            ]);
        }
    }

    /**
     * Get remaining leave balances based on annual allocation
     */
    public function getLeaveBalances()
    {
        $this->checkMonthlyReset(); // Keep for potential future monthly tracking
        
        $leaveYearStart = $this->leave_year_start 
            ? Carbon::parse($this->leave_year_start) 
            : ($this->employment_start_date ? Carbon::parse($this->employment_start_date) : Carbon::now()->startOfYear());

        // Annual allocations
        $annualLeaveTotal = 21; // 21 days per year
        $shortLeaveTotal = 36;  // 36 short leaves per year

        // Calculate leaves used from the start of the leave year
        $leavesUsedInYear = Leave::where('employee_id', $this->id)
            ->where('status', 'approved')
            ->where('start_date', '>=', $leaveYearStart)
            ->get();

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

        return [
            'annual_leaves_remaining' => max(0, $annualLeaveTotal - $annualDaysUsed),
            'short_leaves_remaining' => max(0, $shortLeaveTotal - $shortLeavesUsed),
            'annual_leaves_used' => $annualDaysUsed,
            'short_leaves_used' => $shortLeavesUsed,
            'annual_leave_total' => $annualLeaveTotal,
            'short_leave_total' => $shortLeaveTotal,
            // Keep monthly tracking for display purposes if needed
            'monthly_leaves_remaining' => 2 - $this->monthly_leaves_used,
            'monthly_half_leaves_remaining' => 1 - $this->monthly_half_leaves_used,
            'monthly_short_leaves_remaining' => 3 - $this->monthly_short_leaves_used
        ];
    }

    /**
     * Calculate no-pay amount for excess leaves based on annual balance
     * This considers the full annual leave (21 days) and short leave (36) allocation
     */
    public function calculateNoPayAmount($leaveType, $duration)
    {
        $leaveYearStart = $this->leave_year_start 
            ? Carbon::parse($this->leave_year_start) 
            : ($this->employment_start_date ? Carbon::parse($this->employment_start_date) : Carbon::now()->startOfYear());

        // Annual allocations
        $annualLeaveTotal = 21; // 21 days per year
        $shortLeaveTotal = 36;  // 36 short leaves per year

        // Calculate leaves used from the start of the leave year
        $leavesUsedInYear = Leave::where('employee_id', $this->id)
            ->where('status', 'approved')
            ->where('start_date', '>=', $leaveYearStart)
            ->get();

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

        $dailyRate = $this->getDailyRate();
        
        // Calculate no-pay based on remaining annual balance
        switch ($leaveType) {
            case 'full_day':
            case 'half_day':
                if ($duration > $annualLeaveRemaining) {
                    $excessDays = $duration - $annualLeaveRemaining;
                    return $excessDays * $dailyRate;
                }
                return 0;
                
            case 'short_leave':
                if ($duration > $shortLeaveRemaining) {
                    $excessShortLeaves = $duration - $shortLeaveRemaining;
                    // Each short leave is 1/4 of a day
                    return ($excessShortLeaves / 4) * $dailyRate;
                }
                return 0;
        }
        
        return 0;
    }

    /**
     * Get daily rate for no-pay calculation
     */
    private function getDailyRate()
    {
        // Get latest salary details
        $latestSalary = \App\Models\SalaryDetails::where('employee_id', $this->employee_id)
            ->latest()
            ->first();
            
        if ($latestSalary) {
            return ($latestSalary->basic + $latestSalary->budget_allowance) / 30;
        }

        if ($this->basic !== null) {
            $base = (float) $this->basic + (float) ($this->budget_allowance ?? 0);
            if ($base > 0) {
                return $base / 30;
            }
        }
        
        return 1000; // Default daily rate
    }
    /**
 * Relationship: Bank Details
 */
public function bankDetails()
{
    return $this->hasOne(BankDetails::class);
}

public function salaryDetails()
{
    return $this->hasOne(SalaryDetails::class, 'employee_id');
}

    public function saturdayAssignments()
    {
        return $this->hasMany(SaturdayAssignment::class);
    }

}


