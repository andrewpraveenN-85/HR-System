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

    <!-- Flex container for both sections -->
    <div class="flex justify-between items-stretch space-x-20 w-full">
        
        <!-- Employee Information (Left Side) -->
        <div class="w-1/2 bg-[#D9D9D980] p-8 rounded-3xl flex flex-col justify-between">
            <div>
                <h2 class="text-3xl font-bold mb-8 text-center text-[#1C1B1F]">Employee Information</h2>

                <div class="flex flex-col space-y-4 text-black font-bold">
                    <div>
                        <label for="first_name" class="text-xl">Full Name</label>
                        <input type="text" id="full_name" name="full_name" placeholder="Enter First Name"
                            class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required />
                    </div>

                    <div>
                        <label for="employee_id" class="text-xl">Employee ID</label>
                        <input type="text" id="employee_id" name="employee_id" placeholder="Enter Employee ID"
                            class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required />
                    </div>

                    <div>
                        <label for="title" class="text-xl">Job Title</label>
                        <input type="text" id="title" name="title" placeholder="Enter Job Title"
                            class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required />
                    </div>

                </div>
            </div>
        </div>

        <!-- Bank Details (Right Side) -->
        <div class="w-1/2 bg-[#D9D9D980] p-8 rounded-3xl flex flex-col justify-between">
            <div>
                <h2 class="text-3xl font-bold mb-8 text-center text-[#1C1B1F]">Bank Details</h2>

                <div class="flex flex-col space-y-4 text-black font-bold">
                    <div>
                        <label for="account_holder_name" class="text-xl">Account Holder Name</label>
                        <input type="text" id="account_holder_name" name="account_holder_name" placeholder="Enter Account Holder Name" value="{{ old('account_holder_name') }}" required
                            class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                    </div>

                    <div>
                        <label for="bank_name" class="text-xl">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" placeholder="Enter Bank Name" value="{{ old('bank_name') }}" required
                            class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                    </div>

                    <div>
                        <label for="bank_code" class="text-xl">Bank Code</label>
                        <input type="text" id="bank_code" name="bank_code" placeholder="Enter Bank Name"
                            class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                    </div>

                    <div>
                        <label for="account_no" class="text-xl">Account No</label>
                        <input type="text" id="account_number" name="account_number" placeholder="Enter Account No" value="{{ old('account_number') }}" required
                            class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                    </div>

                    <div>
                        <label for="branch_name" class="text-xl">Branch Name</label>
                        <input type="text" id="branch_name" name="branch_name" placeholder="Enter Branch code"
                            class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                    </div>

                    <div>
                        <label for="branch_code" class="text-xl">Branch Code</label>
                        <input type="text" id="branch_code" name="branch_code" placeholder="Enter Branch code"
                            class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Details -->
    <div class="w-full bg-[#D9D9D980] p-8 rounded-3xl mt-14">
        <h2 class="text-3xl font-bold mb-6 text-center text-[#1C1B1F]">Salary Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-black font-bold">
            <div class="flex flex-col space-y-2">
                <label for="epf_no" class="text-xl">EPF Number</label>
                <input type="text" id="epf_no" name="epf_no" placeholder="Enter EPF Number" value="{{ old('epf_no') }}"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="basic" class="text-xl">Basic Salary</label>
                <input type="number" id="basic" name="basic" placeholder="Enter Basic Salary" value="{{ old('basic') }}" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="budget_allowance" class="text-xl">Budget Allowance</label>
                <input type="number" id="budget_allowance" name="budget_allowance" placeholder="Enter Budget Allowance" value="{{ old('budget_allowance') }}" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="transport_allowance" class="text-xl">Transport Allowance</label>
                <input type="number" id="transport_allowance" name="transport_allowance" placeholder="Enter Transport Allowance" value="{{ old('transport_allowance') }}" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="attendance_allowance" class="text-xl">Attendance Allowance</label>
                <input type="number" id="attendance_allowance" name="attendance_allowance" placeholder="Enter Attendance Allowance" value="{{ old('attendance_allowance') }}" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="phone_allowance" class="text-xl">Phone Allowance</label>
                <input type="number" id="phone_allowance" name="phone_allowance" placeholder="Enter Phone Allowance" value="{{ old('phone_allowance') }}" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="car_allowance" class="text-xl">Car Allowance</label>
                <input type="number" id="car_allowance" name="car_allowance" placeholder="Enter Car Allowance" value="{{ old('car_allowance') }}" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="production_bonus" class="text-xl">Production Bonus</label>
                <input type="number" id="production_bonus" name="production_bonus" placeholder="Enter Production Bonus" value="{{ old('production_bonus') }}" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="stamp_duty" class="text-xl">Stamp Duty</label>
                <input type="number" id="stamp_duty" name="stamp_duty" placeholder="Enter Stamp Duty" value="{{ old('stamp_duty', '25.00') }}" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
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
