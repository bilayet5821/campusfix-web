# CampusFix

**Smart Campus Issue Reporting and Resolution Management System**

CampusFix is a responsive Laravel web application for reporting, tracking, assigning, and resolving campus facility and technology issues. It was developed as the final project for **CSE 416 — Web Engineering Lab**.

The system gives students and teachers a focused reporter portal, while maintenance staff and administrators receive permission-controlled operational tools. CampusFix is an academic project and is not presented as an officially deployed or endorsed university service.

## Table of contents

- [Problem statement and objectives](#problem-statement-and-objectives)
- [Key features and workflows](#key-features-and-workflows)
- [Roles and feature permissions](#roles-and-feature-permissions)
- [Technology stack](#technology-stack)
- [System architecture](#system-architecture)
- [Main modules](#main-modules)
- [Database design](#database-design)
- [Prerequisites](#prerequisites)
- [Installation on Windows with XAMPP and MySQL](#installation-on-windows-with-xampp-and-mysql)
- [Optional SQLite setup](#optional-sqlite-setup)
- [Local development](#local-development)
- [Local-only demo data](#local-only-demo-data)
- [Security](#security)
- [Screenshots](#screenshots)
- [Automated tests](#automated-tests)
- [Collaboration and contributors](#collaboration-and-contributors)
- [Limitations and future improvements](#limitations-and-future-improvements)
- [Academic project information](#academic-project-information)

## Problem statement and objectives

Campus maintenance concerns are often reported through informal channels, making them difficult to classify, assign, track, and audit. Reporters may not know whether an issue has been reviewed, while operations teams can lack a consistent view of priorities and outstanding work.

CampusFix addresses that problem by providing one structured workflow with these objectives:

- make issue submission clear and accessible on desktop and mobile;
- capture consistent category, building, room, priority, description, and image evidence;
- let reporters follow their own issues and resolution updates;
- give authorized personnel searchable, filterable operational views;
- preserve a status history for accountability;
- separate internal administrative notes from reporter-visible resolution updates; and
- enforce access at both the route and record-ownership levels.

## Key features and workflows

### Reporter workflow

1. An administrator provisions and activates a Student or Teacher account.
2. The reporter signs in and is redirected to the reporter dashboard.
3. The reporter submits an issue with a category, building, room or area, description, optional priority, and up to five supporting images.
4. The reporter searches or filters their own reports and opens a detail page to view status, assignment, attachments, and public history updates.
5. Resolution notes remain visible to the reporter after operational work is completed.

### Operations workflow

1. Maintenance Staff or an Administrator opens the operations dashboard or issue list, subject to assigned permissions.
2. Issues can be searched and filtered by reference/title, status, priority, category, and date.
3. An authorized issue manager assigns an active administrator, maintenance staff member, or other user granted issue-management access; changes priority or status; and records internal or resolution notes.
4. Material workflow changes create an ordered status-history record inside the same database transaction.
5. Administrators with the relevant permissions can add buildings and rooms, provision accounts, deactivate users, and grant or revoke individual features.

### Issue lifecycle

| Type | Supported values |
|---|---|
| Status | `submitted`, `under_review`, `in_progress`, `resolved`, `closed`, `rejected` |
| Priority | `low`, `medium`, `high`, `urgent` |

## Roles and feature permissions

Roles provide sensible defaults, but authorization is ultimately based on the feature permissions stored for each user. An administrator with account-management access can grant or revoke individual features, including assigning a non-default combination when required.

| Role | Default access |
|---|---|
| Student | Reporter dashboard, own reports, and new issue submission |
| Teacher | Reporter dashboard, own reports, and new issue submission |
| Maintenance Staff | Operations dashboard and issue management |
| Administrator | Operations dashboard, issue management, location management, and account/permission management |

Implemented feature permissions:

| Permission | Capability |
|---|---|
| `view_student_portal` | Open the reporter dashboard and track personal reports |
| `create_issues` | Submit new reports with supporting images |
| `view_admin_dashboard` | View campus-wide statistics, distributions, and recent activity |
| `manage_issues` | View all reports, assign staff, and update status, priority, and notes |
| `manage_locations` | Add buildings and rooms used by the reporting form |
| `manage_users` | Create/edit accounts, activate/deactivate users, and manage permissions |

Public self-registration is intentionally disabled. Accounts are created through the protected account-management module.

## Technology stack

| Layer | Technologies |
|---|---|
| Backend | PHP 8.2+ (PHP 8.3 recommended), Laravel 12, Eloquent ORM, Form Requests, middleware, policies, database transactions |
| Frontend | Blade templates and components, Bootstrap 5.3, CSS, vanilla JavaScript |
| Assets | Vite 7, npm, Popper.js |
| Database | MySQL/MariaDB for the primary setup; SQLite supported for local development and tests |
| Storage | Laravel private local filesystem with authorized attachment delivery |
| Testing | PHPUnit 11 through Laravel's test runner |

No client-side application framework such as React, Vue, or Livewire is required.

## System architecture

CampusFix is a server-rendered Laravel MVC application:

```text
Browser
  -> Laravel web routes
  -> session authentication
  -> active-account and feature-permission middleware
  -> Form Request authorization and validation
  -> controllers and database transactions
  -> Eloquent models, policies, MySQL/SQLite, private file storage
  -> Blade layouts/components + Vite-built Bootstrap/CSS/JavaScript
```

Important architectural boundaries:

- named web routes are grouped by authentication and feature permission;
- policies enforce reporter ownership for issues and attachments;
- controllers eager-load relationships and paginate list screens;
- Form Requests normalize and validate incoming data;
- issue updates and status-history creation are transactional;
- attachments remain private and are streamed through an authorized controller;
- local preview routes are loaded only when `APP_ENV=local`.

## Main modules

| Module | Implemented functionality |
|---|---|
| Public website | Responsive landing page, feature overview, workflow explanation, and login entry point |
| Authentication | Login, remember-me support, throttling, active-account checks, role/permission-aware redirect, and secure logout |
| Reporter dashboard | Personal report statistics, recent reports, activity, tips, and empty states |
| Issue reporting | Guided validated form, building/room selection, category and priority, description, accuracy confirmation, and image previews |
| My reports | Ownership-scoped search, status/category filters, pagination, responsive cards/table, and issue details |
| Operations dashboard | Campus-wide status totals plus status, priority, and category distributions and recent activity |
| Issue management | Search and filters, assignment, priority/status updates, internal admin notes, public resolution notes, and full history |
| Location management | Protected creation of buildings and rooms used by reporter forms |
| Account management | Protected user listing, search/filtering, account creation/editing, roles, activation, and feature-level access |
| Frontend previews | GET-only sample screens under `/preview/*`, available exclusively in the local environment |

## Database design

### Core entities and relationships

| Entity | Purpose and principal relationships |
|---|---|
| `departments` | Organizes users; one department has many users |
| `users` | Authenticated account with role, permission list, active state, optional department, reported issues, and assigned issues |
| `categories` | Classifies issues; one category has many issues |
| `buildings` | Contains campus rooms/areas through a one-to-many relationship with locations |
| `locations` | Represents a room or area and belongs to a building; one location has many issues |
| `issues` | Belongs to a reporter, category, and location; optionally belongs to an assignee; has attachments and status histories |
| `issue_attachments` | Belongs to an issue and uploader; stores private disk metadata rather than a public file path |
| `issue_status_histories` | Belongs to an issue and optional actor; records old/new status, title, note, and timestamp |

Laravel also supplies the `sessions`, cache, jobs, failed jobs, and password-reset-token tables. Issues use soft deletion at the model and schema level. Foreign-key behavior protects referenced categories, buildings, and locations while cleaning up issue-owned history and attachment records when appropriate.

## Prerequisites

- Git
- Composer 2
- PHP 8.2 or newer with `fileinfo`, `mbstring`, `openssl`, `pdo`, and the selected database extension enabled
- PHP 8.3 recommended for the project environment
- Node.js compatible with Vite 7 (Node.js 20.19+ or 22.12+ recommended) and npm
- XAMPP with a compatible PHP/MySQL or MariaDB version for the Windows setup

Check the command-line tools before installation:

```powershell
php -v
composer --version
node --version
npm --version
```

If Windows uses XAMPP's PHP, ensure `C:\xampp\php` is on `PATH`, or run PHP using its full executable path.

## Installation on Windows with XAMPP and MySQL

### 1. Obtain the project and install dependencies

```powershell
git clone <repository-url>
cd campusfix-web
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
```

Never commit the generated `.env` file or share real database credentials.

### 2. Create a database safely

Start **MySQL** from the XAMPP Control Panel. Apache is optional when using `php artisan serve`.

Create an empty database in phpMyAdmin or the MySQL client:

```sql
CREATE DATABASE campusfix
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Do not drop an existing database. If `campusfix` already contains important data, use another database name or take a backup before continuing.

### 3. Configure `.env`

Set the local connection values to match your XAMPP installation:

```dotenv
APP_NAME=CampusFix
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=campusfix
DB_USERNAME=your_local_mysql_user
DB_PASSWORD=your_local_mysql_password
```

XAMPP installations often use a local `root` account with an empty password, but use the credentials actually configured on your machine. Do not copy private credentials into documentation or source control.

After editing `.env`, clear cached configuration:

```powershell
php artisan config:clear
```

### 4. Create tables and optional local demo data

For a new local database with the provided reference and demo data:

```powershell
php artisan migrate --seed
```

`migrate --seed` applies pending migrations without deleting existing tables. Do not use `migrate:fresh` as a routine setup command because it drops all tables.

To initialize only the reference data and omit demo accounts/issues, run migrations and the three base seeders explicitly:

```powershell
php artisan migrate
php artisan db:seed --class=DepartmentSeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=LocationSeeder
```

### 5. Build and run

```powershell
npm run build
php artisan serve
```

Open <http://127.0.0.1:8000>. If serving through Apache instead, configure the web root to the Laravel `public` directory—never expose the repository root.

> CampusFix accepts at most five JPG, PNG, or WebP images per report, with a 5 MB limit per image. For multi-image uploads, XAMPP's `php.ini` may require larger `upload_max_filesize` and `post_max_size` values. Restart Apache or the PHP process after changing `php.ini`.

## Optional SQLite setup

SQLite is convenient for local review and automated tests. Ensure PHP's `pdo_sqlite` extension is enabled.

```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate

if (!(Test-Path database/database.sqlite)) {
    New-Item -ItemType File -Path database/database.sqlite
}
```

Use this database setting in `.env` and remove or comment out MySQL-specific values:

```dotenv
APP_ENV=local
DB_CONNECTION=sqlite
```

Then initialize and run the application:

```powershell
php artisan config:clear
php artisan migrate --seed
npm run build
php artisan serve
```

## Local development

Run the Laravel server and Vite watcher in separate terminals:

```powershell
php artisan serve
```

```powershell
npm run dev
```

Alternatively, the repository's Composer script starts the server, queue listener, log viewer, and Vite together:

```powershell
composer run dev
```

Useful maintenance and inspection commands:

```powershell
php artisan route:list --except-vendor
php artisan migrate:status
php artisan config:clear
php artisan view:clear
npm run build
```

No public storage link is required for issue images; files are intentionally served from private storage through the authorized attachment route.

## Local-only demo data

Demo seeding is intentionally restricted to the `local` and `testing` environments. In production, `DatabaseSeeder` loads only reference departments, categories, buildings, and locations. Direct execution of either demo seeder is also guarded and cannot create or reset demo accounts in production.

With `APP_ENV=local`, load or refresh all reference and demo data using:

```powershell
php artisan db:seed
```

The following credentials are **known demonstration credentials for local/testing use only**:

| Role | Demo account | Email | Password |
|---|---|---|---|
| Administrator | Nadia Karim | `admin@campusfix.edu` | `Admin@12345` |
| Teacher | Dr. Kamrul Islam | `teacher@campusfix.edu` | `Teacher@12345` |
| Maintenance Staff | Mahmudul Hasan (Facilities) | `facilities@campusfix.edu` | `Admin@12345` |
| Maintenance Staff | Farhana Akter (IT Support) | `itsupport@campusfix.edu` | `Admin@12345` |
| Student | Ariana Rahman | `student@campusfix.edu` | `Student@12345` |
| Student | Tanvir Ahmed | `student2@campusfix.edu` | `Student@12345` |

> **Never use these accounts or passwords in production.** Do not change `APP_ENV` merely to force demo data into a shared or production database. Re-running the local demo user seeder intentionally restores these known demo passwords.

Local-only, read-only frontend previews are also available under `/preview/*`, including login, reporter, issue, and administrator screens. They provide sample presentation data only and do not authenticate users or accept writes.

## Security

Implemented safeguards include:

- session-based authentication with session regeneration after login;
- login throttling after repeated failures;
- secure logout with session invalidation and CSRF-token regeneration;
- CSRF protection on state-changing forms;
- active-account enforcement on login and protected requests;
- feature-permission middleware and issue/attachment ownership policies;
- server-side Form Request authorization and validation;
- password hashing through Laravel's hashed cast;
- validated assignment to active, authorized staff or administrators;
- private attachment storage and authorized streaming with `nosniff` and private cache headers;
- image count, size, extension, MIME type, and decoded-image validation;
- Eloquent parameter binding and escaped Blade output;
- database transactions for issue updates and history creation;
- internal-only `admin_note` handling, with reporter-visible `resolution_note` kept separate;
- hidden sensitive model attributes and guarded demo seeders; and
- automated regression coverage for ownership, unauthorized access, upload spoofing, XSS, filtering, pagination, and internal-note leakage.

Security-related limitations:

- password recovery, email verification enforcement, and two-factor authentication are not implemented;
- attachments use the local filesystem rather than external object storage or malware scanning;
- operational history from older free-text records may be conservatively redacted from reporters when it cannot be safely distinguished from internal content; and
- production deployment, TLS, backups, monitoring, mail delivery, and server hardening remain deployment responsibilities outside this repository.

## Screenshots

Replace these placeholders with final screenshots before presentation if desired. No external or fabricated screenshots are included in the repository documentation.

| Screen | Placeholder |
|---|---|
| Public landing page | _Screenshot placeholder — desktop and mobile landing page_ |
| Login | _Screenshot placeholder — authentication screen_ |
| Reporter dashboard | _Screenshot placeholder — statistics, reports, and activity_ |
| New issue report | _Screenshot placeholder — guided form and attachment preview_ |
| Issue details | _Screenshot placeholder — metadata, attachments, and status timeline_ |
| Operations dashboard | _Screenshot placeholder — totals and distributions_ |
| Issue management | _Screenshot placeholder — filters and update workflow_ |
| Account permissions | _Screenshot placeholder — user role and feature access editor_ |

## Automated tests

Run the complete suite with:

```powershell
php artisan test
```

Additional submission checks:

```powershell
composer validate
npm run build
php artisan view:cache
php artisan route:list --except-vendor
```

Verified final result:

```text
37 tests passed (360 assertions)
```

The feature suite covers authentication and redirects, inactive accounts, login throttling, public-registration blocking, feature permissions, account management, reporter ownership, private attachments, issue submission, upload restrictions and spoof detection, search/filter/pagination, location validation, admin workflows, transactional status history, internal-note protection, seeder safety, XSS escaping, and local-only frontend previews.

Tests use Laravel's isolated test environment and `RefreshDatabase`; they must not be pointed at a real production database.

## Collaboration and contributors

| Contributor | Responsibility |
|---|---|
| **Md. Bilayet Hossain** | Frontend & Integration |
| **Mobin Mohtadi Tasin** | Backend |

Development was organized across frontend, backend, and integration work, with Git history and a pull-request merge into `main` preserving the collaboration trail. Future changes should continue through focused branches and reviewed pull requests rather than direct unreviewed changes to `main`.

## Limitations and future improvements

Current limitations and suitable next steps include:

- add password reset, verified-email workflows, and optional two-factor authentication;
- add email or in-app notifications for assignments and status changes;
- add configurable service-level targets, escalation rules, and richer audit reporting;
- add category/department editing and broader location lifecycle management;
- move attachments to managed object storage with antivirus scanning for production use;
- provide CSV/PDF exports and longer-term analytics;
- add an API and mobile client if required;
- add browser-based end-to-end and accessibility automation; and
- prepare environment-specific deployment, backup, observability, and disaster-recovery procedures.

## Academic project information

| Item | Details |
|---|---|
| Project | CampusFix — Smart Campus Issue Reporting and Resolution Management System |
| Course | CSE 416 — Web Engineering Lab |
| Project type | Final academic web engineering project |
| Application style | Full-stack, server-rendered Laravel application |
| Status | Completed and merged into `main` |

This repository demonstrates full-stack web engineering practices for an academic submission. It does not claim a production deployment, institutional adoption, or official university endorsement.
