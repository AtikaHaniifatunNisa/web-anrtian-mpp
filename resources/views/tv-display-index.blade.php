<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Display - Pilih Zona</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .tv-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .zone-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .zone-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        .zone-number {
            font-size: 4rem;
            font-weight: 900;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .zone-title {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .service-count {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            font-weight: 600;
        }
        
        .instansi-count {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="tv-container">
    <!-- Header -->
    <div class="px-6 py-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <img src="{{ asset('img/logopemkot_white.png') }}" alt="Logo" class="h-20 object-contain">
                <div>
                    <h1 class="text-white text-4xl font-bold">SIOLA MALL PELAYANAN PUBLIK</h1>
                    <p class="text-white/80 text-lg">Jl. Tunjungan No.1-3, Genteng, Kec. Genteng, Surabaya</p>
                </div>
            </div>
            <div class="text-right text-white">
                <div class="text-4xl font-bold" id="current-time"></div>
                <div class="text-lg" id="current-date"></div>
            </div>
        </div>
        
        <!-- Title -->
        <div class="text-center mt-8">
            <h2 class="text-white text-5xl font-black uppercase tracking-wider">
                Pilih Zona TV Display
            </h2>
            <p class="text-white/80 text-xl mt-4">Klik zona untuk melihat tampilan TV panggilan antrian</p>
        </div>
    </div>

    <!-- Zones Grid -->
    <div class="px-6 pb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-8 max-w-7xl mx-auto" id="zones-container">
            <!-- Zones will be loaded here -->
        </div>
    </div>

    <script>
        // Zone data
        const zones = [
            { id: 5, name: 'ZONA 1', services: 15, instansi: 1, color: 'from-blue-500 to-blue-600' },
            { id: 20, name: 'ZONA 2', services: 9, instansi: 6, color: 'from-green-500 to-green-600' },
            { id: 29, name: 'ZONA 3', services: 11, instansi: 5, color: 'from-purple-500 to-purple-600' },
            { id: 40, name: 'ZONA 4', services: 10, instansi: 8, color: 'from-orange-500 to-orange-600' },
            { id: 109, name: 'ZONA 5', services: 3, instansi: 3, color: 'from-pink-500 to-pink-600' }
        ];
        
        // Update time
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString('id-ID');
            document.getElementById('current-date').textContent = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
        
        // Load zones
        function loadZones() {
            const container = document.getElementById('zones-container');
            container.innerHTML = '';
            
            zones.forEach(zone => {
                const zoneCard = document.createElement('div');
                zoneCard.className = 'zone-card rounded-3xl p-8 text-center cursor-pointer';
                zoneCard.onclick = () => window.open(`/tv-display/zona/${zone.id}`, '_blank');
                
                zoneCard.innerHTML = `
                    <div class="zone-number text-${zone.color.split('-')[1]}-600 mb-4">${zone.name.split(' ')[1]}</div>
                    <div class="zone-title text-gray-800 mb-6">${zone.name}</div>
                    
                    <div class="space-y-3 mb-6">
                        <div class="service-count inline-block">
                            ${zone.services} Layanan
                        </div>
                        <div class="instansi-count inline-block ml-2">
                            ${zone.instansi} Instansi
                        </div>
                    </div>
                    
                    <div class="text-gray-600 text-sm">
                        Klik untuk membuka TV Display
                    </div>
                    
                    <div class="mt-4">
                        <svg class="w-8 h-8 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                `;
                
                container.appendChild(zoneCard);
            });
        }
        
        // Initialize
        updateTime();
        setInterval(updateTime, 1000);
        loadZones();
    </script>
</body>
</html>
