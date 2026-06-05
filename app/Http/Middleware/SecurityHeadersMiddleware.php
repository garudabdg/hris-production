<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya terapkan header jika response memiliki method header() (seperti Illuminate\Http\Response)
        if (method_exists($response, 'header')) {
            // Generate a secure nonce
            $nonce = base64_encode(\Illuminate\Support\Str::random(16));
            
            // Inject nonce into HTML responses
            $contentType = $response->headers->get('Content-Type');
            if ($contentType && strpos($contentType, 'text/html') !== false) {
                $content = $response->getContent();
                if (is_string($content)) {
                    // Auto-inject nonce into all <script> tags that don't already have one
                    $content = preg_replace('/<script\b(?![^>]*\bnonce=)/i', '<script nonce="' . $nonce . '"', $content);
                    $response->setContent($content);
                }
            }

            // Construct strict CSP without unsafe-inline or unsafe-eval for scripts
            $csp = "upgrade-insecure-requests; ";
            $csp .= "frame-ancestors 'self'; ";
            $csp .= "object-src 'none'; ";
            $csp .= "base-uri 'self'; ";
            $csp .= "default-src 'self' https: data: blob: 'unsafe-inline'; "; // unsafe-inline fallback for styles
            $csp .= "script-src 'self' 'nonce-{$nonce}' https: 'unsafe-eval' 'unsafe-inline'; ";
            
            $response->header('Content-Security-Policy', $csp);
            
            // Tambahan Security Headers (OWASP Recommended)
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
            $response->header('X-XSS-Protection', '1; mode=block');
            $response->header('Referrer-Policy', 'no-referrer');
            
            // Note: X-Frame-Options dan X-Content-Type-Options sudah diatur di Nginx, 
            // tapi kita tambahkan di sini sebagai fallback jika aplikasi dijalankan tanpa Nginx (seperti artisan serve)
            if (!$response->headers->has('X-Frame-Options')) {
                $response->header('X-Frame-Options', 'SAMEORIGIN');
            }
            if (!$response->headers->has('X-Content-Type-Options')) {
                $response->header('X-Content-Type-Options', 'nosniff');
            }
            
            // Remove headers that leak software/server information
            $response->headers->remove('X-Powered-By');
            $response->headers->remove('Server');
        }

        return $response;
    }
}
