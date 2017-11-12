<?php

namespace Tests\Feature\Employees;

use App\Employees\Employee;
use Tests\TestCase;

class EmployeeFeatureTest extends TestCase
{
    /** @test */
    public function it_errors_when_editing_an_employee_that_is_not_found()
    {
        $this->actingAs($this->employee, 'admin')
            ->get(route('admin.employees.edit', 999))
            ->assertStatus(404);
    }
    
    /** @test */
    public function it_errors_when_looking_for_an_employee_that_is_not_found()
    {
        $this->actingAs($this->employee, 'admin')
            ->get(route('admin.employees.show', 999))
            ->assertStatus(404);
    }
    
    /** @test */
    public function it_can_list_all_the_employees()
    {
        $employee = factory(Employee::class)->create();

        $this->actingAs($this->employee, 'admin')
            ->get(route('admin.employees.index'))
            ->assertStatus(200)
            ->assertSee($employee->name);
    }

    /** @test */
    public function it_errors_when_the_email_is_already_taken()
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->employee->email,
            'password' => 'secret'
        ];

        $this->actingAs($this->employee, 'admin')
            ->post(route('admin.employees.store'), $data)
            ->assertStatus(302)
            ->assertSessionHas(['errors']);
    }

    /** @test */
    public function it_errors_if_the_password_is_less_than_eight_characters()
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'secret'
        ];

        $this->actingAs($this->employee, 'admin')
            ->post(route('admin.employees.store'), $data)
            ->assertStatus(302)
            ->assertSessionHas(['errors']);
    }
    
    /** @test */
    public function it_can_only_soft_delete_an_employee()
    {
        $employee = factory(Employee::class)->create();

        $this->actingAs($this->employee, 'admin')
            ->delete(route('admin.employees.destroy', $employee->id))
            ->assertStatus(302)
            ->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseHas('employees', $employee->toArray());
    }

    /** @test */
    public function it_can_update_the_employees_password()
    {
        $employee = factory(Employee::class)->create();

        $update = [
            'name' => $employee->name,
            'email' => $employee->email,
            'password' => 'secret!!'
        ];

        $this->actingAs($this->employee, 'admin')
            ->put(route('admin.employees.update', $employee->id), $update)
            ->assertStatus(302)
            ->assertRedirect(route('admin.employees.edit', $employee->id));

        $collection = collect($update)->except('password');
        $this->assertDatabaseHas('employees', $collection->all());
    }
    
    /** @test */
    public function it_can_update_the_employee()
    {
        $update = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->email
        ];

        $this->actingAs($this->employee, 'admin')
            ->put(route('admin.employees.update', $this->employee->id), $update)
            ->assertStatus(302)
            ->assertRedirect(route('admin.employees.edit', $this->employee->id));

        $this->assertDatabaseHas('employees', $update);
    }
    
    /** @test */
    public function it_can_show_the_employee()
    {
        $this->actingAs($this->employee, 'admin')
            ->get(route('admin.employees.show', $this->employee->id))
            ->assertStatus(200)
            ->assertViewHas('employee');
    }
    
    /** @test */
    public function it_can_create_an_employee()
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'secret!!'
        ];

        $this->actingAs($this->employee, 'admin')
            ->post(route('admin.employees.store'), $data)
            ->assertStatus(302)
            ->assertRedirect(route('admin.employees.index'));

        $created = collect($data)->except('password');

        $this->assertDatabaseHas('employees', $created->all());
    }
}