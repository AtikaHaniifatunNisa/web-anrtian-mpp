<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\RekapLayananExport;
use App\Models\AntrianSkck;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{

    private $bulan = [
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

    public function rekapLayanan(Request $request)
    {
        $from = $request->query('from', now()->toDateString());
        $to   = $request->query('to', now()->toDateString());

        $fileName = "rekap_layanan_{$from}_sd_{$to}.xlsx";

        return Excel::download(new RekapLayananExport($from, $to), $fileName);
    }

    public function cetakSkck(Request $request, $id)
    {
        $id = str_replace('SKCK', '', $id);

        $antrianSkck = AntrianSkck::find($id);

        if ($antrianSkck == null) abort(404);

        $logo = public_path('logo_pemkot.png');

        $logoBase64 = base64_encode(file_get_contents($logo));
        $tanggal = date('j', strtotime($antrianSkck->created_at));
        $bulanAngka = date('n', strtotime($antrianSkck->created_at));
        $tahun = date('Y', strtotime($antrianSkck->created_at));

        $format = $tanggal . ' ' . $this->bulan[$bulanAngka] . ' ' . $tahun;
        $pdf = Pdf::loadView('antrian-skck', [
            'logo' => $logoBase64,
            'nomor' => str_pad($antrianSkck->antrian, 3, '0', STR_PAD_LEFT),
            'tanggal' => $format,
            'nama' => $antrianSkck->nama
        ]);

        $customPaper = [0, 0, 360, 360];

        return $pdf->setPaper($customPaper)->stream($id . '.pdf');
    }
}
