<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Pastikan user sudah login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Cek apakah role user ada di daftar role yang diizinkan
        if (!in_array($user->role, $roles)) {
            // Jika AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses ditolak. Role kamu tidak memiliki izin ini.'
                ], 403);
            }

            // Jika request biasa — redirect dengan pesan error
            return redirect()
                ->back()
                ->with('error',
                    'Akses ditolak. Fitur ini hanya untuk: ' .
                    implode(', ', $roles) . '.'
                );
        }

        return $next($request);
    }
}