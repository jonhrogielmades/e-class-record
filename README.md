# E-Class Record System (Laravel)

This project converts the original static HTML/CSS/JavaScript prototype into a Laravel application with real authentication, database-backed records, seeded demo data, and Blade-rendered teacher/student dashboards.

## Stack

- Laravel 10
- PHP 8.2
- SQLite
- Blade templates
- Vanilla JavaScript for theme toggles, charts, and small UI helpers

## Features

- Teacher and student login
- Teacher dashboard, class list, students, grading, and settings pages
- Student dashboard, class view, records, grades, and settings pages
- Section CRUD
- Student profile CRUD
- Attendance CRUD
- Grade CRUD
- Seeded demo data for presentation and testing
- Preserved glass-style UI from the original prototype

## Demo Accounts

- Teacher: `teacher@eclass.local` / `teacher123`
- Student: `student@eclass.local` / `student123`

## Setup

1. Make sure PHP 8.2+ is available.
2. Run migrations and seed the database:
   `php artisan migrate:fresh --seed`
3. Start Laravel:
   `php artisan serve`
4. Open the app in your browser.

## Tests

Run:

`php artisan test`

## Notes

- The original frontend prototype is preserved in `legacy-prototype/`.
- The application is configured to use `database/database.sqlite` by default.