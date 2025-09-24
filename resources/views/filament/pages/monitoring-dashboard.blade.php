<x-filament::page>
    <h2 class="text-xl font-bold mb-4">Monitoring Real Time</h2>

    <div class="overflow-x-auto rounded-lg shadow">
        <table class="min-w-full border text-center">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border">Layanan</th>
                    <th class="px-4 py-2 border">Menunggu</th>
                    <th class="px-4 py-2 border">Sekarang</th>
                    <th class="px-4 py-2 border">Selesai</th>
                    <th class="px-4 py-2 border">Skip</th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->getMonitoringRealTime() as $service)
                    <tr class="bg-red-100">
                        <td class="border px-4 py-2">{{ $service->name }}</td>
                        <td class="border px-4 py-2">{{ $service->menunggu_count }}</td>
                        <td class="border px-4 py-2">{{ $service->sekarang_count }}</td>
                        <td class="border px-4 py-2">{{ $service->selesai_count }}</td>
                        <td class="border px-4 py-2">{{ $service->skip_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h2 class="text-xl font-bold mt-8 mb-4">Rekap Per Hari</h2>
    {{ $this->form }}

    {{-- REKAP PER HARI --}}
    <h2 class="text-xl font-bold my-6">Rekap Per Hari</h2>

    <div class="flex items-center space-x-4 mb-4">
        <div>
            <label class="block text-sm font-medium">Dari Tanggal</label>
            <input type="date" wire:model="from" class="border p-2 rounded">
        </div>
        <div>
            <label class="block text-sm font-medium">Sampai Tanggal</label>
            <input type="date" wire:model="to" class="border p-2 rounded">
        </div>
    </div>
    
    <table class="w-full border mt-4">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2">Jenis Instansi</th>
                <th class="border p-2">Jumlah Pemohon</th>
            </tr>
        </thead>
        <tbody>
        @foreach($this->getRekapJumlahPemohon() as $instansi)
            <tr class="bg-red-100">
                <td class="border px-4 py-2">{{ $instansi->name }}</td>
                <td class="border px-4 py-2">{{ $instansi->total_pemohon }}</td>
            </tr>
        @endforeach

        </tbody>
    </table>


    <div class="mt-4">
        <x-filament::button wire:click="exportExcel">Export Excel</x-filament::button>
    </div>
</x-filament::page>
