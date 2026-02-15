<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Models\Position;
use App\Models\Employee;
use Illuminate\Support\Facades\Event;

class PositionTest extends TestCase
{

    use RefreshDatabase;

    

    public function authenticate(){
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }
    ///// باقي فيها مشكلة
    public function test_position_cannot_be_deleted_if_has_employees()
    {
        $this->authenticate();

        $position = Position::factory()->create();

        Employee::factory()->create([
            'position_id' => $position->id
        ]);

        $response = $this->deleteJson(
            "/api/v1/positions/{$position->id}"
        );

        $response->assertStatus(422);

        $this->assertDatabaseHas('positions', [
            'id' => $position->id
        ]);
    }


    public function test_logs_created_when_employee_updated()
    {
        $this->authenticate();

        Event::fake();

        $position = Position::factory()->create();

        $manager = Employee::factory()->create();

        $employee = Employee::factory()->create([
            'position_id' => $position->id,
            'salary' => 3000,
            'manager_id' => $manager->id
        ]);

        app(\App\Services\EmployeeService::class)->update($employee, [
            'salary' => 8000,
            'manager_id' => $manager->id,
        ]);

        $this->assertDatabaseHas('employee_logs', [
            'employee_id' => $employee->id,
            'action' => 'updated'
        ]);
    }


    public function test_employee_without_salary_change_filter()
    {
        $this->authenticate();

        $position = Position::factory()->create();

        $oldEmployee = Employee::factory()->create([
            'position_id' => $position->id,
            'salary_changed_at' => now()->subMonths(6)
        ]);

        $recentEmployee = Employee::factory()->create([
            'position_id' => $position->id,
            'salary_changed_at' => now()
        ]);

        $response = $this->getJson(
            "/api/v1/employees/no-salary-change/3"
        );

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'id' => $oldEmployee->id
                ]);

        $response->assertJsonMissing([
            'id' => $recentEmployee->id
        ]);
    }

}

