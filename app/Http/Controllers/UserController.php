<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Userkaryawan;
use App\Models\Cabang;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $userType = $request->user_type ?? 'biasa';
        
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();

        // Jika tidak punya users.admin, paksa ke tab karyawan
        if (!$authUser->can('users.admin')) {
            $userType = 'karyawan';
        }

        // Ambil cabang & departemen yang boleh dilihat oleh auth user
        $allowedCabangs    = $authUser->getCabangCodes();
        $allowedDepartemens = $authUser->getDepartemenCodes();

        $users = User::with(['roles', 'cabangs', 'departemens'])
            ->when($request->name, function ($query, $name) {
                return $query->where('name', 'like', '%' . $name . '%');
            })
            ->when($request->role_id, function ($query, $role_id) {
                return $query->whereHas('roles', function ($subQuery) use ($role_id) {
                    $subQuery->where('role_id', $role_id);
                });
            })
            ->leftjoin('users_karyawan', 'users.id', '=', 'users_karyawan.id_user')
            ->when($userType == 'karyawan', function ($query) use ($authUser, $allowedCabangs, $allowedDepartemens, $request) {
                // Filter hanya users yang punya relasi dengan users_karyawan
                $query->whereNotNull('users_karyawan.id_user');

                // Scope berdasarkan cabang & departemen yang diizinkan ke auth user
                if (!$authUser->isSuperAdmin()) {
                    if (!empty($allowedCabangs)) {
                        $query->whereHas('userkaryawan.karyawan', function ($q) use ($allowedCabangs) {
                            $q->whereIn('kode_cabang', $allowedCabangs);
                        });
                    }
                    if (!empty($allowedDepartemens)) {
                        $query->whereHas('userkaryawan.karyawan', function ($q) use ($allowedDepartemens) {
                            $q->whereIn('kode_dept', $allowedDepartemens);
                        });
                    }
                }

                // Filter cabang dari request
                if ($request->filled('kode_cabang')) {
                    $query->whereHas('userkaryawan.karyawan', function ($q) use ($request) {
                        $q->where('kode_cabang', $request->kode_cabang);
                    });
                }

                // Filter departemen dari request
                if ($request->filled('kode_dept')) {
                    $query->whereHas('userkaryawan.karyawan', function ($q) use ($request) {
                        $q->where('kode_dept', $request->kode_dept);
                    });
                }
            }, function ($query) use ($authUser) {
                // Filter hanya users yang TIDAK punya relasi dengan users_karyawan
                if (!$authUser->can('users.admin')) {
                    return $query->whereRaw('1 = 0');
                }
                return $query->whereNull('users_karyawan.id_user');
            })
            ->select('users.*', 'users_karyawan.nik')
            ->distinct()
            ->paginate(10);
        
        $users->appends($request->all());

        // User tanpa permission users.admin hanya bisa lihat role karyawan di filter
        if (!$authUser->can('users.admin')) {
            $roles = Role::orderBy('name')->where('name', 'karyawan')->get();
        } else {
            $roles = Role::orderBy('name')->get();
        }

        // Cabang & departemen untuk filter dropdown (sesuai akses auth user)
        $cabangList     = $authUser->getCabang();
        $departemenList = $authUser->getDepartemen();
        
        return view('settings.users.index', compact('users', 'roles', 'cabangList', 'departemenList', 'userType'));
    }

    public function create()
    {
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();
        
        // User tanpa permission users.admin tidak bisa create user admin
        if (!$authUser->can('users.admin')) {
            abort(403, 'Anda tidak memiliki akses untuk membuat user admin.');
        }
        
        $roles = Role::orderBy('name')->where('name', '!=', 'karyawan')->get();
        $cabangs = Cabang::orderBy('kode_cabang')->get();
        $departemens = Departemen::orderBy('kode_dept')->get();
        return view('settings.users.create', compact('roles', 'cabangs', 'departemens'));
    }

    public function edit($id)
{
    $id = Crypt::decrypt($id);
    $user = User::with(['roles', 'cabangs', 'departemens'])->where('id', $id)->first();

    /** @var \App\Models\User $authUser */
    $authUser = auth()->user();
    
    // User tanpa permission users.admin tidak bisa edit user yang bukan karyawan
    $targetUserRole = $user->getRoleNames()->first();
    if (!$authUser->can('users.admin') && $targetUserRole !== 'karyawan') {
        abort(403, 'Anda tidak memiliki akses untuk mengedit user admin.');
    }
    
    $roles = Role::orderBy('name')->where('name', '!=', 'karyawan')->get();
    $cabangs = Cabang::orderBy('kode_cabang')->get();
    $departemens = Departemen::orderBy('kode_dept')->get();
    $userCabangs = $user->cabangs->pluck('kode_cabang')->toArray();
    $userDepartemens = $user->departemens->pluck('kode_dept')->toArray();
    
    // Approval delegation - get all non-karyawan users as potential admin
    $adminUsers = User::role(Role::where('name', '!=', 'karyawan')->pluck('name')->toArray())->orderBy('name')->get();
    $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
    
    return view('settings.users.edit', compact('user', 'roles', 'cabangs', 'departemens', 'userCabangs', 'userDepartemens', 'adminUsers', 'userkaryawan'));
}

    public function store(Request $request)
    {
        $isAdmin = strtolower($request->role) !== 'karyawan';
        $request->validate([
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'password' => \App\Helpers\PasswordHelper::getRules(null, $isAdmin, false, false),
            'role' => 'required'
        ]);

        // Validasi untuk role selain super admin
        $roleName = strtolower($request->role);
        if ($roleName !== 'super admin') {
            $request->validate([
                'cabangs' => 'required|array|min:1',
                'cabangs.*' => 'exists:cabang,kode_cabang',
                'departemens' => 'required|array|min:1',
                'departemens.*' => 'exists:departemen,kode_dept',
            ], [
                'cabangs.required' => 'Minimal 1 cabang harus dipilih',
                'cabangs.min' => 'Minimal 1 cabang harus dipilih',
                'departemens.required' => 'Minimal 1 departemen harus dipilih',
                'departemens.min' => 'Minimal 1 departemen harus dipilih',
            ]);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            $user->assignRole($request->role);

            // Jika role adalah super admin, berikan akses ke semua cabang dan departemen
            if ($roleName === 'super admin') {
                $allCabangs = Cabang::pluck('kode_cabang')->toArray();
                $allDepartemens = Departemen::pluck('kode_dept')->toArray();
                $user->cabangs()->sync($allCabangs);
                $user->departemens()->sync($allDepartemens);
            } else {
                // Sync akses cabang
                if (isset($request->cabangs) && is_array($request->cabangs)) {
                    $user->cabangs()->sync($request->cabangs);
                }

                // Sync akses departemen
                if (isset($request->departemens) && is_array($request->departemens)) {
                    $syncData = [];
                    foreach ($request->departemens as $deptCode) {
                        $subDepts = $request->sub_departemen_access[$deptCode] ?? null;
                        if (!empty($subDepts) && is_array($subDepts)) {
                            $syncData[$deptCode] = ['sub_departemen' => json_encode($subDepts)];
                        } else {
                            $syncData[$deptCode] = ['sub_departemen' => null];
                        }
                    }
                    $user->departemens()->sync($syncData);
                }
            }

            return Redirect::back()->with(['success' => 'Data Berhasil Disimpan']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['eror' => 'Data Gagal Disimpan']);
        }
    }


    public function update($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $user = User::findorFail($id);

        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();
        
        // User tanpa permission users.admin tidak bisa update user yang bukan karyawan
        $targetUserRole = $user->getRoleNames()->first();
        if (!$authUser->can('users.admin') && $targetUserRole !== 'karyawan') {
            abort(403, 'Anda tidak memiliki akses untuk mengedit user admin.');
        }

        $request->validate([
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email',
        ]);

        try {

            if (isset($request->password)) {
                User::where('id', $id)->update([
                    'name' => $request->name,
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ]);
            } else {
                User::where('id', $id)->update([
                    'name' => $request->name,
                    'username' => $request->username,
                    'email' => $request->email,
                ]);
            }

            if (isset($request->role)) {
                $user->syncRoles([]);
                $user->assignRole($request->role);
            }

            // Jika role adalah super admin, berikan akses ke semua cabang dan departemen
            $roleName = isset($request->role) ? strtolower($request->role) : strtolower($user->roles->pluck('name')->first() ?? '');
            
            // Validasi untuk role selain super admin dan karyawan
            if ($roleName !== 'super admin' && $roleName !== 'karyawan') {
                $request->validate([
                    'cabangs' => 'required|array|min:1',
                    'cabangs.*' => 'exists:cabang,kode_cabang',
                    'departemens' => 'required|array|min:1',
                    'departemens.*' => 'exists:departemen,kode_dept',
                ], [
                    'cabangs.required' => 'Minimal 1 cabang harus dipilih',
                    'cabangs.min' => 'Minimal 1 cabang harus dipilih',
                    'departemens.required' => 'Minimal 1 departemen harus dipilih',
                    'departemens.min' => 'Minimal 1 departemen harus dipilih',
                ]);
            }
            
            if ($roleName === 'super admin') {
                $allCabangs = Cabang::pluck('kode_cabang')->toArray();
                $allDepartemens = Departemen::pluck('kode_dept')->toArray();
                $user->cabangs()->sync($allCabangs);
                $user->departemens()->sync($allDepartemens);
            } else {
                // Sync akses cabang
                if (isset($request->cabangs) && is_array($request->cabangs)) {
                    $user->cabangs()->sync($request->cabangs);
                } else {
                    // Jika tidak ada cabang yang dipilih, hapus semua akses
                    $user->cabangs()->sync([]);
                }

                // Sync akses departemen
                if (isset($request->departemens) && is_array($request->departemens)) {
                    $syncData = [];
                    foreach ($request->departemens as $deptCode) {
                        $subDepts = $request->sub_departemen_access[$deptCode] ?? null;
                        if (!empty($subDepts) && is_array($subDepts)) {
                            $syncData[$deptCode] = ['sub_departemen' => json_encode($subDepts)];
                        } else {
                            $syncData[$deptCode] = ['sub_departemen' => null];
                        }
                    }
                    $user->departemens()->sync($syncData);
                } else {
                    // Jika tidak ada departemen yang dipilih, hapus semua akses
                    $user->departemens()->sync([]);
                }
            }

            // Update approval_admin_id for karyawan
            $userkaryawan = Userkaryawan::where('id_user', $id)->first();
            if ($userkaryawan) {
                Userkaryawan::where('id_user', $id)->update([
                    'approval_admin_id' => $request->approval_admin_id ?: null,
                ]);
            }

            return Redirect::back()->with(['success' => 'Data Berhasil Disimpan']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['error' => $e->getMessage()]);
        }
    }


    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();
        
        $user = User::findOrFail($id);
        
        // User tanpa permission users.admin tidak bisa delete user yang bukan karyawan
        $targetUserRole = $user->getRoleNames()->first();
        if (!$authUser->can('users.admin') && $targetUserRole !== 'karyawan') {
            abort(403, 'Anda tidak memiliki akses untuk menghapus user admin.');
        }
        
        try {
            User::where('id', $id)->delete();
            $cek_user_karyawan = Userkaryawan::where('id_user', $id)->first();
            if ($cek_user_karyawan) {
                Userkaryawan::where('id_user', $id)->delete();
            }

            return Redirect::back()->with(['success' => 'Data Berhasil Dihapus']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['error' => $e->getMessage()]);
        }
    }

    public function editpassword($id)
    {
        $id = Crypt::decrypt($id);
        $user = User::where('id', $id)->first();
        return view('settings.users.editpassword', compact('user'));
    }

    public function updatepassword(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $user = User::findOrFail($id);
        $request->validate([
            'username' => 'required|unique:users,username,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'passwordbaru' => \App\Helpers\PasswordHelper::getRules($user, null, true, false),
            'konfirmasipassword' => 'same:passwordbaru'
        ]);
        try {
            $data = [
                'username' => $request->username,
                'email' => $request->email
            ];

            if (!empty($request->passwordbaru)) {
                $data['password'] = Hash::make($request->passwordbaru);
            }

            User::where('id', $id)->update($data);
            return Redirect::back()->with(['success' => 'Data Berhasil Diupdate']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['error' => $e->getMessage()]);
        }
    }
}
