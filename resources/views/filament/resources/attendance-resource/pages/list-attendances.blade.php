<x-filament-panels::page>
    @php
        $monthlyRecap = $this->getMonthlyRecap();
    @endphp
    
    <style>
        .attendance-cell {
            border: 1px solid #d1d5db;
        }
        .attendance-cell[data-bg-color] {
            background-color: var(--bg-color);
            color: var(--text-color);
        }
    </style>
    
    <div class="space-y-6">
        {{-- Tabel Rekap Absensi Real Time --}}
        <div>
            <h2 class="text-xl font-bold mb-4">Rekap Absensi Real Time</h2>
            {{ $this->table }}
        </div>

        {{-- Tabel Rekap Bulanan --}}
        <div class="mt-8">
            <h2 class="text-xl font-bold mb-4">{{ $monthlyRecap['year'] }}</h2>
            
            {{-- Tabel Rekap Per Instansi Per Bulan --}}
            <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-300">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse" style="border: 1px solid #d1d5db;">
                        <thead>
                            <tr>
                                <th class="border border-gray-300 bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 text-center" style="background-color: #f3f4f6; border: 1px solid #d1d5db;">No.</th>
                                <th class="border border-gray-300 bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-700 text-left" style="background-color: #f3f4f6; border: 1px solid #d1d5db;">Nama Instansi</th>
                                @foreach($monthlyRecap['month_names'] as $monthNum => $monthName)
                                    <th class="border border-gray-300 bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 text-center" style="background-color: #f3f4f6; border: 1px solid #d1d5db;">{{ $monthName }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyRecap['instansi_data'] as $index => $instansi)
                                <tr class="hover:bg-gray-50">
                                    <td class="border border-gray-300 px-3 py-2 text-sm text-center text-gray-700" style="border: 1px solid #d1d5db;">{{ $index + 1 }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm font-medium text-gray-900" style="border: 1px solid #d1d5db;">{{ $instansi['nama_instansi'] }}</td>
                                    @foreach($monthlyRecap['month_names'] as $monthNum => $monthName)
                                        @php
                                            $percentage = $instansi['monthly_percentages'][$monthNum]['percentage'] ?? 0;
                                            
                                            // Warna hitam putih saja
                                            $bgColor = '#ffffff'; // White
                                            $textColor = '#000000'; // Black
                                            
                                        @endphp
                                        <td class="attendance-cell border border-gray-300 px-3 py-2 text-sm text-center font-semibold" 
                                            data-bg-color="{{ $bgColor }}"
                                            data-text-color="{{ $textColor }}"
                                            style="--bg-color: {{ $bgColor }}; --text-color: {{ $textColor }}; background-color: var(--bg-color); color: var(--text-color);">
                                            {{ number_format($percentage, 0) }}%
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Note --}}
            <div class="mt-4 text-sm text-gray-600 italic">
                <p><strong>NB:</strong> kehadiran dalam % tiap bulan</p>
            </div>
        </div>
    </div>
</x-filament-panels::page>

