<?php
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

$groupName = 'ID Control List';
$group = DB::table('permission_groups')->where('name', $groupName)->first();
if (!$group) {
    $groupId = DB::table('permission_groups')->insertGetId(['name' => $groupName]);
} else {
    $groupId = $group->id;
}

$permissions = ['view id control list', 'create id control list', 'edit id control list', 'delete id control list'];
foreach ($permissions as $permission) {
    Permission::updateOrCreate(['name' => $permission], ['id_permission_group' => $groupId]);
}
echo "Permissions created successfully.\n";
