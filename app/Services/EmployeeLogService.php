<?php
namespace App\Services;

use App\Models\EmployeeLog;

class EmployeeLogService
{
    public function getAll(array $filters = [])
    {
        return EmployeeLog::with('employee')
            ->when($filters['employee_id'] ?? null, function ($query, $employeeId) {
                $query->where('employee_id', $employeeId);
            })
            ->orderByDesc('created_at')
            ->paginate(15);
    }
}
