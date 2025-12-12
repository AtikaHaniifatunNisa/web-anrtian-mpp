<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Instansi;
use App\Exports\AttendanceExport;
use App\Exports\AttendanceYearlyRecapExport;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Excel (Harian)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $selectedDate = request()->get('tableFilters.date.date', now()->toDateString());
                    $fileName = 'absensi_petugas_' . \Carbon\Carbon::parse($selectedDate)->format('Y-m-d') . '.xlsx';
                    
                    return Excel::download(
                        new AttendanceExport($selectedDate),
                        $fileName
                    );
                }),
            Action::make('exportYearly')
                ->label('Export Excel (Rekap Tahunan)')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->action(function () {
                    $currentYear = Carbon::now()->year;
                    $fileName = 'rekap_absensi_tahunan_' . $currentYear . '.xlsx';
                    
                    return Excel::download(
                        new AttendanceYearlyRecapExport($currentYear),
                        $fileName
                    );
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        // Get selected date from filter (default to today)
        $selectedDate = request()->get('tableFilters.date.date', now()->toDateString());
        
        // Admin bisa lihat semua absensi, bukan hanya absensi user yang login
        $query = Attendance::query()
            ->whereDate('date', $selectedDate)
            ->with(['instansi', 'user'])
            ->orderBy('check_in', 'asc');
        
        return $query;
    }

    public function getMonthlyRecapData(): array
    {
        $currentYear = Carbon::now()->year;
        $startOfYear = Carbon::create($currentYear, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($currentYear, 12, 31)->endOfDay();
        
        // Get all instansi
        $instansis = Instansi::orderBy('nama_instansi')->get();
        
        // Admin bisa lihat semua absensi semua user, bukan hanya user yang login
        $allAttendances = Attendance::whereBetween('date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
            ->whereNotNull('instansi_id')
            ->get();
        
        // Group by instansi_id first, then by month
        $attendances = $allAttendances->groupBy('instansi_id')->map(function ($instansiAttendances) {
            return $instansiAttendances->groupBy(function ($attendance) {
                return Carbon::parse($attendance->date)->format('Y-m');
            });
        });
        
        // Month names in Indonesian
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        
        // Prepare data structure
        $instansiData = [];
        
        foreach ($instansis as $instansi) {
            $instansiId = $instansi->instansi_id;
            $monthlyPercentages = [];
            
            // Calculate percentage for each month
            for ($month = 1; $month <= 12; $month++) {
                $monthStart = Carbon::create($currentYear, $month, 1)->startOfDay();
                $monthEnd = Carbon::create($currentYear, $month, 1)->endOfMonth()->endOfDay();
                
                // Count total days in month
                $totalDays = $monthStart->diffInDaysFiltered(function ($date) {
                    return true; // Count all days
                }, $monthEnd) + 1;
                
                // Get attendance for this instansi in this month
                $monthKey = $currentYear . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                $monthAttendances = $attendances->get($instansiId)?->get($monthKey) ?? collect();
                
                // Count unique days with attendance
                $daysPresent = $monthAttendances->groupBy(function ($attendance) {
                    return Carbon::parse($attendance->date)->format('Y-m-d');
                })->count();
                
                // Calculate percentage
                $percentage = $totalDays > 0 ? round(($daysPresent / $totalDays) * 100, 2) : 0;
                
                $monthlyPercentages[$month] = [
                    'percentage' => $percentage,
                    'days_present' => $daysPresent,
                    'total_days' => $totalDays,
                ];
            }
            
            $instansiData[] = [
                'instansi_id' => $instansiId,
                'nama_instansi' => $instansi->nama_instansi,
                'monthly_percentages' => $monthlyPercentages,
            ];
        }
        
        return [
            'year' => $currentYear,
            'month_names' => $monthNames,
            'instansi_data' => $instansiData,
        ];
    }

    public function getView(): string
    {
        return 'filament.resources.attendance-resource.pages.list-attendances';
    }

    public function getMonthlyRecap(): array
    {
        return $this->getMonthlyRecapData();
    }
}
