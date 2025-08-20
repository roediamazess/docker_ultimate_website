<!DOCTYPE html>
<html>
<head>
    <title>Debug Jobsheet Specific Issue</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8fafc; }
        .container { max-width: 1000px; margin: 0 auto; }
        .card { background: white; border-radius: 12px; padding: 20px; margin: 20px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .success { border-left: 4px solid #10b981; }
        .warning { border-left: 4px solid #f59e0b; }
        .error { border-left: 4px solid #ef4444; }
        .info { border-left: 4px solid #3b82f6; }
        .code { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 8px; font-family: monospace; overflow-x: auto; }
        .btn { background: #3b82f6; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; margin: 5px; font-size: 14px; }
        .btn:hover { background: #2563eb; }
        .btn-success { background: #10b981; }
        .btn-success:hover { background: #059669; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug Jobsheet Specific Issue</h1>
        
        <div class="card error">
            <h2>❌ Masalah yang Ditemukan:</h2>
            <p>PHP sudah working (berdasarkan test sebelumnya), tapi <code>jobsheet.php</code> masih ditampilkan sebagai kode mentah. Ini menunjukkan masalah spesifik dengan file tersebut.</p>
        </div>
        
        <div class="card info">
            <h2>📋 Diagnosis Steps</h2>
            <div id="diagnosis-results">Loading...</div>
        </div>
        
        <div class="card">
            <h2>🚀 Quick Fix Actions</h2>
            <button class="btn btn-success" onclick="checkJobsheetFile()">🔍 Check Jobsheet File</button>
            <button class="btn btn-success" onclick="testJobsheetDirect()">🧪 Test Jobsheet Direct</button>
            <button class="btn btn-danger" onclick="fixJobsheetFile()">🔧 Fix Jobsheet File</button>
            <button class="btn btn-success" onclick="createSimpleJobsheet()">📄 Create Simple Jobsheet</button>
        </div>
        
        <div class="card">
            <h2>💡 Possible Causes</h2>
            <ul>
                <li><strong>File Corruption:</strong> File jobsheet.php mungkin rusak</li>
                <li><strong>PHP Syntax Error:</strong> Ada error syntax yang mencegah eksekusi</li>
                <li><strong>Include Error:</strong> File yang di-include bermasalah</li>
                <li><strong>Session Error:</strong> Masalah dengan session_start()</li>
                <li><strong>Database Error:</strong> Error saat koneksi database</li>
            </ul>
        </div>
        
        <div class="card">
            <h2>🔧 Manual Solutions</h2>
            <ol>
                <li><strong>Check File Content:</strong> Buka jobsheet.php di text editor</li>
                <li><strong>Check Error Log:</strong> Lihat Apache error log</li>
                <li><strong>Test Include Files:</strong> Test db.php dan access_control.php</li>
                <li><strong>Simplify File:</strong> Buat versi sederhana untuk test</li>
            </ol>
        </div>
    </div>
    
    <script>
        // Check on page load
        window.onload = function() {
            checkJobsheetFile();
        };
        
        async function checkJobsheetFile() {
            const results = document.getElementById('diagnosis-results');
            results.innerHTML = '<div class="warning">🔄 Checking jobsheet.php file...</div>';
            
            try {
                const response = await fetch('check_jobsheet_file.php');
                const result = await response.json();
                
                if (result.ok) {
                    results.innerHTML = '<div class="success">✅ File check completed</div>';
                    results.innerHTML += '<div class="code">' + JSON.stringify(result, null, 2) + '</div>';
                } else {
                    results.innerHTML = '<div class="error">❌ File check failed: ' + result.error + '</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error checking file: ' + error.message + '</div>';
            }
        }
        
        async function testJobsheetDirect() {
            const results = document.getElementById('diagnosis-results');
            results.innerHTML = '<div class="warning">🔄 Testing jobsheet.php directly...</div>';
            
            try {
                const response = await fetch('jobsheet.php');
                const text = await response.text();
                
                if (text.includes('<?php') && text.includes('session_start()')) {
                    results.innerHTML = '<div class="error">❌ jobsheet.php still showing raw PHP code!</div>';
                    results.innerHTML += '<p><strong>This confirms the file has a specific issue!</strong></p>';
                    results.innerHTML += '<div class="code">' + text.substring(0, 500) + '...</div>';
                } else if (text.includes('Jobsheet') && !text.includes('<?php')) {
                    results.innerHTML = '<div class="success">✅ jobsheet.php is now working!</div>';
                } else {
                    results.innerHTML = '<div class="warning">⚠️ Unexpected response</div>';
                    results.innerHTML += '<div class="code">' + text.substring(0, 500) + '...</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error testing jobsheet.php: ' + error.message + '</div>';
            }
        }
        
        async function fixJobsheetFile() {
            const results = document.getElementById('diagnosis-results');
            results.innerHTML = '<div class="warning">🔄 Attempting to fix jobsheet.php...</div>';
            
            try {
                const response = await fetch('fix_jobsheet_file.php');
                const result = await response.json();
                
                if (result.ok) {
                    results.innerHTML = '<div class="success">✅ File fix attempted!</div>';
                    results.innerHTML += '<div class="code">' + JSON.stringify(result, null, 2) + '</div>';
                    results.innerHTML += '<p><strong>Now test jobsheet.php again!</strong></p>';
                } else {
                    results.innerHTML = '<div class="error">❌ File fix failed: ' + result.error + '</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error fixing file: ' + error.message + '</div>';
            }
        }
        
        async function createSimpleJobsheet() {
            const results = document.getElementById('diagnosis-results');
            results.innerHTML = '<div class="warning">🔄 Creating simple jobsheet.php...</div>';
            
            try {
                const response = await fetch('create_simple_jobsheet.php');
                const result = await response.json();
                
                if (result.ok) {
                    results.innerHTML = '<div class="success">✅ Simple jobsheet.php created!</div>';
                    results.innerHTML += '<p><strong>Now test: <a href="jobsheet_simple.php" target="_blank">jobsheet_simple.php</a></strong></p>';
                } else {
                    results.innerHTML = '<div class="error">❌ Failed to create simple jobsheet: ' + result.error + '</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error creating simple jobsheet: ' + error.message + '</div>';
            }
        }
    </script>
</body>
</html>

