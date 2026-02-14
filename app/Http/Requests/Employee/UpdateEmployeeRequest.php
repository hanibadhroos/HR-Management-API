<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:employees,email,' . $this->route('employee')->id,
            // 'email' => 'sometimes|email|unique:employees,email,' . $this->route('employee'),
            'salary' => 'sometimes|numeric|min:0',
            'position_id' => 'sometimes|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
            'is_founder' => 'sometimes|boolean',
        ];
    }
}
