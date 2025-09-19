<x-filament::page>
    @if (!$selectedCounter)
        {{-- Step 1: Pilih Counter (Zona) --}}
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold">MALL PELAYANAN PUBLIK</h1>
            <p class="text-sm text-gray-600">
                Jl. Tunjungan No.1-3, Genteng, Kec. Genteng, Surabaya, Jawa Timur 60275
            </p>
            <p class="text-gray-500 mt-2">Silakan pilih Zona untuk melihat layanan</p>
        </div>

        {{-- Baris pertama: max 3 zona --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach(array_slice($counters, 0, 3, true) as $id => $counter)
                <div class="bg-gradient-to-b from-pink-100 to-pink-300 p-6 rounded-xl shadow">
                    <button wire:click="selectCounter({{ $id }})"
                        class="w-full font-bold text-lg bg-white px-4 py-2 rounded-full shadow hover:bg-pink-200 transition">
                        {{ $counter['name'] }}
                    </button>
                    <ul class="mt-4 text-left text-sm list-disc list-inside text-gray-700">
                        @foreach($counter['services'] as $service)
                            <li>{{ is_array($service) ? ($service['nama_service'] ?? $service['name'] ?? '-') : $service }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        {{-- Baris kedua: sisa zona diratakan ke tengah --}}
        <div class="mt-6 flex justify-center gap-6 flex-wrap">
            @foreach(array_slice($counters, 3, null, true) as $id => $counter)
                <div class="bg-gradient-to-b from-pink-100 to-pink-300 p-6 rounded-xl shadow w-80">
                    <button wire:click="selectCounter({{ $id }})"
                        class="w-full font-bold text-lg bg-white px-4 py-2 rounded-full shadow hover:bg-pink-200 transition">
                        {{ $counter['name'] }}
                    </button>
                    <ul class="mt-4 text-left text-sm list-disc list-inside text-gray-700">
                        @foreach($counter['services'] as $service)
                            <li>{{ is_array($service) ? ($service['nama_service'] ?? $service['name'] ?? '-') : $service }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

    @else
        {{-- Step 2: Pilih Layanan --}}
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">{{ $counters[$selectedCounter]['name'] }}</h2>
            <button wire:click="resetSelection" class="bg-pink-400 text-white px-5 py-2 rounded-lg shadow hover:bg-pink-500 ml-4">
                ← Kembali ke Zona
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($counters[$selectedCounter]['services'] as $service)
                <button
                    class="bg-white p-4 rounded-xl shadow hover:bg-pink-100 transition">
                    {{ is_array($service) ? ($service['nama_service'] ?? $service['name'] ?? '-') : $service }}
                </button>
            @endforeach
        </div>

        {{-- Step 3: Cetak Antrian --}}
        <div class="mt-6 text-center">
            <button class="bg-green-500 text-white px-6 py-3 rounded-lg shadow hover:bg-green-600">
                Cetak Struk
            </button>
            <button class="bg-blue-500 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-600 ml-4">
                Cetak Barcode
            </button>
        </div>
    @endif
</x-filament::page>


@push('styles')
<style>
    .service-card {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .service-card:hover {
        transform: translateY(-12px) scale(1.03);
        box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.3);
    }

    .service-card:active {
        transform: translateY(-8px) scale(1.01);
    }

    .service-icon {
        transition: all 0.5s ease;
    }

    .status-indicator {
        width: 14px;
        height: 14px;
        background: #10b981;
        border-radius: 50%;
        position: relative;
    }

    .status-indicator::before {
        content: '';
        position: absolute;
        width: 14px;
        height: 14px;
        background: #10b981;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 15px rgba(16, 185, 129, 0);
        }

        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    .clock-display {
        font-family: 'Inter', monospace;
        font-weight: 600;
        letter-spacing: 0.1em;
    }

    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background: rgba(59, 130, 246, 0.3);
        transform: scale(0);
        animation: ripple 0.6s linear;
        pointer-events: none;
    }

    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }

    .service-card:active .ripple-effect {
        animation: ripple 0.6s linear;
    }

    /* Kiosk Mode Styles */
    body {
        overflow: hidden;
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }

    /* Floating Animation */
    .floating-shape {
        position: absolute;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1));
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    /* Responsive Design for Kiosk */
    @media (max-height: 768px) {
        .text-6xl {
            font-size: 3rem;
        }

        .text-7xl {
            font-size: 4rem;
        }

        .p-12 {
            padding: 2rem;
        }

        .mb-16 {
            margin-bottom: 2rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        const connectButton = document.getElementById('connect-button');

        if (connectButton) {
            connectButton.addEventListener('click', async () => {
                window.connectedPrinter = await getPrinter()
            })
        }

        Livewire.on("print-start", async (text) => {
            await printThermal(text)
        })
    })
</script>
@endpush