<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;


class AttendanceController extends Controller
{
    public function create()
    {
        // Fetch all employees to associate with the attendance record
        //$employees = Employee::all();

        // Return the view to create a new attendance record
        return view('management.attendance.attendance-create');
    }
    public function edit($id)
    {
        // Find the attendance record by ID
        $attendance = Attendance::findOrFail($id);

        // Retrieve the employee associated with this attendance record
        $employee = Employee::findOrFail($attendance->employee_id); // Assuming `employee_id` exists in the attendance table
        //  dd($employee);
        // Return the edit view with both attendance and employee data
        return view('management.attendance.attendance-edit', compact('attendance', 'employee'));
    }

    /*   public function store(Request $request)
{
    // Validate input to ensure correct format
    $request->validate([
        'employee_id' => 'required',
        'date' => 'required|date',

        'total_work_hours' => ['nullable', 'regex:/^([0-9]+):([0-5][0-9]):([0-5][0-9])$/'],
        'overtime_hours' => ['nullable', 'regex:/^([0-9]+):([0-5][0-9]):([0-5][0-9])$/'],
        'late_by' => ['nullable', 'regex:/^([0-9]+):([0-5][0-9]):([0-5][0-9])$/'],
    ]);


    // Convert HH:MM:SS to seconds
    $totalWorkSeconds = $this->convertToSeconds($request->input('total_work_hours'));
    $overtimeSeconds = $this->convertToSeconds($request->input('overtime_hours'));
    $lateBySeconds = $this->convertToSeconds($request->input('late_by'));

    try {
        $employee = Employee::where('employee_id', $request['employee_id'])->first();
        // Handle file uploads if supporting_documents exist
      
        // Create the attendance record
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => $request->input('date'),
            'clock_in_time' => $request->input('clock_in_time'),
            'clock_out_time' => $request->input('clock_out_time'),
            'total_work_hours' => $totalWorkSeconds,
            'overtime_seconds' => $overtimeSeconds,
            'late_by_seconds' => $lateBySeconds,
            'status' => 'present', // Default status
        ]);
    
        return redirect()->route('attendance.management')->with('success', 'Attendance record added successfully!');
    } catch (\Illuminate\Database\QueryException $e) {
        // Log the error and display a message
        \Log::error('Query Exception: ' . $e->getMessage());
        return redirect()->route('attendance.management')->with('error', 'Failed to add attendance record.'.$e->getMessage());
    } catch (\Exception $e) {
        // Catch any other exceptions
        \Log::error('Exception: ' . $e->getMessage());
        return redirect()->route('attendance.management')->with('error', 'An error occurred while adding the attendance record.'.$e->getMessage());
    }
}
 */


    public function store(Request $request)
    {
        $data = $request->json()->all();
        file_put_contents(storage_path('logs/attendance_payload.log'), now() . ' - ' . json_encode($data, JSON_PRETTY_PRINT) . 'request received  end' . PHP_EOL, FILE_APPEND);

        if (!is_array($data)) {
            file_put_contents(storage_path('logs/error_attendance_payload.log'), now() . 'error - ' . json_encode($data, JSON_PRETTY_PRINT) . 'date format error end' . PHP_EOL, FILE_APPEND);
            return response()->json(['error' => 'Invalid data format'], 400);
        }

        if (isset($data['EmpId'])) {
            $data = [$data];
        }

        foreach ($data as $entry) {
            if (!isset($entry['EmpId']) || !isset($entry['AttTime'])) {
                file_put_contents(storage_path('logs/error_attendance_payload.log'), now() . 'Missing required fields: EmpId or AttTime -' . json_encode($data, JSON_PRETTY_PRINT) . 'missing feilds error end' . PHP_EOL, FILE_APPEND);
                return response()->json(['error' => 'Missing required fields: EmpId or AttTime'], 400);
            }

            $employeeId  = $entry['EmpId'];
            $attFullData = $entry['AttTime'];

            $employee = Employee::where('id', $employeeId)->first();
            if (!$employee) {
                file_put_contents(storage_path('logs/error_attendance_payload.log'), now() . 'Employee ID not present -' . json_encode($data, JSON_PRETTY_PRINT) . 'missing employee ID error end' . PHP_EOL, FILE_APPEND);
                return response()->json(['error' => "Employee ID {$employeeId} not found"], 404);
            }

            // --- Parse once with the correct date and time ---
            $attDT   = Carbon::parse($attFullData);                 
            $attDate = $attDT->toDateString();                    
            $attTime = $attDT->format('H:i:s');                    

            // Try to find a record for THIS date first
            $attendanceRecord = Attendance::where('employee_id', $employeeId)
                ->where('date', $attDate)
                ->first();

            // ---------- 30-MINUTE GAP CHECK ----------
            // If a record exists and has a clock-in time, check if 30 minutes have passed
            if ($attendanceRecord && $attendanceRecord->clock_in_time && !$attendanceRecord->clock_out_time) {
                $clockInDateTime = Carbon::parse($attendanceRecord->date . ' ' . $attendanceRecord->clock_in_time);
                $minutesSinceClockIn = $clockInDateTime->diffInMinutes($attDT);
                
                if ($minutesSinceClockIn < 30) {
                    // Less than 30 minutes have passed, ignore this fingerprint entry
                    file_put_contents(storage_path('logs/attendance_ignored.log'), 
                        now() . ' - Ignored fingerprint for Employee ID: ' . $employeeId . 
                        ' - Only ' . $minutesSinceClockIn . ' minutes since check-in' . PHP_EOL, 
                        FILE_APPEND
                    );
                    continue; // Skip this entry
                }
            }

            // ---------- CROSS-MIDNIGHT CLOSE (00:00–05:00) ----------
            if (!$attendanceRecord && $attTime < '05:00:00') {
                $prevDate = $attDT->copy()->subDay()->toDateString();

                $openPrev = Attendance::where('employee_id', $employeeId)
                    ->where('date', $prevDate)
                    ->whereNull('clock_out_time')
                    ->first();

                if ($openPrev) {
                    // Check 30-minute gap for cross-midnight scenario
                    $clockInDateTime = Carbon::parse($openPrev->date . ' ' . $openPrev->clock_in_time);
                    $minutesSinceClockIn = $clockInDateTime->diffInMinutes($attDT);
                    
                    if ($minutesSinceClockIn < 30) {
                        // Less than 30 minutes have passed, ignore this fingerprint entry
                        file_put_contents(storage_path('logs/attendance_ignored.log'), 
                            now() . ' - Ignored cross-midnight fingerprint for Employee ID: ' . $employeeId . 
                            ' - Only ' . $minutesSinceClockIn . ' minutes since check-in' . PHP_EOL, 
                            FILE_APPEND
                        );
                        continue; // Skip this entry
                    }

                    // Compute using full datetimes with correct dates
                    $clockIn  = Carbon::parse($openPrev->date . ' ' . $openPrev->clock_in_time);
                    $clockOut = $attDT->copy(); // this is next-day early morning

                    if ($clockOut->lessThan($clockIn)) {
                        $clockOut->addDay();
                    }

                    $totalWorkSeconds = $clockIn->diffInSeconds($clockOut);
                    $overtimeSeconds  = $this->calculateOvertimeSeconds($clockIn, $clockOut, $openPrev->date);

                    $openPrev->update([
                        'clock_out_time'   => $attTime,         // store only time; date remains previous day
                        'status'           => 1,
                        'total_work_hours' => $totalWorkSeconds,
                        'overtime_seconds' => $overtimeSeconds,
                    ]);

                    // done with this entry; continue to next
                    continue;
                }
            }

            // ---------- FIRST ENTRY OF THE DAY (CLOCK-IN) ----------
            if (!$attendanceRecord) {
                $lateThreshold = Carbon::parse($attDate . ' 08:30:00');
                $lateBySeconds = $attDT->greaterThan($lateThreshold)
                    ? $attDT->diffInSeconds($lateThreshold)
                    : 0;

                $attendanceRecord = Attendance::create([
                    'employee_id'       => $employeeId,
                    'date'              => $attDate,
                    'clock_in_time'     => $attTime,
                    'clock_out_time'    => null,
                    'status'            => 1,
                    'total_work_hours'  => null,
                    'overtime_seconds'  => null,
                    'late_by_seconds'   => $lateBySeconds,
                ]);

                // Check for automatic short leave (late arrival after 9:00 AM)
                $this->processAutoShortLeave($attendanceRecord);

                continue;
            }

            // ---------- SUBSEQUENT ENTRY OF THE SAME DAY (CLOCK-OUT) ----------
            $clockInDT  = Carbon::parse($attendanceRecord->date . ' ' . $attendanceRecord->clock_in_time);
            $clockOutDT = $attDT->copy(); // uses the parsed event's date

            if ($clockOutDT->lessThan($clockInDT)) {
                $clockOutDT->addDay(); // safety for any earlier-time edge case
            }

            $totalWorkSeconds = $clockInDT->diffInSeconds($clockOutDT);
            $overtimeSeconds  = $this->calculateOvertimeSeconds($clockInDT, $clockOutDT, $attendanceRecord->date);

            $attendanceRecord->update([
                'clock_out_time'   => $attTime,
                'status'           => 1,
                'total_work_hours' => $totalWorkSeconds,
                'overtime_seconds' => $overtimeSeconds,
            ]);
        }

        file_put_contents(storage_path('logs/success_attendance_payload.log'), now() . ' - ' . json_encode($data, JSON_PRETTY_PRINT) . 'request successfully processed  end' . PHP_EOL, FILE_APPEND);
        return response()->json(['message' => 'Records processed successfully'], 201);
    }

    public function destroy($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->delete();

            return redirect()->route('attendance.management')->with('success', 'Attendance record deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Attendance Delete Error: ' . $e->getMessage());
            return redirect()->route('attendance.management')->with('error', 'Failed to delete attendance record. ' . $e->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // Validate input
        $request->validate([
            'employee_id' => 'required|numeric',
            'clock_in_time' => 'required',
            'clock_out_time' => 'required',
            'date' => 'required|date',
        ]);

        $date = $request->input('date');
        $clockIn = Carbon::parse($date . ' ' . $request->input('clock_in_time'));
        $clockOut = Carbon::parse($date . ' ' . $request->input('clock_out_time'));

        if ($clockOut->lessThan($clockIn)) {
            $clockOut->addDay();
        }

        // Thresholds
        $eightThirty = Carbon::parse($date . ' 08:30:00');
        $tenAM = Carbon::parse($date . ' 10:00:00');
        $lateThreshold = Carbon::parse($date . ' 08:30:00');

        if ($clockIn->greaterThanOrEqualTo($tenAM)) {
            // After 10:00 AM – count from actual clock-in
            $workStart = $clockIn->copy();
        } else {
            // Before 10:00 AM – Start at 8:30 AM if earlier
            $workStart = $clockIn->lessThan($eightThirty) ? $eightThirty->copy() : $clockIn->copy();
        }

        // Work time in seconds
        $totalWorkSeconds = $workStart->diffInSeconds($clockOut);

        // Overtime calculation based on 4:30 PM cutoff
        $overtimeSeconds = $this->calculateOvertimeSeconds($clockIn, $clockOut, $date);

        // Late time calculation
        $lateBySeconds = $clockIn->greaterThan($lateThreshold)
            ? $clockIn->diffInSeconds($lateThreshold)
            : 0;

        try {
            $employee = Employee::where('employee_id', $request->employee_id)->first();

            $isUpdated = $attendance->update([
                'employee_id' => $employee->id,
                'clock_in_time' => $request->input('clock_in_time'),
                'clock_out_time' => $request->input('clock_out_time'),
                'total_work_hours' => $totalWorkSeconds,
                'overtime_seconds' => $overtimeSeconds,
                'late_by_seconds' => $lateBySeconds,
                'date' => $date,
            ]);

            return redirect()->route('attendance.management')->with(
                $isUpdated ? 'success' : 'error',
                $isUpdated ? 'Attendance updated successfully!' : 'Failed to update attendance record.'
            );
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Query Exception: ' . $e->getMessage());
            return redirect()->route('attendance.management')->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Exception: ' . $e->getMessage());
            return redirect()->route('attendance.management')->with('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|numeric|exists:employees,employee_id',
            'clock_in_time' => 'required',
            'clock_out_time' => 'required',
            'date' => 'required|date',
        ]);

        try {
            $employee = Employee::where('employee_id', $request->employee_id)->first();

            $clockIn = Carbon::parse($request->date . ' ' . $request->clock_in_time);
            $clockOut = Carbon::parse($request->date . ' ' . $request->clock_out_time);

            // Handle clockOut on next day
            if ($clockOut->lessThan($clockIn)) {
                $clockOut->addDay();
            }

            // Define time thresholds
            $eightThirty = Carbon::parse($request->date . ' 08:30:00');
            $tenAM = Carbon::parse($request->date . ' 10:00:00');
            $lateThreshold = Carbon::parse($request->date . ' 08:30:00');

            if ($clockIn->greaterThanOrEqualTo($tenAM)) {
                // Came after 10:00 AM – work from actual clock-in
                $workStart = $clockIn->copy();
            } else {
                // Came before 10:00 AM – Work from 8:30 AM or actual clock-in (whichever is later)
                $workStart = $clockIn->lessThan($eightThirty) ? $eightThirty->copy() : $clockIn->copy();
            }

            // Calculate total work seconds
            $totalWorkSeconds = $workStart->diffInSeconds($clockOut);

            // Calculate OT
            $overtimeSeconds = $this->calculateOvertimeSeconds($clockIn, $clockOut, $request->date);

            // Calculate Late By
            $lateBySeconds = $clockIn->greaterThan($lateThreshold)
                ? $clockIn->diffInSeconds($lateThreshold)
                : 0;


            $attendanceRecord = Attendance::create([
                'employee_id' => $employee->id,
                'date' => $request->date,
                'clock_in_time' => $request->clock_in_time,
                'clock_out_time' => $request->clock_out_time,
                'status' => 'present',
                'total_work_hours' => $totalWorkSeconds,
                'overtime_seconds' => $overtimeSeconds,
                'late_by_seconds' => $lateBySeconds,
            ]);

            // Check for automatic short leave (late arrival after 9:00 AM)
            $this->processAutoShortLeave($attendanceRecord);

            return redirect()->route('attendance.management')->with('success', 'Attendance added successfully!');
        } catch (\Exception $e) {
            Log::error('Attendance Store Manual Error: ' . $e->getMessage());
            return redirect()->route('attendance.management')->with('error', 'Error saving attendance. ' . $e->getMessage());
        }
    }



    /**
     * Convert HH:MM:SS format to seconds.
     *
     * @param string|null $time
     * @return int
     */
    private function convertToSeconds($time)
    {
        if (!$time) {
            return 0;
        }

        [$hours, $minutes, $seconds] = explode(':', $time);
        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    /**
     * Calculate overtime seconds using the fixed 4:30 PM cutoff.
     * For Saturdays, applies special Saturday OT rules for Head Office employees.
     */
    private function calculateOvertimeSeconds(Carbon $clockIn, Carbon $clockOut, string $referenceDate): int
    {
        $date = Carbon::parse($referenceDate);
        
        // Check if it's a Saturday
        if ($date->isSaturday()) {
            return $this->calculateSaturdayOvertimeSeconds($clockIn, $clockOut, $date);
        }
        
        // Regular weekday OT calculation (after 16:30)
        $otStart = Carbon::parse($referenceDate . ' 16:30:00');
        $effectiveStart = $clockIn->greaterThan($otStart) ? $clockIn->copy() : $otStart;

        if ($clockOut->lessThanOrEqualTo($effectiveStart)) {
            return 0;
        }

        return $clockOut->diffInSeconds($effectiveStart);
    }

    /**
     * Calculate Saturday overtime based on assignment count per month
     */
    private function calculateSaturdayOvertimeSeconds(Carbon $clockIn, Carbon $clockOut, Carbon $date): int
    {
        // Get the employee_id from the context (we'll need to pass it)
        // For now, we'll need to modify the calling methods to pass employee_id
        // But let's create the logic first
        
        $totalWorkedSeconds = $clockIn->diffInSeconds($clockOut);
        
        if ($totalWorkedSeconds <= 0) {
            return 0;
        }
        
        // For now, return OT after 8 hours (28800 seconds)
        // The full Saturday logic will be applied at payroll generation
        // This ensures some OT is recorded in attendance
        $standardSeconds = 28800; // 8 hours
        return max(0, $totalWorkedSeconds - $standardSeconds);
    }

    /**
     * Process automatic short leave for late attendance
     */
    private function processAutoShortLeave($attendanceRecord)
    {
        if (!$attendanceRecord->clock_in_time) {
            return;
        }
        
        $clockInTime = \Carbon\Carbon::parse($attendanceRecord->clock_in_time);
        $lateThreshold = \Carbon\Carbon::parse('09:00:00');
        
        if ($clockInTime->gt($lateThreshold)) {
            // Get employee
            $employee = \App\Models\Employee::find($attendanceRecord->employee_id);
            if (!$employee) {
                return;
            }
            
            // Check if auto short leave already exists
            $existingAutoLeave = \App\Models\AutoShortLeave::where('attendance_id', $attendanceRecord->id)->first();
            
            if (!$existingAutoLeave) {
                // Create auto short leave
                \App\Models\AutoShortLeave::create([
                    'employee_id' => $employee->id,
                    'attendance_id' => $attendanceRecord->id,
                    'date' => $attendanceRecord->date,
                    'actual_clock_in' => $attendanceRecord->clock_in_time,
                    'short_leave_type' => 'morning',
                    'is_deducted' => false
                ]);
                
                // Create leave record
                \App\Models\Leave::create([
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->full_name,
                    'employment_ID' => $employee->employee_id,
                    'leave_type' => 'Auto Short Leave - Late Arrival',
                    'leave_category' => 'short_leave',
                    'short_leave_type' => 'morning',
                    'start_date' => $attendanceRecord->date,
                    'end_date' => $attendanceRecord->date,
                    'start_time' => '08:30:00',
                    'end_time' => $attendanceRecord->clock_in_time,
                    'duration' => 1,
                    'approved_person' => 'System',
                    'status' => 'approved',
                    'description' => 'Automatically generated for late arrival at ' . $attendanceRecord->clock_in_time,
                    'is_no_pay' => false,
                    'no_pay_amount' => 0
                ]);
                
                // Update employee balances
                $employee->checkMonthlyReset();
                $employee->increment('short_leave_used', 1);
                $employee->increment('monthly_short_leaves_used', 1);
            }
        }
    }
}
