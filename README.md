# SAMPAHAN — Sistem Informasi Pengelolaan Sampah Berbasis Web-GIS

> Platform pelaporan dan pengelolaan sampah berbasis peta interaktif untuk pemerintah daerah.  
> Warga melaporkan, petugas menangani, semua terpantau secara real-time.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Stack Teknologi](#stack-teknologi)
- [Struktur Peran Pengguna](#struktur-peran-pengguna)
- [Alur Status Laporan](#alur-status-laporan)
- [Persyaratan Server](#persyaratan-server)
- [Instalasi Lokal (XAMPP)](#instalasi-lokal-xampp)
- [Deploy ke Hosting (cPanel / Shared Hosting)](#deploy-ke-hosting-cpanel--shared-hosting)
- [Konfigurasi .env](#konfigurasi-env)
- [Struktur Direktori](#struktur-direktori)
- [Skema Database](#skema-database)
- [Data Dummy](#data-dummy)
- [Akun Default Seeder](#akun-default-seeder)

---

## Fitur Utama

| Fitur | Keterangan |
|---|---|
| **Pelaporan berbasis GPS** | Warga mengambil foto + koordinat GPS otomatis terdeteksi via browser |
| **Validasi wilayah** | Laporan divalidasi masuk batas wilayah kota menggunakan algoritma *point-in-polygon* GeoJSON |
| **Deteksi duplikasi** | Mencegah laporan ganda dalam radius 10 meter dari laporan aktif |
| **Hotspot berulang** | Lokasi yang pernah dilaporkan sebelumnya otomatis ditandai sebagai hotspot prioritas |
| **Peta interaktif publik** | Peta sebaran laporan real-time terbuka untuk umum tanpa login |
| **Manajemen laporan Dinas** | Petugas dinas mengelola laporan via peta interaktif dengan filter status |
| **Manajemen laporan Admin** | Admin dapat ubah status bebas, hapus, dan bulk aksi laporan |
| **Notifikasi email otomatis** | Email konfirmasi saat laporan masuk, dan notifikasi saat laporan selesai |
| **Pengaturan sistem** | Logo, nama kota, SMTP, radius duplikasi, batas GeoJSON, timezone — semua via panel admin |
| **SweetAlert2** | Semua dialog konfirmasi/aksi menggunakan SweetAlert2 (bukan native browser alert) |
| **Mobile-first** | Responsif penuh untuk smartphone, tablet, dan iPhone (safe viewport height) |
| **White-label** | Dapat dikonfigurasi untuk kota/daerah mana pun tanpa mengubah kode |

---

## Stack Teknologi

- **Backend:** PHP 8.2+, CodeIgniter 4.7
- **Database:** MySQL / MariaDB
- **Frontend:** Bootstrap 5.3, Bootstrap Icons 1.11
- **Peta:** Leaflet.js 1.9.4 + OpenStreetMap
- **Chart:** Chart.js 4.4
- **Dialog:** SweetAlert2 v11
- **Font:** Poppins (Google Fonts)
- **Email:** PHPMailer via konfigurasi SMTP dinamis (disimpan di tabel `settings`)

---

## Struktur Peran Pengguna

| Peran | Akses |
|---|---|
| **Masyarakat** | Kirim laporan, lihat riwayat laporan sendiri, edit profil |
| **Dinas** | Dashboard statistik, kelola laporan via peta (advance / reject), edit profil |
| **Admin** | Semua akses Dinas + manajemen user, manajemen semua laporan, pengaturan sistem |

---

## Alur Status Laporan

```
pending → reviewed → in_progress → cleaned
                  ↘
                   rejected
```

- **pending** — baru dikirim warga, menunggu verifikasi
- **reviewed** — diterima & dijadwalkan
- **in_progress** — petugas sedang menangani di lapangan
- **cleaned** — area berhasil dibersihkan
- **rejected** — ditolak (di luar wilayah / duplikat / gambar tidak valid / manual)

> Catatan: Status `pending` dan `rejected` tidak ditampilkan di peta publik.

---

## Persyaratan Server

- PHP **8.2** atau lebih tinggi
- MySQL **5.7+** / MariaDB 10.4+
- Apache dengan **mod_rewrite** aktif
- Ekstensi PHP: `intl`, `mbstring`, `json`, `mysqlnd`, `libcurl`, `gd` atau `imagick`

---

## Instalasi Lokal (XAMPP)

```bash
# 1. Clone atau ekstrak project ke htdocs
cd C:/xampp/htdocs/project-website/
# (letakkan folder sampahan di sini)

# 2. Install dependencies
cd sampahan
composer install

# 3. Salin file environment
copy env .env

# 4. Edit .env — minimal isi bagian ini:
#    app.baseURL     = 'http://localhost/project-website/sampahan/public/'
#    database.default.hostname = localhost
#    database.default.database = sampahan
#    database.default.username = root
#    database.default.password = root

# 5. Buat database
# Buka phpMyAdmin dan buat database bernama: sampahan

# 6. Jalankan migrasi & seeder
php spark migrate
php spark db:seed DatabaseSeeder

# 7. (Opsional) Data dummy 35 laporan area Tangerang Selatan
php spark db:seed DummyReportSeeder
```

Buka browser: `http://localhost/project-website/sampahan/public/`

---

## Deploy ke Hosting (cPanel / Shared Hosting)

### Opsi A — Domain root (`example.com`)

1. Upload **seluruh isi** folder `sampahan/` ke `public_html/`
2. File `.htaccess` di root sudah dikonfigurasi untuk meneruskan traffic ke `public/`
3. Tidak perlu mengubah apapun di `public/.htaccess`

### Opsi B — Subdomain (`sampahan.example.com`)

1. Set document root subdomain ke `public_html/sampahan/public/`
2. Upload seluruh folder ke `public_html/sampahan/`

### Opsi C — Subfolder (`example.com/sampahan/`)

1. Upload ke `public_html/sampahan/`
2. Edit `.htaccess` root project, ubah baris:
   ```apache
   RewriteBase /
   ```
   menjadi:
   ```apache
   RewriteBase /sampahan/
   ```
3. Edit `.env`:
   ```
   app.baseURL = 'https://example.com/sampahan/'
   ```

### Langkah umum setelah upload

```bash
# Di terminal hosting (SSH) atau via phpMyAdmin:
php spark migrate
php spark db:seed DatabaseSeeder
```

Pastikan folder `writable/` memiliki permission **755** atau **777**.

---

## Konfigurasi .env

Salin file `env` menjadi `.env` dan sesuaikan:

```env
CI_ENVIRONMENT = production

app.baseURL = 'https://yourdomain.com/'

database.default.hostname = localhost
database.default.database = nama_database
database.default.username = user_database
database.default.password = password_database
database.default.DBDriver = MySQLi
database.default.port     = 3306
```

> SMTP email dikonfigurasi via panel **Admin → Pengaturan Sistem**, bukan di `.env`.

---

## Struktur Direktori

```
sampahan/
├── .htaccess                   ← Root rewrite rules (routing ke public/)
├── .env                        ← Konfigurasi environment (jangan di-commit)
├── app/
│   ├── Config/
│   │   ├── Routes.php          ← Semua route aplikasi
│   │   └── ...
│   ├── Controllers/
│   │   ├── Admin/              ← DashboardController, UserController, SettingsController, ReportController
│   │   ├── Auth/               ← AuthController, ForgotPasswordController
│   │   ├── Dinas/              ← DashboardController, MapController, ProfileController
│   │   ├── Masyarakat/         ← DashboardController, ReportController, ProfileController
│   │   └── Public/             ← LandingController (beranda & peta publik)
│   ├── Database/
│   │   ├── Migrations/         ← 4 migrasi: users, settings, reports, report_logs
│   │   └── Seeds/              ← DatabaseSeeder, DummyReportSeeder
│   ├── Filters/
│   │   ├── AuthFilter.php      ← Cek session login
│   │   └── RoleFilter.php      ← Cek role (admin/dinas/masyarakat)
│   ├── Models/
│   │   ├── ReportModel.php     ← Logic duplikasi, hotspot, GeoJSON, status transition
│   │   ├── UserModel.php
│   │   ├── SettingModel.php
│   │   └── ReportLogModel.php
│   ├── Services/
│   │   ├── DynamicMailer.php   ← PHPMailer dengan konfigurasi SMTP dari DB
│   │   └── ImageAnalysisService.php
│   ├── Libraries/
│   │   └── GeoHelper.php       ← Point-in-polygon & Haversine distance
│   └── Views/
│       ├── layouts/
│       │   ├── _base.php       ← Layout utama (Bootstrap + Leaflet + SweetAlert2)
│       │   ├── admin.php       ← Sidebar admin
│       │   ├── dinas.php       ← Sidebar dinas
│       │   ├── masyarakat.php  ← Sidebar masyarakat
│       │   └── public.php      ← Layout halaman publik
│       ├── admin/              ← Views admin
│       ├── dinas/              ← Views dinas
│       ├── masyarakat/         ← Views masyarakat
│       ├── public/             ← landing.php, map.php
│       └── emails/             ← Template email HTML
├── public/
│   ├── index.php               ← Entry point
│   ├── .htaccess               ← Remove index.php dari URL
│   └── uploads/                ← Foto laporan, logo, favicon
└── writable/
    ├── logs/                   ← Log error CI4
    ├── cache/
    └── session/
```

---

## Skema Database

### Tabel `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT | Primary key |
| name | VARCHAR(150) | Nama lengkap |
| email | VARCHAR(191) | Unique |
| password | VARCHAR(255) | Bcrypt |
| role | ENUM | `admin` / `dinas` / `masyarakat` |
| is_active | TINYINT | 0 = nonaktif |
| activation_token | VARCHAR | Token aktivasi email |
| reset_token | VARCHAR | Token reset password |

### Tabel `reports`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT | Primary key |
| user_id | INT | FK → users |
| latitude | DECIMAL(10,7) | Koordinat GPS |
| longitude | DECIMAL(10,7) | Koordinat GPS |
| photo_path | VARCHAR | Relatif ke `public/` |
| description | TEXT | Deskripsi dari pelapor |
| status | ENUM | `pending / reviewed / in_progress / cleaned / rejected` |
| admin_note | TEXT | Catatan petugas |
| is_recurrent_hotspot | TINYINT | 1 = hotspot berulang |
| rejection_reason | ENUM | `outside_boundary / duplicate_active / invalid_image / manual` |

### Tabel `report_logs`
Menyimpan riwayat setiap perubahan status beserta siapa yang mengubah dan catatan.

### Tabel `settings`
Pasangan `key → value` untuk konfigurasi aplikasi (nama, logo, SMTP, GeoJSON batas, dst).

---

## Data Dummy

Mengisi 35 laporan sampah tersebar di area **Kota Tangerang Selatan**:

```bash
php spark db:seed DummyReportSeeder
```

Seeder membutuhkan akun `dinas@sampahan.id` dan `masyarakat@sampahan.id` sudah ada (dibuat oleh `DatabaseSeeder`).

---

## Akun Default Seeder

Setelah menjalankan `php spark db:seed DatabaseSeeder`:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@sampahan.id` | `Admin@1234` |

> **Ganti semua password default segera setelah deploy ke production.**

---

## GeoJSON Kota/Kabupaten Source
[Source](https://www.petanusa.web.id)

---

## Lisensi

Project ini dikembangkan untuk keperluan penelitian.  
Framework CodeIgniter 4 dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).

