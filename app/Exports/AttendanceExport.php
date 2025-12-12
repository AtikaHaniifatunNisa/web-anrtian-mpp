<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Instansi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle, WithEvents, WithMapping
{
    protected $selectedDate;

    public function __construct($selectedDate = null)
    {
        $this->selectedDate = $selectedDate ?: now()->toDateString();
    }

    public function collection(): Collection
    {
        // Get all instansi
        $instansis = Instansi::all();
        
        // Get aggregated attendance data for the selected date
        $attendances = Attendance::whereDate('date', $this->selectedDate)
            ->whereNotNull('instansi_id')
            ->select(
                'instansi_id',
                DB::raw('DATE(date) as tanggal'),
                DB::raw('MIN(check_in) as waktu'),
                DB::raw('COUNT(*) as total_kehadiran')
            )
            ->groupBy('instansi_id', DB::raw('DATE(date)'))
            ->get()
            ->keyBy('instansi_id');
        
        // Build collection with all instansi and their attendance status
        $data = collect();
        
        foreach ($instansis as $instansi) {
            $attendance = $attendances->get($instansi->instansi_id);
            
            $waktu = '-';
            if ($attendance && $attendance->waktu) {
                // Format waktu dari TIME (HH:MM:SS) ke HH:MM
                if (is_string($attendance->waktu) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $attendance->waktu)) {
                    $waktu = substr($attendance->waktu, 0, 5);
                } else {
                    try {
                        $waktu = Carbon::parse($attendance->waktu)->format('H:i');
                    } catch (\Exception $e) {
                        $waktu = $attendance->waktu;
                    }
                }
            }
            
            $data->push([
                'tanggal' => Carbon::parse($this->selectedDate)->format('d M Y'),
                'waktu' => $waktu,
                'instansi' => $instansi->nama_instansi,
                'status' => $attendance && $attendance->total_kehadiran > 0 ? 'Hadir' : 'Tidak Hadir',
                'total_kehadiran' => $attendance ? (int)$attendance->total_kehadiran : 0,
            ]);
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Waktu',
            'Instansi / Perusahaan',
            'Status Kehadiran',
            'Total Kehadiran',
        ];
    }

    public function map($row): array
    {
        return [
            $row['tanggal'],
            $row['waktu'],
            $row['instansi'],
            $row['status'],
            $row['total_kehadiran'],
        ];
    }

    public function title(): string
    {
        return 'Absensi Petugas';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Get the last row and column BEFORE inserting new rows
                $originalLastRow = $sheet->getHighestRow();
                $lastColumn = 'E'; // We know we have 5 columns (A-E)
                
                // Insert 2 rows at the top for title
                $sheet->insertNewRowBefore(1, 2);
                
                // After insertion, header is now at row 3, data starts at row 4
                $headerRow = 3;
                $dataStartRow = 4;
                $lastRow = $originalLastRow + 2; // Adjust for inserted rows
                
                // Add title
                $sheet->setCellValue('A1', 'Laporan Absensi Petugas');
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                
                // Add subtitle with date
                $dateFormatted = Carbon::parse($this->selectedDate)->format('d F Y');
                $sheet->setCellValue('A2', 'Tanggal: ' . $dateFormatted);
                $sheet->mergeCells('A2:' . $lastColumn . '2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                
                // Style header row (row 3)
                $headerRange = 'A' . $headerRow . ':' . $lastColumn . $headerRow;
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF006100'); // Dark green background
                $sheet->getStyle($headerRange)->getFont()->getColor()
                    ->setARGB('FFFFFFFF'); // White text
                $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($headerRange)->getAlignment()->setVertical('center');
                
                // Add borders to all data cells (including header)
                $dataRange = 'A' . $headerRow . ':' . $lastColumn . $lastRow;
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                // Center align certain columns
                if ($lastRow >= $dataStartRow) {
                    $sheet->getStyle('A' . $dataStartRow . ':A' . $lastRow)->getAlignment()->setHorizontal('center'); // Tanggal
                    $sheet->getStyle('B' . $dataStartRow . ':B' . $lastRow)->getAlignment()->setHorizontal('center'); // Waktu
                    $sheet->getStyle('D' . $dataStartRow . ':D' . $lastRow)->getAlignment()->setHorizontal('center'); // Status
                    $sheet->getStyle('E' . $dataStartRow . ':E' . $lastRow)->getAlignment()->setHorizontal('center'); // Total Kehadiran
                }
                
                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(15); // Tanggal
                $sheet->getColumnDimension('B')->setWidth(12); // Waktu
                $sheet->getColumnDimension('C')->setWidth(40); // Instansi
                $sheet->getColumnDimension('D')->setWidth(18); // Status
                $sheet->getColumnDimension('E')->setWidth(18); // Total Kehadiran
                
                // Color code status column
                for ($row = $dataStartRow; $row <= $lastRow; $row++) {
                    $statusCell = 'D' . $row;
                    $statusValue = $sheet->getCell($statusCell)->getValue();
                    
                    if ($statusValue === 'Hadir') {
                        $sheet->getStyle($statusCell)->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFC6EFCE'); // Light green
                    } elseif ($statusValue === 'Tidak Hadir') {
                        $sheet->getStyle($statusCell)->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFFC7CE'); // Light red
                    }
                }
            },
        ];
    }
}

