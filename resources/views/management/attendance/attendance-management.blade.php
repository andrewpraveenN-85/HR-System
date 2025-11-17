@extends('layouts.dashboard-layout')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


@section('title', 'Attendance Management')

@section('content')

<style>
/* Custom pagination styling */
.dataTables_paginate {
    margin-top: 1rem !important;
    display: flex !important;
    justify-content: center !important;
    width: 100% !important;
}

.pagination-btn, .page-number-btn {
    transition: all 0.2s ease-in-out;
    border-radius: 0.375rem;
}

.pagination-btn:hover:not(:disabled):not(.opacity-50), 
.page-number-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.pagination-btn:first-child {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.pagination-btn:last-child {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.page-number-btn {
    border-radius: 0.375rem !important;
    margin: 0 1px;
}

/* Hide default DataTables pagination info */
.dataTables_info {
    display: none !important;
}

/* Ensure table wrapper has proper spacing */
.dataTables_wrapper {
    margin-bottom: 2rem;
}
</style>

@if(session('success') )
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const successMessage = "{{ session('success') }}";
        const errorMessage = "{{ session('error') }}";

        if (successMessage) {
            showNotification(successMessage, 'success');
        }
        if (errorMessage) {
            showNotification(errorMessage, 'error');
        }
    });

    async function showNotification(message) {
        const notification = document.getElementById('notification');
        const notificationMessage = document.getElementById('notification-message');

        // Set the message
        notificationMessage.textContent = message;
        if (type === 'success') {
            notification.style.backgroundColor = '#4CAF50'; // Green for success
        } else if (type === 'error') {
            notification.style.backgroundColor = '#F44336'; // Red for error
        }
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
            @if(session('error'))
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
                <ul>
                    {{session('error')}}
                </ul>
            </div>
        @endif

<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="false">
  <button type="button" class="btn-close position-absolute top-0 end-0" data-bs-dismiss="modal" aria-label="Close"></button>

    <div class="modal-dialog modal-dialog-left" style="padding: 0;">
      <div class="rounded-3xl" style="padding-top: 60px;" id="editAttendanceContent">
        <!-- Close Button -->
        <!-- Dynamically loaded content will be injected here -->
        <div class="text-center py-4">
          <p>Loading...</p>
        </div>
      </div>
    </div>
  </div>

  <!--edit modal end -->
  <div class="flex flex-col items-start justify-start w-full px-2">

  <div class="w-full pt-1">
    <div class="flex items-center justify-between w-full">
      <div class="flex ">
      <p class="md:text-6xl text-4xl font-bold text-black nunito-">Attendance</p>
      </div>
      <div class="flex items-center space-x-4">


      <!-- Filter Button -->

      <!-- Add record Button -->
      <button class="flex items-center justify-center nunito- space-x-2 px-8 py-2 text-white md:text-2xl text-xl bg-gradient-to-r from-[#184E77] to-[#52B69A] rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
      <p class="text-3xl"><i class="ri-add-fill"></i></p>
          <a href="#"
          onclick="openAddModal()">Add Record</a>

      </button>

      </div>



    </div>


  </div>

  <nav class="flex py-3" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3 nunito-">
      <li class="inline-flex items-center">
        <a href="#" class="inline-flex items-center text-3xl font-medium text-[#00000080] hover:text-blue-600">
        Attendance
        </a>
      </li>
      <li>
        <div class="flex items-center">
          <p class="text-[#00000080] text-3xl"><i class="ri-arrow-right-wide-line"></i></p>
          <a href="#" class="ml-1 font-medium text-[#00000080] text-3xl hover:text-blue-600">Employee Attendance</a>
        </div>
      </li>
    </ol>
  </nav>

  <div class="w-full flex justify-end items-end pt-2 pb-2 gap-4">
    <button id="import-attendance-btn" class="flex items-center justify-center space-x-2 px-6 py-2 text-white bg-[#52B69A] border-2 border-[#52B69A] text-2xl rounded-xl shadow-sm hover:bg-[#3A9D84]">
          <span class="iconify" data-icon="mdi:file-excel" style="font-size: 24px;"></span>
          <span>Import Attendance</span>
      </button>
    <button id="print-table" class="flex items-center justify-center space-x-2 px-6 py-2 text-[#184E77] border-2 border-[#184E77] text-2xl bg-white rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
          <span>Generate Report</span>
      </button>
    </div>
  <div class="w-full flex justify-end items-center">

  <div class="relative w-[300px] nunito-">
    <!-- Calendar Button -->
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

    <!-- Hidden Input for Flatpickr -->
    <input
      id="calendarInput"
      type="text"
      class="absolute z-10 opacity-0 pointer-events-none"
    />
    <button
    id="resetCalendarButton"
    class="px-4 py-2 text-white bg-red-600 rounded-xl shadow-sm hover:bg-red-700 mt-2"
  >
    Reset
  </button>
  </div>

  </div>
  <div class="flex w-1/3 align-left">
    <input id="custom-search-input" type="text" placeholder="Search record here" class="w-full px-4 py-2 border-2 border-[#00000080] text-2xl text-[#00000080] rounded-xl"/>
    </div>
  <table class="w-full nunito- border-separate" style="border-spacing: 0 12px; width: 100%;" id="attendance-table">
    <thead class="w-full">
      <tr class="bg-white">
        <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Employee</th>
        <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Date</th>
        <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Check In & Out</th>
        <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Total Work hours</th>
        <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">O/T hours</th>
        <th class="text-xl text-black font-bold px-4 py-2 text-left align-middle">Late By</th>
        <th class="text-xl w-1/2 text-black font-bold px-4 py-2 text-center align-left hidden">Actions</th>

      </tr>
    </thead>
    <tbody>
    @foreach ($attendance as $record)
    <tr class="hover:shadow-popup hover:rounded-xl hover:scale-101 hover:bg-white transition duration-300 hover:border-r-4 hover:border-b-4 hover:border-gray-500 "
  >
    <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966] rounded-l-xl font-bold">
    {{ $record->employee->full_name }}
          <p class="text-sm">{{ $record->employee->employee_id }}</p>
    </td>
    <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">
    {{ $record->date }}
    </td>
    <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966] font-bold">
    <span class="text-[#EB5E12]">{{ $record->clock_in_time }}</span>------<span class="text-[#FFBF00]"> {{ $record->clock_out_time }}</span>
    </td>
    <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">
    {{ $record->total_work_hours }}
    </td>
    <td class="text-xl text-black px-4 py-2 text-left align-middle bg-[#D9D9D966]">
    {{ $record->overtime_hours }}
    </td>
    <td class="text-xl text-[#3569C3] px-4 py-2 text-left align-middle bg-[#D9D9D966]">
    {{ $record->late_by }}
    </td>
    <td class="text-left align-left  bg-[#D9D9D966] rounded-r-xl">



       <!-- Start dropdown -->

  <div class="relative inline-block text-center">
          <!-- Toggle Button -->
          <button id="dropdownButton"  class="dropdown-trigger p-2 rounded hover:bg-gray-300">
          <span class="iconify" data-icon="qlementine-icons:menu-dots-16" style="width: 16px; height: 16px;"></span>
      </button>

          <!-- Dropdown Menu -->
          <div id="dropdown-menu"
               class="absolute top-0 right-3 mt-12 w-30 bg-white border border-gray-100 rounded-xl shadow-lg hidden z-10">
              <ul class=" text-gray-700">
                  <li><a href="#" class="block px-2 py-2 hover:bg-gray-100" onclick="openViewModal({
          employeeName: '{{ $record->employee->full_name }}',
          employeeId: '{{ $record->employee->id }}',
          checkIn: '{{ $record->clock_in_time }}',
          checkOut: '{{ $record->clock_out_time }}',
          totalWorkHours: '{{ $record->total_work_hours }}',
          overtimeHours: '{{ $record->overtime_hours }}',
          lateBy: '{{ $record->late_by }}',
          date: '{{ $record->date }}'

              })">
              View</a></li>
                  <li class="cursor-pointer" ><a onclick="openEditModal({{ $record->id}})"  class="block px-2 py-2 hover:bg-gray-100">Edit</a></li>
                  <li class="bg-red">

                    <form action="{{ route('attendance.destroy', ['id' => $record->id]) }}" method="POST" class="m-0 p-0 delete-attendance-form" data-employee-name="{{ $record->employee->full_name }}" data-attendance-date="{{ $record->date }}">
  @csrf
              @method('DELETE')
              <button type="submit"
      class="block px-2 py-2 w-full rounded-xl bg-red-600 text-white hover:bg-red-700">
      Delete
  </button>
              </form></li>
              </ul>
          </div>
      </div>

       <!-- End dropdown -->


    </td>
  </tr>
  @endforeach
    </tbody>
  </table>


  </div>


  <!--View record form start-->
  <div id="view-attendance-modal-container" class=" fixed inset-0 bg-black bg-opacity-50 w-full opacity-0 transition-opacity duration-300 flex justify-center items-center hidden z-50">

  <div class="w-full flex justify-center items-center rounded-3xl">
    <!-- Close Button -->
    <div id="modal-container" class="w-1/3 flex flex-col justify-start items-center relative bg-white nunito- p-2 rounded-3xl bg-gradient-to-r from-[#184E77] to-[#52B69A]">
      <button onclick="closeAddNewModal()" id="close-button" class="absolute top-4 right-4 text-black font-medium rounded-full text-xl p-4 inline-flex items-center">
          <span class="iconify" data-icon="ic:baseline-close" style="width: 16px; height: 16px;"></span>
      </button>
      <div class="w-full flex flex-col justify-start items-center bg-white p-8 rounded-3xl space-y-8">
        <div class="flex flex-col justify-center items-center space-y-4">
          <p class="text-5xl text-black font-bold">Attendance</p>
          <p class="text-3xl text-[#00000080]">Enter the Information about Incidents</p>
        </div>
          <div class="w-full flex mx-auto pb-8 pt-8 px-16">
              <div class="w-1/2 flex flex-col space-y-8">
                  <!-- Employee ID -->
              <p class="text-xl font-bold text-black">Employee Name :</p>
              <p class="text-xl font-bold text-black">Employee ID :</p>
              <p class="text-xl font-bold text-black">Check In Time :</p>
              <p class="text-xl font-bold text-black">Check Out Time :</p>
              <p class="text-xl font-bold text-black">Total Work Hours :</p>
              <p class="text-xl font-bold text-black">O/T Hours :</p>
              <p class="text-xl font-bold text-black">Late By : </p>
              <p class="text-xl font-bold text-black">Date :</p>
              </div>
              <div class="w-1/2 flex flex-col space-y-8">
              <p class="text-xl font-bold text-black modal-employee-name"></p>
  <p class="text-xl font-bold text-black modal-employee-id"></p>
  <p class="text-xl font-bold text-black modal-check-in"></p>
  <p class="text-xl font-bold text-black modal-check-out"></p>
  <p class="text-xl font-bold text-black modal-total-work-hours"></p>
  <p class="text-xl font-bold text-black modal-overtime-hours"></p>
  <p class="text-xl font-bold text-black modal-late-by"></p>
  <p class="text-xl font-bold text-black modal-date"></p>


              </div>

          </div>
          <!-- Submit Button -->
          <div class="w-full text-center px-16">
            <button
              onclick="closeAddNewModal()"
              class="w-full bg-gradient-to-r from-[#184E77] to-[#52B69A] text-xl text-white font-bold py-4 px-4 rounded-xl hover:from-blue-600 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
            Done
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
<!--View record form end-->

<!-- Import Attendance Modal -->
<div id="import-attendance-modal" class="fixed inset-0 bg-black bg-opacity-50 w-full opacity-0 transition-opacity duration-300 flex justify-center items-center hidden z-50">
  <div class="w-1/2 flex flex-col justify-start items-center relative bg-white nunito- p-8 rounded-3xl">
    <button id="close-import-modal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
      <span class="iconify" data-icon="mdi:close" style="font-size: 24px;"></span>
    </button>
    
    <h2 class="text-3xl font-bold text-[#184E77] mb-6">Import Attendance Records</h2>
    
    <form id="import-attendance-form" action="{{ route('attendance.import') }}" method="POST" enctype="multipart/form-data" class="w-full">
      @csrf
      
      <div class="mb-6">
        <label class="block text-xl font-semibold text-gray-700 mb-2">
          Select Excel Files (.xls or .xlsx)
        </label>
        <input 
          type="file" 
          name="attendance_files[]" 
          id="attendance_files"
          accept=".xls,.xlsx"
          multiple
          required
          class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-[#184E77]"
        />
        <p class="text-sm text-gray-500 mt-2">
          Upload one or more attendance export files from the fingerprint machine.
        </p>
        <div id="file-list" class="mt-3 text-sm text-gray-600"></div>
      </div>
      
      <div class="flex justify-end gap-4">
        <button 
          type="button" 
          id="cancel-import-btn"
          class="px-6 py-2 text-gray-700 bg-gray-200 rounded-xl hover:bg-gray-300"
        >
          Cancel
        </button>
        <button 
          type="submit" 
          class="px-6 py-2 text-white bg-[#52B69A] rounded-xl hover:bg-[#3A9D84]"
        >
          Import & Process
        </button>
      </div>
    </form>
  </div>
</div>
<!-- Import Attendance Modal End -->

<!-- Import Results Modal -->
@if(session('import_results'))
<div id="import-results-modal" class="fixed inset-0 bg-black bg-opacity-50 w-full flex justify-center items-center z-50">
  <div class="w-3/4 max-h-[90vh] overflow-y-auto flex flex-col justify-start items-center relative bg-white nunito- p-8 rounded-3xl">
    <button id="close-results-modal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
      <span class="iconify" data-icon="mdi:close" style="font-size: 24px;"></span>
    </button>
    
    <h2 class="text-3xl font-bold text-[#184E77] mb-6">Import Results</h2>
    
    @php
      $results = session('import_results');
      $dateRanges = $results['dateRanges'] ?? [];
      $filesProcessed = $results['filesProcessed'] ?? 0;
      $totalFiles = $results['totalFiles'] ?? 0;
    @endphp
    
    @if(!empty($dateRanges))
    <div class="w-full mb-6 p-4 bg-blue-50 rounded-xl">
      <p class="text-xl font-semibold text-gray-700 mb-2">
        Files Processed: {{ $filesProcessed }} of {{ $totalFiles }}
      </p>
      @foreach($dateRanges as $rangeInfo)
      <p class="text-md text-gray-600">
        <strong>{{ $rangeInfo['file'] }}:</strong> {{ $rangeInfo['range']['start']->format('Y-m-d') }} to {{ $rangeInfo['range']['end']->format('Y-m-d') }}
      </p>
      @endforeach
    </div>
    @endif
    
    <!-- Summary -->
    <div class="w-full grid grid-cols-3 gap-4 mb-6">
      <div class="p-4 bg-green-100 rounded-xl text-center">
        <p class="text-3xl font-bold text-green-700">{{ count($results['processed']) }}</p>
        <p class="text-lg text-gray-700">Processed Successfully</p>
      </div>
      <div class="p-4 bg-yellow-100 rounded-xl text-center">
        <p class="text-3xl font-bold text-yellow-700">{{ count($results['missing']) }}</p>
        <p class="text-lg text-gray-700">Missing Data</p>
      </div>
      <div class="p-4 bg-red-100 rounded-xl text-center">
        <p class="text-3xl font-bold text-red-700">{{ count($results['errors']) }}</p>
        <p class="text-lg text-gray-700">Errors</p>
      </div>
    </div>
    
    <!-- Missing Records Details -->
    @if(!empty($results['missing']))
    <div class="w-full mb-6">
      <h3 class="text-2xl font-bold text-yellow-700 mb-4">Records with Missing Data</h3>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300">
          <thead class="bg-gray-200">
            <tr>
              <th class="border border-gray-300 px-4 py-2 text-left">Employee ID</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Employee Name</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Date</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Clock In</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Clock Out</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Reason</th>
            </tr>
          </thead>
          <tbody>
            @foreach($results['missing'] as $record)
            <tr class="hover:bg-gray-50">
              <td class="border border-gray-300 px-4 py-2">{{ $record['employee_id'] }}</td>
              <td class="border border-gray-300 px-4 py-2">{{ $record['employee_name'] }}</td>
              <td class="border border-gray-300 px-4 py-2">{{ $record['date'] }}</td>
              <td class="border border-gray-300 px-4 py-2">{{ $record['clock_in'] ?? 'N/A' }}</td>
              <td class="border border-gray-300 px-4 py-2">{{ $record['clock_out'] ?? 'N/A' }}</td>
              <td class="border border-gray-300 px-4 py-2 text-yellow-700">{{ $record['reason'] ?? 'Missing data' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif
    
    <!-- Errors -->
    @if(!empty($results['errors']))
    <div class="w-full mb-6">
      <h3 class="text-2xl font-bold text-red-700 mb-4">Errors</h3>
      <div class="bg-red-50 p-4 rounded-xl">
        <ul class="list-disc list-inside">
          @foreach($results['errors'] as $error)
          <li class="text-red-700">{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
    @endif
    
    <!-- Processed Records -->
    @if(!empty($results['processed']))
    <div class="w-full mb-6">
      <h3 class="text-2xl font-bold text-green-700 mb-4">Successfully Processed Records</h3>
      <div class="overflow-x-auto max-h-96">
        <table class="w-full border-collapse border border-gray-300">
          <thead class="bg-gray-200">
            <tr>
              <th class="border border-gray-300 px-4 py-2 text-left">Employee ID</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Employee Name</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Date</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Clock In</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Clock Out</th>
            </tr>
          </thead>
          <tbody>
            @foreach($results['processed'] as $record)
            <tr class="hover:bg-gray-50">
              <td class="border border-gray-300 px-4 py-2">{{ $record['employee_id'] }}</td>
              <td class="border border-gray-300 px-4 py-2">{{ $record['employee_name'] }}</td>
              <td class="border border-gray-300 px-4 py-2">{{ $record['date'] }}</td>
              <td class="border border-gray-300 px-4 py-2">{{ $record['clock_in'] }}</td>
              <td class="border border-gray-300 px-4 py-2">{{ $record['clock_out'] }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif
    
    <button id="close-results-btn" class="px-6 py-2 text-white bg-[#184E77] rounded-xl hover:bg-[#0D3B5C]">
      Close
    </button>
  </div>
</div>
@endif
<!-- Import Results Modal End -->

<script>

  function openEditModal(attendanceId) {
      const modal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
      modal.show();

      const modalContent = document.getElementById('editAttendanceContent');
      modalContent.innerHTML = '<div class="text-center "><p>Loading...</p></div>';

      // Fetch content from the server
      fetch(`${window.location.origin}/dashboard/attendance/${attendanceId}/edit`)
        .then(response => response.text())
        .then(html => {
          modalContent.innerHTML = html;
        })
        .catch(error => {
          modalContent.innerHTML = '<div class="text-center py-4 text-danger"><p>Error loading content. Please try again later.</p></div>';
          console.error('Error:', error);
        });
    }// Attendance Management Routes

    function openAddModal() {
      const modal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
      modal.show();

      const modalContent = document.getElementById('editAttendanceContent');
      modalContent.innerHTML = '<div class="text-center "><p>Loading...</p></div>';

      $('#editAttendanceModal').on('shown.bs.modal', function () {
    // Ensure inputs inside the modal are editable
    $('#editAttendanceContent input').prop('disabled', false);
  });
      // Fetch content from the server
      fetch(`${window.location.origin}/dashboard/attendance/create`)
        .then(response => response.text())
        .then(html => {
          modalContent.innerHTML = html;
        })
        .catch(error => {
          modalContent.innerHTML = '<div class="text-center py-4 text-danger"><p>Error loading content. Please try again later.</p></div>';
          console.error('Error:', error);
        });
    }// Attendance Management Routes

  function formatDuration(seconds) {
      // Calculate hours, minutes, and seconds
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      const remainingSeconds = seconds % 60;

      // Format as HH:MM:SS
      return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
  }

     function toggleDropdown(button) {
      const dropdown = button.nextElementSibling; // Find the next sibling element (dropdown menu)
      dropdown.classList.toggle('hidden'); // Toggle the 'hidden' class
  }

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
  // Initialize Flatpickr



  $(document).ready(function () {
      // Initialize DataTable
      var table = $('#attendance-table').DataTable({
        dom: 'rt<"bottom"p>', // Remove default search, keep table and pagination
      paging: true,
      pageLength: 10,
      pagingType: 'full_numbers',
      order: [[1, 'desc']],
      searching: true,
          buttons: [
              {
                  extend: 'print',
                  text: 'Print Table',
                  title: 'Attendance Report', // Optional title
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
      columnDefs: [
          {
              targets: 0,
              className: 'employee-column'
          },
          {
              targets: 1,
              className: 'date-column'
          },
          {
              targets: 2,
              className: 'checkinCheckout-column'
          },
          {
              targets: 3,
              className: 'total-work-column'
          },
          {
              targets: 4,
              className: 'total-ot-column'
          },
          {
              targets: 5,
              className: 'lateby-column'
          },
          {
              targets: 6,
              className: 'actions-column'
          }
      ]
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

  $('#resetCalendarButton').on('click', function () {
    selectedDate.textContent = '13.03.2021'; // Reset displayed date
    calendarInput._flatpickr.clear(); // Clear Flatpickr input
    table.columns(3).search('').draw(); // Clear DataTable date filter
  });
  //pagination controls
  table.on('draw', function () {
      const pagination = $('.dataTables_paginate');
      const pageInfo = table.page.info();

      // Clear existing pagination
      pagination.empty();

      // Only show pagination if there are multiple pages
      if (pageInfo.pages > 1) {
          // Build custom pagination
          let customPagination = $(`
              <div class="flex items-center justify-center w-full space-x-2 mt-6 mb-4">
                  <button class="pagination-btn prev-btn flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 hover:text-gray-700 ${
                      pageInfo.page === 0 ? 'cursor-not-allowed opacity-50' : ''
                  }" data-action="prev" ${pageInfo.page === 0 ? 'disabled' : ''}>
                      <i class="ri-arrow-left-s-line mr-1"></i>
                      Prev
                  </button>
                  <div class="flex items-center space-x-1 page-numbers">
                  </div>
                  <button class="pagination-btn next-btn flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 hover:text-gray-700 ${
                      pageInfo.page + 1 >= pageInfo.pages ? 'cursor-not-allowed opacity-50' : ''
                  }" data-action="next" ${pageInfo.page + 1 >= pageInfo.pages ? 'disabled' : ''}>
                      Next
                      <i class="ri-arrow-right-s-line ml-1"></i>
                  </button>
              </div>
          `);

          // Add page number buttons
          const pageNumbersContainer = customPagination.find('.page-numbers');
          
          // Calculate which pages to show
          let startPage = Math.max(0, pageInfo.page - 2);
          let endPage = Math.min(pageInfo.pages - 1, startPage + 4);
          
          // Adjust start if we're near the end
          if (endPage - startPage < 4) {
              startPage = Math.max(0, endPage - 4);
          }

          // Add first page and ellipsis if needed
          if (startPage > 0) {
              pageNumbersContainer.append(`
                  <button class="page-number-btn w-8 h-8 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 hover:text-gray-900" data-page="0">
                      1
                  </button>
              `);
              if (startPage > 1) {
                  pageNumbersContainer.append('<span class="px-2 py-2 text-gray-500">...</span>');
              }
          }

          // Add page numbers
          for (let i = startPage; i <= endPage; i++) {
              const isActive = i === pageInfo.page;
              pageNumbersContainer.append(`
                  <button class="page-number-btn w-8 h-8 text-sm font-medium ${
                      isActive 
                          ? 'text-white bg-[#184E77] border-[#184E77]' 
                          : 'text-gray-700 bg-white hover:bg-gray-50 hover:text-gray-900'
                  } border border-gray-300" data-page="${i}">
                      ${i + 1}
                  </button>
              `);
          }

          // Add last page and ellipsis if needed
          if (endPage < pageInfo.pages - 1) {
              if (endPage < pageInfo.pages - 2) {
                  pageNumbersContainer.append('<span class="px-2 py-2 text-gray-500">...</span>');
              }
              pageNumbersContainer.append(`
                  <button class="page-number-btn w-8 h-8 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 hover:text-gray-900" data-page="${pageInfo.pages - 1}">
                      ${pageInfo.pages}
                  </button>
              `);
          }

          pagination.append(customPagination);
      }
  });

  // Handle custom pagination button clicks
  $(document).on('click', '.dataTables_paginate .pagination-btn, .dataTables_paginate .page-number-btn', function (e) {
      e.preventDefault();
      
      if ($(this).prop('disabled') || $(this).hasClass('opacity-50')) {
          return;
      }

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




      // Initialize Flatpickr for Date Selection
      const calendarInput = document.getElementById("calendarInput");
      const selectedDate = document.getElementById("selectedDate");
      const calendarButton = document.getElementById("calendarButton");

      flatpickr(calendarInput, {
          dateFormat: "Y-m-d",
          onChange: function (selectedDates, dateStr) {
              selectedDate.textContent = dateStr;
              // Filter DataTable by selected date
              table.search(dateStr).draw();
          },
      });

      calendarButton.addEventListener("click", () => {
          calendarInput._flatpickr.open();
      });

      table.draw('page');
  });

  function openAddNewModal() {
    const modal = document.getElementById('view-attendance-modal-container');
    modal.classList.remove('hidden'); // Make visible
    setTimeout(() => {
      modal.classList.remove('opacity-0', 'scale-95'); // Apply transition effects
    }, 10);
  }

  function closeAddNewModal() {
    const modal = document.getElementById('view-attendance-modal-container');
    modal.classList.add('opacity-0', 'scale-95'); // Apply transition effects
    setTimeout(() => {
      modal.classList.add('hidden'); // Hide modal after transition
    }, 300); // Match the transition duration
  }


  function openViewModal(data) {
      // Populate modal fields with the passed data
      document.querySelector('#view-attendance-modal-container .modal-employee-name').textContent = data.employeeName;
      document.querySelector('#view-attendance-modal-container .modal-employee-id').textContent = data.employeeId;
      document.querySelector('#view-attendance-modal-container .modal-check-in').textContent = data.checkIn;
      document.querySelector('#view-attendance-modal-container .modal-check-out').textContent = data.checkOut;
      document.querySelector('#view-attendance-modal-container .modal-total-work-hours').textContent = data.totalWorkHours;
      document.querySelector('#view-attendance-modal-container .modal-overtime-hours').textContent = data.overtimeHours;
      document.querySelector('#view-attendance-modal-container .modal-late-by').textContent = data.lateBy;
      document.querySelector('#view-attendance-modal-container .modal-date').textContent = data.date;

      // Show the modal
      const modal = document.getElementById('view-attendance-modal-container');
      modal.classList.remove('hidden');
      setTimeout(() => {
          modal.classList.remove('opacity-0', 'scale-95');
      }, 10);
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

  // Import Attendance Modal Handlers
  const importBtn = document.getElementById('import-attendance-btn');
  const importModal = document.getElementById('import-attendance-modal');
  const closeImportModal = document.getElementById('close-import-modal');
  const cancelImportBtn = document.getElementById('cancel-import-btn');
  const attendanceFilesInput = document.getElementById('attendance_files');
  const fileListDiv = document.getElementById('file-list');
  
  // Display selected files
  if (attendanceFilesInput) {
    attendanceFilesInput.addEventListener('change', (e) => {
      const files = e.target.files;
      if (files.length > 0) {
        let fileListHTML = '<strong>Selected files:</strong><ul class="list-disc ml-5 mt-1">';
        for (let i = 0; i < files.length; i++) {
          const sizeKB = (files[i].size / 1024).toFixed(2);
          fileListHTML += `<li>${files[i].name} (${sizeKB} KB)</li>`;
        }
        fileListHTML += '</ul>';
        fileListDiv.innerHTML = fileListHTML;
      } else {
        fileListDiv.innerHTML = '';
      }
    });
  }
  
  if (importBtn) {
    importBtn.addEventListener('click', () => {
      importModal.classList.remove('hidden');
      setTimeout(() => {
        importModal.classList.remove('opacity-0');
      }, 10);
    });
  }
  
  function closeImportModalHandler() {
    importModal.classList.add('opacity-0');
    setTimeout(() => {
      importModal.classList.add('hidden');
      // Reset file input and list
      if (attendanceFilesInput) attendanceFilesInput.value = '';
      if (fileListDiv) fileListDiv.innerHTML = '';
    }, 300);
  }
  
  if (closeImportModal) {
    closeImportModal.addEventListener('click', closeImportModalHandler);
  }
  
  if (cancelImportBtn) {
    cancelImportBtn.addEventListener('click', closeImportModalHandler);
  }
  
  // Import Results Modal Handlers
  const resultsModal = document.getElementById('import-results-modal');
  const closeResultsModal = document.getElementById('close-results-modal');
  const closeResultsBtn = document.getElementById('close-results-btn');
  
  if (closeResultsModal) {
    closeResultsModal.addEventListener('click', () => {
      resultsModal.classList.add('hidden');
    });
  }
  
  if (closeResultsBtn) {
    closeResultsBtn.addEventListener('click', () => {
      resultsModal.classList.add('hidden');
    });
  }
  
  // Close modal when clicking outside
  if (importModal) {
    importModal.addEventListener('click', (e) => {
      if (e.target === importModal) {
        closeImportModalHandler();
      }
    });
  }
  
  if (resultsModal) {
    resultsModal.addEventListener('click', (e) => {
      if (e.target === resultsModal) {
        resultsModal.classList.add('hidden');
      }
    });
  }

  document.querySelectorAll('.delete-attendance-form').forEach((form) => {
      form.addEventListener('submit', (event) => {
          const employee = form.dataset.employeeName || 'this employee';
          const date = form.dataset.attendanceDate ? ` on ${form.dataset.attendanceDate}` : '';
          const message = `Are you sure you want to delete the attendance record for ${employee}${date}? This action cannot be undone.`;

          if (!window.confirm(message)) {
              event.preventDefault();
              event.stopPropagation();
          }
      });
  });
  </script>
@endsection
