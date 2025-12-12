<?php

namespace App\Listeners;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RecordAttendanceOnLogin
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        try {
            // Get user from event if available, otherwise from Auth
            $user = null;
            
            // Try to get user from event property (for Laravel Authenticated event)
            if (isset($event->user)) {
                $user = $event->user;
            } elseif (property_exists($event, 'user')) {
                $user = $event->user;
            } elseif (method_exists($event, 'getUser')) {
                // Some events have getUser() method
                $user = $event->getUser();
            } else {
                // Fallback to Auth::user()
                $user = Auth::user();
            }
            
            // Jika masih null, coba lagi (untuk mengatasi timing issue)
            if (!$user) {
                $user = Auth::user();
            }
            
            // Log untuk debugging
            Log::info('RecordAttendanceOnLogin triggered', [
                'user_id' => $user?->id,
                'user_role' => $user?->role,
                'user_name' => $user?->name,
                'event_class' => get_class($event),
                'has_user' => !is_null($user),
            ]);
            
            // Record untuk semua user yang bukan admin
            if ($user && $user->role !== 'admin') {
                $today = now()->toDateString();
                
                // Check if attendance already exists for today
                $existingAttendance = Attendance::where('user_id', $user->id)
                    ->where('date', $today)
                    ->first();
                
                if (!$existingAttendance) {
                    // Get instansi_id from user's service or counter
                    $instansiId = null;
                    
                    // Coba ambil dari service
                    if ($user->service_id) {
                        $service = \App\Models\Service::find($user->service_id);
                        if ($service && $service->instansi_id) {
                            $instansiId = $service->instansi_id;
                        }
                    }
                    
                    // Jika belum dapat, coba ambil dari counter
                    if (!$instansiId && $user->counter_id) {
                        $counter = \App\Models\Counter::withoutGlobalScopes()->find($user->counter_id);
                        if ($counter && $counter->instansi_id) {
                            $instansiId = $counter->instansi_id;
                        }
                    }
                    
                    try {
                        $attendance = Attendance::create([
                            'user_id' => $user->id,
                            'instansi_id' => $instansiId,
                            'date' => $today,
                            'check_in' => now()->format('H:i:s'),
                            'status' => 'present',
                        ]);
                        
                        Log::info('Attendance created successfully', [
                            'attendance_id' => $attendance->id,
                            'user_id' => $user->id,
                            'instansi_id' => $instansiId,
                            'date' => $today,
                            'check_in' => $attendance->check_in,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create attendance', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                } else {
                    Log::info('Attendance already exists for today', [
                        'user_id' => $user->id,
                        'attendance_id' => $existingAttendance->id,
                        'date' => $today,
                    ]);
                }
            } else {
                Log::info('User is admin, skipping attendance record', [
                    'user_id' => $user?->id,
                    'user_role' => $user?->role,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in RecordAttendanceOnLogin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
