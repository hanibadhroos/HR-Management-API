<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\EmployeeLogController;
use App\Http\Controllers\Api\V1\PositionController;

Route::prefix('auth')->group(function () {

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);


    });

});

////Employee routes
Route::middleware('auth:sanctum')->group(function(){

    Route::apiResource('employees', EmployeeController::class);

    Route::get('employees-export', [EmployeeController::class, 'export']);

    Route::post('employees-import', [EmployeeController::class, 'import']);

    Route::get('employees/no-salary-change/{months}',[EmployeeController::class, 'noSalaryChange']);

});

Route::prefix('employees')->middleware('auth:sanctum')->group(function () {

    Route::get('{employee}/hierarchy', [EmployeeController::class, 'hierarchy']);
    Route::get('{employee}/hierarchy-with-salaries', [EmployeeController::class, 'hierarchyWithSalaries']);

    Route::get('employee-logs',[EmployeeLogController::class, 'index']);
});



///// Position routes
Route::middleware('auth::sanctum')->group(function(){
    Route::apiResource('positions', PositionController::class);


});

