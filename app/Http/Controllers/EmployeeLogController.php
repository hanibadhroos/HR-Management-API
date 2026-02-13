<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmployeeLogResource;
use App\Services\EmployeeLogService;
use Illuminate\Http\Request;

class EmployeeLogController extends Controller
{
    public function __construct(
        protected EmployeeLogService $logService
    ) {}

    public function index(Request $request)
    {
        $logs = $this->logService->getAll(
            $request->only('employee_id')
        );

        return EmployeeLogResource::collection($logs);
    }


}
