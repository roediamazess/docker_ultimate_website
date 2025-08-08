<?php
// Test file untuk memverifikasi toggle dark/light mode yang sudah diperbaiki dengan animasi smooth
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Toggle Dark/Light Mode - Enhanced Animation</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            margin: 0;
        }
        
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: #ffffff;
        }
        
        .test-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 20px auto;
            max-width: 800px;
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        [data-theme="dark"] .test-container {
            background: rgba(45, 45, 45, 0.95);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        
        .toggle-test {
            border: 2px solid #007bff;
            padding: 25px;
            margin: 20px 0;
            border-radius: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        [data-theme="dark"] .toggle-test {
            background: linear-gradient(135deg, #3d3d3d 0%, #2d2d2d 100%);
            border-color: #6c757d;
        }
        
        .toggle-test::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.8s ease;
        }
        
        .toggle-test:hover::before {
            left: 100%;
        }
        
        .status {
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            font-weight: bold;
            transition: all 0.3s ease;
            border: none;
        }
        
        .status.success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            box-shadow: 0 4px 15px rgba(21, 87, 36, 0.2);
        }
        
        [data-theme="dark"] .status.success {
            background: linear-gradient(135deg, #1e4a2e 0%, #2d5a3d 100%);
            color: #d4edda;
            box-shadow: 0 4px 15px rgba(212, 237, 218, 0.2);
        }
        
        .status.info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            box-shadow: 0 4px 15px rgba(12, 84, 96, 0.2);
        }
        
        [data-theme="dark"] .status.info {
            background: linear-gradient(135deg, #1e3a3f 0%, #2d4a4f 100%);
            color: #d1ecf1;
            box-shadow: 0 4px 15px rgba(209, 236, 241, 0.2);
        }
        
        .toggle-demo {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            margin: 20px 0;
            backdrop-filter: blur(5px);
        }
        
        .animation-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .feature-card h4 {
            color: #007bff;
            margin-bottom: 10px;
        }
        
        [data-theme="dark"] .feature-card h4 {
            color: #4dabf7;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }
        
        h1 {
            background: linear-gradient(135deg, #007bff, #6610f2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
            margin-bottom: 30px;
        }
        
        [data-theme="dark"] h1 {
            background: linear-gradient(135deg, #4dabf7, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🌟 Toggle Dark/Light Mode - Enhanced Animation</h1>
        <p style="text-align: center; font-size: 18px; margin-bottom: 30px;">
            Halaman ini untuk menguji toggle dark/light mode dengan animasi yang lebih smooth dan elegan
        </p>
        
        <div class="status success">
            ✨ Toggle button sudah diperbarui dengan animasi yang lebih smooth dan visual yang lebih menarik
        </div>
        
        <div class="toggle-test">
            <h3>🎯 Toggle Button Demo</h3>
            <p>Klik tombol di bawah untuk menguji animasi toggle yang enhanced:</p>
            
            <div class="toggle-demo">
                <button type="button" data-theme-toggle class="modern-theme-toggle">
                    <div class="toggle-track">
                        <div class="toggle-thumb">
                            <div class="sun-disc"></div>
                            <div class="moon-disc"></div>
                        </div>
                    </div>
                </button>
            </div>
            
            <div class="status info">
                <strong>🎨 Status Tema:</strong> <span id="current-theme">light</span>
            </div>
        </div>
        
        <div class="toggle-test">
            <h3>🚀 Fitur Animasi yang Diperbarui:</h3>
            <div class="animation-info">
                <div class="feature-card">
                    <h4>🌅 Sun Disc Animation</h4>
                    <ul>
                        <li>Radial gradient yang lebih realistis</li>
                        <li>Rotasi smooth saat transisi</li>
                        <li>Box shadow dengan inset effects</li>
                        <li>Scale animation yang halus</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <h4>🌙 Moon Disc Animation</h4>
                    <ul>
                        <li>Gradient yang menyerupai bulan asli</li>
                        <li>Rotasi terbalik saat muncul</li>
                        <li>Glow effect yang elegan</li>
                        <li>Opacity transition yang smooth</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <h4>🎨 Track Background</h4>
                    <ul>
                        <li>Gradient 3 warna untuk light mode</li>
                        <li>Gradient biru gelap untuk dark mode</li>
                        <li>Box shadow yang lebih dalam</li>
                        <li>Inset shadow untuk depth</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <h4>✨ Hover Effects</h4>
                    <ul>
                        <li>Lift effect saat hover</li>
                        <li>Scale animation pada icons</li>
                        <li>Enhanced glow effects</li>
                        <li>Rotasi subtle pada hover</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="toggle-test">
            <h3>📋 Instruksi Testing:</h3>
            <ol>
                <li>🎯 Klik tombol toggle di atas untuk melihat animasi</li>
                <li>👀 Perhatikan transisi smooth dari light ke dark mode</li>
                <li>🌟 Amati animasi sun-disc dan moon-disc yang enhanced</li>
                <li>🔄 Klik lagi untuk kembali ke light mode</li>
                <li>💾 Refresh halaman untuk memastikan tema tersimpan</li>
                <li>🖱️ Hover pada toggle untuk melihat efek tambahan</li>
            </ol>
        </div>
        
        <div class="toggle-test">
            <h3>🔗 Link ke Halaman Utama:</h3>
            <div style="text-align: center;">
                <a href="index.php" class="btn btn-primary">🏠 Kembali ke Dashboard</a>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script>
        // Enhanced test script with smooth animations
        const themeSpan = document.getElementById("current-theme");
        const button = document.querySelector("[data-theme-toggle]");
        
        function updateThemeDisplay() {
            const currentTheme = document.documentElement.getAttribute("data-theme") || "light";
            if (themeSpan) {
                themeSpan.textContent = currentTheme;
                // Add smooth transition effect to status text
                themeSpan.style.transition = "all 0.3s ease";
                themeSpan.style.transform = "scale(1.1)";
                setTimeout(() => {
                    themeSpan.style.transform = "scale(1)";
                }, 300);
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
        
        // Add click animation
        if (button) {
            button.addEventListener('click', function() {
                this.style.transform = "scale(0.95)";
                setTimeout(() => {
                    this.style.transform = "scale(1)";
                }, 150);
            });
        }
        
        console.log('🎉 Enhanced test script loaded successfully');
        console.log('🔧 Toggle button found:', !!button);
        
        // Add some fun console messages
        console.log('🌟 Animasi smooth toggle dark/light mode siap digunakan!');
        console.log('🎨 Fitur: Radial gradients, rotations, smooth transitions');
    </script>
</body>
</html>
