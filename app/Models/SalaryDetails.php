<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryDetails extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'employee_salary_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'employee_name',
        'known_name',
        'epf_no',
        'basic',
        'budget_allowance',
        'gross_salary',
        'transport_allowance',
        'attendance_allowance',
        'phone_allowance',
        'production_bonus',
        'car_allowance',
        'loan_payment',
        'total_earnings',
        'epf_8_percent',
        'epf_12_percent',
        'etf_3_percent',
        'advance_payment',
        'stamp_duty',
        'no_pay',
        'total_deductions',
        'net_salary',
        'loan_balance',
        'pay_date', // Newly added
        'payed_month', // Newly added
        'ot_payment',
    ];

    /**
     * The employee this salary detail belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

}
