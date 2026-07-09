<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InitPermIdControl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init-perm-id-control';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $groupName = 'ID Control List';
        $group = \Illuminate\Support\Facades\DB::table('permission_groups')->where('name', $groupName)->first();
        if (!$group) {
            $groupId = \Illuminate\Support\Facades\DB::table('permission_groups')->insertGetId(['name' => $groupName]);
        } else {
            $groupId = $group->id;
        }

        $permissions = ['view id control list', 'create id control list', 'edit id control list', 'delete id control list'];
        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::updateOrCreate(
                ['name' => $permission],
                ['id_permission_group' => $groupId]
            );
        }
        $this->info('Permissions generated successfully');
    }
}
