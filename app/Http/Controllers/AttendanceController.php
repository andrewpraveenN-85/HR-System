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

private function calculateOvertimeSeconds($clockInDT, $clockOutDT, $date)
{
    // Define the standard end of workday as 4:30 PM
    $standardEnd = \Carbon\Carbon::parse($date . ' 16:30:00');

    // Handle cross-midnight (e.g., clock-out after midnight)
    if ($clockOutDT->lessThan($clockInDT)) {
        $clockOutDT->addDay();
    }

    // If employee leaves before 4:30 PM → no overtime
    if ($clockOutDT->lessThanOrEqualTo($standardEnd)) {
        return 0;
    }

    // Otherwise, overtime = time worked after 4:30 PM
    return $standardEnd->diffInSeconds($clockOutDT);
}



   public function store(Request $request)
{
    $data = $request->json()->all();
    file_put_contents(storage_path('logs/attendance_payload.log'), now() . ' - ' . json_encode($data, JSON_PRETTY_PRINT) . ' request received end' . PHP_EOL, FILE_APPEND);

    if (!is_array($data)) {
        file_put_contents(storage_path('logs/error_attendance_payload.log'), now() . ' error - ' . json_encode($data, JSON_PRETTY_PRINT) . ' date format error end' . PHP_EOL, FILE_APPEND);
        return response()->json(['error' => 'Invalid data format'], 400);
    }

    // Wrap single entry into array
    if (isset($data['EmpId'])) {
        $data = [$data];
    }

    foreach ($data as $entry) {
        if (!isset($entry['EmpId']) || !isset($entry['AttTime'])) {
            file_put_contents(storage_path('logs/error_attendance_payload.log'), now() . ' Missing required fields: EmpId or AttTime - ' . json_encode($data, JSON_PRETTY_PRINT) . ' end' . PHP_EOL, FILE_APPEND);
            return response()->json(['error' => 'Missing required fields: EmpId or AttTime'], 400);
        }
        
        $employee = Employee::where('employee_id', $entry['EmpId'])->first();

        if (!$employee) {
            file_put_contents(storage_path('logs/error_attendance_payload.log'), now() . " Employee ID {$entry['EmpId']} not found" . PHP_EOL, FILE_APPEND);
            return response()->json(['error' => "Employee ID {$entry['EmpId']} not found"], 404);
        }

        $employeeId = $employee->id; // the actual ID from employees table
        $attFullData = $entry['AttTime'];

        // Parse the datetime
        $attDT   = Carbon::parse($attFullData);
        $attDate = $attDT->toDateString();
        $attTime = $attDT->format('H:i:s');

        // Check if an attendance record exists for this date
        $attendanceRecord = Attendance::where('employee_id', $employeeId)
            ->where('date', $attDate)
            ->first();

        // --- Cross-midnight case: after midnight but before 5 AM ---
        if (!$attendanceRecord && $attTime < '05:00:00') {
            $prevDate = $attDT->copy()->subDay()->toDateString();

            $openPrev = Attendance::where('employee_id', $employeeId)
                ->where('date', $prevDate)
                ->whereNull('clock_out_time')
                ->first();
                
          if ($openPrev) {
                    // Check if 30 minutes have passed since clock-in
                    $clockInDT  = Carbon::parse($openPrev->date . ' ' . $openPrev->clock_in_time);
                    $clockOutDT = $attDT->copy();
                    
                    $minutesSinceClockIn = $clockInDT->diffInMinutes($clockOutDT);
                    if ($minutesSinceClockIn < 30) {
                        Log::info('Ignoring check-out: less than 30 minutes since check-in (cross-midnight)', [
                            'Employee' => $employeeId,
                            'ClockIn' => $clockInDT,
                            'AttemptedCheckOut' => $clockOutDT,
                            'MinutesSince' => $minutesSinceClockIn
                        ]);
                        continue;
                    }

                    $cutoff = Carbon::parse($openPrev->date . ' 08:30:00');
                    $startCount = $clockInDT->lessThan($cutoff) ? $cutoff->copy() : $clockInDT->copy();
                    if ($clockOutDT->lessThan($startCount)) $clockOutDT->addDay();

                    $totalWorkSeconds = $clockOutDT->lessThanOrEqualTo($startCount)
                        ? 0
                        : $startCount->diffInSeconds($clockOutDT);

                    $overtimeSeconds = $this->calculateOvertimeSeconds($clockInDT, $clockOutDT, $openPrev->date);

                    Log::info('Updating previous day attendance (cross-midnight)', [
                        'Employee' => $employeeId,
                        'ClockIn' => $clockInDT,
                        'ClockOut' => $clockOutDT,
                        'StartCount' => $startCount,
                        'TotalWorkSeconds' => $totalWorkSeconds,
                        'OvertimeSeconds' => $overtimeSeconds
                    ]);

                    $openPrev->update([
                        'clock_out_time'   => $attTime,
                        'status'           => 1,
                        'total_work_hours' => $totalWorkSeconds,
                        'overtime_seconds' => $overtimeSeconds,
                    ]);

                    continue;
                }
            }

            // First clock-in of the day
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

                $this->processAutoShortLeave($attendanceRecord);

                continue;
            }

        // Subsequent clock-out - Check if 30 minutes have passed since clock-in
            $clockInDT  = Carbon::parse($attendanceRecord->date . ' ' . $attendanceRecord->clock_in_time);
            $clockOutDT = $attDT->copy();

            $minutesSinceClockIn = $clockInDT->diffInMinutes($clockOutDT);
            if ($minutesSinceClockIn < 30) {
                Log::info('Ignoring check-out: less than 30 minutes since check-in', [
                    'Employee' => $employeeId,
                    'ClockIn' => $clockInDT,
                    'AttemptedCheckOut' => $clockOutDT,
                    'MinutesSince' => $minutesSinceClockIn
                ]);
                continue;
            }

            // Check if 30 minutes have passed since last clock-out (if exists)
            if ($attendanceRecord->clock_out_time) {
                $lastClockOutDT = Carbon::parse($attendanceRecord->date . ' ' . $attendanceRecord->clock_out_time);
                $minutesSinceLastClockOut = $lastClockOutDT->diffInMinutes($clockOutDT);
                
                if ($minutesSinceLastClockOut < 30) {
                    Log::info('Ignoring check-out: less than 30 minutes since last check-out', [
                        'Employee' => $employeeId,
                        'LastClockOut' => $lastClockOutDT,
                        'AttemptedCheckOut' => $clockOutDT,
                        'MinutesSince' => $minutesSinceLastClockOut
                    ]);
                    continue;
                }
            }

            $cutoff = Carbon::parse($attendanceRecord->date . ' 08:30:00');
            $startCount = $clockInDT->lessThan($cutoff) ? $cutoff->copy() : $clockInDT->copy();

            if ($clockOutDT->lessThan($startCount)) $clockOutDT->addDay();

            $totalWorkSeconds = $clockOutDT->lessThanOrEqualTo($startCount)
                ? 0
                : $startCount->diffInSeconds($clockOutDT);

            $overtimeSeconds = $this->calculateOvertimeSeconds($clockInDT, $clockOutDT, $attendanceRecord->date);

            Log::info('Updating attendance', [
                'Employee' => $employeeId,
                'ClockIn' => $clockInDT,
                'ClockOut' => $clockOutDT,
                'StartCount' => $startCount,
                'TotalWorkSeconds' => $totalWorkSeconds,
                'OvertimeSeconds' => $overtimeSeconds
            ]);

            $attendanceRecord->update([
                'clock_out_time'   => $attTime,
                'status'           => 1,
                'total_work_hours' => $totalWorkSeconds,
                'overtime_seconds' => $overtimeSeconds,
            ]);
        }

        Log::info('All attendance records processed successfully', $data);
        return response()->json(['message' => 'Records processed successfully'], 201);
    }
}