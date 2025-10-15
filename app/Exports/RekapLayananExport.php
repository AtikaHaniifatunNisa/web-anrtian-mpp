<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;

class RekapLayananExport implements FromCollection, ShouldAutoSize, WithTitle, WithEvents
{
    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function collection()
    {
        $from = now()->parse($this->from)->startOfDay();
        $to   = now()->parse($this->to)->endOfDay();

        // Ambil semua instansi yang memiliki antrian dalam periode yang dipilih
        $instansis = DB::table('instansis as i')
            ->leftJoin('services as s', 'i.instansi_id', '=', 's.instansi_id')
            ->leftJoin('queues as q', function($join) use ($from, $to) {
                $join->on('q.service_id', '=', 's.id')
                     ->whereBetween('q.created_at', [$from, $to]);
            })
            ->select('i.instansi_id', 'i.nama_instansi')
            ->groupBy('i.instansi_id', 'i.nama_instansi')
            ->orderBy('i.nama_instansi')
            ->get();

        // Generate array tanggal lengkap untuk satu bulan (1-31)
        $dates = [];
        for ($day = 1; $day <= 31; $day++) {
            $dates[] = $day;
        }

        $data = collect();
        $no = 1;

        foreach ($instansis as $instansi) {
            $row = [
                'No' => $no,
                'Jenis_Instansi' => $instansi->nama_instansi
            ];

            // Hitung jumlah pemohon per tanggal (1-31)
            foreach ($dates as $day) {
                // Buat tanggal berdasarkan bulan dan tahun dari range yang dipilih
                $year = $from->year;
                $month = $from->month;
                $checkDate = Carbon::create($year, $month, $day);
                
                // Cek apakah tanggal tersebut ada dalam range yang dipilih
                if ($checkDate->gte($from) && $checkDate->lte($to)) {
                    $dateStart = $checkDate->copy()->startOfDay();
                    $dateEnd = $checkDate->copy()->endOfDay();
                    
                    // Query yang diperbaiki untuk menghitung jumlah antrian per instansi per tanggal
                    $jumlahPemohon = DB::table('queues as q')
                        ->join('services as s', 'q.service_id', '=', 's.id')
                        ->where('s.instansi_id', $instansi->instansi_id)
                        ->whereBetween('q.created_at', [$dateStart, $dateEnd])
                        ->count();
                } else {
                    $jumlahPemohon = 0; // Jika tanggal di luar range, set 0
                }

                $row['Tanggal_' . $day] = $jumlahPemohon;
            }

            $data->push($row);
            $no++;
        }

        return $data;
    }


    public function title(): string
    {
        return 'Rekap Layanan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $from = now()->parse($this->from)->startOfDay();
                $to   = now()->parse($this->to)->endOfDay();
                
                // Selalu tampilkan 31 kolom tanggal (1-31)
                $dateCount = 31;
                
                // Kolom terakhir untuk data tanggal (C + 31 - 1 = AG)
                $lastDateColumnIndex = 2 + $dateCount; // C (index 2) + 31 - 1 = 33 (AG)
                $lastColumnLetter = Coordinate::stringFromColumnIndex($lastDateColumnIndex);
                
                // 1. Sisipkan 4 baris di awal untuk judul dan header
                $sheet->insertNewRowBefore(1, 4);
                
                // 2. Tambahkan judul utama di baris 1
                $sheet->setCellValue('A1', 'Rekapan Jumlah Pemohon Mall Pelayanan Publik Kota Surabaya');
                $sheet->mergeCells('A1:' . $lastColumnLetter . '1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                
                // 3. Tambahkan subtitle di baris 2
                $sheet->setCellValue('A2', 'Bulan ' . now()->parse($this->from)->format('F Y'));
                $sheet->mergeCells('A2:' . $lastColumnLetter . '2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                
                // 4. Header baris 3: No., Jenis Instansi (merged 2 baris), Tanggal (merged)
                $sheet->setCellValue('A3', 'No.');
                $sheet->mergeCells('A3:A4'); // Merge No. untuk 2 baris
                $sheet->setCellValue('B3', 'Jenis Instansi');
                $sheet->mergeCells('B3:B4'); // Merge Jenis Instansi untuk 2 baris
                if ($dateCount > 0) {
                    $sheet->setCellValue('C3', 'Tanggal');
                    $sheet->mergeCells('C3:' . $lastColumnLetter . '3');
                    $sheet->getStyle('C3:' . $lastColumnLetter . '3')->getAlignment()->setHorizontal('center');
                }
                
                // 5. Header baris 4: Angka tanggal (1-31)
                $colIndex = 3; // Mulai dari kolom C
                for ($day = 1; $day <= 31; $day++) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex) . '4', $day);
                    $colIndex++;
                }
                
                // Styling untuk header tabel (baris 3 dan 4)
                $headerRange = 'A3:' . $lastColumnLetter . '4';
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0'); // Light gray background
                $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($headerRange)->getAlignment()->setVertical('center');
                
                // Alignment khusus untuk kolom No. dan Jenis Instansi (merged cells)
                $sheet->getStyle('A3')->getAlignment()->setHorizontal('center'); // No. center aligned
                $sheet->getStyle('B3')->getAlignment()->setHorizontal('center'); // Jenis Instansi center aligned
                
                // Set lebar kolom
                $sheet->getColumnDimension('A')->setWidth(8); // Kolom No. 
                $sheet->getColumnDimension('B')->setWidth(50); // Kolom Jenis Instansi
                for ($col = 3; $col <= $lastDateColumnIndex; $col++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setWidth(6); // Kolom tanggal
                }
                
                // Tambahkan border ke seluruh tabel
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A3:' . $lastColumnLetter . $lastRow)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            },
        ];
    }
}