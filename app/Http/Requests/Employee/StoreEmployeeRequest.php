<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(){
        return[
            'name' => 'required|string|max:255|unique:employees,name',
            'email' => 'required|email|unique:employees,email',
            'salary' => 'required|numeric|min:0',
            'position_id' => 'required|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
            'is_founder' => 'boolean',
        ];
    }

}
