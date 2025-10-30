<?php

namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Leave;
use App\Models\Deduction;
use App\Models\Allowance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SalaryDetails;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Services\OvertimeCalculator;
use App\Services\LeaveBalanceService;


class PayrollController extends Controller
{
    private OvertimeCalculator $overtimeCalculator;
    private LeaveBalanceService $leaveBalanceService;

    public function __construct(OvertimeCalculator $overtimeCalculator, LeaveBalanceService $leaveBalanceService)
    {
        $this->overtimeCalculator = $overtimeCalculator;
        $this->leaveBalanceService = $leaveBalanceService;
    }

    public function create()
    {
      $employees = Employee::all(); // fetch all employees
    return view('management.payroll.payroll-create', compact('employees'));
    }

 public function store(Request $request)
    {
        // Validate the input data
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'employee_name' => 'required|string|max:255|regex:/^[a-zA-Z\s\.]+$/',
            'known_name' => 'nullable|string|max:255',
            'epf_no' => 'nullable|string|max:255',
            'pay_date' => 'nullable|date',
            'payed_month' => 'required|string|max:255',
            'basic' => 'required|numeric|min:0',
            'budget_allowance' => 'nullable|numeric|min:0',
            'gross_salary' => 'required|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'attendance_allowance' => 'nullable|numeric|min:0',
            'phone_allowance' => 'nullable|numeric|min:0',
            'production_bonus' => 'nullable|numeric|min:0',
            'car_allowance' => 'nullable|numeric|min:0',
            'loan_payment' => 'nullable|numeric|min:0',
            'stamp_duty' => 'nullable|numeric|min:0',
            'no_pay' => 'nullable|numeric|min:0',
            'advance_payment' => 'nullable|numeric|min:0',
            'ot_payment' => 'nullable|numeric',
            'epf_8_percent' => 'nullable|numeric',
            'total_deductions' => 'nullable|numeric',
            'total_earnings' => 'nullable|numeric',
            'net_salary' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $employeeId = (int) $request->employee_id;
            $month = trim((string) $request->payed_month);
            $employee = Employee::find($employeeId);

            $payDate = $request->pay_date
                ? Carbon::parse($request->pay_date)
                : Carbon::parse($month . '-01')->endOfMonth();

            $employeeName = trim((string) $request->employee_name);
            if ($employeeName === '' && $employee) {
                $employeeName = trim((string) ($employee->full_name
                    ?? trim(($employee->first_name ? $employee->first_name . ' ' : '') . ($employee->last_name ?? ''))));
            }
            if ($employeeName === '') {
                $employeeName = 'N/A';
            }

            $knownNameInput = trim((string) $request->input('known_name', ''));
            $knownName = $knownNameInput !== ''
                ? $knownNameInput
                : ($employee?->known_name
                    ?? $employeeName
                    ?? 'N/A');
            if ($knownName === null || $knownName === '') {
                $knownName = $employeeName !== '' ? $employeeName : 'N/A';
            }

            $epfNoInput = trim((string) $request->input('epf_no', ''));
            $epfNo = $epfNoInput !== ''
                ? $epfNoInput
                : ($employee?->epf_no ?? 'N/A');
            if ($epfNo === null || $epfNo === '') {
                $epfNo = 'N/A';
            }

            $budgetAllowance = (float) ($request->budget_allowance ?? ($employee?->budget_allowance ?? 0));

            // ✅ Check if a record already exists for this employee in this month
            $existingRecord = SalaryDetails::where('employee_id', $employeeId)
                ->where('payed_month', $month)
                ->first();

            // Basic calculations
            $grossSalary = (float) $request->gross_salary;

            // ============== NEW: AUTO-CALCULATE OT, LOANS, AND ADVANCES ==============
            $calculatedData = $this->calculatePayrollData($employeeId, $month, $grossSalary);
            
            // Use calculated values or manual input (manual takes priority)
            $otPayment = $request->ot_payment ?? $calculatedData['ot_payment'];
            $loanPayment = $request->loan_payment ?? $calculatedData['loan_payment'];
            $advancePayment = $request->advance_payment ?? $calculatedData['advance_payment'];
            $leaveNoPayAmount = $calculatedData['leave_no_pay'];
            $loanBalance = $calculatedData['loan_balance'];
            $advanceBalance = $calculatedData['advance_balance'];

            $noPayDeductions = (($request->no_pay ?? 0) * 1000) + $leaveNoPayAmount;

            // ✅ Use Rs.25 if stamp duty is empty or 0
            $stampDuty = ($request->stamp_duty && $request->stamp_duty > 0)
                ? $request->stamp_duty
                : 25;

            // ✅ Calculate EPF 8%
            $epf8Percent = $request->epf_8_percent ?? round($grossSalary * 0.08, 2);

            // ✅ Calculate total deductions
            $totalDeductions = (
                $epf8Percent +
                $advancePayment +
                $loanPayment +
                $stampDuty +
                $noPayDeductions
            );

            // ✅ Calculate total earnings
            $totalEarnings = (
                $grossSalary +
                ($request->transport_allowance ?? 0) +
                ($request->attendance_allowance ?? 0) +
                ($request->phone_allowance ?? 0) +
                ($request->car_allowance ?? 0) +
                ($request->production_bonus ?? 0) +
                $otPayment
            );

            // ✅ Calculate net salary
            $netSalary = $totalEarnings - $totalDeductions;

            // ✅ EPF & ETF
            $epf12Percent = round($grossSalary * 0.12, 2);
            $etf3Percent = round($grossSalary * 0.03, 2);

            $recordData = [
                'employee_name' => $employeeName,
                'known_name' => $knownName,
                'epf_no' => $epfNo,
                'pay_date' => $payDate->toDateString(),
                'payed_month' => $month,
                'basic' => $request->basic,
                'budget_allowance' => $budgetAllowance,
                'gross_salary' => $grossSalary,
                'transport_allowance' => $request->transport_allowance,
                'attendance_allowance' => $request->attendance_allowance,
                'phone_allowance' => $request->phone_allowance,
                'production_bonus' => $request->production_bonus,
                'car_allowance' => $request->car_allowance,
                'loan_payment' => $loanPayment,
                'advance_payment' => $advancePayment,
                'ot_payment' => $otPayment,
                'stamp_duty' => $stampDuty,
                'no_pay' => ($request->no_pay ?? 0) + ($leaveNoPayAmount / 1000), // Convert back to days
                'total_deductions' => $totalDeductions,
                'total_earnings' => $totalEarnings,
                'net_salary' => $netSalary,
                'epf_8_percent' => $epf8Percent,
                'epf_12_percent' => $epf12Percent,
                'etf_3_percent' => $etf3Percent,
                'loan_balance' => $loanBalance,
                'advance_balance' => $advanceBalance,
            ];

            // ✅ Insert or Update record
            if ($existingRecord) {
                $existingRecord->update($recordData);
            } else {
                $recordData['employee_id'] = $employeeId;
                SalaryDetails::create($recordData);
            }

            // Update loan balances in the database
            if (!empty($calculatedData['updated_loan_balances'])) {
                foreach ($calculatedData['updated_loan_balances'] as $loanId => $newBalance) {
                    DB::table('loans')
                        ->where('id', $loanId)
                        ->update([
                            'remaining_balance' => $newBalance,
                            'updated_at' => now()
                        ]);

                    // Mark loan as completed if balance reaches zero
                    if ($newBalance == 0) {
                        DB::table('loans')
                            ->where('id', $loanId)
                            ->update([
                                'loan_end_date' => now()
                            ]);
                    }
                }
            }

            return redirect()->route('payroll.management')
                ->with('success', 'Payroll record saved successfully with auto-calculated OT, loans, and advances.');
        } catch (\Exception $e) {
            Log::error('Payroll save error', [
                'employee_id' => $request->employee_id,
                'payed_month' => $request->payed_month,
                'message' => $e->getMessage(),
            ]);
            return redirect()->back()
                ->with('error', 'Failed to save the payroll record.')
                ->withInput();
        }
    }

    private function calculatePayrollData($employeeId, $selectedMonth, $grossSalary)
    {
        // Date range calculation (5th to 5th of next month)
        $startDate = date('Y-m-05', strtotime($selectedMonth));
        $endDate = date('Y-m-05', strtotime('+1 month', strtotime($selectedMonth)));
        
        // Calculate leave-based no-pay deductions
        $leaveNoPayAmount = Leave::where('employee_id', $employeeId)
            ->where('is_no_pay', true)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->sum('no_pay_amount');

        // ========== Get approved loans for this employee ==========
        $approvedLoans = DB::table('loans')
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->get();

        // Calculate total monthly loan payment
        $totalMonthlyLoanPayment = 0;
        $newLoanBalances = [];

        foreach ($approvedLoans as $loan) {
            // Calculate monthly payment for each active loan
            if ($loan->remaining_balance > 0) {
                $monthlyPayment = $loan->monthly_paid;
                
                // If remaining balance is less than monthly payment, pay only the remaining
                $actualPayment = min($monthlyPayment, $loan->remaining_balance);
                $totalMonthlyLoanPayment += $actualPayment;
                
                // Update remaining balance for this loan
                $newLoanBalances[$loan->id] = max(0, $loan->remaining_balance - $actualPayment);
            }
        }

        // ========== Get approved advances for this employee ==========
        $approvedAdvances = DB::table('advances')
            ->where('employment_ID', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('advance_date', [$startDate, $endDate])
            ->get();

        // Calculate total advance amount for this period
        $advancePayment = $approvedAdvances->sum('advance_amount');
        
        // Get current advance balance from latest salary record
        $latestSalary = SalaryDetails::where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Calculate new advance balance
        $currentAdvanceBalance = $latestSalary->advance_balance ?? 0;
        $newAdvanceBalance = max(0, $currentAdvanceBalance + $advancePayment);

        $periodStart = Carbon::parse($startDate);
        $periodEnd = Carbon::parse($endDate);

        $employee = Employee::find($employeeId);

        $overtimeResult = $this->overtimeCalculator->calculate($employee, $periodStart, $periodEnd);

        $otRate = 0.0041667327;
        $regularOTHours = $overtimeResult['regular_seconds'] / 3600;
        $sundayOTHours = $overtimeResult['sunday_seconds'] / 3600;

        $otPayment = ($regularOTHours * (($grossSalary / 240) * 1.5)) +
                     ($sundayOTHours * ($grossSalary * 1.5 * $otRate * 2));

        return [
            'ot_payment' => round($otPayment, 2),
            'loan_payment' => $totalMonthlyLoanPayment,
            'advance_payment' => $advancePayment,
            'leave_no_pay' => $leaveNoPayAmount,
            'loan_balance' => array_sum($newLoanBalances),
            'advance_balance' => $newAdvanceBalance,
            'updated_loan_balances' => $newLoanBalances,
            'overtime_breakdown' => $overtimeResult,
        ];
    }


    public function update(Request $request, $id)
    {
        //dd($request->all());
        // Validate the input fields
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'employee_name' => 'required|string|max:255',
            'pay_date' => 'required|date',
            'payed_month' => 'required|string|max:255',
            'basic' => 'required|numeric|min:0',
            'budget_allowance' => 'nullable|numeric|min:0',
            'advance_payment' => 'nullable|numeric|min:0',
            'ot_payment' => 'nullable|numeric|min:0',
            'loan_payment' => 'nullable|numeric|min:0',
            'stamp_duty' => 'nullable|numeric|min:0',
            'no_pay' => 'nullable|numeric|min:0',
        ]);

        // Find the record by ID
        $salaryDetail = SalaryDetails::findOrFail($id);

        // Update the record with validated data
        $salaryDetail->update([
            'employee_id' => $validated['employee_id'],
            'employee_name' => $validated['employee_name'],
            'pay_date' => $validated['pay_date'],
            'payed_month' => $validated['payed_month'],
            'basic' => $validated['basic'],
            'budget_allowance' => $validated['budget_allowance'],
            'advance_payment' => $validated['advance_payment'],
            'ot_payment' => $validated['ot_payment'],
            'loan_payment' => $validated['loan_payment'],
            'stamp_duty' => $validated['stamp_duty'],
            'no_pay' => $validated['no_pay'],
        ]);


         // Re-fetch the updated details
    $salaryDetail = SalaryDetails::findOrFail($id);

    // Recalculate total earnings
    $totalEarnings = $salaryDetail->basic + $salaryDetail->budget_allowance + 
                      ($salaryDetail->transport_allowance ?? 0) + 
                      ($salaryDetail->attendance_allowance ?? 0) + 
                      ($salaryDetail->phone_allowance ?? 0) + 
                      ($salaryDetail->production_bonus ?? 0) + 
                      ($salaryDetail->car_allowance ?? 0) + 
                      ($salaryDetail->ot_payment ?? 0);

    // Recalculate total deductions
    $totalDeductions = ($salaryDetail->advance_payment ?? 0) + 
                       ($salaryDetail->loan_payment ?? 0) + 
                       ($salaryDetail->stamp_duty ?? 0) + 
                       ($salaryDetail->no_pay ?? 0) + 
                       ($salaryDetail->epf_8_percent ?? 0);

    // Calculate net salary
    $netSalary = $totalEarnings - $totalDeductions;

    // Update total earnings, total deductions, and net salary
    $salaryDetail->update([
        'total_earnings' => $totalEarnings,
        'total_deductions' => $totalDeductions,
        'net_salary' => $netSalary,
    ]);
        // Redirect with success message
        return redirect()->route('payroll.management')->with('success', 'Payroll record saved successfully.');
    }

    public function edit($id)
    {
        // Find the record by ID
        $record = SalaryDetails::findOrFail($id);

        // Return the edit form view with the record
        return view('management.payroll.payroll-edit', compact('record'));
    }

    // Display the card view for payroll records
    public function index()
    {
        // Fetch payroll data with employee relationships
        $payrolls = SalaryDetails::with('employee')->get();

        return view('payroll.index', compact('payrolls'));
    }

    public function updateAdvanceAndLoan(Request $request, $id)
    {
        // Validate input
        $request->validate([
            'advance_payment' => 'nullable|numeric|min:0',
            'loan_payment' => 'nullable|numeric|min:0',
        ]);

        // Update advance and loan amounts
        $record = SalaryDetails::findOrFail($id);
        $record->advance_payment = $request->input('advance_payment', 0);
        $record->loan_payment = $request->input('loan_payment', 0);
        $record->save();

        return response()->json(['success' => true, 'message' => 'Record updated successfully']);
    }

    public function viewPaysheet($id)
    {
        $record = SalaryDetails::with('employee')->findOrFail($id);

        // Generate PDF view for paysheet
        $pdf = PDF::loadView('management.payroll.paysheet', compact('record'));

        return $pdf->download('Paysheet-' . $record->employee_name . '.pdf');
    }

    public function downloadAllPaysheets($month)
    {
        $records = SalaryDetails::where('payroll_month', $month)->get();

        // Create a merged PDF
        $pdf = PDF::loadView('payroll.all-paysheets', compact('records'));

        return $pdf->download('All-Paysheets-' . $month . '.pdf');
    } 

public function destroy($id)
{
    $payroll = Payroll::findOrFail($id);
    $payroll->delete();

    return redirect()->route('dashboard.payroll')->with('success', 'Payroll record deleted successfully!');
}

    public function getSalaryDetails($id)
    {
        $employeeRecord = Employee::find($id);

        if ($employeeRecord === null) {
            return response()->json(['error' => 'No data found'], 404);
        }

        $latestSalary = SalaryDetails::where('employee_id', $id)
            ->orderByDesc('pay_date')
            ->orderByDesc('created_at')
            ->first();

        $basic = (float) ($employeeRecord->basic ?? 0);
        $budgetAllowance = (float) ($employeeRecord->budget_allowance ?? 0);

        return response()->json([
            'employee_name' => $employeeRecord->full_name,
            'gross_salary' => round($basic + $budgetAllowance, 2),
            'transport_allowance' => (float) ($employeeRecord->transport_allowance ?? 0),
            'attendance_allowance' => (float) ($employeeRecord->attendance_allowance ?? 0),
            'phone_allowance' => (float) ($employeeRecord->phone_allowance ?? 0),
            'car_allowance' => (float) ($employeeRecord->car_allowance ?? 0),
            'production_bonus' => (float) ($employeeRecord->production_bonus ?? 0),
            'basic' => $basic,
            'budget_allowance' => $budgetAllowance,
            'stamp_duty' => (float) ($employeeRecord->stamp_duty ?? 25.00),
            'epf_no' => $employeeRecord->epf_no,
            'advance_payment' => (float) ($latestSalary->advance_payment ?? 0),
            'loan_payment' => (float) ($latestSalary->loan_payment ?? 0),
            'ot_payment' => (float) ($latestSalary->ot_payment ?? 0),
        ]);
    }
public function getNoPayLeave($id,$month)
{
    $startDate = date('Y-m-05', strtotime($month));
    $endDate = date('Y-m-05', strtotime('+1 month', strtotime($month)));
    
    // Calculate leave-based no-pay deductions using annual balance
    // This considers the full annual leave (21 days) and short leave (36) balance
    $leaveNoPayAmount = $this->leaveBalanceService->calculateNoPayForPeriod(
        $id,
        $startDate,
        $endDate
    );

    // Also return leave balance information
    $balances = $this->leaveBalanceService->getLeaveBalances($id);

    return response()->json([
        'no_pay_amount' => $leaveNoPayAmount,
        'leave_balances' => $balances
    ]);

}
}

