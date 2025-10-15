<?php

namespace Database\Seeders;

use App\Models\Counter;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperatorPerCounterSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus operator lama yang tidak memiliki counter_id yang valid
        User::where('role', 'operator')->whereNull('counter_id')->delete();

        // Buat operator untuk setiap counter yang sudah ada
        $counters = Counter::all();

        foreach ($counters as $counter) {
            $email = 'operator.' . strtolower(str_replace(' ', '', $counter->name)) . '@example.com';

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Operator ' . $counter->name,
                    'password' => Hash::make('operator123'),
                    'role' => 'operator',
                    'counter_id' => $counter->id,
                ]
            );
        }
    }

}


