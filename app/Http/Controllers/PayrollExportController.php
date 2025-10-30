<?php
namespace App\Http\Controllers;

use App\Models\SalaryDetails;
use App\Models\Attendance;
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
use App\Services\LeaveBalanceService;


class PayrollExportController extends Controller
{
    private OvertimeCalculator $overtimeCalculator;
    private LeaveBalanceService $leaveBalanceService;

    public function __construct(OvertimeCalculator $overtimeCalculator, LeaveBalanceService $leaveBalanceService)
    {
        $this->overtimeCalculator = $overtimeCalculator;
        $this->leaveBalanceService = $leaveBalanceService;
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

    $previousMonth = date('Y-m', strtotime('-1 month', strtotime($selectedMonth)));

    $payrolls = SalaryDetails::where('payed_month', $previousMonth)->get();

    if ($payrolls->isEmpty()) {
        return back()->with('error', 'No payroll records found for the previous month.');
    }

    $employees = Employee::with('department')->get();
    if ($employees->isEmpty()) {
        return back()->with('error', 'No employees found in the system.');
    }

    foreach ($employees as $employee) {

        // Date range for OT calculation (5th of selected month to 5th of next month)
        $startDate = date('Y-m-05', strtotime($selectedMonth));
        $endDate = date('Y-m-05', strtotime('+1 month', strtotime($selectedMonth)));

        $periodStart = Carbon::parse($startDate);
        $periodEnd = Carbon::parse($endDate);

        // Annual leave year cycle
        $yearStart = Carbon::parse("1 Jan {$periodStart->year}");
        $yearEnd = Carbon::parse("31 Dec {$periodStart->year}");

        // Get total approved annual leaves
        $totalAnnualLeavesUsed = Leave::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('leave_type', 'Annual Leave')
            ->whereIn('leave_category', ['full_day', 'half_day'])
            ->whereBetween('start_date', [$yearStart, $yearEnd])
            ->sum('duration');

        $noPayDays = max(0, $totalAnnualLeavesUsed - 21);
        $payroll = $payrolls->firstWhere('employee_id', $employee->id);

        if (!$payroll) continue; // Skip if no payroll found

        $dailyRate = $payroll->basic / 30;
        $leaveNoPayAmount = max(0, $noPayDays * $dailyRate);

        // Use LeaveBalanceService for accurate no-pay calculation
        $leaveNoPayAmount = $this->leaveBalanceService->calculateNoPayForPeriod(
            $employee->id,
            $startDate,
            $endDate
        );

        // Approved loans
        $approvedLoans = DB::table('loans')
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->get();

        $totalMonthlyLoanPayment = 0;
        $newLoanBalances = [];

        foreach ($approvedLoans as $loan) {
            if ($loan->remaining_balance > 0) {
                $monthlyPayment = $loan->monthly_paid;
                $actualPayment = min($monthlyPayment, $loan->remaining_balance);
                $totalMonthlyLoanPayment += $actualPayment;
                $newLoanBalances[$loan->id] = max(0, $loan->remaining_balance - $actualPayment);
            }
        }

        // Approved advances
        $approvedAdvances = DB::table('advances')
            ->where('employment_ID', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('advance_date', [
                date('Y-m-01', strtotime($selectedMonth)),
                date('Y-m-t', strtotime($selectedMonth))
            ])
            ->get();

        $advancePayment = $approvedAdvances->sum('advance_amount');
        $newAdvanceBalance = max(0, ($payroll->advance_balance ?? 0) + $advancePayment);

        // Attendance and OT calculation
        $attendanceRecords = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalOTHours = $attendanceRecords->sum('overtime_seconds') / 3600;
        $totalLateByHours = $attendanceRecords->sum('late_by_seconds') / 3600;

        $regularOTSeconds = 0;
        $sundayOTSeconds = 0;

        foreach ($attendanceRecords as $record) {
            $dayOfWeek = date('w', strtotime($record->date));
            $isSunday = ($dayOfWeek == 0);

            // Department 2 Saturday logic
            if ($employee->department_id == 2 && $dayOfWeek == 6 && $record->clock_in_time && $record->clock_out_time) {
                $workedSeconds = Carbon::parse($record->clock_out_time)
                    ->diffInSeconds(Carbon::parse($record->clock_in_time));

                if ($workedSeconds > 14400) {
                    $regularOTSeconds += ($workedSeconds - 14400);
                }
            } elseif ($isSunday) {
                $sundayWorkedSeconds = Carbon::parse($record->clock_out_time)
                    ->diffInSeconds(Carbon::parse($record->clock_in_time));
                $sundayOTSeconds += $sundayWorkedSeconds;
            } else {
                $regularOTSeconds += $record->overtime_seconds;
            }
        }

        $regularOTHours = $regularOTSeconds / 3600;
        $sundayOTHours = $sundayOTSeconds / 3600;

        $grossSalary = $payroll->basic + $payroll->budget_allowance;
        $otRate = 0.0041667327;
        $otPayment = ($regularOTHours * (($grossSalary / 240) * 1.5)) +
                     ($sundayOTHours * ($grossSalary * 1.5 * $otRate * 2));

        // Deductions
        $totalDeductions = (
            ($payroll->epf_8_percent ?? 0) +
            ($payroll->stamp_duty ?? 0) +
            $leaveNoPayAmount +
            $advancePayment +
            $totalMonthlyLoanPayment
        );

        // Earnings
        $totalEarnings = (
            $grossSalary +
            ($payroll->transport_allowance ?? 0) +
            ($payroll->attendance_allowance ?? 0) +
            ($payroll->phone_allowance ?? 0) +
            ($payroll->car_allowance ?? 0) +
            ($payroll->production_bonus ?? 0) +
            $otPayment
        );

        $netSalary = $totalEarnings - $totalDeductions;

        // Create salary record
        SalaryDetails::create([
            'employee_name' => $payroll->employee_name,
            'employee_id' => $employee->id,
            'known_name' => $payroll->known_name,
            'epf_no' => $payroll->epf_no,
            'basic' => $payroll->basic,
            'budget_allowance' => $payroll->budget_allowance,
            'gross_salary' => $grossSalary,
            'transport_allowance' => $payroll->transport_allowance,
            'attendance_allowance' => $payroll->attendance_allowance,
            'phone_allowance' => $payroll->phone_allowance,
            'production_bonus' => $payroll->production_bonus,
            'car_allowance' => $payroll->car_allowance,
            'loan_payment' => $totalMonthlyLoanPayment,
            'advance_payment' => $advancePayment,
            'ot_payment' => $otPayment,
            'total_earnings' => $totalEarnings,
            'epf_8_percent' => $payroll->epf_8_percent,
            'epf_12_percent' => $payroll->epf_12_percent,
            'etf_3_percent' => $payroll->etf_3_percent,
            'stamp_duty' => $payroll->stamp_duty,
            'no_pay' => $leaveNoPayAmount,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'loan_balance' => array_sum($newLoanBalances),
            'advance_balance' => $newAdvanceBalance,
            'pay_date' => now(),
            'payed_month' => $selectedMonth,
            'status' => $payroll->status,
        ]);

        // Update loan balances
        foreach ($newLoanBalances as $loanId => $newBalance) {
            DB::table('loans')->where('id', $loanId)->update([
                'remaining_balance' => $newBalance,
                'updated_at' => now()
            ]);
        }
    }

    return redirect()->route('payroll.management')
        ->with('success', 'Records generated for the selected month with annual leave no-pay deductions.');
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
