# MGVCL Feeder Portal

A real-time feeder status monitoring portal for **Madhya Gujarat Vij Company Limited (MGVCL)** — Vadodara City Circle.

Built by [Trentiums](https://trentiums.com).

---

## Overview

Web-based portal to track and manage the current status (ON / Partial / OFF) of all feeders across divisions and sub-divisions. Supports real-time updates, role-based access, and export capabilities.

---

## Tech Stack

- **Backend:** Laravel 11, PHP 8.2
- **Frontend:** Bootstrap 5, jQuery, DataTables
- **Database:** MySQL
- **Real-time:** Pusher + Laravel Echo
- **Auth & Roles:** Spatie Laravel-Permission
- **Exports:** SheetJS (Excel), jsPDF + autoTable (PDF)

---

## Roles & Permissions

| Role | Access |
|------|--------|
| `admin` | Full access — all circles, master data, user management |
| `circle` | Circle-scoped — dashboard, feeders, master data, manage sub-users |
| `circle_viewer` | Circle-scoped view-only — dashboard, feeder list, logs, export |
| `division_manager` | Division-scoped — feeder list, status update |
| `sub_division_manager` | Sub-division-scoped — feeder list, status update |

---

## Features

- **Dashboard** — Division-wise & Sub-division-wise feeder status summary with tabs
- **Feeder List** — Filterable by status, category, division, sub-division; sorted Division → Sub-Division → Name
- **Status Update** — Single & bulk feeder status update (Fully ON / Partially ON / Fully OFF)
- **Status Logs** — Per-feeder history of all status changes
- **Export** — Excel, PDF, WhatsApp-formatted message copy
- **Real-time** — Live feeder status updates via Pusher broadcasting
- **Master Data** — CRUD for Circles, Divisions, Sub-Divisions, Substations, Feeders, Feeder Categories
- **User Management** — Role-based user creation with jurisdiction scoping

---

## Jurisdiction Hierarchy

```
Circle
 └── Division
      └── Sub-Division
           └── Substation
                └── Feeder
```

---

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

Configure `.env`:
```env
DB_DATABASE=mgvcl
DB_USERNAME=root
DB_PASSWORD=

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=ap2

BROADCAST_DRIVER=pusher
```

---

## Development

```bash
php artisan serve
php artisan queue:work
```

---

## License

Proprietary — MGVCL / Trentiums. All rights reserved.
