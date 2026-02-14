<?php

namespace App\Listeners;

use App\Events\SalaryChanged;
use App\Models\Employee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandelSalaryChanged
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     */
    public function handle(SalaryChanged $event): void
    {
        $employee = $event->employee;

        // Send email to employee
        $employee->notify(
            new \App\Notifications\SalaryChangedNotification(
                $event->oldSalary,
                $employee->salary
            )
        );


        // Broadcast to managers up to founder
        $manager = $employee->manager;


        while ($manager) {

            $manager->notify(
                new \App\Notifications\ManagerSalaryChangedNotification($employee)
            );
            

            ///// من الى مدير اعلى وصولا الى الموسس
            $manager = $manager->manager;  
        }

        // $allEmployees = Employee::all()->keyBy('id');
        // $managerId = $employee->manager_id;

        // while ($managerId) {
        //     $manager = $allEmployees->get($managerId);

        //     if (!$manager) break;

        //     $manager->notify(
        //         new \App\Notifications\ManagerSalaryChangedNotification($employee)
        //     );

        //     $managerId = $manager->manager_id;
        // }


        Log::channel('employee')->info('Salary changed', [
            'employee_id' => $employee->id,
            'old_salary' => $event->oldSalary,
            'new_salary' => $employee->salary,
        ]);
    }
}
