# E-Class Record System (Laravel)

This project converts the original static HTML/CSS/JavaScript prototype into a Laravel application with real authentication, database-backed records, and Blade-rendered teacher/student dashboards.

## Stack

- Laravel 10
- PHP 8.2+
- SQLite
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
- Database-seeded test data for quick functional testing

## Demo Accounts (Seeded)

All seeded users use the same password: `password123`

- Professor: `professor@eclass.local` (Prof. Lucia Mendoza)
- Student: `aira.santos@eclass.local` (Aira Mae Santos, Section A)
- Student: `john.rivera@eclass.local` (John Paul Rivera, Section B)

## Setup After Cloning

Run these commands from the project root:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create the SQLite database file (if it does not exist):

```bash
type nul > database/database.sqlite
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

## Optional Frontend Dev Command

```bash
npm install
npm run dev
```

## Tests

```bash
php artisan test
```

## Notes

- The original frontend prototype is preserved in `legacy-prototype/`.
- The app uses `database/database.sqlite` by default.
