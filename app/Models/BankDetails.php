<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDetails extends Model
{
    use HasFactory;

    protected $table = 'bank_details'; // Define the table name if not the plural of the model name

    protected $fillable = [
        'employee_id',
        'account_holder_name',
        'bank_name',
        'bank_code',
        'branch',
        'branch_code',
        'account_number'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
