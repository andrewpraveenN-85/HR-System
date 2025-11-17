<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * AttendanceImport
 * 
 * This class handles importing attendance records from Excel files exported
 * from fingerprint machines. It processes the Excel file structure and creates
 * or updates attendance records in the database.
 * 
 * IMPORTANT: Employee matching is done ONLY by employee_id (Column A).
 * Employee names from the Excel file are ignored to avoid mismatches.
 * All displayed names come from the database after employee_id match.
 * 
 * Expected Excel Format:
 * - Row 1: Title "Attendance Record"
 * - Row 3: Date range (e.g., "Made Date:2025/11/06-2025/11/14")
 * - Row 5: Date headers (columns D onwards contain day numbers)
 * - Row 7+: Employee data
 *   - Column A: Employee ID (USED FOR MATCHING)
 *   - Column B: Employee Name (FOR REFERENCE ONLY - NOT USED FOR MATCHING)
 *   - Column C: Department
 *   - Column D+: Attendance times for each date (format: "HH:MM\nHH:MM\n...")
 * 
 * Features:
 * - Automatically fills missing attendance records for the date range
 * - Updates incomplete records (missing clock-in or clock-out)
 * - Calculates work hours, overtime, and late arrival automatically
 * - Reports records with missing data for manual review
 * - Handles cross-midnight clock-out scenarios
 */
class AttendanceImport
{
    protected $missingRecords = [];
    protected $processedRecords = [];
    protected $errors = [];
    protected $dateRange = [];

    public function import($filePath)
    {
        try {
            $reader = IOFactory::createReader('Xls');
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Extract date range from Row 3 (index 3)
            $dateRangeCell = $worksheet->getCell('A4')->getValue();
            $this->extractDateRange($dateRangeCell);

            // Extract dates from Row 4 (index 4) - starting from column D
            $dates = [];
            $columnIndex = 4; // Column D (0-indexed: A=1, B=2, C=3, D=4)
            
            foreach ($worksheet->getRowIterator(5, 5) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $colNum = 1;
                foreach ($cellIterator as $cell) {
                    if ($colNum >= $columnIndex) {
                        $value = $cell->getValue();
                        if (!empty($value) && is_numeric($value)) {
                            // Construct date from the day number and the date range
                            if (!empty($this->dateRange['start'])) {
                                $year = $this->dateRange['start']->year;
                                $month = $this->dateRange['start']->month;
                                $dates[$colNum] = Carbon::create($year, $month, (int)$value);
                            }
                        }
                    }
                    $colNum++;
                }
            }

            // Process employee rows starting from row 7 (index 6)
            $rowIndex = 7;
            foreach ($worksheet->getRowIterator(7) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }

                // Stop if empty row
                if (empty($cells[0])) {
                    break;
                }

                $employeeId = $cells[0]; // Column A
                $excelEmployeeName = $cells[1]; // Column B (for reference only)
                
                // Find employee in database by employee_id only
                $employee = Employee::where('employee_id', $employeeId)->first();
                
                if (!$employee) {
                    $this->errors[] = "Employee ID $employeeId (Excel name: $excelEmployeeName) not found in database";
                    continue;
                }

                // Process attendance data for each date
                // Use employee name from database, not from Excel
                $colNum = 1;
                foreach ($cells as $cellValue) {
                    if ($colNum >= 4 && isset($dates[$colNum])) {
                        $date = $dates[$colNum];
                        $this->processAttendanceCell($employee, $date, $cellValue);
                    }
                    $colNum++;
                }

                $rowIndex++;
            }

            return [
                'success' => true,
                'processed' => $this->processedRecords,
                'missing' => $this->missingRecords,
                'errors' => $this->errors,
                'dateRange' => $this->dateRange
            ];

        } catch (\Exception $e) {
            Log::error('Attendance import failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function extractDateRange($dateRangeString)
    {
        // Format: "Made Date:2025/11/06-2025/11/14"
        if (preg_match('/(\d{4}\/\d{2}\/\d{2})-(\d{4}\/\d{2}\/\d{2})/', $dateRangeString, $matches)) {
            $this->dateRange['start'] = Carbon::createFromFormat('Y/m/d', $matches[1]);
            $this->dateRange['end'] = Carbon::createFromFormat('Y/m/d', $matches[2]);
        }
    }

    protected function processAttendanceCell($employee, $date, $cellValue)
    {
        $dateString = $date->toDateString();
        
        // Check if attendance already exists
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $dateString)
            ->first();

        // Parse the cell value to extract clock in/out times
        $times = $this->parseTimesFromCell($cellValue);

        // If no times found and no existing attendance, mark as missing
        if (empty($times) && !$existingAttendance) {
            $this->missingRecords[] = [
                'employee_id' => $employee->employee_id,
                'employee_name' => $employee->full_name,
                'date' => $dateString,
                'reason' => 'No attendance data in Excel file'
            ];
            return;
        }

        // If times found but incomplete
        if (!empty($times)) {
            $clockIn = $times[0] ?? null;
            $clockOut = end($times) ?? null;

            // If existing record, update it if needed
            if ($existingAttendance) {
                if (!$existingAttendance->clock_in_time || !$existingAttendance->clock_out_time) {
                    $this->updateAttendanceRecord($existingAttendance, $clockIn, $clockOut);
                }
            } else {
                // Create new attendance record
                $this->createAttendanceRecord($employee, $dateString, $clockIn, $clockOut);
            }
        }
    }

    protected function parseTimesFromCell($cellValue)
    {
        $times = [];
        
        if (empty($cellValue) || is_array($cellValue)) {
            return $times;
        }

        $cellValue = str_replace(["\n", "\r"], ' ', $cellValue);
        
        // Match time patterns like 08:30, 16:45
        preg_match_all('/(\d{1,2}:\d{2})/', $cellValue, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $time) {
                $times[] = $time . ':00'; // Add seconds
            }
        }

        return $times;
    }

    protected function createAttendanceRecord($employee, $date, $clockIn, $clockOut)
    {
        try {
            if (!$clockIn && !$clockOut) {
                $this->missingRecords[] = [
                    'employee_id' => $employee->employee_id,
                    'employee_name' => $employee->full_name,
                    'date' => $date,
                    'reason' => 'Missing both clock-in and clock-out times'
                ];
                return;
            }

            $clockInDT = $clockIn ? Carbon::parse($date . ' ' . $clockIn) : null;
            $clockOutDT = $clockOut ? Carbon::parse($date . ' ' . $clockOut) : null;

            // Calculate late by seconds
            $lateBySeconds = 0;
            if ($clockInDT) {
                $lateThreshold = Carbon::parse($date . ' 08:30:00');
                if ($clockInDT->greaterThan($lateThreshold)) {
                    $lateBySeconds = $clockInDT->diffInSeconds($lateThreshold);
                }
            }

            // Calculate total work hours and overtime
            $totalWorkSeconds = null;
            $overtimeSeconds = null;
            
            if ($clockInDT && $clockOutDT) {
                $cutoff = Carbon::parse($date . ' 08:30:00');
                $startCount = $clockInDT->lessThan($cutoff) ? $cutoff->copy() : $clockInDT->copy();
                
                if ($clockOutDT->lessThan($startCount)) {
                    $clockOutDT->addDay();
                }

                $totalWorkSeconds = $clockOutDT->lessThanOrEqualTo($startCount) 
                    ? 0 
                    : $startCount->diffInSeconds($clockOutDT);

                // Calculate overtime (after 4:30 PM)
                $standardEnd = Carbon::parse($date . ' 16:30:00');
                if ($clockOutDT->greaterThan($standardEnd)) {
                    $overtimeSeconds = $standardEnd->diffInSeconds($clockOutDT);
                } else {
                    $overtimeSeconds = 0;
                }
            }

            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'date' => $date,
                'clock_in_time' => $clockIn,
                'clock_out_time' => $clockOut,
                'status' => 1,
                'total_work_hours' => $totalWorkSeconds,
                'overtime_seconds' => $overtimeSeconds,
                'late_by_seconds' => $lateBySeconds,
            ]);

            $recordInfo = [
                'employee_id' => $employee->employee_id,
                'employee_name' => $employee->full_name,
                'date' => $date,
                'clock_in' => $clockIn ?? 'N/A',
                'clock_out' => $clockOut ?? 'N/A',
            ];

            if (!$clockIn || !$clockOut) {
                $recordInfo['reason'] = 'Missing ' . (!$clockIn ? 'clock-in' : 'clock-out');
                $this->missingRecords[] = $recordInfo;
            } else {
                $this->processedRecords[] = $recordInfo;
            }

        } catch (\Exception $e) {
            Log::error('Failed to create attendance record: ' . $e->getMessage());
            $this->errors[] = "Failed to create record for {$employee->full_name} (ID: {$employee->employee_id}) on {$date}: " . $e->getMessage();
        }
    }

    protected function updateAttendanceRecord($attendance, $clockIn, $clockOut)
    {
        try {
            $updates = [];
            
            if (!$attendance->clock_in_time && $clockIn) {
                $updates['clock_in_time'] = $clockIn;
            }
            
            if (!$attendance->clock_out_time && $clockOut) {
                $updates['clock_out_time'] = $clockOut;
            }

            if (!empty($updates)) {
                // Recalculate work hours if both times are now available
                if (($attendance->clock_in_time || $clockIn) && ($attendance->clock_out_time || $clockOut)) {
                    $finalClockIn = $clockIn ?? $attendance->clock_in_time;
                    $finalClockOut = $clockOut ?? $attendance->clock_out_time;
                    
                    $clockInDT = Carbon::parse($attendance->date . ' ' . $finalClockIn);
                    $clockOutDT = Carbon::parse($attendance->date . ' ' . $finalClockOut);
                    
                    $cutoff = Carbon::parse($attendance->date . ' 08:30:00');
                    $startCount = $clockInDT->lessThan($cutoff) ? $cutoff->copy() : $clockInDT->copy();
                    
                    if ($clockOutDT->lessThan($startCount)) {
                        $clockOutDT->addDay();
                    }

                    $updates['total_work_hours'] = $startCount->diffInSeconds($clockOutDT);
                    
                    // Calculate overtime
                    $standardEnd = Carbon::parse($attendance->date . ' 16:30:00');
                    if ($clockOutDT->greaterThan($standardEnd)) {
                        $updates['overtime_seconds'] = $standardEnd->diffInSeconds($clockOutDT);
                    }
                }

                $attendance->update($updates);

                $this->processedRecords[] = [
                    'employee_id' => $attendance->employee->employee_id,
                    'employee_name' => $attendance->employee->full_name,
                    'date' => $attendance->date,
                    'clock_in' => $attendance->clock_in_time,
                    'clock_out' => $attendance->clock_out_time,
                    'action' => 'Updated'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Failed to update attendance record: ' . $e->getMessage());
            $this->errors[] = "Failed to update record for {$attendance->employee->full_name} (ID: {$attendance->employee->employee_id}) on {$attendance->date}: " . $e->getMessage();
        }
    }
}
