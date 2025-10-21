<div id="modal-container" class="w-full flex flex-col justify-start items-center relative bg-white nunito- p-2 rounded-3xl bg-gradient-to-r from-[#184E77] to-[#52B69A]">
    <div class="w-full flex flex-col justify-start items-center bg-white p-8 rounded-3xl space-y-8">
      <div class="flex flex-col justify-center items-center space-y-4">
        <p class="text-5xl text-black font-bold">Loan</p>
        <p class="text-3xl text-[#00000080]">Enter the Information about Loan</p>
      </div>
      <div class="w-full mx-auto p-6">
      <form action="{{ route('advance.store') }}" method="POST" class="w-full mx-auto p-6 "  enctype="multipart/form-data">
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
          @push('scripts')
          <script>
          $(document).ready(function() {
              $('#employment_ID').select2({
                  placeholder: "Select Employee",
                  allowClear: true,
                  width: '100%'  
              });
          });
          </script>
          @endpush
          

          <div>
            <label for="loan_amount" class="block text-xl text-black font-bold">Loan Amount :</label>
            <input
              type="number"
              id="loan_amount"
              name = "loan_amount"
              placeholder="Enter your Amount"
              min="1"
              step="0.01"
              oninput="this.value = Math.abs(this.value) || ''"
              required
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md focus:ring-blue-500 focus:border-blue-500 font-bold text-[#0000008C]"
            />
            <p id="loan_amount_error" class="text-red-600 text-sm mt-1 hidden">Loan amount must be a positive number.</p>
          </div>
          <!-- <div>
            <label for="interest_rate" class="block text-xl text-black font-bold">Interest(%) :</label>
            <input
              type="number"
              id="interest_rate"
              name = "interest_rate"
              placeholder="Enter your Interest Rate"
              min="0.01"
              max="100"
              step="0.01" 
                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^(-)/g, '');"
              required
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md focus:ring-blue-500 focus:border-blue-500 font-bold text-[#0000008C]"
            />
          </div> -->

          <!-- Claim Date -->
          <div>
            <label for="loan_start_date" class="block text-xl text-black font-bold">Loan Date :</label>
            <input
              type="date"
              id="loan_start_date"
              name = "loan_start_date"
              placeholder="Enter the date here"
              required
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-bold text-[#0000008C]"
            />
          </div>
          <!-- Status -->
          <!-- <div>
            <label for="duration" class="block text-xl text-black font-bold">Payment Duration(Months):</label>
            <input
              type="number"
              id="duration"
              name = "duration"
              placeholder="Enter the duration"
              oninput="this.value = Math.abs(this.value) || ''"
              required
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-bold text-[#0000008C]"
            />
          </div> -->

              <!-- Approval Status -->
              <div>
                <label for="status" class="block text-xl text-black font-bold">Approval Status :</label>
                <select
                  id="status"
                  name="status"
                  class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:bg-gradient-to-r from-[#184E77] to-[#52B69A] text-xl text-black focus:border-blue-500 font-bold text-[#0000008C]">
                  <option value="approved">Approved</option>
                  <option value="pending">Pending</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>

          <!-- Supporting Document -->
          <div>
            <label for="doc-files" class="flex items-center justify-center px-4 py-2 bg-[#184E77] border-2 border-[#52B69A80] text-white rounded-md cursor-pointer hover:bg-blue-600 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"> 
                <span class="iconify" data-icon="ic:sharp-upload" style="width: 16px; height: 16px;"></span>
                <span class="ml-2">Upload Files</span>
            </label>
            <!-- Visible File Input -->
            <input type="file" id="doc-files" accept="application/pdf" class="hidden" multiple />
            <input type="file" name="advance_documents[]" id="hidden-files" class="hidden" multiple />
            <div id="file-list" class="text-black text-sm w-full bg-[#D9D9D980] flex flex-col justify-end items-end rounded-b-xl pr-4 pt-4">
            <p>Attached Files:</p>
            <ul id="file-list-items"></ul>
        </div>
        </div>

        <!-- Description -->
        <div class="mt-4">
          <label for="description" class="block text-xl text-black font-bold">Description:</label>
          <textarea
            id="description"
            name = "description"
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


      </form>
        
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


    document.querySelector('form').addEventListener('submit', async function (e) {
    e.preventDefault(); // Prevent default form submission

    const formData = new FormData(this);

    // Collect files
    const files = formData.getAll('attachments[]');
    const fileArray = await Promise.all(
        files.map(file => {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve({ name: file.name, content: reader.result });
                reader.onerror = reject;
                reader.readAsDataURL(file); // Encode file as base64
            });
        })
    );

    // Add files to the JSON data
    const jsonData = {};
    formData.forEach((value, key) => {
        if (key !== 'attachments[]') {
            jsonData[key] = value;
        }
    });
    jsonData.attachments = fileArray;

    // Send the JSON data to the server
    fetch('{{ route("leave.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(jsonData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        alert('Leave record added successfully');
        console.log(data);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error adding the leave record');
    });
});


</script>
