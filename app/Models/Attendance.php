<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'clock_in_time',
        'clock_out_time',
        'status',
        'total_work_hours',
        'overtime_seconds',
        'late_by_seconds'
    ];

    /**
     * Relationship: Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Get formatted total work hours
     */
    public function getTotalWorkHoursAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        
        $hours = floor($value / 3600);
        $minutes = floor(($value % 3600) / 60);
        $seconds = $value % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Get formatted overtime hours
     */
    public function getOvertimeHoursAttribute()
    {
        $value = $this->overtime_seconds;
        
        if (is_null($value)) {
            return null;
        }
        
        $hours = floor($value / 3600);
        $minutes = floor(($value % 3600) / 60);
        $seconds = $value % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Get formatted late by time
     */
    public function getLateByAttribute()
    {
        $value = $this->late_by_seconds;
        
        if (is_null($value) || $value == 0) {
            return null;
        }
        
        $hours = floor($value / 3600);
        $minutes = floor(($value % 3600) / 60);
        $seconds = $value % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
