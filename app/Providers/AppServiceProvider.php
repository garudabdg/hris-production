<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || env('APP_ENV') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        Paginator::useBootstrapFive();

        // Share general_setting to all views to prevent undefined variable errors on login and other pages
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('generalsetting')) {
                $general_setting = \App\Models\Generalsetting::first();
                \Illuminate\Support\Facades\View::share('general_setting', $general_setting);
            }
        } catch (\Exception $e) {
            // Silence if DB not ready
        }

        // Auto-seed laporan.lembur permission if missing
        try {
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permission = \Spatie\Permission\Models\Permission::where('name', 'laporan.lembur')->first();
                if (!$permission) {
                    $permissiongroup = \App\Models\Permission_group::firstOrCreate(['name' => 'Laporan']);
                    $permission = \Spatie\Permission\Models\Permission::firstOrCreate(
                        ['name' => 'laporan.lembur'], 
                        ['id_permission_group' => $permissiongroup->id]
                    );
                    $role = \Spatie\Permission\Models\Role::findById(1);
                    if ($role && !$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }
        } catch (\Exception $e) {
            // Silence errors during migration/seeding when DB/tables are not ready
        }
    }
}
