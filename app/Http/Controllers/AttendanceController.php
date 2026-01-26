<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Imports\AttendanceImport;

use Carbon\Carbon;


class AttendanceController extends Controller
{
    private const SHIFT_START = '08:30:00';
    private const SHIFT_END = '16:30:00';
    public function create()
    {
        // Fetch all employees to associate with the attendance record
        $employees = Employee::all();

        // Return the view to create a new attendance record
        return view('management.attendance.attendance-create', compact('employees'));
    }
    public function edit($id)
    {
        // Find the attendance record by ID
        $attendance = Attendance::findOrFail($id);

        // Retrieve the employee associated with this attendance record
        $employee = Employee::findOrFail($attendance->employee_id); // Assuming `employee_id` exists in the attendance table
        
        // Fetch all employees for dropdown
        $employees = Employee::all();
        //  dd($employee);
        // Return the edit view with both attendance and employee data
        return view('management.attendance.attendance-edit', compact('attendance', 'employee', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'clock_in_date' => 'nullable|date',
            'clock_out_date' => 'nullable|date',
            'clock_in_time' => 'nullable|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i',
        ]);

        $clockInDate = $validated['clock_in_date'] ?? $validated['date'];
        $clockOutDate = $validated['clock_out_date'] ?? $clockInDate;

        $clockInDT = $this->combineDateAndTime($clockInDate, $validated['clock_in_time'] ?? null);
        $clockOutDT = $this->combineDateAndTime($clockOutDate, $validated['clock_out_time'] ?? null);

        $durations = $this->deriveManualDurations($clockInDT, $clockOutDT);

        $attendance->update([
            'employee_id' => $validated['employee_id'],
            'date' => $validated['date'],
            'clock_in_time' => $validated['clock_in_time'] ?? null,
            'clock_out_time' => $validated['clock_out_time'] ?? null,
            'total_work_hours' => $durations['total_work_seconds'],
            'overtime_seconds' => $durations['overtime_seconds'],
            'late_by_seconds' => $durations['late_by_seconds'],
        ]);

        return redirect()->route('attendance.management')->with('success', 'Attendance record updated successfully.');
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return redirect()
            ->route('attendance.management')
            ->with('success', 'Attendance record deleted successfully.');
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

    /**
     * Import attendance records from Excel file(s)
     */
    public function importAttendance(Request $request)
    {
        try {
            $request->validate([
                'attendance_files' => 'required|array|min:1',
                'attendance_files.*' => 'file|mimes:xls,xlsx|max:10240'
            ]);

            $files = $request->file('attendance_files');
            
            // Initialize aggregated results
            $allProcessed = [];
            $allMissing = [];
            $allErrors = [];
            $dateRanges = [];
            $filesProcessed = 0;
            $savedFiles = [];

            // Process each file
            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                $fileName = $file->getClientOriginalName();

                // Save the file to storage
                $savedPath = $file->store('attendance_imports', 'public');
                $savedFiles[] = [
                    'original_name' => $fileName,
                    'saved_path' => $savedPath,
                    'uploaded_at' => now()->toDateTimeString()
                ];

                // Process the import
                $importer = new AttendanceImport();
                $result = $importer->import($filePath);

                if (!$result['success']) {
                    $allErrors[] = "File '{$fileName}': " . $result['error'];
                    continue;
                }

                // Aggregate results
                $allProcessed = array_merge($allProcessed, $result['processed']);
                $allMissing = array_merge($allMissing, $result['missing']);
                $allErrors = array_merge($allErrors, $result['errors']);
                
                if (!empty($result['dateRange'])) {
                    $dateRanges[] = [
                        'file' => $fileName,
                        'range' => $result['dateRange']
                    ];
                }
                
                $filesProcessed++;
            }

            // Store aggregated results in session (persist, not flash)
            session()->put('import_results', [
                'processed' => $allProcessed,
                'missing' => $allMissing,
                'errors' => $allErrors,
                'dateRanges' => $dateRanges,
                'filesProcessed' => $filesProcessed,
                'totalFiles' => count($files),
                'savedFiles' => $savedFiles,
                'imported_at' => now()->toDateTimeString()
            ]);
            
            // Flag to show the modal on redirect
            session()->flash('show_import_modal', true);

            $message = "Processed {$filesProcessed} of " . count($files) . " file(s). ";
            $message .= count($allProcessed) . ' records processed successfully.';
            if (!empty($allMissing)) {
                $message .= ' ' . count($allMissing) . ' records with missing data found.';
            }
            if (!empty($allErrors)) {
                $message .= ' ' . count($allErrors) . ' errors occurred.';
            }

            return redirect()->route('attendance.management')->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Attendance import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function combineDateAndTime(?string $date, ?string $time): ?Carbon
    {
        if (!$date || !$time) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time, config('app.timezone'));
    }

    private function deriveManualDurations(?Carbon $clockInDT, ?Carbon $clockOutDT): array
    {
        $result = [
            'total_work_seconds' => null,
            'overtime_seconds' => null,
            'late_by_seconds' => null,
        ];

        if (!$clockInDT) {
            return $result;
        }

        $shiftDate = $clockInDT->format('Y-m-d');
        $shiftStart = Carbon::parse($shiftDate . ' ' . self::SHIFT_START, config('app.timezone'));

        if ($clockInDT->greaterThan($shiftStart)) {
            $result['late_by_seconds'] = $clockInDT->diffInSeconds($shiftStart);
        }

        if (!$clockOutDT) {
            return $result;
        }

        $adjustedClockOut = $clockOutDT->copy();
        if ($adjustedClockOut->lessThanOrEqualTo($clockInDT)) {
            $adjustedClockOut->addDay();
        }

        $workCountStart = $clockInDT->lessThan($shiftStart) ? $shiftStart->copy() : $clockInDT->copy();
        $result['total_work_seconds'] = $adjustedClockOut->lessThanOrEqualTo($workCountStart)
            ? 0
            : $workCountStart->diffInSeconds($adjustedClockOut);

        $result['overtime_seconds'] = $this->calculateOvertimeSeconds($clockInDT->copy(), $adjustedClockOut->copy(), $shiftDate);

        return $result;
    }

    public function updateMissingRecord(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:employees,employee_id',
                'date' => 'required|date',
                'clock_in' => 'nullable|date_format:H:i',
                'clock_out' => 'nullable|date_format:H:i',
            ]);

            // Find the employee by employee_id
            $employee = Employee::where('employee_id', $request->employee_id)->first();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $date = $request->date;
            $clockIn = $request->clock_in ? $request->clock_in . ':00' : null;
            $clockOut = $request->clock_out ? $request->clock_out . ':00' : null;

            if (!$clockIn && !$clockOut) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide at least Clock In or Clock Out time'
                ], 400);
            }

            // Check if attendance record already exists
            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $date)
                ->first();

            if ($attendance) {
                // Update existing record
                $updates = [];
                
                if ($clockIn && !$attendance->clock_in_time) {
                    $updates['clock_in_time'] = $clockIn;
                }
                if ($clockOut && !$attendance->clock_out_time) {
                    $updates['clock_out_time'] = $clockOut;
                }

                if (!empty($updates)) {
                    // Recalculate work hours if both times are now available
                    $finalClockIn = $updates['clock_in_time'] ?? $attendance->clock_in_time;
                    $finalClockOut = $updates['clock_out_time'] ?? $attendance->clock_out_time;
                    
                    if ($finalClockIn && $finalClockOut) {
                        $clockInDT = Carbon::parse($date . ' ' . $finalClockIn);
                        $clockOutDT = Carbon::parse($date . ' ' . $finalClockOut);
                        
                        $cutoff = Carbon::parse($date . ' 08:30:00');
                        $startCount = $clockInDT->lessThan($cutoff) ? $cutoff->copy() : $clockInDT->copy();
                        
                        if ($clockOutDT->lessThan($startCount)) {
                            $clockOutDT->addDay();
                        }

                        $updates['total_work_hours'] = $startCount->diffInSeconds($clockOutDT);
                        
                        // Calculate overtime
                        $standardEnd = Carbon::parse($date . ' 16:30:00');
                        if ($clockOutDT->greaterThan($standardEnd)) {
                            $updates['overtime_seconds'] = $standardEnd->diffInSeconds($clockOutDT);
                        }
                        
                        // Calculate late arrival
                        $lateThreshold = Carbon::parse($date . ' 08:30:00');
                        if ($clockInDT->greaterThan($lateThreshold)) {
                            $updates['late_by_seconds'] = $clockInDT->diffInSeconds($lateThreshold);
                        }
                    }

                    $attendance->update($updates);
                }
            } else {
                // Create new record
                $clockInDT = $clockIn ? Carbon::parse($date . ' ' . $clockIn) : null;
                $clockOutDT = $clockOut ? Carbon::parse($date . ' ' . $clockOut) : null;

                $lateBySeconds = 0;
                if ($clockInDT) {
                    $lateThreshold = Carbon::parse($date . ' 08:30:00');
                    if ($clockInDT->greaterThan($lateThreshold)) {
                        $lateBySeconds = $clockInDT->diffInSeconds($lateThreshold);
                    }
                }

                $totalWorkSeconds = null;
                $overtimeSeconds = null;
                
                if ($clockInDT && $clockOutDT) {
                    $cutoff = Carbon::parse($date . ' 08:30:00');
                    $startCount = $clockInDT->lessThan($cutoff) ? $cutoff->copy() : $clockInDT->copy();
                    
                    if ($clockOutDT->lessThan($startCount)) {
                        $clockOutDT->addDay();
                    }

                    $totalWorkSeconds = $startCount->diffInSeconds($clockOutDT);

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
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance record updated successfully',
                'data' => [
                    'employee_name' => $employee->full_name,
                    'date' => $date,
                    'clock_in' => $attendance->clock_in_time,
                    'clock_out' => $attendance->clock_out_time,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update missing attendance record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clearImportResults()
    {
        session()->forget('import_results');
        return redirect()->route('attendance.management')->with('success', 'Import results cleared successfully');
    }

}