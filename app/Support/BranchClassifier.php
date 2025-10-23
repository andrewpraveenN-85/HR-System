<?php

namespace App\Support;

use App\Models\Employee;

class BranchClassifier
{
    public static function isHeadOffice(Employee $employee): bool
    {
        $employee->loadMissing('department');

        $branch = optional($employee->department)->branch;
        $headOfficeBranch = config('saturday.head_office_branch', 'Head Office');

        if ($branch !== null && strcasecmp($branch, $headOfficeBranch) === 0) {
            return true;
        }

        $name = optional($employee->department)->name;

        return $name !== null && stripos($name, 'head') !== false;
    }

    public static function isFactory(Employee $employee): bool
    {
        $employee->loadMissing('department');

        $branch = optional($employee->department)->branch;
        $factoryBranch = config('saturday.factory_branch', 'Factory');

        if ($branch !== null && strcasecmp($branch, $factoryBranch) === 0) {
            return true;
        }

        $name = optional($employee->department)->name;

        return $name !== null && stripos($name, 'factory') !== false;
    }
}
