# Permission: users.admin

## Deskripsi
Permission `users.admin` mengontrol akses untuk mengelola user admin (non-karyawan) dalam sistem HRIS.

## Fitur yang Dikontrol
- View tab "Users Admin" di halaman Users
- Create user dengan role admin (super admin, head, hrd, direktur, finance, dll)
- Edit user admin
- Delete user admin

## Default Assignment
- ✅ **super admin** - Memiliki permission secara default
- ✅ **head** - Memiliki permission secara default
- ❌ **hrd** - TIDAK memiliki permission secara default
- ❌ **hr staff** - TIDAK memiliki permission secara default
- ❌ **Role lainnya** - TIDAK memiliki permission secara default

## Cara Mengaktifkan untuk Role Lain

### Via Web UI (Recommended)
1. Login sebagai super admin
2. Buka menu **Settings > Roles**
3. Klik tombol **Edit** pada role yang ingin diberi akses (misal: HRD)
4. Di halaman "Role Permission", cari group **"Users"**
5. Centang checkbox **"users.admin"**
6. Klik **Simpan**

URL: `https://hris.didimax.online/roles/{encrypted_role_id}/createrolepermission`

### Via Seeder/Tinker
```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$permission = Permission::where('name', 'users.admin')->first();
$role = Role::where('name', 'hrd')->first();

if ($role && $permission) {
    $role->givePermissionTo($permission);
    echo "Permission users.admin granted to {$role->name}";
}
```

## Behavior

### Dengan Permission `users.admin`:
- ✅ Bisa lihat tab "Users Admin"
- ✅ Bisa lihat tab "Users Karyawan"
- ✅ Bisa create user admin
- ✅ Bisa edit user admin
- ✅ Bisa delete user admin
- ✅ Bisa manage user karyawan

### Tanpa Permission `users.admin`:
- ❌ Tab "Users Admin" hidden
- ✅ Bisa lihat tab "Users Karyawan" (auto-active)
- ❌ Tidak bisa create user admin
- ❌ Tidak bisa edit user admin (tombol hidden)
- ❌ Tidak bisa delete user admin (tombol hidden)
- ✅ Bisa manage user karyawan (sesuai permission users.index)

## Testing

### Test HRD tanpa permission:
1. Login sebagai user dengan role HRD
2. Buka menu Users
3. Harus otomatis ke tab "Users Karyawan"
4. Tab "Users Admin" tidak terlihat
5. Button "Tambah User" tidak terlihat

### Test HRD dengan permission:
1. Berikan permission users.admin ke role HRD
2. Login sebagai user dengan role HRD
3. Buka menu Users
4. Tab "Users Admin" terlihat
5. Bisa create/edit/delete user admin

## Catatan Keamanan
- Permission ini SANGAT SENSITIF karena mengontrol akses ke user admin
- Hanya berikan ke role yang dipercaya
- Super admin selalu memiliki akses penuh (bypass permission check via isSuperAdmin())
- Permission ini bekerja bersama dengan permission `users.index` untuk akses dasar ke halaman Users

## File yang Dimodifikasi
- `app/Http/Controllers/UserController.php` - Logika permission check
- `resources/views/settings/users/index.blade.php` - UI conditional rendering
- `database/seeders/UsersAdminPermissionSeeder.php` - Seeder untuk create permission

## Migration/Rollback
Jika ingin menghapus permission:
```php
php artisan tinker
Permission::where('name', 'users.admin')->delete();
```

Jika ingin menambahkan ulang:
```php
php artisan db:seed --class=UsersAdminPermissionSeeder
```
