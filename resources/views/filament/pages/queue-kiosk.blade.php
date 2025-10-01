<x-filament::page>
    @if (!$selectedCounter)
        {{-- ===== Gambar 1: PILIH ZONA ===== --}}
        <div class="flex items-center justify-between px-6 py-4" style="background-color:#0D009A;">
            <div class="w-24 flex justify-center">
                <img src="{{ asset('img/logokiri.png') }}" alt="Logo Kiri" class="h-24 object-contain">
            </div>
            <div class="text-center flex-1 text-white">
                <h1 class="text-3xl font-bold tracking-wide">MALL PELAYANAN PUBLIK</h1>
                <p class="mt-2 text-base">Jl. Tunjungan No.1-3, Genteng, Kec. Genteng, Surabaya, Jawa Timur 60275</p>
            </div>
            <div class="w-28 flex justify-center">
                <img src="{{ asset('img/logokanan.png') }}" alt="Logo DPMPTSP" class="h-24 object-contain">
            </div>
        </div>

        <p class="text-center text-gray-600 mt-6">Silakan pilih Zona untuk melihat instansi</p>

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
        {{-- ===== Sudah pilih ZONA ===== --}}
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">{{ $counters[$selectedCounter]['name'] }}</h2>

            <div class="flex gap-3">
                @if($selectedInstansi)
                    <button wire:click="resetInstansi"
                        class="bg-yellow-500 font-bold text-white px-5 py-2 rounded-lg shadow hover:bg-yellow-600">
                        ← Kembali ke Instansi
                    </button>
                @endif
                <button wire:click="resetSelection"
                    class="bg-pink-400 font-bold text-white px-5 py-2 rounded-lg shadow hover:bg-pink-500 ml-4">
                    ← Kembali ke Zona
                </button>
            </div>
        </div>

        {{-- ===== Gambar 2: daftar INSTANSI dari DB ===== --}}
        @if(!$selectedInstansi)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse($instansis as $instansi)
                    <button
                        wire:click="selectInstansi({{ $instansi->instansi_id }})"
                        class="bg-pink-100 text-pink-900 p-4 rounded-xl shadow hover:bg-pink-200 transition">
                        {{ $instansi->nama_instansi }}
                    </button>
                @empty
                    <div class="col-span-full text-center text-gray-500 py-8">
                        Belum ada instansi untuk zona ini.
                    </div>
                @endforelse
            </div>

        {{-- ===== Gambar 3: daftar LAYANAN (tampilan seperti mockup) ===== --}}
        @else
            @php
                $instansiNow  = $instansis->firstWhere('instansi_id', $selectedInstansi);
                $instansiName = $instansiNow?->nama_instansi ?? 'Instansi';
                // pretty title khusus UPTSP biar sesuai mockup
                $prettyTitle  = strtoupper($instansiName) === 'UPTSP'
                    ? 'Unit Pelayanan Terpadu Satu Pintu (UPTSP)'
                    : $instansiName;
            @endphp

            <div class="mpp-board relative overflow-hidden rounded-2xl">
                {{-- background foto gedung --}}
                <div class="absolute inset-0 bg-cover bg-center opacity-90"
                     style="background-image:url('{{ asset('img/bg.png') }}');"></div>
                <div class="absolute inset-0 bg-black/20"></div>

                {{-- banner judul instansi --}}
                <div class="relative flex justify-center">
                    <div class="mpp-title shadow-lg">
                        <span class="block text-white text-2xl md:text-3xl font-extrabold text-center leading-tight">
                            {{ $prettyTitle }}
                        </span>
                    </div>
                </div>

                {{-- grid kartu layanan --}}
                <div class="relative mx-auto max-w-6xl px-6 pb-10 pt-6">
                    <div class="flex flex-wrap gap-6 md:gap-8 justify-center">
                        @forelse($services as $service)
                            <button
                                wire:click="selectService({{ $service->id }})"
                                class="mpp-chip group">
                                <span class="mpp-chip-label">
                                    {{ $service->name ?? $service->nama_service ?? '-' }}
                                </span>
                            </button>
                        @empty
                            <div class="text-center text-white/90 py-10">
                                Belum ada layanan untuk instansi ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- action cetak --}}
            <div class="mt-6 text-center">
                @if($selectedService)
                    <div class="mb-3 text-sm text-gray-300">
                        Layanan dipilih:
                        <span class="font-semibold text-white">
                            {{ $selectedService->name ?? $selectedService->nama_service }}
                        </span>
                    </div>
                    <button
                        class="bg-green-500 font-bold text-white px-6 py-3 rounded-lg shadow hover:bg-green-600"
                        wire:click="printStruk({{ $selectedService->id }})">
                        Cetak Struk
                    </button>
                    <button
                        class="bg-blue-500 font-bold text-white px-6 py-3 rounded-lg shadow hover:bg-blue-600 ml-4"
                        wire:click="printBarcode({{ $selectedService->id }})">
                        Cetak Barcode
                    </button>
                @else
                    <div class="text-gray-500">Silakan pilih layanan terlebih dahulu</div>
                @endif
            </div>
        @endif
    @endif
</x-filament::page>

@push('styles')
<style>
    html, body { height: 100%; overflow-y: auto !important; }

    /* ====== BOARD ala mockup (Gambar 3) ====== */
    .mpp-board {
        margin-top: .5rem;
        background: linear-gradient(180deg, rgba(13,0,154,.08), rgba(13,0,154,.08));
        border: 1px solid rgba(255,255,255,.08);
    }
    .mpp-title{
        margin-top: 1.25rem;
        background: #0D009A;
        border-radius: 1.25rem;
        padding: .9rem 1.25rem;
        min-width: 320px;
        max-width: 720px;
        width: fit-content;
        border: 6px solid rgba(255,255,255,.92);
    }
    .mpp-chip{
        width: 15.5rem;   /* ~248px */
        height: 4.5rem;
        border-radius: 1rem;
        background: linear-gradient(180deg, #9DB0FF 0%, #7E8DFF 100%);
        box-shadow: 0 10px 18px rgba(0,0,0,.18);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .75rem 1rem;
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        border: 2px solid rgba(255,255,255,.65);
        backdrop-filter: blur(2px);
    }
    .mpp-chip:hover{
        transform: translateY(-3px);
        box-shadow: 0 14px 24px rgba(0,0,0,.22);
        filter: brightness(1.03);
    }
    .mpp-chip:active{ transform: translateY(-1px) scale(.995); }
    .mpp-chip-label{
        color: #0d0d0d;
        font-weight: 700;
        text-align: center;
        line-height: 1.2;
        font-size: .98rem;
    }
    @media (max-width: 480px){
        .mpp-chip{ width: 12.5rem; height: 4rem; }
        .mpp-chip-label{ font-size: .92rem; }
    }

    /* ==== sisa styling lama (opsional tetap) ==== */
    .service-card { background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; }
    .service-card:hover { transform: translateY(-12px) scale(1.03); box-shadow: 0 30px 60px -12px rgba(0,0,0,0.3); }
    .service-card:active { transform: translateY(-8px) scale(1.01); }
    .service-icon { transition: all 0.5s ease; }
    .status-indicator { width:14px; height:14px; background:#10b981; border-radius:50%; position:relative; }
    .status-indicator::before { content:''; position:absolute; width:14px; height:14px; background:#10b981; border-radius:50%; animation:pulse 2s infinite; }
    @keyframes pulse { 0%{transform:scale(0.95); box-shadow:0 0 0 0 rgba(16,185,129,0.7);} 70%{transform:scale(1); box-shadow:0 0 0 15px rgba(16,185,129,0);} 100%{transform:scale(0.95); box-shadow:0 0 0 0 rgba(16,185,129,0);} }
    .clock-display { font-family:'Inter', monospace; font-weight:600; letter-spacing:0.1em; }
    .ripple-effect { position:absolute; border-radius:50%; background:rgba(59,130,246,0.3); transform:scale(0); animation:ripple 0.6s linear; pointer-events:none; }
    @keyframes ripple { to { transform:scale(4); opacity:0; } }
    .service-card:active .ripple-effect { animation:ripple 0.6s linear; }
    .floating-shape { position:absolute; border-radius:50%; background:linear-gradient(135deg, rgba(59,130,246,0.1), rgba(147,51,234,0.1)); animation:float 6s ease-in-out infinite; }
    @keyframes float { 0%,100%{transform:translateY(0px);} 50%{transform:translateY(-20px);} }
    @media (max-height: 768px){ .text-6xl{font-size:3rem;} .text-7xl{font-size:4rem;} .p-12{padding:2rem;} .mb-16{margin-bottom:2rem;} }
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

    Livewire.on('print-start', async (payload) => {
        const text = typeof payload === 'string' ? payload : (payload?.text ?? '')
        if (text) await printThermal(text)
    })

    Livewire.on('notify', (payload) => {
        const msg = typeof payload === 'string' ? payload : (payload?.message ?? '')
        if (msg) alert(msg)
    })
})
</script>
@endpush
