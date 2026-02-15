<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\ImportEmployeeRequest;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;


class EmployeeController extends Controller
{

    public function __construct(
        protected EmployeeService $employeeService
    ) {}



    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $employees = $this->employeeService->getAll(
            $request->only(['name', 'salary', 'salary_min', 'salary_max'])
        );

        return EmployeeResource::collection($employees);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->employeeService->create($request->validated());

        return response()->json([
            'message' => 'Employee created successfully.',
            'data' => new EmployeeResource($employee)
        ], 201);
    }

    /**
     * Display the specified resource.
     */


    public function show(Employee $employee)
    {
        $employee->load(['manager', 'position']);

        return new EmployeeResource($employee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request,  Employee $employee)
    {
        if(!$employee){
            return response()->json([
                'message'=> 'Employee no found.'
            ], 404);
        }

        $employee = $this->employeeService->update(
            $employee,
            $request->validated()
        );

        return response()->json([
            'message' => 'Employee updated successfully.',
            'data' => new EmployeeResource($employee)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $this->employeeService->delete($employee);

        return response()->json([
            'message' => 'Employee deleted successfully.'
        ]);
    }


    
    ////////Hierarchy by employee name
    public function hierarchy(Employee $employee)
    {
        $data = $this->employeeService->getHierarchy($employee);

        return response()->json([
            'data' => $data
        ]);
    }

    ///////Heirarchy by name + Salary.
    public function hierarchyWithSalaries(Employee $employee)
    {
        $data = $this->employeeService->getHierarchyWithSalaries($employee);

        return response()->json([
            'data' => $data
        ]);
    }


    ///// Expoert emp data to CSV file.
    public function export(): StreamedResponse
    {
        $employees = $this->employeeService->exportToCsv();

        $fileName = 'employees_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
        ];

        $callback = function () use ($employees) {

            $handle = fopen('php://output', 'w');

            // Header Row
            fputcsv($handle, [
                'ID',
                'Name',
                'Email',
                'Salary',
                'Position',
                'Manager',
                'Is Founder',
                'Salary Changed At',
                'Created At'
            ]);

            foreach ($employees as $employee) {

                fputcsv($handle, [
                    $employee->id,
                    $employee->name,
                    $employee->email,
                    $employee->salary,
                    $employee->position?->title,
                    $employee->manager?->name,
                    $employee->is_founder ? 'Yes' : 'No',
                    $employee->salary_changed_at,
                    $employee->created_at,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }


    ////////Import employees of csv file.
    public function import(ImportEmployeeRequest $request)
    {
        $result = $this->employeeService->importFromCsv(
            $request->file('file')
        );

        return response()->json([
            'message' => 'Import completed.',
        ]);
    }


    ///// Employees without salary changed in X months.
    public function noSalaryChange(int $months)
    {
        if ($months <= 0) {
            return response()->json([
                'message' => 'Months must be greater than zero.'
            ], 422);
        }

        $employees = $this->employeeService
            ->getEmployeesWithoutSalaryChange($months);

        return EmployeeResource::collection($employees);
    }

}
