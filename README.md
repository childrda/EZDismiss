## CarLineManager

CarLineManager delivers multi-school car rider dismissal management with per-tenant isolation. Each school shares the same codebase and database while maintaining scoped views, queues, logging, and RFID automation.

### Feature Highlights

- Multi-school tenancy with district, school admin, teacher, and staff roles
- Color-coded live queue, classroom dashboard, gym display, and mobile drag-and-drop entry
- RFID reader support with Sanctum-secured API endpoints (mode A/B)
- Queue broadcasting over Laravel Reverb (`school.{school_id}` channels)
- CSV imports (students, parents, teachers, homerooms, authorized pickups) with preview + summary
- Tenant-scoped activity logging for RFID scans, manual inserts, queue actions, imports, and more
- Admin panels for school management; district portal for cross-school oversight

### Tech Stack

- Laravel 11, PHP 8.2, MySQL
- Laravel Breeze style authentication (custom login provided; breeze install optional)
- Tailwind CSS, Blade, Alpine.js for UI
- Laravel Sanctum for API auth tokens
- Laravel Reverb for realtime queue updates

## Local Setup

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run build # or npm run dev for Vite dev server
```

### Real-time Updates (Laravel Reverb)

1. Configure the broadcast driver and credentials in `.env`:

```
BROADCAST_DRIVER=reverb
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_APP_ID=carline
REVERB_APP_KEY=local
REVERB_APP_SECRET=secret
VITE_REVERB_HOST=${REVERB_HOST}
VITE_REVERB_PORT=${REVERB_PORT}
VITE_REVERB_SCHEME=${REVERB_SCHEME}
VITE_REVERB_APP_KEY=${REVERB_APP_KEY}
```

2. Start the Reverb server:

```bash
php artisan reverb:start
```

3. Run the Laravel queue worker if you broadcast via queue:

```bash
php artisan queue:listen
```

### User Roles (seeded)

| Role            | Email                          | Password |
|-----------------|--------------------------------|----------|
| District Admin  | `district.admin@example.com`   | `password` |
| School Admin(s) | `admin1@example.com`, `admin2@example.com` | `password` |
| Staff / Teacher | `staff1@example.com`, `teacher11@example.com`, etc. | `password` |

Seed data creates two schools (2 lanes each), homerooms, 10 students, RFID-enabled drivers, authorized pickups, and base activity logs.

## RFID API

All endpoints require a Sanctum token. Generate personal access tokens for RFID devices or service accounts.

### Mode A: Reader-based

`POST /api/rfid/checkin`

```json
{
  "reader_id": 2,
  "tag_uid": "ABC123"
}
```

- Looks up reader → lane + school
- Finds driver by `tag_uid`
- Creates checkin (`method=rfid`) with next position
- Broadcasts `CheckinCreated` + `QueueUpdated`
- Logs `rfid_scan`

### Mode B: Lane override

`POST /api/rfid/lane/{lane}/checkin`

```json
{
  "tag_uid": "ABC123",
  "school_key": "optional-api-key"
}
```

- `school_key` matches `schools.api_key` (fallback to token’s school)
- Remaining flow identical to Mode A

Use Postman or curl with `Authorization: Bearer {token}` headers.

## CSV Imports

Navigate to `Admin → Import`. Upload CSV to preview actions. After confirming preview, upload the same file again to execute.

Supported headers / keys:

- `students.csv`: `powerschool_id`, `name`, `grade`, `homeroom`, `parent_ids`
- `parents.csv`: `parent_id`, `name`, `email`, `phone`, `vehicle_desc`, `tag_uid`, `student_ids`
- `teachers.csv`: `name`, `email`
- `homerooms.csv`: `name`, `teacher_name`
- `authorized_pickups.csv`: `parent_id`, `student_powerschool_id`, `relationship`, `expires_at`

Preview shows create/update/skip counts and highlights errors. Successful imports log a `csv_import` activity.

## Testing Checklist

- `php artisan test` (add tests as flows are expanded)
- Validate RFIDs with sample payloads (ensure reader/tag exist)
- Confirm Reverb broadcasting: open `/queue`, perform manual insert, verify real-time updates

## Scripts

- Migrate & seed: `php artisan migrate --seed`
- Reverb server: `php artisan reverb:start`
- Queue worker (if needed): `php artisan queue:listen`
- Vite dev server: `npm run dev`

## Roadmap

- PowerSchool integration workflow (placeholder UI ready)
- School disable / archival flag
- Comprehensive automated test suite

