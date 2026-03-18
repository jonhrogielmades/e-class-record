# E-Class Record System

## System Overview
The E-Class Record System is a capstone-ready frontend prototype for managing academic records with two roles: `teacher` and `student`. It uses HTML, CSS, and vanilla JavaScript with `localStorage` to simulate a lightweight class-record workflow without a backend.

The project follows this flow:
- Login
- Dashboard
- Class List
- Students
- Grading
- Settings

## Core Features
- Role-based login and registration for teacher and student accounts
- Teacher dashboard with section overview, attendance activity, and grading summaries
- Student dashboard with personal class details, attendance rate, and grade trend
- Class List page for `Section A` and `Section B`
- Student Records page with:
  - student profile cards
  - attendance table
  - teacher attendance entry form
- Grading page with:
  - teacher grade input form
  - section leaderboard
  - student grade history
  - summary charts
- Settings page with profile editing and log out actions
- Dark and light theme toggle
- White, black, and sky-blue visual palette built on the existing glass-admin theme

## Demo Accounts
- Teacher: `teacher@eclass.local` / `teacher123`
- Student: `student@eclass.local` / `student123`

## Technologies Used
- HTML5
- CSS3
- Vanilla JavaScript
- `localStorage`
- Canvas API

## File Structure
```text
/
|-- index.html
|-- login.html
|-- register.html
|-- dashboard.html
|-- class-list.html
|-- students.html
|-- grading.html
|-- settings.html
|-- README.md
|-- templatemo-glass-admin-style.css
|-- assets/
|   |-- css/
|   |   |-- style.css
|   |-- js/
|       |-- utils.js
|       |-- data.js
|       |-- app-store.js
|       |-- app-ui.js
|       |-- auth.js
|       |-- dashboard.js
|       |-- class-list.js
|       |-- students.js
|       |-- grading.js
|       |-- settings.js
```

## How to Run
1. Open `index.html` in a modern browser.
2. Sign in with one of the demo accounts or register a new teacher or student account.
3. Navigate through the role-based pages.
4. Data changes are saved automatically in the browser through `localStorage`.

## Stored Data
The system stores:
- registered users
- current logged-in session
- seeded and user-updated sections
- student profiles
- attendance records
- grades
- theme preference

## Future Improvements
- Add backend authentication and persistent database storage
- Export attendance and grading reports to PDF or CSV
- Add section-level filtering for multiple teachers
- Add printable report cards and attendance sheets
- Add subject-level grading categories and quarterly breakdowns
