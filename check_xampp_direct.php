<!DOCTYPE html>
<html>
<head>
    <title>Check XAMPP Status Direct</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8fafc; }
        .container { max-width: 800px; margin: 0 auto; }
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Check XAMPP Status Direct</h1>
        
        <div class="card info">
            <h2>📋 Status Check</h2>
            <div id="status-results">Loading...</div>
        </div>
        
        <div class="card">
            <h2>🚀 Quick Fix Actions</h2>
            <button class="btn btn-success" onclick="startXampp()">▶️ Start XAMPP</button>
            <button class="btn btn-warning" onclick="restartXampp()">🔄 Restart XAMPP</button>
            <button class="btn btn-danger" onclick="stopXampp()">⏹️ Stop XAMPP</button>
            <button class="btn btn-info" onclick="checkPorts()">🔌 Check Ports</button>
        </div>
        
        <div class="card">
            <h2>💡 Manual Steps</h2>
            <ol>
                <li><strong>Buka XAMPP Control Panel</strong></li>
                <li><strong>Stop Apache dan MySQL</strong> (jika running)</li>
                <li><strong>Start Apache</strong> - tunggu sampai status "Running"</li>
                <li><strong>Start MySQL</strong> - tunggu sampai status "Running"</li>
                <li><strong>Test jobsheet.php</strong> di browser</li>
            </ol>
        </div>
        
        <div class="card">
            <h2>🔧 Alternative Solutions</h2>
            <div class="code">
                <h3>1. Check Apache Error Log:</h3>
                <p>C:\xampp\apache\logs\error.log</p>
                
                <h3>2. Check PHP Error Log:</h3>
                <p>C:\xampp\php\logs\php_error_log</p>
                
                <h3>3. Check httpd.conf:</h3>
                <p>C:\xampp\apache\conf\httpd.conf</p>
                <p>Pastikan ada: LoadModule php_module "C:/xampp/php/php8apache2_4.dll"</p>
                
                <h3>4. Check php.ini:</h3>
                <p>C:\xampp\php\php.ini</p>
                <p>Pastikan: display_errors = On</p>
            </div>
        </div>
    </div>
    
    <script>
        // Check status on page load
        window.onload = function() {
            checkXamppStatus();
        };
        
        async function checkXamppStatus() {
            const results = document.getElementById('status-results');
            results.innerHTML = '<div class="warning">🔄 Checking XAMPP status...</div>';
            
            try {
                // Test localhost connection
                const response = await fetch('http://localhost/ultimate-website/');
                if (response.ok) {
                    results.innerHTML = '<div class="success">✅ Localhost accessible - XAMPP Apache is running</div>';
                } else {
                    results.innerHTML = '<div class="error">❌ Localhost not accessible - XAMPP Apache may not be running</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Cannot connect to localhost - XAMPP Apache is not running</div>';
                results.innerHTML += '<p><strong>Solution:</strong> Start XAMPP Control Panel and start Apache service</p>';
            }
        }
        
        async function startXampp() {
            const results = document.getElementById('status-results');
            results.innerHTML = '<div class="warning">🔄 Starting XAMPP...</div>';
            
            try {
                // Try to start Apache using XAMPP commands
                const response = await fetch('start_xampp.php');
                const result = await response.json();
                
                if (result.ok) {
                    results.innerHTML = '<div class="success">✅ XAMPP started successfully!</div>';
                    results.innerHTML += '<p>Now test jobsheet.php in your browser</p>';
                } else {
                    results.innerHTML = '<div class="error">❌ Failed to start XAMPP: ' + result.error + '</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error starting XAMPP: ' + error.message + '</div>';
                results.innerHTML += '<p><strong>Manual:</strong> Open XAMPP Control Panel and start Apache manually</p>';
            }
        }
        
        async function restartXampp() {
            const results = document.getElementById('status-results');
            results.innerHTML = '<div class="warning">🔄 Restarting XAMPP...</div>';
            
            try {
                const response = await fetch('restart_xampp.php');
                const result = await response.json();
                
                if (result.ok) {
                    results.innerHTML = '<div class="success">✅ XAMPP restarted successfully!</div>';
                    results.innerHTML += '<p>Now test jobsheet.php in your browser</p>';
                } else {
                    results.innerHTML = '<div class="error">❌ Failed to restart XAMPP: ' + result.error + '</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error restarting XAMPP: ' + error.message + '</div>';
            }
        }
        
        async function stopXampp() {
            const results = document.getElementById('status-results');
            results.innerHTML = '<div class="warning">🔄 Stopping XAMPP...</div>';
            
            try {
                const response = await fetch('stop_xampp.php');
                const result = await response.json();
                
                if (result.ok) {
                    results.innerHTML = '<div class="success">✅ XAMPP stopped successfully!</div>';
                    results.innerHTML += '<p>Now start it again to test</p>';
                } else {
                    results.innerHTML = '<div class="error">❌ Failed to stop XAMPP: ' + result.error + '</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error stopping XAMPP: ' + error.message + '</div>';
            }
        }
        
        async function checkPorts() {
            const results = document.getElementById('status-results');
            results.innerHTML = '<div class="warning">🔄 Checking ports...</div>';
            
            try {
                const response = await fetch('check_ports.php');
                const result = await response.json();
                
                if (result.ok) {
                    results.innerHTML = '<div class="success">✅ Port check completed</div>';
                    results.innerHTML += '<pre>' + JSON.stringify(result.ports, null, 2) + '</pre>';
                } else {
                    results.innerHTML = '<div class="error">❌ Port check failed: ' + result.error + '</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error checking ports: ' + error.message + '</div>';
            }
        }
    </script>
</body>
</html>

