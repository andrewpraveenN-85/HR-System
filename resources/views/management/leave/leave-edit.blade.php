
<!-- Main Content -->
<div class="w-full flex justify-center items-center">
<div class="w-full flex justify-center items-center rounded-3xl">
  <div id="modal-container" class="w-full flex flex-col justify-start items-center relative bg-white nunito- p-2 rounded-3xl bg-gradient-to-r from-[#184E77] to-[#52B69A]">
    <div class="w-full flex flex-col justify-start items-center bg-white p-8 rounded-3xl space-y-8">
      <div class="flex flex-col justify-center items-center space-y-4">
        <p class="text-5xl text-black font-bold">Edit Leave Application</p>
        <p class="text-3xl text-[#00000080]">Edit leave details with balance tracking</p>
      </div>
      
      <!-- Employee Info Section -->
      <div id="employee-info" class="w-full p-4 bg-gray-100 rounded-lg">
        <h3 class="text-xl font-bold mb-4">Employee Leave Information</h3>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <strong>Employee Name:</strong> <span id="emp-name">{{ $leave->employee_name }}</span>
          </div>
          <div>
            <strong>Department:</strong> <span id="emp-department">{{ $leave->employee->department->name ?? 'N/A' }}</span>
          </div>
        </div>
        
        <h4 class="text-lg font-bold mt-4 mb-2">Leave Balances</h4>
        <div class="grid grid-cols-4 gap-4 text-sm">
          <div class="bg-blue-100 p-2 rounded">
            <strong>Annual Leaves</strong><br>
            <span id="annual-remaining">{{ $leave->employee ? ($leave->employee->annual_leave_balance - $leave->employee->annual_leave_used) : 0 }}</span> remaining
          </div>
          <div class="bg-green-100 p-2 rounded">
            <strong>Short Leaves</strong><br>
            <span id="short-remaining">{{ $leave->employee ? ($leave->employee->short_leave_balance - $leave->employee->short_leave_used) : 0 }}</span> remaining
          </div>
          <div class="bg-yellow-100 p-2 rounded">
            <strong>Monthly Leaves</strong><br>
            <span id="monthly-remaining">{{ $leave->employee ? (2 - $leave->employee->monthly_leaves_used) : 0 }}</span> remaining
          </div>
          <div class="bg-purple-100 p-2 rounded">
            <strong>Monthly Half Leaves</strong><br>
            <span id="half-remaining">{{ $leave->employee ? (1 - $leave->employee->monthly_half_leaves_used) : 0 }}</span> remaining
          </div>
        </div>
      </div>
      <div class="w-full mx-auto p-6 ">

    <form action="{{ route('leave.update', $leave->id) }}" method="POST" class="w-full mx-auto p-6 " enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-2 gap-4">
          <!-- Employee ID -->
          

          <div>
            <label for="employment_ID" class="block text-xl text-black font-bold">Employee:</label>
            <select
              id="employment_ID"
              name="employment_ID"
              required
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md focus:ring-blue-500 focus:border-blue-500 text-[#0000008C] font-bold select2"
              onchange="fetchEmployeeData()"
            >
              <option value="">Select Employee</option>
              @foreach($employees as $employee)
                <option value="{{ $employee->employee_id }}" {{ $leave->employment_ID == $employee->employee_id ? 'selected' : '' }}>
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
            <label for="leave_type" class="block text-xl text-black font-bold">Leave Type</label>
            <input
              type="text"
              id="leave_type"
              name="leave_type"
              value="{{ $leave->leave_type }}"
              placeholder="e.g., Sick Leave, Annual Leave"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>

          <div>
            <label for="leave_category" class="block text-xl text-black font-bold">Leave Category:</label>
            <select
              id="leave_category"
              name="leave_category"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              onchange="handleCategoryChange()"
            >
              <option value="">Select Category</option>
              <option value="full_day" {{ $leave->leave_category == 'full_day' ? 'selected' : '' }}>Full Day Leave</option>
              <option value="half_day" {{ $leave->leave_category == 'half_day' ? 'selected' : '' }}>Half Day Leave</option>
              <option value="short_leave" {{ $leave->leave_category == 'short_leave' ? 'selected' : '' }}>Short Leave</option>
            </select>
          </div>

          <div id="half_day_options" style="display: {{ $leave->leave_category == 'half_day' ? 'block' : 'none' }};">
            <label for="half_day_type" class="block text-xl text-black font-bold">Half Day Type:</label>
            <select
              id="half_day_type"
              name="half_day_type"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
              <option value="">Select Type</option>
              <option value="morning" {{ $leave->half_day_type == 'morning' ? 'selected' : '' }}>Morning (8:30 AM - 1:00 PM)</option>
              <option value="evening" {{ $leave->half_day_type == 'evening' ? 'selected' : '' }}>Evening (1:00 PM - 5:00 PM)</option>
            </select>
          </div>

          <div id="short_leave_options" style="display: {{ $leave->leave_category == 'short_leave' ? 'block' : 'none' }};">
            <label for="short_leave_type" class="block text-xl text-black font-bold">Short Leave Type:</label>
            <select
              id="short_leave_type"
              name="short_leave_type"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
              <option value="">Select Type</option>
              <option value="morning" {{ $leave->short_leave_type == 'morning' ? 'selected' : '' }}>Morning (8:30 AM - 10:00 AM)</option>
              <option value="evening" {{ $leave->short_leave_type == 'evening' ? 'selected' : '' }}>Evening (3:30 PM - 5:00 PM)</option>
            </select>
          </div>

          <div>
            <label for="approved_person" class="block text-xl text-black font-bold">Approved By:</label>
            <input
              type="text"
              id="approved_person"
              name="approved_person"
              value="{{ $leave->approved_person }}"
              placeholder="Enter the approved by"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>

          <div>
            <label for="start_date" class="block text-xl text-black font-bold">Date From:</label>
            <input
              type="date"
              id="start_date"
              name="start_date"
              value="{{ $leave->start_date }}"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              onchange="calculateDuration()"
            />
          </div>
          
          <div>
            <label for="end_date" class="block text-xl text-black font-bold">Date To:</label>
            <input
              type="date"
              id="end_date"
              name="end_date"
              value="{{ $leave->end_date }}"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              onchange="calculateDuration()"
            />
          </div>

          <div id="time_inputs" style="display: {{ in_array($leave->leave_category, ['half_day', 'short_leave']) ? 'block' : 'none' }};">
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label for="start_time" class="block text-xl text-black font-bold">Start Time:</label>
                <input
                  type="time"
                  id="start_time"
                  name="start_time"
                  value="{{ $leave->start_time }}"
                  class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm"
                />
              </div>
              <div>
                <label for="end_time" class="block text-xl text-black font-bold">End Time:</label>
                <input
                  type="time"
                  id="end_time"
                  name="end_time"
                  value="{{ $leave->end_time }}"
                  class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm"
                />
              </div>
            </div>
          </div>

          <div>
            <label for="status" class="block text-xl text-black font-bold">Approval Status:</label>
            <select
              id="status"
              name="status"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
              <option value="pending" {{ $leave->status == 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="approved" {{ $leave->status == 'approved' ? 'selected' : '' }}>Approved</option>
              <option value="rejected" {{ $leave->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
          </div>

          <div class="col-span-2">
            <label for="description" class="block text-xl text-black font-bold">Description:</label>
            <textarea
              id="description"
              name="description"
              placeholder="Enter the reason for leave"
              rows="3"
              class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >{{ $leave->description }}</textarea>
          </div>

          <!-- No Pay Warning -->
          <div id="no-pay-warning" class="col-span-2 p-4 bg-red-100 border border-red-300 rounded-md" style="display: {{ $leave->is_no_pay ? 'block' : 'none' }};">
            <h4 class="text-red-800 font-bold">⚠️ No Pay Leave Warning</h4>
            <p class="text-red-700">This leave application exceeds available balances and will result in no-pay deductions.</p>
            <p class="text-red-700"><strong>Current No-Pay Amount: LKR <span id="current-no-pay-amount">{{ $leave->no_pay_amount ?? 0 }}</span></strong></p>
            <p class="text-red-700"><strong>Estimated No-Pay Amount: LKR <span id="no-pay-amount">{{ $leave->no_pay_amount ?? 0 }}</span></strong></p>
          </div>

          <div>
    <label for="doc-files" class="flex items-center justify-center px-4 py-2 bg-[#184E77] border-2 border-[#52B69A80] text-white rounded-md cursor-pointer hover:bg-blue-600 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"> 
        <span class="iconify" data-icon="ic:sharp-upload" style="width: 16px; height: 16px;"></span>
        <span class="ml-2">Upload Files</span>
    </label>
    
    <input type="file" id="doc-files" accept="application/pdf" class="hidden" multiple />
    <input type="file" name="supporting_documents[]" id="hidden-files" class="hidden" multiple />
    <input type="hidden" id="existing-files-data" value='{{ $leave->supporting_documents }}'>
    <input type="hidden" name="existing_files" id="existing-files">
    
    <div id="file-list" class="text-black text-sm w-full bg-[#D9D9D980] flex flex-col justify-end items-end rounded-b-xl pr-4 pt-4">
        <p>Attached Files:</p>
        <ul id="file-list-items" class="w-full px-4">
        @php
                      $documents = json_decode($leave->supporting_documents, true) ?: [];
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

        </div>

        <div class="flex justify-center space-x-4 mt-6">
          <button
            type="submit"
            class="px-6 py-3 bg-gradient-to-r from-[#184E77] to-[#52B69A] text-white rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8] text-xl font-bold"
          >
            Update Leave Application
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
<script>
@php
    $employeeDataForJS = [
        'employee' => $leave->employee ?? null,
        'balances' => $leave->employee ? $leave->employee->getLeaveBalances() : [],
        'recent_leaves' => $leave->employee ? $leave->employee->leaves()->orderBy('created_at', 'desc')->limit(10)->get() : []
    ];
@endphp
let employeeData = @json($employeeDataForJS);

function fetchEmployeeData() {
    const employeeId = document.getElementById('employment_ID').value;
    if (!employeeId) return;
    
    fetch(`/api/employee-leave-data?employee_id=${employeeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Employee not found');
                return;
            }
            
            employeeData = data;
            displayEmployeeInfo(data);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error fetching employee data');
        });
}

function displayEmployeeInfo(data) {
    // Basic info
    document.getElementById('emp-name').textContent = data.employee.full_name;
    document.getElementById('emp-department').textContent = data.employee.department?.name || 'N/A';
    
    // Balances
    const balances = data.balances;
    document.getElementById('annual-remaining').textContent = balances.annual_leaves_remaining;
    document.getElementById('short-remaining').textContent = balances.short_leaves_remaining;
    document.getElementById('monthly-remaining').textContent = balances.monthly_leaves_remaining;
    document.getElementById('half-remaining').textContent = balances.monthly_half_leaves_remaining;
    
    calculateNoPayAmount();
}

function handleCategoryChange() {
    const category = document.getElementById('leave_category').value;
    const halfDayOptions = document.getElementById('half_day_options');
    const shortLeaveOptions = document.getElementById('short_leave_options');
    const timeInputs = document.getElementById('time_inputs');
    
    // Hide all conditional fields
    halfDayOptions.style.display = 'none';
    shortLeaveOptions.style.display = 'none';
    timeInputs.style.display = 'none';
    
    // Show relevant fields
    if (category === 'half_day') {
        halfDayOptions.style.display = 'block';
        timeInputs.style.display = 'block';
    } else if (category === 'short_leave') {
        shortLeaveOptions.style.display = 'block';
        timeInputs.style.display = 'block';
    }
    
    calculateNoPayAmount();
}

function calculateDuration() {
    calculateNoPayAmount();
}

function calculateNoPayAmount() {
    if (!employeeData) return;
    
    const category = document.getElementById('leave_category').value;
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (!category || !startDate || !endDate) return;
    
    const start = new Date(startDate);
    const end = new Date(endDate);
    const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
    
    let duration = days;
    if (category === 'half_day') {
        duration = days * 0.5;
    }
    
    const balances = employeeData.balances;
    let availableLeaves = 0;
    let dailyRate = 1000; // Default rate
    
    switch (category) {
        case 'full_day':
            availableLeaves = Math.min(balances.annual_leaves_remaining, balances.monthly_leaves_remaining);
            break;
        case 'half_day':
            availableLeaves = Math.min(balances.annual_leaves_remaining * 2, balances.monthly_half_leaves_remaining);
            dailyRate = dailyRate / 2;
            break;
        case 'short_leave':
            availableLeaves = Math.min(balances.short_leaves_remaining, balances.monthly_short_leaves_remaining);
            dailyRate = dailyRate / 4;
            break;
    }
    
    const excessLeaves = Math.max(0, duration - availableLeaves);
    const noPayAmount = excessLeaves * dailyRate;
    
    const warningDiv = document.getElementById('no-pay-warning');
    if (noPayAmount > 0) {
        warningDiv.style.display = 'block';
        document.getElementById('no-pay-amount').textContent = noPayAmount.toFixed(2);
    } else {
        warningDiv.style.display = 'none';
    }
}

// File upload handling
document.getElementById('doc-files').addEventListener('change', function(e) {
    const fileList = document.getElementById('file-list-items');
    const hiddenInput = document.getElementById('hidden-files');
    
    // Clear existing "no documents" message if it exists
    const noDocsMessage = fileList.querySelector('.text-gray-500');
    if (noDocsMessage) {
        noDocsMessage.remove();
    }
    
    hiddenInput.files = this.files;
    
    Array.from(this.files).forEach(file => {
        const li = document.createElement('li');
        li.innerHTML = `
            <span class="text-2xl">
                <i class="ri-file-pdf-2-fill"></i>
            </span>
            <span class="text-blue-500">${file.name}</span>
            <span onclick="removeNewDocument(this)" class="text-red-500 cursor-pointer ml-2">✖</span>
        `;
        fileList.appendChild(li);
    });
});

function removeDocument(documentPath) {
    if (confirm('Are you sure you want to remove this document?')) {
        // Find the document element and remove it
        const elements = document.querySelectorAll('#file-list-items li');
        elements.forEach(element => {
            const link = element.querySelector('a');
            if (link && link.href.includes(documentPath)) {
                element.remove();
            }
        });
        
        // Update existing files input
        updateExistingFiles();
    }
}

function removeNewDocument(element) {
    if (confirm('Are you sure you want to remove this document?')) {
        element.closest('li').remove();
    }
}

function updateExistingFiles() {
    const existingFiles = [];
    const links = document.querySelectorAll('#file-list-items a');
    
    links.forEach(link => {
        const href = link.getAttribute('href');
        if (href) {
            // Extract the file path from the asset URL
            const pathMatch = href.match(/storage\/(.+)$/);
            if (pathMatch) {
                existingFiles.push(pathMatch[1]);
            }
        }
    });
    
    document.getElementById('existing-files').value = JSON.stringify(existingFiles);
}

// Initialize existing files
document.addEventListener('DOMContentLoaded', function() {
    updateExistingFiles();
    handleCategoryChange(); // Initialize the form based on current category
});
</script>

