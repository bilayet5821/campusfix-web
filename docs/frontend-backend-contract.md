# CampusFix frontend-backend contract

This document defines the production interfaces expected by the Blade frontend. Local `preview.*` routes are GET-only development aids and are not production substitutes.

## Required named routes

| Method | Name | Purpose |
|---|---|---|
| GET | `home` | Public landing page |
| GET | `login` | Login form |
| POST | `login.store` | Authenticate a user |
| POST | `logout` | End the authenticated session |
| GET | `student.dashboard` | Student dashboard |
| GET | `student.issues.index` | Current student's reports |
| GET | `student.issues.create` | New report form |
| POST | `student.issues.store` | Create a report |
| GET | `student.issues.show` | Current student's report details |
| GET | `admin.dashboard` | Admin / staff operations dashboard |
| GET | `admin.issues.index` | All authorized issue reports |
| GET | `admin.issues.show` | Admin / staff issue details |
| GET | `admin.issues.edit` | Admin / staff issue management form |
| PATCH | `admin.issues.update` | Update workflow fields and notes |
| GET | `admin.locations.index` | Buildings & rooms directory and creation forms |
| POST | `admin.buildings.store` | Add a new campus building |
| POST | `admin.locations.store` | Add a new room/area under a building |
| GET | `admin.users.index` | User accounts & feature access directory |
| GET | `admin.users.create` | Create new user account form (any role + feature permissions) |
| POST | `admin.users.store` | Store a new user account |
| GET | `admin.users.edit` | Edit user account & toggle feature access permissions |
| PATCH | `admin.users.update` | Update user account, active status, and feature permissions |

Public self-registration is disabled; only administrators with `manage_users` can create accounts and grant/revoke individual feature permissions (`view_student_portal`, `create_issues`, `view_admin_dashboard`, `manage_issues`, `manage_locations`, `manage_users`) for any user type (`student`, `teacher`, `staff`, `admin`). Student show routes enforce ownership server-side.

## Form fields

### Login: `POST login.store`

- `email` — required email
- `password` — required string
- `remember` — optional boolean

### Registration: `POST register.store`

- `name` — required string
- `student_id` — required, unique student identifier
- `email` — required, unique email
- `department_id` — required existing department ID
- `phone` — nullable phone string
- `password` — required confirmed password
- `password_confirmation` — required confirmation
- `terms` — accepted boolean

### Create report: `POST student.issues.store`

The form uses `multipart/form-data`.

- `title` — required, maximum 120 characters
- `category_id` — required existing category ID
- `building_id` — required existing active building ID
- `location_id` — required existing active room/location ID belonging to `building_id`
- `description` — required, 20–1500 characters
- `priority` — nullable enum; the backend may override student-selected priority
- `attachments[]` — optional image files
- `accuracy_confirmed` — accepted boolean

Recommended attachment validation: maximum five files; JPG, PNG, or WebP; maximum 5 MB per file. Backend validation is authoritative.

### Admin update: `PATCH admin.issues.update`

- `status` — required status enum
- `priority` — required priority enum
- `assigned_to` — nullable existing authorized staff/admin user ID
- `admin_note` — nullable internal-only string, maximum 2000 characters; never expose it to students or public responses
- `resolution_note` — nullable string, maximum 2000 characters

Changing status should create a status-history record in the same transaction. `admin_note` is strictly internal and must never be copied into student-visible status-history notes. `resolution_note` is the student-visible resolution update.

## View variables

| View | Required variables |
|---|---|
| `auth.register` | `$departments` |
| `student.dashboard` | `$student`, `$stats`, `$recentIssues`, `$recentActivity` |
| `student.issues.create` | `$student`, `$categories`, `$buildings`, `$locations` |
| `student.issues.index` | `$student`, `$issues`, `$filters`, `$categories` |
| `student.issues.show` | `$student`, `$issue`, `$statusHistory` |
| `admin.dashboard` | `$admin`, `$stats`, `$recentIssues`, `$statusDistribution`, `$priorityDistribution`, `$categoryDistribution`, `$recentActivity` |
| `admin.issues.index` | `$admin`, `$issues`, `$filters`, `$categories` |
| `admin.issues.show` | `$admin`, `$issue`, `$statusHistory` |
| `admin.issues.edit` | `$admin`, `$issue`, `$assignees` |

`$issues` should normally be a Laravel paginator. Collections are supported for local previews. Filtered paginators should retain the query string.

Dashboard `$stats` keys:

- Student: `total`, `pending_review`, `in_progress`, `resolved`
- Admin: `total`, `submitted`, `under_review`, `in_progress`, `resolved`, `closed_rejected`

Distribution items contain `label`, `count`, and `percentage`. Activity items contain `title`, `description`, and `created_at`.

## Model and relationship shape

Issue templates access:

- `id`, `reference_number`, `title`, `description`, `status`, `priority`
- `resolution_note`, `created_at`, `updated_at`
- `admin_note` on admin-only templates and responses; exclude it from every student-facing view model and serialized response
- `category.id`, `category.name`
- `location.id`, `location.name`
- `reporter.id`, `reporter.name`, `reporter.student_id`, `reporter.email`
- `assignee.id`, `assignee.name`, `assignee.email` (nullable)
- `attachments`
- `status_history` when `$statusHistory` is not passed separately

Controllers should eager-load `category`, `location`, `reporter`, `assignee`, and attachments. Detail controllers should also load ordered status history and each history entry's actor.

Attachment items use `original_name` (or `name`), `mime_type`, and a browser-accessible `url` or `public_url`. If files are private, supply an authorized download URL rather than exposing storage paths. Blade must not receive raw filesystem paths.

Status-history items use `status`, optional `title`, optional `note`, optional `actor.name`, and `created_at`. Return newest-first or oldest-first consistently; the current timeline renders the supplied order.

## Enums

Statuses:

- `submitted`
- `under_review`
- `in_progress`
- `resolved`
- `closed`
- `rejected`

Priorities:

- `low`
- `medium`
- `high`
- `urgent`

Roles expected by navigation and authorization are `student` and `admin`.

## Filters, redirects, and flash messages

Student issue filters: `search`, `status`, `category`.

Admin issue filters: `search`, `status`, `priority`, `category`, `date_from`, `date_to`.

Search should match at least `reference_number` and `title`. All filters should be validated, applied server-side, and preserved through pagination.

On successful mutations, redirect to an appropriate named route with a `success` flash message. On operational failure, use `error`. Standard Laravel validation errors are rendered automatically through `$errors` and `old()`.

After login, redirect students to `student.dashboard` and admins to `admin.dashboard`. After logout, redirect to `home` or `login`.
