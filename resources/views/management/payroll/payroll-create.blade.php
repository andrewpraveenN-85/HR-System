

@extends('layouts.dashboard-layout')

@section('title', 'Add Payroll')

@section('content')
@if($errors->any())
<div class="bg-red-100 text-red-800 p-3 rounded mb-4">
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if (session('success'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            alert("{{ session('success') }}");
        });
    </script>
@endif

@if (session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            alert("{{ session('error') }}");
        });
    </script>
@endif

<form method="POST" action="{{ route('payroll.store') }}" class="p-0 m-0">
    @csrf
    <div class="flex flex-col items-start justify-start w-full px-4">
        <div class="w-full pt-2">
            <div class="flex items-center justify-between w-full">
                <div class="flex ">
                    <p class="text-6xl font-bold text-black nunito-">Payroll</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="flex items-center justify-center nunito- space-x-2 px-8 py-2 text-white text-2xl bg-gradient-to-r from-[#184E77] to-[#52B69A] rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
                        <p class="text-3xl"><i class="ri-add-fill"></i></p>
                        <span>Save</span>
                    </button>
                </div>
            </div>
        </div>
        <nav class="flex py-3" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 nunito-">
                <li class="inline-flex items-center">
                    <a href="#" class="inline-flex items-center text-3xl font-medium text-[#00000080] hover:text-blue-600">
                        Payroll
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <p class="text-[#00000080] text-3xl"><i class="ri-arrow-right-wide-line"></i></p>
                        <a href="#" class="ml-1 font-medium text-[#00000080] text-3xl hover:text-blue-600">Add New Record</a>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="w-full flex flex-col justify-center items-center space-y-4">
            <!-- Employee Details -->
            <div class="w-full mx-auto  bg-[#D9D9D966] rounded-3xl p-8 space-y-4">
                <div class="w-full flex justify-start items-center ">
                    <p class="text-3xl font-bold text-black pl-16">Employee Details :</p>
                </div>
                <div class="w-full  flex justify-between items-center space-x-48 px-16">
                  <div class="w-full">
    <label for="employee_id" class="block text-xl text-black font-bold">Employee ID :</label>
    <select id="employee_id" name="employee_id"
        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl combobox">
        <option value="">Select or type Employee ID</option>
        @foreach($employees as $employee)
            <option value="{{ $employee->id }}">{{ $employee->employee_id }} </option>
        @endforeach
    </select>
</div>


                    <div class="w-full">
                        <label for="full_name" class="block text-xl text-black font-bold">Employee Name :</label>
                        <input type="text" id="full_name" name="full_name" placeholder="Enter Employee Name" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" />
                    </div>
                    <div class="w-full">
                       <span for="payed_month" class="block text-xl text-black font-bold">Select a Month</span>
                {{-- <p id="selectedMonth" class="text-lg font-bold">03.2021</p> --}}
                <!-- Add this in your HTML, possibly near your existing month-related elements -->


                <input
                    type="month"
                    id="payed_month"
                    name="payed_month"
                    class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#184E77] focus:border-[#184E77]"
                >
                </div>
              
                
                    <!-- <div >
                        <label for="known_name" class="block text-xl text-black font-bold">Known Name :</label>
                        <input type="text" id="known_name" name="known_name" placeholder="Enter known Name" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" />
                    </div>
                      </div> -->
                    <!--<div class="w-full">
                        <label for="epf_no" class="block text-xl text-black font-bold">EPF No :</label>
                        <input type="text" id="epf_no" name="epf_no" placeholder="Enter EPF No" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" />
                    </div> -->
                
</div>
            </div>
            <div class="w-full flex justify-between items-start space-x-8 mt-8">

            <!-- Salary and Allowances Section -->
            <div class="w-1/2 bg-[#D9D9D966] rounded-3xl p-8 space-y-4">
                <div class="w-full flex justify-start items-center">
                    <p class="text-3xl font-bold text-black pl-4">Salary and Allowances :</p>
                </div>
              <div class="flex flex-col space-y-4 px-4">
                      <!-- <div >
                            <label for="pay_date" class="block text-xl text-black font-bold">Pay Date :</label>
                            <input type="date" id="pay_date" name="pay_date" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" />
                        </div>
                        <div>
                            <label for="payed_month" class="block text-xl text-black font-bold">Paid Month :</label>
                            <input type="month" id="payed_month" name="payed_month" placeholder="Enter Paid Month" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" />
                        </div> -->
                       <div>
                            <label for="basic" class="block text-xl text-black font-bold">Basic Salary :</label>
                            <input type="number" id="basic" name="basic" placeholder="Enter Basic Salary" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" oninput="calculateGrossSalary()" />
                        </div>
                        <div>
                            <label for="budget_allowance" class="block text-xl text-black font-bold">Budget Allowance :</label>
                            <input type="number" id="budget_allowance" name="budget_allowance" placeholder="Enter Budget Allowance" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" oninput="calculateGrossSalary()" />
                        </div>
                        <div>
                            <label for="transport_allowance" class="block text-xl text-black font-bold">Transport Allowance :</label>
                            <input type="number" id="transport_allowance" name="transport_allowance" placeholder="Enter Transport Allowance" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" oninput="calculateTotalEarnings()"/>
                        </div>
                        <div>
                            <label for="attendance_allowance" class="block text-xl text-black font-bold">Attendance Allowance :</label>
                            <input type="number" id="attendance_allowance" name="attendance_allowance" placeholder="Enter Attendance Allowance" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" oninput="calculateTotalEarnings()" />
                        </div>
                        <div>
                            <label for="phone_allowance" class="block text-xl text-black font-bold">Phone Allowance :</label>
                            <input type="number" id="phone_allowance" name="phone_allowance" placeholder="Enter Phone Allowance" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" oninput="calculateTotalEarnings()"/>
                        </div>

                        <div>
                            <label for="car_allowance" class="block text-xl text-black font-bold">Car Allowance :</label>
                            <input type="number" id="car_allowance" name="car_allowance" placeholder="Enter car allowance" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" oninput="calculateTotalEarnings()"/>
                        </div>
                        <div>
                            <label for="production_bonus" class="block text-xl text-black font-bold">Production Bonus :</label>
                            <input type="number" id="production_bonus" name="production_bonus" placeholder="Enter production bonus" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" oninput="calculateTotalEarnings()"/>
                        </div>
                        <!-- <div>
                            <label for="ot_payment" class="block text-xl text-black font-bold">OT Payment:</label>
                            <input type="number" id="ot_payment" name="ot_payment" placeholder="Enter OT Payment" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" oninput="calculateTotalEarnings()"/>
                        </div> -->


                        <div>
                           <label for="gross_salary" class="block text-xl text-black font-bold">Gross Salary :</label>
                           <input type="number" id="gross_salary" name="gross_salary" readonly class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" />
                       </div>
                       
                    </div>
                    
                      


                    
              
            </div>
               <div class="w-1/2 flex flex-col space-y-8">

            <!-- Deductions Section -->
            <div class="bg-[#D9D9D966] rounded-3xl p-8 space-y-4">
                <div class="w-full flex justify-start items-center">
                    <p class="text-3xl font-bold text-black pl-4">Deductions :</p>
                </div>
                 <div class="flex flex-col space-y-4 px-4">
                       <div >
                            <label for="stamp_duty" class="block text-xl text-black font-bold">Stamp Duty :</label>
                            <input type="number" id="stamp_duty" name="stamp_duty" value="25" readonly
    class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] bg-gray-100 text-gray-700 font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl"
    oninput="calculateTotalDeductions()" />

                        </div>
                        <div >
                            <label for="no_pay" class="block text-xl text-black font-bold">No Pay :</label>
                            <input type="number" id="no_pay" name="no_pay" oninput="calculateTotalDeductions()" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" />
                        </div>
                        <div>
                            <label for="epf_8_percent" class="block text-xl text-black font-bold">EPF (8%) :</label>
                            <input type="number" id="epf_8_percent" name="epf_8_percent" readonly class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" />
                        </div>
                        <div>
                            <label for="advance_payment" class="block text-xl text-black font-bold">Advance Payment :</label>
                            <input type="number" id="advance_payment" name="advance_payment" placeholder="Enter Advance Payment" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" oninput="calculateTotalDeductions()" />
                        </div>
                        <div>
                            <label for="loan_payment" class="block text-xl text-black font-bold">Loan Payment :</label>
                            <input type="number" id="loan_payment" name="loan_payment" placeholder="Enter Loan Payment" class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" oninput="calculateTotalDeductions()" />
                        </div>
                    </div>
                </div>     
                   <div>
                      <!-- ✅ New Box: Total Section (below deductions) -->
                    <div class="bg-[#D9D9D966] rounded-3xl p-8 space-y-4">
                        <div class="w-full flex justify-start items-center">
                            <p class="text-3xl font-bold text-black pl-4">Salary Summary :</p>
                        </div>

             <div class="flex flex-col space-y-4 px-4">
                     <div >
                        <div>
                            <label for="total_deductions" class="block text-xl text-black font-bold">Total Deductions :</label>
                            <input type="number" id="total_deductions" name="total_deductions" readonly class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" />
                        </div>
                        <div>
                            <label for="total_earnings" class="block text-xl text-black font-bold">Total Earnings :</label>
                            <input type="number" id="total_earnings" name="total_earnings" readonly class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" />
                        </div>

                        <div>
                            <label for="net_salary" class="block text-xl text-black font-bold">Net Salary :</label>
                            <input type="number" id="net_salary" name="net_salary" readonly class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 text-xl" />
                        </div>
                    </div>
                   </div>
                </div>
                
            
            
        </div>
</div>
    </div>
</form>

<script>
    function calculateGrossSalary() {
        const basic = parseFloat(document.getElementById('basic').value) || 0;
        const budgetAllowance = parseFloat(document.getElementById('budget_allowance').value) || 0;
        document.getElementById('gross_salary').value = (basic + budgetAllowance).toFixed(2);
        calculateEPF();
    }

    function calculateEPF() {
        const grossSalary = parseFloat(document.getElementById('gross_salary').value) || 0;
        document.getElementById('epf_8_percent').value = (grossSalary * 0.08).toFixed(2);
        calculateTotalDeductions();
    }

      function calculateTotalDeductions() {
        console.log("Calculating deductions...");

        const epf = parseFloat(document.getElementById('epf_8_percent').value) || 0;
        const advancePayment = parseFloat(document.getElementById('advance_payment').value) || 0;
        const loanPayment = parseFloat(document.getElementById('loan_payment').value) || 0;
        const noPay = parseFloat(document.getElementById('no_pay').value) || 0;
        const stampDuty = parseFloat(document.getElementById('stamp_duty').value) || 0; // ✅ included

        // ✅ Include stamp duty in total deductions
        const totalDeductions = epf + advancePayment + loanPayment + noPay + stampDuty;

        document.getElementById('total_deductions').value = totalDeductions.toFixed(2);
        calculateTotalEarnings();
    }

    function calculateTotalEarnings() {
        const grossSalary = parseFloat(document.getElementById('gross_salary').value) || 0;
        const transportAllowance = parseFloat(document.getElementById('transport_allowance').value) || 0;
        const attendanceAllowance = parseFloat(document.getElementById('attendance_allowance').value) || 0;
        const phoneAllowance = parseFloat(document.getElementById('phone_allowance').value) || 0;
        const carAllowance = parseFloat(document.getElementById('car_allowance').value) || 0;
        const prodBonus = parseFloat(document.getElementById('production_bonus').value) || 0;
        const totalEarnings = grossSalary + transportAllowance + attendanceAllowance + phoneAllowance + carAllowance + prodBonus;

        document.getElementById('total_earnings').value = totalEarnings.toFixed(2);
        calculateNetSalary();
    }

    function calculateNetSalary() {
        const grossSalary = parseFloat(document.getElementById('gross_salary').value) || 0;
        const transportAllowance = parseFloat(document.getElementById('transport_allowance').value) || 0;
        const attendanceAllowance = parseFloat(document.getElementById('attendance_allowance').value) || 0;
        const phoneAllowance = parseFloat(document.getElementById('phone_allowance').value) || 0;
        const carAllowance = parseFloat(document.getElementById('car_allowance').value) || 0;
        const prodBonus = parseFloat(document.getElementById('production_bonus').value) || 0;
        const deductions = parseFloat(document.getElementById('total_deductions').value) || 0;

        // ✅ Correct net salary calculation
        const totalEarnings = grossSalary + transportAllowance + attendanceAllowance + phoneAllowance + carAllowance + prodBonus;
        const netSalary = totalEarnings - deductions;

        document.getElementById('net_salary').value = netSalary.toFixed(2);
    }
    $('#employee_id').on('change', function () {
        let employeeId = $(this).val();

        if (employeeId) {
            $.ajax({
                url: `/employees/${employeeId}/salary-details`,
                type: 'GET',
                success: function (data) {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    // Fill the fields from employee table
                    $('#full_name').val(data.full_name);
                    $('#basic').val(data.basic);
                    $('#budget_allowance').val(data.budget_allowance);
                    $('#transport_allowance').val(data.transport_allowance);
                    $('#attendance_allowance').val(data.attendance_allowance);
                    $('#phone_allowance').val(data.phone_allowance);
                    $('#car_allowance').val(data.car_allowance);
                    $('#production_bonus').val(data.production_bonus);

                    // Calculate gross salary dynamically
                    calculateGrossSalary();
                },
                error: function () {
                    alert('Failed to fetch employee salary details.');
                }
            });
        } else {
            // Reset fields if no employee is selected
            $('#full_name, #basic, #budget_allowance, #transport_allowance, #attendance_allowance, #phone_allowance, #car_allowance, #production_bonus, #gross_salary').val('');
        }
    });

// $('#monthSelector').on('change', function () {
//     let month = $(this).val(); // Get the selected month
//     let id = $('#employee_id').val(); // Get employee ID

//     if (month && id) {
//         $.ajax({
//             url: `/employees/${id}/no-pay/${month}`,
//             type: 'GET',
//             success: function (data) {

//                 if (data.error) {
//                     alert(data.error);
//                     return;
//                 }

//                 // Fill the no pay field
//                 $('#no_pay').val(data.no_pay_amount);
//                 calculateGrossSalary();
//             },
//             error: function (xhr, status, error) {
//                 alert('Failed to fetch no pay details.');
//             }
//         });
//     } else {
//         console.warn("⚠️ Month or Employee ID not selected");
//     }
// });




</script>
@endsection

