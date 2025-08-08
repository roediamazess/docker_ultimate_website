<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Waktu Lokal PC</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .time-display {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
        }
        .greeting {
            font-size: 18px;
            color: #666;
            margin: 10px 0;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .test-button {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        .test-button:hover {
            background: #5a6fd8;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🌍 Test Waktu Lokal PC</h1>
        <p>Website ini akan menyesuaikan dengan waktu lokal PC pengguna</p>
        
        <div class="info">
            <strong>Informasi:</strong>
            <ul>
                <li>Waktu akan diambil dari waktu lokal PC pengguna</li>
                <li>Background dan greeting akan berubah otomatis</li>
                <li>Tidak bergantung pada timezone server</li>
            </ul>
        </div>
        
        <div class="time-display" id="currentTime">Loading...</div>
        <div class="greeting" id="greeting">Loading...</div>
        <div class="greeting" id="backgroundClass">Loading...</div>
        
        <div style="margin: 20px 0;">
            <button class="test-button" onclick="updateTime()">🔄 Update Waktu</button>
            <button class="test-button" onclick="testAllTimes()">🧪 Test Semua Waktu</button>
            <a href="login_simple.php" class="test-button" style="text-decoration: none;">🏠 Kembali ke Login</a>
        </div>
        
        <div id="testResults"></div>
    </div>

    <script>
        function updateTime() {
            const now = new Date();
            const hour = now.getHours();
            const minute = now.getMinutes();
            const second = now.getSeconds();
            
            const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}:${second.toString().padStart(2, '0')}`;
            const dateString = now.toLocaleDateString('id-ID', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            document.getElementById('currentTime').textContent = `${dateString} - ${timeString}`;
            
            let timeOfDay = '';
            let bgClass = '';
            
            if (hour >= 3 && hour < 10) {
                timeOfDay = 'Pagi Gaes!';
                bgClass = 'morning';
            } else if (hour >= 10 && hour < 15) {
                timeOfDay = 'Siang Gaes!';
                bgClass = 'afternoon';
            } else if (hour >= 15 && hour < 18) {
                timeOfDay = 'Sore Gaes!';
                bgClass = 'evening';
            } else {
                timeOfDay = 'Malam Gaes!';
                bgClass = 'night';
            }
            
            document.getElementById('greeting').textContent = `Selamat ${timeOfDay}`;
            document.getElementById('backgroundClass').textContent = `Background: ${bgClass}`;
        }
        
        function testAllTimes() {
            const results = document.getElementById('testResults');
            let html = '<h3>🧪 Test Semua Range Waktu:</h3>';
            
            for (let hour = 0; hour < 24; hour++) {
                let timeOfDay = '';
                let bgClass = '';
                let color = '';
                
                if (hour >= 3 && hour < 10) {
                    timeOfDay = 'Pagi Gaes!';
                    bgClass = 'morning';
                    color = 'orange';
                } else if (hour >= 10 && hour < 15) {
                    timeOfDay = 'Siang Gaes!';
                    bgClass = 'afternoon';
                    color = 'blue';
                } else if (hour >= 15 && hour < 18) {
                    timeOfDay = 'Sore Gaes!';
                    bgClass = 'evening';
                    color = 'red';
                } else {
                    timeOfDay = 'Malam Gaes!';
                    bgClass = 'night';
                    color = 'purple';
                }
                
                const current = (hour === new Date().getHours()) ? ' ← SEKARANG' : '';
                html += `<p style="color: ${color}; margin: 5px 0;"><strong>${hour.toString().padStart(2, '0')}:00</strong> - ${timeOfDay} (${bgClass})${current}</p>`;
            }
            
            results.innerHTML = html;
        }
        
        // Update waktu setiap detik
        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>
</html> 
