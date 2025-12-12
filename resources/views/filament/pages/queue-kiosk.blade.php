<x-filament::page>
    @if (!$selectedCounter)
        {{-- ===== Gambar 1: PILIH ZONA ===== --}}
        <div class="flex items-center justify-between px-6 py-4" style="background-color:#0D009A;">
            <div class="w-24 flex justify-center">
                <img src="{{ asset('img/logopemkot_white.png') }}" alt="Logo Kiri" class="h-24 object-contain">
            </div>
            <div class="text-center flex-1 text-white">
                <h1 class="text-3xl font-bold tracking-wide">MALL PELAYANAN PUBLIK</h1>
                <p class="mt-2 text-base">Jl. Tunjungan No.1-3, Genteng, Kec. Genteng, Surabaya, Jawa Timur 60275</p>
            </div>
            <div class="w-28 flex justify-center">
                <img src="{{ asset('img/dpmptsp.png') }}" alt="Logo DPMPTSP" class="h-24 object-contain">
            </div>
        </div>

        <p class="text-center text-gray-600 mt-6">Silakan pilih Zona untuk melihat instansi</p>

        {{-- Layout Zona Persis seperti Gambar Kedua --}}
        <div class="max-w-6xl mx-auto px-4 mt-8">
            {{-- Baris Pertama: 3 Zona --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                @foreach(['1' => 1, '2' => 2, '3' => 3] as $label => $id)
                    @if(isset($counters[$id]))
                        <div 
                            wire:click="selectCounter({{ $id }})"
                            class="zona-card-new relative rounded-3xl shadow-lg cursor-pointer hover:shadow-xl transition-all duration-300 hover:scale-[1.02]" 
                            style="background: #8A8CFF; min-height: 300px; padding: 1.5rem;">
                            
                            {{-- Label Zona --}}
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                                <div class="zona-label-new px-6 py-2 bg-white rounded-full border-2 border-black shadow-md">
                                    <span class="font-bold text-lg text-black">ZONA {{ $label }}</span>
                                </div>
                            </div>
                            
                            {{-- Content Area --}}
                            <div class="pt-8 pb-4">
                                {{-- Daftar Instansi --}}
                                <ul class="space-y-2 text-black">
                                    @foreach($counters[$id]['services'] as $service)
                                        <li class="flex items-start">
                                            <span class="text-black mr-2 mt-1 text-lg">•</span>
                                            <span class="font-semibold text-base leading-snug underline">
                                                {{ is_array($service) ? ($service['nama_service'] ?? $service['name'] ?? '-') : $service }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Baris Kedua: 2 Zona (Centered) --}}
            <div class="flex justify-center gap-6">
                @foreach(['4' => 4, '5' => 5] as $label => $id)
                    @if(isset($counters[$id]))
                        <div 
                            wire:click="selectCounter({{ $id }})"
                            class="zona-card-new relative rounded-3xl shadow-lg cursor-pointer hover:shadow-xl transition-all duration-300 hover:scale-[1.02]" 
                            style="background: #8A8CFF; width: 300px; min-height: 300px; padding: 1.5rem;">
                            
                            {{-- Label Zona --}}
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                                <div class="zona-label-new px-6 py-2 bg-white rounded-full border-2 border-black shadow-md">
                                    <span class="font-bold text-lg text-black">ZONA {{ $label }}</span>
                                </div>
                            </div>
                            
                            {{-- Content Area --}}
                            <div class="pt-8 pb-4">
                                {{-- Daftar Instansi --}}
                                <ul class="space-y-2 text-black">
                                    @foreach($counters[$id]['services'] as $service)
                                        <li class="flex items-start">
                                            <span class="text-black mr-2 mt-1 text-lg">•</span>
                                            <span class="font-semibold text-base leading-snug underline">
                                                {{ is_array($service) ? ($service['nama_service'] ?? $service['name'] ?? '-') : $service }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        @else
        {{-- ===== Sudah pilih ZONA ===== --}}
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">{{ $counters[$selectedCounter]['name'] ?? 'Zona ' . $selectedCounter }}</h2>

            <div class="flex gap-3">
                @if($selectedInstansi && $selectedCounter != 1)
                    <button wire:click="resetInstansi"
                        class="back-btn-neutral font-bold text-black px-6 py-3 rounded-2xl">
                        ← Kembali ke Instansi
                    </button>
                @endif
                <button wire:click="resetSelection"
                    class="back-btn-neutral font-bold text-black px-6 py-3 rounded-2xl ml-4">
                    ← Kembali ke Zona
                </button>
            </div>
        </div>

        {{-- ===== Tampilan Instansi dengan Background Gedung (seperti Screenshot Pertama) ===== --}}
        @if(!$selectedInstansi && $instansis->count() > 1)
            <div class="mpp-board-instansi relative overflow-hidden rounded-2xl mt-6">
                {{-- Background foto gedung --}}
                <div class="absolute inset-0 bg-cover bg-center opacity-90 mpp-bg-image-instansi"></div>
                <div class="absolute inset-0 bg-black/30"></div>

                {{-- Content Area dengan Centering yang Lebih Baik --}}
                <div class="instansi-container-centered">
                    @forelse($instansis as $instansi)
                        <button
                            wire:click="selectInstansi({{ $instansi->instansi_id }})"
                            class="instansi-chip-blue group">
                            <span class="instansi-chip-label-blue">
                                {{ $instansi->nama_instansi }}
                            </span>
                        </button>
                    @empty
                        <div class="w-full text-center text-white/90 py-12">
                            <div class="bg-black/20 rounded-2xl p-8 backdrop-blur-sm">
                                <p class="text-xl font-semibold mb-2">Belum ada instansi</p>
                                <p class="text-white/70">Belum ada instansi untuk zona ini.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

        {{-- ===== daftar LAYANAN (tampilan seperti mockup) ===== --}}
        @elseif($selectedInstansi)
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
                <div class="absolute inset-0 bg-cover bg-center opacity-90 mpp-bg-image"></div>
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
            <div class="mt-6 text-center" style="display: none;">
                {{-- Tombol cetak disembunyikan karena auto-print sudah aktif --}}
                
                @if($selectedService)
                    <div class="mb-4 text-base bg-blue-50 px-6 py-3 rounded-xl border-2 border-blue-300 shadow-lg">
                        <span class="font-semibold text-gray-800">Layanan dipilih:</span>
                        <span class="font-bold text-blue-900 ml-2 text-lg">
                            {{ $selectedService->name ?? $selectedService->nama_service }}
                        </span>
                    </div>
                    {{-- Tombol Cetak Struk disembunyikan --}}
                    <button
                        class="bg-green-500 font-bold text-white px-6 py-3 rounded-lg shadow hover:bg-green-600"
                        wire:click="printStruk({{ $selectedService->id }})"
                        style="display: none;">
                        Cetak Struk
                    </button>
                    {{-- Tombol Cetak Barcode disembunyikan (tidak dihapus) --}}
                    <button
                        class="bg-blue-500 font-bold text-white px-6 py-3 rounded-lg shadow hover:bg-blue-600 ml-4"
                        wire:click="printBarcode({{ $selectedService->id }})"
                        style="display: none;">
                        Cetak Barcode
                    </button>
                    
                @else
                    <div class="text-gray-500">Silakan pilih layanan terlebih dahulu</div>
                    
                    {{-- Test button untuk memilih layanan pertama --}}
                    @if($services->count() > 0)
                        <div class="mt-4">
                            <button 
                                wire:click="selectService({{ $services->first()->id }})"
                                class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                                Test: Pilih {{ $services->first()->name }}
                            </button>
                        </div>
                    @endif
                @endif
            </div>
        @endif
    @endif

</x-filament::page>

@push('styles')
<style>
    html, body { height: 100%; overflow-y: auto !important; }

    /* ====== ZONA CARDS NEW STYLING (Matching Second Image) ====== */
    .zona-card-new {
        position: relative;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .zona-card-new:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
    }
    
    .zona-label-new {
        background: #ffffff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    
    .zona-card-new:hover .zona-label-new {
        transform: scale(1.02);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }

    /* ====== RESPONSIVE DESIGN FOR NEW ZONA CARDS ====== */
    @media (max-width: 1024px) {
        .zona-card-new {
            min-height: 280px;
        }
    }
    
    @media (max-width: 768px) {
        .zona-card-new {
            min-height: 260px;
            padding: 1.25rem;
        }
        
        .zona-label-new {
            font-size: 1rem;
            padding: 0.5rem 1.25rem;
        }
    }
    
    @media (max-width: 480px){
        .zona-card-new {
            min-height: 240px;
            padding: 1rem;
        }
        
        .zona-label-new {
            font-size: 0.9rem;
            padding: 0.4rem 1rem;
        }
        
        .instansi-card-pink {
            min-height: 100px;
        }
    }

    /* ====== ANIMATIONS FOR NEW ZONA CARDS ====== */
    @keyframes slideInScale {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .zona-card-new {
        animation: slideInScale 0.5s ease-out;
    }
    
    .zona-card-new:nth-child(1) { animation-delay: 0.1s; }
    .zona-card-new:nth-child(2) { animation-delay: 0.2s; }
    .zona-card-new:nth-child(3) { animation-delay: 0.3s; }

    /* ====== INSTANSI CARDS PINK STYLING ====== */
    .instansi-card-pink {
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .instansi-card-pink::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        transition: left 0.5s;
    }
    
    .instansi-card-pink:hover::before {
        left: 100%;
    }
    
    .instansi-card-pink:hover {
        transform: translateY(-4px) scale(1.03);
        box-shadow: 0 15px 30px rgba(236, 72, 153, 0.25);
    }

    /* ====== ANIMATIONS FOR INSTANSI CARDS ====== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .instansi-card-pink {
        animation: fadeInUp 0.4s ease-out;
    }

    /* ====== INSTANSI BOARD WITH BACKGROUND (Like First Screenshot) ====== */
    .mpp-board-instansi {
        margin-top: 1rem;
        background: linear-gradient(180deg, rgba(13,0,154,.08), rgba(13,0,154,.08));
        border: 1px solid rgba(255,255,255,.08);
        min-height: 450px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .mpp-bg-image-instansi {
        background-image: url('{{ asset("img/bg.png") }}');
        background-size: cover;
        background-position: center;
    }

    /* ====== CENTERING CONTAINER FOR INSTANSI ====== */
    .instansi-container-centered {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        align-content: center;
        gap: 1rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
        min-height: 300px;
    }
    
    /* Alternative Grid Layout for Perfect Centering */
    .instansi-grid-centered {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(15.5rem, 1fr));
        gap: 1rem;
        justify-items: center;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }

    /* ====== INSTANSI CHIPS BLUE STYLING ====== */
    .instansi-chip-blue {
        width: 15.5rem;   /* ~248px - sama seperti mpp-chip */
        height: 4rem;
        border-radius: 1rem;
        background: linear-gradient(180deg, #9DB0FF 0%, #7E8DFF 100%);
        box-shadow: 0 8px 20px rgba(0,0,0,.15);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .75rem 1rem;
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        border: 2px solid rgba(255,255,255,.65);
        backdrop-filter: blur(2px);
    }
    
    .instansi-chip-blue:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(0,0,0,.25);
        filter: brightness(1.05);
    }
    
    .instansi-chip-blue:active { 
        transform: translateY(-1px) scale(.995); 
    }
    
    .instansi-chip-label-blue {
        color: #0d0d0d;
        font-weight: 700;
        text-align: center;
        line-height: 1.2;
        font-size: .95rem;
    }

    /* ====== RESPONSIVE FOR INSTANSI CHIPS ====== */
    @media (max-width: 768px) {
        .instansi-chip-blue {
            width: 13rem;
            height: 3.5rem;
        }
        
        .instansi-chip-label-blue {
            font-size: .9rem;
        }
        
        .instansi-container-centered {
            padding: 1.5rem;
            gap: 0.8rem;
        }
        
        .instansi-grid-centered {
            grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
            gap: 0.8rem;
            padding: 1.5rem;
        }
    }
    
    @media (max-width: 480px) {
        .instansi-chip-blue {
            width: 12rem;
            height: 3rem;
        }
        
        .instansi-chip-label-blue {
            font-size: .85rem;
        }
        
        .instansi-container-centered {
            padding: 1rem;
            gap: 0.6rem;
        }
        
        .instansi-grid-centered {
            grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
            gap: 0.6rem;
            padding: 1rem;
        }
        
        .mpp-board-instansi {
            min-height: 350px;
        }
    }

    /* ====== ANIMATIONS FOR INSTANSI CHIPS ====== */
    @keyframes slideInFromBottom {
        from {
            opacity: 0;
            transform: translateY(40px) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .instansi-chip-blue {
        animation: slideInFromBottom 0.6s ease-out;
    }
    
    .instansi-chip-blue:nth-child(1) { animation-delay: 0.1s; }
    .instansi-chip-blue:nth-child(2) { animation-delay: 0.2s; }
    .instansi-chip-blue:nth-child(3) { animation-delay: 0.3s; }
    .instansi-chip-blue:nth-child(4) { animation-delay: 0.4s; }
    .instansi-chip-blue:nth-child(5) { animation-delay: 0.5s; }
    .instansi-chip-blue:nth-child(6) { animation-delay: 0.6s; }

    /* ====== BACK BUTTONS BLUE STYLING (Matching Instansi/Service Buttons) ====== */
    .back-btn-neutral {
        background: linear-gradient(180deg, #9DB0FF 0%, #7E8DFF 100%);
        border: 2px solid rgba(255,255,255,0.65);
        backdrop-filter: blur(2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
    }
    
    .back-btn-neutral:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.25);
        filter: brightness(1.05);
    }
    
    .back-btn-neutral:active {
        transform: translateY(-1px) scale(0.995);
    }
    
    .back-btn-neutral .text-black {
        color: #0d0d0d;
        font-weight: 700;
    }

    /* ====== RESPONSIVE FOR BACK BUTTONS ====== */
    @media (max-width: 768px) {
        .back-btn-neutral {
            padding: 0.75rem 1.25rem;
            font-size: 0.9rem;
        }
    }
    
    @media (max-width: 480px) {
        .back-btn-neutral {
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
        }
    }

    /* ====== BOARD ala mockup (Gambar 3) ====== */
    .mpp-board {
        margin-top: .5rem;
        background: linear-gradient(180deg, rgba(13,0,154,.08), rgba(13,0,154,.08));
        border: 1px solid rgba(255,255,255,.08);
    }
    .mpp-bg-image {
        background-image: url('{{ asset("img/bg.png") }}');
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


    Livewire.on('open-pdf', (payload) => {
        console.log('Opening PDF:', payload);
        const url = payload?.url || payload;
        if (url) {
            window.open(url, '_blank');
        }
    });

    Livewire.on('open-barcode', (payload) => {
        console.log('Opening Barcode:', payload);
        const url = payload?.url || payload;
        if (url) {
            window.open(url, '_blank');
        }
    });
})
</script>
@endpush