<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Leave;
use App\Models\Loan;
use App\Models\Todo;
use App\Models\Department;
use App\Models\ExpenseClaim;
use App\Models\Attendance;
use App\Models\Incident;
use App\Models\SalaryDetails;
use Illuminate\Support\Facades\DB;

class ManagementController extends Controller
{

    public function employeeManagement()
    {
        $employees = Employee::with(['department'])->paginate(8);
        $section = "employee";
    
        return view('management.employee.employee-management', compact('section', 'employees'));

    }
    public function payrollManagement()
    {
      

    $payrolls = SalaryDetails::with('employee')->get();

    return view('management.payroll.payroll-management', compact('payrolls'));
    }

    public function leaveManagement()
    {
        $leaves = Leave::with('employee')->get();
        
        // Get all employees with their leave balances
        $employees = Employee::all()->map(function ($employee) {
            $balances = $employee->getLeaveBalances();
            return [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'employee_id' => $employee->employee_id,
                'annual_used' => $employee->annual_leave_used,
                'annual_balance' => $employee->annual_leave_balance,
                'annual_remaining' => $balances['annual_leaves_remaining'],
                'short_used' => $employee->short_leave_used,
                'short_balance' => $employee->short_leave_balance,
                'short_remaining' => $balances['short_leaves_remaining'],
                'monthly_leaves_used' => $employee->monthly_leaves_used,
                'monthly_leaves_remaining' => $balances['monthly_leaves_remaining'],
                'monthly_half_leaves_used' => $employee->monthly_half_leaves_used,
                'monthly_half_leaves_remaining' => $balances['monthly_half_leaves_remaining'],
                'monthly_short_leaves_used' => $employee->monthly_short_leaves_used,
                'monthly_short_leaves_remaining' => $balances['monthly_short_leaves_remaining'],
            ];
        });
        
        return view('management.leave.leave-management', compact('leaves', 'employees'));
    }


    public function departmentManagement(Request $request)
{
    // Modify the query to use pagination
    $departments = DB::table('departments')
        ->leftJoin('employees', 'departments.id', '=', 'employees.department_id')
        ->select(
            'departments.department_id',
            'departments.name',
            DB::raw('COUNT(DISTINCT employees.id) as employees_count'),
            DB::raw('COUNT(DISTINCT departments.branch) as branch_count')
        )
        ->groupBy(
            'departments.department_id',
            'departments.name'
        );

    // Apply search filter if a search term is provided
    if ($search = $request->input('search')) {
        $departments->where('departments.name', 'like', "%{$search}%")
                    ->orWhere('departments.department_id', 'like', "%{$search}%");
    }

    // Apply pagination
    $departments = $departments->paginate(8);

    // Pass the paginated result to the view
    return view('management.department.department-management', compact('departments'));
}
public function settingManagement()
{
    $user = auth()->user();
    return view('management.setting.setting', compact('user'));
}
    public function viewDashboard()
    {
       // $leaves = Leave::all();

       $totalEmployees = Employee::count();
       $totalDepartments = Department::count();
       $totalPayrolls = Payroll::count();
       $totalIncidents = Incident::where('resolution_status', 'pending')->count();
       
       // Get new employees this month
       $newEmployees = Employee::whereMonth('employment_start_date', now()->month)
                             ->whereYear('employment_start_date', now()->year)
                             ->count();
       
       // Get total expenses (sum of all payrolls)
       $totalExpenses = Payroll::sum('payable');
       
       // Get headcount data for line chart
       $headcountData = Employee::selectRaw('DATE_FORMAT(employment_start_date, "%Y-%m") as month, COUNT(*) as count')
                               ->groupBy('month')
                               ->orderBy('month')
                               ->get();
                               
       // Get department distribution for pie chart
       $departmentData = Employee::select('departments.name', DB::raw('COUNT(*) as count'))
                                ->join('departments', 'employees.department_id', '=', 'departments.id')
                                ->groupBy('departments.name')
                                ->get();


        
   // Get salary distribution data - corrected SQL and using payroll table
   $salaryRanges = DB::select("
   SELECT 
       CASE
           WHEN p.payable <= 30000 THEN '0-30k'
           WHEN p.payable <= 50000 THEN '30k-50k'
           WHEN p.payable <= 80000 THEN '50k-80k'
           ELSE '80k+'
       END as salary_range,
       COUNT(*) as count
   FROM payrolls p
   GROUP BY 
       CASE
           WHEN p.payable <= 30000 THEN '0-30k'
           WHEN p.payable <= 50000 THEN '30k-50k'
           WHEN p.payable <= 80000 THEN '50k-80k'
           ELSE '80k+'
       END
");



// Get employee turnover data - using correct date column
$turnoverData = DB::select("
   SELECT 
       DATE_FORMAT(date_column, '%Y-%m') as month,
       SUM(CASE WHEN type = 'hire' THEN 1 ELSE -1 END) as net_change
   FROM (
       SELECT employment_start_date as date_column, 'hire' as type 
       FROM employees
       UNION ALL
       SELECT employment_end_date as date_column, 'termination' as type 
       FROM employees 
       WHERE employment_end_date IS NOT NULL
   ) changes
   WHERE date_column IS NOT NULL
   GROUP BY DATE_FORMAT(date_column, '%Y-%m')
   ORDER BY month
");

// Get attendance statistics for the last 30 days
$attendance = Attendance::selectRaw('status, COUNT(*) as count')
    ->whereDate('date', '>=', now()->subDays(30))
    ->groupBy('status')
    ->get();



// Get todos
$todos = Todo::where('user_id', auth()->id())
    ->orderBy('due_date')
    ->get();
   
       return view('management.main-dashboard', compact(
           'totalEmployees',
           'totalDepartments',
           'totalPayrolls',
           'totalIncidents',
           'newEmployees',
           'totalExpenses',
           'headcountData',
           'departmentData',
           'salaryRanges',
           'attendance',
           'turnoverData',
           'todos'
       ));
   }
       
    


    
    
    public function getDashboardView()
    {
        $data = [
            'totalEmployees' => 150,
            'totalDepartments' => 10,
            'totalExpenses' => 50000,
            'headcountData' => [
                'labels' => ['January', 'February', 'March', 'April'],
                'datasets' => [
                    [
                        'label' => 'Headcount',
                        'data' => [10, 20, 30, 40],
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ],
            'departmentData' => [
                'labels' => ['HR', 'Tech', 'Sales', 'Admin'],
                'datasets' => [
                    [
                        'label' => 'Department Distribution',
                        'data' => [25, 50, 60, 15],
                        'backgroundColor' => [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)'
                        ],
                        'borderColor' => [
                            'rgba(255,99,132,1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        'borderWidth' => 1
                    ]
                ]
            ]
        ];
    
        return response()->json($data);
    }


    public function expenseManagement()
    {
        $expenses = ExpenseClaim::with('employee')->get(); // Fetch paginated data
     //   dd($expenses);
        return view('management.expenses.expenses-management', compact('expenses'));
    }

    
    public function incidentManagement()
    {
        $incidents = Incident::all();
        return view('management.incident.incident-management', compact('incidents'));
    }



    public function attendanceManagement()
    {
        // Fetch attendance records with employee details
        // Note: The Attendance model already has accessors that format durations
        $attendance = Attendance::with(['employee'])
            ->where('date', '>', '2025-05-05')
            ->get();
    
        return view('management.attendance.attendance-management', compact('attendance'));
    }
    

public function advanceManagement()
{
    $advances = Loan::all();
    return view('management.advance.advance-management', compact('advances'));
}


/**
 * Helper function to convert seconds to HH:MM:SS format.
 *
 * @param int|null $seconds
 * @return string|null
 */
private function formatDuration($seconds)
{
    if ($seconds === null) {
        return null;
    }

    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
}

}
