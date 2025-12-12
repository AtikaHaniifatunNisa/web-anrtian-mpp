<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use App\Listeners\RecordAttendanceOnLogin;

class Login extends BaseLogin
{
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['login'],
            'password' => $data['password'],
        ];
    }
    
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('Username')
            ->required()
            ->maxLength(255)
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1])
            ->validationAttribute('username');
    }
    
    /**
     * Get the URL that the user should be redirected to after login.
     */
    protected function getRedirectUrl(): string
    {
        $user = Auth::user();
        if ($user) {
            // Tambahkan user_id di query parameter untuk memastikan session cookie name yang benar
            // Ini memungkinkan multiple user login secara bersamaan di browser yang sama
            $baseUrl = parent::getRedirectUrl();
            return $baseUrl . '?user_id=' . $user->id;
        }
        return parent::getRedirectUrl();
    }
    
    /**
     * Hook setelah login berhasil - menggunakan authenticated event
     */
    protected function authenticated(): void
    {
        parent::authenticated();
        
        // Panggil listener setelah parent untuk memastikan user sudah tersedia
        $this->recordAttendanceAfterLogin();
    }
    
    /**
     * Record attendance setelah login berhasil
     */
    protected function recordAttendanceAfterLogin(): void
    {
        try {
            $user = Auth::user();
            if ($user) {
                // Panggil listener langsung untuk memastikan absensi tercatat
                $listener = new RecordAttendanceOnLogin();
                $listener->handle((object) ['user' => $user]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error recording attendance in Login::authenticated', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

