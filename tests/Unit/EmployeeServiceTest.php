<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Position;
use App\Services\EmployeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeServiceTest extends TestCase
{
    use RefreshDatabase;

    

    public function test_salary_change_updates_salary_changed_at()
    {
        $position = Position::factory()->create();

        $employee = Employee::factory()->create([
            'position_id' => $position->id,
            'salary' => 3000,
            'salary_changed_at' => now()->subMonths(2),
        ]);

        $service = new EmployeeService();

        $manager = Employee::factory()->create([
            'position_id' => $position->id
        ]);

        $service->update($employee, [
            'salary' => 7000,
            'manager_id' => $manager->id
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'salary' => 7000
        ]);

        $this->assertNotNull(
            $employee->fresh()->salary_changed_at
        );
    }
}

