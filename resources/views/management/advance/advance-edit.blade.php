<div id="modal-container" class="w-full flex flex-col justify-start items-center relative bg-white nunito- p-2 rounded-3xl bg-gradient-to-r from-[#184E77] to-[#52B69A]">
    <div class="w-full flex flex-col justify-start items-center bg-white p-8 rounded-3xl space-y-8">
      <div class="flex flex-col justify-center items-center space-y-4">
        <p class="text-5xl text-black font-bold">Loan</p>
        <p class="text-3xl text-[#00000080]">Edit details of the Loan</p>
      </div>
      <div class="w-full mx-auto p-6 ">

    <form action="{{ route('advance.update', $advance->id) }}" method="POST" class="w-full mx-auto p-6 " enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="employment_ID" class="block text-xl text-black font-bold">Employee:</label>
            <select
              id="employment_ID"
              name="employment_ID"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
              <option value="">Select Employee</option>
              @foreach($employees as $emp)
                <option value="{{ $emp->employee_id }}" {{ $advance->employment_ID == $emp->employee_id ? 'selected' : '' }}>
                  {{ $emp->employee_id }} - {{ $emp->full_name }}
                </option>
              @endforeach
            </select>
          </div>

          <div>
            <label for="loan_amount" class="block text-xl text-black font-bold">Loan Amount:</label>
            <input
              type="text"
              id="loan_amount"
              name="loan_amount"
              value="{{ $advance->loan_amount }}"
              placeholder="Enter the amount"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>
<!-- 
          <div>
            <label for="interest_rate" class="block text-xl text-black font-bold">Interest:</label>
            <input
              type="text"
              id="interest_rate"
              name="interest_rate"
              value="{{ $advance->interest_rate }}"
              placeholder="Enter the interest"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div> -->

          <div>
              <label for="loan_start_date" class="block text-xl text-black font-bold">Loan Date:</label>
              <input
                  type="date"
                  id="loan_start_date"
                  name="loan_start_date"
                  value="{{ $advance->loan_start_date }}"
                  placeholder="DD:MM:YYYY"
                  class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
          </div>

          <!-- <div>
              <label for="duration" class="block text-xl text-black font-bold">Payment Duration:</label>
              <input
                  type="text"
                  id="duration"
                  name="duration"
                  value="{{ $advance->duration }}"
                  placeholder="eg:-6"
                  class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
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

          <div>
            <label for="description" class="block text-xl text-black font-bold">Description:</label>
            <input
              type="text"
              id="description"
              name="description"
              value="{{ $advance->description }}"
              placeholder="Enter the description"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>

          <div>
            <label for="doc-files" class="flex items-center justify-center px-4 py-2 bg-[#184E77] border-2 border-[#52B69A80] text-white rounded-md cursor-pointer hover:bg-blue-600 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"> 
                <span class="iconify" data-icon="ic:sharp-upload" style="width: 16px; height: 16px;"></span>
                <span class="ml-2">Upload Files</span>
            </label>
    
            <input type="file" id="doc-files" accept="application/pdf" class="hidden" multiple />
            <input type="file" name="advance_documents[]" id="hidden-files" class="hidden" multiple />
            <input type="hidden" id="existing-files-data" value='{{$advance->advance_documents }}'>
            <input type="hidden" name="existing_files" id="existing-files">

            <div id="file-list" class="text-black text-sm w-full bg-[#D9D9D980] flex flex-col justify-end items-end rounded-b-xl pr-4 pt-4">
              <p>Attached Files:</p>
              <ul id="file-list-items" class="w-full px-4">
                  @php
                      $documents = json_decode($advance->advance_documents, true) ?: [];
                  @endphp

                  @if (empty($documents))
                      <li class="text-gray-500">No documents attached.</li>
                  @else
                      @foreach ($documents as $document)
                          <li>
                              <span class="text-2xl">
                                  <i class="ri-file-pdf-2-fill"></i>
                              </span>
                              <a href="{{ asset('storage/' . $document) }}" target="_blank" class="text-blue-500 underline">
                                  {{ basename($document) }}
                              </a>
                              <span onclick="removeDocument('{{ $document }}')" class="text-red-500 cursor-pointer ml-2">✖</span>
                          </li>
                      @endforeach
                  @endif
              </ul>
          </div>

          </div>

        <!-- Submit Button -->
        <div class="mt-6 col-span-2 flex justify-center">
        <button
          type="submit"
          class="w-1/2 bg-gradient-to-r from-[#184E77] to-[#52B69A] text-white font-medium px-6 py-2 rounded-md hover:from-blue-600 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          Save Record
        </button>
    </div>
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

@push('scripts')
<script>
$(document).ready(function() {
    $('#employment_ID').select2({
        placeholder: 'Select Employee',
        allowClear: true,
        width: '100%'
    });
});
</script>
@endpush

<script>
  document.getElementById('close-button').addEventListener('click', function () {
    document.getElementById('modal-container').style.display = 'none';
  });

  document.getElementById('doc-files').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const hiddenInput = document.getElementById('hidden-files');
    const dataTransfer = new DataTransfer();
    
    // Add new files to hidden input
    files.forEach(file => {
        dataTransfer.items.add(file);
    });
    
    // Set the files to the hidden input
    hiddenInput.files = dataTransfer.files;
    
    // Add new files to the list
    const fileList = document.getElementById('file-list-items');
    files.forEach(file => {
        const li = document.createElement('li');
        li.className = 'flex items-center space-x-2 mb-2';
        li.innerHTML = `
            <span class="text-2xl">
                <i class="ri-file-pdf-2-fill"></i>
            </span>
            <span class="text-blue-500 flex-grow">${file.name}</span>
            <span onclick="removeDocument(this)" class="text-red-500 cursor-pointer ml-2">✖</span>
        `;
        fileList.appendChild(li);
    });
});

// Function to remove documents
function removeDocument(element) {
    if (confirm('Are you sure you want to remove this document?')) {
        const li = element.closest('li');
        const hiddenInput = li.querySelector('input[name="existing_documents[]"]');
        if (hiddenInput) {
            // Create a hidden input to track removed files
            const removedInput = document.createElement('input');
            removedInput.type = 'hidden';
            removedInput.name = 'removed_documents[]';
            removedInput.value = hiddenInput.value;
            li.closest('form').appendChild(removedInput);
        }
        li.remove();
        
        // If no documents left, show the "No documents attached" message
        const fileList = document.getElementById('file-list-items');
        if (fileList.children.length === 0) {
            const noDocsMessage = document.createElement('li');
            noDocsMessage.className = 'text-gray-500 italic';
            noDocsMessage.textContent = 'No documents attached';
            fileList.appendChild(noDocsMessage);
        }
    }
}
</script>

