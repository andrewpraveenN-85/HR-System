
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

          <!-- Amount -->
          <div>
            <label for="clock_in_time" class="block text-xl text-black font-bold">Check In Time</label>
            <input
              type="time"
              id="clock_in_time"
              name="clock_in_time"
              value="{{ old('clock_in_time', $attendance->clock_in_time) }}"
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
              value="{{ old('clock_out_time', \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i')) }}"              placeholder="Enter the Status"
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
        value="{{ old('total_work_hours', gmdate('H:i:s', $attendance->total_work_hours ?? 0)) }}"
        placeholder="HH:MM:SS"
        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
    />
</div>

<!-- Overtime Hours -->
<div>
    <label for="overtime_hours" class="block text-xl text-black font-bold">O/T Hours</label>
    <input
        type="text"
        id="overtime_hours"
        name="overtime_hours"
        value="{{ old('overtime_hours', gmdate('H:i:s', $attendance->overtime_seconds ?? 0)) }}"
        placeholder="HH:MM:SS"
        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
    />
</div>

<!-- Late By -->
<div>
    <label for="late_by" class="block text-xl text-black font-bold">Late By</label>
    <input
        type="text"
        id="late_by"
        name="late_by"
        value="{{ old('late_by', gmdate('H:i:s', $attendance->late_by_seconds ?? 0)) }}"
        placeholder="HH:MM:SS"
        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
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
    const checkInInput = document.getElementById("clock_in_time");
    const checkOutInput = document.getElementById("clock_out_time");
    const totalWorkInput = document.getElementById("total_work_hours");

    function calculateWorkHours() {
        const checkIn = checkInInput.value;
        const checkOut = checkOutInput.value;
        if (!checkIn || !checkOut) return;

        const today = new Date().toISOString().split("T")[0];
        const startLimit = new Date(`${today}T08:30:00`);
        let clockIn = new Date(`${today}T${checkIn}`);
        let clockOut = new Date(`${today}T${checkOut}`);

        // ⏰ Cut off early arrivals (before 08:30)
        if (clockIn < startLimit) {
            clockIn = startLimit;
        }

        // 🕐 Handle cases like 09:30 → 06:30 (means 6:30 PM)
        if (clockOut < clockIn) {
            // Try adding 12 hours (likely 6:30 PM instead of 6:30 AM)
            const plus12 = new Date(clockOut.getTime() + 12 * 60 * 60 * 1000);
            const diff12 = (plus12 - clockIn) / 1000 / 3600;

            if (diff12 > 0 && diff12 <= 12) {
                clockOut = plus12; // use corrected time
            } else {
                // if still invalid, assume next day (overnight)
                clockOut = new Date(clockOut.getTime() + 24 * 60 * 60 * 1000);
            }
        }

        // ✅ Calculate work duration
        const diffSeconds = (clockOut - clockIn) / 1000;
        if (diffSeconds <= 0) {
            totalWorkInput.value = "00:00:00";
            return;
        }

        const hours = Math.floor(diffSeconds / 3600);
        const minutes = Math.floor((diffSeconds % 3600) / 60);
        const seconds = Math.floor(diffSeconds % 60);

        totalWorkInput.value =
            String(hours).padStart(2, "0") + ":" +
            String(minutes).padStart(2, "0") + ":" +
            String(seconds).padStart(2, "0");
    }

    checkInInput.addEventListener("change", calculateWorkHours);
    checkOutInput.addEventListener("change", calculateWorkHours);
});
</script>