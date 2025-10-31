<!DOCTYPE html>
<html>
<head>
    <title>Payslip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        img{
            display: inline !important;
        }
        .payslip-container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .header {
            width: 100%;
            display: flex !important;
         
            justify-content: space-between !important;
            margin-bottom: 20px !important;
        }
        .header-logo {
      
            display: flex !important;
      
            justify-content: space-between!important;
        }
        .header-logo img {
            max-height: 80px !important;
            max-width: 200px !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
        }
        .company-details {
          
            text-align: right !important;
        }
        .company-details h1 {
            margin: 0 !important;
            font-size: 20px !important;
            color: #333;
        }
        .company-details h2 {
            margin: 0 !important;
            font-size: 16px !important;
            color: #666;
        }
        .details {
            margin-bottom: 20px !important;
            display: flex !important;
            justify-content: space-between !important;
            font-size: 14px !important;
        }
        .details div {
            flex: 1;
        }
        .details p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
           padding: 4px 6px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
            color: #333;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .totals-row {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
        .signature {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .signature div {
            text-align: center;
            flex: 1;
        }
        .signature div p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <div class="header">
            <div class="header-content">
                <div class="header-logo">
                    
                   
                    <img style="display: inline-block"  src="./logo.png" alt="Company Logo">
                    <div style="display: inline-block; margin-left:40%; text-align:right; font-weight: bold;">Apex Aura INT (Pvt) Ltd<br/>823/E, Kaduwela Road,<br/> Malabe</br>Tel:011-3476476</br></div>
                    
                
          
            </div>
        </div>

        <!-- Rest of the payslip remains the same -->
        <div class="details">
            <div>
                <p><strong>Employee Name:</strong> {{ $record->employee->full_name }}</p>
                <p><strong>EPF No:</strong> #{{ $record->epf_no}}</p>
                <p><strong>Designation:</strong> {{ $record->employee->title }}</p>
                <p><strong>Joined Date:</strong> {{ $record->employee->employment_start_date }}</p>
                <p><strong>Pay Slip for the Period of:</strong> {{ \Carbon\Carbon::createFromFormat('Y-m', $record->payed_month)->format('Y F') }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Basic Salary</td>
                    <td class="text-right">{{ number_format($record->basic, 2) }}</td>
                </tr>
                <tr>
                    <td>Budget Allowance</td>
                    <td class="text-right">{{ number_format($record->budget_allowance, 2) }}</td>
                </tr>
                <tr>
                    <td>(Gross Salary)</td>
                    <td class="text-right no-border">({{ number_format($record->gross_salary, 2) }})</td>
                </tr>
                <tr>
                    <td colspan="2" class="pt-4 pb-2 font-bold text-lg text-blue-600 border-t-2 border-gray-300">
        Fixed Allowances
    </td>
                </tr>
                <tr>
                    <td>Traveling Allowances</td>
                    <td class="text-right">{{ number_format($record->transport_allowance, 2) }}</td>
                </tr>
                <tr>
                    <td>Attendance Allowance</td>
                    <td class="text-right">{{ number_format($record->attendance_allowance, 2) }}</td>
                </tr>
                <tr>
                    <td>Car Allowance</td>
                    <td class="text-right">{{ number_format($record->car_allowance, 2) }}</td>
                </tr>
                <tr>
                    <td>Phone Allowance</td>
                    <td class="text-right">{{ number_format($record->phone_allowance, 2) }}</td>
                </tr>
                 <tr>
                    <td>Production Bonus</td>
                    <td class="text-right">{{ number_format($record->production_bonus, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="pt-4 pb-2 font-bold text-lg text-blue-600 border-t-2 border-gray-300">Variable Allowance</td>
                </tr>
                <tr>
                    <td>OT</td>
                    <td class="text-right">{{ number_format($record->ot_payment, 2) }}</td>
                </tr>
               
                <tr class="totals-row mt-2 underline-offset-0">
                    <td>Total Earnings</td>
                    <td class="text-right">{{ number_format($record->total_earnings, 2) }}</td>
                </tr>
                <tr class="bold-row no-upper-border">
                    <td colspan="2" class="pt-4 pb-2 font-bold text-lg text-blue-600 border-t-2 border-gray-300">Deductions</td>
                </tr>
                <tr>
                    <td>EPF</td>
                    <td class="text-right">{{ number_format($record->epf_8_percent, 2) }}</td>
                </tr>
                <tr>
                    <td>Advance</td>
                    <td class="text-right">{{ number_format($record->advance_payment, 2) }}</td>
                </tr>
                <tr>
                    <td>Loan (Remaining balance: {{ number_format($record->loan_balance, 2) }})</td>
                    <td class="text-right">{{ number_format($record->loan_payment, 2) }}</td>
                </tr>
                <tr class="totals-row">
                    <td>Total Deductions</td>
                    <td class="text-right">{{ number_format($record->total_deductions, 2) }}</td>
                </tr>
                <tr class="totals-row">
                    <td>Net Pay</td>
                    <td class="text-right">{{ number_format($record->net_salary, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature">
            <div>
                <p>_______________________</p>
                <p>Authorized Signature</p>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated payslip. If you have any queries, contact HR.</p>
        </div>
    </div>
    <script>
        // Safely embed the PHP variable into JavaScript
        const record = JSON.parse(`{!! addslashes(json_encode($record)) !!}`);

        console.log('Record Data:', record);
        console.log('Employee Data:', record.employee);
    </script>



</body>
</html>