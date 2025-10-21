@extends('layouts.dashboard-layout')

@section('title', 'Employee Details')

@section('content')
<!-- Main Content -->

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif


<div class="flex flex-col items-start justify-start w-full px-16">

<div class="w-full pt-8">
  <div class="flex items-center justify-between w-full">
  <div class="w-full flex justify-end items-end pt-4 pr-2">
  <a href="{{ route('employee.edit', ['id' => $employee->id]) }}" class="flex items-center justify-center space-x-8 px-10 py-2 text-[#184E77] border-2 border-[#184E77] text-2xl bg-white rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
        <p class="text-3xl"><i class="ri-edit-box-line"></i></p>
        <span>Edit Details</span>
  </a>
  </div>
</div>
<nav class="flex px-5 py-3" aria-label="Breadcrumb">
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

 <div class="flex items-center justify-center  bg-gray-100">
  <div class="w-2/5 flex flex-col pt-8 pb-4 space-y-4 bg-gray-500 rounded-3xl shadow-lg">
      <div class="flex flex-col items-center justify-start w-full px-16 pt-8 space-y-8">
          <!--<div class="w-1/3 flex justify-start items-center">
          <img src="{{ $employee->image ? asset('storage/' . $employee->image) : 'https://via.placeholder.com/200/cccccc/666666?text=Employee' }}" class="w-48 h-48 rounded-full object-cover border-4 border-gray-300">
          </div>-->
          <div class="w-full flex flex-col justify-center items-center text-center space-y-4 nunito-">
              <p class="text-5xl text-white">{{ $employee->full_name }}</p>
              <p class="text-xl text-white">Emp No:{{ $employee->employee_id }}</p>
              <p class="text-3xl text-white ">{{ $employee->title }}</p>
          </div>
      </div>
      <div class="w-full h-1/2">
          <p class="text-xl text-[#00000099] text-justify">{{ $employee->description }}</p>
      </div>  
  </div>
</div>

<!--<div class="w-1/2 flex flex-col justify-start items-start nunito- rounded-3xl">
        <div class="w-full flex flex-col jusity-start items-start bg-[#D9D9D980] px-4 pt-4 rounded-t-xl">
            <p class="text-3xl font-bold text-black">Legal Documents</p>
            @if (!empty($employee->legal_documents) && is_array(json_decode($employee->legal_documents, true)))
            @foreach (json_decode($employee->legal_documents, true) as $document)
            <div class="mt-4 space-y-2">
            <ul>
               
                    <li>
                        Extract the file name from the path 
                        <span class="text-2xl">
                          <i class="ri-file-pdf-2-fill"></i>
                      </span>
                        @php
                            $fileName = basename($document);
                        @endphp

                         Display as a link with the actual document name 
                        <a href="{{ asset('storage/' . $document) }}" target="_blank" class="text-blue-500 underline">
                            {{ $fileName }}
                        </a>
                    </li>
                @endforeach
                @endif
            </ul>
            </div>
            
        </div>
    </div>
</div>-->

   <!-- <div class="w-full flex space-x-16 pt-8">
    <div class="w-full flex flex-col space-y-4 p-8 bg-[#D9D9D980] rounded-3xl cursor-pointer nunito focus:outline-none focus:ring-2 focus:ring-[#52B69A] focus:border-[#184E77]">
    <div class="w-full flex pl-8">
        <p class="text-3xl font-bold text-black">Employment Information</p>
    </div>
    <div class="w-full flex">
        <div class="w-1/2 flex flex-col pl-8 space-y-4 text-[#00000080]">
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
            <p class="text-xl">{{ $employee->title }}</p>
            <p class="text-xl">{{ $employee->department->name ?? 'Null'}}</p>
            <p class="text-xl">{{ $employee->department->branch ?? 'Null'}}</p>
            <p class="text-xl">{{ $employee->employment_type }}</p>
            <p class="text-xl">{{ $employee->manager_id ?? 'Null'}}</p>
            <p class="text-xl">{{ $employee->probation_start_date }}</p>
            <p class="text-xl">{{ $employee->probation_period }}</p>
          
        </div>
    </div>
    
    </div>-->
   <div class="w-full flex flex-col space-y-8 p-8 bg-[#D9D9D980] rounded-3xl mt-4">
    <div class="w-full flex pl-8">
        <p class="text-3xl font-bold text-black">Personal Information</p>
    </div>

    <div class="w-full flex flex-col space-y-4 pl-8 text-black">
        <!-- Full Name -->
        <div class="flex items-center space-x-4">
            <i class="ri-info-card-fill text-xl"></i>
            <span class="w-32 text-xl font-semibold">Full Name:</span>
            <span class="text-xl">{{ $employee->full_name }}</span>
        </div>

        <!-- Phone -->
        <div class="flex items-center space-x-4">
            <i class="ri-phone-fill text-xl"></i>
            <span class="w-32 text-xl font-semibold">Phone No:</span>
            <span class="text-xl">{{ $employee->phone }}</span>
        </div>

        <!-- Address -->
        <div class="flex items-center space-x-4">
            <i class="ri-home-2-fill text-xl"></i>
            <span class="w-32 text-xl font-semibold">Address:</span>
            <span class="text-xl">{{ $employee->address }}</span>
        </div>
    </div>
</div>


    </div>
           <!-- <div class="w-full flex space-x-8">
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
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="dob" class="text-xl">Date of Birth</label>
            </div>
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="address" class="text-xl">Living Address</label>
            </div>-->
 
   <!-- <div class="w-full flex space-x-16 pb-8 pt-8">
        <div class="w-full flex flex-col h-auto space-y-8 p-8 bg-[#D9D9D980] rounded-3xl nunito cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#52B69A] focus:border-[#184E77]">
    <div class="w-full flex pl-8">
        <p class="text-3xl font-bold text-black">Education and Experience</p>
    </div>
    <div class="w-full flex">
        <div class="w-1/2 flex flex-col pl-8 space-y-4 text-[#00000080]">
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="degree" class="text-xl">Degree</label>
            </div>
            <div class="w-full flex space-x-8">
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
            <p class="text-xl">{{ $employee->education->degree ?? 'N/A' }}</p>
            <p class="text-xl">{{ $employee->education->institution ?? 'N/A' }}</p>
            <p class="text-xl">{{ $employee->education->graduation_year ?? 'N/A' }}</p>
            <div class="w-full flex flex-col">
            <p class="text-xl">{{ $employee->education->work_experience_years ?? 'N/A' }}</p>
            <p class="text-xl">{{ $employee->education->work_experience_role ?? 'N/A' }}</p>
            <p class="text-xl">{{ $employee->education->work_experience_company ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
    
    </div>
    <div class="w-full flex flex-col h-auto space-y-8 p-8 bg-[#D9D9D980] rounded-3xl nunito cursor-pointer mt-4 focus:outline-none focus:ring-2 focus:ring-[#52B69A] focus:border-[#184E77]">
    <div class="w-full flex pl-8">
        <p class="text-3xl font-bold text-black">Training and Certification</p>
    </div>
    <div class="w-full flex">
        <div class="w-1/2 flex flex-col pl-8 space-y-4 text-[#00000080]">
            <div class="w-full flex space-x-8">
                <p class="text-xl"><i class="ri-info-card-fill"></i></p>
                <label for="course_name" class="text-xl">Course Name</label>
            </div>
            <div class="w-full flex space-x-8">
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
            <p class="text-xl">{{ $employee->education->course_name ?? 'N/A' }}</p>
            <p class="text-xl">{{ $employee->education->training_provider ?? 'N/A' }}</p>
            <p class="text-xl">{{ $employee->education->completion_date ?? 'N/A' }}</p>
            <p class="text-xl">{{ $employee->education->certification_status ?? 'N/A' }}</p>
        </div>
    </div>
</div>

    </div>-->
<div class="w-full flex flex-col h-auto space-y-8 p-8 bg-[#D9D9D980] rounded-3xl nunito cursor-pointer mt-4">
    <div class="w-full flex pl-8">
        <p class="text-3xl font-bold text-black">Bank Details</p>
    </div>

    <div class="w-full flex flex-col space-y-4 pl-8 text-black">
        <!-- Account Holder Name -->
        <div class="flex items-center space-x-4">
            <i class="ri-info-card-fill text-xl"></i>
            <span class="w-40 text-xl font-semibold">Account Holder Name:</span>
            <span class="text-xl">{{ $employee->bankDetails->account_holder_name ?? 'N/A' }}</span>
        </div>

        <!-- Bank Code -->
        <div class="flex items-center space-x-4">
            <i class="ri-bank-fill text-xl"></i>
            <span class="w-40 text-xl font-semibold">Bank Code:</span>
            <span class="text-xl">{{ $employee->bankDetails->bank_code ?? 'N/A' }}</span>
        </div>

        <!-- Account Number -->
        <div class="flex items-center space-x-4">
            <i class="ri-file-text-fill text-xl"></i>
            <span class="w-40 text-xl font-semibold">Account No:</span>
            <span class="text-xl">{{ $employee->bankDetails->account_number ?? 'N/A' }}</span>
        </div>

        <!-- Branch Code -->
        <div class="flex items-center space-x-4">
            <i class="ri-building-4-fill text-xl"></i>
            <span class="w-40 text-xl font-semibold">Branch Code:</span>
            <span class="text-xl">{{ $employee->bankDetails->branch_code ?? 'N/A' }}</span>
        </div>
    </div>
</div>

<div class="w-full flex flex-col h-auto space-y-8 p-8 bg-[#D9D9D980] rounded-3xl nunito cursor-pointer mt-4">
    <div class="w-full flex pl-8">
        <p class="text-3xl font-bold text-black">Salary Details</p>
    </div>

    <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-4 pl-8 pr-8 text-black">
        <div class="flex items-center space-x-4">
            <i class="ri-file-list-3-line text-xl"></i>
            <span class="w-48 text-xl font-semibold">EPF Number:</span>
            <span class="text-xl">{{ $employee->epf_no ?? 'N/A' }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <i class="ri-money-dollar-circle-line text-xl"></i>
            <span class="w-48 text-xl font-semibold">Basic Salary:</span>
            <span class="text-xl">{{ $employee->basic ? number_format($employee->basic, 2) : 'N/A' }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <i class="ri-wallet-3-line text-xl"></i>
            <span class="w-48 text-xl font-semibold">Budget Allowance:</span>
            <span class="text-xl">{{ $employee->budget_allowance ? number_format($employee->budget_allowance, 2) : 'N/A' }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <i class="ri-bus-2-fill text-xl"></i>
            <span class="w-48 text-xl font-semibold">Transport Allowance:</span>
            <span class="text-xl">{{ $employee->transport_allowance ? number_format($employee->transport_allowance, 2) : 'N/A' }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <i class="ri-award-line text-xl"></i>
            <span class="w-48 text-xl font-semibold">Attendance Allowance:</span>
            <span class="text-xl">{{ $employee->attendance_allowance ? number_format($employee->attendance_allowance, 2) : 'N/A' }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <i class="ri-smartphone-line text-xl"></i>
            <span class="w-48 text-xl font-semibold">Phone Allowance:</span>
            <span class="text-xl">{{ $employee->phone_allowance ? number_format($employee->phone_allowance, 2) : 'N/A' }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <i class="ri-car-line text-xl"></i>
            <span class="w-48 text-xl font-semibold">Car Allowance:</span>
            <span class="text-xl">{{ $employee->car_allowance ? number_format($employee->car_allowance, 2) : 'N/A' }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <i class="ri-funds-box-line text-xl"></i>
            <span class="w-48 text-xl font-semibold">Production Bonus:</span>
            <span class="text-xl">{{ $employee->production_bonus ? number_format($employee->production_bonus, 2) : 'N/A' }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <i class="ri-stamp-line text-xl"></i>
            <span class="w-48 text-xl font-semibold">Stamp Duty:</span>
            <span class="text-xl">{{ $employee->stamp_duty ? number_format($employee->stamp_duty, 2) : 'N/A' }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <i class="ri-bar-chart-2-line text-xl"></i>
            <span class="w-48 text-xl font-semibold">Gross (Basic + Budget):</span>
            <span class="text-xl">
                @php
                    $gross = ($employee->basic ?? 0) + ($employee->budget_allowance ?? 0);
                @endphp
                {{ $gross > 0 ? number_format($gross, 2) : 'N/A' }}
            </span>
        </div>
    </div>
</div>



</div>

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

</script>
@endsection
