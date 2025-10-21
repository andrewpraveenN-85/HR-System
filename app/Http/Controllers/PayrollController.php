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


class PayrollController extends Controller
{
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
            'epf_no' => 'nullable|integer|unique:employee_salary_details,epf_no',
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
            // Calculate deductions and net salary
            $grossSalary = $request->gross_salary;
            $noPayDeductions = ($request->no_pay ?? 0) * 1000;
            $stampDuty = ($request->stamp_duty ?? 0) + 25;
            $totalDeductions = (
                ($request->epf_8_percent ?? 0) +
                ($request->advance_payment ?? 0) +
                ($request->loan_payment ?? 0) +
                $stampDuty +
                $noPayDeductions
            );

            $totalEarnings = (
                $grossSalary +
                ($request->transport_allowance ?? 0) +
                ($request->attendance_allowance ?? 0) +
                ($request->phone_allowance ?? 0) +
                ($request->car_allowance ?? 0) +
                ($request->production_bonus ?? 0)
            );

            $netSalary = $totalEarnings - $totalDeductions;

            // Calculate EPF 12% and ETF 3% based on gross salary
            $epf12Percent = round($grossSalary * 0.12, 2);
            $etf3Percent = round($grossSalary * 0.03, 2);

            // Save the data to the database
            SalaryDetails::create([
                'employee_id' => $request->employee_id,
                'employee_name' => $request->employee_name,
                'known_name' => $request->known_name,
                'epf_no' => $request->epf_no,
                'pay_date' => $request->pay_date,
                'payed_month' => $request->payed_month,
                'basic' => $request->basic,
                'budget_allowance' => $request->budget_allowance,
                'gross_salary' => $grossSalary,
                'transport_allowance' => $request->transport_allowance,
                'attendance_allowance' => $request->attendance_allowance,
                'phone_allowance' => $request->phone_allowance,
                'production_bonus' => $request->production_bonus,
                'car_allowance' => $request->car_allowance,
                'loan_payment' => $request->loan_payment,
                'stamp_duty' => $stampDuty,
                'no_pay' => $request->no_pay,
                'advance_payment' => $request->advance_payment,
                'total_deductions' => $totalDeductions,
                'total_earnings' => $totalEarnings,
                'net_salary' => $netSalary,
                'epf_8_percent' => $request->epf_8_percent,
                'epf_12_percent' => $epf12Percent,
                'etf_3_percent' => $etf3Percent,
            ]);

            return redirect()->route('management.payroll.payroll-management')->with('success', 'Payroll record saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to save the payroll record.')->withInput();
        }
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
    
    // Calculate leave-based no-pay deductions
    $leaveNoPayAmount = Leave::where('employee_id', $id)
        ->where('is_no_pay', true)
        ->whereBetween('start_date', [$startDate, $endDate])
        ->sum('no_pay_amount');

    return response()->json([
        'no_pay_amount' => $leaveNoPayAmount

    ]);

}
}

