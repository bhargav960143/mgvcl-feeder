# MGVCL Feeder Power Position — Project Plan

**Client:** MGVCL (Madhya Gujarat Vij Company Limited)  
**Project Type:** Internal Web Application  
**Stack:** Laravel 11 + Blade + MySQL  
**Goal:** Replace manual chain-of-command status updates with real-time, role-based feeder status management system  
**Date:** 2026-06-04  

---

## 1. Problem Statement

Current flow:
```
Field Staff / Substation → (call) → Sub Division Manager → (call) → Division Manager → manual CSV update
```

Pain points:
- 3-hop delay before anyone sees the truth
- No audit trail — no record of who changed what, when
- Manual CSV = stale data, human error, no concurrent updates
- Senior MGVCL staff has no real-time visibility
- No outage history for analysis

Target flow:
```
Field Staff / Substation Staff → direct web update → EVERYONE sees instantly
```

---

## 2. Data Inventory (from CSV)

**390 feeders** across hierarchy:

```
Division (3)
  └─ Sub Division (≈15)
       └─ Substation / SS Name (≈30)
            └─ Feeder (390 total)
                  └─ Status: fully_on | partially_on | fully_off
```

**Divisions:**
- Lalbagh
- Vishwamitri-East
- Vishwamitri-West

**Feeder Categories (CAT):**
| Code  | Meaning                  |
|-------|--------------------------|
| URBAN | Urban residential/commercial |
| GIDC  | Gujarat Industrial Dev Corp |
| HTEX  | High Tension Extra        |
| EHT   | Extra High Tension        |
| SST   | Self-sufficient Transformer|
| IND   | Industrial                |

**CSV Columns:**
`SR NO, DIVISION, SUB DIVISION, SS NAME, FEEDER, CAT, TND CODE, TOTAL CONSUMER, TOTAL TC, FULLY ON, PARTIALLY ON, FULLY OFF`

---

## 3. System Architecture

```
┌─────────────────────────────────────────────────┐
│                   Browser (Blade)               │
│  Dashboard | Feeder List | Status Update Form   │
└────────────────────┬────────────────────────────┘
                     │ HTTP / AJAX polling (30s)
┌────────────────────▼────────────────────────────┐
│              Laravel 11 Application             │
│  Routes → Middleware → Controller → Service     │
│  Spatie Permission (RBAC) | Form Request Valid. │
└──────────┬──────────────────────┬───────────────┘
           │                      │
    ┌──────▼──────┐      ┌────────▼────────┐
    │   MySQL DB  │      │  Laravel Queue  │
    │  (core data)│      │ (notifications) │
    └─────────────┘      └─────────────────┘
```

**No external services required for Phase 1.**  
Phase 2 option: add Soketi (self-hosted WebSocket) for true push updates.

---

## 4. Database Schema

### 4.1 `divisions`
```sql
CREATE TABLE divisions (
    id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 4.2 `sub_divisions`
```sql
CREATE TABLE sub_divisions (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    division_id BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_subdivision (division_id, name)
);
```

### 4.3 `substations`
```sql
CREATE TABLE substations (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sub_division_id BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(150) NOT NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (sub_division_id) REFERENCES sub_divisions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_substation (sub_division_id, name)
);
```

### 4.4 `feeders`
```sql
CREATE TABLE feeders (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    substation_id  BIGINT UNSIGNED NOT NULL,
    name           VARCHAR(150) NOT NULL,
    tnd_code       VARCHAR(20) NOT NULL UNIQUE,
    category       ENUM('URBAN','GIDC','HTEX','EHT','SST','IND') NOT NULL,
    total_consumer INT UNSIGNED DEFAULT 0,
    total_tc       INT UNSIGNED DEFAULT 0,
    current_status ENUM('fully_on','partially_on','fully_off') NOT NULL DEFAULT 'fully_on',
    last_updated_by BIGINT UNSIGNED NULL,
    last_updated_at TIMESTAMP NULL,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL,
    FOREIGN KEY (substation_id) REFERENCES substations(id) ON DELETE CASCADE,
    FOREIGN KEY (last_updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (current_status),
    INDEX idx_category (category)
);
```

### 4.5 `feeder_status_logs` (audit trail — never delete)
```sql
CREATE TABLE feeder_status_logs (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    feeder_id  BIGINT UNSIGNED NOT NULL,
    old_status ENUM('fully_on','partially_on','fully_off') NULL,
    new_status ENUM('fully_on','partially_on','fully_off') NOT NULL,
    remarks    TEXT NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (feeder_id) REFERENCES feeders(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_feeder_created (feeder_id, created_at),
    INDEX idx_created (created_at)
);
```

### 4.6 `users` (Laravel default + extensions)
```sql
-- Add to default users table migration:
ALTER TABLE users ADD COLUMN jurisdiction_type ENUM('division','sub_division','substation','global') NOT NULL DEFAULT 'global';
ALTER TABLE users ADD COLUMN jurisdiction_id   BIGINT UNSIGNED NULL;
ALTER TABLE users ADD COLUMN employee_id       VARCHAR(50) NULL UNIQUE;
ALTER TABLE users ADD COLUMN phone             VARCHAR(15) NULL;
-- jurisdiction_id points to divisions.id OR sub_divisions.id OR substations.id based on jurisdiction_type
```

### 4.7 `notifications` (Laravel built-in table)
```bash
php artisan notifications:table
```
Used for email/SMS alerts when feeder goes to `fully_off`.

---

## 5. Eloquent Relationships

```
Division       hasMany SubDivision
SubDivision    belongsTo Division, hasMany Substation
Substation     belongsTo SubDivision, hasMany Feeder
Feeder         belongsTo Substation, hasMany FeederStatusLog, belongsTo User (last_updated_by)
FeederStatusLog belongsTo Feeder, belongsTo User (updated_by)
User           hasMany FeederStatusLog
```

---

## 6. System Roles & Permissions

Using **Spatie Laravel Permission** package.

### 6.1 Role Definitions

| Role                  | Jurisdiction Scope        | Description |
|-----------------------|---------------------------|-------------|
| `super_admin`         | All data, all divisions   | MGVCL senior staff, full access |
| `division_manager`    | One division              | Sees all feeders in their division |
| `sub_division_manager`| One sub division          | Sees feeders in their sub division |
| `substation_manager`  | One or more substations   | Can update feeder status |
| `field_staff`         | One or more substations   | Can update feeder status |

### 6.2 Permission Matrix

| Permission                  | super_admin | division_mgr | sub_div_mgr | substation_mgr | field_staff |
|-----------------------------|:-----------:|:------------:|:-----------:|:--------------:|:-----------:|
| `view-all-divisions`        | ✅          | ❌           | ❌          | ❌             | ❌          |
| `view-division`             | ✅          | ✅           | ✅          | ✅             | ✅          |
| `view-sub-division`         | ✅          | ✅           | ✅          | ✅             | ✅          |
| `view-substation`           | ✅          | ✅           | ✅          | ✅             | ✅          |
| `view-feeder`               | ✅          | ✅           | ✅          | ✅             | ✅          |
| `update-feeder-status`      | ✅          | ❌           | ❌          | ✅             | ✅          |
| `view-status-logs`          | ✅          | ✅           | ✅          | ✅             | ❌          |
| `export-report`             | ✅          | ✅           | ✅          | ❌             | ❌          |
| `manage-users`              | ✅          | ❌           | ❌          | ❌             | ❌          |
| `import-csv`                | ✅          | ❌           | ❌          | ❌             | ❌          |

### 6.3 Data Scoping Rules

Every query is scoped to the user's jurisdiction — enforced in middleware, not just views:

```php
// Example: SubDivisionManager can ONLY see their sub_division's data
// Enforced via Global Scope or Policy — not via if/else in controller

// User::jurisdiction_type = 'sub_division'
// User::jurisdiction_id   = sub_divisions.id
// → All feeder queries auto-filtered via FeederScope
```

---

## 7. Laravel Code Structure

```
app/
├── Console/
│   └── Commands/
│       └── ImportFeederCsv.php          # php artisan feeder:import
│
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── LoginController.php
│   │   ├── DashboardController.php      # role-aware dashboard
│   │   ├── FeederController.php         # list, show, status update
│   │   ├── FeederStatusLogController.php # audit log view
│   │   ├── ReportController.php         # export CSV/PDF
│   │   └── Admin/
│   │       ├── UserController.php       # manage users + assign jurisdiction
│   │       └── DivisionController.php   # manage master data
│   │
│   ├── Middleware/
│   │   └── ScopeToJurisdiction.php      # auto-scope queries per user role
│   │
│   └── Requests/
│       ├── UpdateFeederStatusRequest.php # validates status + remarks
│       └── ImportCsvRequest.php
│
├── Models/
│   ├── Division.php
│   ├── SubDivision.php
│   ├── Substation.php
│   ├── Feeder.php
│   ├── FeederStatusLog.php
│   └── User.php
│
├── Policies/
│   └── FeederPolicy.php                 # can user update THIS feeder?
│
├── Services/
│   └── FeederStatusService.php          # business logic: update + log + notify
│
├── Notifications/
│   └── FeederOfflineNotification.php    # email/SMS when fully_off
│
└── Imports/
    └── FeederCsvImport.php              # CSV row parser (used by artisan cmd)

database/
├── migrations/
│   ├── create_divisions_table.php
│   ├── create_sub_divisions_table.php
│   ├── create_substations_table.php
│   ├── create_feeders_table.php
│   └── create_feeder_status_logs_table.php
│
└── seeders/
    ├── DatabaseSeeder.php
    ├── RolePermissionSeeder.php         # seeds all roles + permissions
    └── FeederDataSeeder.php             # seeds from CSV (dev/staging only)

resources/
└── views/
    ├── layouts/
    │   └── app.blade.php                # nav + sidebar (role-aware)
    ├── auth/
    │   └── login.blade.php
    ├── dashboard/
    │   └── index.blade.php              # summary cards + status counts
    ├── feeders/
    │   ├── index.blade.php              # filterable feeder table
    │   ├── show.blade.php               # single feeder detail + log
    │   └── partials/
    │       ├── status-badge.blade.php   # reusable status indicator
    │       └── status-form.blade.php    # update form (shown only to authorized)
    ├── reports/
    │   └── index.blade.php
    └── admin/
        ├── users/
        │   ├── index.blade.php
        │   ├── create.blade.php
        │   └── edit.blade.php
        └── divisions/
            └── index.blade.php

routes/
├── web.php                              # all app routes
└── api.php                             # AJAX polling endpoints (status refresh)
```

---

## 8. Key Routes

```php
// routes/web.php

// Auth
Route::get('/login', [LoginController::class, 'showForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'scope.jurisdiction'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Feeders
    Route::get('/feeders', [FeederController::class, 'index'])->name('feeders.index');
    Route::get('/feeders/{feeder}', [FeederController::class, 'show'])->name('feeders.show');
    Route::patch('/feeders/{feeder}/status', [FeederController::class, 'updateStatus'])
         ->name('feeders.updateStatus')
         ->middleware('can:update-feeder-status');

    // Audit logs
    Route::get('/feeders/{feeder}/logs', [FeederStatusLogController::class, 'index'])
         ->name('feeders.logs')
         ->middleware('can:view-status-logs');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])
         ->name('reports.index')
         ->middleware('can:export-report');
    Route::get('/reports/export', [ReportController::class, 'export'])
         ->name('reports.export')
         ->middleware('can:export-report');

    // Admin
    Route::middleware('can:manage-users')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('divisions', DivisionController::class)->only(['index', 'show']);
    });
});

// AJAX — status summary for dashboard polling
Route::get('/api/feeders/summary', [FeederController::class, 'summary'])
     ->middleware(['auth', 'throttle:60,1'])
     ->name('api.feeders.summary');
```

---

## 9. Core Service: FeederStatusService

```php
// app/Services/FeederStatusService.php

class FeederStatusService
{
    public function updateStatus(Feeder $feeder, string $newStatus, string $remarks, User $updatedBy): void
    {
        $oldStatus = $feeder->current_status;

        DB::transaction(function () use ($feeder, $newStatus, $oldStatus, $remarks, $updatedBy) {
            $feeder->update([
                'current_status'  => $newStatus,
                'last_updated_by' => $updatedBy->id,
                'last_updated_at' => now(),
            ]);

            FeederStatusLog::create([
                'feeder_id'  => $feeder->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'remarks'    => $remarks,
                'updated_by' => $updatedBy->id,
            ]);
        });

        // Notify managers if feeder goes fully offline
        if ($newStatus === 'fully_off' && $oldStatus !== 'fully_off') {
            $this->notifyManagers($feeder);
        }
    }

    private function notifyManagers(Feeder $feeder): void
    {
        // Queue notification to division_manager and sub_division_manager of this feeder
        $managers = User::whereIn('role', ['division_manager', 'sub_division_manager'])
            ->whereJurisdictionCovers($feeder)
            ->get();

        Notification::send($managers, new FeederOfflineNotification($feeder));
    }
}
```

---

## 10. FeederPolicy

```php
// app/Policies/FeederPolicy.php

class FeederPolicy
{
    public function updateStatus(User $user, Feeder $feeder): bool
    {
        // super_admin can update anything
        if ($user->hasRole('super_admin')) return true;

        // substation_manager / field_staff — only their substation's feeders
        if ($user->hasAnyRole(['substation_manager', 'field_staff'])) {
            return $feeder->substation_id === $user->jurisdiction_id;
        }

        return false;
    }
}
```

---

## 11. CSV Import Command

```php
// app/Console/Commands/ImportFeederCsv.php
// Usage: php artisan feeder:import "path/to/file.csv"
// Idempotent — safe to re-run (uses firstOrCreate)

// Logic:
// 1. Parse CSV row by row (skip header)
// 2. firstOrCreate Division by name
// 3. firstOrCreate SubDivision by (division_id + name)
// 4. firstOrCreate Substation by (sub_division_id + name)
// 5. updateOrCreate Feeder by tnd_code
//    → sets: name, category, total_consumer, total_tc
//    → does NOT overwrite current_status (preserve live data on re-import)
// 6. Report: X created, Y updated, Z skipped
```

---

## 12. Real-Time Strategy

### Phase 1 — AJAX Polling (ship first)

```javascript
// In dashboard Blade view
setInterval(function () {
    fetch('/api/feeders/summary', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => updateDashboardCards(data));
}, 30000); // 30 seconds
```

Dashboard shows: Total feeders | Fully ON | Partially ON | Fully OFF — updates every 30s without page reload.

### Phase 2 — Laravel Broadcasting (optional upgrade)

- Install Soketi (self-hosted, free) or use Pusher (paid)
- Fire `FeederStatusUpdated` event after every update
- Frontend Blade listens via Echo → instant push

---

## 13. Dashboard Design (per role)

### super_admin / division_manager view
```
┌─────────────────────────────────────────────────────┐
│  MGVCL Feeder Status Dashboard         [Export CSV] │
├─────────────────────────────────────────────────────┤
│  Filter: [Division ▼] [Sub Div ▼] [Status ▼] [Cat ▼]│
├──────────┬────────────┬─────────────┬───────────────┤
│ Total    │ Fully ON   │ Partial ON  │ Fully OFF     │
│  390     │   350 ✅   │   25 ⚠️    │   15 🔴       │
├─────────────────────────────────────────────────────┤
│ Division     │ ON  │ PARTIAL │ OFF │ Last Updated   │
│ Lalbagh      │ 98  │   5     │  2  │ 5 min ago      │
│ Vishwamitri-E│ 120 │   8     │  7  │ 2 min ago      │
│ Vishwamitri-W│ 132 │  12     │  6  │ just now       │
└─────────────────────────────────────────────────────┘
```

### substation_manager / field_staff view
```
┌──────────────────────────────────────────────────────────┐
│  Feeders — MOTIBAG Substation                            │
├─────────────────┬──────────┬───────────┬────────────────-┤
│ Feeder          │ Category │ Status    │ Action          │
├─────────────────┼──────────┼───────────┼─────────────────┤
│ LAHRIPURA       │ URBAN    │ ✅ ON     │ [Update Status] │
│ AZAD MEDAN      │ URBAN    │ 🔴 OFF    │ [Update Status] │
│ TIN MURTI       │ URBAN    │ ⚠️ PARTIAL │ [Update Status] │
└─────────────────┴──────────┴───────────┴─────────────────┘
```

**Update Status Modal:**
- Radio: Fully ON / Partially ON / Fully OFF
- Textarea: Remarks (required when marking OFF or PARTIAL)
- Submit → PATCH `/feeders/{id}/status`

---

## 14. Security

### 14.1 Authentication
- Laravel built-in session auth (no JWT needed — server-rendered Blade)
- Bcrypt password hashing (Laravel default)
- Session lifetime: 8 hours (match workday)
- `session.secure = true` in production (HTTPS only)

### 14.2 Authorization
- **Every route** protected by `auth` middleware
- **Every feeder update** checked via `FeederPolicy` — no client-side trust
- **Data scoping** enforced in `ScopeToJurisdiction` middleware using DB-level WHERE clauses — a substation manager cannot access another substation's feeders even by guessing IDs
- All policies registered in `AuthServiceProvider`

### 14.3 CSRF
- Laravel CSRF token on all POST/PATCH forms (default `VerifyCsrfToken` middleware)
- AJAX requests include `X-CSRF-TOKEN` header

### 14.4 Input Validation
```php
// UpdateFeederStatusRequest
public function rules(): array
{
    return [
        'status'  => ['required', Rule::in(['fully_on', 'partially_on', 'fully_off'])],
        'remarks' => ['nullable', 'string', 'max:500'],
    ];
}
```
- All input validated via Form Requests before hitting controller
- No raw `$request->input()` passed to DB

### 14.5 SQL Injection
- Eloquent ORM + Query Builder only — no raw SQL with user input
- If raw SQL ever needed: use PDO `?` bindings exclusively

### 14.6 XSS
- Blade `{{ }}` escapes all output by default
- `{!! !!}` (unescaped) NEVER used with user-supplied data
- Content Security Policy header set in middleware

### 14.7 Rate Limiting
```php
// routes/api.php
Route::middleware(['auth', 'throttle:60,1'])  // 60 req/min per user
Route::middleware(['auth', 'throttle:10,1'])  // 10 req/min for status updates
```

### 14.8 Audit Trail
- `feeder_status_logs` table: immutable, append-only
- Stores: who, what (old→new), when, why (remarks)
- No delete/update permissions on this table for app DB user
- DB user for app: only `SELECT, INSERT, UPDATE` on core tables; `SELECT, INSERT` only on logs table

### 14.9 Secrets / Environment
```env
APP_ENV=production
APP_DEBUG=false          # NEVER true in production
DB_PASSWORD=<strong>     # min 20 chars, random
SESSION_DRIVER=database  # not file (multi-server safe)
SESSION_LIFETIME=480     # 8 hours
MAIL_*                   # for notifications
```

### 14.10 Additional
- HTTP security headers via middleware: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`
- Employee login: employee_id + password (not email — aligns with MGVCL systems)
- Password policy: min 10 chars, enforced at user creation by super_admin
- No self-registration — super_admin creates all user accounts

---

## 15. Notifications

When feeder status changes to `fully_off`:
1. Email to Division Manager (immediate, queued)
2. Email to Sub Division Manager (immediate, queued)
3. In-app notification badge (Laravel Notification + database channel)

```php
class FeederOfflineNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("ALERT: Feeder Offline — {$this->feeder->name}")
            ->line("Feeder {$this->feeder->name} (TND: {$this->feeder->tnd_code})")
            ->line("Substation: {$this->feeder->substation->name}")
            ->line("Status changed to: FULLY OFF")
            ->line("Time: " . now()->format('d-M-Y H:i'));
    }
}
```

---

## 16. Reports

Available to `super_admin`, `division_manager`, `sub_division_manager`:

| Report | Format | Description |
|--------|--------|-------------|
| Current Status Summary | CSV / screen | All feeders with current status |
| Outage History | CSV | All `fully_off` events in date range |
| Feeder Log | CSV | All status changes for selected feeder |
| Division Summary | Screen | Count of ON/PARTIAL/OFF per division |

Export via Laravel Excel (Maatwebsite) or simple CSV stream (no package needed for basic).

---

## 17. Build Order / Milestones

### Phase 1 — Foundation (Week 1-2)
- [ ] Laravel 11 project setup
- [ ] Database migrations (all 5 tables)
- [ ] Spatie permission: roles + permissions seeded
- [ ] Auth: login/logout
- [ ] CSV import artisan command + data migration

### Phase 2 — Core Feature (Week 2-3)
- [ ] FeederPolicy + ScopeToJurisdiction middleware
- [ ] Feeder list view (with filters)
- [ ] Feeder status update (PATCH + modal)
- [ ] FeederStatusService (update + log)
- [ ] Audit log view

### Phase 3 — Dashboard + Reports (Week 3-4)
- [ ] Role-aware dashboard with summary cards
- [ ] AJAX polling (30s refresh)
- [ ] Report export (CSV)
- [ ] Email notifications on fully_off

### Phase 4 — Admin + Polish (Week 4-5)
- [ ] User management (super_admin)
- [ ] Mobile-responsive Blade (Bootstrap 5 or Tailwind)
- [ ] Security headers middleware
- [ ] Testing: feature tests for status update flow

### Phase 5 — Optional Upgrades
- [ ] Soketi + Laravel Echo (real-time push)
- [ ] SMS notification (Twilio / MSG91)
- [ ] Map view showing feeder status by area

---

## 18. Tech Dependencies

| Package | Purpose |
|---------|---------|
| `laravel/framework:^11.0` | Core framework |
| `spatie/laravel-permission` | RBAC roles + permissions |
| `maatwebsite/excel` | CSV/Excel export (optional, can use native) |
| Bootstrap 5 (CDN) | UI components |

No other external packages needed for Phase 1-4.

---

## 19. Environment Setup (WAMP / Development)

```bash
# Create project
composer create-project laravel/laravel mgvcl-feeder
cd mgvcl-feeder

# Install Spatie permission
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Run migrations
php artisan migrate

# Seed roles + permissions
php artisan db:seed --class=RolePermissionSeeder

# Import CSV data
php artisan feeder:import "C:/wamp64/www/tsp/mgvcl-feeder/Feeder power position.csv"

# Create first super_admin user
php artisan tinker
# >>> User::create([...]) then assign role
```

MySQL DB: `mgvcl_feeder` | Charset: `utf8mb4` | Collation: `utf8mb4_unicode_ci`

---

## 20. Key Decisions & Rationale

| Decision | Choice | Reason |
|----------|--------|--------|
| Real-time method | AJAX polling (Phase 1) | No extra infra, covers 95% of need |
| Auth type | Session (not JWT) | Server-rendered Blade, simpler, more secure for internal tool |
| RBAC package | Spatie Permission | Most mature, battle-tested, good caching |
| Status model | Single enum on `feeders` + log table | Atomic update, full history, simple queries |
| Jurisdiction scoping | Middleware + Policy (not just view) | Defense in depth — security at data layer |
| CSV re-import | Does not overwrite current_status | Prevents wiping live data on re-import |
| User creation | super_admin only | No self-registration risk for internal utility |
