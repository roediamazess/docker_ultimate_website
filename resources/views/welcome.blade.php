<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            margin: 20px;
        }
        .logo {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 20px;
        }
        .title {
            font-size: 28px;
            color: #333;
            margin-bottom: 15px;
        }
        .subtitle {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .success-badge {
            background: #10b981;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .links {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        .link {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 15px 25px;
            text-decoration: none;
            color: #4a5568;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .link:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        .info {
            background: #f7fafc;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        .info-title {
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 10px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🚀</div>
        <div class="success-badge">✅ Laravel Successfully Running!</div>
        <h1 class="title">Ultimate Website</h1>
        <p class="subtitle">
            Your Laravel application is now running successfully with Docker. 
            The framework has been integrated and is ready for development.
        </p>
        
        <div class="info">
            <div class="info-title">System Information</div>
            <div class="info-item">
                <span>Laravel Version:</span>
                <span>{{ app()->version() }}</span>
            </div>
            <div class="info-item">
                <span>PHP Version:</span>
                <span>{{ phpversion() }}</span>
            </div>
            <div class="info-item">
                <span>Environment:</span>
                <span>{{ app()->environment() }}</span>
            </div>
            <div class="info-item">
                <span>Debug Mode:</span>
                <span>{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</span>
            </div>
        </div>

        <div class="links">
            <a href="/health.php" class="link">Health Check</a>
            <a href="/check_env.php" class="link">Config Check</a>
            <a href="http://localhost:8081" class="link" target="_blank">PgAdmin</a>
            <a href="http://localhost:8025" class="link" target="_blank">Mailpit</a>
        </div>
    </div>
</body>
</html>