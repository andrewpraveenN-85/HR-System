<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leave;
use App\Models\Employee;
use App\Models\AutoShortLeave;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function create()
{
    return view('management.leave.leave-create'); 
}

    /**
     * Get employee leave data via AJAX
     */
    public function getEmployeeLeaveData(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $employee = Employee::with('department')->where('employee_id', $employeeId)->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
        
        $balances = $employee->getLeaveBalances();
        
        // Get recent leave history
        $recentLeaves = Leave::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        return response()->json([
            'employee' => $employee,
            'balances' => $balances,
            'recent_leaves' => $recentLeaves
        ]);
    }

public function store(Request $request)
{  

   // Validate the input
    $validated = $request->validate([
        'employment_ID' => 'required|string|max:255',
        'leave_type' => 'required|string|max:255',
        'leave_category' => 'required|in:full_day,half_day,short_leave',
        'half_day_type' => 'nullable|in:morning,evening',
        'short_leave_type' => 'nullable|in:morning,evening',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'start_time' => 'nullable|date_format:H:i',
        'end_time' => 'nullable|date_format:H:i',
        'approved_person' => 'required|string|max:255',
        'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        'description' => 'nullable|string',
        'supporting_documents' => 'nullable|array',
    ]);

    // Get the employee
    $employee = Employee::where('employee_id', $validated['employment_ID'])->first();
    if (!$employee) {
        return back()->withErrors(['employment_ID' => 'Invalid Employee ID'])->withInput();
    }

    // Calculate leave duration
    $duration = $this->calculateLeaveDuration($validated);

    // Calculate no-pay amount
    $noPayAmount = $employee->calculateNoPayAmount($validated['leave_category'], $duration);
    $isNoPay = $noPayAmount > 0;

    // Handle file uploads
    $uploadedFiles = [];
    if ($request->hasFile('supporting_documents')) {
        foreach ($request->file('supporting_documents') as $file) {
            $filePath = $file->storeAs(
                'leave-documents',
                time() . '_' . $file->getClientOriginalName(),
                'public'
            );
            $uploadedFiles[] = $filePath;
        }
    }

    // Create leave record manually
    $leave = new Leave();
    $leave->employee_id = $employee->id;                 // REQUIRED
    $leave->employee_name = $employee->full_name;
    $leave->employment_ID = $validated['employment_ID'];
    $leave->leave_type = $validated['leave_type'];
    $leave->leave_category = $validated['leave_category'];
    $leave->half_day_type = $validated['half_day_type'] ?? null;
    $leave->short_leave_type = $validated['short_leave_type'] ?? null;
    $leave->approved_person = $validated['approved_person'];
    $leave->start_date = $validated['start_date'];
    $leave->end_date = $validated['end_date'];
    $leave->start_time = $validated['start_time'] ?? null;
    $leave->end_time = $validated['end_time'] ?? null;
    $leave->duration = $duration;
    $leave->status = $validated['status'];
    $leave->description = $validated['description'] ?? null;
    $leave->is_no_pay = $isNoPay;
    $leave->no_pay_amount = $noPayAmount;
    $leave->supporting_documents = !empty($uploadedFiles) ? json_encode($uploadedFiles) : null;

    $leave->save();


    // ✅ If the leave is approved, check if it's no-pay
    if ($leave->status === 'approved') {
        $leave->updateEmployeeBalances();
        $this->calculateNoPayForLeave($leave);
    }

    return redirect()->back()->with('success', 'Leave submitted successfully.');

    try {
        $validated = $request->validate([
            'employment_ID' => 'required|string|max:255',
            'leave_type' => 'required|string|max:255',
            'leave_category' => 'required|in:full_day,half_day,short_leave',
            'half_day_type' => 'nullable|in:morning,evening',
            'short_leave_type' => 'nullable|in:morning,evening',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'approved_person' => 'required|string|max:255',
            'status' => ['required',Rule::in(['pending', 'approved', 'rejected']),],
            'description' => 'nullable|string',
            'supporting_documents' => 'nullable|array',
        ]);            
    } catch (\Illuminate\Validation\ValidationException $e) {
        return redirect()->route('leave.management')->with('error', 'Validation failed: ' . json_encode($e->errors()));
    }
        $employee = Employee::where('employee_id', $request->employment_ID)->first();
        if (!$employee) {
            return back()->withErrors(['employment_ID' => 'Invalid Employee ID.'])->withInput();
        }

        // Calculate duration based on leave category
        $duration = $this->calculateLeaveDuration($validated);
        
        // Calculate no-pay amount if applicable
        $noPayAmount = $employee->calculateNoPayAmount($validated['leave_category'], $duration);
        $isNoPay = $noPayAmount > 0;

    try {  
        $uploadedFiles = [];
        if ($request->hasFile('supporting_documents')) {
            foreach ($request->file('supporting_documents') as $file) {
                $filePath = $file->storeAs(
                    'leave-documents', 
                    time() . '_' . $file->getClientOriginalName(), 
                    'public' 
                );
                $uploadedFiles[] = $filePath;
            }
        }
        

    } catch (\Exception $e) {
        // Dump the error message and stack trace for debugging
        return redirect()->route('leave.management')->with('error', 'Error adding record!'.$e);

    }
    
        $leave = new Leave();
        $leave->employee_id = $employee->id;
        $leave->employee_name = $employee->full_name;
        $leave->employment_ID = $validated['employment_ID'];
        $leave->leave_type = $validated['leave_type'];
        $leave->leave_category = $validated['leave_category'];
        $leave->half_day_type = $validated['half_day_type'] ?? null;
        $leave->short_leave_type = $validated['short_leave_type'] ?? null;
        $leave->approved_person = $validated['approved_person'];
        $leave->start_date = $validated['start_date'];
        $leave->end_date = $validated['end_date'];
        $leave->start_time = $validated['start_time'] ?? null;
        $leave->end_time = $validated['end_time'] ?? null;
        $leave->duration = $duration;
        $leave->status = $validated['status'];
        $leave->description = $validated['description'] ?? null;
        $leave->is_no_pay = $isNoPay;
        $leave->no_pay_amount = $noPayAmount;
        $leave->supporting_documents = !empty($uploadedFiles) ? json_encode($uploadedFiles) : null;

        $leave->save();
        
        // Update employee balances if approved
        if ($validated['status'] === 'approved') {
            $leave->updateEmployeeBalances();
        }
            
        return redirect()->route('leave.management')->with('success', 'Leave added successfully.');
}
    

public function calculateMonthlyNoPay()
{
    $employees = Employee::all();
    $currentYear = now()->year;

    foreach ($employees as $employee) {
        // Get total leave days taken this year
        $totalLeaveDays = Leave::where('employment_ID', $employee->employment_ID)
            ->whereYear('leave_date', $currentYear)
            ->sum('no_of_days');

        // Check if they exceeded 21 annual leave days
        $excessLeaveDays = max(0, $totalLeaveDays - 21);

        // Calculate no pay amount if exceeded
        if ($excessLeaveDays > 0) {
            $dailySalary = $employee->basic_salary / 30;
            $noPayAmount = $excessLeaveDays * $dailySalary;

            // Update or create in leaves table (example column: no_pay_amount)
            Leave::updateOrCreate(
                [
                    'employment_ID' => $employee->employment_ID,
                    'leave_type' => 'No Pay',
                    'leave_month' => now()->month,
                    'leave_year' => $currentYear
                ],
                [
                    'no_of_days' => $excessLeaveDays,
                    'no_pay_amount' => $noPayAmount,
                    'status' => 'approved'
                ]
            );
        }
    }

    return response()->json(['message' => 'Monthly No Pay calculated successfully.']);
}


     // Display details of a specific payroll record
    public function show($id)
    {
        $leave = Leave::with('employee')->findOrFail($id); // Load the related employee
        return view('management.leave.leave-details', compact('leave'));
    }

    public function edit($id)
    {
        $leave = Leave::with('employee')->findOrFail($id);
        return view('management.leave.leave-edit', compact('leave'));
    }
 
    public function update(Request $request, $id)
{
    $leave = Leave::findOrFail($id);
    $oldStatus = $leave->status;
    
    $validated = $request->validate([
        'employment_ID' => 'required|string|max:255',
        'leave_type' => 'required|string|max:255',
        'leave_category' => 'required|in:full_day,half_day,short_leave',
        'half_day_type' => 'nullable|in:morning,evening',
        'short_leave_type' => 'nullable|in:morning,evening',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'start_time' => 'nullable|date_format:H:i',
        'end_time' => 'nullable|date_format:H:i',
        'approved_person' => 'required|string|max:255',
        'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        'description' => 'nullable|string',
        'supporting_documents' => 'nullable|array',
    ]);
     

        $employee = Employee::where('employee_id', $validated['employment_ID'])->first();
        if (!$employee) {
            return back()->withErrors(['employment_ID' => 'Invalid Employee ID.'])->withInput();
        }else{
           // $leave->update($validated);

        // Calculate new duration
        $duration = $this->calculateLeaveDuration($validated);
        
        // Calculate no-pay amount
        $noPayAmount = $employee->calculateNoPayAmount($validated['leave_category'], $duration);
        $isNoPay = $noPayAmount > 0;

        $currentFiles = is_string($leave->supporting_documents) 
        ? json_decode($leave->supporting_documents, true) ?: [] 
        : (is_array($leave->supporting_documents) ? $leave->supporting_documents : []);
    
    $remainingFiles = is_string($request->input('existing_files')) 
        ? json_decode($request->input('existing_files', '[]'), true) ?: [] 
        : (is_array($request->input('existing_files')) ? $request->input('existing_files') : []);
    
    $newFiles = [];
    
    if ($request->hasFile('supporting_documents')) {
        foreach ($request->file('supporting_documents') as $file) {
            try {
                $filePath = $file->storeAs(
                    'leave-documents',
                    time() . '_' . $file->getClientOriginalName(),
                    'public'
                );
                $newFiles[] = $filePath;
            } catch (\Exception $e) {
                return back()->withErrors(['file_upload' => 'Error uploading file: ' . $file->getClientOriginalName()]);
            }
        }
    }
    
    $finalFiles = array_values(array_unique(array_merge($remainingFiles, $newFiles)));
    


        $leave->employee_name = $employee->full_name;
        $leave->employee_id = $employee->id;
        $leave->employment_ID = $request->employment_ID;
        $leave->leave_type = $request->leave_type;
        $leave->leave_category = $request->leave_category;
        $leave->half_day_type = $request->half_day_type;
        $leave->short_leave_type = $request->short_leave_type;
        $leave->approved_person = $request->approved_person;
        $leave->start_date = $request->start_date;
        $leave->end_date = $request->end_date;
        $leave->start_time = $request->start_time;
        $leave->end_time = $request->end_time;
        $leave->duration = $duration;
        $leave->status = $request->status;
        $leave->description = $request->description ?? null;
        $leave->is_no_pay = $isNoPay;
        $leave->no_pay_amount = $noPayAmount;
        $leave->supporting_documents = !empty($finalFiles) ? json_encode(array_values(array_unique($finalFiles))) : null;
        
        $leave->save();
        
        // Update employee balances if status changed to approved
        if ($oldStatus !== 'approved' && $request->status === 'approved') {
         $leave->updateEmployeeBalances();
         $this->calculateNoPayForLeave($leave);
}
        
        return redirect()->route('leave.management')->with('success', 'Leave updated successfully.');

}
}

    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);
        $leave->delete();

        return redirect()->route('leave.management')->with('success', 'Leave record deleted successfully.');
    }

    public function getLeaveFiles($leaveId)
    {
        $leave = Leave::with('documents')->findOrFail($leaveId);
        return response()->json($leave->documents);
    }

    /**
     * Calculate leave duration based on category
     */
    private function calculateLeaveDuration($data)
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        
        switch ($data['leave_category']) {
            case 'full_day':
                return $endDate->diffInDays($startDate) + 1;
                
            case 'half_day':
                return ($endDate->diffInDays($startDate) + 1) * 0.5;
                
            case 'short_leave':
                return $endDate->diffInDays($startDate) + 1; // Each day counts as 1 short leave
                
            default:
                return 1;
        }
    }

    /**
     * Process automatic short leave for late attendance
     */
    public function processAutoShortLeave($attendanceId)
    {
        $attendance = \App\Models\Attendance::findOrFail($attendanceId);
        $employee = $attendance->employee;
        
        if (!$attendance->clock_in_time) {
            return;
        }
        
        $clockInTime = Carbon::parse($attendance->clock_in_time);
        $lateThreshold = Carbon::parse('09:00:00');
        
        if ($clockInTime->gt($lateThreshold)) {
            // Check if auto short leave already exists
            $existingAutoLeave = AutoShortLeave::where('attendance_id', $attendanceId)->first();
            
            if (!$existingAutoLeave) {
                // Create auto short leave
                AutoShortLeave::create([
                    'employee_id' => $employee->id,
                    'attendance_id' => $attendanceId,
                    'date' => $attendance->date,
                    'actual_clock_in' => $attendance->clock_in_time,
                    'short_leave_type' => 'morning',
                    'is_deducted' => false
                ]);
                
                // Create leave record
                Leave::create([
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->full_name,
                    'employment_ID' => $employee->employee_id,
                    'leave_type' => 'Auto Short Leave - Late Arrival',
                    'leave_category' => 'short_leave',
                    'short_leave_type' => 'morning',
                    'start_date' => $attendance->date,
                    'end_date' => $attendance->date,
                    'start_time' => '08:30:00',
                    'end_time' => $attendance->clock_in_time,
                    'duration' => 1,
                    'approved_person' => 'System',
                    'status' => 'approved',
                    'description' => 'Automatically generated for late arrival at ' . $attendance->clock_in_time,
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
    /**
 * Check and calculate No Pay Leave
 */
private function calculateNoPayForLeave(Leave $leave)
{
    $employee = $leave->employee;
    if (!$employee) return;

    // Define your leave year: March 6 to April 5
    $currentYear = now()->year;
    $yearStart = Carbon::parse("1 January $currentYear");
    $yearEnd = Carbon::parse("31 December " . ($currentYear + 1));

    // Adjust if leave is before March 6 (belongs to previous cycle)
    if (Carbon::parse($leave->start_date)->lt($yearStart)) {
        $yearStart->subYear();
        $yearEnd->subYear();
    }

    // Get total approved full-day and half-day leaves within the year
    $totalUsedDays = Leave::where('employee_id', $employee->id)
        ->where('status', 'approved')
        ->whereIn('leave_category', ['full_day', 'half_day'])
        ->whereBetween('start_date', [$yearStart, $yearEnd])
        ->sum('duration');

    // If employee exceeded 21 annual leaves
    if ($totalUsedDays > 21) {
        $extraDays = $totalUsedDays - 21;

        // Example: daily rate (change if you have salary field)
        $dailyRate = $employee->daily_rate ?? 1000;

        // Calculate total no-pay amount
        $noPayAmount = $extraDays * $dailyRate;

        // Update this leave record
        $leave->update([
            'is_no_pay' => true,
            'no_pay_amount' => $noPayAmount,
        ]);
    } else { 
        // Reset if under limit
        $leave->update([
            'is_no_pay' => false,
            'no_pay_amount' => 0,
        ]);
    }
}

}