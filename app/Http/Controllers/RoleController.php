<?php

namespace App\Http\Controllers;

use App\Models\Permission_group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Role::query();
        if (!empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        $roles = $query->paginate(10);
        $roles->appends(request()->all());
        return view('settings.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('settings.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $name = strtolower($request->name);
        try {
            Role::create(['name' => $name]);
            return Redirect::back()->with(['success' => 'Data Berhasil Disimpan']);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return Redirect::back()->with(['error' => $message]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $role = Role::findorFail($id);
        return view('settings.roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        try {
            Role::where('id', $id)->update(['name' => strtolower($request->name)]);

            return Redirect::back()->with(['success' => 'Data Berhasil Diupdate']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        try {
            Role::where('id', $id)->delete();
            return Redirect::back()->with(['success' => 'Data Berhasil Dihapus']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['error' => $e->getMessage()]);
        }
    }


    public function createrolepermission($id)
    {
        $id = Crypt::decrypt($id);
        $permissions = Permission::orderBy('id_permission_group')
            ->selectRaw('id_permission_group,permission_groups.name as group_name,GROUP_CONCAT(permissions.id,"-",permissions.name) as permissions')
            ->join('permission_groups', 'permissions.id_permission_group', '=', 'permission_groups.id')
            ->groupBy('id_permission_group')
            ->groupBy('permission_groups.name')
            ->get();

        $role = Role::findById($id);
        $rolepermissions = $role->permissions->pluck('name')->toArray();
        return view('settings.roles.create_role_permission', compact('permissions', 'role', 'rolepermissions'));
    }

    public function storerolepermission($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $permissions = $request->permission;
        $role = Role::findById($id);
        $old_permissions = $role->permissions->pluck('name')->toArray();


        if (empty($permissions)) {
            return Redirect::back()->with(['warning' => 'Data Permission Harus Di Pilih']);
        }

        try {
            $role->revokePermissionTo($old_permissions);
            $role->givePermissionTo($permissions);
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            return Redirect::back()->with(['success' => 'Data Berhasil Disimpan']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show asset permissions management page
     */
    public function assetPermissions()
    {
        $roles = Role::all();
        $assetPermissions = Permission::where('name', 'like', 'asset%')
            ->orderBy('name')
            ->get();

        // Group permissions by category
        $groupedPermissions = [];
        foreach ($assetPermissions as $perm) {
            $parts = explode('.', $perm->name);
            $category = $parts[1] ?? 'main';
            
            // Group CRUD and utility actions for general assets under 'main' (Aset Umum)
            if (in_array($category, ['index', 'create', 'edit', 'show', 'delete', 'export', 'import'])) {
                $category = 'main';
            }
            
            if (!isset($groupedPermissions[$category])) {
                $groupedPermissions[$category] = [];
            }
            $groupedPermissions[$category][] = $perm;
        }

        // Get role permissions
        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role->id] = $role->permissions()
                ->where('name', 'like', 'asset%')
                ->pluck('name')
                ->toArray();
        }

        return view('settings.roles.asset_permissions', compact('roles', 'groupedPermissions', 'rolePermissions'));
    }

    /**
     * Update asset permissions for a role
     */
    public function updateAssetPermission($roleId, Request $request)
    {
        try {
            $roleId = Crypt::decrypt($roleId);
            $role = Role::findById($roleId);
            $permissions = $request->input('permissions', []);

            // Get all asset permissions
            $allAssetPermissions = Permission::where('name', 'like', 'asset%')->pluck('name')->toArray();

            // Revoke all asset permissions first
            $role->revokePermissionTo($allAssetPermissions);

            // Give only selected permissions
            if (!empty($permissions)) {
                foreach ($permissions as $permission) {
                    if (Permission::where('name', $permission)->exists()) {
                        $role->givePermissionTo($permission);
                    }
                }
            }

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            return Redirect::back()->with(['success' => 'Izin akses aset untuk ' . $role->name . ' berhasil diperbarui']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['error' => 'Gagal memperbarui izin akses: ' . $e->getMessage()]);
        }
    }
}
