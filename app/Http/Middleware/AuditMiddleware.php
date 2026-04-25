<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;

class AuditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya log untuk user yang sudah login
        if (auth()->check()) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Log aktivitas user
     */
    protected function logActivity(Request $request, Response $response)
    {
        // Skip logging untuk routes tertentu (untuk menghindari terlalu banyak log)
        $skipRoutes = [
            'audit.index', // Skip audit index agar tidak rekursif
            'logout', // Logout sudah di-handle khusus
        ];

        $routeName = $request->route() ? $request->route()->getName() : null;
        
        if (in_array($routeName, $skipRoutes)) {
            return;
        }

        // Tentukan action berdasarkan HTTP method dan route
        $action = $this->determineAction($request);
        $module = $this->determineModule($request);
        $description = $this->generateDescription($request, $action, $module);

        // Hanya log untuk POST, PUT, PATCH, DELETE (actions yang mengubah data)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            try {
                AuditLog::log($action, $module, $description);
            } catch (\Exception $e) {
                // Silent fail, jangan ganggu normal flow
                \Log::error('Audit log failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Tentukan action dari request
     */
    protected function determineAction(Request $request)
    {
        $method = $request->method();
        $routeName = $request->route() ? $request->route()->getName() : '';

        if (str_contains($routeName, '.store') || $method === 'POST') {
            return 'create';
        } elseif (str_contains($routeName, '.update') || in_array($method, ['PUT', 'PATCH'])) {
            return 'update';
        } elseif (str_contains($routeName, '.destroy') || str_contains($routeName, '.delete') || $method === 'DELETE') {
            return 'delete';
        }

        return strtolower($method);
    }

    /**
     * Tentukan module dari request
     */
    protected function determineModule(Request $request)
    {
        $path = $request->path();
        $segments = explode('/', $path);
        
        // Ambil segment pertama sebagai module
        return $segments[0] ?? 'unknown';
    }

    /**
     * Generate deskripsi dari request
     */
    protected function generateDescription(Request $request, $action, $module)
    {
        $routeName = $request->route() ? $request->route()->getName() : $request->path();
        $user = auth()->user();
        
        return sprintf(
            '%s %s %s - Route: %s',
            $user->name,
            $action,
            $module,
            $routeName
        );
    }
}
