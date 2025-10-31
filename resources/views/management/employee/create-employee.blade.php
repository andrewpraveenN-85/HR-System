@extends('layouts.dashboard-layout')

@section('title', 'Add New Employee')

@section('content')
<div class="flex flex-col items-start justify-start w-full px-16">
    
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
    <nav class="flex px-5 py-3 mt-4 mb-4" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
        <a href="#" class="inline-flex items-center text-3xl font-medium text-[#00000080] hover:text-blue-600">
            Employee
        </a>
        </li>
        <li>
        <div class="flex items-center">
            <p class="text-[#00000080] text-3xl"><i class="ri-arrow-right-wide-line"></i></p>
            <a href="#" class="ml-1 font-medium text-[#00000080] text-3xl hover:text-blue-600">Employee Management</a>
        </div>
        </li>
    </ol>
    </nav>
<div class="w-full flex justify-center px-16">

    <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" class="w-full px-16">
    @csrf

         <!-- ========================= FIRST DIV ========================= -->
        <div class="bg-[#D9D9D980] p-8 rounded-3xl flex flex-col md:flex-row items-center space-y-6 md:space-y-0 md:space-x-10">
            <!-- Photo Upload -->
            <div class="flex flex-col items-center">
                <div class="relative group cursor-pointer" onclick="triggerFileInput()">
                    <div id="imagePlaceholder" class="w-48 h-48 rounded-full flex flex-col items-center justify-center bg-gray-200 border-2 border-dashed border-[#52B69A]">
                        <i class="ri-user-add-line text-gray-400 text-4xl mb-2"></i>
                        <span class="text-gray-500 text-sm font-medium text-center px-4">Upload Employee Photo</span>
                    </div>
                    <img id="profileImage" src="{{ asset('build/assets/bg1.png') }}" class="w-48 h-48 rounded-full object-cover border-2 border-[#52B69A] hidden">
                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <i class="ri-camera-fill text-white text-2xl mb-1"></i>
                        <span class="text-white text-sm font-semibold" id="hover-text">Upload Photo</span>
                    </div>
                </div>
                <div id="imageActions" class="mt-2 gap-2 hidden">
                    <button type="button" onclick="triggerFileInput()" class="px-3 py-1 text-xs bg-[#184E77] text-white rounded-md hover:bg-[#1B5A8A]">Change Photo</button>
                    <button type="button" onclick="removeImage()" class="px-3 py-1 text-xs bg-red-500 text-white rounded-md hover:bg-red-600">Remove</button>
                </div>
                <input type="file" name="image" id="image" style="display:none;" accept="image/*" onchange="previewImage(event)">
            </div>

            <!-- Full Name & Employee ID -->
            <div class="flex flex-col w-full md:w-2/3 space-y-4">
                <div>
                    <label for="full_name" class="text-xl font-bold">Full Name</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Enter Full Name" value="{{ old('full_name') }}"
                        class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required />
                </div>
                <div>
                    <label for="employee_id" class="text-xl font-bold">Employee ID</label>
                    <input type="text" id="employee_id" name="employee_id" placeholder="Enter Employee ID" value="{{ old('employee_id') }}"
                        class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required />
                </div>
            </div>
        </div>
<!-- ========================= PERSONAL INFORMATION ========================= -->
<div class="w-full bg-[#D9D9D980] p-8 rounded-3xl mt-10">
    <h2 class="text-3xl font-bold mb-6 text-center text-[#1C1B1F]">Personal Information</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="flex flex-col space-y-2">
            <label for="full_name" class="text-xl font-bold">Full Name</label>
            <input type="text" id="full_name" name="personal_full_name" placeholder="Enter Full Name"
                class="w-full p-2 text-lg border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
        </div>

        <div class="flex flex-col space-y-2">
            <label for="nic" class="text-xl font-bold">NIC</label>
            <input type="text" id="nic" name="nic" placeholder="Enter NIC Number"
                class="w-full p-2 text-lg border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
        </div>

        <div class="flex flex-col space-y-2">
            <label for="email" class="text-xl font-bold">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter Email Address"
                class="w-full p-2 text-lg border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
        </div>

        <div class="flex flex-col space-y-2">
            <label for="phone" class="text-xl font-bold">Phone Number</label>
            <input type="text" id="phone" name="phone" placeholder="Enter Phone Number"
                class="w-full p-2 text-lg border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
        </div>

        <div class="flex flex-col space-y-2">
            <label for="gender" class="text-xl font-bold">Gender</label>
            <select id="gender" name="gender"
                class="w-full p-2 text-lg border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="flex flex-col space-y-2">
            <label for="date_of_birth" class="text-xl font-bold">Date of Birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth"
                class="w-full p-2 text-lg border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
        </div>

        <div class="col-span-2 flex flex-col space-y-2">
            <label for="address" class="text-xl font-bold">Living Address</label>
            <input type="text" id="address" name="address" placeholder="Enter Living Address"
                class="w-full p-2 text-lg border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
        </div>
    </div>
</div>


<!-- ========================= SECOND DIV ========================= -->
<div class="bg-[#D9D9D980] p-8 rounded-3xl flex flex-col md:flex-row space-y-6 md:space-y-0 md:space-x-10 w-full mt-10">
    <!-- Employee Info + Bank Details -->
   
       <!-- Employment Information -->
<div class="w-full md:w-1/2 space-y-4">
    <h2 class="text-3xl font-bold mb-4 text-center text-[#1C1B1F]">Employment Information</h2>

    <div>
        <label for="title" class="text-xl font-bold">Job Title</label>
        <input type="text" id="title" name="title" placeholder="Enter Job Title"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required>
    </div>

    <div>
        <label for="department_id" class="text-xl font-bold">Department ID</label>
        <input type="text" id="department_id" name="department_id" placeholder="Enter Department ID"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required>
    </div>

    <div>
        <label for="name" class="text-xl font-bold">Department</label>
        <select name="name" id="name"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required>
            <option value="">Select Department</option>
            @foreach($departments->unique('name') as $department)
                <option value="{{ $department->name }}">{{ $department->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="branch" class="text-xl font-bold">Branch</label>
        <select name="branch" id="branch"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required>
            <option value="">Select Branch</option>
            @foreach($departments->unique('branch') as $department)
                <option value="{{ $department->branch }}">{{ $department->branch }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="employment_type" class="text-xl font-bold">Employment Type</label>
        <select id="employment_type" name="employment_type"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required>
            <option value="" disabled selected>Select Employment Type</option>
            <option value="full time">Full Time</option>
            <option value="part time">Part Time</option>
        </select>
    </div>

    <div class="w-full" @if($isFirstEmployee) style="display: none;" @endif>
        <label for="manager_id" class="text-xl font-bold">Manager</label>
        <select name="manager_id" id="manager_id"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]"
            @if($isFirstEmployee) disabled @endif @if(!$isFirstEmployee) required @endif>
            <option value="">Select Manager</option>
            @foreach($employees->unique('employee_id') as $employee)
                <option value="{{ $employee->id }}">{{ $employee->employee_id }} - {{ $employee->first_name }}</option>
            @endforeach
        </select>
    </div>
    <div >
        <label for="probation_period" class="text-xl font-bold">Probation Period</label>
                    <input type="date" name="probation_start_date" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                </div>
                <div>
                    <input type="text" name="probation_period" placeholder="Enter Probation Period" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                </div>
            </div>




        <!-- Bank Details -->
        <div class="w-full md:w-1/2 space-y-4">
            <h2 class="text-3xl font-bold mb-4 text-center text-[#1C1B1F]">Bank Details</h2>

            <div>
                <label for="account_holder_name" class="text-xl font-bold">Account Holder Name</label>
                <input type="text" id="account_holder_name" name="account_holder_name" placeholder="Enter Account Holder Name" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required>
            </div>

            <div>
                <label for="bank_name" class="text-xl font-bold">Bank Name</label>
                <input type="text" id="bank_name" name="bank_name" placeholder="Enter Bank Name" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required>
            </div>

            <div>
                <label for="bank_code" class="text-xl font-bold">Bank Code</label>
                <input type="text" id="bank_code" name="bank_code" placeholder="Enter Bank Code" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">
            </div>

            <div>
                <label for="account_number" class="text-xl font-bold">Account No</label>
                <input type="text" id="account_number" name="account_number" placeholder="Enter Account No" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required>
            </div>

            <div>
                <label for="branch_name" class="text-xl font-bold">Branch Name</label>
                <input type="text" id="branch_name" name="branch_name" placeholder="Enter Branch Name" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">
            </div>

            <div>
                <label for="branch_code" class="text-xl font-bold">Branch Code</label>
                <input type="text" id="branch_code" name="branch_code" placeholder="Enter Branch Code" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">
            </div>
        </div>
    </div>
</div>


<!-- ========================= THIRD DIV: Salary Details ========================= -->
<div class="bg-[#D9D9D980] p-8 rounded-3xl mt-10 w-full">
    <h2 class="text-3xl font-bold mb-6 text-center text-[#1C1B1F]">Salary Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-black font-bold">
        <div class="flex flex-col space-y-2">
            <label for="epf_no" class="text-xl font-bold">EPF Number</label>
            <input type="text" id="epf_no" name="epf_no" placeholder="Enter EPF Number" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required>
        </div>

        <div class="flex flex-col space-y-2">
            <label for="basic" class="text-xl font-bold">Basic Salary</label>
            <input type="number" id="basic" name="basic" placeholder="Enter Basic Salary" min="0" step="0.01" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required>
        </div>

        <div class="flex flex-col space-y-2">
            <label for="budget_allowance" class="text-xl font-bold">Budget Allowance</label>
            <input type="number" id="budget_allowance" name="budget_allowance" placeholder="Enter Budget Allowance" min="0" step="0.01" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">
        </div>

        <div class="flex flex-col space-y-2">
            <label for="transport_allowance" class="text-xl font-bold">Transport Allowance</label>
            <input type="number" id="transport_allowance" name="transport_allowance" placeholder="Enter Transport Allowance" min="0" step="0.01" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">
        </div>

        <div class="flex flex-col space-y-2">
            <label for="attendance_allowance" class="text-xl font-bold">Attendance Allowance</label>
            <input type="number" id="attendance_allowance" name="attendance_allowance" placeholder="Enter Attendance Allowance" min="0" step="0.01" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">
        </div>

        <div class="flex flex-col space-y-2">
            <label for="phone_allowance" class="text-xl font-bold">Phone Allowance</label>
            <input type="number" id="phone_allowance" name="phone_allowance" placeholder="Enter Phone Allowance" min="0" step="0.01" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">
        </div>

        <div class="flex flex-col space-y-2">
            <label for="car_allowance" class="text-xl font-bold">Car Allowance</label>
            <input type="number" id="car_allowance" name="car_allowance" placeholder="Enter Car Allowance" min="0" step="0.01" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">
        </div>

        <div class="flex flex-col space-y-2">
            <label for="production_bonus" class="text-xl font-bold">Production Bonus</label>
            <input type="number" id="production_bonus" name="production_bonus" placeholder="Enter Production Bonus" min="0" step="0.01" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">
        </div>

        <div class="flex flex-col space-y-2">
            <label for="stamp_duty" class="text-xl font-bold">Stamp Duty</label>
            <input type="number" id="stamp_duty" name="stamp_duty" placeholder="Enter Stamp Duty" value="25.00" min="0" step="0.01" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">
            <span class="text-sm font-normal text-[#00000080]">Default value is 25.00. Adjust if needed.</span>
        </div>
    </div>
</div>


    <!-- Submit Button -->
    <div class="w-full flex justify-end mt-10">
        <button type="submit" class="px-8 py-3 bg-[#52B69A] text-white font-bold text-xl rounded-lg hover:bg-[#40916C] transition duration-300">
            Save Employee
        </button>
    </div>
</form>
</div>
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
    // Trigger the file input when the image is clicked
    let selectedFiles = new DataTransfer();

    function handleFileSelection(input) {
        const files = Array.from(input.files);
        const fileListDisplay = document.getElementById('file-list-items');
        const hiddenInput = document.getElementById('hidden-files');

        files.forEach((file) => {
            selectedFiles.items.add(file);

            const listItem = document.createElement('li');
            listItem.textContent = file.name;

            const removeButton = document.createElement('span');
            removeButton.textContent = ' ✖';
            removeButton.style.color = 'red';
            removeButton.style.cursor = 'pointer';
            removeButton.style.marginLeft = '10px';
            removeButton.onclick = function () {
                removeFile(file.name);
                listItem.remove();
            };

            listItem.appendChild(removeButton);
            fileListDisplay.appendChild(listItem);
        });

        hiddenInput.files = selectedFiles.files;
        input.value = ''; // Clear the visible input to allow re-upload
    }

    function removeFile(fileName) {
        const newDataTransfer = new DataTransfer();
        Array.from(selectedFiles.files).forEach((file) => {
            if (file.name !== fileName) {
                newDataTransfer.items.add(file);
            }
        });

        selectedFiles = newDataTransfer;
        document.getElementById('hidden-files').files = selectedFiles.files;
    }

    document.getElementById('doc-files').addEventListener('change', function () {
        handleFileSelection(this);
    });


    function triggerFileInput() {
        document.getElementById('image').click();
    }

    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('profileImage');
            const placeholder = document.getElementById('imagePlaceholder');
            const imageActions = document.getElementById('imageActions');
            const hoverText = document.getElementById('hover-text');
            
            output.src = reader.result;  // Update the image preview
            output.classList.remove('hidden');  // Show the uploaded image
            placeholder.classList.add('hidden');  // Hide the placeholder
            imageActions.style.display = 'flex';  // Show action buttons
            imageActions.classList.remove('hidden');
            hoverText.textContent = 'Change Photo';  // Update hover text
        };
        reader.readAsDataURL(event.target.files[0]);  // Convert image to data URL for preview
    }

    function removeImage() {
        const output = document.getElementById('profileImage');
        const placeholder = document.getElementById('imagePlaceholder');
        const imageActions = document.getElementById('imageActions');
        const imageInput = document.getElementById('image');
        const hoverText = document.getElementById('hover-text');
        
        // Reset image
        output.src = '';
        output.classList.add('hidden');  // Hide the uploaded image
        placeholder.classList.remove('hidden');  // Show the placeholder
        imageActions.style.display = 'none';  // Hide action buttons
        imageActions.classList.add('hidden');
        imageInput.value = '';  // Clear the file input
        hoverText.textContent = 'Upload Photo';  // Reset hover text
    }

</script>
  
@endsection
