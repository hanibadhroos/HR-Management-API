<?php
namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Events\SalaryChanged;
use App\Exports\EmployeeExport;
use App\Imports\EmployeeImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $this->validateFounder($data);

            $data['is_founder'] = $data['is_founder'] ?? false;

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

            // Validate if the emp is founder
            $this->validateFounder($data, $employee);

            $originalSalary = $employee->salary;
            $salaryChanged = false;

            // Salary Logic Before Update.
            if (array_key_exists('salary', $data)) {

                if ($employee->is_founder) {
                    throw ValidationException::withMessages([
                        'salary' => 'Founder salary cannot be changed.'
                    ]);
                }

                if ($data['salary'] != $originalSalary) {
                    $salaryChanged = true;
                    $data['salary_changed_at'] = now();
                }
            }

            $employee->update($data);

            EmployeeLog::create([
                'employee_id' => $employee->id,
                'action' => 'updated',
                'description' => "Employee {$employee->name} updated."
            ]);

            // Dispatch Event Only If Salary Changed.
            if ($salaryChanged) {

                DB::afterCommit(function() use ($employee, $originalSalary){
                    event(new SalaryChanged($employee, $originalSalary));
                });
                // event(new SalaryChanged($employee, $originalSalary));

            }

            return $employee->fresh(['manager', 'position']);
        });
    }


    public function validateFounder(array $data, ?Employee $employee = null)
    {
        $isFounder = $data['is_founder'] ?? $employee?->is_founder ?? false;

        if ($isFounder) {

            $query = Employee::where('is_founder', true);

            if ($employee) {
                $query->where('id', '!=', $employee->id);
            }

            if ($query->exists()) {
                throw ValidationException::withMessages([
                    'is_founder' => 'There can only be one founder in the company.'
                ]);
            }

            return;
        }

        $managerId = $data['manager_id'] ?? $employee?->manager_id;

        if (empty($managerId)) {
            throw ValidationException::withMessages([
                'manager_id' => 'Each employee, except the founder, must have a manager.'
            ]);
        }

        if ($employee && $managerId == $employee->id) {
            throw ValidationException::withMessages([
                'manager_id' => 'Employee cannot be their own manager.'
            ]);
        }
    }



    public function getAll(array $filters)
    {
        return Employee::with(['manager', 'position'])
            ->filter($filters)
            ->paginate(10);
    }



    ////// Heirarchy by name + Salary.
    public function getHierarchyWithSalaries(Employee $employee)
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
    public function getHierarchy(Employee $employee)
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
    public function delete(Employee $employee)
    {
        DB::transaction(function () use ($employee) {


            if ($employee->is_founder) {
                throw ValidationException::withMessages([
                    'employee' => 'Founder cannot be deleted.'
                ]);
            }

            if (Employee::where('manager_id', $employee->id)->exists()) {
                throw ValidationException::withMessages([
                    'employee' => 'Cannot delete manager who has subordinates.'
                ]);
            }

            //////======================
            $employeeId = $employee->id;
            $employeeName = $employee->name;

            EmployeeLog::create([
                'employee_id' => $employee->id,
                'action' => 'deleted',
                'description' => "Employee {$employee->name} deleted."
            ]);
    
            ///// منع حذف الموسس
            if ($employee->is_founder) {
                throw ValidationException::withMessages([
                    'employee' => 'Founder cannot be deleted.'
                ]);
            }

            //// منع حذف مدير نحته موظفين
            if (Employee::where('manager_id', $employee->id)->exists()) {
                throw ValidationException::withMessages([
                    'employee' => 'Cannot delete manager who has subordinates.'
                ]);
            }
                        
            $employee->delete();

            ///// ويمكننا استرجاعه باستخدام الامر Employee::withTrashed()->find($id);
        });
    }
    
    

    ///// Export emp to CSV
    public function exportToCsv()
    {
        $employees = Employee::with(['manager', 'position'])->get();

        return $employees;
    }


    //////Import emp of CSV.
    public function importFromCsv($file)
    {
        Excel::import(new EmployeeImport, $file);
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
