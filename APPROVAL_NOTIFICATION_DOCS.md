# Dokumentasi: Approval Status Notification System

## Deskripsi
Sistem notifikasi otomatis yang mengirimkan notif ke karyawan ketika status approval (Izin Sakit, Izin Absen, Izin Cuti, Izin Dinas) berubah (disetujui/ditolak).

## File yang Dibuat/Dimodifikasi

### 1. Notification Class
**File:** `app/Notifications/ApprovalStatusNotification.php`

Notification class yang handle pengiriman notif melalui:
- Database (tersimpan di tabel `notifications`)
- Email (jika user memiliki email)

**Parameters:**
- `$approvalType`: Tipe approval (IZIN_SAKIT, IZIN_ABSEN, IZIN_CUTI, IZIN_DINAS)
- `$approvalCode`: Kode izin (misal: IS26040001)
- `$status`: Status approval (1=Disetujui, 2=Ditolak)
- `$approverName`: Nama person yang approve/reject
- `$notes`: Catatan tambahan (optional)

### 2. Email Template
**File:** `resources/views/emails/approval-status.blade.php`

Template email yang dikirim ke karyawan dengan informasi:
- Status approval (Disetujui/Ditolak)
- Kode izin
- Nama approver
- Catatan/notes jika ada

### 3. Controller Updates
**File:** `app/Http/Controllers/IzinsakitController.php` (dan controller izin lainnya)

Update di method `storeApprove()`:
- Ketika approve berhasil → kirim notif dengan status=1 (Disetujui)
- Ketika reject → kirim notif dengan status=2 (Ditolak)

**Contoh kode:**
```php
$karyawanUser = User::whereHas('userkaryawan', function($q) use ($nik) {
    $q->where('nik', $nik);
})->first();

if ($karyawanUser) {
    $karyawanUser->notify(new ApprovalStatusNotification(
        'IZIN_SAKIT',
        $kode_izin_sakit,
        1, // status: approved
        $approvalAdmin->name
    ));
}
```

### 4. Helper Functions
**File:** `app/Helpers/NotificationHelper.php`

Helper functions untuk query approval notifications:
- `getApprovalNotifications($userId, $limit)` - Get approval notifs
- `countUnreadApprovalNotifications($userId)` - Count unread notifs
- `getLatestApprovalNotification($userId)` - Get latest notif

## Cara Implementasi untuk Controller Lain

Untuk controller `Izinabsen`, `Izincuti`, `Izindinas`, ikuti pattern yang sama:

1. **Import Notification Class:**
```php
use App\Notifications\ApprovalStatusNotification;
```

2. **Di method storeApprove():**
```php
// Setelah update status ke APPROVED atau REJECTED
$karyawanUser = User::whereHas('userkaryawan', function($q) use ($nik) {
    $q->where('nik', $nik);
})->first();

if ($karyawanUser) {
    $karyawanUser->notify(new ApprovalStatusNotification(
        'IZIN_ABSEN', // atau 'IZIN_CUTI', 'IZIN_DINAS'
        $kode_izin,   // atau $kode_izin_cuti, $kode_izin_dinas
        $status,      // 1 untuk approve, 2 untuk reject
        $approverName
    ));
}
```

## Bagaimana Notifikasi Ditampilkan

### 1. Database Storage
Notifikasi tersimpan di tabel `notifications` dengan struktur:
```
- id: UUID
- notifiable_id: User ID
- notifiable_type: App\Models\User
- type: ApprovalStatusNotification
- data: JSON array berisi approval details
- read_at: Waktu dibaca (null = belum dibaca)
- created_at: Waktu dibuat
```

### 2. Menampilkan di View

**Di Dashboard:**
```php
$approvalNotifs = getApprovalNotifications(auth()->user()->id, 5);
$unreadCount = countUnreadApprovalNotifications(auth()->user()->id);
```

**Di Notification Center:**
```blade.php
@foreach($approvalNotifs as $notif)
    <div class="notification @if($notif['is_read']) read @endif">
        <h6>{{ $notif['title'] }}</h6>
        <p>{{ $notif['message'] }}</p>
        <small>{{ $notif['created_at']->diffForHumans() }}</small>
    </div>
@endforeach
```

### 3. Email
Email otomatis dikirim ke email user dengan:
- Subject: "[IZIN_TYPE] - [STATUS]"
- Body: Detail approval dengan link ke dashboard

## Queue Configuration
Notifikasi menggunakan `ShouldQueue` interface, jadi perlu configure queue di `.env`:
```
QUEUE_CONNECTION=database
# atau sync (synchronous, instant)
```

## Testing
Untuk test notifikasi:
```php
$user = User::find(1);
$user->notify(new ApprovalStatusNotification(
    'IZIN_SAKIT',
    'IS26040001',
    1,
    'Admin Name'
));
```

Check database:
```sql
SELECT * FROM notifications WHERE notifiable_id = 1;
```

## TODO - Implementasi di Controller Lain
Setelah verify di IzinsakitController, implementasi sama untuk:
- [ ] IzinabsenController
- [ ] IzincutiController
- [ ] IzindinasController

Pattern: Cari method `storeApprove()` di masing-masing controller, tambah notify code sebelum return success.
