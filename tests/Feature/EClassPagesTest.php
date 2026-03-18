<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EClassPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render(): void
    {
        $this->get(route('landing'))->assertOk();
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
    }

    public function test_teacher_pages_render(): void
    {
        $this->seed();

        $teacher = User::query()->where('role', User::ROLE_TEACHER)->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Section Snapshot');

        $this->actingAs($teacher)->get(route('sections.index'))->assertOk();
        $this->actingAs($teacher)->get(route('students.index'))->assertOk();
        $this->actingAs($teacher)->get(route('grades.index'))->assertOk();
        $this->actingAs($teacher)->get(route('settings.index'))->assertOk();
    }

    public function test_student_pages_render(): void
    {
        $this->seed();

        $student = User::query()->where('role', User::ROLE_STUDENT)->firstOrFail();

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Section');

        $this->actingAs($student)->get(route('sections.index'))->assertOk();
        $this->actingAs($student)->get(route('students.index'))->assertOk();
        $this->actingAs($student)->get(route('grades.index'))->assertOk();
        $this->actingAs($student)->get(route('settings.index'))->assertOk();
    }
}