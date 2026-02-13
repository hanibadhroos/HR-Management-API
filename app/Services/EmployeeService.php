<?php
namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Events\SalaryChanged;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class EmployeeService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $this->validateFounder($data);

            $employee = Employee::create([
                ...$data,
                'salary_changed_at' => now(),
            ]);

            // Log in database
            EmployeeLog::create([
                'employee_id' => $employee->id,
                'action' => 'created',
                'description' => "Employee {$employee->name} created."
            ]);

            // Log in file
            Log::channel('employee')->info("Employee created", [
                'employee_id' => $employee->id,
                'name' => $employee->name,
            ]);

            // Notify manager if exists
            if ($employee->manager) {
                $employee->manager->notify(
                    new \App\Notifications\EmployeeCreatedNotification($employee)
                );
            }

            return $employee->load(['manager', 'position']);
        });
    }


    public function update(Employee $employee, array $data)
    {
        return DB::transaction(function () use ($employee, $data) {

            $oldSalary = $employee->salary;

            $this->validateFounder($data);

            $employee->update($data);

            // Check salary change
            if (array_key_exists('salary', $data) && $oldSalary != $data['salary']) {

                $employee->update([
                    'salary_changed_at' => now()
                ]);

                event(new SalaryChanged($employee, $oldSalary));
            }

            // Database log
            EmployeeLog::create([
                'employee_id' => $employee->id,
                'action' => 'updated',
                'description' => "Employee {$employee->name} updated."
            ]);

            Log::channel('employee')->info('Employee updated', [
                'employee_id' => $employee->id
            ]);

            return $employee->load(['manager', 'position']);
        });
    }


    public function validateFounder(array $data): void
    {
        $isFounder = $data['is_founder'] ?? false;

        if ($isFounder) {

            if (Employee::where('is_founder', true)->exists()) {
                throw ValidationException::withMessages([
                    'is_founder' => 'There can only be one founder.'
                ]);
            }

        } else {

            if (empty($data['manager_id'])) {
                throw ValidationException::withMessages([
                    'manager_id' => 'Manager is required unless employee is founder.'
                ]);
            }
        }
    }


    public function getAll(array $filters)
    {
        return Employee::with(['manager', 'position'])
            ->filter($filters)
            ->paginate(10);
    }


    // public function getHierarchy(Employee $employee): array
    // {
    //     $hierarchy = [];

    //     $current = $employee->load('manager');

    //     while ($current) {

    //         array_unshift($hierarchy, $current->name);

    //         if (!$current->manager_id) {
    //             break;
    //         }

    //         $current = Employee::with('manager')
    //             ->find($current->manager_id);
    //     }

    //     return $hierarchy;
    // }

    // public function getHierarchyWithSalaries(Employee $employee): array
    // {
    //     $hierarchy = [];

    //     $current = $employee->load('manager');

    //     while ($current) {

    //         $hierarchy = [
    //             $current->name => $current->salary
    //         ] + $hierarchy;

    //         if (!$current->manager_id) {
    //             break;
    //         }

    //         $current = Employee::with('manager')
    //             ->find($current->manager_id);
    //     }

    //     return $hierarchy;
    // }

    ////// Heirarchy by name + Salary.
    public function getHierarchyWithSalaries(Employee $employee): array
    {
        $allEmployees = Employee::select('id', 'name', 'salary', 'manager_id')->get()->keyBy('id');

        $hierarchy = [];
        $current = $employee;

        while ($current) {

            // array_unshift($hierarchy, $current->name);
            $hierarchy = [$current->name => $current->salary] + $hierarchy;

            if (!$current->manager_id) {
                break;
            }

            $current = $allEmployees->get($current->manager_id);
        }

        return $hierarchy;
    }


    //////// Hierarchy by employee name
    public function getHierarchy(Employee $employee): array
    {
        $allEmployees = Employee::select('id', 'name', 'salary', 'manager_id')->get()->keyBy('id');

        $hierarchy = [];
        $current = $employee;

        while ($current) {

            array_unshift($hierarchy, $current->name);

            if (!$current->manager_id) {
                break;
            }

            $current = $allEmployees->get($current->manager_id);
        }

        return $hierarchy;
    }

    /////////

    public function delete(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {

            //////Prevent Founder deletion
            if ($employee->is_founder) {
                throw ValidationException::withMessages([
                    'employee' => 'Founder cannot be deleted.'
                ]);
            }

            ///// Prevent if he has an employees.
            if ($employee->subordinates()->exists()) {
                throw ValidationException::withMessages([
                    'employee' => 'Cannot delete employee with subordinates.'
                ]);
            }

            // Database log
            EmployeeLog::create([
                'employee_id' => $employee->id,
                'action' => 'deleted',
                'description' => "Employee {$employee->name} deleted."
            ]);

            // File log
            Log::channel('employee')->info('Employee deleted', [
                'employee_id' => $employee->id,
                'name' => $employee->name
            ]);

            $employee->delete();
        });
    }

    ///// Export emp to CSV
    public function exportToCsv()
    {
        $employees = Employee::with(['manager', 'position'])->get();

        return $employees;
    }


    //////Import emp of CSV.
    public function importFromCsv($file): array
    {
        $rows = array_map('str_getcsv', file($file->getRealPath()));

        ///// remove header.
        $header = array_shift($rows);

        $created = 0;
        $errors = [];

        DB::transaction(function () use ($rows, &$created, &$errors) {

            foreach ($rows as $index => $row) {

                $data = [
                    'name' => $row[0] ?? null,
                    'email' => $row[1] ?? null,
                    'salary' => $row[2] ?? null,
                    'position_id' => $row[3] ?? null,
                    'manager_id' => $row[4] ?? null,
                    'is_founder' => filter_var($row[5] ?? false, FILTER_VALIDATE_BOOLEAN),
                ];

                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:employees,email',
                    'salary' => 'required|numeric|min:0',
                    'position_id' => 'required|exists:positions,id',
                    'manager_id' => 'nullable|exists:employees,id',
                    'is_founder' => 'boolean',
                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        'row' => $index + 2,
                        'errors' => $validator->errors()->toArray()
                    ];
                    continue;
                }

                ///// Founder logic
                if ($data['is_founder'] && Employee::where('is_founder', true)->exists()) {
                    $errors[] = [
                        'row' => $index + 2,
                        'errors' => ['is_founder' => ['Founder already exists']]
                    ];
                    continue;
                }

                if (!$data['is_founder'] && empty($data['manager_id'])) {
                    $errors[] = [
                        'row' => $index + 2,
                        'errors' => ['manager_id' => ['Manager required unless founder']]
                    ];
                    continue;
                }

                Employee::create([
                    ...$data,
                    'salary_changed_at' => now(),
                ]);

                $created++;
            }
        });

        Log::channel('employee')->info('Employees imported', [
            'created' => $created,
            'errors_count' => count($errors)
        ]);

        return [
            'created' => $created,
            'errors' => $errors,
        ];
    }


    ///// Emp without salry changed in X months.
    public function getEmployeesWithoutSalaryChange(int $months)
    {
        $date = Carbon::now()->subMonths($months);

        return Employee::with(['manager', 'position'])
            ->where(function ($query) use ($date) {
                $query->whereNull('salary_changed_at')
                    ->orWhere('salary_changed_at', '<=', $date);
            })
            ->paginate(10);
    }


    
}
