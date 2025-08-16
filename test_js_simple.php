<!DOCTYPE html>
<html>
<head>
    <title>🧪 Test JavaScript Validation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .input-group { margin: 10px 0; }
        input { padding: 8px; border: 1px solid #ccc; border-radius: 3px; width: 200px; }
        .validation-feedback { margin-top: 5px; font-size: 14px; }
        .is-invalid { border-color: #dc3545 !important; }
        .is-valid { border-color: #198754 !important; }
        .invalid-feedback { color: #dc3545; }
        .valid-feedback { color: #198754; }
        .test-btn { background: #dc3545; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; margin-left: 10px; }
    </style>
</head>
<body>
    <h1>🧪 Test JavaScript Validation - Simple Version</h1>
    
    <div class="test-section">
        <h2>Test 1: Basic JavaScript Functionality</h2>
        <button onclick="testBasicJS()" class="test-btn">Test Basic JS</button>
        <div id="basic-result"></div>
    </div>
    
    <div class="test-section">
        <h2>Test 2: Project ID Validation</h2>
        <div class="input-group">
            <label>Project ID:</label><br>
            <input type="text" id="testProjectId" placeholder="Enter Project ID (e.g., PRJ999)">
            <button onclick="testValidation()" class="test-btn">Test Validation</button>
        </div>
        <div id="validation-result"></div>
    </div>
    
    <div class="test-section">
        <h2>Test 3: API Call Test</h2>
        <button onclick="testAPICall()" class="test-btn">Test API Call</button>
        <div id="api-result"></div>
    </div>
    
    <div class="test-section">
        <h2>Test 4: Real-time Validation</h2>
        <div class="input-group">
            <label>Real-time Project ID:</label><br>
            <input type="text" id="realtimeProjectId" placeholder="Type PRJ999 to test">
            <div class="validation-feedback" id="realtimeFeedback"></div>
        </div>
    </div>
    
    <script>
        // Test 1: Basic JavaScript
        function testBasicJS() {
            const result = document.getElementById('basic-result');
            result.innerHTML = '✅ JavaScript is working! Current time: ' + new Date().toLocaleTimeString();
            console.log('🧪 Basic JS test passed');
        }
        
        // Test 2: Validation function
        function testValidation() {
            const projectId = document.getElementById('testProjectId').value.trim();
            const result = document.getElementById('validation-result');
            
            if (!projectId) {
                result.innerHTML = '❌ Please enter a Project ID';
                return;
            }
            
            result.innerHTML = '🔄 Testing validation for: ' + projectId;
            console.log('🧪 Testing validation for:', projectId);
            
            // Simulate validation
            if (projectId === 'PRJ999') {
                result.innerHTML = '❌ Project ID "PRJ999" sudah digunakan! (Simulated)';
                result.style.color = '#dc3545';
            } else {
                result.innerHTML = '✅ Project ID "' + projectId + '" tersedia (Simulated)';
                result.style.color = '#198754';
            }
        }
        
        // Test 3: API Call
        async function testAPICall() {
            const result = document.getElementById('api-result');
            result.innerHTML = '🔄 Testing API call...';
            
            try {
                const response = await fetch('check_project_id_uniqueness.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'project_id=PRJ999'
                });
                
                const data = await response.json();
                result.innerHTML = `
                    <h4>API Response:</h4>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                    <p><strong>Exists:</strong> ${data.exists ? 'YES' : 'NO'}</p>
                    <p><strong>Message:</strong> ${data.message}</p>
                `;
                console.log('🧪 API call successful:', data);
            } catch (error) {
                result.innerHTML = '❌ API call failed: ' + error.message;
                console.error('🧪 API call failed:', error);
            }
        }
        
        // Test 4: Real-time validation
        document.getElementById('realtimeProjectId').addEventListener('blur', async function() {
            const projectId = this.value.trim();
            const feedback = document.getElementById('realtimeFeedback');
            
            if (!projectId) return;
            
            try {
                const response = await fetch('check_project_id_uniqueness.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'project_id=' + encodeURIComponent(projectId)
                });
                
                const data = await response.json();
                
                if (data.exists) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                    feedback.textContent = '❌ Project ID "' + projectId + '" sudah digunakan!';
                    feedback.className = 'validation-feedback invalid-feedback';
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                    feedback.textContent = '✅ Project ID "' + projectId + '" tersedia';
                    feedback.className = 'validation-feedback valid-feedback';
                }
                
                console.log('🧪 Real-time validation result:', data);
            } catch (error) {
                feedback.textContent = '❌ Error: ' + error.message;
                feedback.style.color = '#dc3545';
                console.error('🧪 Real-time validation error:', error);
            }
        });
        
        // Clear validation on input
        document.getElementById('realtimeProjectId').addEventListener('input', function() {
            this.classList.remove('is-valid', 'is-invalid');
            const feedback = document.getElementById('realtimeFeedback');
            feedback.textContent = '';
            feedback.className = 'validation-feedback';
        });
        
        // Auto-test on page load
        window.addEventListener('load', function() {
            console.log('🧪 Test page loaded, running auto-tests...');
            
            // Auto-fill PRJ999 for testing
            document.getElementById('testProjectId').value = 'PRJ999';
            document.getElementById('realtimeProjectId').value = 'PRJ999';
            
            // Auto-run basic test
            setTimeout(testBasicJS, 1000);
        });
    </script>
</body>
</html>
