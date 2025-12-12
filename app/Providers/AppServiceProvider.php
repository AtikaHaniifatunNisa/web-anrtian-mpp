<?php

namespace App\Providers;

use App\Services\QueueService;
use App\Services\ThermalPrinterService;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login as LaravelLogin;
use Illuminate\Auth\Events\Logout as LaravelLogout;
use App\Listeners\RecordAttendanceOnLogin;
use App\Listeners\RecordAttendanceOnLogout;
use App\Listeners\UpdateSessionCookieOnLogin;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ThermalPrinterService::class, function ($app) {
            return new ThermalPrinterService();
        });

        $this->app->singleton(QueueService::class, function ($app) {
            return new QueueService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentAsset::register([
            Js::make('thermal-printer', asset('js/thermal-printer.js')),
            Js::make('call-queue', asset('js/call-queue.js'))
        ]);
        
        // Register event listener for attendance - Laravel native Login event
        \Illuminate\Support\Facades\Event::listen(
            LaravelLogin::class,
            RecordAttendanceOnLogin::class
        );
        
        // Register event listener for attendance - Laravel native Authenticated event (backup)
        \Illuminate\Support\Facades\Event::listen(
            Authenticated::class,
            RecordAttendanceOnLogin::class
        );
        
        // Register event listener for updating session cookie on login
        // Ini memungkinkan multiple user login secara bersamaan di browser yang sama
        \Illuminate\Support\Facades\Event::listen(
            LaravelLogin::class,
            UpdateSessionCookieOnLogin::class
        );
        
        // Register event listener for attendance checkout on logout
        \Illuminate\Support\Facades\Event::listen(
            LaravelLogout::class,
            RecordAttendanceOnLogout::class
        );
    }
}
