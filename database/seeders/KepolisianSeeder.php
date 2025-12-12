<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Instansi;
use App\Models\Service;
use App\Models\Counter;

class KepolisianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari atau buat instansi Kepolisian Resor Kota Besar
        $instansi = Instansi::firstOrCreate(
            ['nama_instansi' => 'Kepolisian Resor Kota Besar'],
            [
                'nama_instansi' => 'Kepolisian Resor Kota Besar',
                'deskripsi' => 'Instansi kepolisian yang menyediakan layanan SIM, SKCK, dan ETLE'
            ]
        );

        // Cari counter yang tersedia (misalnya ZONA 2)
        $counter = Counter::where('name', 'ZONA 2')->first();
        
        if (!$counter) {
            $counter = Counter::create([
                'name' => 'ZONA 2',
                'is_active' => true
            ]);
        }

        // Buat 3 layanan terpisah untuk Kepolisian
        $services = [
            [
                'name' => 'Layanan SIM',
                'prefix' => 'SIM',
                'padding' => 3,
                'is_active' => true,
                'instansi_id' => $instansi->instansi_id,
                'counter_id' => $counter->id
            ],
            [
                'name' => 'Layanan SKCK',
                'prefix' => 'SKCK',
                'padding' => 3,
                'is_active' => true,
                'instansi_id' => $instansi->instansi_id,
                'counter_id' => $counter->id
            ],
            [
                'name' => 'Layanan ETLE',
                'prefix' => 'ETLE',
                'padding' => 3,
                'is_active' => true,
                'instansi_id' => $instansi->instansi_id,
                'counter_id' => $counter->id
            ]
        ];

        foreach ($services as $serviceData) {
            Service::firstOrCreate(
                [
                    'name' => $serviceData['name'],
                    'instansi_id' => $serviceData['instansi_id']
                ],
                $serviceData
            );
        }

        $this->command->info('Data Kepolisian dengan 3 layanan (SIM, SKCK, ETLE) berhasil dibuat!');
    }
}
