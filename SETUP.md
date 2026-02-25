# SAMPAHAN – Setup & Run Guide

## Prerequisites
- PHP 8.1+, MySQL 5.7+, Composer
- Apache/Nginx with `mod_rewrite` enabled (XAMPP works perfectly)

---

## 1. Clone / Copy Project

Place the project at your web root. For XAMPP:
```
C:\xampp\htdocs\project-website\sampahan\
```

---

## 2. Install Dependencies
```bash
composer install
```

---

## 3. Configure `.env`
Copy the sample and edit:
```bash
cp env .env
```

Edit `.env` — **only the database block needs filling; SMTP is configured via Admin GUI**:
```ini
CI_ENVIRONMENT = development

# ── Required: Database ─────────────────────────────────────────────────────────
database.default.hostname = localhost
database.default.database = sampahan
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =

# ── App base URL ───────────────────────────────────────────────────────────────
app.baseURL = 'http://localhost/project-website/sampahan/public/'
```

> **Important:** Leave all SMTP/mail settings blank. They are stored in the database
> and managed via the Admin → Settings page at runtime.

---

## 4. Create the Database
```sql
CREATE DATABASE sampahan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 5. Run Migrations & Seeders
```bash
php spark migrate
php spark db:seed DatabaseSeeder
```

This creates:
| Table          | Description                               |
|----------------|-------------------------------------------|
| `users`        | Authentication + roles                    |
| `settings`     | All white-label / SMTP / GIS config       |
| `reports`      | Waste reports with lat/lng                |
| `report_logs`  | Audit trail for status changes            |

Default admin account seeded:
- **Email**: `admin@sampahan.id`
- **Password**: `Admin@1234`

---

## 6. Open in Browser
```
http://localhost/project-website/sampahan/public/
```

Login at `/auth/login`.

---

## White-Label Configuration (Admin Panel)
After logging in as admin, go to **Settings** to customise:

| Tab        | Setting                                          |
|------------|--------------------------------------------------|
| Appearance | App Name, City Name, Logo, Favicon               |
| Map        | Centre Lat/Lng, Default Zoom, City Boundary GeoJSON |
| Mail       | SMTP Host/Port/User/Pass/Crypto, From Name       |
| General    | Email Verification toggle, Duplicate radius (m)  |

No code changes are ever required for a new city deployment.

---

## City Boundary GeoJSON
Upload or paste a GeoJSON `Polygon` or `MultiPolygon` in the **Map** settings tab.

- If empty, the boundary check passes for all coordinates (open mode).
- Used for:
  - Server-side `pointInPolygon` validation before accepting a report
  - Client-side inverted grey mask on the Leaflet map

---

## Folder Structure

```
app/
├── Config/
│   ├── Filters.php          ← Registers AuthFilter + RoleFilter aliases
│   └── Routes.php           ← Full role-segmented route map
├── Controllers/
│   ├── Auth/                ← Login, Register, Forgot/Reset Password
│   ├── Admin/               ← Dashboard, Settings, Users, Profile
│   ├── Dinas/               ← Dashboard, Map (advance/reject), Profile
│   ├── Masyarakat/          ← Dashboard, Report (submit), History, Profile
│   └── Public/              ← Landing page + public map
├── Database/
│   ├── Migrations/          ← 4 migrations (users/settings/reports/logs)
│   └── Seeds/               ← SettingsSeeder + AdminSeeder + DatabaseSeeder
├── Filters/
│   ├── AuthFilter.php       ← Session check + is_active guard
│   └── RoleFilter.php       ← Multi-role access control
├── Libraries/
│   └── GeoHelper.php        ← haversineDistance(), pointInPolygon()
├── Models/
│   ├── SettingModel.php     ← Key-value store + in-request cache
│   ├── UserModel.php        ← Auth helpers, stats, deactivate/activate
│   ├── ReportModel.php      ← State machine, GeoJSON, duplicate check
│   └── ReportLogModel.php   ← Audit trail
├── Services/
│   ├── DynamicMailer.php    ← Reads SMTP from DB, sends 3 email types
│   └── ImageAnalysisService.php ← Interface + Mock + Google Vision stub
└── Views/
    ├── layouts/
    │   ├── _base.php        ← Master layout (sidebar, topbar, flash)
    │   ├── admin.php        ← Admin sidebar injection
    │   ├── dinas.php        ← Dinas sidebar injection
    │   ├── masyarakat.php   ← Masyarakat sidebar injection
    │   └── public.php       ← Public navbar
    ├── auth/                ← login, register, forgot/reset password
    ├── admin/               ← dashboard, settings, users/*, profile
    ├── dinas/               ← dashboard, map, profile
    ├── masyarakat/          ← dashboard, report_form, history, detail, profile
    ├── public/              ← landing, map
    └── emails/              ← activation, password_reset, cleaned_notification
```

---

## How Dynamic SMTP Works

1. `SettingsSeeder` seeds an empty SMTP config (host='', pass='', etc.) into `settings` table.
2. Admin fills SMTP credentials in **Admin → Settings → Mail tab**.
3. `SettingModel::getMailConfig()` reads those rows and returns a CI4-compatible Email config array.
4. `DynamicMailer::__construct()` calls `getMailConfig()` and initialises CI4's Email service.

**No `.env` SMTP variables are ever read.** This allows a single codebase to serve multiple cities, each with its own mail server.

---

## How Logo / Favicon Propagate Everywhere

`BaseController::initController()` runs on every request and loads all settings:
```php
$this->appSettings = model(SettingModel::class)->getAll();
```

`BaseController::render()` merges `$this->appSettings` into every view as `$settings`:
```php
$data['settings'] = $this->appSettings;
```

Every view then reads:
```php
<link rel="icon" href="<?= base_url($settings['app_favicon'] ?? 'favicon.ico') ?>">
<img src="<?= base_url($settings['app_logo'] ?? '') ?>" alt="<?= esc($settings['app_name']) ?>">
```

One DB read per request. No per-view calls needed.

---

## Enabling Google Vision API (Image Analysis)

1. Open `app/Services/ImageAnalysisService.php`
2. Uncomment the Google Cloud Vision SDK block in `GoogleVisionAnalysisService::analyzeImage()`
3. Run: `composer require google/cloud-vision`
4. In `app/Controllers/Masyarakat/ReportController.php` replace:
   ```php
   $analyser = new MockImageAnalysisService();
   ```
   with:
   ```php
   $analyser = new GoogleVisionAnalysisService();
   ```

---

## Default Credentials Summary

| Role      | Email                  | Password    |
|-----------|------------------------|-------------|
| Admin     | admin@sampahan.id      | Admin@1234  |

Register additional `dinas` and `masyarakat` users via the Admin → Users page or the public `/auth/register` page.

---

## Status Flow

```
pending → reviewed → in_progress → cleaned
       ↘                        ↗
        rejected (at any stage)
```

Transitions are enforced by `ReportModel::TRANSITIONS`. Moving to `cleaned` automatically emails the reporter via `DynamicMailer::sendCleanedNotification()`.

get geojson "https://www.petanusa.web.id"