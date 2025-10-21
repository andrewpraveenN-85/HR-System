@extends('layouts.dashboard-layout')

@section('title', 'Employee Management')

@section('content')
<div class="w-full p-4 flex h-auto bg-[#FFFFFF]">
    <div class="flex flex-col items-start justify-start w-full px-2">

        <div class="w-full pt-4">
            <div class="flex items-center justify-between w-full">
                <div class="flex">
                    <p class="text-6xl font-bold text-black nunito-">EMPLOYEE</p>
                </div>
                <div class="flex items-center space-x-4">
 
                    <!-- Add Employee Button -->
                    <a href="{{ route('employees.create') }}" 
   class="flex items-center justify-center space-x-2 px-8 py-2 text-white text-2xl bg-gradient-to-r from-[#184E77] to-[#52B69A] rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]">
    <p class="text-3xl"><i class="ri-add-fill"></i></p>
    <span>Add Employee</span>
</a>
                </div>
            </div>
        </div>
        <nav class="flex py-3" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="#" class="inline-flex items-center text-3xl font-medium text-[#00000080] hover:text-blue-600">
                        Employee
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <p class="text-[#00000080] text-3xl"><i class="ri-arrow-right-wide-line"></i></p>
                        <a href="#" class=" font-medium text-[#00000080] text-3xl hover:text-blue-600">Employee Management</a>
                    </div>
                </li>
            </ol>
        </nav>
        <div class="flex">
 
        @if(request('search'))
            <span class="text-xl text-[#00000080]">- Results for "{{ request('search') }}"</span>
        @endif
 


</div>
<div class="w-1/2 flex space-y-2 pb-8  pt-8">
    <form method="GET" action="{{ route('employees.search') }}" class="flex w-5/6 space-x-4">
        <input 
            name="search" 
            id="input-field" 
            type="text" 
            placeholder="Search employee here" 
            class="w-full px-4 py-2 border-2 border-[#00000080] text-2xl text-[#00000080] rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
            value="{{ request('search') }}" 
        />
        <button 
            type="submit" 
            class="text-white text-2xl px-6 py-2 bg-gradient-to-r from-[#184E77] to-[#52B69A] rounded-xl shadow-sm hover:from-[#1B5A8A] hover:to-[#60C3A8]"
        >
            Search
        </button>
    </form>
</div>

        <!-- Dynamic Employee Cards -->
        <div class="w-full grid grid-cols-1 md:grid-cols-4 gap-8 items-center pt-8 pb-8">

        @foreach ($employees as $employee)
    <a href="{{ route('employee.show', $employee->id) }}" class="block relative">
        <div class="border-2 border-[#00000066] p-8 space-y-4 rounded-3xl relative bg-blue-100 shadow-xl">
            <!-- Icon in top-right corner -->
            <div class="absolute top-4 right-4">
                <form action="{{ route('employee.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-black-600 hover:text-red-800">
                        <i class="ri-delete-bin-line text-2xl"></i>
                    </button>
                </form>
            </div>
            <!-- Rest of your employee card content -->
            <div class="w-full flex justify-center items-center">
               <!-- <div class="w-1/2 flex justify-start items-center">
                    <img src="{{ $employee->image ? asset('storage/' . $employee->image) : asset('build/assets/bg1.png') }}" class="w-32 h-32 rounded-full">
                </div>-->
                <div class="w-1/2 flex flex-col justify-center items-center space-y-8 pt-4">
                    <div class="w-full h-1/2 flex justify-center items-center">
                        <span class="px-6 py-1 text-sm font-medium rounded-xl
                            {{ $employee->current === 'Active' ? 'text-[#008526] bg-[#9ce99280] border border-[#47B439] rounded-xl' : 'text-[#ad0000] bg-[#ffb5b5] border-[#FF0000]' }}">
                            {{ $employee->current }}
                        </span>
                    </div>
                    <div class="w-full h-1/2 flex flex-col justify-start items-center">
                       
                        <p class="text-black nunito font-bold text-2xl style="font-weight: 700;">{{ $employee->full_name}}</p>
                        
                        
                    </div>
                </div>
            </div>
           <!-- <div class="w-full flex flex-col space-y-2 pt-4">
                <p class="text-4xl text-black nunito- font-bold" style="font-weight: 700;">{{ $employee->first_name }}</p>
                <p class="text-4xl text-black nunito- font-bold" style="font-weight: 700;">{{ $employee->last_name }}</p>
                
            </div>-->
            <div class="w-full flex justify-between items-center">
                <div class="w-full flex flex-col">
                    <p class="text-xl nunito- text-[#00000080] font-bold" style="font-weight: 100;">{{ $employee->title }}</p>
                    <p class="text-xl nunito- text-[#00000080] font-bold">Department -<span class="text-black font-bold"> {{ explode(' ', $employee->department->name ?? '')[0] ?? 'No Department' }}</span></p>
                    <p class="text-l nunito- text-black font-bold" style="font-weight: 700;"></p>
                    <p class=" text-[#00000080] nunito-">Employee ID : {{ $employee->id }}</p>
                </div>
                <!--<div class="w-full flex flex-col">
                    <p class="text-xl nunito- text-[#00000080] font-bold">Hired Date </p>
                    <p class="text-l nunito- text-black font-bold" style="font-weight: 700;">{{ $employee->probation_start_date }}</p>
                </div>-->
            </div>
            <div class="w-full flex flex-col justify-start items-start space-y-4">
               <!-- <div class="w-full flex justify-start items-center space-x-2">
                    <div class="w-8 h-8 flex justify-center items-center border border-black rounded-full p-1">
                        <p class="text-xl"><i class="ri-mail-line"></i></p>
                    </div>
                    <p class="text-black font-bold nunito-" style="font-weight: 600;">{{ $employee->email }}</p>
                </div>-->
                <div class="w-full flex justify-start items-center space-x-2">
                    <div class="w-8 h-8 flex justify-center items-center border border-black rounded-full p-1">
                        <p class="text-xl"><i class="ri-phone-line"></i></p>
                    </div>
                    <p class="text-black font-bold nunito-" style="font-weight: 600;">{{ $employee->phone }}</p>
                </div>
            </div>
        </div>
    </a>
    @endforeach
</div>



        

<!-- Pagination -->
<div class="w-full flex justify-center items-center pt-4">
    {{ $employees->appends(['search' => request('search')])->links('vendor.pagination.tailwind') }}
</div>
    </div>
</div>


@endsection