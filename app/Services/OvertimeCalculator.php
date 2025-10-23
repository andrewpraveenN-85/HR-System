<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\SaturdayAssignment;
use App\Support\BranchClassifier;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OvertimeCalculator
{
    private const HEAD_OFFICE_STANDARD_SECONDS = 28800; // 8 hours from 08:30 to 16:30
    private const FACTORY_STANDARD_SECONDS = 16200; // 4.5 hours from 08:30 to 13:00

    public function calculate(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        $employee->loadMissing('department');

        $attendanceRecords = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->get();

        $regularOtSeconds = 0;
        $sundayOtSeconds = 0;
        $headOfficeSummary = [];
        $factorySummary = [];

        if (BranchClassifier::isHeadOffice($employee)) {
            $result = $this->processHeadOfficeSaturdays($employee, $attendanceRecords, $startDate, $endDate);
            $regularOtSeconds += $result['regular_ot_seconds'];
            $headOfficeSummary = $result['summary'];
        } elseif (BranchClassifier::isFactory($employee)) {
            $result = $this->processFactorySaturdays($attendanceRecords);
            $regularOtSeconds += $result['regular_ot_seconds'];
            $factorySummary = $result['summary'];
        }

        foreach ($attendanceRecords as $record) {
            $date = Carbon::parse($record->date);

            if ($date->isSaturday()) {
                continue; // Already handled above
            }

            if ($date->isSunday()) {
                $workedSeconds = $this->calculateWorkedSeconds($record);
                if ($workedSeconds > 0) {
                    $sundayOtSeconds += $workedSeconds;
                }
                continue;
            }

            $regularOtSeconds += (int) ($record->overtime_seconds ?? 0);
        }

        return [
            'regular_seconds' => $regularOtSeconds,
            'sunday_seconds' => $sundayOtSeconds,
            'head_office_summary' => $headOfficeSummary,
            'factory_summary' => $factorySummary,
        ];
    }

    private function processHeadOfficeSaturdays(Employee $employee, Collection $attendanceRecords, Carbon $startDate, Carbon $endDate): array
    {
        $assignments = SaturdayAssignment::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn ($assignment) => Carbon::parse($assignment->work_date)->toDateString());

        $summary = [];
        $regularOtSeconds = 0;
        $standardSaturdaysWorked = 0;

        $saturdayRecords = $attendanceRecords->filter(function ($record) {
            return Carbon::parse($record->date)->isSaturday();
        })->sortBy('date');

        foreach ($saturdayRecords as $record) {
            $date = Carbon::parse($record->date)->toDateString();
            $workedSeconds = $this->calculateWorkedSeconds($record);
            if ($workedSeconds <= 0) {
                continue;
            }

            $isAssigned = $assignments->has($date);

            if ($isAssigned && $standardSaturdaysWorked < 2) {
                $standardSaturdaysWorked++;
                $regularPortion = min($workedSeconds, self::HEAD_OFFICE_STANDARD_SECONDS);
                $otPortion = max(0, $workedSeconds - self::HEAD_OFFICE_STANDARD_SECONDS);
                $regularOtSeconds += $otPortion;
                $summary[$date] = [
                    'status' => 'scheduled_worked',
                    'worked_seconds' => $workedSeconds,
                    'ot_seconds' => $otPortion,
                    'assigned' => true,
                ];
            } else {
                $regularOtSeconds += $workedSeconds;
                $summary[$date] = [
                    'status' => $isAssigned ? 'scheduled_extra_ot' : 'unscheduled_ot',
                    'worked_seconds' => $workedSeconds,
                    'ot_seconds' => $workedSeconds,
                    'assigned' => $isAssigned,
                ];
            }
        }

        foreach ($assignments as $dateKey => $assignment) {
            if (!isset($summary[$dateKey])) {
                $summary[$dateKey] = [
                    'status' => 'scheduled_not_worked',
                    'worked_seconds' => 0,
                    'ot_seconds' => 0,
                    'assigned' => true,
                ];
            }
        }

        ksort($summary);

        return [
            'regular_ot_seconds' => $regularOtSeconds,
            'summary' => $summary,
        ];
    }

    private function processFactorySaturdays(Collection $attendanceRecords): array
    {
        $summary = [];
        $regularOtSeconds = 0;

        $saturdayRecords = $attendanceRecords->filter(function ($record) {
            return Carbon::parse($record->date)->isSaturday();
        });

        foreach ($saturdayRecords as $record) {
            $date = Carbon::parse($record->date)->toDateString();
            $workedSeconds = $this->calculateWorkedSeconds($record);
            if ($workedSeconds <= 0) {
                continue;
            }

            $otSeconds = max(0, $workedSeconds - self::FACTORY_STANDARD_SECONDS);
            $regularOtSeconds += $otSeconds;

            $summary[$date] = [
                'status' => 'factory_standard',
                'worked_seconds' => $workedSeconds,
                'ot_seconds' => $otSeconds,
                'assigned' => true,
            ];
        }

        ksort($summary);

        return [
            'regular_ot_seconds' => $regularOtSeconds,
            'summary' => $summary,
        ];
    }

    private function calculateWorkedSeconds(Attendance $record): int
    {
        if (!$record->clock_in_time || !$record->clock_out_time) {
            return 0;
        }

        $clockIn = Carbon::parse($record->clock_in_time);
        $clockOut = Carbon::parse($record->clock_out_time);

        if ($clockOut->lessThanOrEqualTo($clockIn)) {
            return 0;
        }

        return $clockOut->diffInSeconds($clockIn);
    }
}
