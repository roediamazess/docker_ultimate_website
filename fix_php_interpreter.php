<!DOCTYPE html>
<html>
<head>
    <title>Fix PHP Interpreter Issue</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8fafc; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; border-radius: 12px; padding: 20px; margin: 20px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .success { border-left: 4px solid #10b981; }
        .warning { border-left: 4px solid #f59e0b; }
        .error { border-left: 4px solid #ef4444; }
        .info { border-left: 4px solid #3b82f6; }
        .btn { background: #3b82f6; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; margin: 5px; font-size: 14px; }
        .btn:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success { background: #10b981; }
        .btn-success:hover { background: #059669; }
        .code { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 8px; font-family: monospace; overflow-x: auto; }
        .step { background: #f0f9ff; border: 1px solid #0ea5e9; padding: 15px; border-radius: 8px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix PHP Interpreter Issue</h1>
        
        <div class="card error">
            <h2>❌ Masalah yang Ditemukan:</h2>
            <p>File <code>jobsheet.php</code> ditampilkan sebagai kode mentah, bukan hasil eksekusi PHP. Ini menunjukkan:</p>
            <ul>
                <li><strong>PHP Interpreter tidak berjalan</strong></li>
                <li><strong>Server tidak dikonfigurasi untuk memproses file .php</strong></li>
                <li><strong>XAMPP mungkin tidak running</strong></li>
            </ul>
        </div>
        
        <div class="card info">
            <h2>📋 Langkah Perbaikan:</h2>
            <div class="step">
                <h3>1. Start XAMPP Services</h3>
                <p>Pastikan Apache dan MySQL berjalan di XAMPP Control Panel</p>
                <button class="btn btn-success" onclick="checkXamppStatus()">🔍 Check XAMPP Status</button>
            </div>
            
            <div class="step">
                <h3>2. Test PHP Basic</h3>
                <p>Test apakah PHP bisa berjalan dengan file sederhana</p>
                <button class="btn btn-info" onclick="testBasicPHP()">🧪 Test Basic PHP</button>
            </div>
            
            <div class="step">
                <h3>3. Check File Permissions</h3>
                <p>Pastikan file memiliki permission yang benar</p>
                <button class="btn btn-info" onclick="checkFilePermissions()">📁 Check Permissions</button>
            </div>
            
            <div class="step">
                <h3>4. Restart Apache</h3>
                <p>Restart Apache service untuk memastikan konfigurasi terbaru</p>
                <button class="btn btn-warning" onclick="restartApache()">🔄 Restart Apache</button>
            </div>
        </div>
        
        <div class="card">
            <h2>🔍 Diagnosis Results</h2>
            <div id="diagnosis-results">Klik tombol di atas untuk memulai diagnosis...</div>
        </div>
        
        <div class="card">
            <h2>💡 Manual Solutions</h2>
            <div class="step">
                <h3>Manual XAMPP Start:</h3>
                <ol>
                    <li>Buka <strong>XAMPP Control Panel</strong></li>
                    <li>Klik <strong>Start</strong> pada Apache</li>
                    <li>Klik <strong>Start</strong> pada MySQL</li>
                    <li>Pastikan status berubah menjadi <strong>Running</strong></li>
                </ol>
            </div>
            
            <div class="step">
                <h3>Check Apache Configuration:</h3>
                <p>File: <code>C:\xampp\apache\conf\httpd.conf</code></p>
                <p>Pastikan ada baris: <code>LoadModule php_module "C:/xampp/php/php8apache2_4.dll"</code></p>
            </div>
            
            <div class="step">
                <h3>Check PHP Version:</h3>
                <p>Buka: <code>http://localhost/ultimate-website/phpinfo.php</code></p>
            </div>
        </div>
        
        <div class="card">
            <h2>🚀 Quick Actions</h2>
            <button class="btn btn-success" onclick="createPhpInfo()">📄 Create phpinfo.php</button>
            <button class="btn btn-success" onclick="createTestPHP()">🧪 Create test.php</button>
            <button class="btn btn-danger" onclick="clearAllCache()">🧹 Clear All Cache</button>
        </div>
    </div>
    
    <script>
        async function checkXamppStatus() {
            const results = document.getElementById('diagnosis-results');
            results.innerHTML = '<div class="warning">🔄 Checking XAMPP status...</div>';
            
            try {
                // Test if we can reach localhost
                const response = await fetch('http://localhost/ultimate-website/');
                if (response.ok) {
                    results.innerHTML = '<div class="success">✅ Localhost accessible - XAMPP Apache is running</div>';
                } else {
                    results.innerHTML = '<div class="error">❌ Localhost not accessible - XAMPP Apache may not be running</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Cannot connect to localhost - XAMPP Apache is not running</div>';
            }
        }
        
        async function testBasicPHP() {
            const results = document.getElementById('diagnosis-results');
            results.innerHTML = '<div class="warning">🔄 Testing basic PHP...</div>';
            
            try {
                const response = await fetch('test_basic_php.php');
                const text = await response.text();
                
                if (text.includes('PHP Version')) {
                    results.innerHTML = '<div class="success">✅ PHP is working correctly!</div>';
                } else {
                    results.innerHTML = '<div class="error">❌ PHP is not processing - showing raw code</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error testing PHP: ' + error.message + '</div>';
            }
        }
        
        async function checkFilePermissions() {
            const results = document.getElementById('diagnosis-results');
            results.innerHTML = '<div class="warning">🔄 Checking file permissions...</div>';
            
            try {
                const response = await fetch('check_file_permissions.php');
                const result = await response.json();
                
                if (result.ok) {
                    results.innerHTML = '<div class="success">✅ File permissions are correct</div>';
                } else {
                    results.innerHTML = '<div class="error">❌ File permission issue: ' + result.error + '</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error checking permissions: ' + error.message + '</div>';
            }
        }
        
        async function restartApache() {
            const results = document.getElementById('diagnosis-results');
            results.innerHTML = '<div class="warning">🔄 Restarting Apache...</div>';
            
            try {
                const response = await fetch('restart_apache.php');
                const result = await response.json();
                
                if (result.ok) {
                    results.innerHTML = '<div class="success">✅ Apache restart initiated</div>';
                } else {
                    results.innerHTML = '<div class="error">❌ Apache restart failed: ' + result.error + '</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error restarting Apache: ' + error.message + '</div>';
            }
        }
        
        async function createPhpInfo() {
            const results = document.getElementById('diagnosis-results');
            results.innerHTML = '<div class="warning">🔄 Creating phpinfo.php...</div>';
            
            try {
                const response = await fetch('create_phpinfo.php');
                const result = await response.json();
                
                if (result.ok) {
                    results.innerHTML = '<div class="success">✅ phpinfo.php created successfully!</div>';
                    results.innerHTML += '<p><a href="phpinfo.php" target="_blank">Open phpinfo.php</a></p>';
                } else {
                    results.innerHTML = '<div class="error">❌ Failed to create phpinfo.php: ' + result.error + '</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error creating phpinfo.php: ' + error.message + '</div>';
            }
        }
        
        async function createTestPHP() {
            const results = document.getElementById('diagnosis-results');
            results.innerHTML = '<div class="warning">🔄 Creating test.php...</div>';
            
            try {
                const response = await fetch('create_test_php.php');
                const result = await response.json();
                
                if (result.ok) {
                    results.innerHTML = '<div class="success">✅ test.php created successfully!</div>';
                    results.innerHTML += '<p><a href="test.php" target="_blank">Open test.php</a></p>';
                } else {
                    results.innerHTML = '<div class="error">❌ Failed to create test.php: ' + result.error + '</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error creating test.php: ' + error.message + '</div>';
            }
        }
        
        async function clearAllCache() {
            const results = document.getElementById('diagnosis-results');
            results.innerHTML = '<div class="warning">🔄 Clearing all cache...</div>';
            
            try {
                // Clear localStorage
                localStorage.clear();
                sessionStorage.clear();
                
                // Clear cookies
                document.cookie.split(";").forEach(function(c) { 
                    document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
                });
                
                results.innerHTML = '<div class="success">✅ All browser cache cleared!</div>';
                results.innerHTML += '<p>Now try refreshing the page with Ctrl+F5</p>';
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error clearing cache: ' + error.message + '</div>';
            }
        }
    </script>
</body>
</html>

