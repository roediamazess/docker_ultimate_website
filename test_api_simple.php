<!DOCTYPE html>
<html>
<head>
    <title>Simple API Test</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🧪 Simple API Test</h1>
    
    <div>
        <button onclick="testPRJ999()">Test PRJ999 (Existing)</button>
        <button onclick="testNewID()">Test PRJ_NEW_001 (New)</button>
        <button onclick="clearResults()">Clear Results</button>
    </div>
    
    <div id="results"></div>

    <script>
        function clearResults() {
            document.getElementById('results').innerHTML = '';
        }

        function addResult(content, type = 'info') {
            const resultsDiv = document.getElementById('results');
            const resultDiv = document.createElement('div');
            resultDiv.className = `result ${type}`;
            resultDiv.innerHTML = content;
            resultsDiv.appendChild(resultDiv);
        }

        async function testPRJ999() {
            addResult('<h3>🔍 Testing PRJ999 (Existing Project ID)</h3>', 'info');
            
            try {
                const response = await fetch('check_project_id_uniqueness.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'project_id=PRJ999'
                });
                
                addResult(`<p><strong>Response Status:</strong> ${response.status}</p>`, 'info');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const responseText = await response.text();
                addResult(`<p><strong>Raw Response:</strong></p><pre>${responseText}</pre>`, 'info');
                
                // Try to find JSON part
                const jsonStart = responseText.indexOf('{');
                if (jsonStart !== -1) {
                    const jsonPart = responseText.substring(jsonStart);
                    addResult(`<p><strong>JSON Part:</strong></p><pre>${jsonPart}</pre>`, 'info');
                    
                    try {
                        const result = JSON.parse(jsonPart);
                        addResult(`<p><strong>Parsed Result:</strong></p><pre>${JSON.stringify(result, null, 2)}</pre>`, 'success');
                        
                        if (result.exists) {
                            addResult(`<p style="color: red; font-weight: bold;">❌ Project ID PRJ999 sudah ada di database!</p>`, 'error');
                            if (result.project_info) {
                                addResult(`<p><strong>Project Info:</strong></p><ul>
                                    <li>Project Name: ${result.project_info.project_name || 'N/A'}</li>
                                    <li>Hotel: ${result.project_info.hotel_name_text || 'N/A'}</li>
                                    <li>Type: ${result.project_info.type || 'N/A'}</li>
                                    <li>Status: ${result.project_info.status || 'N/A'}</li>
                                    <li>Created: ${result.project_info.created_at || 'N/A'}</li>
                                </ul>`, 'info');
                            }
                        } else {
                            addResult(`<p style="color: green; font-weight: bold;">✅ Project ID PRJ999 tersedia (ini seharusnya tidak terjadi)</p>`, 'error');
                        }
                    } catch (parseError) {
                        addResult(`<p style="color: red; font-weight: bold;">❌ JSON Parse Error: ${parseError.message}</p>`, 'error');
                    }
                } else {
                    addResult(`<p style="color: red; font-weight: bold;">❌ Tidak ada JSON dalam response</p>`, 'error');
                }
                
            } catch (error) {
                addResult(`<p style="color: red; font-weight: bold;">❌ Error: ${error.message}</p>`, 'error');
            }
        }

        async function testNewID() {
            addResult('<h3>🔍 Testing PRJ_NEW_001 (New Project ID)</h3>', 'info');
            
            try {
                const response = await fetch('check_project_id_uniqueness.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'project_id=PRJ_NEW_001'
                });
                
                addResult(`<p><strong>Response Status:</strong> ${response.status}</p>`, 'info');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const responseText = await response.text();
                addResult(`<p><strong>Raw Response:</strong></p><pre>${responseText}</pre>`, 'info');
                
                // Try to find JSON part
                const jsonStart = responseText.indexOf('{');
                if (jsonStart !== -1) {
                    const jsonPart = responseText.substring(jsonStart);
                    addResult(`<p><strong>JSON Part:</strong></p><pre>${jsonPart}</pre>`, 'info');
                    
                    try {
                        const result = JSON.parse(jsonPart);
                        addResult(`<p><strong>Parsed Result:</strong></p><pre>${JSON.stringify(result, null, 2)}</pre>`, 'success');
                        
                        if (result.exists) {
                            addResult(`<p style="color: red; font-weight: bold;">❌ Project ID PRJ_NEW_001 sudah ada di database</p>`, 'error');
                        } else {
                            addResult(`<p style="color: green; font-weight: bold;">✅ Project ID PRJ_NEW_001 tersedia dan dapat digunakan</p>`, 'success');
                        }
                    } catch (parseError) {
                        addResult(`<p style="color: red; font-weight: bold;">❌ JSON Parse Error: ${parseError.message}</p>`, 'error');
                    }
                } else {
                    addResult(`<p style="color: red; font-weight: bold;">❌ Tidak ada JSON dalam response</p>`, 'error');
                }
                
            } catch (error) {
                addResult(`<p style="color: red; font-weight: bold;">❌ Error: ${error.message}</p>`, 'error');
            }
        }

        // Auto-test on page load
        window.addEventListener('load', function() {
            addResult('<p>🚀 Page loaded. Click buttons above to test API endpoints.</p>', 'info');
        });
    </script>
</body>
</html>

