@extends('layouts.dashboard-layout')

@php use Carbon\Carbon; @endphp

@section('title', 'Head Office Saturday Roster')

@section('content')
    @php
        $formatDuration = function (int $seconds): string {
            if ($seconds <= 0) {
                return '00:00';
            }

            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);

            return sprintf('%02d:%02d', $hours, $minutes);
        };
    @endphp

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col space-y-10">
        <div class="rounded-3xl bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-black">Head Office Saturday Roster</h1>
                    <p class="mt-2 text-base text-gray-600">Plan which team works on {{ $workDate->format('l, d M Y') }}. Head Office shift runs 08:30 – 16:30. First 2 Saturdays per month count as regular work days (hours after 16:30 are OT). Additional Saturdays (3rd, 4th, etc.) are full OT days. Factory teams automatically appear in payroll and always cover Saturdays 08:30 – 13:00.</p>
                </div>
                <div class="flex items-center space-x-2 rounded-2xl bg-[#184E77] px-4 py-2 text-white">
                    <span class="text-sm uppercase tracking-wide">Head Office</span>
                </div>
            </div>

            <form method="POST" action="{{ route('payroll.saturday-roster.store') }}" class="mt-6">
                @csrf
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="flex flex-col text-sm font-semibold text-gray-700">
                        Upcoming Saturday
                        <input
                            type="date"
                            name="work_date"
                            value="{{ old('work_date', $workDate->toDateString()) }}"
                            class="mt-2 rounded-xl border border-gray-300 px-4 py-2 focus:border-[#184E77] focus:outline-none"
                            id="work-date-input"
                        >
                    </label>
                    <div class="md:col-span-2">
                        <label class="flex flex-col text-sm font-semibold text-gray-700">
                            Quick Filter
                            <input
                                type="text"
                                id="employee-search"
                                placeholder="Search by employee name or ID..."
                                class="mt-2 rounded-xl border border-gray-300 px-4 py-2 focus:border-[#184E77] focus:outline-none"
                            >
                        </label>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-gray-200">
                    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
                        <div>
                            <p class="text-lg font-semibold text-black">Select the team</p>
                            <p class="text-sm text-gray-500">Employees can be assigned to multiple Saturdays. The first two worked Saturdays per month are regular work days; any additional Saturdays will be paid as full OT days.</p>
                        </div>
                        <div class="flex items-center space-x-3 text-sm">
                            <button type="button" id="select-all" class="rounded-xl border border-[#184E77] px-3 py-1 text-[#184E77] hover:bg-[#184E77] hover:text-white">Select all</button>
                            <button type="button" id="clear-all" class="rounded-xl border border-gray-400 px-3 py-1 text-gray-600 hover:bg-gray-100">Clear</button>
                        </div>
                    </div>

                    <div class="max-h-96 overflow-y-auto" id="employee-checkboxes">
                        @forelse($headOfficeEmployees as $employee)
                            <label class="flex items-center justify-between border-b border-gray-100 px-4 py-3 hover:bg-gray-50">
                                <div class="flex flex-col">
                                    <span class="text-base font-medium text-black">{{ $employee->full_name ?? '—' }}</span>
                                    <span class="text-sm text-gray-500">{{ $employee->employee_id }} · {{ optional($employee->department)->name ?? 'No department' }}</span>
                                </div>
                                <input
                                    type="checkbox"
                                    name="employee_ids[]"
                                    value="{{ $employee->id }}"
                                    class="h-5 w-5 rounded border-gray-300 text-[#184E77] focus:ring-[#184E77]"
                                    {{ in_array($employee->id, old('employee_ids', $assignedEmployeeIds)) ? 'checked' : '' }}
                                >
                            </label>
                        @empty
                            <p class="px-4 py-3 text-sm text-gray-500">No head office employees found. Add departments with the "{{ $headOfficeBranch }}" branch to populate this list.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-[#184E77] to-[#52B69A] px-6 py-3 text-lg font-semibold text-white shadow-sm hover:from-[#1B5A8A] hover:to-[#5CC2A6]">Save roster</button>
                </div>
            </form>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-black">Monthly coverage snapshot</h2>
                    <p class="mt-1 text-sm text-gray-500">Tracking {{ $monthStart->format('F Y') }}. Regular Days = first 2 worked Saturdays (normal pay). OT Days = 3rd, 4th+ worked Saturdays (full OT pay). Employees flagged below have fewer than two Saturday attendances.</p>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full table-auto text-left">
                    <thead class="bg-gray-100 text-sm uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Employee</th>
                            <th class="px-4 py-3">Scheduled</th>
                            <th class="px-4 py-3">Worked (Scheduled)</th>
                            <th class="px-4 py-3">Worked (Extra)</th>
                            <th class="px-4 py-3">Regular Days</th>
                            <th class="px-4 py-3">OT Days</th>
                            <th class="px-4 py-3">Total Worked</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($monthlySummary as $row)
                            <tr class="{{ $row['needs_attention'] ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-3 font-medium text-black">
                                    <div class="flex flex-col">
                                        <span>{{ $row['employee']->full_name ?? '—' }}</span>
                                        <span class="text-xs text-gray-500">{{ $row['employee']->employee_id }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ $row['scheduled_count'] }}</td>
                                <td class="px-4 py-3">{{ $row['worked_scheduled'] }}</td>
                                <td class="px-4 py-3">{{ $row['worked_extra'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">
                                        {{ $row['regular_saturdays'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-700">
                                        {{ $row['ot_saturdays'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ $row['total_worked'] }}</td>
                                <td class="px-4 py-3">
                                    @if($row['total_worked'] >= 2)
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Met target</span>
                                    @else
                                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Needs 2 Saturdays</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-4 text-center text-sm text-gray-500">No head office employees available for this month.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-black">Upcoming scheduled Saturdays</h2>
                    <p class="mt-1 text-sm text-gray-500">Next 8 weeks of scheduled Saturday teams. Unassigned dates will show no team members.</p>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                @forelse($upcomingSaturdays as $upcoming)
                    <div class="rounded-2xl border border-gray-200 p-4 {{ Carbon::parse($upcoming['date'])->isSameDay($workDate) ? 'border-[#184E77] bg-blue-50' : '' }}">
                        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                            <div>
                                <p class="text-xl font-semibold text-black">
                                    {{ $upcoming['date']->format('l, d M Y') }}
                                    @if(Carbon::parse($upcoming['date'])->isSameDay($workDate))
                                        <span class="ml-2 rounded-full bg-[#184E77] px-3 py-1 text-xs font-semibold text-white">Currently Selected</span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-500">{{ $upcoming['date']->diffForHumans(now(), ['parts' => 2, 'short' => true]) }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 text-xs uppercase tracking-wide">
                                <span class="rounded-full bg-blue-100 px-3 py-1 text-blue-700">Team size: {{ count($upcoming['assigned_employees']) }}</span>
                                @if(Carbon::parse($upcoming['date'])->isFuture())
                                    <a href="{{ route('payroll.saturday-roster.index', ['work_date' => $upcoming['date']->toDateString()]) }}" 
                                       class="rounded-full bg-[#184E77] px-3 py-1 text-white hover:bg-[#1B5A8A]">Edit</a>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4">
                            @if(count($upcoming['assigned_employees']) > 0)
                                <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-3">
                                    @foreach($upcoming['assigned_employees'] as $emp)
                                        <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 px-3 py-2">
                                            <div>
                                                <p class="text-sm font-medium text-black">{{ $emp['full_name'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $emp['employee_id'] }} · {{ $emp['department'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-center">
                                    <p class="text-sm text-gray-500">No team assigned yet</p>
                                    @if(Carbon::parse($upcoming['date'])->isFuture())
                                        <a href="{{ route('payroll.saturday-roster.index', ['work_date' => $upcoming['date']->toDateString()]) }}" 
                                           class="mt-2 inline-block text-sm font-semibold text-[#184E77] hover:underline">Assign team →</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500">
                        No upcoming Saturdays found.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm">
            <div class="flex flex-col space-y-4 md:flex-row md:items-end md:justify-between md:space-y-0">
                <div>
                    <h2 class="text-2xl font-bold text-black">Saturday history</h2>
                    <p class="mt-1 text-sm text-gray-500">Showing records between {{ $historyStart->format('d M Y') }} and {{ $historyEnd->format('d M Y') }}.</p>
                </div>
                <form method="GET" action="{{ route('payroll.saturday-roster.index') }}" class="flex flex-col items-start space-y-2 md:flex-row md:items-center md:space-x-3 md:space-y-0">
                    <input type="hidden" name="work_date" value="{{ $workDate->toDateString() }}">
                    <label class="text-sm font-semibold text-gray-700">
                        Filter by month
                        <select name="history_month" class="mt-1 w-full rounded-xl border border-gray-300 px-4 py-2 focus:border-[#184E77] focus:outline-none md:w-auto">
                            <option value="">Last 8 Saturdays</option>
                            @foreach($availableHistoryMonths as $month)
                                <option value="{{ $month }}" {{ $selectedHistoryMonth === $month ? 'selected' : '' }}>
                                    {{ Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="rounded-xl border border-[#184E77] px-4 py-2 text-sm font-semibold text-[#184E77] hover:bg-[#184E77] hover:text-white">Apply</button>
                </form>
            </div>

            <div class="mt-6 space-y-4">
                @forelse($history as $entry)
                    <div class="rounded-2xl border border-gray-200 p-4">
                        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                            <div>
                                <p class="text-xl font-semibold text-black">{{ Carbon::parse($entry['date'])->format('l, d M Y') }}</p>
                                <p class="text-sm text-gray-500">Rostered on {{ Carbon::parse($entry['date'])->diffForHumans(now(), ['parts' => 2, 'short' => true]) }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 text-xs uppercase tracking-wide">
                                <span class="rounded-full bg-blue-100 px-3 py-1 text-blue-700">Scheduled: {{ $entry['assigned']->count() }}</span>
                                <span class="rounded-full bg-green-100 px-3 py-1 text-green-700">Worked: {{ $entry['assigned']->where('status', 'worked')->count() }}</span>
                                <span class="rounded-full bg-red-100 px-3 py-1 text-red-700">Absent: {{ $entry['assigned']->where('status', 'absent')->count() }}</span>
                                <span class="rounded-full bg-yellow-100 px-3 py-1 text-yellow-700">Extra OT: {{ $entry['extras']->count() }}</span>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-600">Scheduled team</h3>
                                <div class="mt-2 space-y-2">
                                    @forelse($entry['assigned'] as $item)
                                        <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 px-3 py-2">
                                            <div>
                                                <p class="text-sm font-medium text-black">{{ $item['employee']->full_name ?? '—' }}</p>
                                                <p class="text-xs text-gray-500">{{ $item['employee']->employee_id }}</p>
                                            </div>
                                            <div class="text-right text-xs">
                                                <p class="font-semibold {{ $item['status'] === 'worked' ? 'text-green-700' : 'text-red-600' }}">
                                                    {{ $item['status'] === 'worked' ? 'Worked' : 'Absent' }}
                                                </p>
                                                @if($item['status'] === 'worked')
                                                    <p class="text-gray-500">{{ $formatDuration($item['worked_seconds']) }} hrs</p>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="rounded-xl border border-dashed border-gray-200 px-3 py-2 text-sm text-gray-500">No assignments recorded.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-600">Extras (unscheduled OT)</h3>
                                <div class="mt-2 space-y-2">
                                    @forelse($entry['extras'] as $item)
                                        <div class="flex items-center justify-between rounded-xl border border-yellow-100 bg-yellow-50 px-3 py-2">
                                            <div>
                                                <p class="text-sm font-medium text-black">{{ $item['employee']->full_name ?? '—' }}</p>
                                                <p class="text-xs text-gray-500">{{ $item['employee']->employee_id }}</p>
                                            </div>
                                            <div class="text-right text-xs text-yellow-700">
                                                <p class="font-semibold">Extra OT</p>
                                                <p>{{ $formatDuration($item['worked_seconds']) }} hrs</p>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="rounded-xl border border-dashed border-gray-200 px-3 py-2 text-sm text-gray-500">No extra attendance recorded.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500">
                        No Saturday history available for the selected window.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const workDateInput = document.getElementById('work-date-input');
            const searchInput = document.getElementById('employee-search');
            const checkboxContainer = document.getElementById('employee-checkboxes');
            const selectAllBtn = document.getElementById('select-all');
            const clearAllBtn = document.getElementById('clear-all');

            if (workDateInput) {
                workDateInput.addEventListener('change', () => {
                    const date = new Date(workDateInput.value);
                    if (date.getUTCDay() !== 6) {
                        alert('Please pick a Saturday. The closest Saturday will be selected for you.');
                        const diff = (6 - date.getUTCDay() + 7) % 7;
                        date.setUTCDate(date.getUTCDate() + diff);
                        workDateInput.value = date.toISOString().split('T')[0];
                    }
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    const term = searchInput.value.trim().toLowerCase();
                    checkboxContainer.querySelectorAll('label').forEach(label => {
                        const text = label.textContent.toLowerCase();
                        label.style.display = text.includes(term) ? 'flex' : 'none';
                    });
                });
            }

            const toggleCheckboxes = (checked) => {
                checkboxContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                    cb.checked = checked;
                });
            };

            selectAllBtn?.addEventListener('click', () => toggleCheckboxes(true));
            clearAllBtn?.addEventListener('click', () => toggleCheckboxes(false));
        });
    </script>
@endsection
