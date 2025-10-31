<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\SaturdayAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SaturdayRosterController extends Controller
{
    public function index(Request $request)
    {
        $headOfficeBranch = config('saturday.head_office_branch', 'Head Office');

        $workDate = $request->filled('work_date')
            ? Carbon::parse($request->query('work_date'))
            : Carbon::now()->next(Carbon::SATURDAY);

        if (!$workDate->isSaturday()) {
            $workDate = $workDate->copy()->next(Carbon::SATURDAY);
        }

        $headOfficeEmployees = $this->getHeadOfficeEmployees();
        $headOfficeEmployeeIds = $headOfficeEmployees->pluck('id')->all();

        $assignedEmployeeIds = SaturdayAssignment::whereDate('work_date', $workDate->toDateString())
            ->whereIn('employee_id', $headOfficeEmployeeIds)
            ->pluck('employee_id')
            ->all();

        $selectedMonth = $request->query('history_month');
        [$historyStart, $historyEnd] = $this->resolveHistoryRange($workDate, $selectedMonth);
        $history = $this->buildHistory($historyStart, $historyEnd);
        $availableHistoryMonths = $this->getAvailableHistoryMonths();

        [$monthStart, $monthEnd] = $this->resolveMonthWindow($workDate);
        $monthlySummary = $this->buildMonthlySummary($headOfficeEmployees, $monthStart, $monthEnd);

        // Get upcoming scheduled Saturdays
        $upcomingSaturdays = $this->getUpcomingSaturdays($headOfficeEmployeeIds, $workDate);

        return view('management.payroll.saturday-roster', [
            'workDate' => $workDate,
            'headOfficeEmployees' => $headOfficeEmployees,
            'assignedEmployeeIds' => $assignedEmployeeIds,
            'history' => $history,
            'historyStart' => $historyStart,
            'historyEnd' => $historyEnd,
            'selectedHistoryMonth' => $selectedMonth,
            'availableHistoryMonths' => $availableHistoryMonths,
            'monthlySummary' => $monthlySummary,
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'headOfficeBranch' => $headOfficeBranch,
            'upcomingSaturdays' => $upcomingSaturdays,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'work_date' => ['required', 'date'],
            'employee_ids' => ['array'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
        ]);

        $workDate = Carbon::parse($request->input('work_date'));

        if (!$workDate->isSaturday()) {
            return back()->withErrors([
                'work_date' => 'Selected date must be a Saturday.',
            ])->withInput();
        }

        $headOfficeEmployees = $this->getHeadOfficeEmployees();
        $headOfficeEmployeeIds = $headOfficeEmployees->pluck('id')->all();

        $employeeIds = collect($request->input('employee_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->all();

        $invalidIds = array_diff($employeeIds, $headOfficeEmployeeIds);
        if (!empty($invalidIds)) {
            return back()->withErrors([
                'employee_ids' => 'Selected employees must belong to the head office.',
            ])->withInput();
        }

        $headOfficeBranch = config('saturday.head_office_branch', 'Head Office');
        $assignedBy = auth()->id();

        DB::transaction(function () use ($workDate, $headOfficeEmployeeIds, $employeeIds, $assignedBy, $headOfficeBranch) {
            SaturdayAssignment::whereDate('work_date', $workDate->toDateString())
                ->whereIn('employee_id', $headOfficeEmployeeIds)
                ->delete();

            if (empty($employeeIds)) {
                return;
            }

            $employees = Employee::with('department')
                ->whereIn('id', $employeeIds)
                ->get();

            foreach ($employees as $employee) {
                SaturdayAssignment::updateOrCreate(
                    [
                        'work_date' => $workDate->toDateString(),
                        'employee_id' => $employee->id,
                    ],
                    [
                        'department_id' => $employee->department_id,
                        'branch' => optional($employee->department)->branch ?? $headOfficeBranch,
                        'assigned_by' => $assignedBy,
                    ]
                );
            }
        });

        return redirect()
            ->route('payroll.saturday-roster.index', ['work_date' => $workDate->toDateString()])
            ->with('success', 'Saturday roster updated successfully.');
    }

    public function history(Request $request)
    {
        $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $workDate = $request->filled('start')
            ? Carbon::parse($request->input('start'))
            : Carbon::now();

        $selectedMonth = $request->input('month');

        [$historyStart, $historyEnd] = $this->resolveHistoryRange($workDate, $selectedMonth, $request->input('end'));
        $history = $this->buildHistory($historyStart, $historyEnd);

        return response()->json([
            'data' => $history,
            'start' => $historyStart->toDateString(),
            'end' => $historyEnd->toDateString(),
        ]);
    }

    private function getHeadOfficeEmployees(): Collection
    {
        $headOfficeBranch = config('saturday.head_office_branch', 'Head Office');

        return Employee::with('department')
            ->whereHas('department', function ($query) use ($headOfficeBranch) {
                $query->where('branch', $headOfficeBranch)
                    ->orWhere('name', 'like', '%Head%');
            })
            ->orderBy('full_name')
            ->get();
    }

    private function resolveHistoryRange(Carbon $workDate, ?string $selectedMonth = null, ?string $explicitEnd = null): array
    {
        if ($selectedMonth) {
            try {
                $start = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
                $end = $start->copy()->endOfMonth();
            } catch (\Exception $exception) {
                $start = $workDate->copy()->subWeeks(8);
                $end = $workDate->copy()->subDay();
            }
        } else {
            $start = $workDate->copy()->subWeeks(8);
            $end = $workDate->copy()->subDay();
        }

        if ($explicitEnd) {
            $end = Carbon::parse($explicitEnd);
        }

        $now = Carbon::now();
        if ($end->greaterThan($now)) {
            $end = $now;
        }

        if ($start->greaterThan($end)) {
            $start = $end->copy()->subWeeks(8);
        }

        if (!$start->isSaturday()) {
            $start = $start->copy()->next(Carbon::SATURDAY);
        }

        return [$start, $end];
    }

    private function resolveMonthWindow(Carbon $workDate): array
    {
        $monthStart = $workDate->copy()->startOfMonth();
        $monthEnd = $workDate->copy()->endOfMonth();

        return [$monthStart, $monthEnd];
    }

    private function buildHistory(Carbon $startDate, Carbon $endDate): array
    {
        if ($startDate->greaterThan($endDate)) {
            return [];
        }

        $dates = [];
        $cursor = $startDate->copy();

        if (!$cursor->isSaturday()) {
            $cursor = $cursor->next(Carbon::SATURDAY);
        }

        while ($cursor->lte($endDate)) {
            $dates[] = $cursor->toDateString();
            $cursor->addWeek();
        }

        if (empty($dates)) {
            return [];
        }

        $assignments = SaturdayAssignment::with(['employee:id,full_name,employee_id,department_id'])
            ->whereIn('work_date', $dates)
            ->orderBy('work_date')
            ->get()
            ->groupBy(fn ($assignment) => Carbon::parse($assignment->work_date)->toDateString());

        $attendance = Attendance::with(['employee:id,full_name,employee_id,department_id', 'employee.department:id,name,branch'])
            ->whereIn('date', $dates)
            ->whereHas('employee', function ($query) {
                $headOfficeBranch = config('saturday.head_office_branch', 'Head Office');
                $query->whereHas('department', function ($deptQuery) use ($headOfficeBranch) {
                    $deptQuery->where('branch', $headOfficeBranch)
                        ->orWhere('name', 'like', '%Head%');
                });
            })
            ->get()
            ->groupBy(fn ($record) => Carbon::parse($record->date)->toDateString());

        $allDates = collect($dates)
            ->merge($assignments->keys())
            ->merge($attendance->keys())
            ->unique()
            ->sort()
            ->values();

        $history = [];

        foreach ($allDates as $date) {
            $assignmentGroup = $assignments->get($date, collect());
            $attendanceGroup = $attendance->get($date, collect());
            $attendanceByEmployee = $attendanceGroup->keyBy('employee_id');

            $assigned = $assignmentGroup->map(function ($assignment) use ($attendanceByEmployee) {
                $record = $attendanceByEmployee->get($assignment->employee_id);
                $workedSeconds = $record ? $this->calculateWorkedSeconds($record->clock_in_time, $record->clock_out_time) : 0;

                return [
                    'employee' => $assignment->employee,
                    'status' => $record ? 'worked' : 'absent',
                    'worked_seconds' => $workedSeconds,
                ];
            })->values();

            $extras = $attendanceGroup->filter(function ($record) use ($assignmentGroup) {
                return !$assignmentGroup->contains('employee_id', $record->employee_id);
            })->map(function ($record) {
                return [
                    'employee' => $record->employee,
                    'status' => 'extra',
                    'worked_seconds' => $this->calculateWorkedSeconds($record->clock_in_time, $record->clock_out_time),
                ];
            })->values();

            $history[] = [
                'date' => $date,
                'assigned' => $assigned,
                'extras' => $extras,
            ];
        }

        return $history;
    }

    private function buildMonthlySummary(Collection $headOfficeEmployees, Carbon $monthStart, Carbon $monthEnd): array
    {
        $employeeIds = $headOfficeEmployees->pluck('id')->all();
        if (empty($employeeIds)) {
            return [];
        }

        $assignmentGroups = SaturdayAssignment::whereIn('employee_id', $employeeIds)
            ->whereBetween('work_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->groupBy('employee_id')
            ->map(function ($group) {
                return $group->map(fn ($assignment) => Carbon::parse($assignment->work_date)->toDateString())->all();
            });

        $attendanceGroups = Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->filter(fn ($record) => Carbon::parse($record->date)->isSaturday())
            ->groupBy('employee_id')
            ->map(function ($group) {
                return $group->map(fn ($record) => Carbon::parse($record->date)->toDateString())->all();
            });

        $summary = [];

        foreach ($headOfficeEmployees as $employee) {
            $scheduledDates = $assignmentGroups->get($employee->id, []);
            $attendanceDates = $attendanceGroups->get($employee->id, []);

            $workedScheduled = count(array_intersect($scheduledDates, $attendanceDates));
            $workedExtra = count(array_diff($attendanceDates, $scheduledDates));
            $scheduledCount = count($scheduledDates);
            $workedCount = count($attendanceDates);

            $summary[] = [
                'employee' => $employee,
                'scheduled_count' => $scheduledCount,
                'worked_scheduled' => $workedScheduled,
                'worked_extra' => $workedExtra,
                'total_worked' => $workedCount,
                'needs_attention' => $workedCount < 2,
            ];
        }

        return $summary;
    }

    private function getAvailableHistoryMonths(): array
    {
        return SaturdayAssignment::selectRaw('DATE_FORMAT(work_date, "%Y-%m") as month')
            ->distinct()
            ->orderByDesc('month')
            ->pluck('month')
            ->all();
    }

    private function calculateWorkedSeconds(?string $clockIn, ?string $clockOut): int
    {
        if (!$clockIn || !$clockOut) {
            return 0;
        }

        $start = Carbon::parse($clockIn);
        $end = Carbon::parse($clockOut);

        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        return $end->diffInSeconds($start);
    }

    private function getUpcomingSaturdays(array $headOfficeEmployeeIds, Carbon $fromDate): array
    {
        $today = Carbon::now()->startOfDay();
        $endDate = $today->copy()->addWeeks(8);

        $assignments = SaturdayAssignment::with(['employee:id,full_name,employee_id,department_id', 'employee.department:id,name'])
            ->whereIn('employee_id', $headOfficeEmployeeIds)
            ->whereDate('work_date', '>=', $today->toDateString())
            ->whereDate('work_date', '<=', $endDate->toDateString())
            ->orderBy('work_date')
            ->get()
            ->groupBy(fn ($assignment) => Carbon::parse($assignment->work_date)->toDateString());

        $upcoming = [];
        $cursor = $today->copy();

        if (!$cursor->isSaturday()) {
            $cursor = $cursor->next(Carbon::SATURDAY);
        }

        while ($cursor->lte($endDate)) {
            $dateKey = $cursor->toDateString();
            $assignmentGroup = $assignments->get($dateKey, collect());

            $upcoming[] = [
                'date' => $cursor->copy(),
                'assigned_employees' => $assignmentGroup->map(function ($assignment) {
                    return [
                        'id' => $assignment->employee->id,
                        'full_name' => $assignment->employee->full_name ?? '—',
                        'employee_id' => $assignment->employee->employee_id,
                        'department' => optional($assignment->employee->department)->name ?? 'No department',
                    ];
                })->values()->all(),
            ];

            $cursor->addWeek();
        }

        return $upcoming;
    }
}
