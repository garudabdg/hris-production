# Fitur Audit Log

## Deskripsi
Sistem audit log untuk mencatat semua aktivitas yang dilakukan oleh admin/user di aplikasi HRIS. Fitur ini akan secara otomatis mencatat:
- **Login & Logout** - Waktu login dan logout user
- **Create, Update, Delete** - Semua perubahan data (karyawan, presensi, gaji, dll)
- **IP Address & User Agent** - Informasi device dan lokasi akses
- **Module & Description** - Detail aktivitas yang dilakukan

## Struktur Database

### Tabel: `audit_logs`
```sql
- id (bigint, primary key)
- user_id (bigint, nullable, foreign key to users)
- action (varchar 100) - login, logout, create, update, delete, dll
- module (varchar 100, nullable) - karyawan, presensi, gaji, dll
- description (text, nullable) - Detail deskripsi aktivitas
- ip_address (varchar 45, nullable) - IP address user
- user_agent (text, nullable) - Browser/device information
- login_at (timestamp, nullable) - Waktu login
- logout_at (timestamp, nullable) - Waktu logout
- created_at (timestamp)
- updated_at (timestamp)
```

## File-file yang Dibuat

### 1. Migration
- `database/migrations/2026_04_11_185049_create_audit_logs_table.php`

### 2. Model
- `app/Models/AuditLog.php`
  - Helper methods: `log()`, `logLogin()`, `logLogout()`
  - Scopes: `byUser()`, `byAction()`, `byModule()`, `byDateRange()`

### 3. Middleware
- `app/Http/Middleware/AuditMiddleware.php`
  - Otomatis mencatat semua POST, PUT, PATCH, DELETE requests
  - Registered di `app/Http/Kernel.php` dalam web middleware group

### 4. Controller
- `app/Http/Controllers/AuditController.php`
  - `index()` - Menampilkan daftar audit logs dengan filter
  - `show($id)` - Detail satu audit log
  - `export()` - Export audit logs ke CSV
  - `cleanup()` - Hapus log lama (maintenance)

### 5. Views
- `resources/views/audit/index.blade.php`
  - Filter: User, Action, Module, Date Range, Search
  - Statistics cards: Total Logs, Today Logs, Total Users, Login Today
  - Export CSV button
  - Pagination

### 6. Routes
```php
Route::middleware('role:super admin')->prefix('audit')->group(function () {
    Route::get('/', 'index')->name('audit.index');
    Route::get('/export', 'export')->name('audit.export');
    Route::post('/cleanup', 'cleanup')->name('audit.cleanup');
    Route::get('/{id}', 'show')->name('audit.show');
});
```

## Cara Penggunaan

### 1. Otomatis (via Middleware)
Semua aktivitas POST, PUT, PATCH, DELETE otomatis tercatat:
```php
// Tidak perlu kode tambahan, sudah otomatis!
// Contoh: ketika update karyawan
Route::put('/karyawan/{id}', 'update'); // Otomatis tercatat!
```

### 2. Manual Logging
Jika ingin log aktivitas tertentu secara manual:

```php
use App\Models\AuditLog;

// Log aktivitas umum
AuditLog::log('view', 'laporan', 'User viewed salary report');

// Log login (otomatis dipanggil di AuthenticatedSessionController)
AuditLog::logLogin();

// Log logout (otomatis dipanggil di AuthenticatedSessionController)
AuditLog::logLogout();
```

### 3. Query dengan Scope
```php
// Filter by user
$logs = AuditLog::byUser(78)->get();

// Filter by action
$logs = AuditLog::byAction('login')->get();

// Filter by module
$logs = AuditLog::byModule('karyawan')->get();

// Filter by date range
$logs = AuditLog::byDateRange('2026-04-01', '2026-04-30')->get();

// Kombinasi filter
$logs = AuditLog::byUser(78)
    ->byAction('create')
    ->byModule('karyawan')
    ->orderBy('created_at', 'DESC')
    ->paginate(20);
```

## Akses Menu
- Menu terletak di **Sidebar → Utilities → Audit Log**
- Hanya bisa diakses oleh user dengan role **"super admin"**
- URL: `/audit`

## Fitur Halaman Audit Log

### 1. Statistics Cards
- **Total Logs** - Total semua audit logs
- **Logs Hari Ini** - Total logs hari ini
- **Total Users** - Jumlah user yang tercatat
- **Login Hari Ini** - Total login hari ini

### 2. Filter
- **User** - Filter berdasarkan nama user (select2)
- **Action** - Filter berdasarkan jenis aksi (login, logout, create, update, delete)
- **Module** - Filter berdasarkan module (karyawan, presensi, gaji, dll)
- **Dari Tanggal** - Filter tanggal mulai
- **Sampai Tanggal** - Filter tanggal akhir
- **Search** - Pencarian di description atau IP address

### 3. Export
- Export filtered data ke format CSV
- Otomatis generate filename dengan timestamp
- URL: `/audit/export?user_id=78&action=login&...`

### 4. Table Columns
- ID
- User (nama + avatar icon)
- Action (badge berwarna sesuai jenis)
- Module (badge secondary)
- Description (detail aktivitas)
- IP Address (format code)
- Login At (waktu login dengan icon)
- Logout At (waktu logout dengan icon)
- Waktu (created_at + relative time)

## Badge Colors
```php
'login' => 'success' (hijau)
'logout' => 'secondary' (abu-abu)
'create' => 'info' (biru)
'update' => 'warning' (kuning)
'delete' => 'danger' (merah)
```

## Maintenance

### Cleanup Old Logs
Untuk menghapus log lama (misalnya lebih dari 90 hari):
```php
POST /audit/cleanup
Parameter: days=90 (default)
```

Atau via Tinker:
```bash
php artisan tinker
AuditLog::where('created_at', '<', now()->subDays(90))->delete();
```

### Scheduled Cleanup (Optional)
Tambahkan di `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // Cleanup audit logs older than 6 months, every month
    $schedule->call(function () {
        AuditLog::where('created_at', '<', now()->subMonths(6))->delete();
    })->monthly();
}
```

## Testing

### 1. Test Login Logging
```bash
# Login via browser, kemudian cek database
SELECT * FROM audit_logs WHERE action = 'login' ORDER BY id DESC LIMIT 5;
```

### 2. Test Action Logging
```bash
# Buat/edit/hapus data karyawan, kemudian cek
SELECT * FROM audit_logs WHERE module = 'karyawan' ORDER BY id DESC LIMIT 5;
```

### 3. Test via Tinker
```bash
php artisan tinker

# Create sample log
AuditLog::log('test', 'testing', 'This is a test log');

# Check logs
AuditLog::latest()->first();

# Test login log
AuditLog::logLogin();

# Test logout log
AuditLog::logLogout();
```

## Security Notes

1. **Foreign Key Constraint**: user_id menggunakan `ON DELETE SET NULL` agar log tetap ada meskipun user dihapus
2. **Middleware Skip**: Audit index route di-skip untuk menghindari recursive logging
3. **Silent Fail**: Error pada audit logging tidak mengganggu normal application flow
4. **Super Admin Only**: Hanya super admin yang bisa melihat audit logs
5. **No Edit/Delete**: Tidak ada fitur edit/delete individual log untuk menjaga integritas audit trail

## Performance Considerations

1. **Index**: Database index pada `user_id`, `action`, `created_at` untuk performa query
2. **Pagination**: Default 20 records per page
3. **Selective Logging**: Hanya log POST, PUT, PATCH, DELETE (tidak log GET/view)
4. **Skip Routes**: Beberapa route di-skip untuk menghindari log yang tidak perlu

## Troubleshooting

### Issue: Audit logs tidak tercatat
**Solusi:**
1. Cek middleware sudah registered di `app/Http/Kernel.php`
2. Pastikan migration sudah dijalankan: `php artisan migrate`
3. Cek error log: `tail -f storage/logs/laravel.log`

### Issue: Error foreign key constraint
**Solusi:**
1. Pastikan user_id yang digunakan ada di tabel users
2. Atau set user_id = null untuk log tanpa user

### Issue: Terlalu banyak logs
**Solusi:**
1. Jalankan cleanup: `POST /audit/cleanup` dengan parameter `days`
2. Setup scheduled cleanup di Kernel.php
3. Adjust `$skipRoutes` di AuditMiddleware untuk skip lebih banyak route

## Future Enhancements

1. ✅ Basic logging untuk login/logout
2. ✅ Logging untuk CRUD operations
3. ✅ Filter dan search
4. ✅ Export to CSV
5. 🔄 Advanced filtering (IP range, user agent parsing)
6. 🔄 Real-time notifications untuk aktivitas critical
7. 🔄 Dashboard visualization (charts)
8. 🔄 Anomaly detection (unusual activities)
9. 🔄 Backup audit logs to external storage

## Author
Created on April 11, 2026
