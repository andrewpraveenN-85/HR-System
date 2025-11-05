<div class="w-full relative bg-white rounded-3xl">
    <div class="w-full flex justify-center items-center">
        <div class="w-full flex justify-center items-center rounded-3xl">
            <div id="modal-container"
                class="w-full flex flex-col justify-start items-center relative bg-white nunito- p-2 rounded-3xl bg-gradient-to-r from-[#184E77] to-[#52B69A]">
                <div class="w-full flex flex-col justify-start items-center bg-white p-8 rounded-3xl space-y-8">
                    <div class="flex flex-col justify-center items-center space-y-4">
                        <p class="text-5xl text-black font-bold">Leave Application</p>
                        <p class="text-3xl text-[#00000080]">Apply for leave with balance tracking</p>
                    </div>

                    <!-- Employee Info Section -->
                    <div id="employee-info" class="w-full p-4 bg-gray-100 rounded-lg">
                        <h3 class="text-xl font-bold mb-4">Employee Leave Information</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <strong>Employee Name:</strong> <span id="emp-name"></span>
                            </div>
                            <div>
                                <strong>Department:</strong> <span id="emp-department"></span>
                            </div>
                        </div>

                        <h4 class="text-lg font-bold mt-4 mb-2">Leave Balances</h4>
                        <div class="grid grid-cols-4 gap-4 text-sm">
                            <div class="bg-blue-100 p-2 rounded">
                                <strong>Annual Leaves</strong><br />
                                <span id="annual-remaining"></span> remaining
                            </div>
                            <div class="bg-green-100 p-2 rounded">
                                <strong>Short Leaves</strong><br />
                                <span id="short-remaining"></span> remaining
                            </div>
                            <div class="bg-yellow-100 p-2 rounded">
                                <strong>Monthly Leaves</strong><br />
                                <span id="monthly-remaining"></span> remaining
                            </div>
                            <div class="bg-purple-100 p-2 rounded">
                                <strong>Monthly Half Leaves</strong><br />
                                <span id="half-remaining"></span> remaining
                            </div>
                        </div>

                        <h4 class="text-lg font-bold mt-4 mb-2">Recent Leave History</h4>
                        <div id="leave-history" class="max-h-32 overflow-y-auto">
                            <!-- Leave history will be populated here -->
                        </div>
                    </div>

                    <div class="w-full mx-auto p-6">
                        <form action="{{ route('leave.store') }}" method="POST" class="w-full mx-auto p-6"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="employment_ID" class="block text-xl text-black font-bold">Employee:</label>
                                    <select id="employment_ID" name="employment_ID" required
                                        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md focus:ring-blue-500 focus:border-blue-500 text-[#0000008C] font-bold select2"
                                        onchange="fetchEmployeeData()">
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
                                    <label for="leave_type" class="block text-xl text-black font-bold">Leave
                                        Type</label>
                                    <input type="text" id="leave_type" name="leave_type"
                                        placeholder="e.g., Sick Leave, Annual Leave"
                                        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                                </div>

                                <div>
                                    <label for="leave_category" class="block text-xl text-black font-bold">Leave
                                        Category:</label>
                                    <select id="leave_category" name="leave_category"
                                        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        onchange="handleCategoryChange()">
                                        <option value="">Select Category</option>
                                        <option value="full_day">Full Day Leave</option>
                                        <option value="half_day">Half Day Leave</option>
                                        <option value="short_leave">Short Leave</option>
                                    </select>
                                </div>

                                <!-- Placeholder div to maintain grid layout when category options are hidden -->
                                <div id="category_type_placeholder"></div>

                                <div id="half_day_options" class="hidden">
                                    <label for="half_day_type" class="block text-xl text-black font-bold">Half Day
                                        Type:</label>
                                    <select id="half_day_type" name="half_day_type"
                                        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Select Type</option>
                                        <option value="morning">Morning (8:30 AM - 1:00 PM)</option>
                                        <option value="evening">Evening (1:00 PM - 5:00 PM)</option>
                                    </select>
                                </div>

                                <div id="short_leave_options" class="hidden">
                                    <label for="short_leave_type" class="block text-xl text-black font-bold">Short Leave
                                        Type:</label>
                                    <select id="short_leave_type" name="short_leave_type"
                                        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Select Type</option>
                                        <option value="morning">Morning (8:30 AM - 10:00 AM)</option>
                                        <option value="evening">Evening (3:30 PM - 5:00 PM)</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="start_date" class="block text-xl text-black font-bold">Date
                                        From:</label>
                                    <input type="date" id="start_date" name="start_date"
                                        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        onchange="calculateDuration()" />
                                </div>

                                <div>
                                    <label for="end_date" class="block text-xl text-black font-bold">Date To:</label>
                                    <input type="date" id="end_date" name="end_date"
                                        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        onchange="calculateDuration()" />
                                </div>

                                <div id="time_inputs" class="col-span-2 hidden">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="start_time" class="block text-xl text-black font-bold">Start
                                                Time:</label>
                                            <input type="time" id="start_time" name="start_time"
                                                class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm" />
                                        </div>
                                        <div>
                                            <label for="end_time" class="block text-xl text-black font-bold">End
                                                Time:</label>
                                            <input type="time" id="end_time" name="end_time"
                                                class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm" />
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="status" class="block text-xl text-black font-bold">Approval
                                        Status:</label>
                                    <select id="status" name="status"
                                        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        onchange="handleStatusChange()">
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>

                                <div id="approved_by_field" style="display: none;">
                                    <label for="approved_person" class="block text-xl text-black font-bold">Approved
                                        By:</label>
                                    <input type="text" id="approved_person" name="approved_person"
                                        placeholder="Enter approved by"
                                        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                                </div>

                                <!-- Upload -->
                                <div>
                                    <label for="doc-files"
                                        class="flex items-center justify-center px-4 py-2 bg-[#184E77] border-2 border-[#52B69A80] text-white rounded-md cursor-pointer hover:bg-blue-600 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <span class="iconify" data-icon="ic:sharp-upload"
                                            style="width: 16px; height: 16px;"></span>
                                        <span class="ml-2">Upload Files</span>
                                    </label>

                                    <!-- Single input used for both selection and submission -->
                                    <input type="file" id="doc-files" name="supporting_documents[]"
                                        accept="application/pdf" class="hidden" multiple />

                                    <div id="file-list"
                                        class="text-black text-sm w-full bg-[#D9D9D980] flex flex-col justify-end items-end rounded-b-xl pr-4 pt-4">
                                        <p>Attached Files:</p>
                                        <ul id="file-list-items"></ul>
                                    </div>
                                </div>

                                <div class="col-span-2">
                                    <label for="description"
                                        class="block text-xl text-black font-bold">Description:</label>
                                    <textarea id="description" name="description" placeholder="Enter the reason for leave" rows="3"
                                        class="mt-1 block w-full px-3 py-2 border-2 border-[#1C1B1F80] rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                </div>

                                <!-- No Pay Warning -->
                                <div id="no-pay-warning"
                                    class="col-span-2 p-4 bg-red-100 border border-red-300 rounded-md"
                                    style="display: none;">
                                    <h4 class="text-red-800 font-bold">⚠️ No Pay Leave Warning</h4>
                                    <p class="text-red-700">This leave application exceeds available balances and will
                                        result in no-pay deductions.</p>
                                    <p class="text-red-700">
                                        <strong>Estimated No-Pay Amount: LKR <span id="no-pay-amount">0</span></strong>
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-center space-x-4 mt-6">
                                <button type="submit"
                                    class="px-6 py-3 bg-gradient-to-r from-[#184E77] to-[#52B69A] text-white rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8] text-xl font-bold">
                                    Submit Leave Application
                                </button>
                            </div>
                        </form>
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
    let employeeData = null;

    function handleStatusChange() {
        const status = document.getElementById('status').value;
        const approvedByField = document.getElementById('approved_by_field');
        const approvedPersonInput = document.getElementById('approved_person');

        if (!approvedByField || !approvedPersonInput) return;

        if (status === 'approved') {
            approvedByField.style.display = 'block';
            approvedPersonInput.required = true;
        } else {
            approvedByField.style.display = 'none';
            approvedPersonInput.required = false;
            approvedPersonInput.value = ''; // Clear the field when hidden
        }
    }

    function fetchEmployeeData() {
        const employeeId = document.getElementById('employment_ID').value;
        if (!employeeId) return;

        fetch(`/api/employee-leave-data?employee_id=${encodeURIComponent(employeeId)}`)
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    alert('Employee not found');
                    return;
                }

                employeeData = data;
                displayEmployeeInfo(data);
            })
            .catch((error) => {
                console.error('Error:', error);
                alert('Error fetching employee data');
            });
    }

    function displayEmployeeInfo(data) {
        document.getElementById('employee-info').style.display = 'block';

        // Basic info
        document.getElementById('emp-name').textContent = data.employee.full_name ?? '';
        document.getElementById('emp-department').textContent = data.employee?.department?.name ?? 'N/A';

        // Balances
        const balances = data.balances ?? {};
        document.getElementById('annual-remaining').textContent = balances.annual_leaves_remaining ?? 0;
        document.getElementById('short-remaining').textContent = balances.short_leaves_remaining ?? 0;
        document.getElementById('monthly-remaining').textContent = balances.monthly_leaves_remaining ?? 0;
        document.getElementById('half-remaining').textContent = balances.monthly_half_leaves_remaining ?? 0;

        // Leave history
        const historyDiv = document.getElementById('leave-history');
        historyDiv.innerHTML = '';

        const recent = Array.isArray(data.recent_leaves) ? data.recent_leaves : [];
        if (recent.length === 0) {
            historyDiv.innerHTML = '<p>No recent leave history</p>';
        } else {
            recent.forEach((leave) => {
                const leaveDiv = document.createElement('div');
                leaveDiv.className = 'p-2 bg-white rounded mb-1 text-sm';
                const cat = leave.leave_category || 'full_day';
                leaveDiv.innerHTML = `
          <strong>${leave.leave_type ?? ''}</strong> - ${leave.start_date} to ${leave.end_date}
          <br><span class="text-gray-600">${cat} (${leave.duration} days) - ${leave.status}</span>
        `;
                historyDiv.appendChild(leaveDiv);
            });
        }

        calculateNoPayAmount();
    }

    function handleCategoryChange() {
        const category = document.getElementById('leave_category').value;
        const halfDayOptions = document.getElementById('half_day_options');
        const shortLeaveOptions = document.getElementById('short_leave_options');
        const timeInputs = document.getElementById('time_inputs');
        const placeholder = document.getElementById('category_type_placeholder');

        const halfDaySelect = document.getElementById('half_day_type');
        const shortLeaveSelect = document.getElementById('short_leave_type');

        // Hide all conditional fields using Tailwind's hidden class
        halfDayOptions.classList.add('hidden');
        shortLeaveOptions.classList.add('hidden');
        timeInputs.classList.add('hidden');

        // Show placeholder by default
        placeholder.style.display = 'block';

        // Reset required flags
        if (halfDaySelect) halfDaySelect.required = false;
        if (shortLeaveSelect) shortLeaveSelect.required = false;

        if (category === 'half_day') {
            halfDayOptions.classList.remove('hidden');
            timeInputs.classList.remove('hidden');
            placeholder.style.display = 'none';
            if (halfDaySelect) halfDaySelect.required = true;
        } else if (category === 'short_leave') {
            shortLeaveOptions.classList.remove('hidden');
            timeInputs.classList.remove('hidden');
            placeholder.style.display = 'none';
            if (shortLeaveSelect) shortLeaveSelect.required = true;
        }

        calculateNoPayAmount();
    }

    function calculateDuration() {
        // Validate date ordering
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        if (startDate && endDate) {
            const s = new Date(startDate);
            const e = new Date(endDate);
            if (e < s) {
                alert('“Date To” cannot be earlier than “Date From”.');
                document.getElementById('end_date').value = '';
                document.getElementById('no-pay-warning').style.display = 'none';
                return;
            }
        }

        calculateNoPayAmount();
    }

    function calculateDurationInDays(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);

        if (end < start) return null;

        const ms = end - start;
        const days = Math.floor(ms / (1000 * 60 * 60 * 24)) + 1; // inclusive
        return days;
    }

    function calculateNoPayAmount() {
        if (!employeeData) return;

        const category = document.getElementById('leave_category').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        if (!category || !startDate || !endDate) return;

        const spanDays = calculateDurationInDays(startDate, endDate);
        if (spanDays === null) {
            document.getElementById('no-pay-warning').style.display = 'none';
            return;
        }

        const b = employeeData.balances || {};
        // Balances with fallbacks
        const annualDays = Number(b.annual_leaves_remaining ?? 0); // days
        const monthlyDays = Number(b.monthly_leaves_remaining ?? 0); // days
        const monthlyHalfs = Number(b.monthly_half_leaves_remaining ?? 0); // half-day count
        const shortCount = Number(b.short_leaves_remaining ?? 0); // count
        const monthlyShort = Number(b.monthly_short_leaves_remaining ?? 0); // count (if not in API, treated as 0)

        let requestedDayEq = 0;
        let availableDayEq = 0;

        if (category === 'full_day') {
            requestedDayEq = spanDays * 1.0;
            availableDayEq = Math.min(annualDays, monthlyDays); // both in days already
        } else if (category === 'half_day') {
            requestedDayEq = spanDays * 0.5;
            const annualDayEqCap = annualDays; // annual full days can fund that many day-eq of halves
            const monthlyDayEqCap = monthlyHalfs * 0.5; // each half = 0.5 day
            availableDayEq = Math.min(annualDayEqCap, monthlyDayEqCap);
        } else if (category === 'short_leave') {
            // Treat each day in the span as one short-leave slot; 1 short ≈ 0.25 day (adjust if your policy differs)
            requestedDayEq = spanDays * 0.25;
            const annualDayEqCap = shortCount * 0.25;
            const monthlyDayEqCap = monthlyShort * 0.25;
            availableDayEq = Math.min(annualDayEqCap, monthlyDayEqCap);
        }

        // Base daily rate; if API provides employee.daily_rate, use that.
        const baseDailyRate = Number(employeeData?.employee?.daily_rate ?? 1000);

        const excessDayEq = Math.max(0, requestedDayEq - availableDayEq);
        const noPayAmount = excessDayEq * baseDailyRate;

        const warningDiv = document.getElementById('no-pay-warning');
        if (noPayAmount > 0) {
            warningDiv.style.display = 'block';
            document.getElementById('no-pay-amount').textContent = noPayAmount.toFixed(2);
        } else {
            warningDiv.style.display = 'none';
        }
    }

    // File upload preview (single input used for both select + submit)
    document.getElementById('doc-files').addEventListener('change', function() {
        const fileList = document.getElementById('file-list-items');
        fileList.innerHTML = '';
        Array.from(this.files).forEach((file) => {
            const li = document.createElement('li');
            li.textContent = file.name;
            fileList.appendChild(li);
        });
    });

    // Initialize handlers and initial UI state
    document.addEventListener('DOMContentLoaded', function() {
        const statusEl = document.getElementById('status');
        if (statusEl) {
            statusEl.addEventListener('change', handleStatusChange);
        }
        // Ensure correct visibility on load (covers case when status has a preset value)
        handleStatusChange();
    });
</script>
