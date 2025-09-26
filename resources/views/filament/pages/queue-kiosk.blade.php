<x-filament::page>
    @if (!$selectedCounter)
        <div class="flex items-center justify-between px-6 py-4" style="background-color:#0D009A;">
            <div class="w-24 flex justify-center">
                <img src="{{ asset('img/logokiri.png') }}" alt="Logo Kiri" class="h-24 object-contain">
            </div>
            <div class="text-center flex-1 text-white">
                <h1 class="text-3xl font-bold tracking-wide">MALL PELAYANAN PUBLIK</h1>
                <p class="mt-2 text-base">
                    Jl. Tunjungan No.1-3, Genteng, Kec. Genteng, Surabaya, Jawa Timur 60275
                </p>
            </div>
            <div class="w-28 flex justify-center">
                <img src="{{ asset('img/logokanan.png') }}" alt="Logo DPMPTSP" class="h-24 object-contain">
            </div>
        </div>

        <p class="text-center text-gray-600 mt-6">Silakan pilih Zona untuk melihat layanan</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            @foreach(array_slice($counters, 0, 3, true) as $id => $counter)
                <div class="relative rounded-3xl shadow-lg p-6 text-black" style="background-color:#8A8CFF;">
                    
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2">
                        <button
                            wire:click="selectCounter({{ $id }})"
                            class="px-8 py-3 bg-white rounded-[2rem] border border-black shadow-md font-bold text-lg uppercase">
                            {{ $counter['name'] }}
                        </button>
                    </div>

                    <ul class="mt-10 list-disc list-inside space-y-2 text-base font-medium">
                        @foreach($counter['services'] as $service)
                            <li class="underline underline-offset-2">
                                {{ is_array($service) ? ($service['nama_service'] ?? $service['name'] ?? '-') : $service }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
        <div class="mt-6 flex justify-center gap-6 flex-wrap">
            @foreach(array_slice($counters, 3, null, true) as $id => $counter)
                <div class="relative w-80 rounded-3xl shadow-lg p-6 text-black" style="background-color:#8A8CFF;">
                    
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2">
                        <button
                            wire:click="selectCounter({{ $id }})"
                            class="px-8 py-3 bg-white rounded-[2rem] border border-black shadow-md font-bold text-lg uppercase">
                            {{ $counter['name'] }}
                        </button>
                    </div>
            
                    <ul class="mt-10 list-disc list-inside space-y-2 text-base font-medium">
                        @foreach($counter['services'] as $service)
                            <li class="underline underline-offset-2">
                                {{ is_array($service) ? ($service['nama_service'] ?? $service['name'] ?? '-') : $service }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

    @else
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">{{ $counters[$selectedCounter]['name'] }}</h2>
            <button wire:click="resetSelection" class="bg-pink-400 font-bold text-white px-5 py-2 rounded-lg shadow hover:bg-pink-500 ml-4">
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

        <div class="mt-6 text-center">
            <button class="bg-green-500 font-bold text-white px-6 py-3 rounded-lg shadow hover:bg-green-600">
                Cetak Struk
            </button>
            <button class="bg-blue-500 font-bold text-white px-6 py-3 rounded-lg shadow hover:bg-blue-600 ml-4">
                Cetak Barcode
            </button>
        </div>
    @endif
</x-filament::page>


@push('styles')
<style>
    html, body {
        height: 100%;
        overflow-y: auto !important;
    }

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

    .floating-shape {
        position: absolute;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1));
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }

    @media (max-height: 768px) {
        .text-6xl { font-size: 3rem; }
        .text-7xl { font-size: 4rem; }
        .p-12 { padding: 2rem; }
        .mb-16 { margin-bottom: 2rem; }
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