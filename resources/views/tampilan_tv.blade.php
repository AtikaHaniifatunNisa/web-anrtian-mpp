<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antrian Digital - Loket Pelayanan Publik Kota Surabaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            height: 100%;
            width: 100%;
            overflow: hidden;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: white;
        }
        .queue-item {
            transition: all 0.3s ease;
        }
        .queue-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }
        .blink {
            animation: blink 1.5s infinite;
        }
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        /* Custom scrollbar untuk area antrian */
        .queue-scroll {
            scrollbar-width: thin;
            scrollbar-color: #3b82f6 #f1f5f9;
        }
        .queue-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .queue-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        .queue-scroll::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 3px;
        }
    </style>
</head>
<body class="flex flex-col h-screen">
    <!-- Main Container - Full Screen -->
    <div class="flex flex-col h-full w-full bg-white">
        <!-- Header -->
        <div class="bg-blue-900 text-white py-3 lg:py-4 px-4 lg:px-6">
            <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
                <div class="text-center sm:text-left">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-black tracking-wide">MALL PELAYANAN PUBLIK</h1>
                    <p class="text-lg sm:text-xl lg:text-2xl xl:text-3xl font-semibold mt-1 sm:mt-2">KOTA SURABAYA</p>
                    <P class="text-lg sm:text-xl lg:text-2xl xl:text-1xl font-regular mt-1 sm:mt-2">Jl. Tunjungan No.1-3, Genteng, Kec. Genteng, Surabaya, Jawa Timur 60275</p>
                </div>
                <div class="text-center sm:text-right">
                    <p class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-bold">ZONA 1</p>
                    <p class="text-base sm:text-lg lg:text-xl xl:text-2xl mt-1 sm:mt-2">LOKET: <span class="font-black text-xl sm:text-2xl lg:text-3xl xl:text-4xl">B001</span></p>
                </div>
            </div>
        </div>
        
        <!-- Content Area - Takes remaining space -->
        <div class="flex flex-col lg:flex-row flex-1 overflow-hidden">
            <!-- Left Side - Next Queue Numbers (Compact) -->
            <div class="w-full lg:w-2/5 bg-gray-50 p-3 lg:p-4 border-b-2 lg:border-b-0 lg:border-r-2 border-blue-200 flex flex-col">
                <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-center mb-3 text-gray-800 py-2 bg-blue-100 rounded-lg">
                    ANTRIAN BERIKUTNYA
                </h2>
                
                <div class="queue-scroll flex-1 overflow-y-auto pr-1">
                    <div class="grid grid-cols-1 gap-2 sm:gap-3">
                        <!-- Queue Item 1 -->
                        <div class="queue-item bg-white rounded-lg shadow-sm p-3 sm:p-4 flex items-center justify-between border-2 border-blue-200">
                            <div class="text-left">
                                <p class="text-gray-600 text-xs sm:text-sm mb-1">NOMOR ANTRIAN</p>
                                <p class="text-blue-700 text-base sm:text-lg lg:text-xl font-bold">LOKET PELAYANAN PUBLIK</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl sm:text-2xl lg:text-3xl font-black text-blue-800">A015</p>
                            </div>
                        </div>
                        
                        <!-- Queue Item 2 -->
                        <div class="queue-item bg-white rounded-lg shadow-sm p-3 sm:p-4 flex items-center justify-between border-2 border-blue-200">
                            <div class="text-left">
                                <p class="text-gray-600 text-xs sm:text-sm mb-1">NOMOR ANTRIAN</p>
                                <p class="text-blue-700 text-base sm:text-lg lg:text-xl font-bold">LOKET PELAYANAN PUBLIK</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl sm:text-2xl lg:text-3xl font-black text-blue-800">A016</p>
                            </div>
                        </div>
                        
                        <!-- Queue Item 3 -->
                        <div class="queue-item bg-white rounded-lg shadow-sm p-3 sm:p-4 flex items-center justify-between border-2 border-blue-200">
                            <div class="text-left">
                                <p class="text-gray-600 text-xs sm:text-sm mb-1">NOMOR ANTRIAN</p>
                                <p class="text-blue-700 text-base sm:text-lg lg:text-xl font-bold">LOKET PELAYANAN PUBLIK</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl sm:text-2xl lg:text-3xl font-black text-blue-800">A017</p>
                            </div>
                        </div>
                        
                        <!-- Queue Item 4 -->
                        <div class="queue-item bg-white rounded-lg shadow-sm p-3 sm:p-4 flex items-center justify-between border-2 border-blue-200">
                            <div class="text-left">
                                <p class="text-gray-600 text-xs sm:text-sm mb-1">NOMOR ANTRIAN</p>
                                <p class="text-blue-700 text-base sm:text-lg lg:text-xl font-bold">LOKET PELAYANAN PUBLIK</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl sm:text-2xl lg:text-3xl font-black text-blue-800">A018</p>
                            </div>
                        </div>
                        
                        <!-- Additional queue items -->
                        <div class="queue-item bg-white rounded-lg shadow-sm p-3 sm:p-4 flex items-center justify-between border-2 border-blue-200">
                            <div class="text-left">
                                <p class="text-gray-600 text-xs sm:text-sm mb-1">NOMOR ANTRIAN</p>
                                <p class="text-blue-700 text-base sm:text-lg lg:text-xl font-bold">LOKET PELAYANAN PUBLIK</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl sm:text-2xl lg:text-3xl font-black text-blue-800">A019</p>
                            </div>
                        </div>
                        
                        <div class="queue-item bg-white rounded-lg shadow-sm p-3 sm:p-4 flex items-center justify-between border-2 border-blue-200">
                            <div class="text-left">
                                <p class="text-gray-600 text-xs sm:text-sm mb-1">NOMOR ANTRIAN</p>
                                <p class="text-blue-700 text-base sm:text-lg lg:text-xl font-bold">LOKET PELAYANAN PUBLIK</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl sm:text-2xl lg:text-3xl font-black text-blue-800">A020</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Current Queue Number (Prominent) -->
            <div class="w-full lg:w-3/5 bg-gray-50 p-4 sm:p-6 lg:p-8 flex flex-col items-center justify-center">
                <div class="text-center mb-4 sm:mb-6 lg:mb-8 w-full">
                    <p class="text-gray-700 text-xl sm:text-2xl lg:text-3xl font-semibold mb-3 sm:mb-4">SEDANG DIPANGGIL</p>
                    <div class="pulse-animation bg-white rounded-xl sm:rounded-2xl lg:rounded-3xl p-4 sm:p-6 lg:p-8 w-full max-w-2xl mx-auto border-4 sm:border-6 lg:border-8 border-green-500 shadow-lg">
                        <h2 class="text-5xl sm:text-6xl lg:text-7xl xl:text-8xl 2xl:text-9xl font-black text-blue-800">A014</h2>
                    </div>
                    <p class="text-gray-800 text-xl sm:text-2xl lg:text-3xl font-bold mt-4 sm:mt-6 lg:mt-8">LOKET PELAYANAN PUBLIK</p>
                </div>
                
                <div class="bg-white rounded-xl sm:rounded-2xl lg:rounded-3xl shadow-lg p-4 sm:p-6 lg:p-8 w-full max-w-md lg:max-w-lg xl:max-w-2xl text-center border-4 border-yellow-400 mb-4 sm:mb-6">
                    <p class="text-gray-600 text-lg sm:text-xl lg:text-2xl mb-3 lg:mb-4">MENUJU LOKET</p>
                    <p class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-black text-blue-700 mb-3 lg:mb-4">B001</p>
                    <p class="text-gray-700 text-lg sm:text-xl lg:text-2xl font-semibold">LOKET PELAYANAN PUBLIK</p>
                </div>
                
                <div class="text-center">
                    <p class="text-gray-700 text-base sm:text-lg lg:text-xl font-medium mb-3 sm:mb-4">SILAHKAN MENUJU LOKET YANG TELAH DITETAPKAN</p>
                    <div class="flex justify-center space-x-2 sm:space-x-3 lg:space-x-4">
                        <div class="w-3 h-3 sm:w-4 sm:h-4 lg:w-5 lg:h-5 bg-green-500 rounded-full blink"></div>
                        <div class="w-3 h-3 sm:w-4 sm:h-4 lg:w-5 lg:h-5 bg-green-500 rounded-full blink" style="animation-delay: 0.3s"></div>
                        <div class="w-3 h-3 sm:w-4 sm:h-4 lg:w-5 lg:h-5 bg-green-500 rounded-full blink" style="animation-delay: 0.6s"></div>
                        <div class="w-3 h-3 sm:w-4 sm:h-4 lg:w-5 lg:h-5 bg-green-500 rounded-full blink" style="animation-delay: 0.9s"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="bg-gray-900 text-white py-2 sm:py-3 px-4 sm:px-6 text-center">
            <p class="text-sm sm:text-base">© 2023 Loket Pelayanan Publik Kota Surabaya. Semua Hak Dilindungi.</p>
        </div>
    </div>

    <!-- Auto-refresh script -->
    <script>
        // Auto-refresh setiap 10 detik untuk update antrian
        setTimeout(function() {
            location.reload();
        }, 10000);
        
        // Animasi untuk nomor antrian yang sedang dipanggil
        const currentNumber = document.querySelector('.pulse-animation');
        
        setInterval(() => {
            currentNumber.classList.toggle('shadow-lg');
            currentNumber.classList.toggle('shadow-xl');
        }, 2000);
        
        // Tambahan efek visual untuk menarik perhatian pada antrian berikutnya
        const queueItems = document.querySelectorAll('.queue-item');
        let currentItem = 0;
        
        if (queueItems.length > 0) {
            setInterval(() => {
                queueItems.forEach(item => {
                    item.classList.remove('border-yellow-400', 'bg-yellow-50');
                    item.classList.add('border-blue-200');
                });
                
                // Highlight antrian berikutnya yang akan dipanggil
                queueItems[currentItem].classList.remove('border-blue-200');
                queueItems[currentItem].classList.add('border-yellow-400', 'bg-yellow-50');
                
                currentItem = (currentItem + 1) % queueItems.length;
            }, 2000);
        }
        
        // Deteksi perangkat dan sesuaikan perilaku
        function detectDevice() {
            const width = window.innerWidth;
            if (width >= 1920) {
                console.log("TV Screen Detected - Fullscreen mode");
            } else if (width >= 1024) {
                console.log("Desktop Screen Detected");
            } else if (width >= 768) {
                console.log("Tablet Screen Detected");
            } else {
                console.log("Mobile Screen Detected");
            }
        }
        
        // Panggil saat halaman dimuat dan saat ukuran layar berubah
        window.addEventListener('load', detectDevice);
        window.addEventListener('resize', detectDevice);
        
        // Fullscreen functionality
        function enterFullscreen() {
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
        }
        
        // Enter fullscreen automatically (optional)
        // enterFullscreen();
    </script>
</body>
</html>