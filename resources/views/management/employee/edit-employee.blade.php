@extends('layouts.dashboard-layout')

@section('title', 'Edit Employee')

@section('content')
<div class="flex flex-col items-start justify-start w-full px-16">
    @if (session('success'))
        <div class="w-full bg-green-100 text-green-800 px-4 py-3 rounded-lg mt-6">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="w-full bg-red-100 text-red-800 px-4 py-3 rounded-lg mt-6">
            <p class="font-semibold">Please fix the following issues:</p>
            <ul class="list-disc list-inside text-sm mt-2 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $dateOfBirth = $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('Y-m-d') : null;
        $probationStart = $employee->probation_start_date ? \Carbon\Carbon::parse($employee->probation_start_date)->format('Y-m-d') : null;
        $employmentStart = $employee->employment_start_date ? \Carbon\Carbon::parse($employee->employment_start_date)->format('Y-m-d') : null;
        $employmentEnd = $employee->employment_end_date ? \Carbon\Carbon::parse($employee->employment_end_date)->format('Y-m-d') : null;
    @endphp

    <nav class="flex px-5 py-3 mt-6 mb-6" aria-label="Breadcrumb">
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
            <li>
                <div class="flex items-center">
                    <p class="text-[#00000080] text-3xl"><i class="ri-arrow-right-wide-line"></i></p>
                    <span class="ml-1 font-semibold text-[#184E77] text-3xl">Edit Employee</span>
                </div>
            </li>
        </ol>
    </nav>

    <form method="POST" action="{{ route('employee.update', $employee->id) }}" enctype="multipart/form-data" class="w-full space-y-10 pb-16">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-[#D9D9D980] rounded-3xl p-8 space-y-6 col-span-1">
                <h2 class="text-2xl font-bold text-[#1C1B1F]">Profile</h2>
                <div class="flex flex-col items-center space-y-6">
                    <div class="relative group">
                        <div class="w-40 h-40 rounded-full overflow-hidden border-4 border-gray-300 flex items-center justify-center bg-white shadow">
                            @php
                                $imageUrl = $employee->image ? asset('storage/' . $employee->image) : 'https://via.placeholder.com/200/cccccc/666666?text=Employee';
                            @endphp
                            <img id="profileImage" src="{{ $imageUrl }}" alt="Employee Image" class="w-full h-full object-cover">
                        </div>
                        <label for="image" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-semibold rounded-full cursor-pointer">
                            Change Photo
                        </label>
                        <input type="file" id="image" name="image" accept="image/*" class="hidden" onchange="previewImage(event)">
                    </div>
                    <p class="text-center text-[#00000099] text-sm">Upload a square image (JPG, PNG, max 4MB) to update the profile picture.</p>
                </div>
            </div>

            <div class="bg-[#D9D9D980] rounded-3xl p-8 space-y-6 col-span-2">
                <h2 class="text-2xl font-bold text-[#1C1B1F]">Personal Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-black">
                    <div class="flex flex-col space-y-2">
                        <label for="full_name" class="text-xl font-semibold">Full Name</label>
                        <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $employee->full_name) }}" required class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="first_name" class="text-xl font-semibold">First Name</label>
                        <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $employee->first_name) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="last_name" class="text-xl font-semibold">Last Name</label>
                        <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $employee->last_name) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="email" class="text-xl font-semibold">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $employee->email) }}" required class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="phone" class="text-xl font-semibold">Phone</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $employee->phone) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="date_of_birth" class="text-xl font-semibold">Date of Birth</label>
                        <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', $dateOfBirth) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="nic" class="text-xl font-semibold">NIC</label>
                        <input id="nic" name="nic" type="text" value="{{ old('nic', $employee->nic) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="gender" class="text-xl font-semibold">Gender</label>
                        <select id="gender" name="gender" class="input-field">
                            <option value="" {{ old('gender', $employee->gender) === null ? 'selected' : '' }}>Select gender</option>
                            <option value="Male" {{ old('gender', $employee->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $employee->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Non-binary" {{ old('gender', $employee->gender) === 'Non-binary' ? 'selected' : '' }}>Non-binary</option>
                            <option value="Other" {{ old('gender', $employee->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col space-y-2 text-black">
                    <label for="address" class="text-xl font-semibold">Address</label>
                    <textarea id="address" name="address" rows="3" class="input-field">{{ old('address', $employee->address) }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-[#D9D9D980] rounded-3xl p-8 space-y-6">
            <h2 class="text-2xl font-bold text-[#1C1B1F]">Employment Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-black">
                <div class="flex flex-col space-y-2">
                    <label for="employee_id" class="text-xl font-semibold">Employee ID</label>
                    <input id="employee_id" name="employee_id" type="text" value="{{ old('employee_id', $employee->employee_id) }}" class="input-field">
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="title" class="text-xl font-semibold">Job Title</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $employee->title) }}" class="input-field">
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="employment_type" class="text-xl font-semibold">Employment Type</label>
                    <input id="employment_type" name="employment_type" type="text" value="{{ old('employment_type', $employee->employment_type) }}" class="input-field">
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="manager_id" class="text-xl font-semibold">Manager</label>
                    <select id="manager_id" name="manager_id" class="input-field">
                        <option value="">No manager</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" {{ (string) old('manager_id', $employee->manager_id) === (string) $manager->id ? 'selected' : '' }}>
                                {{ $manager->full_name }} ({{ $manager->employee_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col space-y-2 md:col-span-2">
                    <label for="department_select" class="text-xl font-semibold">Department</label>
                    <select id="department_select" class="input-field">
                        <option value="">Select a department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}"
                                data-name="{{ $department->name }}"
                                data-branch="{{ $department->branch }}"
                                {{ (string) old('department_id', $employee->department_id) === (string) $department->id ? 'selected' : '' }}>
                                {{ $department->name }} — {{ $department->branch }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-sm text-[#00000099] mt-1">Selecting a department will automatically update its name and branch for validation.</p>
                    <input type="hidden" name="department_id" id="department_id" value="{{ old('department_id', $employee->department_id) }}">
                    <input type="hidden" name="name" id="department_name" value="{{ old('name', optional($employee->department)->name) }}">
                    <input type="hidden" name="branch" id="department_branch" value="{{ old('branch', optional($employee->department)->branch) }}">
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="probation_start_date" class="text-xl font-semibold">Probation Start Date</label>
                    <input id="probation_start_date" name="probation_start_date" type="date" value="{{ old('probation_start_date', $probationStart) }}" class="input-field">
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="probation_period" class="text-xl font-semibold">Probation Period (days)</label>
                    <input id="probation_period" name="probation_period" type="number" min="1" value="{{ old('probation_period', $employee->probation_period) }}" class="input-field">
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="employment_start_date" class="text-xl font-semibold">Employment Start Date</label>
                    <input id="employment_start_date" name="employment_start_date" type="date" value="{{ old('employment_start_date', $employmentStart) }}" class="input-field">
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="employment_end_date" class="text-xl font-semibold">Employment End Date</label>
                    <input id="employment_end_date" name="employment_end_date" type="date" value="{{ old('employment_end_date', $employmentEnd) }}" class="input-field">
                </div>
                <div class="flex flex-col space-y-2 md:col-span-2">
                    <label for="status" class="text-xl font-semibold">Employment Status</label>
                    <input id="status" name="status" type="text" value="{{ old('status', $employee->status) }}" class="input-field">
                </div>
                <div class="flex flex-col space-y-2 md:col-span-2">
                    <label for="description" class="text-xl font-semibold">Bio / Description</label>
                    <textarea id="description" name="description" rows="3" class="input-field">{{ old('description', $employee->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-[#D9D9D980] rounded-3xl p-8 space-y-6">
                <h2 class="text-2xl font-bold text-[#1C1B1F]">Bank Details</h2>
                <div class="space-y-4 text-black">
                    <div class="flex flex-col space-y-2">
                        <label for="account_holder_name" class="text-xl font-semibold">Account Holder Name</label>
                        <input id="account_holder_name" name="account_holder_name" type="text" value="{{ old('account_holder_name', optional($employee->bankDetails)->account_holder_name ?? $employee->account_holder_name) }}" required class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="bank_name" class="text-xl font-semibold">Bank Name</label>
                        <input id="bank_name" name="bank_name" type="text" value="{{ old('bank_name', optional($employee->bankDetails)->bank_name ?? $employee->bank_name) }}" required class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="bank_code" class="text-xl font-semibold">Bank Code</label>
                        <input id="bank_code" name="bank_code" type="text" value="{{ old('bank_code', optional($employee->bankDetails)->bank_code) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="account_number" class="text-xl font-semibold">Account Number</label>
                        <input id="account_number" name="account_number" type="text" value="{{ old('account_number', optional($employee->bankDetails)->account_number ?? $employee->account_no) }}" required class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="branch_name" class="text-xl font-semibold">Branch Name</label>
                        <input id="branch_name" name="branch_name" type="text" value="{{ old('branch_name', optional($employee->bankDetails)->branch ?? $employee->branch_name) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="branch_code" class="text-xl font-semibold">Branch Code</label>
                        <input id="branch_code" name="branch_code" type="text" value="{{ old('branch_code', optional($employee->bankDetails)->branch_code) }}" class="input-field">
                    </div>
                </div>
            </div>

            <div class="bg-[#D9D9D980] rounded-3xl p-8 space-y-6">
                <h2 class="text-2xl font-bold text-[#1C1B1F]">Salary Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-black">
                    <div class="flex flex-col space-y-2">
                        <label for="epf_no" class="text-xl font-semibold">EPF Number</label>
                        <input id="epf_no" name="epf_no" type="text" value="{{ old('epf_no', $employee->epf_no) }}" required class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="basic" class="text-xl font-semibold">Basic Salary</label>
                        <input id="basic" name="basic" type="number" step="0.01" min="0" value="{{ old('basic', $employee->basic) }}" required class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="budget_allowance" class="text-xl font-semibold">Budget Allowance</label>
                        <input id="budget_allowance" name="budget_allowance" type="number" step="0.01" min="0" value="{{ old('budget_allowance', $employee->budget_allowance) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="transport_allowance" class="text-xl font-semibold">Transport Allowance</label>
                        <input id="transport_allowance" name="transport_allowance" type="number" step="0.01" min="0" value="{{ old('transport_allowance', $employee->transport_allowance) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="attendance_allowance" class="text-xl font-semibold">Attendance Allowance</label>
                        <input id="attendance_allowance" name="attendance_allowance" type="number" step="0.01" min="0" value="{{ old('attendance_allowance', $employee->attendance_allowance) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="phone_allowance" class="text-xl font-semibold">Phone Allowance</label>
                        <input id="phone_allowance" name="phone_allowance" type="number" step="0.01" min="0" value="{{ old('phone_allowance', $employee->phone_allowance) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="car_allowance" class="text-xl font-semibold">Car Allowance</label>
                        <input id="car_allowance" name="car_allowance" type="number" step="0.01" min="0" value="{{ old('car_allowance', $employee->car_allowance) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="production_bonus" class="text-xl font-semibold">Production Bonus</label>
                        <input id="production_bonus" name="production_bonus" type="number" step="0.01" min="0" value="{{ old('production_bonus', $employee->production_bonus) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="stamp_duty" class="text-xl font-semibold">Stamp Duty</label>
                        <input id="stamp_duty" name="stamp_duty" type="number" step="0.01" min="0" value="{{ old('stamp_duty', $employee->stamp_duty ?? 25.00) }}" class="input-field">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="loan_monthly_instalment" class="text-xl font-semibold">Loan Monthly Instalment</label>
                        <input id="loan_monthly_instalment" name="loan_monthly_instalment" type="number" step="0.01" min="0" value="{{ old('loan_monthly_instalment', $employee->loan_monthly_instalment ?? 0.00) }}" class="input-field">
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full flex justify-end pt-4">
            <button type="submit" class="px-8 py-3 bg-[#52B69A] text-white font-bold text-xl rounded-lg hover:bg-[#40916C] transition duration-300 shadow">
                Update Employee
            </button>
        </div>
    </form>
</div>

@push('styles')
    <style>
        .input-field {
            width: 100%;
            padding: 0.75rem;
            font-size: 1.125rem;
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .input-field:focus {
            border-color: #52B69A;
            box-shadow: 0 0 0 3px rgba(82, 182, 154, 0.25);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const departmentSelect = document.getElementById('department_select');
            const departmentIdInput = document.getElementById('department_id');
            const departmentNameInput = document.getElementById('department_name');
            const departmentBranchInput = document.getElementById('department_branch');

            const syncDepartmentFields = (option) => {
                if (!option || !option.value) {
                    departmentIdInput.value = '';
                    departmentNameInput.value = '';
                    departmentBranchInput.value = '';
                    return;
                }

                departmentIdInput.value = option.value;
                departmentNameInput.value = option.dataset.name || '';
                departmentBranchInput.value = option.dataset.branch || '';
            };

            if (departmentSelect) {
                syncDepartmentFields(departmentSelect.selectedOptions[0]);
                departmentSelect.addEventListener('change', (event) => {
                    syncDepartmentFields(event.target.selectedOptions[0]);
                });
            }

        });

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                const output = document.getElementById('profileImage');
                output.src = reader.result;
            };
            if (event.target.files && event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
@endpush
@endsection
