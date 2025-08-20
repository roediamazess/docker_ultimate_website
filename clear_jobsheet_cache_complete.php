<!DOCTYPE html>
<html>
<head>
    <title>Clear Jobsheet Cache Complete</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { background: #d1fae5; border: 1px solid #10b981; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .warning { background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .error { background: #fee2e2; border: 1px solid #ef4444; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .btn { background: #3b82f6; color: white; padding: 15px 30px; border: none; border-radius: 8px; cursor: pointer; margin: 10px; font-size: 16px; }
        .btn:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success { background: #10b981; }
        .btn-success:hover { background: #059669; }
        .info-box { background: #f0f9ff; border: 1px solid #0ea5e9; padding: 15px; border-radius: 8px; margin: 15px 0; }
    </style>
</head>
<body>
    <h1>🧹 Clear Jobsheet Cache Complete</h1>
    
    <div class="warning">
        <h3>⚠️ Masalah yang Ditemukan:</h3>
        <p>Data jobsheet muncul lagi setelah reload meskipun sudah di-clear dari database. Ini disebabkan oleh:</p>
        <ul>
            <li><strong>Browser Cache:</strong> Data tersimpan di memory browser</li>
            <li><strong>localStorage:</strong> Data tersimpan di browser storage</li>
            <li><strong>Session Storage:</strong> Data tersimpan di session browser</li>
            <li><strong>IndexedDB:</strong> Data tersimpan di database browser</li>
        </ul>
    </div>
    
    <div class="info-box">
        <h3>📋 Langkah yang Harus Dilakukan:</h3>
        <ol>
            <li><strong>Clear Database:</strong> Hapus semua data dari tabel jobsheet</li>
            <li><strong>Clear Browser Cache:</strong> Bersihkan cache browser</li>
            <li><strong>Clear localStorage:</strong> Hapus data dari browser storage</li>
            <li><strong>Hard Refresh:</strong> Reload dengan Ctrl+F5 atau Cmd+Shift+R</li>
        </ol>
    </div>
    
    <h2>🔧 Actions</h2>
    
    <button class="btn btn-danger" onclick="clearDatabase()">🗑️ Clear Database</button>
    <button class="btn btn-danger" onclick="clearBrowserStorage()">🧹 Clear Browser Storage</button>
    <button class="btn btn-success" onclick="hardRefresh()">🔄 Hard Refresh</button>
    
    <div id="status"></div>
    
    <h2>📊 Database Status</h2>
    <div id="db-status">Loading...</div>
    
    <h2>💾 Browser Storage Status</h2>
    <div id="storage-status">Loading...</div>
    
    <script>
        // Check database status
        async function checkDatabaseStatus() {
            try {
                const response = await fetch('check_jobsheet_db_status.php');
                const result = await response.json();
                
                if (result.ok) {
                    document.getElementById('db-status').innerHTML = `
                        <div class="success">
                            <h4>✅ Database Status:</h4>
                            <p>Total records: ${result.total}</p>
                            <p>Last updated: ${result.lastUpdated || 'N/A'}</p>
                        </div>
                    `;
                } else {
                    document.getElementById('db-status').innerHTML = `
                        <div class="error">
                            <h4>❌ Database Error:</h4>
                            <p>${result.error}</p>
                        </div>
                    `;
                }
            } catch (error) {
                document.getElementById('db-status').innerHTML = `
                    <div class="error">
                        <h4>❌ Connection Error:</h4>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }
        
        // Check browser storage status
        function checkBrowserStorage() {
            let storageInfo = '<div class="info-box"><h4>💾 Browser Storage Status:</h4><ul>';
            
            // Check localStorage
            const localStorageKeys = Object.keys(localStorage);
            const jobsheetLocalKeys = localStorageKeys.filter(key => key.includes('jobsheet') || key.includes('job'));
            storageInfo += `<li><strong>localStorage:</strong> ${localStorageKeys.length} total keys, ${jobsheetLocalKeys.length} jobsheet-related</li>`;
            
            // Check sessionStorage
            const sessionStorageKeys = Object.keys(sessionStorage);
            const jobsheetSessionKeys = sessionStorageKeys.filter(key => key.includes('jobsheet') || key.includes('job'));
            storageInfo += `<li><strong>sessionStorage:</strong> ${sessionStorageKeys.length} total keys, ${jobsheetSessionKeys.length} jobsheet-related</li>`;
            
            // Check cookies
            const cookies = document.cookie.split(';').filter(cookie => cookie.includes('jobsheet') || cookie.includes('job'));
            storageInfo += `<li><strong>Cookies:</strong> ${cookies.length} jobsheet-related cookies</li>`;
            
            storageInfo += '</ul></div>';
            
            if (jobsheetLocalKeys.length > 0 || jobsheetSessionKeys.length > 0 || cookies.length > 0) {
                storageInfo += '<div class="warning"><p><strong>⚠️ Found jobsheet data in browser storage!</strong></p></div>';
            } else {
                storageInfo += '<div class="success"><p><strong>✅ No jobsheet data found in browser storage</strong></p></div>';
            }
            
            document.getElementById('storage-status').innerHTML = storageInfo;
        }
        
        // Clear database
        async function clearDatabase() {
            if (!confirm('⚠️ Yakin ingin menghapus SEMUA data jobsheet dari database? Tindakan ini tidak dapat dibatalkan!')) {
                return;
            }
            
            try {
                document.getElementById('status').innerHTML = '<div class="warning">🔄 Clearing database...</div>';
                
                const response = await fetch('clear_jobsheet_database.php');
                const result = await response.json();
                
                if (result.ok) {
                    document.getElementById('status').innerHTML = `
                        <div class="success">
                            <h4>✅ Database Cleared Successfully!</h4>
                            <p>${result.message}</p>
                        </div>
                    `;
                    checkDatabaseStatus();
                } else {
                    document.getElementById('status').innerHTML = `
                        <div class="error">
                            <h4>❌ Database Clear Failed:</h4>
                            <p>${result.error}</p>
                        </div>
                    `;
                }
            } catch (error) {
                document.getElementById('status').innerHTML = `
                    <div class="error">
                        <h4>❌ Error:</h4>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }
        
        // Clear browser storage
        function clearBrowserStorage() {
            if (!confirm('⚠️ Yakin ingin menghapus SEMUA data jobsheet dari browser storage? Tindakan ini tidak dapat dibatalkan!')) {
                return;
            }
            
            try {
                document.getElementById('status').innerHTML = '<div class="warning">🔄 Clearing browser storage...</div>';
                
                // Clear localStorage
                const localStorageKeys = Object.keys(localStorage);
                const jobsheetLocalKeys = localStorageKeys.filter(key => key.includes('jobsheet') || key.includes('job'));
                jobsheetLocalKeys.forEach(key => localStorage.removeItem(key));
                
                // Clear sessionStorage
                const sessionStorageKeys = Object.keys(sessionStorage);
                const jobsheetSessionKeys = sessionStorageKeys.filter(key => key.includes('jobsheet') || key.includes('job'));
                jobsheetSessionKeys.forEach(key => sessionStorage.removeItem(key));
                
                // Clear cookies
                const cookies = document.cookie.split(';');
                cookies.forEach(cookie => {
                    const [name] = cookie.split('=');
                    if (name.trim().includes('jobsheet') || name.trim().includes('job')) {
                        document.cookie = `${name.trim()}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
                    }
                });
                
                document.getElementById('status').innerHTML = `
                    <div class="success">
                        <h4>✅ Browser Storage Cleared Successfully!</h4>
                        <p>Cleared ${jobsheetLocalKeys.length} localStorage keys, ${jobsheetSessionKeys.length} sessionStorage keys, and ${cookies.filter(c => c.includes('jobsheet') || c.includes('job')).length} cookies</p>
                    </div>
                `;
                
                checkBrowserStorage();
                
            } catch (error) {
                document.getElementById('status').innerHTML = `
                    <div class="error">
                        <h4>❌ Error:</h4>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }
        
        // Hard refresh
        function hardRefresh() {
            if (confirm('🔄 Yakin ingin melakukan hard refresh? Halaman akan reload dan semua cache akan dibersihkan.')) {
                // Clear cache headers
                window.location.reload(true);
            }
        }
        
        // Load status on page load
        window.onload = function() {
            checkDatabaseStatus();
            checkBrowserStorage();
        };
    </script>
</body>
</html>


