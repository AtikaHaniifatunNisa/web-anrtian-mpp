<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ResetDailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:reset-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily attendance - mark incomplete attendances from previous day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily attendance reset...');
        
        try {
            // Ambil tanggal kemarin
            $yesterday = Carbon::yesterday()->toDateString();
            
            // Cari semua absensi kemarin yang belum check_out
            $incompleteAttendances = Attendance::where('date', $yesterday)
                ->whereNull('check_out')
                ->get();
            
            $count = 0;
            
            foreach ($incompleteAttendances as $attendance) {
                // Hitung working hours berdasarkan check_in sampai akhir hari (23:59:59)
                $checkIn = Carbon::parse($attendance->date . ' ' . $attendance->check_in);
                $endOfDay = Carbon::parse($attendance->date)->endOfDay();
                
                // Jika check_in setelah jam 8 pagi, tandai sebagai late
                $lateThreshold = Carbon::parse($attendance->date . ' 08:00:00');
                $status = $checkIn->gt($lateThreshold) ? 'late' : 'present';
                
                // Hitung working hours dalam menit
                $workingHours = $checkIn->diffInMinutes($endOfDay);
                
                // Update attendance
                $attendance->update([
                    'check_out' => $endOfDay->toTimeString(),
                    'status' => $status,
                    'working_hours' => $workingHours,
                ]);
                
                $count++;
            }
            
            $this->info("Successfully reset {$count} incomplete attendances from {$yesterday}");
            
            Log::info('Daily attendance reset completed', [
                'date' => $yesterday,
                'count' => $count,
            ]);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error resetting daily attendance: ' . $e->getMessage());
            
            Log::error('Error in daily attendance reset', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Command::FAILURE;
        }
    }
}
