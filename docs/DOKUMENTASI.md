# 📋 DOKUMENTASI LENGKAP — HRIS DIDIMAX

**Versi Aplikasi:** 3.0.6  
**Framework:** Laravel 10.10 (PHP 8.1+)  
**URL Produksi:** https://hris.didimax.online dan https://hris.didimax.id
**Tanggal Dokumentasi:** 18 Mei 2026  

---

## 📑 DAFTAR ISI

1. [Deskripsi Aplikasi](#1-deskripsi-aplikasi)
2. [Arsitektur Sistem](#2-arsitektur-sistem)
3. [Teknologi & Dependensi](#3-teknologi--dependensi)
4. [Instalasi & Setup](#4-instalasi--setup)
5. [Konfigurasi Environment](#5-konfigurasi-environment)
6. [Struktur Database](#6-struktur-database)
7. [Modul Aplikasi](#7-modul-aplikasi)
8. [Manajemen Pengguna & Roles](#8-manajemen-pengguna--roles)
9. [API Endpoints](#9-api-endpoints)
10. [Autentikasi & Keamanan](#10-autentikasi--keamanan)
11. [Sistem Notifikasi](#11-sistem-notifikasi)
12. [Queue & Background Jobs](#12-queue--background-jobs)
13. [Laporan & Export](#13-laporan--export)
14. [Integrasi WhatsApp Gateway](#14-integrasi-whatsapp-gateway)
15. [Sistem Update Otomatis](#15-sistem-update-otomatis)
16. [Artisan Commands](#16-artisan-commands)
17. [Diagram Alur Proses](#17-diagram-alur-proses)
18. [Panduan Troubleshooting](#18-panduan-troubleshooting)

---

## 1. DESKRIPSI APLIKASI

**HRIS DIDIMAX** adalah sistem informasi manajemen sumber daya manusia (Human Resource Information System) berbasis web yang dibangun menggunakan framework Laravel. Aplikasi ini dirancang untuk mengelola seluruh proses HR secara terpadu dalam satu platform.

### Fitur Utama

| Kategori | Fitur |
|----------|-------|
| **Kehadiran** | Presensi masuk/keluar, presensi istirahat, presensi lembur, kiosk publik, face recognition |
| **Izin** | Izin absen, izin sakit, izin cuti, izin dinas dengan multi-level approval |
| **Penggajian** | Gaji pokok, tunjangan, BPJS Kesehatan, BPJS Tenaga Kerja, slip gaji, penyesuaian gaji |
| **Karyawan** | Data karyawan, kontrak, mutasi, jabatan, departemen, cabang |
| **Rekrutmen** | Manajemen lowongan, pelamar, status interview, konfirmasi |
| **Aset** | Manajemen aset perusahaan, peminjaman, transaksi |
| **KPI** | Indikator kinerja, periode, penilaian karyawan |
| **IT Ticket** | Sistem tiket IT support dengan response tracking |
| **Laporan** | Laporan presensi, gaji, karyawan, export Excel/PDF |
| **Keamanan** | 2FA, audit log, trusted device, role-based access |

---

## 2. ARSITEKTUR SISTEM

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                             │
│   Browser (Web App)  │  Mobile App (Flutter)  │  ADMS Device   │
└──────────────┬──────────────────┬─────────────────┬────────────┘
               │                  │                 │
               ▼                  ▼                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                     NGINX / APACHE                              │
│                    hris.didimax.online                          │
└──────────────────────────┬──────────────────────────────────────┘
                           │
               ┌───────────▼──────────────┐
               │   Laravel Application    │
               │       (PHP 8.1+)         │
               │                          │
               │  ┌──────────────────┐   │
               │  │    Routing        │   │
               │  │  (web.php/api.php)│   │
               │  └────────┬─────────┘   │
               │           │             │
               │  ┌────────▼─────────┐   │
               │  │   Middleware      │   │
               │  │  (Auth, CSRF,     │   │
               │  │   Permission)     │   │
               │  └────────┬─────────┘   │
               │           │             │
               │  ┌────────▼─────────┐   │
               │  │   Controllers     │   │
               │  │  (66 controllers) │   │
               │  └────────┬─────────┘   │
               │           │             │
               │  ┌────────▼─────────┐   │
               │  │   Models          │   │
               │  │  (70 models)      │   │
               │  └────────┬─────────┘   │
               └───────────┼─────────────┘
                           │
     ┌─────────────────────┼──────────────────────┐
     │                     │                      │
     ▼                     ▼                      ▼
┌──────────┐    ┌──────────────────┐    ┌──────────────────┐
│  MySQL   │    │  File Storage    │    │  Queue (Redis/DB) │
│ Database │    │  (uploads/docs)  │    │  Background Jobs │
└──────────┘    └──────────────────┘    └──────────────────┘
```

### Pattern yang Digunakan

- **MVC** (Model-View-Controller) — Laravel default
- **Repository Pattern** — via Eloquent ORM
- **Service Layer** — `ApprovalService`, `UpdateService`
- **Observer Pattern** — via Laravel Notifications
- **Queue Pattern** — via Laravel Jobs (`SendWaMessage`)
- **Gate/Policy** — via Spatie Laravel Permission

---

## 3. TEKNOLOGI & DEPENDENSI

### Backend (PHP)

| Package | Versi | Kegunaan |
|---------|-------|----------|
| `laravel/framework` | ^10.10 | Core framework |
| `spatie/laravel-permission` | ^6.3 | Role & permission management |
| `laravel/sanctum` | ^3.3 | API token authentication |
| `maatwebsite/excel` | ^3.1.48 | Export/Import Excel |
| `barryvdh/laravel-dompdf` | ^3.1 | Generate PDF |
| `intervention/image` | ^3.11 | Image processing |
| `arielmejiadev/larapex-charts` | ^8.1 | Chart/grafik |
| `jenssegers/agent` | ^2.6 | Device detection |
| `milon/barcode` | ^12.0 | Barcode generation |
| `guzzlehttp/guzzle` | ^7.2 | HTTP client (WA Gateway) |

### Frontend

| Package | Versi | Kegunaan |
|---------|-------|----------|
| `tailwindcss` | ^3.1.0 | CSS Framework |
| `alpinejs` | ^3.4.2 | Reactive JS component |
| `vite` | ^5.0.0 | Asset bundler |
| `axios` | ^1.6.4 | HTTP client |
| `face-api.js` | CDN | Face recognition |
| `leaflet.js` | CDN | Maps / GPS |
| `webcam.js` | CDN | Camera capture |
| `sweetalert2` | CDN | Alert dialogs |
| `ion-icons` | CDN | Icon library |

### Infrastruktur

- **OS:** Linux (Debian)
- **Web Server:** Nginx / Apache
- **Database:** MySQL 8.x
- **PHP:** 8.1+
- **Cache/Queue:** Database / Redis (configurable)

---

## 4. INSTALASI & SETUP

### Prasyarat

```bash
- PHP >= 8.1
- Composer 2.x
- Node.js >= 18.x
- MySQL 8.x
- Git
```

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/garudabdg/hris-production.git
cd hris-production

# 2. Install PHP dependencies
composer install --optimize-autoloader --no-dev

# 3. Install Node dependencies & build assets
npm install
npm run build

# 4. Copy dan konfigurasi environment
cp .env.example .env
php artisan key:generate

# 5. Konfigurasi .env (database, mail, dll)
nano .env

# 6. Jalankan migrasi database
php artisan migrate --force

# 7. Jalankan seeder
php artisan db:seed

# 8. Buat symbolic link storage
php artisan storage:link

# 9. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 10. Cache konfigurasi (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Setup Queue Worker

```bash
# Jalankan queue worker (gunakan supervisor di production)
php artisan queue:work --queue=default --tries=3

# Atau dengan supervisor:
# /etc/supervisor/conf.d/hris-worker.conf
[program:hris-worker]
command=php /var/www/hris.didimax.online/artisan queue:work database --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
```

---

## 5. KONFIGURASI ENVIRONMENT

### File `.env` — Parameter Penting

```env
# Aplikasi
APP_NAME="HRIS DIDIMAX"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hris.didimax.online

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hris_db
DB_USERNAME=hris_user
DB_PASSWORD=your_password

# Email (untuk notifikasi approval)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@didimax.online
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@didimax.online
MAIL_FROM_NAME="HRIS DIDIMAX"

# Queue (database atau redis)
QUEUE_CONNECTION=database

# Cache
CACHE_DRIVER=file
SESSION_DRIVER=file

# WhatsApp Gateway (Fonnte / custom)
WA_GATEWAY_URL=https://api.fonnte.com/send
WA_GATEWAY_TOKEN=your_wa_token

# OneSignal (Push Notification)
ONESIGNAL_APP_ID=your_app_id
ONESIGNAL_REST_API_KEY=your_rest_key

# Update System
UPDATE_SERVER_URL=https://update.didimax.online
UPDATE_LICENSE_KEY=your_license_key
```

---

## 6. STRUKTUR DATABASE

### 147 Migration Files

Database terdiri dari **70+ tabel** yang dibagi menjadi kelompok:

### Tabel Master Data

| Tabel | Deskripsi |
|-------|-----------|
| `karyawan` | Data utama karyawan (NIK, nama, jabatan, dept, cabang) |
| `jabatan` | Master jabatan/posisi |
| `departemen` | Master departemen dengan sub-departemen |
| `cabang` | Master cabang/lokasi perusahaan |
| `users` | Akun login (relasi dengan karyawan via `userkaryawan`) |
| `userkaryawan` | Mapping user → karyawan |

### Tabel Presensi & Kehadiran

| Tabel | Deskripsi |
|-------|-----------|
| `presensi` | Record presensi harian (hadir/izin/sakit/cuti/alpha) |
| `presensi_jamkerja` | Definisi jam kerja |
| `presensi_jamkerja_byday` | Jam kerja per hari |
| `presensi_jamkerja_bydate` | Jam kerja per tanggal khusus |
| `presensi_jamkerja_bydept` | Jam kerja per departemen |
| `hari_libur` | Master hari libur nasional |
| `hari_libur_detail` | Detail karyawan yang libur |
| `facerecognition` | Data face descriptor untuk pengenalan wajah |
| `log_absen` | Log aktivitas presensi |

### Tabel Izin & Persetujuan

| Tabel | Deskripsi |
|-------|-----------|
| `presensi_izinabsen` | Pengajuan izin absen |
| `presensi_izinsakit` | Pengajuan izin sakit |
| `presensi_izincuti` | Pengajuan izin cuti |
| `presensi_izindinas` | Pengajuan izin dinas |
| `presensi_izinabsen_approve` | Relasi presensi ↔ izin absen |
| `presensi_izinsakit_approve` | Relasi presensi ↔ izin sakit |
| `presensi_izincuti_approve` | Relasi presensi ↔ izin cuti |
| `approvals` | Log history approval multi-step |
| `approval_layers` | Konfigurasi alur approval per jabatan |

### Tabel Penggajian

| Tabel | Deskripsi |
|-------|-----------|
| `karyawan_gajipokok` | Riwayat gaji pokok karyawan |
| `karyawan_tunjangan` | Data tunjangan karyawan |
| `karyawan_tunjangan_detail` | Detail jenis tunjangan |
| `jenis_tunjangan` | Master jenis tunjangan |
| `karyawan_slip_gaji` | Slip gaji bulanan |
| `karyawan_penyesuaiangaji` | Penyesuaian/koreksi gaji |
| `karyawan_penyesuaiangaji_detail` | Detail penyesuaian gaji |
| `bpjs_kesehatan` | Data BPJS Kesehatan karyawan |
| `bpjs_tenagakerja` | Data BPJS Tenaga Kerja karyawan |
| `denda` | Data denda/potongan karyawan |
| `cuti` | Master jenis cuti & jumlah hari |

### Tabel Lainnya

| Tabel | Deskripsi |
|-------|-----------|
| `lembur` | Data lembur karyawan |
| `kontrak` | Kontrak kerja karyawan |
| `mutasi_karyawan` | Riwayat mutasi karyawan |
| `pelanggaran` | Data pelanggaran karyawan |
| `aktivitas_karyawan` | Log aktivitas/catatan HR |
| `kunjungan` | Data kunjungan lapangan karyawan |
| `pengumuman` | Pengumuman internal perusahaan |
| `assets` | Data aset perusahaan |
| `asset_categories` | Kategori aset |
| `asset_pinjam` | Peminjaman aset |
| `asset_transactions` | Transaksi aset |
| `it_tickets` | Tiket IT support |
| `it_ticket_responses` | Response tiket IT |
| `recruitments` | Data pelamar/rekrutmen |
| `recruitment_vacancies` | Lowongan kerja |
| `kpi_periods` | Periode penilaian KPI |
| `kpi_indicators` | Indikator KPI |
| `kpi_employees` | Data KPI per karyawan |
| `notifications` | In-app notifications |
| `audit_logs` | Audit trail seluruh aktivitas |
| `trusted_devices` | Device terpercaya untuk 2FA |
| `updates` | Riwayat update aplikasi |
| `wa_messages` | Log pesan WhatsApp |

---

## 7. MODUL APLIKASI

### 7.1 Modul Presensi

**Cara Kerja:**
1. Karyawan buka halaman presensi (`/karyawan/presensi`)
2. Sistem deteksi wajah via `face-api.js` (TinyFaceDetector)
3. Sistem ambil lokasi GPS via browser Geolocation API
4. Validasi radius lokasi vs lokasi kantor (`pengaturanumum`)
5. Presensi tersimpan dengan foto, koordinat, dan timestamp
6. Notifikasi WA dikirim ke atasan jika terlambat

**Jenis Presensi:**
- `h` — Hadir
- `i` — Izin
- `s` — Sakit
- `c` — Cuti
- `a` — Alpha

**File Terkait:**
- Controller: `PresensiKaryawanController`, `PresensiController`, `PresensiistirahatController`
- Model: `Presensi`, `Facerecognition`, `Pengaturanumum`
- View: `resources/views/presensi/`
- API: `app/Http/Controllers/Api/PresensiKaryawanController.php`

**Public Kiosk:**
- URL: `/public/kiosk`
- Tidak memerlukan login
- Face recognition langsung matching ke semua karyawan
- Cocok untuk mesin absensi berbasis browser

### 7.2 Modul Izin

**Jenis Izin:**

| Jenis | Kode | Controller | Route Prefix |
|-------|------|------------|--------------|
| Izin Absen | `IA` | `IzinabsenController` | `/izinabsen` |
| Izin Sakit | `IS` | `IzinsakitController` | `/izinsakit` |
| Izin Cuti | `IC` | `IzincutiController` | `/izincuti` |
| Izin Dinas | `ID` | `IzindinasController` | `/izindinas` |

**Alur Pengajuan:**
```
Karyawan Ajukan → Approval Layer 1 → Approval Layer 2 → ... → DISETUJUI/DITOLAK
                                                                       │
                                                        Email Notifikasi ke Karyawan
                                                        In-app Notification
                                                        Update Status Presensi
```

**Approval Layer Configuration:**
- Konfigurasi di `/approvallayer`
- Berbasis Role + Departemen + Jabatan + Cabang
- Multi-level hingga tidak terbatas
- Skip level jika tidak ada rule

**Notifikasi Email:**
- Disetujui → Subject: "📋 Izin [Type] Anda Disetujui ✅"
- Ditolak → Subject: "📋 Izin [Type] Anda Ditolak ❌"
- Template: `resources/views/emails/approval-status.blade.php`
- Class: `App\Notifications\ApprovalStatusNotification`

### 7.3 Modul Penggajian

**Komponen Gaji:**
1. **Gaji Pokok** — Diinput per karyawan dengan tanggal berlaku
2. **Tunjangan** — Multiple jenis tunjangan per karyawan
3. **BPJS** — Kesehatan & Tenaga Kerja
4. **Denda** — Potongan karena pelanggaran
5. **Penyesuaian** — Koreksi/bonus manual

**Slip Gaji:**
- Generate PDF via DomPDF
- Akses karyawan: `/slipgaji`
- Format: Per bulan/tahun

### 7.4 Modul Rekrutmen

**Alur Rekrutmen:**
```
Buat Lowongan → Terima Lamaran → Review CV → Interview → Konfirmasi → 
Lulus/Tidak Lulus → Onboarding
```

**Status Pelamar:**
- `pending` → Baru masuk
- `reviewed` → Sedang direview
- `interview` → Dipanggil interview
- `accepted` → Diterima
- `rejected` → Ditolak

**Notifikasi:**
- Email otomatis ke pelamar saat status berubah
- WA notification via gateway
- In-app notification untuk HR

### 7.5 Modul Aset

**Fitur:**
- Master data aset dengan kategori
- Import/Export Excel
- Peminjaman aset dengan approval
- Transaksi aset (masuk/keluar)
- Tracking stok aset

### 7.6 Modul KPI

**Komponen:**
- **Periode** — Kuartal/semester/tahunan
- **Indikator** — Per jabatan atau global
- **Target** — Set per karyawan per periode
- **Realisasi** — Input aktual pencapaian
- **Skor** — Kalkulasi otomatis

### 7.7 Modul IT Ticket

**Alur:**
```
Karyawan Buat Tiket → IT Staff Terima → Response/Update Status → Selesai
```

**Prioritas:** `critical` | `high` | `medium` | `low`

**Notifikasi:** Real-time via in-app + email ke IT Staff

### 7.8 Modul Lembur

**Fitur:**
- Pengajuan lembur oleh karyawan
- Approval atasan
- Presensi lembur dengan GPS
- Kalkulasi jam lembur
- Laporan lembur

---

## 8. MANAJEMEN PENGGUNA & ROLES

### Daftar Roles

| Role | Deskripsi | Level Akses |
|------|-----------|-------------|
| `super admin` | Administrator penuh | Semua fitur |
| `head` | Kepala divisi/departemen | Approval + report |
| `hrd` | HR & Admin | Kelola karyawan, izin, penggajian |
| `hr staff` | Staff HR | Operasional HR |
| `head cso` | Head Customer Service | Khusus CSO |
| `head business` | Head Business Unit | Khusus BU |
| `direktur utama` | Direktur | View report, approval |
| `finance` | Finance | Akses penggajian |
| `it staff` | Staff IT | IT Ticket management |
| `karyawan` | Karyawan biasa | Self-service |

### Permission Groups

Permission dikelompokkan berdasarkan modul:
- `Presensi` — presensi.index, presensi.create, dll
- `Izin` — izinabsen.*, izincuti.*, izinsakit.*, izindinas.*
- `Karyawan` — karyawan.index, karyawan.create, dll
- `Penggajian` — gajipokok.*, tunjangan.*, slipgaji.*
- `User` — users.index, users.create, users.admin (khusus admin)
- `Laporan` — laporan.*
- Dan 20+ grup lainnya

### Permission Khusus: `users.admin`

Permission `users.admin` mengontrol akses ke manajemen user admin (non-karyawan):
- Tanpa permission ini: hanya bisa kelola "Users Karyawan"
- Dengan permission ini: bisa kelola semua user termasuk admin

**Default assignment:**
- ✅ `super admin` — Ya
- ✅ `head` — Ya
- ❌ `hrd` — Tidak (harus di-grant manual via UI)

**Konfigurasi:** `/roles/{id}/createrolepermission`

### Struktur User ↔ Karyawan

```
users (login account)
  └── userkaryawan (mapping)
        └── karyawan (profile data)
              ├── jabatan
              ├── departemen
              └── cabang
```

---

## 9. API ENDPOINTS

### Karyawan Mobile API (`/api/karyawan/`)

**Public:**
```text
POST   /api/karyawan/login                 Login karyawan (mengembalikan Sanctum token)
```

**Protected (Membutuhkan Bearer Token Sanctum):**
```text
POST   /api/karyawan/logout                Logout (Hapus token)
GET    /api/karyawan/dashboard             Data dashboard utama
GET    /api/karyawan/presensi              Riwayat presensi
GET    /api/karyawan/rekap                 Rekap presensi bulanan
GET    /api/karyawan/lembur                Riwayat lembur
GET    /api/karyawan/pengajuan-izin        Riwayat pengajuan izin
GET    /api/karyawan/profil                Data profil karyawan
PUT    /api/karyawan/profil                Update profil karyawan
GET    /api/karyawan/notifikasi            Daftar notifikasi
POST   /api/karyawan/notifikasi/read-all   Tandai semua notifikasi dibaca

# Face Recognition
GET    /api/karyawan/face                  Ambil descriptor wajah yang tersimpan
POST   /api/karyawan/face                  Simpan data wajah baru
DELETE /api/karyawan/face                  Hapus semua data wajah
DELETE /api/karyawan/face/{id}             Hapus data wajah spesifik

# Aksi Presensi
GET    /api/karyawan/presensi/info         Info presensi hari ini
GET    /api/karyawan/presensi/jam-kerja    Daftar jam kerja
POST   /api/karyawan/presensi              Presensi (Masuk/Pulang)
POST   /api/karyawan/presensi/istirahat    Presensi (Mulai/Selesai Istirahat)
```

### ADMS & Mesin Fingerprint

**Tanpa Middleware Throttle:**
```text
GET|POST /api/presensimachine              Resource API Presensi Machine
POST   /api/presensi/log                   Simpan log presensi
POST   /api/presensi/receive-data          Menerima data dari mesin REVO
POST   /api/adms/capture                   Menerima data mentah dari mesin ADMS
```

### Update System

**Public:**
```text
GET    /api/update/check                   Cek versi terbaru
GET    /api/update/version                 Ambil current version
GET    /api/update/list                    Daftar update tersedia
GET    /api/update/{version}               Lihat detail versi
```

**Protected:**
```text
GET    /api/update/history                 Riwayat update
GET    /api/update/log/{id}                Lihat log instalasi
GET    /api/update/status/{logId}          Status progress update
POST   /api/update/{version}/download      Download package update
POST   /api/update/{version}/install       Install package update
POST   /api/update/{version}/update-now    Langsung update
```

### Request/Response Format

**Login Request:**
```json
{
  "id_user": "username_atau_email",
  "password": "password",
  "device_name": "Android App"
}
```

**Login Response:**
```json
{
  "success": true,
  "token": "sanctum_token",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "karyawan": {
    "nik": "DX001",
    "nama_karyawan": "John Doe",
    "kode_jabatan": "J001",
    "nama_jabatan": "Staff IT"
  }
}
```

---

## 10. AUTENTIKASI & KEAMANAN

### Metode Autentikasi

| Metode | Digunakan Untuk |
|--------|----------------|
| Session-based (Breeze) | Web browser |
| Sanctum Token | Mobile app & API |
| Two Factor (2FA) | Login web (OTP via email) |

### Two Factor Authentication (2FA)

1. Login dengan username/password
2. Jika 2FA aktif → redirect ke `/two-factor-challenge`
3. Kode OTP dikirim via email
4. Masukkan kode OTP
5. Opsi "Remember device" untuk skip 2FA di device yang sama
6. Trusted devices disimpan di tabel `trusted_devices`

### Fitur Keamanan Aktif

- ✅ **CSRF Protection** — Middleware aktif di semua route web
- ✅ **Password Hashing** — bcrypt via `Hash::make()`
- ✅ **Input Validation** — Laravel Validation Rules
- ✅ **XSS Protection** — Blade template auto-escape `{{ }}`
- ✅ **SQL Injection Prevention** — Eloquent ORM + parameter binding
- ✅ **Route Encryption** — ID sensitif di-encrypt via `Crypt::encrypt()`
- ✅ **Role-based Authorization** — Spatie Permission
- ✅ **Audit Log** — Semua aksi penting tercatat di `audit_logs`
- ✅ **Rate Limiting** — Login throttle via `LoginRequest`
- ✅ **Trusted Device** — Persistent 2FA bypass per device

### Audit Log

Aktivitas yang dicatat:
- Login/Logout
- Create/Update/Delete data karyawan
- Approval/Reject izin
- Perubahan permission & role
- Akses ke data sensitif

Akses: `/audit` (super admin & hrd only)

---

## 11. SISTEM NOTIFIKASI

### Jenis Notifikasi

| Notifikasi | Channel | Trigger |
|-----------|---------|---------|
| Approval Status | Database + Email | Izin disetujui/ditolak |
| New IT Ticket | Database + Email | Tiket IT baru |
| New Recruitment | Database + Email | Lamaran masuk |
| Pengumuman | Database | Pengumuman baru |
| WA Message | WhatsApp | Presensi terlambat, dll |
| Push Notification | OneSignal | Pengumuman mobile |

### Notification Classes

```
app/Notifications/
├── ApprovalStatusNotification.php  # Status izin (approve/reject)
├── NewItTicketNotification.php     # IT ticket baru
├── NewRecruitmentNotification.php  # Pelamar baru
└── PengumumanNotification.php      # Pengumuman
```

### Email Template

```
resources/views/emails/
├── approval-status.blade.php       # Email status izin
└── ...
```

### In-App Notification Center

- URL: `/notification`
- API: `GET /api/notification/latest` (untuk navbar)
- Mark as read: `POST /notification/{id}/read`
- Mark all read: `POST /notification/read-all`

---

## 12. QUEUE & BACKGROUND JOBS

### Job Classes

```
app/Jobs/
└── SendWaMessage.php    # Kirim pesan WhatsApp asynchronous
```

### Cara Kerja Queue

```php
// Dispatch job
SendWaMessage::dispatch($phone, $message)->delay(now()->addSeconds(5));

// Worker command
php artisan queue:work database --tries=3 --timeout=60
```

### Failed Jobs

```bash
# Lihat failed jobs
php artisan queue:failed

# Retry semua
php artisan queue:retry all

# Flush failed jobs
php artisan queue:flush
```

---

## 13. LAPORAN & EXPORT

### Laporan Tersedia

| Laporan | Format | Route |
|---------|--------|-------|
| Rekap Presensi Karyawan | Excel | `/laporan/presensi-karyawan` |
| Rekap Presensi Bulanan | Excel | `/laporan/presensi` |
| Data Karyawan | Excel | `/laporan/karyawan` |
| Slip Gaji Massal | PDF | `/slipgaji/export` |
| Aset | Excel | `/manajemen-aset/export` |
| Audit Log | Excel | `/audit/export` |

### Export Classes

```
app/Exports/
├── GajiExport.php               # Export data gaji
├── KaryawanExport.php           # Export data karyawan
├── PresensiExport.php           # Export presensi
├── PresensiKaryawanExport.php   # Export presensi per karyawan
├── TemplateKaryawanExport.php   # Template import karyawan
├── AssetExport.php              # Export aset
└── AssetTemplateExport.php      # Template import aset
```

### Import

- Import karyawan via Excel template
- Download template: `/karyawan/template`
- Import: `/karyawan/import`
- Kelas: `app/Imports/KaryawanImport.php`

---

## 14. INTEGRASI WHATSAPP GATEWAY

### Konfigurasi

WA Gateway menggunakan Fonnte API atau custom gateway:

```env
WA_GATEWAY_URL=https://api.fonnte.com/send
WA_GATEWAY_TOKEN=your_token
```

### Fitur WA Notification

- Notifikasi presensi terlambat
- Status approval izin
- Reminder karyawan
- Broadcast pengumuman

### Testing Gateway

```bash
php artisan test:gateway-info-device
```

### WA Gateway Management

- URL: `/wagateway`
- Kelola multiple gateway
- Test koneksi
- Log pesan terkirim (`wa_messages`)

---

## 15. SISTEM UPDATE OTOMATIS

### Cara Kerja

```
1. Cek versi terbaru via API server update
2. Download package update (.zip)
3. Backup database
4. Ekstrak file update
5. Jalankan migration baru
6. Jalankan seeder baru
7. Clear cache
8. Update VERSION file
```

### Commands

```bash
# Cek update tersedia
php artisan update:fix-stuck --force

# Melalui web UI
URL: /update
```

### Update Service

```
app/Services/UpdateService.php
- checkUpdate()
- downloadUpdate()
- installUpdate()
- backupDatabase()
- rollbackUpdate()
```

---

## 16. ARTISAN COMMANDS

| Command | Deskripsi | Scheduled |
|---------|-----------|-----------|
| `brain:scan` | Analisis project Laravel Brain | Tidak |
| `brain:export-context` | Export AI context | Tidak |
| `brain:generate-rules` | Generate AI rules | Tidak |
| `kpi:reset-tables` | Reset tabel KPI | Tidak |
| `update:fix-stuck` | Fix update yang stuck | Tidak |
| `test:onesignal` | Test koneksi OneSignal | Tidak |
| `test:gateway-info-device` | Test WA gateway | Tidak |

### Scheduled Tasks (Kernel.php)

```php
// Cek di app/Console/Kernel.php
$schedule->command('...')->daily();
```

---

## 17. DIAGRAM ALUR PROSES

### Alur Presensi Masuk

```
Karyawan Buka Halaman Presensi
        │
        ▼
Load Model Face Recognition (face-api.js)
        │
        ▼
Kamera Aktif → Deteksi Wajah
        │
        ▼ (wajah terdeteksi)
Ambil Lokasi GPS (Geolocation API)
        │
        ▼
Validasi Radius (± X meter dari kantor)
        │
        ├── Diluar Radius → Error Message
        │
        ▼ (dalam radius)
Kirim ke Server: foto + koordinat + timestamp
        │
        ▼
Server Validasi (jam kerja, jadwal, duplikat)
        │
        ▼
Simpan Presensi → Update Status Karyawan
        │
        ▼
Kirim WA/Notifikasi (jika terlambat)
        │
        ▼
✅ Presensi Berhasil
```

### Alur Approval Izin

```
Karyawan Submit Izin
        │
        ▼
Cek Approval Layer Configuration
(berdasarkan jabatan + departemen + cabang)
        │
        ▼
Notifikasi ke Approver Layer 1
        │
        ▼
Approver Layer 1 Review
        │
        ├── TOLAK → Email ke Karyawan (❌ Ditolak)
        │           Update Status = 2
        │
        ▼ (Setujui)
Ada Layer Berikutnya?
        │
        ├── YA → Increment approval_step
        │        Notifikasi Approver Layer 2
        │        (ulangi proses)
        │
        ▼ (Tidak ada layer berikutnya)
FINAL APPROVED
        │
        ▼
Email ke Karyawan (✅ Disetujui)
        │
        ▼
Generate Record Presensi (izin/sakit/cuti/dinas)
        │
        ▼
✅ Selesai
```

---

## 18. PANDUAN TROUBLESHOOTING

### Error Umum & Solusi

#### 1. "Class not found" setelah update
```bash
composer dump-autoload
php artisan optimize:clear
```

#### 2. Notifikasi email tidak terkirim
```bash
# Cek konfigurasi mail
php artisan config:clear
php artisan tinker
Mail::raw('test', fn($m) => $m->to('test@example.com')->subject('Test'));

# Cek queue
php artisan queue:work --once -v
```

#### 3. Queue tidak berjalan
```bash
# Restart queue
php artisan queue:restart
php artisan queue:work database --tries=3

# Cek failed jobs
php artisan queue:failed

# Clear stuck jobs
php artisan update:fix-stuck --force
```

#### 4. Presensi face recognition gagal
- Pastikan model face-api.js ter-load (`/models/` directory)
- Cek kamera permissions browser
- Pastikan HTTPS jika di production (Geolocation API butuh HTTPS)
- Cek file descriptor di `facerecognition` table
- Pastikan foto karyawan ada di storage

#### 5. GPS tidak akurat
- Cek browser geolocation permissions
- Pastikan `maximumAge: 60000` di konfigurasi
- Cek radius di pengaturan umum

#### 6. Permission error setelah seeder
```bash
php artisan permission:cache-reset
php artisan cache:clear
```

#### 7. Update aplikasi stuck
```bash
php artisan update:fix-stuck --force
```

#### 8. Storage symlink tidak ada
```bash
php artisan storage:link
chmod -R 775 storage/
```

### Log Files

```bash
# Application log
tail -f storage/logs/laravel.log

# Queue worker log (jika pakai supervisor)
tail -f /var/log/supervisor/hris-worker.log

# Nginx access log
tail -f /var/log/nginx/access.log
```

### Performance Tips

1. **Cache semua di production:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

2. **Optimize Composer:**
```bash
composer install --optimize-autoloader --no-dev
```

3. **Database indexing** — Pastikan kolom yang sering di-query sudah ada index (NIK, kode_cabang, kode_dept, tanggal)

4. **Queue untuk operasi berat** — Email, WA, PDF generation sebaiknya via queue

5. **Image optimization** — Foto presensi dikompres via `intervention/image`

---

## 📞 SUPPORT & KONTAK

- **Aplikasi:** HRIS DIDIMAX v3.0.6
- **Framework:** Laravel 10.10
- **Repository:** https://github.com/garudabdg/hris-production
- **Last Updated:** 18 Mei 2026

---

*Dokumentasi ini di-generate secara otomatis berdasarkan analisis kode oleh Laravel Brain dan GitHub Copilot.*
