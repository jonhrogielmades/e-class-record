<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    public function test_unknown_login_shows_validation_message(): void
    {
        $this->post(route('login.store'), [
            'email' => 'no-account@eclass.local',
            'password' => 'invalid-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_register_page_handles_missing_sections_table_gracefully(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('sections');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Database setup required');
    }

    public function test_login_handles_missing_users_table_gracefully(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('users');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->post(route('login.store'), [
            'email' => 'teacher@eclass.local',
            'password' => 'teacher123',
        ])->assertSessionHas('error');
    }
}
