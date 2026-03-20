<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Grade;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EClassDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Grade::query()->delete();
            AttendanceRecord::query()->delete();
            StudentProfile::query()->delete();
            Section::query()->delete();
            User::query()->delete();

            $teacher = User::query()->create([
                'name' => 'Prof. Lucia Mendoza',
                'email' => 'professor@eclass.local',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_TEACHER,
                'phone' => '+63 917 555 2000',
                'title' => 'Class Adviser',
                'department' => 'BSIT Program',
            ]);

            $sectionA = Section::query()->create([
                'teacher_id' => $teacher->id,
                'name' => 'Section A',
                'strand' => 'BSIT 3A',
                'room' => 'ICT Lab 201',
                'schedule' => 'Mon / Wed / Fri - 8:00 AM to 10:00 AM',
                'adviser' => $teacher->name,
                'description' => 'Core application development section.',
            ]);

            $sectionB = Section::query()->create([
                'teacher_id' => $teacher->id,
                'name' => 'Section B',
                'strand' => 'BSIT 3B',
                'room' => 'ICT Lab 305',
                'schedule' => 'Tue / Thu - 1:00 PM to 3:00 PM',
                'adviser' => $teacher->name,
                'description' => 'Core systems and integration section.',
            ]);

            $airaUser = User::query()->create([
                'name' => 'Aira Mae Santos',
                'email' => 'aira.santos@eclass.local',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_STUDENT,
                'phone' => '+63 917 555 1020',
            ]);

            $johnUser = User::query()->create([
                'name' => 'John Paul Rivera',
                'email' => 'john.rivera@eclass.local',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_STUDENT,
                'phone' => '+63 917 555 1021',
            ]);

            $airaStudent = StudentProfile::query()->create([
                'user_id' => $airaUser->id,
                'section_id' => $sectionA->id,
                'student_number' => '2026-A-001',
                'name' => $airaUser->name,
                'email' => $airaUser->email,
                'guardian' => 'Mila Santos',
                'contact' => $airaUser->phone,
                'address' => 'Poblacion East, San Jose',
                'focus' => 'Frontend Development',
                'status' => 'Regular',
            ]);

            $johnStudent = StudentProfile::query()->create([
                'user_id' => $johnUser->id,
                'section_id' => $sectionB->id,
                'student_number' => '2026-B-001',
                'name' => $johnUser->name,
                'email' => $johnUser->email,
                'guardian' => 'Ramon Rivera',
                'contact' => $johnUser->phone,
                'address' => 'Luna Street, San Jose',
                'focus' => 'Database Design',
                'status' => 'Regular',
            ]);

            $meetingDate = now()->toDateString();
            $recordedAt = now();

            AttendanceRecord::query()->create([
                'student_profile_id' => $airaStudent->id,
                'section_id' => $sectionA->id,
                'marked_by' => $teacher->id,
                'date' => $meetingDate,
                'topic' => 'Orientation Meeting',
                'status' => 'present',
                'remarks' => 'Present',
            ]);

            AttendanceRecord::query()->create([
                'student_profile_id' => $johnStudent->id,
                'section_id' => $sectionB->id,
                'marked_by' => $teacher->id,
                'date' => $meetingDate,
                'topic' => 'Orientation Meeting',
                'status' => 'present',
                'remarks' => 'Present',
            ]);

            Grade::query()->create([
                'student_profile_id' => $airaStudent->id,
                'section_id' => $sectionA->id,
                'recorded_by' => $teacher->id,
                'category' => 'Quiz',
                'title' => 'Quiz 1',
                'score' => 18,
                'max_score' => 20,
                'remarks' => 'Good performance.',
                'recorded_at' => $recordedAt,
            ]);

            Grade::query()->create([
                'student_profile_id' => $johnStudent->id,
                'section_id' => $sectionB->id,
                'recorded_by' => $teacher->id,
                'category' => 'Quiz',
                'title' => 'Quiz 1',
                'score' => 17,
                'max_score' => 20,
                'remarks' => 'Good performance.',
                'recorded_at' => $recordedAt,
            ]);
        });
    }
}
