<?php

namespace App\Http\Middleware;

use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;

class StartFilamentSession extends StartSession
{
    /**
     * Get the name of the session cookie.
     *
     * @return string
     */
    protected function getSessionCookieName()
    {
        $baseCookieName = config('session.cookie', 'laravel_session');
        $request = request();
        
        // Prioritas 1: Cek apakah ada user_id di query parameter (untuk login baru atau redirect setelah login)
        if ($request->has('user_id') && is_numeric($request->get('user_id'))) {
            $userId = $request->get('user_id');
            // Set cookie untuk menyimpan user_id untuk request berikutnya
            cookie()->queue('_filament_user_id', $userId, 60 * 24 * 30); // 30 hari
            return $baseCookieName . '_filament_' . $userId;
        }
        
        // Prioritas 2: Cek apakah ada user_id di cookie (untuk request berikutnya setelah login)
        if ($request->hasCookie('_filament_user_id')) {
            $userId = $request->cookie('_filament_user_id');
            if (is_numeric($userId)) {
                return $baseCookieName . '_filament_' . $userId;
            }
        }
        
        // Prioritas 3: Cek apakah ada session cookie dengan format _filament_{user_id} di cookie request
        // Ini untuk menangani kasus dimana cookie _filament_user_id belum ada
        foreach ($request->cookies->all() as $name => $value) {
            if (strpos($name, $baseCookieName . '_filament_') === 0) {
                // Extract user ID dari cookie name
                // Format: laravel_session_filament_123
                $parts = explode('_', $name);
                $lastPart = end($parts);
                if (is_numeric($lastPart)) {
                    // Set cookie _filament_user_id untuk request berikutnya
                    cookie()->queue('_filament_user_id', $lastPart, 60 * 24 * 30);
                    return $name;
                }
            }
        }
        
        // Prioritas 4: Jika user sudah login (untuk kasus dimana cookie belum di-set)
        // Ini memungkinkan multiple user login secara bersamaan di browser yang sama
        if (Auth::check()) {
            $userId = Auth::id();
            // Set cookie untuk menyimpan user_id untuk request berikutnya
            cookie()->queue('_filament_user_id', $userId, 60 * 24 * 30);
            return $baseCookieName . '_filament_' . $userId;
        }
        
        // Default: gunakan cookie name dengan identifier unik untuk session baru
        // Ini akan diubah setelah user login dengan redirect yang mengandung user_id
        return $baseCookieName . '_filament';
    }
}

