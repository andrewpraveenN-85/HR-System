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
     * Get remaining leave balances
     */
    public function getLeaveBalances()
    {
        $this->checkMonthlyReset();
        
        return [
            'annual_leaves_remaining' => $this->annual_leave_balance - $this->annual_leave_used,
            'short_leaves_remaining' => $this->short_leave_balance - $this->short_leave_used,
            'monthly_leaves_remaining' => 2 - $this->monthly_leaves_used,
            'monthly_half_leaves_remaining' => 1 - $this->monthly_half_leaves_used,
            'monthly_short_leaves_remaining' => 3 - $this->monthly_short_leaves_used
        ];
    }

    /**
     * Calculate no-pay amount for excess leaves
     */
    public function calculateNoPayAmount($leaveType, $duration)
    {
        $balances = $this->getLeaveBalances();
        $dailyRate = $this->getDailyRate();
        
        switch ($leaveType) {
            case 'full_day':
                $available = min($balances['annual_leaves_remaining'], $balances['monthly_leaves_remaining']);
                return max(0, ($duration - $available) * $dailyRate);
                
            case 'half_day':
                $available = min($balances['annual_leaves_remaining'] * 2, $balances['monthly_half_leaves_remaining']);
                return max(0, ($duration - $available) * ($dailyRate / 2));
                
            case 'short_leave':
                $available = min($balances['short_leaves_remaining'], $balances['monthly_short_leaves_remaining']);
                return max(0, ($duration - $available) * ($dailyRate / 4));
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


