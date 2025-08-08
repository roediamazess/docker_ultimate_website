<?php
// Demo perbandingan toggle sebelum dan sesudah perbaikan
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toggle Comparison Demo - Before vs After</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            margin: 0;
        }
        
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
            color: #ffffff;
        }
        
        .demo-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            margin: 20px auto;
            max-width: 1000px;
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        [data-theme="dark"] .demo-container {
            background: rgba(45, 45, 45, 0.95);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }
        
        .comparison-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin: 30px 0;
        }
        
        .comparison-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: all 0.5s ease;
            position: relative;
            overflow: hidden;
        }
        
        .comparison-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
            background-size: 300% 100%;
            animation: gradientShift 3s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .comparison-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .comparison-card.before {
            border-color: #ff6b6b;
        }
        
        .comparison-card.after {
            border-color: #4ecdc4;
        }
        
        .toggle-demo-area {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin: 20px 0;
            min-height: 120px;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 8px 0;
            position: relative;
            padding-left: 25px;
        }
        
        .feature-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #4ecdc4;
            font-weight: bold;
        }
        
        .feature-list.before li::before {
            color: #ff6b6b;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .status-badge.before {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
        }
        
        .status-badge.after {
            background: linear-gradient(135deg, #4ecdc4, #44a08d);
            color: white;
        }
        
        h1 {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        
        [data-theme="dark"] h1 {
            background: linear-gradient(135deg, #4dabf7, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .card-title {
            font-size: 1.5em;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .before .card-title {
            color: #ff6b6b;
        }
        
        .after .card-title {
            color: #4ecdc4;
        }
        
        .global-toggle {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.6);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(245, 87, 108, 0.4);
        }
        
        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(245, 87, 108, 0.6);
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <h1>🔄 Toggle Comparison Demo</h1>
        <p style="text-align: center; font-size: 18px; margin-bottom: 30px;">
            Perbandingan toggle dark/light mode sebelum dan sesudah perbaikan animasi
        </p>
        
        <div class="global-toggle">
            <h3>🌍 Global Theme Toggle</h3>
            <p>Ubah tema global untuk melihat perbandingan:</p>
            <button type="button" data-theme-toggle class="modern-theme-toggle">
                <div class="toggle-track">
                    <div class="toggle-thumb">
                        <div class="sun-disc"></div>
                        <div class="moon-disc"></div>
                    </div>
                </div>
            </button>
        </div>
        
        <div class="comparison-grid">
            <div class="comparison-card before">
                <div class="status-badge before">Before</div>
                <h2 class="card-title">❌ Toggle Sebelum Perbaikan</h2>
                
                <div class="toggle-demo-area">
                    <!-- Toggle dengan styling inline (sebelum perbaikan) -->
                    <button type="button" style="background: yellow; border: 2px solid blue; width: 70px; height: 35px; display: flex; align-items: center; justify-content: center; cursor: pointer; position: relative; z-index: 1000;">
                        <div style="width: 70px; height: 35px; background: linear-gradient(135deg, #87CEEB 0%, #B0E0E6 100%); border-radius: 17.5px; padding: 3px; position: relative; display: block;">
                            <div style="width: 29px; height: 29px; background: #ffffff; border-radius: 50%; position: relative; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); border-radius: 50%; position: absolute; opacity: 1; transform: scale(1); box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);"></div>
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #E5E7EB 0%, #D1D5DB 100%); border-radius: 50%; position: absolute; opacity: 0; transform: scale(0.8); box-shadow: 0 0 10px rgba(229, 231, 235, 0.5);"></div>
                            </div>
                        </div>
                    </button>
                </div>
                
                <h4>Masalah yang Ditemukan:</h4>
                <ul class="feature-list before">
                    <li>Styling inline yang konflik</li>
                    <li>Gambar bulan tertimpa</li>
                    <li>Animasi kasar dan cepat</li>
                    <li>Hover effects minimal</li>
                    <li>Visual kurang menarik</li>
                    <li>Z-index tidak tepat</li>
                </ul>
            </div>
            
            <div class="comparison-card after">
                <div class="status-badge after">After</div>
                <h2 class="card-title">✅ Toggle Sesudah Perbaikan</h2>
                
                <div class="toggle-demo-area">
                    <!-- Toggle dengan CSS enhanced (sesudah perbaikan) -->
                    <button type="button" data-theme-toggle class="modern-theme-toggle">
                        <div class="toggle-track">
                            <div class="toggle-thumb">
                                <div class="sun-disc"></div>
                                <div class="moon-disc"></div>
                            </div>
                        </div>
                    </button>
                </div>
                
                <h4>Fitur yang Diperbaiki:</h4>
                <ul class="feature-list after">
                    <li>CSS classes yang bersih</li>
                    <li>Elemen tidak tumpang tindih</li>
                    <li>Animasi smooth 0.8s cubic-bezier</li>
                    <li>Hover effects yang menarik</li>
                    <li>Radial gradients yang realistis</li>
                    <li>Z-index yang tepat</li>
                    <li>Rotasi dan scale animations</li>
                    <li>Enhanced box shadows</li>
                </ul>
            </div>
        </div>
        
        <div style="text-align: center; margin: 40px 0;">
            <h3>🎯 Instruksi Testing:</h3>
            <ol style="text-align: left; max-width: 600px; margin: 0 auto;">
                <li>Klik toggle "After" untuk melihat animasi smooth</li>
                <li>Hover pada toggle untuk melihat efek tambahan</li>
                <li>Bandingkan dengan toggle "Before" yang statis</li>
                <li>Gunakan global toggle untuk mengubah tema</li>
                <li>Perhatikan perbedaan visual dan interaksi</li>
            </ol>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="index.php" class="btn btn-primary">🏠 Kembali ke Dashboard</a>
            <a href="test_toggle_fixed.php" class="btn btn-secondary">🧪 Test Halaman Lengkap</a>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script>
        // Enhanced demo script
        const themeSpan = document.getElementById("current-theme");
        const button = document.querySelector("[data-theme-toggle]");
        
        function updateThemeDisplay() {
            const currentTheme = document.documentElement.getAttribute("data-theme") || "light";
            if (themeSpan) {
                themeSpan.textContent = currentTheme;
            }
        }
        
        // Update display on page load
        updateThemeDisplay();
        
        // Listen for theme changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                    updateThemeDisplay();
                }
            });
        });
        
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme']
        });
        
        console.log('🎉 Comparison demo loaded successfully');
        console.log('🔧 Toggle button found:', !!button);
        console.log('📊 Ready to compare before vs after improvements');
    </script>
</body>
</html>

