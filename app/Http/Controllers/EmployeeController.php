<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\Education;
use App\Models\BankDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\NullableType;
use App\Models\SalaryDetail;
use Illuminate\Support\Facades\DB;
use App\Models\SalaryDetails;

class EmployeeController extends Controller
{
    /**
     * Get all employees
     */
    public function GetAllEmployees()
    {
        $employees = Employee::with(['department', 'position'])->paginate(8);
        return view('management.employee-management', compact('employees'));
    }

    /**
     * Display employee management card view
     */
    public function index()
    {
        $employees = Employee::with(['position', 'department'])->paginate(8);
        return view('management.employee.employee-management', compact('employees'));
    }

    /**
     * Show employee details
     */
    public function show($id)
    {
        $employee = Employee::with(['department', 'education', 'bankDetails'])->findOrFail($id);
        return view('management.employee.employee-details', compact('employee'));
    }

    /**
     * Show edit employee form
     */
    public function edit($id)
    {
        $employee = Employee::with(['department', 'education', 'bankDetails'])->findOrFail($id);
        $departments = Department::all();
        return view('management.employee.employee-edit', compact('employee', 'departments'));
    }

    /**
     * Update employee
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $education = Education::findOrFail($employee->education_id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'first_name' => 'nullable|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'last_name' => 'nullable|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|email|unique:employees,email,' . $id,
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'nic' => 'nullable|string',
            'gender' => 'nullable|string',
            'title' => 'nullable|string|regex:/^[a-zA-Z\s]+$/',
            'employment_type' => 'nullable|string|regex:/^[a-zA-Z\s]+$/',
            'image' => 'nullable|mimes:jpg,jpeg,png,bmp,tiff|max:4096',
            'employee_id' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'branch' => 'required|string',
            'name' => 'required|string',
            'probation_start_date' => 'nullable|date',
            'probation_period' => 'nullable|integer|min:1',
            'department_id' => 'nullable|exists:departments,id',
            'manager_id' => 'nullable|exists:employees,id',
            'education_id' => 'nullable|exists:education,id',
            'employment_start_date' => 'nullable|date',
            'employment_end_date' => 'nullable|date',
            'status' => 'nullable|string|max:255',
            'legal_documents' => 'nullable|array',
            'account_holder_name' => 'required|string|regex:/^[a-zA-Z\s]+$/',
            'bank_name' => 'required|string|regex:/^[a-zA-Z\s]+$/',
            'account_number' => 'required|integer',
            'branch_name' => 'required|string',
            'degree' => 'nullable|string|max:255',
            'institution' => 'nullable|string|max:255',
            'graduation_year' => 'nullable|integer',
            'work_experience_years' => 'nullable|integer',
            'work_experience_role' => 'nullable|string|max:255',
            'work_experience_company' => 'nullable|string|max:255',
            'course_name' => 'nullable|string|max:255',
            'training_provider' => 'nullable|string|max:255',
            'completion_date' => 'nullable|date',
            'certification_status' => 'nullable|string|max:255',
        ]);

        // Handle image
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->storeAs(
                'images',
                time() . '_' . $request->file('image')->getClientOriginalName(),
                'public'
            );

            if ($employee->image) {
                Storage::disk('public')->delete($employee->image);
            }
            $employee->image = $imagePath;
        }

        // Handle legal documents
        $currentFiles = is_string($employee->legal_documents)
            ? json_decode($employee->legal_documents, true) ?: []
            : (is_array($employee->legal_documents) ? $employee->legal_documents : []);

        $remainingFiles = is_string($request->input('existing_files'))
            ? json_decode($request->input('existing_files', '[]'), true) ?: []
            : (is_array($request->input('existing_files')) ? $request->input('existing_files') : []);

        $newFiles = [];
        if ($request->hasFile('legal_documents')) {
            foreach ($request->file('legal_documents') as $file) {
                $filePath = $file->storeAs(
                    'legal-documents',
                    time() . '_' . $file->getClientOriginalName(),
                    'public'
                );
                $newFiles[] = $filePath;
            }
        }

        $employee->legal_documents = !empty($newFiles) ? json_encode(array_values(array_unique(array_merge($remainingFiles, $newFiles)))) : null;

        // Update education
        $education->update([
            'degree' => $validated['degree'],
            'institution' => $validated['institution'],
            'graduation_year' => $validated['graduation_year'],
            'work_experience_years' => $validated['work_experience_years'],
            'work_experience_role' => $validated['work_experience_role'],
            'work_experience_company' => $validated['work_experience_company'],
            'course_name' => $validated['course_name'],
            'training_provider' => $validated['training_provider'],
            'completion_date' => $validated['completion_date'],
            'certification_status' => $validated['certification_status'] ?? null,
        ]);

        // Find department
        $department = Department::where('name', $validated['name'])
            ->where('branch', $validated['branch'])
            ->first();

        if (!$department) {
            return redirect()->back()->withErrors(['department_id' => 'Invalid department details provided.']);
        }

        // Update employee
        $employee->update([
            'full_name' => $validated['full_name'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'date_of_birth' => $validated['date_of_birth'],
            'age' => !empty($validated['date_of_birth']) ? Carbon::parse($validated['date_of_birth'])->age : null,
            'nic' => $validated['nic'],
            'gender' => $validated['gender'],
            'title' => $validated['title'],
            'employment_type' => $validated['employment_type'],
            'employee_id' => $validated['employee_id'],
            'description' => $validated['description'],
            'probation_start_date' => $validated['probation_start_date'],
            'probation_period' => $validated['probation_period'],
            'department_id' => $department->id,
            'manager_id' => $validated['manager_id'],
            'education_id' => $education->id,
            'employment_start_date' => $validated['employment_start_date'],
            'employment_end_date' => $validated['employment_end_date'],
            'status' => $validated['status'],
        ]);

        // Update bank details
        $this->updateBankDetails($employee, $validated);

        return redirect()->route('employee.show', $id)->with('success', 'Employee updated successfully!');
    }

    /**
     * Create a new employee
     */
    public function store(Request $request)
    {
        $isFirstEmployee = Employee::count() === 0;

        $validated = $request->validate([
            'full_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            // 'first_name' => 'nullable|string|max:255|regex:/^[a-zA-Z\s]+$/',
            // 'last_name' => 'nullable|string|max:255|regex:/^[a-zA-Z\s]+$/',
            // 'email' => 'nullable|email|unique:employees,email',
            // 'phone' => 'nullable|string|max:15',
            // 'address' => 'nullable|string',
            // 'date_of_birth' => 'nullable|date',
            // 'nic' => 'nullable|string',
            // 'gender' => 'nullable|string',
            'title' => 'nullable|string|regex:/^[a-zA-Z\s]+$/',
            // 'employment_type' => 'nullable|string|regex:/^[a-zA-Z\s]+$/',
            // 'image' => 'nullable|mimes:jpg,jpeg,png,bmp,tiff|max:4096',
            // 'employee_id' => 'nullable|string|max:255',
            // 'description' => 'nullable|string|max:255',
            // 'branch' => 'nullable|string',
            // 'name' => 'nullable|string',
            // 'probation_start_date' => 'nullable|date',
            // 'probation_period' => 'nullable|integer',
            // 'department_id' => 'nullable|exists:departments,id',
            // 'manager_id' => 'required_if:isFirstEmployee,false|exists:employees,id',
            // 'education_id' => 'nullable|exists:education,id',
            // 'employment_start_date' => 'nullable|date',
            // 'employment_end_date' => 'nullable|date',
            // 'status' => 'nullable|string|max:255',
            // 'legal_documents' => 'nullable|array',
            'account_holder_name' => 'required|string|regex:/^[a-zA-Z\s]+$/',
            'bank_name' => 'required|string|regex:/^[a-zA-Z\s]+$/',
            'bank_code' => 'nullable|string',
            'account_number' => 'required|integer',
            'branch_name' => 'nullable|string',
            'branch_code' => 'nullable|string',
            // 'degree' => 'nullable|string|max:255',
            // 'institution' => 'nullable|string|max:255',
            // 'graduation_year' => 'nullable|integer',
            // 'work_experience_years' => 'nullable|integer|min:1|max:365',
            // 'work_experience_role' => 'nullable|string|max:255',
            // 'work_experience_company' => 'nullable|string|max:255',
            // 'course_name' => 'nullable|string|max:255',
            // 'training_provider' => 'nullable|string|max:255',
            // 'completion_date' => 'nullable|date',
            // 'certification_status' => 'nullable|string|max:255',
        ]);

        // Handle image
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->storeAs(
                'images',
                time() . '_' . $image->getClientOriginalName(),
                'public'
            );
        }

        // Handle legal documents
        $uploadedFiles = [];
        if ($request->hasFile('legal_documents')) {
            foreach ($request->file('legal_documents') as $file) {
                $filePath = $file->storeAs(
                    'legal-documents',
                    time() . '_' . $file->getClientOriginalName(),
                    'public'
                );
                $uploadedFiles[] = $filePath;
            }
        }

        // Create education
        // $education = Education::create([
        //     'degree' => $validated['degree'],
        //     'institution' => $validated['institution'],
        //     'graduation_year' => $validated['graduation_year'],
        //     'work_experience_years' => $validated['work_experience_years'],
        //     'work_experience_role' => $validated['work_experience_role'],
        //     'work_experience_company' => $validated['work_experience_company'],
        //     'course_name' => $validated['course_name'],
        //     'training_provider' => $validated['training_provider'],
        //     'completion_date' => $validated['completion_date'],
        //     'certification_status' => $validated['certification_status'] ?? null,
        // ]);

        // Find department
        $department = Department::first(); // or set a default department if you want

        if (!$department) {
            return redirect()->back()->withErrors(['department_id' => 'No departments found.']);
        }

        // Create employee
        $employee = Employee::create([
            'full_name' => $validated['full_name'],
            // 'first_name' => $validated['first_name'],
            // 'last_name' => $validated['last_name'],
            // 'email' => $validated['email'],
            // 'phone' => $validated['phone'],
            // 'address' => $validated['address'],
            // 'date_of_birth' => $validated['date_of_birth'],
            // 'age' => !empty($validated['date_of_birth']) ? Carbon::parse($validated['date_of_birth'])->age : null,
            // 'nic' => $validated['nic'],
            // 'gender' => $validated['gender'],
            'title' => $validated['title'],
            'account_holder_name' => $validated['full_name'],
            'bank_name' => $validated['bank_name'],
            'account_no' =>$validated['account_number'],
            'branch_name' => $validated['branch_name'],
            // 'employment_type' => $validated['employment_type'],
            // 'employee_id' => $validated['employee_id'],
            // 'description' => $validated['description'],
            // 'probation_start_date' => $validated['probation_start_date'],
            // 'probation_period' => $validated['probation_period'],
            // 'department_id' => $department->id,
            // 'manager_id' => $isFirstEmployee ? null : $validated['manager_id'],
            // 'education_id' => $education->id,
            // 'employment_start_date' => $validated['employment_start_date'],
            // 'employment_end_date' => $validated['employment_end_date'],
            // 'status' => $validated['status'],
            // 'image' => $imagePath,
            // 'legal_documents' => !empty($uploadedFiles) ? json_encode($uploadedFiles) : null,
        ]);

        // Update bank details
        $this->updateBankDetails($employee, $validated);

        return redirect()->route('employee.management')->with('success', 'Employee added successfully.');
    }

    /**
     * Update or create bank details for an employee
     */
    protected function updateBankDetails(Employee $employee, array $data)
    {
        $bank_Details = BankDetails::firstOrNew(['employee_id' => $employee->id]);

        $bank_Details->account_holder_name = $data['account_holder_name'];
        $bank_Details->company_ref = "=";
        $bank_Details->bank_name = $data['bank_name'];
        $bank_Details->bank_code = $data['bank_code'];
        $bank_Details->branch = $data['branch_name'];
        $bank_Details->account_number = $data['account_number'];
        $bank_Details->branch_code = $data['branch_code'];

        $bank_Details->save();

        return $bank_Details;
    }

    /**
     * Delete employee
     */
    public function delete($id)
    {
        $employee = Employee::findOrFail($id);
        if ($employee->image) Storage::disk('public')->delete($employee->image);
        if ($employee->legal_documents) {
            $files = json_decode($employee->legal_documents, true);
            foreach ($files as $file) Storage::disk('public')->delete($file);
        }
        $employee->delete();
        return response()->json(['message' => 'Employee deleted successfully']);
    }

    /**
     * Search employees
     */
    public function GetSearchEmployees(Request $request)
    {
        $search = $request->input('search');

        $query = Employee::with(['department']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhereHas('department', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $employees = $query->paginate(8);
        return view('management.employee.employee-management', compact('search', 'employees'));
    }

    /**
     * Show create employee form
     */
    public function create()
    {
        $isFirstEmployee = Employee::count() === 0;
        $departments = Department::all();
        $employees = Employee::all();
        $education = Education::all();

        return view('management.employee.create-employee', compact('departments', 'employees', 'education', 'isFirstEmployee'));
    }

public function getEmployeeDetails($id)
{
    // Load employee with salary details and department info
    $employee = Employee::with(['salaryDetails'])->find($id);


    if (!$employee) {
        return response()->json(['error' => 'Employee not found'], 404);
    }

    // Prepare the data to send to the front-end
    $data = [
        'id' => $employee->id,
        'full_name' => $employee->full_name ?? 'N/A',        
        'basic' => $employee->salaryDetails->basic ?? 'N/A',
        'budget_allowance' => $employee->salaryDetails->budget_allowance ?? 'N/A',
        'gross_salary' => $employee->salaryDetails->gross_salary ?? 'N/A',
        'transport_allowance' => $employee->salaryDetails->transport_allowance ?? 'N/A',
        'attendance_allowance' => $employee->salaryDetails->attendance_allowance ?? 'N/A',
        'phone_allowance' => $employee->salaryDetails->phone_allowance ?? 'N/A',
        'car_allowance' => $employee->salaryDetails->car_allowance ?? 'N/A',
        'gross_salary' => $employee->salaryDetails->gross_salary ?? 'N/A',
    ];

    return response()->json($data);
}

public function getSalaryDetails($id)
{
    $salary = SalaryDetails::where('employee_id', $id)->first();

    if ($salary) {
        return response()->json($salary);
    } else {
        return response()->json(['error' => 'Salary details not found'], 404);
    }
}

}
