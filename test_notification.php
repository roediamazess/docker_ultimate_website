<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contoh Notifikasi Flip Lingkaran dari Logo</title>
    
    <!-- Menggunakan Tailwind CSS untuk styling cepat dan modern -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Menggunakan Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Style dasar untuk body dan font */
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Kontainer untuk semua notifikasi, diposisikan di bawah logo */
        #notification-stack {
            position: fixed;
            top: 5rem; /* 80px, beri ruang di bawah logo */
            left: 1.5rem; /* 24px */
            z-index: 100;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            perspective: 1000px; /* Penting untuk efek 3D semua kartu */
        }

        /* Kontainer untuk setiap kartu flip */
        .flip-card-container {
            transform-origin: top left; /* Animasi berpusat dari pojok kiri atas */
            animation: emerge-from-logo 0.5s cubic-bezier(0.21, 1.02, 0.73, 1) forwards;
        }

        /* Kartu flip berbentuk lingkaran, ukuran diperkecil */
        .flip-card {
            width: 120px;
            height: 120px;
            position: relative;
        }

        .flip-card-inner {
            position: absolute;
            width: 100%;
            height: 100%;
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }

        /* Saat di-hover, putar kartu */
        .flip-card:hover .flip-card-inner {
            transform: rotateY(180deg);
        }

        /* Style untuk sisi depan dan belakang kartu */
        .flip-card-front, .flip-card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-backface-visibility: hidden; /* Safari */
            backface-visibility: hidden;
            border-radius: 9999px; /* Membuat jadi lingkaran penuh */
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .flip-card-back {
            transform: rotateY(180deg);
        }
        
        /* Animasi "muncul dari logo" */
        @keyframes emerge-from-logo {
            from {
                opacity: 0;
                transform: translateY(-40px) scale(0.5);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Animasi saat notifikasi hilang */
        .fade-out {
            animation: fade-out-anim 0.4s ease-in forwards;
        }
        @keyframes fade-out-anim {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.8); }
        }

    </style>
</head>
<body class="bg-slate-100 text-slate-800">

    <!-- Header dengan Logo Perusahaan (Placeholder) -->
    <header class="fixed top-0 left-0 p-6 z-10">
        <div id="company-logo" class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
            P
        </div>
    </header>

    <!-- Kontainer untuk tumpukan notifikasi akan muncul di sini -->
    <div id="notification-stack"></div>

    <!-- Konten utama halaman -->
    <main class="flex items-center justify-center min-h-screen">
        <div class="text-center bg-white p-8 sm:p-12 rounded-2xl shadow-lg">
            <h1 class="text-2xl sm:text-3xl font-bold mb-2">Demo Notifikasi Website</h1>
            <p class="text-slate-600 mb-8">Notifikasi akan muncul dari logo dan bisa di-flip.</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Tombol untuk memicu notifikasi -->
                <button onclick="showNotification('Tugas Baru!', '<b>Desain Ulang Homepage</b> telah ditambahkan.', 'success')" 
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition-transform transform hover:scale-105 shadow-md">
                    Notifikasi Tugas
                </button>
                <button onclick="showNotification('Peringatan', 'Percobaan login dari lokasi tidak dikenal.', 'danger')" 
                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-lg transition-transform transform hover:scale-105 shadow-md">
                    Notifikasi Peringatan
                </button>
            </div>
        </div>
    </main>

    <script>
        const stack = document.getElementById('notification-stack');

        // Fungsi untuk menampilkan notifikasi
        function showNotification(title, message, type = 'info') {
            // Membuat elemen kontainer untuk kartu flip
            const container = document.createElement('div');
            container.className = 'flip-card-container';

            // Menentukan ikon dan warna berdasarkan tipe
            let iconFront, iconBack, frontBgColor;
            switch (type) {
                case 'success':
                    frontBgColor = 'bg-green-600';
                    iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                    iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                    break;
                case 'info':
                    frontBgColor = 'bg-blue-600';
                    iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                    iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                    break;
                case 'warning':
                    frontBgColor = 'bg-yellow-600';
                    iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
                    iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
                    break;
                case 'danger':
                    frontBgColor = 'bg-red-600';
                    iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
                    iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L3.34 16c-.77-1.333.192 3 1.732 3z" /></svg>`;
                    break;
                default: // fallback
                    frontBgColor = 'bg-slate-800';
                    iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                    iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
            }

            // Mengisi konten notifikasi
            container.innerHTML = `
                <div class="flip-card">
                    <div class="flip-card-inner">
                        <!-- SISI DEPAN KARTU -->
                        <div class="flip-card-front ${frontBgColor} text-white p-4 flex flex-col items-center justify-center text-center">
                            ${iconFront}
                            <p class="text-xs opacity-80 mt-1">Hover</p>
                        </div>
                        <!-- SISI BELAKANG KARTU -->
                        <div class="flip-card-back bg-white p-3 flex flex-col justify-center items-center text-center">
                            <div class="mb-1">${iconBack}</div>
                            <h4 class="font-bold text-slate-800 text-xs">${title}</h4>
                            <p class="text-xs text-slate-600 mb-2 leading-tight">${message}</p>
                            <button class="w-full bg-slate-200 text-slate-700 text-xs py-1 rounded-md hover:bg-slate-300 transition-colors">Tutup</button>
                        </div>
                    </div>
                </div>
            `;
            
            const closeButton = container.querySelector('button');
            closeButton.onclick = () => container.classList.add('fade-out');

            // Hapus elemen dari DOM setelah animasi fade-out selesai
            container.addEventListener('animationend', (e) => {
                if (e.animationName === 'fade-out-anim') {
                    container.remove();
                }
            });

            stack.prepend(container); // Pakai prepend agar notif baru selalu di atas

            // Atur timer untuk menghilangkan notifikasi secara otomatis
            setTimeout(() => {
                container.classList.add('fade-out');
            }, 6000);
        }

        // Fungsi untuk menampilkan notifikasi demo secara otomatis
        function showDemoNotifications() {
            // Notifikasi pertama - Success
            setTimeout(() => {
                showNotification('Activity Created!', '<b>Activity baru</b> telah berhasil dibuat.', 'success');
            }, 1000);

            // Notifikasi kedua - Info
            setTimeout(() => {
                showNotification('Activity Updated!', '<b>Activity</b> telah berhasil diperbarui.', 'info');
            }, 3000);

            // Notifikasi ketiga - Warning
            setTimeout(() => {
                showNotification('Activity Deleted!', '<b>Activity</b> telah berhasil dihapus.', 'warning');
            }, 5000);
        }

        // Jalankan demo notifikasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            showDemoNotifications();
        });
    </script>

</body>
</html>
