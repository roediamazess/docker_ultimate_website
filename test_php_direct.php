<!DOCTYPE html>
<html>
<head>
    <title>Test PHP Direct</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8fafc; }
        .container { max-width: 800px; margin: 0 auto; }
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test PHP Direct</h1>
        
        <div class="card info">
            <h2>📋 PHP Test Results</h2>
            <div id="php-test-results">Loading...</div>
        </div>
        
        <div class="card">
            <h2>🚀 Quick Actions</h2>
            <button class="btn btn-success" onclick="testJobsheet()">🔍 Test Jobsheet.php</button>
            <button class="btn btn-success" onclick="testDatabase()">🗄️ Test Database</button>
            <button class="btn btn-success" onclick="testBasicPHP()">🧪 Test Basic PHP</button>
        </div>
        
        <div class="card">
            <h2>💡 What to Look For</h2>
            <ul>
                <li><strong>✅ PHP Working:</strong> You should see PHP output, not raw code</li>
                <li><strong>❌ PHP Not Working:</strong> You'll see raw PHP code like <code>&lt;?php echo "test"; ?&gt;</code></li>
                <li><strong>🔧 Solution:</strong> Start XAMPP Apache service</li>
            </ul>
        </div>
        
        <div class="card">
            <h2>🔧 Manual XAMPP Start</h2>
            <ol>
                <li>Press <strong>Windows + R</strong>, type <code>xampp</code>, press Enter</li>
                <li>In XAMPP Control Panel, click <strong>Start</strong> next to Apache</li>
                <li>Wait for status to change to <strong>Running</strong></li>
                <li>Click <strong>Start</strong> next to MySQL</li>
                <li>Test jobsheet.php again</li>
            </ol>
        </div>
    </div>
    
    <script>
        // Test PHP on page load
        window.onload = function() {
            testBasicPHP();
        };
        
        async function testBasicPHP() {
            const results = document.getElementById('php-test-results');
            results.innerHTML = '<div class="warning">🔄 Testing basic PHP...</div>';
            
            try {
                const response = await fetch('test_basic_php.php');
                const text = await response.text();
                
                if (text.includes('PHP Version') && !text.includes('<?php')) {
                    results.innerHTML = '<div class="success">✅ PHP is working correctly!</div>';
                    results.innerHTML += '<div class="code">' + text + '</div>';
                } else if (text.includes('<?php')) {
                    results.innerHTML = '<div class="error">❌ PHP is NOT working - showing raw code!</div>';
                    results.innerHTML += '<div class="code">' + text + '</div>';
                    results.innerHTML += '<p><strong>Solution:</strong> Start XAMPP Apache service</p>';
                } else {
                    results.innerHTML = '<div class="warning">⚠️ Unexpected response</div>';
                    results.innerHTML += '<div class="code">' + text + '</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error testing PHP: ' + error.message + '</div>';
                results.innerHTML += '<p><strong>Solution:</strong> Start XAMPP Apache service</p>';
            }
        }
        
        async function testJobsheet() {
            const results = document.getElementById('php-test-results');
            results.innerHTML = '<div class="warning">🔄 Testing jobsheet.php...</div>';
            
            try {
                const response = await fetch('jobsheet.php');
                const text = await response.text();
                
                if (text.includes('<?php') && text.includes('session_start()')) {
                    results.innerHTML = '<div class="error">❌ jobsheet.php is showing raw PHP code!</div>';
                    results.innerHTML += '<p><strong>This confirms PHP is not working!</strong></p>';
                    results.innerHTML += '<p><strong>Solution:</strong> Start XAMPP Apache service</p>';
                } else if (text.includes('Jobsheet') && !text.includes('<?php')) {
                    results.innerHTML = '<div class="success">✅ jobsheet.php is working correctly!</div>';
                    results.innerHTML += '<p>PHP is processing the file properly</p>';
                } else {
                    results.innerHTML = '<div class="warning">⚠️ Unexpected response from jobsheet.php</div>';
                    results.innerHTML += '<div class="code">' + text.substring(0, 500) + '...</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error testing jobsheet.php: ' + error.message + '</div>';
            }
        }
        
        async function testDatabase() {
            const results = document.getElementById('php-test-results');
            results.innerHTML = '<div class="warning">🔄 Testing database connection...</div>';
            
            try {
                const response = await fetch('test_database_connection.php');
                const text = await response.text();
                
                if (text.includes('Database connected successfully')) {
                    results.innerHTML = '<div class="success">✅ Database connection successful!</div>';
                    results.innerHTML += '<div class="code">' + text + '</div>';
                } else if (text.includes('<?php')) {
                    results.innerHTML = '<div class="error">❌ Database test showing raw PHP code!</div>';
                    results.innerHTML += '<p><strong>PHP is not working!</strong></p>';
                } else {
                    results.innerHTML = '<div class="warning">⚠️ Database test response</div>';
                    results.innerHTML += '<div class="code">' + text + '</div>';
                }
            } catch (error) {
                results.innerHTML = '<div class="error">❌ Error testing database: ' + error.message + '</div>';
            }
        }
    </script>
</body>
</html>

