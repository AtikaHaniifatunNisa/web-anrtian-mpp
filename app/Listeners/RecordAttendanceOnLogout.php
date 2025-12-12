<?php

namespace App\Listeners;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RecordAttendanceOnLogout
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        try {
            // Get user from event if available, otherwise from Auth
            $user = null;
            
            // Try to get user from event property (for Laravel Logout event)
            if (isset($event->user)) {
                $user = $event->user;
            } elseif (property_exists($event, 'user')) {
                $user = $event->user;
            } elseif (method_exists($event, 'getUser')) {
                // Some events have getUser() method
                $user = $event->getUser();
            } else {
                // Fallback to Auth::user() - might still be available during logout
                $user = Auth::user();
            }
            
            // Log untuk debugging
            Log::info('RecordAttendanceOnLogout triggered', [
                'user_id' => $user?->id,
                'user_role' => $user?->role,
                'user_name' => $user?->name,
                'event_class' => get_class($event),
                'has_user' => !is_null($user),
            ]);
            
            // Record checkout untuk semua user yang bukan admin
            if ($user && $user->role !== 'admin') {
                $today = now()->toDateString();
                
                // Cari absensi hari ini yang belum check_out
                $attendance = Attendance::where('user_id', $user->id)
                    ->where('date', $today)
                    ->whereNull('check_out')
                    ->first();
                
                if ($attendance) {
                    try {
                        $checkOutTime = now()->format('H:i:s');
                        
                        // Hitung working hours dalam menit
                        $checkIn = Carbon::parse($attendance->date . ' ' . $attendance->check_in);
                        $checkOut = Carbon::parse($attendance->date . ' ' . $checkOutTime);
                        $workingHours = $checkIn->diffInMinutes($checkOut);
                        
                        // Update attendance dengan check_out
                        $attendance->update([
                            'check_out' => $checkOutTime,
                            'working_hours' => $workingHours,
                        ]);
                        
                        Log::info('Attendance checkout recorded successfully', [
                            'attendance_id' => $attendance->id,
                            'user_id' => $user->id,
                            'date' => $today,
                            'check_in' => $attendance->check_in,
                            'check_out' => $checkOutTime,
                            'working_hours' => $workingHours,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to record checkout', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                } else {
                    Log::info('No attendance found for checkout', [
                        'user_id' => $user->id,
                        'date' => $today,
                    ]);
                }
            } else {
                Log::info('User is admin or null, skipping checkout record', [
                    'user_id' => $user?->id,
                    'user_role' => $user?->role,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in RecordAttendanceOnLogout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

