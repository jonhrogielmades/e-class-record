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

            $teacher = User::create([
                'name' => 'Prof. Lucia Mendoza',
                'email' => 'teacher@eclass.local',
                'password' => Hash::make('teacher123'),
                'role' => User::ROLE_TEACHER,
                'phone' => '+63 917 555 2010',
                'title' => 'Class Adviser',
                'department' => 'BSIT Program',
                'created_at' => '2026-01-08 08:00:00',
                'updated_at' => '2026-01-08 08:00:00',
            ]);

            $studentUser = User::create([
                'name' => 'Aira Mae Santos',
                'email' => 'student@eclass.local',
                'password' => Hash::make('student123'),
                'role' => User::ROLE_STUDENT,
                'phone' => '+63 917 555 1020',
                'created_at' => '2026-01-09 08:30:00',
                'updated_at' => '2026-01-09 08:30:00',
            ]);

            $sections = collect([
                [
                    'name' => 'Section A',
                    'strand' => 'BSIT 3A',
                    'room' => 'ICT Lab 201',
                    'schedule' => 'Mon / Wed / Fri - 8:00 AM to 10:00 AM',
                    'adviser' => 'Prof. Lucia Mendoza',
                    'description' => 'Application development, database laboratory, and formative quiz tracking.',
                ],
                [
                    'name' => 'Section B',
                    'strand' => 'BSIT 3B',
                    'room' => 'ICT Lab 305',
                    'schedule' => 'Tue / Thu - 1:00 PM to 3:00 PM',
                    'adviser' => 'Prof. Lucia Mendoza',
                    'description' => 'Systems integration, networking activities, and project-based assessment tracking.',
                ],
            ])->mapWithKeys(function (array $section) use ($teacher) {
                $model = Section::create(array_merge($section, [
                    'teacher_id' => $teacher->id,
                    'created_at' => '2026-01-10 09:00:00',
                    'updated_at' => '2026-01-10 09:00:00',
                ]));

                return [$model->name => $model];
            });

            $students = collect([
                [
                    'student_number' => '2026-A-001',
                    'name' => 'Aira Mae Santos',
                    'email' => 'student@eclass.local',
                    'section' => 'Section A',
                    'guardian' => 'Mila Santos',
                    'contact' => '+63 917 555 1020',
                    'address' => 'Poblacion East, San Jose',
                    'focus' => 'Frontend Development',
                    'status' => 'Regular',
                    'user_id' => $studentUser->id,
                ],
                [
                    'student_number' => '2026-A-002',
                    'name' => 'John Paul Rivera',
                    'email' => 'john.rivera@eclass.local',
                    'section' => 'Section A',
                    'guardian' => 'Ramon Rivera',
                    'contact' => '+63 917 555 1021',
                    'address' => 'Luna Street, San Jose',
                    'focus' => 'Database Design',
                    'status' => 'Regular',
                ],
                [
                    'student_number' => '2026-A-003',
                    'name' => 'Camille Dela Cruz',
                    'email' => 'camille.delacruz@eclass.local',
                    'section' => 'Section A',
                    'guardian' => 'Anna Dela Cruz',
                    'contact' => '+63 917 555 1022',
                    'address' => 'Rizal Avenue, San Jose',
                    'focus' => 'UI Design',
                    'status' => 'Regular',
                ],
                [
                    'student_number' => '2026-A-004',
                    'name' => 'Neil Adrian Gomez',
                    'email' => 'neil.gomez@eclass.local',
                    'section' => 'Section A',
                    'guardian' => 'Lorna Gomez',
                    'contact' => '+63 917 555 1023',
                    'address' => 'Mabini District, San Jose',
                    'focus' => 'Software Testing',
                    'status' => 'Regular',
                ],
                [
                    'student_number' => '2026-B-001',
                    'name' => 'Sophia Anne Reyes',
                    'email' => 'sophia.reyes@eclass.local',
                    'section' => 'Section B',
                    'guardian' => 'Joel Reyes',
                    'contact' => '+63 917 555 1024',
                    'address' => 'Bayanihan Homes, San Jose',
                    'focus' => 'Networking',
                    'status' => 'Regular',
                ],
                [
                    'student_number' => '2026-B-002',
                    'name' => 'Mark Joseph Flores',
                    'email' => 'mark.flores@eclass.local',
                    'section' => 'Section B',
                    'guardian' => 'Grace Flores',
                    'contact' => '+63 917 555 1025',
                    'address' => 'Pine Road, San Jose',
                    'focus' => 'System Analysis',
                    'status' => 'Regular',
                ],
                [
                    'student_number' => '2026-B-003',
                    'name' => 'Trisha Mae Castillo',
                    'email' => 'trisha.castillo@eclass.local',
                    'section' => 'Section B',
                    'guardian' => 'Nina Castillo',
                    'contact' => '+63 917 555 1026',
                    'address' => 'Central Park Subdivision, San Jose',
                    'focus' => 'Information Security',
                    'status' => 'Regular',
                ],
                [
                    'student_number' => '2026-B-004',
                    'name' => 'Kevin Lorenz Tan',
                    'email' => 'kevin.tan@eclass.local',
                    'section' => 'Section B',
                    'guardian' => 'Victor Tan',
                    'contact' => '+63 917 555 1027',
                    'address' => 'Magsaysay Extension, San Jose',
                    'focus' => 'Cloud Computing',
                    'status' => 'Regular',
                ],
            ])->mapWithKeys(function (array $student) use ($sections) {
                $section = $sections[$student['section']];
                $model = StudentProfile::create([
                    'user_id' => $student['user_id'] ?? null,
                    'section_id' => $section->id,
                    'student_number' => $student['student_number'],
                    'name' => $student['name'],
                    'email' => $student['email'],
                    'guardian' => $student['guardian'],
                    'contact' => $student['contact'],
                    'address' => $student['address'],
                    'focus' => $student['focus'],
                    'status' => $student['status'],
                    'created_at' => '2026-01-12 09:00:00',
                    'updated_at' => '2026-01-12 09:00:00',
                ]);

                return [$student['student_number'] => $model];
            });

            $attendanceDates = [
                ['date' => '2026-03-03', 'topic' => 'Orientation and syllabus review'],
                ['date' => '2026-03-05', 'topic' => 'Database laboratory'],
                ['date' => '2026-03-10', 'topic' => 'Midterm review'],
                ['date' => '2026-03-12', 'topic' => 'Hands-on coding exercise'],
                ['date' => '2026-03-17', 'topic' => 'Performance task check-in'],
            ];

            $attendanceMap = [
                '2026-A-001' => ['present', 'present', 'late', 'present', 'present'],
                '2026-A-002' => ['present', 'present', 'present', 'late', 'present'],
                '2026-A-003' => ['late', 'present', 'present', 'present', 'present'],
                '2026-A-004' => ['present', 'absent', 'present', 'present', 'late'],
                '2026-B-001' => ['present', 'present', 'present', 'present', 'present'],
                '2026-B-002' => ['absent', 'present', 'late', 'present', 'present'],
                '2026-B-003' => ['present', 'late', 'present', 'present', 'present'],
                '2026-B-004' => ['present', 'present', 'absent', 'present', 'present'],
            ];

            $remarksByStatus = [
                'present' => 'Present and engaged during class activities.',
                'late' => 'Late arrival noted by the adviser.',
                'absent' => 'Absent during the recorded meeting.',
            ];

            foreach ($attendanceMap as $studentNumber => $statuses) {
                $student = $students[$studentNumber];

                foreach ($attendanceDates as $index => $meeting) {
                    $status = $statuses[$index];

                    AttendanceRecord::create([
                        'student_profile_id' => $student->id,
                        'section_id' => $student->section_id,
                        'marked_by' => $teacher->id,
                        'date' => $meeting['date'],
                        'topic' => $meeting['topic'],
                        'status' => $status,
                        'remarks' => $remarksByStatus[$status],
                        'created_at' => $meeting['date'].' 08:00:00',
                        'updated_at' => $meeting['date'].' 08:00:00',
                    ]);
                }
            }

            $gradeTemplates = [
                ['category' => 'Quiz', 'title' => 'Quiz 1 - Web Concepts', 'max_score' => 20, 'recorded_at' => '2026-03-04 09:10:00'],
                ['category' => 'Quiz', 'title' => 'Quiz 2 - Data Modeling', 'max_score' => 20, 'recorded_at' => '2026-03-08 09:10:00'],
                ['category' => 'Exam', 'title' => 'Midterm Exam', 'max_score' => 50, 'recorded_at' => '2026-03-13 10:00:00'],
                ['category' => 'Project', 'title' => 'Interface Prototype', 'max_score' => 100, 'recorded_at' => '2026-03-17 15:00:00'],
            ];

            $gradeMap = [
                '2026-A-001' => [18, 19, 45, 93],
                '2026-A-002' => [17, 18, 42, 89],
                '2026-A-003' => [19, 18, 46, 95],
                '2026-A-004' => [15, 17, 39, 84],
                '2026-B-001' => [20, 19, 47, 97],
                '2026-B-002' => [16, 17, 41, 86],
                '2026-B-003' => [18, 18, 44, 91],
                '2026-B-004' => [17, 16, 40, 88],
            ];

            foreach ($gradeMap as $studentNumber => $scores) {
                $student = $students[$studentNumber];

                foreach ($gradeTemplates as $index => $template) {
                    $score = $scores[$index];

                    Grade::create([
                        'student_profile_id' => $student->id,
                        'section_id' => $student->section_id,
                        'recorded_by' => $teacher->id,
                        'category' => $template['category'],
                        'title' => $template['title'],
                        'score' => $score,
                        'max_score' => $template['max_score'],
                        'remarks' => $score >= ($template['max_score'] * 0.9)
                            ? 'Excellent work and consistent output.'
                            : 'Recorded and ready for follow-up feedback.',
                        'recorded_at' => $template['recorded_at'],
                        'created_at' => $template['recorded_at'],
                        'updated_at' => $template['recorded_at'],
                    ]);
                }
            }
        });
    }
}