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
use App\Exports\BankDetailsExport;


class PayrollExportController extends Controller
{

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
    $previousMonth = date('Y-m', strtotime('-1 month', strtotime($selectedMonth)));

    $payrolls = SalaryDetails::where('payed_month', $previousMonth)->get();

    foreach ($payrolls as $payroll) {
        // Get attendance records for OT calculation (from 5th of selected month to 5th of next month)
        $startDate = date('Y-m-05', strtotime($selectedMonth));
        $endDate = date('Y-m-05', strtotime('+1 month', strtotime($selectedMonth)));
        
        // Calculate leave-based no-pay deductions
        $leaveNoPayAmount = Leave::where('employee_id', $payroll->employee_id)
            ->where('is_no_pay', true)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->sum('no_pay_amount');

        // ========== UPDATED: Get approved loans for this employee ==========
        $approvedLoans = DB::table('loans')
            ->where('employee_id', $payroll->employee_id)
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

        // ========== NEW: Get approved advances for this employee ==========
        $approvedAdvances = DB::table('advances')
            ->where('employment_ID', $payroll->employee_id)
            ->where('status', 'approved')
            ->whereBetween('advance_date', [
        date('Y-m-01', strtotime($selectedMonth)), // start of selected month
        date('Y-m-t', strtotime($selectedMonth))   // end of selected month
        ])
            ->get();

        // Calculate total advance amount for this period
        $advancePayment = $approvedAdvances->sum('advance_amount');
        
        // Calculate new advance balance
        $newAdvanceBalance = max(0, ($payroll->advance_balance ?? 0) + $advancePayment);

        $attendanceRecords = Attendance::where('employee_id', $payroll->employee_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
            
        // Calculate total OT hours and late_by hours
        $totalOTHours = $attendanceRecords->sum('overtime_seconds') / 3600;
        $totalLateByHours = $attendanceRecords->sum('late_by_seconds') / 3600;

        // Deduct late hours from OT hours
        $finalOTHours = max(0, $totalOTHours);

        // Calculate OT Payment
        $grossSalary = $payroll->basic + $payroll->budget_allowance;

        $otRate = 0.0041667327;
        $regularOTSeconds = 0;
        $sundayOTSeconds = 0;

        $employee = Employee::find($payroll->employee_id);

        foreach ($attendanceRecords as $record) {
            $dayOfWeek = date('w', strtotime($record->date));
            $isDoubleOTDay = ($dayOfWeek == 0);

            // Check for department 2 on Saturday
            if ($employee && $employee->department_id == 2 && $dayOfWeek == 6) {
                if ($record->clock_in_time && $record->clock_out_time) {
                    $workedSeconds = Carbon::parse($record->clock_out_time)->diffInSeconds(Carbon::parse($record->clock_in_time));

                    if ($workedSeconds > 14400) {
                        $saturdayTotalOTSeconds = ($workedSeconds - 14400);
                        $regularOTSeconds += $saturdayTotalOTSeconds;
                    }
                }
            } else {
                // Original OT logic
                if ($isDoubleOTDay) {
                    $sundayWorkedSeconds = Carbon::parse($record->clock_out_time)->diffInSeconds(Carbon::parse($record->clock_in_time));
                    $sundayOTSeconds += ($sundayWorkedSeconds);
                } else {
                    $regularOTSeconds += $record->overtime_seconds;
                }
            }
        }

        $totalOTHours = ($regularOTSeconds + $sundayOTSeconds) / 3600;
        $totalLateByHours = $attendanceRecords->sum('late_by_seconds') / 3600;

        // Apply double OT rate for Sundays
        $regularOTHours = $regularOTSeconds / 3600;
        $sundayOTHours = $sundayOTSeconds / 3600;

        // $otPayment = ($regularOTHours * ($grossSalary * 1.5 * $otRate)) +
        //              ($sundayOTHours * ($grossSalary * 1.5 * $otRate * 2));

           $otPayment = ($regularOTHours * (($grossSalary / 240) * 1.5)) +
                     ($sundayOTHours * ($grossSalary * 1.5 * $otRate * 2));

        // ========== UPDATED: Calculate deductions including loans and advances ==========
        $totalDeductions = (
            ($payroll->epf_8_percent ?? 0) +
            ($payroll->stamp_duty ?? 0) +
            $leaveNoPayAmount +
            $advancePayment +  // Add advance payment to deductions
            $totalMonthlyLoanPayment  // Add total loan payments to deductions
        );

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

        // Create new salary record
        $newSalary = SalaryDetails::create([
            'employee_name' => $payroll->employee_name,
            'employee_id' => $payroll->employee_id,
            'known_name' => $payroll->known_name,
            'epf_no' => $payroll->epf_no,
            'basic' => $payroll->basic,
            'budget_allowance' => $payroll->budget_allowance,
            'gross_salary' => $payroll->gross_salary,
            'transport_allowance' => $payroll->transport_allowance,
            'attendance_allowance' => $payroll->attendance_allowance,
            'phone_allowance' => $payroll->phone_allowance,
            'production_bonus' => $payroll->production_bonus,
            'car_allowance' => $payroll->car_allowance,
            'loan_payment' => $totalMonthlyLoanPayment, // Store the total loan payment
            'advance_payment' => $advancePayment, // Store the advance payment
            'ot_payment' => $otPayment,
            'total_earnings' => $totalEarnings,
            'epf_8_percent' => $payroll->epf_8_percent,
            'epf_12_percent' => $payroll->epf_12_percent,
            'etf_3_percent' => $payroll->etf_3_percent,
            'stamp_duty' => $payroll->stamp_duty,
            'no_pay' => $payroll->no_pay + $leaveNoPayAmount,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'loan_balance' => array_sum($newLoanBalances), // Total of all loan balances
            'advance_balance' => $newAdvanceBalance, // Updated advance balance
            'pay_date' => now(),
            'payed_month' => $selectedMonth,
            'status' => $payroll->status,
        ]);

        // ========== FIXED: Update loan balances in the loans table ==========
        foreach ($newLoanBalances as $loanId => $newBalance) {
            DB::table('loans')
                ->where('id', $loanId)
                ->update([
                    'remaining_balance' => $newBalance,
                    'updated_at' => now()
                ]);

            // FIX: Use 'approved' status instead of 'completed' since it's not in enum
            if ($newBalance == 0) {
                DB::table('loans')
                    ->where('id', $loanId)
                    ->update([
                        'status' => 'approved', // Keep as 'approved' or use different field to mark completion
                        'loan_end_date' => now()
                    ]);
            }
        }
    }

    return redirect()->route('payroll.management')->with('success', 'Records generated for the selected month with loan and advance deductions.');
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
