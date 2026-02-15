<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Validation\ValidationException;

class EmployeeImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        $validator = Validator::make($row, [
            'name' => 'required|string|max:255|unique:employees,name',
            'email' => 'required|email|unique:employees,email',
            'salary' => 'required|numeric|min:0',
            'position' => 'required|exists:positions,title',
            'manager' => 'nullable|exists:employees,name',
            'is_founder' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'error' => $validator->errors()->toArray()
            ]);
        }

        if ($row['is_founder'] && Employee::where('is_founder', true)->exists()) {
            throw ValidationException::withMessages([
                'error' => 'Founder already exists, you must add only one founder.'
            ]);
        }

        if (!$row['is_founder'] && empty($row['manager'])) {

            throw ValidationException::withMessages([
                'error' => 'Manager required unless founder.'
            ]);
        }

        $salary = isset($row['salary']) && is_numeric($row['salary']) ? $row['salary'] : 0;

        // جلب الـ position_id حسب الاسم الموجود في CSV
        $position = Position::where('title', $row['position'])->first();
        if(!$position){
            throw ValidationException::withMessages([
                'message'=>'no position with name ' . $row['position']
            ]);
        }
        // جلب manager_id حسب الاسم
        $manager = Employee::where('name', $row['manager'])->first();
        if(!$manager){
            throw ValidationException::withMessages([
                'message'=>'no manager with name ' . $row['manager']
            ]);
        }

        Log::channel('employee')->info('Employee imported', [
            'employee info'  => $row
        ]);


        return new Employee([
            'name' => $row['name'] ?? 'No Name',
            'email' => $row['email'] ?? null,
            'salary' => $salary,
            'position_id' => $position?->id,
            'manager_id' => $manager?->id,
            'is_founder' => filter_var($row['is_founder'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);

    }
}
