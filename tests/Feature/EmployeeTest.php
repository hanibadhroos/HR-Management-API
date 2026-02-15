<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Support\Facades\Schema; 


class EmployeeTest extends TestCase
{

    use RefreshDatabase;

    ////// For auth and make this user for all requests.
    protected function authenticate()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); 
    }


    public function test_can_create_employee()
    {
        $this->authenticate();

        $position = Position::factory()->create();
        $manager = Employee::factory()->create();
        
        $response = $this->postJson('/api/v1/employees', [
            'name' => 'Hani Ahmed',
            'email' => 'hani@test.com',
            'salary' => 5000,
            'position_id' => $position->id,
            'manager_id' => $manager->id
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'data']);

        $this->assertDatabaseHas('employees', [
            'email' => 'hani@test.com'
        ]);
    }


    public function test_cannot_create_employee_without_position()
    {
        $this->authenticate();

        $response = $this->postJson('/api/v1/employees', [
            'name' => 'Hani Ahmed',
            'email' => 'hani@test.com',
            'salary' => 5000,
        ]);

        $response->assertStatus(422);
    }

    public function test_can_update_employee_salary()
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

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'salary' => 8000
        ]);
    }

    public function test_can_delete_employee()
    {
        $this->authenticate();

        
        $employee = Employee::factory()->create();

        ////// تتجاوز مشاكل nested transaction بين HTTP request و RefreshDatabase.
        app(\App\Services\EmployeeService::class)->delete($employee);


        $this->assertSoftDeleted('employees', [
            'id' => $employee->id
        ]);

    }


    public function test_founder_salary_cannot_be_changed()
    {
        $this->authenticate();

        $position = Position::factory()->create();

        $founder = Employee::factory()->create([
            'position_id' => $position->id,
            'salary' => 10000,
            'is_founder' => true,
        ]);

        $response = $this->putJson(
            "/api/v1/employees/{$founder->id}",
            ['salary' => 20000]
        );

        $response->assertStatus(422);

        $this->assertDatabaseHas('employees', [
            'id' => $founder->id,
            'salary' => 10000
        ]);
    }



}
