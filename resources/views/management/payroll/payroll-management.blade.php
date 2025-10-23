@extends('layouts.dashboard-layout')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


@section('title', 'Payroll Management')

@section('content')

@if(session('success'))
<script>

    document.addEventListener("DOMContentLoaded", () => {
        showNotification("{{ session('success') }}");
    });

    async function showNotification(message) {
        const notification = document.getElementById('notification');
        const notificationMessage = document.getElementById('notification-message');

        // Set the message
        notificationMessage.textContent = message;

        // Slide the notification down
        setTimeout(() => {
        // Slide the notification down
        notification.style.top = '20px';

        // Hide the notification after an additional 3 seconds
        setTimeout(() => {
            notification.style.top = '-100px';
        }, 3000);
    }, 5000);

        // Optionally send the message to the backend
        try {
            const response = await fetch("{{ route('notify') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ message }),
            });

            if (!response.ok) {
                console.error('Failed to send notification:', response.statusText);
            }
        } catch (error) {
            console.error('Error sending notification:', error);
        }
    }
    </script>
            @endif
@if($errors->any())
                <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

         
<!--edit modal start -->
<!-- Edit Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="false">
    <button type="button" class="btn-close position-absolute top-0 end-0" data-bs-dismiss="modal" aria-label="Close"></button>

      <div class="modal-dialog modal-dialog-left " style="padding: 0;">
        <div class="rounded-3xl" style="padding-top: 60px;" id="editAttendanceContent">
          <!-- Close Button -->
          <!-- Dynamically loaded content will be injected here -->
          <div class="text-center py-4">
            <p>Loading...</p>
          </div>
        </div>
      </div>
    </div>

    <div class="flex flex-col items-start justify-start w-full px-2">

    <div class="w-full pt-2">
      <div class="flex items-center justify-between w-full">
        <div class="flex ">
        <p class="md:text-6xl text-4xl font-bold text-black nunito-">Payroll</p>
        </div>
        <div class="flex items-center space-x-4">


        <a href="{{ route('payroll.saturday-roster.index') }}" class="flex items-center justify-center nunito- space-x-2 px-6 py-2 text-[#184E77] border-2 border-[#184E77] md:text-2xl text-xl bg-white rounded-xl shadow-sm hover:bg-[#184E77] hover:text-white">
            <span class="text-2xl"><i class="ri-calendar-event-line"></i></span>
            <span>Saturday Roster</span>
        </a>


        <!-- Filter Button -->


        <!-- Add record Button -->
        <button class="flex items-center justify-center nunito- space-x-2 px-8 py-2 text-white md:text-2xl text-xl bg-gradient-to-r from-[#184E77] to-[#52B69A] rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
        <p class="text-3xl"><i class="ri-add-fill"></i></p>
            <a href="{{ route('payroll.create') }}" >Add Record</a>
        </button>

        </div>



      </div>


    <!--   <div class="w-1/5 align-right flex items-right justify-center nunito- space-x-2 px-8 py-2 text-white text-2xl bg-gradient-to-r from-[#184E77] to-[#52B69A] rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
        <button id="print-table">
            Print Records
        </button>
    </div> -->
    </div>


    <nav class="flex py-3" aria-label="Breadcrumb">
      <ol class="inline-flex items-center space-x-1 md:space-x-3 nunito-">
        <li class="inline-flex items-center">
          <a href="#" class="inline-flex items-center text-3xl font-medium text-[#00000080] hover:text-blue-600">
          Payrolls
          </a>
        </li>
        <li>
          <div class="flex items-center">
            <p class="text-[#00000080] text-3xl"><i class="ri-arrow-right-wide-line"></i></p>
            <a href="#" class="ml-1 font-medium text-[#00000080] text-3xl hover:text-blue-600">Employee payrolls</a>
          </div>
        </li>
      </ol>
    </nav>


    <div class="w-full flex justify-end items-end pt-2 pb-2">
      <button id="print-table" class="flex items-center justify-center space-x-2 px-6 py-2 ml-4 text-[#184E77] border-2 border-[#184E77] text-2xl bg-white rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
            <span>Generate Report</span>
        </button>

        <button id="Export_spreadsheet" class="flex items-center justify-center space-x-2 px-6 py-2 ml-4 text-[#184E77] border-2 border-[#184E77] text-2xl bg-white rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
            <span>Export Spreadsheet</span>
        </button>
        
        <button id="Export_bank_document" class="flex items-center justify-center space-x-2 px-6 py-2 ml-4 text-[#184E77] border-2 border-[#184E77] text-2xl bg-white rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
            <span>Export Bank Document</span>
        </button>
        
        <button id="Generate_payslips" class="flex items-center justify-center space-x-2 px-6 py-2 ml-4 text-[#184E77] border-2 border-[#184E77] text-2xl bg-white rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
            <span>Generate Payslips</span>
        </button>

        <button id="Export_payslips" class="flex items-center justify-center space-x-2 px-6 py-2 ml-4 text-[#184E77] border-2 border-[#184E77] text-2xl bg-white rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
            <span>Export Payslips (ZIP)</span>
        </button>
        <button id="Export_PDF" class="flex items-center justify-center space-x-2 px-6 py-2 ml-4 text-[#184E77] border-2 border-[#184E77] text-2xl bg-white rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
            <span>Export PDF</span>
        </button>



      </div>

      <div class="w-full flex justify-between items-center md:space-x-0 space-x-8 py-2">

    <!-- Search Bar on the Left -->
    <div class="w-[300px]">
      <input
      id="custom-search-input"
        type="text"
        placeholder="Search..."
        class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#184E77] focus:border-[#184E77]"
      />
    </div>

    <!-- Calendar Input on the Right -->
    <div class="relative w-[300px] nunito-">
      <button
        id="calendarButton"
        class="flex items-center justify-between w-full px-4 py-2 text-left text-black border-2 border-[#184E77] rounded-xl shadow-sm hover:bg-[#f0f8ff]"
      >
        <div class="flex items-center space-x-4">
          <span class="iconify text-[#184E77]" data-icon="mdi:calendar-outline" style="font-size: 20px;"></span>
          <div>
            <span class="text-sm text-[#184E77]">Select a day</span>
            <p id="selectedDate" class="text-lg font-bold">13.03.2021</p>
          </div>
        </div>
        <span class="iconify text-black" data-icon="mdi:chevron-down"></span>
      </button>

      <!-- Hidden Calendar Input -->
      <input
        id="calendarInput"
        type="text"
        class="absolute z-10 opacity-0 pointer-events-none"
      />
    </div>


  <!-- Month Selector Input -->
  <div class="relative w-[300px] nunito-">
    {{-- <button
        id="monthButton"
        class="flex items-center justify-between w-full px-4 py-2 text-left text-black border-2 border-[#184E77] rounded-xl shadow-sm hover:bg-[#f0f8ff]"
    > --}}
        {{-- <div class="flex items-center space-x-4">
            <span class="iconify text-[#184E77]" data-icon="mdi:calendar-month-outline" style="font-size: 20px;"></span> --}}

            <div>
                <span class="text-sm text-[#184E77]">Select a Month</span>
                {{-- <p id="selectedMonth" class="text-lg font-bold">03.2021</p> --}}
                <!-- Add this in your HTML, possibly near your existing month-related elements -->


                <input
                    type="month"
                    id="monthSelector"
                    class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#184E77] focus:border-[#184E77]"
                >

            </div>
        {{-- </div> --}}
        {{-- <span class="iconify text-black" data-icon="mdi:chevron-down"></span> --}}
    </button>

    <!-- Hidden Month Input -->
    <input
        id="monthInput"
        type="text"
        class="absolute z-10 opacity-0 pointer-events-none"
    />
</div>

    </div>



<!-- Payroll Table -->
<div class="overflow-x-auto" style="width:100%;" >
    <table id="attendance-table" class="nunito border-separate" style="border-spacing: 0 12px;">
        <thead>
            <tr class="bg-white">
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Employee</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Known Name</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">EPF No</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Basic Salary</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Budget Allowance</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Gross Salary</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Transport Allowance</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Attendance Allowance</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Phone Allowance</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Production Bonus</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Car Allowance</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Loan Payment</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">OT Payment</th>

                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Total Earnings</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">EPF 8%</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">EPF 12%</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">ETF 3%</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Advance Payment</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Stamp Duty</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">No Pay</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Total Deductions</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Net Salary</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Loan Balance</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Pay Date</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Pay Month</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Status</th>
                <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payrolls as $record)
            <tr>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ $record->employee_name }} {{ $record->employee_id }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ $record->known_name }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ $record->epf_no }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->basic, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->budget_allowance, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->gross_salary, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->transport_allowance, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->attendance_allowance, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->phone_allowance, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->production_bonus, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->car_allowance, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->loan_payment, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->ot_payment, 2) }}</td>

                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->total_earnings, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->epf_8_percent, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->epf_12_percent, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->etf_3_percent, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->advance_payment, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->stamp_duty, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->no_pay, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->total_deductions, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->net_salary, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ number_format($record->loan_balance, 2) }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ $record->pay_date }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ $record->payed_month }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">{{ $record->status }}</td>
                <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">
                    <!-- Actions -->
                    <button onclick="openViewModal({{ $record->id }})" class="text-blue-500">View</button>

                     <a href="{{ route('payroll.edit', ['id' => $record->id]) }}" >  <button class="text-green-500">Edit</button></a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>






    </div>


    <!--View record form start-->
    <div id="view-attendance-modal-container" class=" fixed inset-0 bg-black bg-opacity-50 w-full opacity-0 transition-opacity duration-300 flex justify-center items-center hidden z-50">

    <div class="w-full flex justify-center items-center rounded-3xl">
      <!-- Close Button -->
      <div id="modal-container" class="w-1/3 flex flex-col justify-start items-center relative bg-white nunito- p-2 rounded-3xl bg-gradient-to-r from-[#184E77] to-[#52B69A]">
        <button onclick="closeViewModal()" id="close-button" class="absolute top-4 right-4 text-black font-medium rounded-full text-xl p-4 inline-flex items-center">
            <span class="iconify" data-icon="ic:baseline-close" style="width: 16px; height: 16px;"></span>
        </button>
        <div class="w-full flex flex-col justify-start items-center bg-white p-8 rounded-3xl space-y-8">
          <div class="flex flex-col justify-center items-center space-y-4">
            <p class="text-5xl text-black font-bold">Incident</p>
            <p class="text-3xl text-[#00000080]">Enter the Information about Incidents</p>
          </div>
            <div class="w-full flex mx-auto pb-8 pt-8 px-16">
                <div class="w-1/2 flex flex-col space-y-8">
                    <!-- Employee ID -->
                <p class="text-xl font-bold text-black">Employee Name :</p>
                <p class="text-xl font-bold text-black">Employee ID :</p>
                <p class="text-xl font-bold text-black">Incident Type :</p>
                <p class="text-xl font-bold text-black">Incident Date :</p>
                <p class="text-xl font-bold text-black">Resolution Status :</p>
                <p class="text-xl font-bold text-black">Description :</p>
                <p class="text-xl font-bold text-black">Added on :</p>
                <p class="text-xl font-bold text-black">Supporting Documents : </p>
                </div>
                <div class="w-1/2 flex flex-col space-y-8">
                <p class="text-xl font-bold text-black modal-employee-name"></p>
    <p class="text-xl font-bold text-black modal-employee-id"></p>
    <p class="text-xl font-bold text-black modal-incident-type"></p>
    <p class="text-xl font-bold text-black modal-incident-date"></p>
    <p class="text-xl font-bold text-black modal-resolution-status"></p>
    <p class="text-xl font-bold text-black modal-description"></p>
    <p class="text-xl font-bold text-black modal-created"></p>
    <p class="text-xl font-bold text-black modal-supporting-document"></p>


                </div>

            </div>
            <!-- Submit Button -->
            <div class="w-full text-center px-16">
              <button
                type="submit"
                class="w-full bg-gradient-to-r from-[#184E77] to-[#52B69A] text-xl text-white font-bold py-4 px-4 rounded-xl hover:from-blue-600 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
              Done
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>


    <iframe id="paysheet-pdf" style="display: none; width: 100%; height: 500px;"></iframe>
    </div>
    <!--View record form end-->

<script>

const exportBankBtn = document.getElementById('Export_bank_document');
if (exportBankBtn) {
    exportBankBtn.addEventListener('click', () => {
        const month = document.getElementById('monthSelector').value;

        if (!month) {
            alert('Please select a month before exporting.');
            return;
        }
        alert(`${window.location.origin}`);
        window.location.href = `${window.location.origin}/dashboard/payroll/payroll/export-bank-details?selected_month=${month}`;
    });
}

function formatDuration(seconds) {
    // Calculate hours, minutes, and seconds
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;

    // Format as HH:MM:SS
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
}


// Document hover tooltip functionality
document.addEventListener('DOMContentLoaded', () => {
    const documentLinks = document.querySelectorAll('.document-link');

    documentLinks.forEach(link => {
        // Create tooltip element
        const tooltip = document.createElement('div');
        tooltip.classList.add('document-tooltip');
        tooltip.style.display = 'none';
        tooltip.style.position = 'absolute';
        tooltip.style.backgroundColor = 'black';
        tooltip.style.color = 'white';
        tooltip.style.padding = '5px';
        tooltip.style.borderRadius = '4px';
        tooltip.style.zIndex = '100';
        tooltip.style.maxWidth = '300px';
        tooltip.style.wordWrap = 'break-word';

        // Set full document name
        tooltip.textContent = link.getAttribute('data-full-name') || link.textContent;

        // Append tooltip to body
        document.body.appendChild(tooltip);

        // Event listeners for hover
        link.addEventListener('mouseenter', (e) => {
            const rect = link.getBoundingClientRect();
            tooltip.style.top = `${rect.bottom + window.scrollY + 5}px`;
            tooltip.style.left = `${rect.left + window.scrollX}px`;
            tooltip.style.display = 'block';
        });

        link.addEventListener('mouseleave', () => {
            tooltip.style.display = 'none';
        });
    });
});



  function toggleGradientText() {
    const textElement = document.getElementById('payrollText');
    if (textElement.classList.contains('text-black')) {
      // Apply gradient
      textElement.classList.remove('text-black');
      textElement.classList.add('bg-gradient-to-r', 'from-[#184E77]', 'to-[#52B69A]', 'text-transparent', 'bg-clip-text');
    } else {
      // Revert to black
      textElement.classList.add('text-black');
      textElement.classList.remove('bg-gradient-to-r', 'from-[#184E77]', 'to-[#52B69A]', 'text-transparent', 'bg-clip-text');
    }
  }

  function toggleMenu(menuId) {
    const menu = document.getElementById(menuId);
    menu.classList.toggle('hidden');
  }

  // Wrap in IIFE to avoid global scope pollution
  (function() {
    const textElements = document.querySelectorAll('span.text-xl');

    textElements.forEach((element) => {
        element.addEventListener('click', function () {
            // Reset all text elements to black
            textElements.forEach((el) => {
                el.classList.remove('bg-gradient-to-r', 'from-[#184E77]', 'to-[#52B69A]', 'text-transparent', 'bg-clip-text');
                el.classList.add('text-black');
            });

            // Apply gradient to the clicked element
            this.classList.remove('text-black');
            this.classList.add('bg-gradient-to-r', 'from-[#184E77]', 'to-[#52B69A]', 'text-transparent', 'bg-clip-text');
        });
    });
  })();

// Initialize Flatpickr
$(document).ready(function () {
    // Initialize DataTable
    var table = $('#attendance-table').DataTable({
      dom: '<"top"f>rt<"bottom"p><"clear">', // Custom layout: search box on top, pagination on bottom
    paging: true,
    pageLength: 10,
    pagingType: 'simple',
    order: [[2, 'asc']], 
    searching: true,
        buttons: [
            {
                extend: 'print',
                text: 'Print Table',
                title: 'Payroll Report', // Optional title
                exportOptions: {
                    columns: ':visible', // Export all visible columns
                }
            }
        ],
        language: {
        paginate: {
            previous: '<span class="custom-prev-button"><i class="ri-arrow-left-s-line"></i> Prev</span>',
            next: '<span class="custom-next-button">Next <i class="ri-arrow-right-s-line"></i></span>',
        },
    },



    });

    $('#monthSelector').on('change', function() {
        var selectedMonth = $(this).val(); // Gets value in YYYY-MM format
console.log(selectedMonth);
        // Custom filtering function
       // const searchTerm = $(this).val(); // Get the value of the search input
       table.columns(24).search(selectedMonth).draw();
    });

    // Attach Print functionality to the custom button
    $('#print-table').on('click', function () {
        table.button('.buttons-print').trigger(); // Trigger the print button
    });

// Attach a keyup event listener to the custom search input element
$('#custom-search-input').on('keyup', function () {
    const searchTerm = $(this).val(); // Get the value of the search input
    table.search(searchTerm).draw(); // Trigger the DataTable search with the entered term
});


//pagination controls
table.on('draw', function () {
    const pagination = $('.dataTables_paginate');
    const pageInfo = table.page.info();

    // Clear existing pagination
    pagination.html('');

    // Build custom pagination
    let customPagination = `
        <div class="flex items-center justify-end w-full space-x-4 mt-8">
            <button class="flex items-center px-2 py-1 text-gray-500 hover:text-black focus:outline-none ${
                pageInfo.page === 0 ? 'cursor-not-allowed text-gray-400' : ''
            }" data-action="prev" ${pageInfo.page === 0 ? 'disabled' : ''}>
                <i class="ri-arrow-left-s-line"></i>
                <span class="ml-1">Prev</span>
            </button>
            <div class="flex items-center space-x-2">
    `;

    for (let i = 0; i < pageInfo.pages; i++) {
        customPagination += `
            <button class="flex items-center justify-center w-8 h-8 ${
                i === pageInfo.page
                    ? 'font-bold text-black bg-[#52B69A80] rounded-full focus:outline-none'
                    : 'text-black rounded-full hover:bg-gray-200 focus:outline-none'
            }" data-page="${i}">
                ${i + 1}
            </button>
        `;
    }

    customPagination += `
            </div>
            <button class="flex items-center px-2 py-1 text-gray-500 hover:text-black focus:outline-none ${
                pageInfo.page + 1 === pageInfo.pages
                    ? 'cursor-not-allowed text-gray-400'
                    : ''
            }" data-action="next" ${
        pageInfo.page + 1 === pageInfo.pages ? 'disabled' : ''
    }>
                <span class="mr-1">Next</span>
                <i class="ri-arrow-right-s-line"></i>
            </button>
        </div>
    `;

    pagination.html(customPagination);
});

// Handle custom pagination button clicks
$(document).on('click', '.dataTables_paginate button', function () {
    const action = $(this).data('action');
    const page = $(this).data('page');

    if (action === 'prev') {
        table.page('previous').draw('page');
    } else if (action === 'next') {
        table.page('next').draw('page');
    } else if (page !== undefined) {
        table.page(page).draw('page');
    }
});
//pagination controls end




    // Initialize Flatpickr for Date Selection
    const calendarInput = document.getElementById("calendarInput");
    const selectedDate = document.getElementById("selectedDate");
    const calendarButton = document.getElementById("calendarButton");

    if (calendarInput && selectedDate && calendarButton) {
        flatpickr(calendarInput, {
            dateFormat: "Y-m-d",
            onChange: function (selectedDates, dateStr) {
                selectedDate.textContent = dateStr;
                // Filter DataTable by selected date
                table.search(dateStr).draw();
            },
        });

        calendarButton.addEventListener("click", () => {
            console.log("Calendar-month");
            calendarInput._flatpickr.open();
        });
    }


    const monthInput = document.getElementById("monthInput");
        const selectedMonthDisplay = document.getElementById("selectedMonth");
        const selectedMonth = document.getElementById("monthInput");
        const monthButton = document.getElementById("monthButton");
       // <p id="selectedMonth" class="text-lg font-bold">03.2021</p>

        if (monthInput && monthButton) {
            flatpickr(monthInput, {
                dateFormat: "Y-m", // Month and Year
                plugins: [new monthSelectPlugin({ shorthand: true, dateFormat: "Y-m" })],
                onChange: function (selectedDates, dateStr) {
                    //console.log(dateStr);
                    if (selectedMonthDisplay) selectedMonthDisplay.textContent = dateStr;
                    if (selectedMonth) selectedMonth.textContent = dateStr;
                    // Filter DataTable by Paid Month
                    table.search(dateStr).draw();
                },
            });

            monthButton.addEventListener("click", () => {
                monthInput._flatpickr.open();
            });
        }


    table.draw('page');
});


// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('doc-files');
    const selectedMonth = () => monthInput._flatpickr?.selectedDates[0]?.toISOString().slice(0, 7) || null;

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            handleFileSelection(this);
        });
    }

    const existingFilesData = document.getElementById('existing-files-data');
    if (existingFilesData) {
        initializeExistingFiles(existingFilesData.value);
    }


    const exportSpreadsheetBtn = document.getElementById('Export_spreadsheet');
    if (exportSpreadsheetBtn) {
        exportSpreadsheetBtn.addEventListener('click', () => {
            // console.log(window.location.origin);
            const month = document.getElementById('monthSelector').value;
            // alert(month);
            if (!month) {
                alert('Please select a month before exporting.');
                return;
            }
            window.location.href = `${window.location.origin}/dashboard/payroll/payroll/export/spreadsheet?selected_month=${month}`;
        });
    }

    const generatePayslipsBtn = document.getElementById('Generate_payslips');
    if (generatePayslipsBtn) {
        generatePayslipsBtn.addEventListener('click', () => {
            // console.log(window.location.origin);
            const month = document.getElementById('monthSelector').value;
            // alert(month);
            if (!month) {
                alert('Please select a month before exporting.');
                return;
            }
            window.location.href = `${window.location.origin}/dashboard/payroll/payroll/generate/paysheets?selected_month=${month}`;
        });
    }

    const exportPayslipsBtn = document.getElementById('Export_payslips');
    if (exportPayslipsBtn) {
        exportPayslipsBtn.addEventListener('click', () => {
            const month = document.getElementById('monthSelector').value;
            if (!month) {
                alert('Please select a month before exporting.');
                return;
            }
            window.location.href = `${window.location.origin}/dashboard/payroll/payroll/export/paysheets?selected_month=${month}`;
        });
    }

    const exportPDFBtn = document.getElementById('Export_PDF');
    if (exportPDFBtn) {
        exportPDFBtn.addEventListener('click', () => {
            const month = document.getElementById('monthSelector').value;
            if (!month) {
                alert('Please select a month before exporting.');
                return;
            }
            // Include the full prefix path
            window.location.href = `${window.location.origin}/dashboard/payroll/payroll/export/pdf?selected_month=${month}`;
        });
    }





});

function openEditModal(id) {
    document.getElementById('edit-modal').style.display = 'block';
    document.getElementById('edit-form').onsubmit = function (event) {
        event.preventDefault();
        submitEditForm(id);
    };
}

function submitEditForm(id) {
    const formData = new FormData(document.getElementById('edit-form'));

    fetch(`/payroll/${id}/update-advance-loan`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error updating record');
            }
        });
}
  let selectedAddFiles = new DataTransfer();

  function openAddModal() {
    // Initialize the modal
    const modal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
    modal.show();

    const modalContent = document.getElementById('editAttendanceContent');
    modalContent.innerHTML = '<div class="text-center"><p>Loading...</p></div>';

    // Fetch content from the server
    fetch("{{ route('incident.create') }}")
        .then(response => response.text())
        .then(html => {
            modalContent.innerHTML = html;

            // Reinitialize any necessary scripts or event listeners
            setTimeout(() => {
                // Ensure file input works after content is loaded
                const docFilesInput = document.getElementById('doc-files');
                if (docFilesInput) {
                    docFilesInput.addEventListener('change', function() {
                      handleAddFileSelection(this);
                    });
                }
            }, 100);
        })
        .catch(error => {
            modalContent.innerHTML = '<div class="text-center py-4 text-danger"><p>Error loading content. Please try again later.</p></div>';
            console.error('Error:', error);
        });
}

function openViewModal(id) {
    const iframe = document.getElementById('paysheet-pdf');
    iframe.src = `/dashboard/payroll/payroll/${id}/view-paysheet`;
   // document.getElementById('view-modal').style.display = 'block';
}

function closeViewModal() {
  const modal = document.getElementById('view-attendance-modal-container');
  modal.classList.add('opacity-0', 'scale-95'); // Apply transition effects
  setTimeout(() => {
    modal.classList.add('hidden'); // Hide modal after transition
  }, 300); // Match the transition duration
}



let existingFilesList = [];
let selectedFiles = new DataTransfer();

// Initialize existing files when modal opens
function initializeExistingFiles(existingFilesJson) {
  console.log(existingFilesJson);
    try {

        existingFilesList = JSON.parse(existingFilesJson || '[]');

        refreshFileList();
    } catch (error) {
        console.error('Error initializing existing files:', error);
    }
}

function handleAddFileSelection(input) {
    const fileList = document.getElementById('file-list-items');
    const hiddenFilesInput = document.getElementById('hidden-files');
    if (!fileList || !hiddenFilesInput) {
        console.error('Required DOM elements not found.');
        return;
    }

    const files = Array.from(input.files);

    // Add newly selected files to the DataTransfer object
    files.forEach(file => {
        selectedAddFiles.items.add(file);
    });

    // Update the displayed list
    refreshAddFileList();

    // Update the hidden input's files
    hiddenFilesInput.files = selectedAddFiles.files;

    // Clear the original file input so the same file can be reselected if needed
    input.value = '';
}


function refreshAddFileList() {
    const fileListDisplay = document.getElementById('file-list-items');
    if (!fileListDisplay) {
        console.error('File list display element not found.');
        return;
    }

    // Clear the list before re-rendering
    fileListDisplay.innerHTML = '';

    // Display all files in `selectedAddFiles`
    Array.from(selectedAddFiles.files).forEach(file => {
        addFileToDisplayAdd(file.name, null);
    });
}


function addFileToDisplayAdd(fileName, filePath) {
    const fileListDisplay = document.getElementById('file-list-items');
    if (!fileListDisplay) {
        console.error('File list display element not found.');
        return;
    }

    const listItem = document.createElement('li');
    listItem.className = 'flex items-center space-x-2 mb-2';

    listItem.innerHTML = filePath
        ? `<span class="text-2xl">
            <i class="ri-file-pdf-2-fill"></i>
        </span>
        <a href="/storage/${filePath}" target="_blank" class="text-blue-500 underline">${fileName}</a>
        <span onclick="removeFileAdd('${filePath}', true)" class="text-red-500 cursor-pointer ml-2">✖</span>`
        : `<span class="text-2xl">
            <i class="ri-file-pdf-2-fill"></i>
        </span>
        <span class="text-blue-500">${fileName}</span>
        <span onclick="removeFileAdd('${fileName}', false)" class="text-red-500 cursor-pointer ml-2">✖</span>`;

    fileListDisplay.appendChild(listItem);
}


// The removeFile function to remove files from selectedAddFiles
function removeFileAdd(fileIdentifier, isFilePath) {
    let updatedFiles;

    if (isFilePath) {
        // If it's a file path, remove the file from DataTransfer using the file path
        updatedFiles = Array.from(selectedAddFiles.files).filter(file => file.name !== fileIdentifier);
    } else {
        // If it's the file name, remove by name
        updatedFiles = Array.from(selectedAddFiles.files).filter(file => file.name !== fileIdentifier);
    }

    // Reset the DataTransfer object with updated files
    selectedAddFiles = new DataTransfer();
    updatedFiles.forEach(file => selectedAddFiles.items.add(file));

    // Update the displayed file list
    refreshAddFileList();

    // Update the hidden input's files to reflect the changes
    const hiddenFilesInput = document.getElementById('hidden-files');
    if (hiddenFilesInput) {
        hiddenFilesInput.files = selectedAddFiles.files;
    }
}


// Handle new file selection
function handleFileSelection(input) {
    const files = Array.from(input.files);

    files.forEach(file => {
        // Check for duplicates
        const isDuplicateNew = Array.from(selectedFiles.files)
            .some(existing => existing.name === file.name);
        const isDuplicateExisting = existingFilesList
            .some(existing => existing.includes(file.name));

        if (isDuplicateNew || isDuplicateExisting) {
            alert(`File "${file.name}" is already selected.`);
            return;
        }

        selectedFiles.items.add(file);
    });

    refreshFileList();
    input.value = ''; // Clear the input for future selections
}

// Refresh the complete file list display
function refreshFileList() {
    const fileListDisplay = document.getElementById('file-list-items');
    fileListDisplay.innerHTML = '';

    // Display existing files
    existingFilesList.forEach(filePath => {
        const fileName = filePath.split('/').pop();
        addFileToDisplay(fileName, filePath, true);
    });

    // Display newly selected files
    Array.from(selectedFiles.files).forEach(file => {
        addFileToDisplay(file.name, null, false);
    });

    // Update hidden inputs
    updateHiddenInputs();
}

// Update all hidden inputs
function updateHiddenInputs() {
    // Update existing files input
    const existingFilesInput = document.getElementById('existing-files');
    existingFilesInput.value = JSON.stringify(existingFilesList);

    // Update new files input
    const hiddenFilesInput = document.getElementById('hidden-files');
    hiddenFilesInput.files = selectedFiles.files;
}


// Add file to display list
function addFileToDisplay(fileName, filePath, isExisting) {
    const fileListDisplay = document.getElementById('file-list-items');
    const listItem = document.createElement('li');
    listItem.className = 'flex items-center space-x-2 mb-2';

    listItem.innerHTML = isExisting ?
        `<span class="text-2xl">
            <i class="ri-file-pdf-2-fill"></i>
        </span>
        <a href="/storage/${filePath}" target="_blank" class="text-blue-500 underline">${fileName}</a>
        <span onclick="removeFile('${filePath}', true)" class="text-red-500 cursor-pointer ml-2">✖</span>` :
        `<span class="text-2xl">
            <i class="ri-file-pdf-2-fill"></i>
        </span>
        <span class="text-blue-500">${fileName}</span>
        <span onclick="removeFile('${fileName}', false)" class="text-red-500 cursor-pointer ml-2">✖</span>`;

    fileListDisplay.appendChild(listItem);
}

// Remove file (both existing and new)
function removeFile(identifier, isExisting) {
    if (isExisting) {
        existingFilesList = existingFilesList.filter(path => path !== identifier);
    } else {
        const newDataTransfer = new DataTransfer();
        Array.from(selectedFiles.files).forEach(file => {
            if (file.name !== identifier) {
                newDataTransfer.items.add(file);
            }
        });
        selectedFiles = newDataTransfer;
    }

    refreshFileList();
}



// Select all dropdown triggers
const dropdownTriggers = document.querySelectorAll('.dropdown-trigger');

// Event listener for dropdown buttons
dropdownTriggers.forEach(button => {
    button.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent the click from propagating to the window

        const dropdownMenu = button.nextElementSibling; // The dropdown menu after the button

        // Close all other dropdowns
        document.querySelectorAll('#dropdown-menu').forEach(menu => {
            if (menu !== dropdownMenu) {
                menu.classList.add('hidden');
            }
        });

        // Toggle the current dropdown menu
        dropdownMenu.classList.toggle('hidden');
    });
});

// Close dropdowns when clicking anywhere outside
window.addEventListener('click', () => {

    document.querySelectorAll('#dropdown-menu').forEach(menu => {
        menu.classList.add('hidden');
    });
});
</script>


@endsection
