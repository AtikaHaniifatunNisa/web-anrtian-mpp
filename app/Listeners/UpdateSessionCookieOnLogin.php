<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

class UpdateSessionCookieOnLogin
{
    /**
     * Handle the event.
     * 
     * Setelah user login, set cookie untuk menyimpan user_id
     * Ini memungkinkan middleware StartFilamentSession menggunakan user_id untuk membuat cookie name unik
     */
    public function handle(object $event): void
    {
        $user = Auth::user();
        
        if ($user) {
            // Set cookie untuk menyimpan user_id
            // Cookie ini akan digunakan oleh middleware StartFilamentSession untuk membuat session cookie name unik
            Cookie::queue('_filament_user_id', $user->id, 60 * 24 * 30); // 30 hari
            
            // Simpan user ID di session juga untuk backup
            Session::put('_filament_user_id', $user->id);
            
            // Regenerate session ID untuk keamanan
            Session::regenerate();
        }
    }
}

