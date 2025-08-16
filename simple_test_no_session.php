<!DOCTYPE html>
<html>
<head>
    <title>Simple Project ID Test (No Session)</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .feedback { margin-top: 5px; padding: 8px; border-radius: 4px; }
        .invalid { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .valid { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        button:hover { background: #0056b3; }
        .log { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin-top: 20px; border-radius: 4px; }
        .log h3 { margin-top: 0; }
        .endpoint-selector { margin-bottom: 20px; }
        .endpoint-selector label { display: inline; margin-right: 15px; }
        .endpoint-selector select { padding: 5px; margin-right: 15px; }
    </style>
</head>
<body>
    <h1>🧪 Simple Project ID Validation Test (No Session)</h1>
    
    <div class="endpoint-selector">
        <label>API Endpoint:</label>
        <select id="endpoint">
            <option value="test_without_session.php">test_without_session.php (No Session)</option>
            <option value="check_project_id_uniqueness.php">check_project_id_uniqueness.php (With Session)</option>
        </select>
        <button onclick="testEndpoint()">Test Endpoint</button>
    </div>
    
    <div class="form-group">
        <label for="project_id">Project ID:</label>
        <input type="text" id="project_id" name="project_id" placeholder="Masukkan Project ID (contoh: PRJ999)">
        <div id="feedback" class="feedback"></div>
    </div>
    
    <button onclick="checkProjectId()">Check Project ID</button>
    <button onclick="clearLog()">Clear Log</button>
    
    <div class="log">
        <h3>Console Log:</h3>
        <div id="log"></div>
    </div>

    <script>
        function log(message, type = 'info') {
            const logDiv = document.getElementById('log');
            const timestamp = new Date().toLocaleTimeString();
            const color = type === 'error' ? 'red' : type === 'success' ? 'green' : 'black';
            logDiv.innerHTML += `<div style="color: ${color}; margin: 2px 0;">[${timestamp}] ${message}</div>`;
            logDiv.scrollTop = logDiv.scrollHeight;
        }

        function clearLog() {
            document.getElementById('log').innerHTML = '';
        }

        async function testEndpoint() {
            const endpoint = document.getElementById('endpoint').value;
            log(`🧪 Testing endpoint: ${endpoint}`);
            
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'project_id=TEST123'
                });
                
                log(`📡 Response status: ${response.status}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const responseText = await response.text();
                log(`📡 Raw response: ${responseText}`);
                
                try {
                    const result = JSON.parse(responseText);
                    log(`✅ Endpoint working: ${JSON.stringify(result, null, 2)}`, 'success');
                } catch (parseError) {
                    log(`❌ Endpoint not returning valid JSON: ${parseError.message}`, 'error');
                }
                
            } catch (error) {
                log(`❌ Endpoint error: ${error.message}`, 'error');
            }
        }

        async function checkProjectId() {
            const projectId = document.getElementById('project_id').value.trim();
            const endpoint = document.getElementById('endpoint').value;
            const feedback = document.getElementById('feedback');
            
            if (!projectId) {
                feedback.textContent = '❌ Project ID tidak boleh kosong';
                feedback.className = 'feedback invalid';
                return;
            }

            log(`🔍 Checking project ID: ${projectId} using ${endpoint}`);
            
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'project_id=' + encodeURIComponent(projectId)
                });
                
                log(`📡 Response status: ${response.status}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const responseText = await response.text();
                log(`📡 Raw response: ${responseText}`);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    log(`❌ JSON parse error: ${parseError.message}`, 'error');
                    log(`❌ Response text: ${responseText}`, 'error');
                    throw new Error('Invalid JSON response from server');
                }
                
                log(`✅ Parsed result: ${JSON.stringify(result, null, 2)}`);
                
                if (result.exists) {
                    feedback.textContent = result.message;
                    feedback.className = 'feedback invalid';
                    log(`❌ Project ID already exists: ${result.message}`, 'error');
                } else {
                    feedback.textContent = result.message;
                    feedback.className = 'feedback valid';
                    log(`✅ Project ID is available: ${result.message}`, 'success');
                }
                
            } catch (error) {
                log(`❌ Error: ${error.message}`, 'error');
                feedback.textContent = `Error: ${error.message}`;
                feedback.className = 'feedback invalid';
            }
        }

        // Auto-check when input changes
        document.getElementById('project_id').addEventListener('blur', function() {
            if (this.value.trim()) {
                checkProjectId();
            }
        });

        // Test with PRJ999 on page load
        window.addEventListener('load', function() {
            document.getElementById('project_id').value = 'PRJ999';
            log('🚀 Page loaded, testing with PRJ999...');
            setTimeout(() => {
                testEndpoint();
                setTimeout(() => {
                    checkProjectId();
                }, 1000);
            }, 500);
        });
    </script>
</body>
</html>

