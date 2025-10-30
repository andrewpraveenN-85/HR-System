<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ExpenseClaimsController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EmployeeContributionController;
use App\Http\Controllers\PayrollExportController;
use App\Http\Controllers\AdvanceController;
use App\Http\Controllers\SaturdayRosterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register.form');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::delete('/account/delete', [AuthController::class, 'deleteAccount'])->name('account.delete');
Route::put('/user/update', [AuthController::class, 'update'])->name('user.update');
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
Route::post('/sendresetlink', [AuthController::class, 'handleForgotPassword'])->name('sendresetlink');
Route::get('/password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/update', [AuthController::class, 'updateNewPassword'])->name('password.update');

// API Routes for Leave Management
Route::get('/api/employee-leave-data', [LeaveController::class, 'getEmployeeLeaveData'])->name('api.employee.leave.data');
Route::post('/api/process-auto-short-leave/{attendanceId}', [LeaveController::class, 'processAutoShortLeave'])->name('api.process.auto.short.leave');
Route::get('/resetsuccess', [AuthController::class, 'showResetSuccess'])->name('resetsuccess');
Route::put('/password/update', [AuthController::class, 'updatePassword'])->name('new-password.update');


// API Routes
Route::get('/api/employees', function () {
    return App\Models\Employee::all();
});

// Dashboard Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');

   // Route::get('/dashboard/{section}', [DashboardController::class, 'show'])->name('dashboard.section');

   Route::get('/dashboard/employee/{id}', [EmployeeController::class, 'show'])->name('employee.details');
    Route::get('/employee/{id}', [EmployeeController::class, 'show'])->name('employee.show');
    Route::get('/employee/{id}/edit', [EmployeeController::class, 'edit'])->name('employee.edit');

    Route::put('/employee/update/{id}', [EmployeeController::class, 'update'])->name('employee.update');
   Route::delete('/employee/delete/{id}', [EmployeeController::class, 'delete']);
   Route::get('/searchemployees', [EmployeeController::class, 'GetSearchEmployees'])->name('employees.search');
   Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
   Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
   Route::delete('/employee/{id}', [EmployeeController::class, 'destroy'])->name('employee.destroy');




    Route::get('/employees/hierarchy', [EmployeeController::class, 'hierarchy'])->name('employees.hierarchy');


    Route::get('/employees/{id}/details', [EmployeeController::class, 'getEmployeeDetails'])->name('employees.details');


});
    Route::get('/api/hr-dashboard', [ManagementController::class, 'getDashboardView'])->name('api.hr.dashboard.data');

// Management Routes
Route::prefix('management')->group(function () {
    Route::get('/employee-management', [ManagementController::class, 'employeeManagement'])->name('employee.management');
    Route::get('/payroll-management', [ManagementController::class, 'payrollManagement'])->name('payroll.management');
    Route::get('/leave-management', [ManagementController::class, 'leaveManagement'])->name('leave.management');
    Route::get('/expense-management', [ManagementController::class, 'expenseManagement'])->name('expense.management');
    Route::get('/incident-management', [ManagementController::class, 'incidentManagement'])->name('incident.management');
    Route::get('/advance-management', [ManagementController::class, 'advanceManagement'])->name('advance.management');


    Route::get('/main-dashboard', [ManagementController::class, 'viewDashboard'])->name('dashboard.management');
    Route::get('/count-dashboard', [DashboardController::class, 'getEmployeeCountByDepartment'])->name('count.management');

    Route::get('/attendance-management', [ManagementController::class, 'attendanceManagement'])->name('attendance.management');
    Route::get('/setting-management', [ManagementController::class, 'settingManagement'])->name('setting.management');

    Route::get('/department-management', [ManagementController::class, 'departmentManagement'])->name('department.management');

});

// Payroll Management Routes
Route::middleware('auth')->prefix('dashboard/payroll')->group(function () {
    Route::get('/', [PayrollController::class, 'create'])->name('payroll.create');
    Route::post('/store', [PayrollController::class, 'store'])->name('payroll.store');
    Route::get('/{id}', [PayrollController::class, 'show'])
        ->whereNumber('id')
        ->name('payroll.details');
    Route::get('/{id}/edit', [PayrollController::class, 'edit'])
        ->whereNumber('id')
        ->name('payroll.edit');
    Route::put('/{id}', [PayrollController::class, 'update'])
        ->whereNumber('id')
        ->name('payroll.update');
    Route::delete('/{id}', [PayrollController::class, 'destroy'])
        ->whereNumber('id')
        ->name('payroll.destroy');

    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
Route::post('/payroll/{id}/update-advance-loan', [PayrollController::class, 'updateAdvanceAndLoan'])->name('payroll.update-advance-loan');
Route::get('/payroll/{id}/view-paysheet', [PayrollController::class, 'viewPaysheet'])->name('payroll.view-paysheet');
Route::get('/payroll/download-all/{month}', [PayrollController::class, 'downloadAllPaysheets'])->name('payroll.download-all');

Route::get('/payroll/export/spreadsheet', [PayrollExportController::class, 'exportSalarySpreadsheet'])->name('payroll.export.spreadsheet');

Route::get('/payroll/export-bank-details', [PayrollExportController::class, 'export'])->name('bank.details.export');


Route::get('/payroll/export/paysheets', [PayrollExportController::class, 'downloadPaysheets'])->name('payroll.export.paysheets');

Route::get('/payroll/generate/paysheets', [PayrollExportController::class, 'generatePreviousMonth'])->name('payroll.generate.paysheets');
Route::get('/payroll/export/pdf', [PayrollExportController::class, 'exportSalaryPDF'])->name('payroll.export.pdf');

Route::get('/saturday-roster', [SaturdayRosterController::class, 'index'])->name('payroll.saturday-roster.index');
Route::post('/saturday-roster', [SaturdayRosterController::class, 'store'])->name('payroll.saturday-roster.store');
Route::get('/saturday-roster/history', [SaturdayRosterController::class, 'history'])->name('payroll.saturday-roster.history');

});

// Expense Management Routes
Route::middleware('auth')->prefix('dashboard/expenses')->group(function () {
    Route::get('/create', [ExpenseClaimsController::class, 'create'])->name('expenses.create');
    Route::post('/store', [ExpenseClaimsController::class, 'store'])->name('expenses.store');
    Route::get('/{id}', [ExpenseClaimsController::class, 'show'])->name('expenses.details');
    Route::get('/{id}/edit', [ExpenseClaimsController::class, 'edit'])->name('expenses.edit');
    Route::put('/{id}', [ExpenseClaimsController::class, 'update'])->name('expenses.update');
    Route::delete('/{id}', [ExpenseClaimsController::class, 'destroy'])->name('expenses.destroy');
});

// Leave Management Routes
Route::middleware('auth')->prefix('dashboard/leaves')->group(function () {
    Route::get('leave/create', [LeaveController::class, 'create'])->name('leave.create');
    Route::post('/store', [LeaveController::class, 'store'])->name('leave.store');
    Route::get('/{id}', [LeaveController::class, 'show'])->name('leave.details');
    Route::get('/{id}/edit', [LeaveController::class, 'edit'])->name('leave.edit');
    Route::put('/leave/{id}', [LeaveController::class, 'update'])->name('leave.update');
    Route::delete('/leave/{id}', [LeaveController::class, 'destroy'])->name('leave.destroy');
    Route::get('/{leave}/files', [LeaveController::class, 'getLeaveFiles']);

});
// Attendance Management Routes
Route::middleware('auth')->prefix('dashboard/attendance')->group(function () {
    Route::get('/create', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/store', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/storemanual', [AttendanceController::class, 'storemanual'])->name('attendance.storemanual');
    Route::get('/{id}', [AttendanceController::class, 'show'])->name('attendance.details');
    Route::get('/{id}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::put('/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::delete('/{id}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
});


// Attendance Management Routes
Route::middleware('auth')->prefix('dashboard/incident')->group(function () {
    Route::get('/create', [IncidentController::class, 'create'])->name('incident.create');
    Route::post('/store', [IncidentController::class, 'store'])->name('incident.store');
    Route::get('/{id}', [IncidentController::class, 'show'])->name('incident.details');
    Route::get('/{id}/edit', [IncidentController::class, 'edit'])->name('incident.edit');
    Route::put('/{id}', [IncidentController::class, 'update'])->name('incident.update');
    Route::delete('/{id}', [IncidentController::class, 'destroy'])->name('incident.destroy');
});



// Loan Management Routes
Route::middleware('auth')->prefix('dashboard/advances')->group(function () {
    Route::get('/advance/create', [LoanController::class, 'create'])->name('advance.create');
    Route::post('/advance/store', [LoanController::class, 'store'])->name('advance.store');
    Route::get('/advance/{id}/edit', [LoanController::class, 'edit'])->name('advance.edit');
    Route::put('/advance/{id}', [LoanController::class, 'update'])->name('advance.update');
    Route::delete('/advance/{id}', [LoanController::class, 'destroy'])->name('advance.destroy');

});

//Advance Management Routes
Route::prefix('dashboard')->middleware(['auth'])->group(function () {
    Route::get('/newadvance', [AdvanceController::class, 'index'])->name('newadvance.manage');
    Route::get('/newadvance/create', [AdvanceController::class, 'create'])->name('newadvance.create');
    Route::post('/newadvance', [AdvanceController::class, 'store'])->name('newadvance.store');
    Route::get('/newadvance/{id}', [AdvanceController::class, 'show'])->name('newadvance.show');
    Route::get('/newadvance/{id}/edit', [AdvanceController::class, 'edit'])->name('newadvance.edit');
    Route::put('/newadvance/{id}', [AdvanceController::class, 'update'])->name('newadvance.update');
    Route::delete('/newadvance/{id}', [AdvanceController::class, 'destroy'])->name('newadvance.destroy');
});

Route::post('/notify', [NotificationController::class, 'notify'])->name('notify');

Route::post('/todos', [DashboardController::class, 'storeTodo'])->name('todos.store');
Route::patch('/todos/{todo}', [DashboardController::class, 'updateTodoStatus'])->name('todos.update.status');

Route::middleware('auth')->prefix('dashboard/departments')->group(function () {
    Route::get('/department/create', [DepartmentController::class, 'create'])->name('department.create');
    Route::post('/department/store', [DepartmentController::class, 'store'])->name('department.store');
    Route::get('/department/{department_id}', [DepartmentController::class, 'show'])->name('department.show');
    Route::delete('/department/branch/{branch}/{department_id}', [DepartmentController::class, 'deleteBranch'])
    ->name('department.branch.delete');
    Route::get('/searchdepartment', [DepartmentController::class, 'GetSearchDepartment'])->name('department.search');

});


Route::middleware('auth')->prefix('dashboard/contributions')->group(function () {
    Route::get('/', [EmployeeContributionController::class, 'index'])->name('employee_contributions.index');
    Route::get('/create', [EmployeeContributionController::class, 'create'])->name('employee_contributions.create');
    Route::post('/store', [EmployeeContributionController::class, 'store'])->name('contribution.store');
    Route::delete('/{id}', [EmployeeContributionController::class, 'destroy'])->name('contribution.destroy');
    Route::get('/{id}/edit', [EmployeeContributionController::class, 'edit'])->name('contribution.edit');

    Route::get('/contributions/{employeeId}', [EmployeeContributionController::class, 'getContributions']);

    Route::post('/store-or-update/{id}', [EmployeeContributionController::class, 'storeOrUpdate'])->name('employee_contributions.store_or_update');
});
Route::get('/employees/{id}/salary-details', [App\Http\Controllers\EmployeeController::class, 'getSalaryDetails']);
// Route::get('/employees/{id}/no-pay/{month}', [App\Http\Controllers\PayrollController::class, 'getNoPayLeave']);

Route::get('/get-loan/{employeeId}', [LoanController::class, 'getEmployeeLoan']);

Route::get('/calculate-no-pay', [LeaveController::class, 'calculateMonthlyNoPay'])->name('calculate.no.pay');


// Debug route for Saturday OT calculation
Route::get('/debug/saturday-ot/{employeeId}/{month}', function ($employeeId, $month) {
    $employee = \App\Models\Employee::with('department')->findOrFail($employeeId);
    $startDate = date('Y-m-05', strtotime($month));
    $endDate = date('Y-m-05', strtotime('+1 month', strtotime($month)));
    
    $overtimeCalculator = app(\App\Services\OvertimeCalculator::class);
    $result = $overtimeCalculator->calculate($employee, \Carbon\Carbon::parse($startDate), \Carbon\Carbon::parse($endDate));
    
    return response()->json([
        'employee' => $employee->full_name,
        'department' => $employee->department->name ?? 'N/A',
        'branch' => $employee->department->branch ?? 'N/A',
        'period' => "{$startDate} to {$endDate}",
        'regular_ot_seconds' => $result['regular_seconds'],
        'regular_ot_hours' => $result['regular_seconds'] / 3600,
        'sunday_ot_hours' => $result['sunday_seconds'] / 3600,
        'head_office_summary' => $result['head_office_summary'] ?? [],
        'saturday_assignments' => \App\Models\SaturdayAssignment::where('employee_id', $employeeId)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get(),
        'saturday_attendance' => \App\Models\Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereRaw('DAYOFWEEK(date) = 7') // Saturday
            ->get(),
    ]);
})->middleware('auth');

// Delete payroll records for a specific month (for regeneration)
Route::delete('/payroll/delete-month/{month}', function ($month) {
    try {
        $deleted = \App\Models\SalaryDetails::where('payed_month', $month)->delete();
        \Log::info("Deleted payroll records", ['month' => $month, 'count' => $deleted]);
        return redirect()->back()->with('success', "Deleted {$deleted} payroll records for {$month}. You can now regenerate them.");
    } catch (\Exception $e) {
        \Log::error("Failed to delete payroll records", ['month' => $month, 'error' => $e->getMessage()]);
        return redirect()->back()->with('error', 'Failed to delete payroll records: ' . $e->getMessage());
    }
})->middleware('auth')->name('payroll.delete-month');



