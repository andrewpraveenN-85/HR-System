
<div class="w-full flex justify-center items-center">
<div class="w-full flex justify-center items-center rounded-3xl">
  <!-- Close Button -->
 
 <div id="modal-container" class="w-full flex flex-col justify-start items-center relative bg-white nunito- p-2 rounded-3xl bg-gradient-to-r from-[#184E77] to-[#52B69A]">
   <!--  <button id="close-button" class="absolute top-4 right-4 text-black font-medium rounded-full text-xl p-4 inline-flex items-center">
        <span class="iconify" data-icon="ic:baseline-close" style="width: 16px; height: 16px;"></span>
    </button>-->
    <div class="w-full flex flex-col justify-start items-center bg-white p-8 rounded-3xl space-y-8">
      <div class="flex flex-col justify-center items-center space-y-4">
        <p class="text-5xl text-black font-bold">Attendance</p>
        <p class="text-3xl text-[#00000080]">Edit details of the record</p>
      </div>
      <div class="w-full mx-auto p-6 ">

    @php
      $formatDuration = static function ($value, $default = '00:00:00') {
        if ($value === null || $value === '') {
          return $default;
        }

        if (is_numeric($value)) {
          return gmdate('H:i:s', (int) $value);
        }

        if (is_string($value) && preg_match('/^\d{1,2}:\d{2}(?::\d{2})?$/', $value)) {
          return strlen($value) === 5 ? $value . ':00' : $value;
        }

        return $default;
      };
    @endphp

    <form action="{{ route('attendance.update', $attendance->id) }}" method="POST" class="w-full mx-auto p-6 ">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-2 gap-4">

          <!-- Claim Date -->
          <div>
            <label for="employee_id" class="block text-xl text-black font-bold">Employee:</label>
            <select
              id="employee_id"
              name="employee_id"
              required
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md focus:ring-blue-500 focus:border-blue-500 text-[#0000008C] font-bold select2"
            >
              <option value="">Select Employee</option>
              @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ old('employee_id', $employee->id) == $emp->id ? 'selected' : '' }}>
                  {{ $emp->employee_id }} - {{ $emp->full_name }}
                </option>
              @endforeach
            </select>
          </div>
          @push('scripts')
          <script>
          $(document).ready(function() {
              $('#employee_id').select2({
                  placeholder: "Select Employee",
                  allowClear: true,
                  width: '100%'  
              });
          });
          </script>
          @endpush

          <!-- Check In Date -->
          <div>
            <label for="clock_in_date" class="block text-xl text-black font-bold">Check In Date</label>
            <input
              type="date"
              id="clock_in_date"
              name="clock_in_date"
              value="{{ old('clock_in_date', $attendance->date) }}"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>

          <!-- Check Out Date -->
          <div>
            <label for="clock_out_date" class="block text-xl text-black font-bold">Check Out Date</label>
            <input
              type="date"
              id="clock_out_date"
              name="clock_out_date"
              value="{{ old('clock_out_date', $attendance->date) }}"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>

          <!-- Amount -->
          <div>
            <label for="clock_in_time" class="block text-xl text-black font-bold">Check In Time</label>
            <input
              type="time"
              id="clock_in_time"
              name="clock_in_time"
              value="{{ old('clock_in_time', $attendance->clock_in_time ? substr($attendance->clock_in_time, 0, 5) : '') }}"
              placeholder="Enter the time"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>

          <!-- Status -->
          <div>
            <label for="clock_out_time" class="block text-xl text-black font-bold">Check Out Time</label>
            <input
              type="time"
              id="clock_out_time"
              name="clock_out_time"
              value="{{ old('clock_out_time', $attendance->clock_out_time ? substr($attendance->clock_out_time, 0, 5) : '') }}"              placeholder="Enter the Status"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>

          <!-- Approved By -->
<!-- Total Work Hours -->
<div>
    <label for="total_work_hours" class="block text-xl text-black font-bold">Total Work Hours</label>
    <input
        type="text"
        id="total_work_hours"
        name="total_work_hours"
        value="{{ old('total_work_hours', $formatDuration($attendance->total_work_hours)) }}"
        placeholder="HH:MM:SS"
        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
        readonly
    />
</div>

<!-- Overtime Hours -->
<div>
    <label for="overtime_hours" class="block text-xl text-black font-bold">O/T Hours</label>
    <input
        type="text"
        id="overtime_hours"
        name="overtime_hours"
      value="{{ old('overtime_hours', $formatDuration($attendance->overtime_seconds)) }}"
        placeholder="HH:MM:SS"
        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
        readonly
    />
</div>

<!-- Late By -->
<div>
    <label for="late_by" class="block text-xl text-black font-bold">Late By</label>
    <input
        type="text"
        id="late_by"
        name="late_by"
      value="{{ old('late_by', $formatDuration($attendance->late_by_seconds)) }}"
        placeholder="HH:MM:SS"
        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
        readonly
    />
</div>


          <div>
            <label for="date" class="block text-xl text-black font-bold">Date :</label>
            <input
              type="date"
              id="date"
              name="date"
              value="{{ old('date', $attendance->date) }}"
              placeholder="Enter the name"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>

        <!-- Submit Button -->
        <div class="mt-6 col-span-2 flex justify-center">
      <button
        type="submit"
        class="w-1/2 bg-gradient-to-r from-[#184E77] to-[#52B69A] text-white font-medium px-6 py-2 rounded-md hover:from-blue-600 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        Save Record
      </button>
    </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>


<style>
  input::placeholder,
  textarea::placeholder {
    color: #0000008C;
    opacity: 1;
  }
</style>
<script>
  // Close button functionality
  document.getElementById('close-button').addEventListener('click', function () {
    document.getElementById('modal-container').style.display = 'none';
  });
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const checkInDateInput = document.getElementById("clock_in_date");
  const checkOutDateInput = document.getElementById("clock_out_date");
  const checkInInput = document.getElementById("clock_in_time");
  const checkOutInput = document.getElementById("clock_out_time");
  const totalWorkInput = document.getElementById("total_work_hours");
  const overtimeInput = document.getElementById("overtime_hours");
  const lateByInput = document.getElementById("late_by");

  const SHIFT_START = "08:30:00";
  const SHIFT_END = "16:30:00";

  const toDateTime = (dateValue, timeValue) => {
    if (!dateValue || !timeValue) {
      return null;
    }

    const normalizedTime = timeValue.length === 5 ? `${timeValue}:00` : timeValue;
    const [year, month, day] = dateValue.split("-").map(Number);
    const [hours, minutes, seconds] = normalizedTime.split(":").map(Number);

    return new Date(year, month - 1, day, hours, minutes, seconds || 0);
  };

  const secondsToHHMMSS = (seconds) => {
    if (!Number.isFinite(seconds) || seconds <= 0) {
      return "00:00:00";
    }

    const hrs = Math.floor(seconds / 3600);
    const mins = Math.floor((seconds % 3600) / 60);
    const secs = Math.floor(seconds % 60);

    return `${String(hrs).padStart(2, "0")}:${String(mins).padStart(2, "0")}:${String(secs).padStart(2, "0")}`;
  };

  function calculateDerivedDurations() {
    const clockInDT = toDateTime(checkInDateInput.value, checkInInput.value);
    const clockOutDT = toDateTime(checkOutDateInput.value, checkOutInput.value);

    if (!clockInDT || !clockOutDT) {
      totalWorkInput.value = "00:00:00";
      overtimeInput.value = "00:00:00";
      lateByInput.value = "00:00:00";
      return;
    }

    let adjustedClockOut = new Date(clockOutDT.getTime());

    if (adjustedClockOut < clockInDT) {
      adjustedClockOut = new Date(adjustedClockOut.getTime() + 24 * 60 * 60 * 1000);
    }

    const shiftStart = toDateTime(checkInDateInput.value, SHIFT_START);
    const shiftEnd = toDateTime(checkInDateInput.value, SHIFT_END);

    const workCountStart = shiftStart && clockInDT < shiftStart ? shiftStart : clockInDT;
    const totalSeconds = Math.max(0, Math.round((adjustedClockOut - workCountStart) / 1000));
    totalWorkInput.value = secondsToHHMMSS(totalSeconds);

    let overtimeSeconds = 0;
    if (shiftEnd && adjustedClockOut > shiftEnd) {
      overtimeSeconds = Math.max(0, Math.round((adjustedClockOut - shiftEnd) / 1000));
    }
    overtimeInput.value = secondsToHHMMSS(overtimeSeconds);

    let lateSeconds = 0;
    if (shiftStart && clockInDT > shiftStart) {
      lateSeconds = Math.max(0, Math.round((clockInDT - shiftStart) / 1000));
    }
    lateByInput.value = secondsToHHMMSS(lateSeconds);
  }

  [
    checkInDateInput,
    checkOutDateInput,
    checkInInput,
    checkOutInput
  ].forEach((el) => {
    el.addEventListener("change", calculateDerivedDurations);
    el.addEventListener("keyup", calculateDerivedDurations);
  });

  calculateDerivedDurations();
});
</script>