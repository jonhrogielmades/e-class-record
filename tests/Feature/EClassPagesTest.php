<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->actingAs($teacher)->get(route('analytics.index'))->assertOk();
        $this->actingAs($teacher)->get(route('attendance.calendar'))->assertOk();
        $this->actingAs($teacher)->get(route('announcements.index'))->assertOk();
        $this->actingAs($teacher)->get(route('assignments.index'))->assertOk();
        $this->actingAs($teacher)->get(route('notifications.index'))->assertOk();
        $this->actingAs($teacher)->get(route('reports.print'))->assertOk();
        $this->actingAs($teacher)->get(route('reports.gradesCsv'))->assertOk();
        $this->actingAs($teacher)->get(route('reports.attendanceCsv'))->assertOk();
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
        $this->actingAs($student)->get(route('analytics.index'))->assertOk();
        $this->actingAs($student)->get(route('attendance.calendar'))->assertOk();
        $this->actingAs($student)->get(route('announcements.index'))->assertOk();
        $this->actingAs($student)->get(route('assignments.index'))->assertOk();
        $this->actingAs($student)->get(route('notifications.index'))->assertOk();
        $this->actingAs($student)->get(route('settings.index'))->assertOk();
    }

    public function test_admin_pages_render(): void
    {
        $this->seed();

        $admin = User::query()->where('role', User::ROLE_ADMIN)->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Admin Panel');

        $this->actingAs($admin)->get(route('settings.index'))->assertOk();
    }

    public function test_guardian_lookup_renders_student_record(): void
    {
        $this->seed();

        $this->post(route('guardian.lookup'), [
            'student_number' => '2026-A-001',
            'access_code' => 'Mila Santos',
        ])->assertOk()
            ->assertSee('Aira Mae Santos');
    }

    public function test_api_sections_crud(): void
    {
        $this->seed();

        $teacher = User::query()->where('role', User::ROLE_TEACHER)->firstOrFail();

        $create = $this->postJson('/api/sections', [
            'teacher_id' => $teacher->id,
            'name' => 'Section API',
            'strand' => 'BSIT API',
            'room' => 'API Lab',
            'schedule' => 'Friday - 9:00 AM',
            'adviser' => $teacher->name,
            'description' => 'Created from API test.',
        ])->assertCreated();

        $id = $create->json('data.id');

        $this->getJson("/api/sections/{$id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Section API');

        $this->putJson("/api/sections/{$id}", [
            'teacher_id' => $teacher->id,
            'name' => 'Section API Updated',
            'strand' => 'BSIT API',
            'room' => 'API Lab 2',
            'schedule' => 'Friday - 10:00 AM',
            'adviser' => $teacher->name,
            'description' => 'Updated from API test.',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Section API Updated');

        $this->deleteJson("/api/sections/{$id}")
            ->assertNoContent();

        $this->getJson('/api/sections')
            ->assertOk();
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sections');
        Schema::enableForeignKeyConstraints();

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Database setup required');
    }

    public function test_login_handles_missing_users_table_gracefully(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        $this->post(route('login.store'), [
            'email' => 'teacher@eclass.local',
            'password' => 'teacher123',
        ])->assertSessionHas('error');
    }
}
