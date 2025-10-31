<div id="modal-container" class="w-full flex flex-col justify-start items-center relative bg-white nunito- p-2 rounded-3xl bg-gradient-to-r from-[#184E77] to-[#52B69A]">
    <div class="w-full flex flex-col justify-start items-center bg-white p-8 rounded-3xl space-y-8">
      <div class="flex flex-col justify-center items-center space-y-4">
        <p class="text-5xl text-black font-bold">Advance</p>
        <p class="text-3xl text-[#00000080]">Enter the Information about Advance</p>
      </div>
      <div class="w-full mx-auto p-6">
      <form action="{{ route('newadvance.store') }}" method="POST" class="w-full mx-auto p-6"  enctype="multipart/form-data">
      @csrf
      <div class="grid grid-cols-2 gap-4">

         <div>
              <label for="employment_ID" class="block text-xl text-black font-bold">Employee:</label>
              <select id="employment_ID" name="employment_ID" required
                  class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md focus:ring-blue-500 focus:border-blue-500 text-[#0000008C] font-bold select2">
                  <option value="">Select Employee</option>
                  @foreach($employees as $employee)
                      <option value="{{ $employee->employee_id }}" {{ old('employment_ID') == $employee->employee_id ? 'selected' : '' }}>
                          {{ $employee->employee_id }} - {{ $employee->full_name }}
                      </option>
                  @endforeach
              </select>
          </div>
         
          <script>
          $(document).ready(function() {
              $('#employment_ID').select2({
                  placeholder: "Select Employee",
                  allowClear: true,
                  width: '100%'  
              });
          });
          </script>
         
          <div>
            <label for="advance_amount" class="block text-xl text-black font-bold">Advance Amount:</label>
            <input
              type="number"
              id="advance_amount"
              name="advance_amount"
              placeholder="Enter your Amount"
              min="1"
              step="0.01"
              oninput="this.value = Math.abs(this.value) || ''"
              required
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md focus:ring-blue-500 focus:border-blue-500 font-bold text-[#0000008C]"
            />
            <p id="advance_amount_error" class="text-red-600 text-sm mt-1 hidden">Advance amount must be a positive number.</p>
          </div>

          <!-- Advance Date -->
          <div>
            <label for="advance_date" class="block text-xl text-black font-bold">Advance Date:</label>
            <input
              type="date"
              id="advance_date"
              name="advance_date"
              placeholder="Enter the date here"
              required
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-bold text-[#0000008C]"
            />
          </div>

          <!-- Approval Status -->
          <div>
            <label for="status" class="block text-xl text-black font-bold">Approval Status:</label>
            <select
              id="status"
              name="status"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:bg-gradient-to-r from-[#184E77] to-[#52B69A] text-xl text-black focus:border-blue-500 font-bold text-[#0000008C]">
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>

         

        <!-- Description -->
        <div class="mt-4">
          <label for="description" class="block text-xl text-black font-bold">Description:</label>
          <textarea
            id="description"
            name="description"
            placeholder="Add description here"
            rows="3"
            class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-bold text-[#0000008C]"
          ></textarea>
        </div>

        <!-- Submit Button -->
        <div class="mt-6 text-center">
          <button
            type="submit"
            class="w-1/2 bg-gradient-to-r from-[#184E77] to-[#52B69A] text-white font-medium py-2 px-4 rounded-md hover:from-blue-600 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
          Add Record
          </button>
        </div>

      </div>
      </form>
        
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