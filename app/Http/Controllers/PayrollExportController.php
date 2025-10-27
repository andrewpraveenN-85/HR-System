<?php
namespace App\Http\Controllers;

use App\Models\SalaryDetails;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Leave;
use Illuminate\Http\Request;
use ZipArchive;
use Carbon\Carbon;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\BankDetailsExport;
use App\Services\OvertimeCalculator;


class PayrollExportController extends Controller
{
    private OvertimeCalculator $overtimeCalculator;

    public function __construct(OvertimeCalculator $overtimeCalculator)
    {
        $this->overtimeCalculator = $overtimeCalculator;
    }

    public function export(Request $request)
    {
        $selectedMonth = $request->query('selected_month');

        if (!$selectedMonth) {
            return back()->withErrors(['error' => 'Please select a month to export.']);
        }
        $payrolls = DB::table('employee_salary_details')
        ->join('bank_details', 'employee_salary_details.employee_id', '=', 'bank_details.employee_id')
        ->where('employee_salary_details.payed_month', '=', $selectedMonth)
        ->select(
            'bank_details.company_ref',
            'bank_details.account_holder_name as beneficiary_name',
            'bank_details.account_number',
            'bank_details.bank_code',
            'bank_details.branch_code',
            'employee_salary_details.net_salary'
        )
        ->get();


  // Export the payroll data to an Excel file
  return Excel::download(new BankDetailsExport($payrolls), 'bank_details.xlsx');
    }
    public function downloadPaysheets(Request $request)
    {

        $selectedMonth = $request->query('selected_month'); // Get the month from the query
        if (!$selectedMonth) {
            return back()->with('error', 'Please select a valid month.');
        }

        $employees = SalaryDetails::where('payed_month', $selectedMonth)->with('employee')->get();

        if ($employees->isEmpty()) {
            return back()->with('error', 'No records found for the selected month.');
        }

        $zipFileName = "employee_paysheets_{$selectedMonth}.zip";
        $zip = new ZipArchive;

        if ($zip->open(public_path($zipFileName), ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            foreach ($employees as $employee) {
                $fileName = "paysheet_{$employee->employee_id}.pdf";
                $pdf = Pdf::loadView('management.payroll.paysheet', ['record' => $employee]);
                $zip->addFromString($fileName, $pdf->output());
            }
            $zip->close();

            return response()->download(public_path($zipFileName))->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Failed to create zip file.');
    }

public function generatePreviousMonth(Request $request)
{
    $selectedMonth = $request->query('selected_month');
    
    if (!$selectedMonth) {
        return back()->with('error', 'Please select a valid month.');
    }

    // Check if payroll already exists for this month
    $existingPayrolls = SalaryDetails::where('payed_month', $selectedMonth)->count();
    if ($existingPayrolls > 0) {
        return back()->with('error', 'Payroll records already exist for ' . $selectedMonth . '. Please delete existing records first or select a different month.');
    }

    // Get all active employees from the employees table
    $employees = Employee::with('department')->get();

    if ($employees->isEmpty()) {
        return back()->with('error', 'No employees found in the system.');
    }

    $recordsGenerated = 0;

foreach ($employees as $employee) {
        // Date range calculation (5th of selected month to 5th of next month)
        $startDate = date('Y-m-05', strtotime($selectedMonth));
        $endDate = date('Y-m-05', strtotime('+1 month', strtotime($selectedMonth)));
        
        // Get salary details from employee table
        $basic = (float) ($employee->basic ?? 0);
        $budgetAllowance = (float) ($employee->budget_allowance ?? 0);
        $grossSalary = $basic + $budgetAllowance;

        // Skip employees without salary information
        if ($grossSalary <= 0) {
            continue;
        }

        // Calculate leave-based no-pay deductions
        $leaveNoPayAmount = Leave::where('employee_id', $employee->id)
            ->where('is_no_pay', true)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->sum('no_pay_amount');

        // ========== Get approved loans for this employee ==========
        $approvedLoans = DB::table('loans')
            ->where('employee_id', $employee->id)
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
            ->where('employment_ID', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('advance_date', [$startDate, $endDate])
            ->get();

        // Calculate total advance amount for this period
        $advancePayment = $approvedAdvances->sum('advance_amount');
        
        // Get current advance balance from latest salary record
        $latestSalary = SalaryDetails::where('employee_id', $employee->id)
            ->orderByDesc('pay_date')
            ->first();
        
        $currentAdvanceBalance = $latestSalary->advance_balance ?? 0;
        $newAdvanceBalance = max(0, $currentAdvanceBalance + $advancePayment);

        // Calculate OT Payment
        $periodStart = Carbon::parse($startDate);
        $periodEnd = Carbon::parse($endDate);

        // Ensure employee has department relationship loaded for OvertimeCalculator
        if (!$employee->relationLoaded('department')) {
            $employee->load('department');
        }
        
        $overtimeResult = $this->overtimeCalculator->calculate($employee, $periodStart, $periodEnd);

        $otRate = 0.0041667327;
        $regularOTHours = $overtimeResult['regular_seconds'] / 3600;
        $sundayOTHours = $overtimeResult['sunday_seconds'] / 3600;

        // Debug logging for Saturday OT (remove after testing)
        if ($regularOTHours > 0 || $sundayOTHours > 0) {
            Log::info("OT Calculation for {$employee->full_name}", [
                'employee_id' => $employee->id,
                'period' => "{$startDate} to {$endDate}",
                'regular_ot_seconds' => $overtimeResult['regular_seconds'],
                'regular_ot_hours' => $regularOTHours,
                'sunday_ot_hours' => $sundayOTHours,
                'gross_salary' => $grossSalary,
                'head_office_summary' => $overtimeResult['head_office_summary'] ?? [],
            ]);
        }

        $otPayment = ($regularOTHours * (($grossSalary / 240) * 1.5)) +
                     ($sundayOTHours * ($grossSalary * 1.5 * $otRate * 2));

        // Calculate EPF contributions
        $epf8Percent = $grossSalary * 0.08;
        $epf12Percent = $grossSalary * 0.12;
        $etf3Percent = $grossSalary * 0.03;

        // Get stamp duty from employee table
        $stampDuty = (float) ($employee->stamp_duty ?? 25.00);

        // Calculate total deductions
        $totalDeductions = (
            $epf8Percent +
            $stampDuty +
            $leaveNoPayAmount +
            $advancePayment +
            $totalMonthlyLoanPayment
        );

        // Calculate total earnings
        $totalEarnings = (
            $grossSalary +
            ($employee->transport_allowance ?? 0) +
            ($employee->attendance_allowance ?? 0) +
            ($employee->phone_allowance ?? 0) +
            ($employee->car_allowance ?? 0) +
            ($employee->production_bonus ?? 0) +
            $otPayment
        );

        $netSalary = $totalEarnings - $totalDeductions;

        // Create new salary record
        $newSalary = SalaryDetails::create([
            'employee_name' => $employee->full_name,
            'employee_id' => $employee->id,
            'known_name' => $employee->known_name ?? $employee->full_name,
            'epf_no' => $employee->epf_no,
            'basic' => $basic,
            'budget_allowance' => $budgetAllowance,
            'gross_salary' => $grossSalary,
            'transport_allowance' => (float) ($employee->transport_allowance ?? 0),
            'attendance_allowance' => (float) ($employee->attendance_allowance ?? 0),
            'phone_allowance' => (float) ($employee->phone_allowance ?? 0),
            'production_bonus' => (float) ($employee->production_bonus ?? 0),
            'car_allowance' => (float) ($employee->car_allowance ?? 0),
            'loan_payment' => $totalMonthlyLoanPayment,
            'advance_payment' => $advancePayment,
            'ot_payment' => $otPayment,
            'total_earnings' => $totalEarnings,
            'epf_8_percent' => $epf8Percent,
            'epf_12_percent' => $epf12Percent,
            'etf_3_percent' => $etf3Percent,
            'stamp_duty' => $stampDuty,
            'no_pay' => $leaveNoPayAmount,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'loan_balance' => array_sum($newLoanBalances),
            'advance_balance' => $newAdvanceBalance,
            'pay_date' => now(),
            'payed_month' => $selectedMonth,
            'status' => 'pending',
        ]);

        // Update loan balances in the loans table
        foreach ($newLoanBalances as $loanId => $newBalance) {
            DB::table('loans')
                ->where('id', $loanId)
                ->update([
                    'remaining_balance' => $newBalance,
                    'updated_at' => now()
                ]);

            if ($newBalance == 0) {
                DB::table('loans')
                    ->where('id', $loanId)
                    ->update([
                        'status' => 'approved',
                        'loan_end_date' => now()
                    ]);
            }
        }

        $recordsGenerated++;
    }

    if ($recordsGenerated > 0) {
        return redirect()->route('payroll.management')->with('success', "Successfully generated {$recordsGenerated} payroll records for {$selectedMonth}.");
    } else {
        return back()->with('error', 'No payroll records were generated. Please ensure employees have salary information configured.');
    }
}

    public function exportSalarySpreadsheet(Request $request)
    {//dd($request->all());
        $selectedMonth = $request->query('selected_month'); // Get the month from the query
        if (!$selectedMonth) {
            return back()->with('error', 'Please select a valid month.');
        }
       // dd($selectedMonth);
        $employees = SalaryDetails::where('payed_month', $selectedMonth)->get();
      //  dd($employees);
        if ($employees->isEmpty()) {

            return back()->with('error', 'No records found for the selected month.');
        }

        $filePath = storage_path("app/public/employee_salaries_{$selectedMonth}.xlsx");
        $writer = SimpleExcelWriter::create($filePath);

        // Add column headings
        $writer->addHeader([
            'Employee ID', 'Employee Name', 'Known Name', 'EPF No', 'Basic Salary',
            'Budget Allowance', 'Gross Salary', 'Transport Allowance', 'Attendance Allowance',
            'Phone Allowance', 'Production Bonus', 'Car Allowance', 'Loan Payment', 'ot_payment',
            'Total Earnings', 'EPF (8%)', 'EPF (12%)', 'ETF (3%)', 'Advance Payment',
            'Stamp Duty', 'No Pay', 'Total Deductions', 'Net Salary', 'Loan Balance',
            'Pay Date', 'Paid Month'
        ]);

        // Add rows
        foreach ($employees as $record) {
            $writer->addRow([
                $record->employee_id,
                $record->employee_name,
                $record->known_name,
                $record->epf_no,
                $record->basic,
                $record->budget_allowance,
                $record->gross_salary,
                $record->transport_allowance,
                $record->attendance_allowance,
                $record->phone_allowance,
                $record->production_bonus,
                $record->car_allowance,
                $record->loan_payment,
                $record->ot_payment,
                $record->total_earnings,
                $record->epf_8_percent,
                $record->epf_12_percent,
                $record->etf_3_percent,
                $record->advance_payment,
                $record->stamp_duty,
                $record->no_pay,
                $record->total_deductions,
                $record->net_salary,
                $record->loan_balance,
                $record->pay_date,
                $record->payed_month,
            ]);
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

public function exportSalaryPDF(Request $request)
{
    $selectedMonth = $request->query('selected_month'); 
    if (!$selectedMonth) {
        return back()->with('error', 'Please select a valid month.');
    }

    $employees = SalaryDetails::where('payed_month', $selectedMonth)->get();
    if ($employees->isEmpty()) {
        return back()->with('error', 'No records found for the selected month.');
    }

    // Pass $selectedMonth to the view
    $pdf = Pdf::loadView('salary_pdf', [
        'employees' => $employees,
        'selectedMonth' => $selectedMonth
    ])->setPaper('A3', 'landscape');

    return $pdf->download("employee_salaries_{$selectedMonth}.pdf");
}
}
