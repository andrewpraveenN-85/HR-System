@extends('layouts.dashboard-layout')

@section('title', 'Add New Employee')

@section('content')
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
<div class="flex flex-col items-start justify-start w-full px-16">


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
<div>
</div>
<form method="POST" action="{{ route('employee.update', $employee->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="w-full flex space-x-32">
        <div class="w-1/2 flex flex-col justify-center items-center nunito- space-y-4 p-8 bg-[#D9D9D980] rounded-3xl">
            <div class="w-full flex">
                <div class="w-1/3 flex justify-start items-center">
                    <img id="profileImage" src="{{ $employee->image ? asset('storage/' . $employee->image) : asset('build/assets/bg1.png') }}" class="w-48 h-48 rounded-full" onclick="triggerFileInput()">
                    <input type="file" name="image" id="image" style="display:none;" onchange="previewImage(event)">
                </div>
                <div class="w-2/3 flex flex-col justify-center items-start space-y-4 nunito-">
                <div class="w-3/4 space-y-4 pl-16 text-black font-bold">
                    <input type="hidden" name="id" value="{{ $employee->id }}">
                    <input type="text" id="first_name" name="first_name" value="{{ $employee->first_name }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A] " required />
                    <input type="text" id="last_name" name="last_name" value="{{ $employee->last_name }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A] " required />
                    <input type="text" id="employee_id" name="employee_id" value="{{ $employee->employee_id }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A] " required />
                </div>
                </div>
            </div>
            <div class="w-full h-1/2">
                <textarea id="description" name="description" rows="2" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">{{ $employee->description }}</textarea>
        </div>  
    </div>

    <!--<div class="w-1/2 flex flex-col justify-start items-start nunito- rounded-3xl">
    <div class="w-full flex justify-between items-center bg-[#D9D9D980] px-4 pt-4 rounded-t-xl">
            <p class="text-3xl font-bold text-black">Legal Documents</p>
            <label for="doc-files" class="flex items-center justify-center px-4 py-2 bg-[#184E77] border-2 border-[#52B69A80] text-white rounded-md cursor-pointer hover:bg-blue-600 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"> 
                <span class="iconify" data-icon="ic:sharp-upload" style="width: 16px; height: 16px;"></span>
                <span class="ml-2">Upload Files</span>
            </label>
            Visible File Input 
            <input type="file" id="doc-files" accept="application/pdf" class="hidden" multiple />

              Hidden File Input 
            <input type="file" name="legal_documents[]" id="hidden-files" class="hidden" multiple />
            <input type="hidden" id="existing-files-data" value='{{ $employee->legal_documents }}'>
            <input type="hidden" name="existing_files" id="existing-files">
        </div>
        
        <div id="file-list" class="text-black text-sm w-full bg-[#D9D9D980] flex flex-col justify-end items-end rounded-b-xl pr-4 pt-4">
            <p>Attached Files:</p>
            <ul id="file-list-items"></ul>
            <div class="mt-4 space-y-2">
    <ul id="file-list-items">
       
    </ul>
</div>
        </div>
        
    </div>-->
</div>   



        <!-- Employment Information 
    <div class="w-full flex space-x-16 pt-8">
    <div tabindex="0" class="w-full flex flex-col h-auto space-y-8 p-8 bg-[#D9D9D980] rounded-3xl cursor-pointer nunito focus:outline-none focus:ring-2 focus:ring-[#52B69A] focus:border-[#184E77]">
        <div class="w-full flex pl-8">
            <p class="text-3xl font-bold text-black">Employment Information</p>
        </div>
        <div class="w-full flex">
            <div class="w-1/2 flex flex-col pl-8 space-y-8 text-[#00000080]">
                <div class="w-full flex space-x-8">
                    <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                    <p class="text-xl">Job Title</p>
                </div>
                <div class="w-full flex space-x-8">
                    <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                    <p class="text-xl">Department</p>
                </div>
                <div class="w-full flex space-x-8">
                    <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                    <p class="text-xl">Branch</p>
                </div>
                <div class="w-full flex space-x-8">
                    <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                    <p class="text-xl">Employment Type</p>
                </div>
                <div class="w-full flex space-x-8">
                    <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                    <p class="text-xl">Manager ID</p>
                </div>
                <div class="w-full flex space-x-8">
                    <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                    <p class="text-xl">Probation Period</p>
                </div>
            </div>
            <div class="w-3/4 space-y-4 pl-16 text-black font-bold">
                <div class="w-full">
                    <input type="text" name="title" value="{{ $employee->title }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                </div>
                <div class="w-full">
                    <select name="name" id="name" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required>
                        <option disabled>Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->name }}" 
                                {{ $employee->department && $employee->department->name == $department->name ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full">
                    <select name="branch" id="branch" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" required>
                        <option disabled>Select Branch</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->branch }}" 
                                {{ $employee->department && $employee->department->branch == $department->branch ? 'selected' : '' }}>
                                {{ $department->branch }}
                            </option>
                        @endforeach
                    </select>
                </div>

            <div class="w-full">    
                <input type="text" name="employment_type" value="{{ $employee->employment_type }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full">    
                <input type="text" name="manager_id" value="{{ $employee->manager_id ?? 'Null' }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>

                <div class="w-full">
                    <input type="date" name="probation_start_date" value="{{ $employee->probation_start_date }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                </div>
                <div class="w-full">
                    <input type="text" name="probation_period" value="{{ $employee->probation_period }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                </div>
            </div>
    </div>
    </div>--> 

        <!-- Personal Information --> 
        <div tabindex="0" class="w-full flex flex-col h-auto space-y-8 p-8 bg-[#D9D9D980] rounded-3xl cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#52B69A] focus:border-[#184E77]">
    <div class="w-full flex pl-8">
        <p class="text-3xl font-bold text-black">Personal Information</p>
    </div>
    <div class="w-full flex">
        <div class="w-1/2 flex flex-col pl-8 space-y-8 text-[#00000080]">
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="full_name" class="text-xl">Full Name</label>
            </div>
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="age" class="text-xl">NIC</label>
            </div>
            <div class="w-full flex space-x-8 pt-4">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="email" class="text-xl">Email Address</label>
            </div>
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="phone" class="text-xl">Phone Number</label>
            </div>
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="gender" class="text-xl">Gender</label>
            </div>
            <div class="w-full flex space-x-8 pt-4">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="date_of_birth" class="text-xl">Date of Birth</label>
            </div>
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="address" class="text-xl">Living Address</label>
            </div>
        </div>
        <div class="w-3/4 space-y-4 pl-16 text-black font-bold">
            <div class="w-full">
                <input type="text" id="full_name" name="full_name" value="{{ $employee->full_name }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A] " required />
            </div>
            <div class="w-full">
                <input type="text" id="nic" name="nic" value="{{ $employee->nic }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full">
                <input type="email" id="email" name="email" value="{{ $employee->email }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full">
                <input type="text" id="phone" name="phone" value="{{ $employee->phone }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full">
                <select id="gender" name="gender" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">
                    <option disabled>Select Gender</option>
                    <option value="male" {{ $employee->gender == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $employee->gender == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ $employee->gender == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="w-full">
                <input type="date" id="date_of_birth" name="date_of_birth" value="{{ $employee->date_of_birth }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full">
                <textarea id="address" name="address" rows="2" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">{{ $employee->address }}</textarea>
            </div>
        </div>
    </div>
</div>
</div>

<!--
        Education and Experience
        <div class="w-full flex space-x-16 pb-8 pt-8">
    <div tabindex="0" class="w-full flex flex-col h-auto space-y-8 p-8 bg-[#D9D9D980] rounded-3xl nunito cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#52B69A] focus:border-[#184E77]">
    <div class="w-full flex pl-8">
        <p class="text-3xl font-bold text-black">Education and Experience</p>
    </div>
    <div class="w-full flex">
        <div class="w-1/2 flex flex-col pl-8 space-y-8 text-[#00000080]">
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="degree" class="text-xl">Degree</label>
            </div>
            <div class="w-full flex space-x-8 pt-4">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="institution" class="text-xl">Institution</label>
            </div>
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="graduation_year" class="text-xl">Graduation Year</label>
            </div>
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="work_experience" class="text-xl">Work Experience</label>
            </div>
        </div>
        <div class="w-3/4 space-y-4 pl-16 text-black font-bold">
            <div class="w-full">
                <input type="text" id="degree" name="degree" value="{{ $employee->education->degree ?? '' }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full">
                <input type="text" id="institution" name="institution" value="{{ $employee->education->institution ?? ''  }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full">
                <input type="number" id="graduation_year" name="graduation_year" value="{{ $employee->education->graduation_year ?? ''  }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full flex flex-col space-y-2">
                <input type="text" id="work_experience_years" name="work_experience_years" value="{{ $employee->education->work_experience_years ?? ''  }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                <input type="text" id="work_experience_role" name="work_experience_role" value="{{ $employee->education->work_experience_role ?? ''  }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                <input type="text" id="work_experience_company" name="work_experience_company" value="{{ $employee->education->work_experience_company ?? ''  }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
        </div>
    </div> 
    </div>

        Training and Certification  
        <div tabindex="0" class="w-full flex flex-col h-auto space-y-8 mt-8 p-8 bg-[#D9D9D980] rounded-3xl nunito cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#52B69A] focus:border-[#184E77]">
    <div class="w-full flex pl-8">
        <p class="text-3xl font-bold text-black">Training and Certification</p>
    </div>
    <div class="w-full flex">
        <div class="w-1/2 flex flex-col pl-8 space-y-8 text-[#00000080]">
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="course_name" class="text-xl">Course Name</label>
            </div>
            <div class="w-full flex space-x-8 pt-4">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="training_provider" class="text-xl">Training Provider</label>
            </div>
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="completion_date" class="text-xl">Completion Date</label>
            </div>
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="certification_status" class="text-xl">Certification Status</label>
            </div>
        </div>
        <div class="w-3/4 space-y-4 pl-16 text-black font-bold">
            <div class="w-full">
                <input type="text" id="course_name" name="course_name" value="{{ $employee->education->course_name ?? '' }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full">
                <input type="text" id="training_provider" name="training_provider" value="{{ $employee->education->training_provider ?? ''  }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full">
                <input type="date" id="completion_date" name="completion_date" value="{{ $employee->education->completion_date ?? ''  }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full">
                <select id="certification_status" name="certification_status" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]">
                    <option disabled>Select Certification Status</option>
                    <option value="merit" {{ $employee->education->certification_status == 'merit' ? 'selected' : '' }}>Merit</option>
                    <option value="distinction" {{ $employee->education->certification_status == 'distinction' ? 'selected' : '' }}>Distinction</option>
                    <option value="pass" {{ $employee->education->certification_status == 'pass' ? 'selected' : '' }}>Pass</option>
                </select>
            </div>
        </div>
    </div>
</div>

</div>-->
 <!-- Bank Details -->
 <div class="w-full flex space-x-16 pb-8 pt-8">
    <div class="w-full flex flex-col h-auto space-y-8 p-8 bg-[#D9D9D980] rounded-3xl nunito focus:outline-none focus:ring-2 focus:ring-[#52B69A] focus:border-[#184E77]">
        <div class="w-full flex pl-8">
            <p class="text-3xl font-bold text-black">Salary Details</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-black font-bold">
            <div class="flex flex-col space-y-2">
                <label for="epf_no" class="text-xl">EPF Number</label>
                <input type="text" id="epf_no" name="epf_no" value="{{ old('epf_no', $employee->epf_no) }}" placeholder="Enter EPF Number" required
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="basic" class="text-xl">Basic Salary</label>
                <input type="number" id="basic" name="basic" value="{{ old('basic', $employee->basic) }}" placeholder="Enter Basic Salary" min="0" step="0.01" required
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="budget_allowance" class="text-xl">Budget Allowance</label>
                <input type="number" id="budget_allowance" name="budget_allowance" value="{{ old('budget_allowance', $employee->budget_allowance) }}" placeholder="Enter Budget Allowance" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="transport_allowance" class="text-xl">Transport Allowance</label>
                <input type="number" id="transport_allowance" name="transport_allowance" value="{{ old('transport_allowance', $employee->transport_allowance) }}" placeholder="Enter Transport Allowance" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="attendance_allowance" class="text-xl">Attendance Allowance</label>
                <input type="number" id="attendance_allowance" name="attendance_allowance" value="{{ old('attendance_allowance', $employee->attendance_allowance) }}" placeholder="Enter Attendance Allowance" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="phone_allowance" class="text-xl">Phone Allowance</label>
                <input type="number" id="phone_allowance" name="phone_allowance" value="{{ old('phone_allowance', $employee->phone_allowance) }}" placeholder="Enter Phone Allowance" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="car_allowance" class="text-xl">Car Allowance</label>
                <input type="number" id="car_allowance" name="car_allowance" value="{{ old('car_allowance', $employee->car_allowance) }}" placeholder="Enter Car Allowance" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="production_bonus" class="text-xl">Production Bonus</label>
                <input type="number" id="production_bonus" name="production_bonus" value="{{ old('production_bonus', $employee->production_bonus) }}" placeholder="Enter Production Bonus" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="flex flex-col space-y-2">
                <label for="stamp_duty" class="text-xl">Stamp Duty</label>
                <input type="number" id="stamp_duty" name="stamp_duty" value="{{ old('stamp_duty', $employee->stamp_duty ?? 25.00) }}" placeholder="Enter Stamp Duty" min="0" step="0.01"
                    class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
                <span class="text-sm font-normal text-[#00000080]">Default value is 25.00. Adjust if needed.</span>
            </div>
        </div>
    </div>
</div>
 <!-- Bank Details -->
 <div class="w-full flex space-x-16 pb-8 pt-8">
    <div tabindex="0" class="w-full flex flex-col h-auto space-y-8 p-8 bg-[#D9D9D980] rounded-3xl nunito cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#52B69A] focus:border-[#184E77]">
    <div class="w-full flex pl-8">
        <p class="text-3xl font-bold text-black">Bank Details</p>
    </div>
    <div class="w-full flex">
        <div class="w-1/2 flex flex-col pl-8 space-y-8 text-[#00000080]">
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="account_holder_name" class="text-xl">Account Holder Name</label>
            </div>
            <div class="w-full flex space-x-8 pt-4">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="bank_name" class="text-xl">Bank Name</label>
            </div>
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="account_no" class="text-xl">Account No</label>
            </div>
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="branch_name" class="text-xl">Branch Name</label>
            </div>
        </div>
        <div class="w-3/4 space-y-4 pl-16 text-black font-bold">
            <div class="w-full">
                <input type="text" id="account_holder_name" name="account_holder_name" value="{{ old('account_holder_name', $employee->account_holder_name ?? '') }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full">
                <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name', $employee->bank_name ?? '')  }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full">
                <input type="text" id="account_number" name="account_number" value="{{ old('account_number', $employee->account_no ?? '') }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
            <div class="w-full flex flex-col space-y-2">
                <input type="text" id="branch_name" name="branch_name" value="{{ old('branch_name', $employee->branch_name ?? '')  }}" class="w-full p-2 text-xl border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#52B69A]" />
            </div>
        </div>
    </div> 
    </div>
</div>

<button class="flex items-center justify-center space-x-2 px-10 py-2 text-white text-2xl bg-gradient-to-r from-[#184E77] to-[#52B69A] rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
        <p class="text-3xl"><i class="ri-add-fill"></i></p>
        <span>Save Employee</span>
</button>
</div>
    </form>

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
function triggerFileInput() {
        document.getElementById('image').click();
    }
    // Preview the selected image in the img element
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profileImage').src = e.target.result;
            };
            reader.readAsDataURL(file);
            
            uploadImage(file);
        }
    }


    let existingFilesList = [];
let selectedFiles = new DataTransfer();

document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('doc-files');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            handleFileSelection(this);
        });
    }

    const existingFilesData = document.getElementById('existing-files-data');
    if (existingFilesData) {
        initializeExistingFiles(existingFilesData.value);
    }
});

function handleFileSelection(input) {
    const files = Array.from(input.files);
    
    files.forEach(file => {
        // Check for duplicates in both new and existing files
        const isDuplicateNew = Array.from(selectedFiles.files)
            .some(existing => existing.name === file.name);
        const isDuplicateExisting = existingFilesList
            .some(existing => existing.split('/').pop() === file.name);

        if (isDuplicateNew || isDuplicateExisting) {
            alert(`File "${file.name}" is already selected.`);
            return;
        }

        selectedFiles.items.add(file);
    });

    refreshFileList();
    input.value = ''; // Clear the input for future selections
}

function removeFile(identifier, isExisting = true) {
    if (isExisting) {
        // Remove from existing files list
        existingFilesList = existingFilesList.filter(path => path !== identifier);
    } else {
        // Remove from selected files using filename
        const newFiles = new DataTransfer();
        Array.from(selectedFiles.files).forEach(file => {
            if (file.name !== identifier) {
                newFiles.items.add(file);
            }
        });
        selectedFiles = newFiles;
    }
    
    refreshFileList();
    updateHiddenInputs(); // Ensure hidden inputs are updated after removal
}

function refreshFileList() {
    const fileListDisplay = document.getElementById('file-list-items');
    if (!fileListDisplay) return;
    
    fileListDisplay.innerHTML = ''; // Clear the list

    // Display existing files first
    existingFilesList.forEach(filePath => {
        const fileName = filePath.split('/').pop();
        addFileToDisplay(fileName, filePath, true);
    });

    // Then display newly selected files
    Array.from(selectedFiles.files).forEach(file => {
        addFileToDisplay(file.name, file.name, false);
    });

    // Update hidden inputs for form submission
    updateHiddenInputs();
}

function addFileToDisplay(fileName, identifier, isExisting) {
    const fileListDisplay = document.getElementById('file-list-items');
    const listItem = document.createElement('li');
    listItem.className = 'flex items-center space-x-2 mb-2';
    
    listItem.innerHTML = `
        <span class="text-2xl">
            <i class="ri-file-pdf-2-fill"></i>
        </span>
        ${isExisting ? 
            `<a href="/storage/${identifier}" target="_blank" class="text-blue-500 underline">${fileName}</a>` :
            `<span class="text-blue-500">${fileName}</span>`
        }
        <span onclick="removeFile('${identifier}', ${isExisting})" class="text-red-500 cursor-pointer ml-2">✖</span>`;
    
    fileListDisplay.appendChild(listItem);
}

function updateHiddenInputs() {
    // Update existing files input
    const existingFilesInput = document.getElementById('existing-files');
    if (existingFilesInput) {
        existingFilesInput.value = JSON.stringify(existingFilesList);
    }

    // Update new files input
    const hiddenFilesInput = document.getElementById('hidden-files');
    if (hiddenFilesInput) {
        hiddenFilesInput.files = selectedFiles.files;
    }
}

function initializeExistingFiles(existingFilesJson) {
    try {
        existingFilesList = JSON.parse(existingFilesJson || '[]');
        refreshFileList();
    } catch (error) {
        console.error('Error initializing existing files:', error);
    }
}

function triggerFileInput() {
        document.getElementById('image').click();
    }

    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('profileImage');
            output.src = reader.result;  // Update the image preview
        };
        reader.readAsDataURL(event.target.files[0]);  // Convert image to data URL for preview
    }


    function removeFile(identifier, isExisting) {
    if (isExisting) {
        existingFilesList = existingFilesList.filter(path => path !== identifier);
    } else {
        const newDataTransfer = new DataTransfer();
        Array.from(selectedFiles.files).forEach(file => {
            if (file.name !== identifier) {
                newDataTransfer.items.add(file);
            }
        });
        selectedFiles = newDataTransfer;
    }
    
    refreshFileList();
}
</script>
  
@endsection
