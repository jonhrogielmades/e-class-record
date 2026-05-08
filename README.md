# E-Class Record System (Laravel)

This is a Laravel-based E-Class Record System for managing class sections, student profiles, attendance records, and grades through role-based teacher and student dashboards.

## Stack

- Laravel 10
- PHP 8.2+
- MySQL
- Blade templates
- Vanilla JavaScript for UI helpers

## Features

- Teacher and student login
- Teacher dashboard, class list, students, grading, and settings pages
- Student dashboard, class view, records, grades, and settings pages
- Section CRUD
- Student profile CRUD
- Attendance CRUD
- Grade CRUD
- Grade and attendance CSV export
- Printable report page for browser PDF export
- Performance analytics charts
- Guardian read-only lookup portal
- Section announcements
- Assignment/activity tracking
- Attendance calendar view
- Search and filter controls
- Role-based admin panel
- REST API routes for sections
- In-app notifications for student updates
- Database-seeded test data for quick functional testing

## Technical Requirements Coverage

| Requirement | Project implementation |
| --- | --- |
| Laravel Framework | Built on Laravel 10 with standard `app`, `routes`, `resources`, `database`, `config`, and `tests` folders. |
| PHP MVC backend | Controllers in `app/Http/Controllers`, models in `app/Models`, services in `app/Services`, and Blade views in `resources/views`. |
| MySQL database | `.env.example` and `phpunit.xml` are configured for MySQL databases. |
| Migrations | Table definitions are in `database/migrations`. |
| Eloquent ORM / Query Builder | Models and controllers use Eloquent relationships, queries, validation, transactions, and CRUD methods. |
| Complete CRUD | Sections, student profiles, attendance records, and grade records all support create, read, update, and delete workflows. |
| Validation and error handling | Form requests use Laravel validation rules, session flash messages, guarded ownership checks, and database-readiness handling. |
| Responsive UI | Blade templates share consistent layouts, navigation, forms, tables, and responsive CSS. |
| Optional REST API | `routes/api.php` exposes section CRUD endpoints for Postman/API demonstrations. |
| Version control | Repository is ready for GitHub with organized source files and setup documentation. |

## Added Feature Set

1. **Grade and Attendance Export** - Teachers, students, and admins can download CSV files for Excel from the Analytics/Reports area.
2. **Printable PDF Report** - The printable report page can be saved as PDF from the browser print dialog.
3. **Performance Analytics** - Analytics page shows grade trends, attendance mix, category averages, and section comparisons.
4. **Guardian View** - Public guardian lookup uses student number plus saved guardian name/contact to show read-only student progress.
5. **Announcements** - Teachers can publish, edit, and delete section announcements; students can read their section announcements.
6. **Assignment Tracking** - Teachers can create, edit, delete, and close assignments/activities per section; students can view assigned work.
7. **Attendance Calendar** - Monthly calendar view displays attendance records by date.
8. **Search and Filters** - Students and grading pages include learner, category, status, and date filters.
9. **Admin Panel** - Admin role can review teachers, students, sections, create teachers, and remove eligible records.
10. **REST API and Notifications** - API section CRUD supports Postman demos, and notifications are created for grades, attendance, announcements, and assignments.

## CRUD Modules

| Module | Create | Read/View | Update/Edit | Delete |
| --- | --- | --- | --- | --- |
| Sections | Teacher creates class sections | Class List page and dashboard summaries | Teacher edits selected section | Teacher deletes empty sections |
| Student Profiles | Teacher creates learner profiles | Students page and roster cards | Teacher edits learner details | Teacher deletes learner profiles |
| Attendance | Teacher records attendance | Attendance table and student record view | Teacher edits attendance entries | Teacher deletes attendance entries |
| Grades | Teacher records assessments | Gradebook, charts, and student grade history | Teacher edits grade entries | Teacher deletes grade entries |

## API Note

RESTful API routes are optional for this requirement set. The current submission focuses on the Laravel web MVC implementation and complete authenticated CRUD workflows. The default Sanctum `/api/user` route is retained for future API expansion.

## Demo Accounts (Seeded)

All seeded users use the same password: `password123`

- Admin: `admin@eclass.local` (System Administrator)
- Professor: `professor@eclass.local` (Prof. Lucia Mendoza)
- Student: `aira.santos@eclass.local` (Aira Mae Santos, Section A)
- Student: `john.rivera@eclass.local` (John Paul Rivera, Section B)

Guardian lookup demo:

- Student Number: `2026-A-001`
- Access Code: `Mila Santos`

## Setup After Cloning

Run these commands from the project root:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create the MySQL databases:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS e_class_record CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE IF NOT EXISTS e_class_record_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Set your database values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e_class_record
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seed the test data:

```bash
php artisan migrate:fresh --seed
```

Start the app:

```bash
php artisan serve
```

Open `http://127.0.0.1:8000` and use the seeded accounts above.

## Important Routes

- `/login` - teacher, student, and admin login
- `/guardian` - guardian lookup portal
- `/dashboard` - teacher/student dashboard
- `/analytics` - charts, reports, CSV exports, and printable PDF page link
- `/attendance-calendar` - monthly attendance calendar
- `/announcements` - section announcement module
- `/assignments` - assignment/activity tracking
- `/notifications` - in-app notification inbox
- `/admin` - administrator panel

## REST API Routes

The optional API module is available at:

```text
GET    /api/sections
POST   /api/sections
GET    /api/sections/{id}
PUT    /api/sections/{id}
PATCH  /api/sections/{id}
DELETE /api/sections/{id}
```

Example JSON body for `POST /api/sections`:

```json
{
  "teacher_id": 1,
  "name": "Section API",
  "strand": "BSIT API",
  "room": "API Lab",
  "schedule": "Friday - 9:00 AM",
  "adviser": "Prof. Lucia Mendoza",
  "description": "Created from Postman."
}
```

## Optional Frontend Dev Command

```bash
npm install
npm run dev
```

## Tests

```bash
php artisan test
```

The test suite uses the dedicated `e_class_record_test` MySQL database from `phpunit.xml`.

## Notes

- The original frontend prototype is preserved in `legacy-prototype/`.
- The app is configured for MySQL via `.env`.
