# CampusFix — Smart Campus Issue Reporting & Resolution System

CampusFix is a full-stack Laravel 12 web application for reporting, tracking, triaging, and resolving campus maintenance and facility issues (CSE 416 — Web Engineering Lab).

## Tech Stack
- **Backend:** PHP 8.2+ / Laravel 12 (Blade, Eloquent ORM, Form Requests, Policies, Role Middleware, DB Transactions)
- **Frontend:** HTML5, CSS3, Bootstrap 5, Vite, Laravel Blade Components
- **Database:** MySQL (XAMPP / MariaDB for demo & production) or SQLite (zero-config local development & testing)

---

## Quick Local Setup (SQLite — Fastest)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create an empty SQLite database file (`database/database.sqlite`), then migrate, seed, build assets, and start the server:

```bash
# PowerShell: New-Item -ItemType File -Path database/database.sqlite -Force
# Bash:       touch database/database.sqlite

php artisan migrate --seed
npm run build
php artisan serve
```

Visit `http://127.0.0.1:8000`.

---

## MySQL Setup (XAMPP — Lab Demo)

1. Start **Apache** and **MySQL** in XAMPP Control Panel.
2. Create a database named `campusfix` in phpMyAdmin (`http://localhost/phpmyadmin`).
3. Update `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=campusfix
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. Run migrations and demo seeders:
   ```bash
   php artisan migrate:fresh --seed
   npm run build
   php artisan serve
   ```

> **Note on Image Uploads:** CampusFix allows up to 5 images (JPG, PNG, WebP) up to 5 MB each. If PHP's default `upload_max_filesize` (2M) or `post_max_size` (8M) is too small for multi-image uploads, increase them in `php.ini` (e.g. `upload_max_filesize = 8M`, `post_max_size = 32M`).

---

## Pre-Seeded Demo Accounts

> **Account Provisioning & Feature Access Control:** Public self-registration is disabled. Only administrators with the **Create Accounts & Manage Feature Access** permission (`/admin/users`) can create new accounts (`Student`, `Teacher`, `Maintenance Staff`, or `Administrator`) and grant or revoke specific features for any user.

| Role | Name | Email | Password |
|---|---|---|---|
| **Administrator** (All admin features) | Nadia Karim | `admin@campusfix.edu` | `Admin@12345` |
| **Teacher** (Reporter Portal & Issues) | Dr. Kamrul Islam | `teacher@campusfix.edu` | `Teacher@12345` |
| **Maintenance Staff** (Dashboard & Issues) | Mahmudul Hasan (Facilities) | `facilities@campusfix.edu` | `Admin@12345` |
| **Maintenance Staff** (Dashboard & Issues) | Farhana Akter (IT Support) | `itsupport@campusfix.edu` | `Admin@12345` |
| **Student** (`221-15-5001`) | Ariana Rahman | `student@campusfix.edu` | `Student@12345` |
| **Student** (`221-15-5002`) | Tanvir Ahmed | `student2@campusfix.edu` | `Student@12345` |

---

## Running Automated Tests

```bash
php artisan test
```

The test suite verifies authentication, role enforcement, issue ownership policies, private attachment authorization, file upload validation (type, size, count, spoofed extensions), XSS escaping, filtering, pagination, transactional status-history logging, and seeder idempotency.
