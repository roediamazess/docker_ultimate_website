<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ultimate Website</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/company/logo.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/company/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="{{ asset('assets/css/login-backgrounds.css') }}" rel="stylesheet">
    <script src="https://unpkg.com/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <style>
        :root{
            --login-primary-start:#667eea; /* purple-blue */
            --login-primary-end:#764ba2; /* deeper purple */
            --login-accent:#f093fb; /* pink-purple */
            --login-text-primary:#2d3748;
            --login-text-secondary:#4a5568;
            --login-text-light:#a0aec0;
            --login-bg-light:#f7fafc;
            --login-bg-dark:#1a202c;
            --login-card-light:#ffffff;
            --login-card-dark:#2d3748;
            --login-border-light:#e2e8f0;
            --login-border-dark:#4a5568;
            --login-shadow:0 20px 60px rgba(102,126,234,0.15);
            --ripple-color-light:rgba(102,126,234,0.1);
            --ripple-color-dark:rgba(156,163,175,0.1);
            --dash-text-1-light:#1a202c; --dash-text-2-light:#4a5568;
            --dash-text-1-dark:#f7fafc; --dash-text-2-dark:#e2e8f0;
        }
        body{ margin:0; padding:0; min-height:100vh; overflow:hidden; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif }
        .login-container{ display:flex; align-items:center; justify-content:center; min-height:100vh; position:relative; overflow:hidden }
        .background-animation{ position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:-1; transition:all 1s ease-in-out }
        .login-card{ background:var(--login-card-light); border-radius:24px; padding:48px; box-shadow:var(--login-shadow); max-width:420px; width:100%; margin:20px; backdrop-filter:blur(20px); position:relative; z-index:10 }
        .login-header{ text-align:center; margin-bottom:32px }
        .login-logo{ width:80px; height:80px; margin:0 auto 24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:36px; color:white }
        .login-title{ font-size:28px; font-weight:700; color:var(--login-text-primary); margin-bottom:8px }
        .time-greeting{ font-size:16px; color:var(--login-text-secondary) }
        .form-group{ position:relative; margin-bottom:24px }
        .form-input{ width:100%; padding:16px 20px 16px 56px; border:2px solid var(--login-border-light); border-radius:12px; font-size:16px; background:transparent; transition:all 0.3s ease; box-sizing:border-box }
        .form-input:focus{ outline:none; border-color:var(--login-primary-start); box-shadow:0 0 0 3px rgba(102,126,234,0.1) }
        .input-icon{ position:absolute; left:20px; top:50%; transform:translateY(-50%); font-size:20px; color:var(--login-text-light) }
        .password-toggle{ position:absolute; right:16px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--login-text-light); cursor:pointer; padding:4px }
        .login-btn{ width:100%; padding:16px; background:linear-gradient(135deg,var(--login-primary-start),var(--login-primary-end)); border:none; border-radius:12px; color:white; font-size:16px; font-weight:600; cursor:pointer; transition:all 0.3s ease; margin-bottom:16px }
        .login-btn:hover{ transform:translateY(-2px); box-shadow:0 8px 25px rgba(102,126,234,0.3) }
        .error-message{ background:linear-gradient(135deg,#ff6b6b,#ee5a52); color:white; padding:16px; border-radius:12px; margin-bottom:24px; display:flex; align-items:center; font-weight:500 }
        .success-message{ background:linear-gradient(135deg,#51cf66,#40c057); color:white; padding:16px; border-radius:12px; margin-bottom:24px; display:flex; align-items:center; font-weight:500 }
        .screen-container{ position:fixed; top:0; left:0; width:100vw; height:100vh; display:flex; align-items:center; justify-content:center; z-index:9999; background:var(--login-bg-light); opacity:0; pointer-events:none; transition:opacity 0.5s ease }
        .screen-container.is-unlocked{ opacity:1; pointer-events:all }
        .screen-container .home-screen{ position:absolute; opacity:0; transform:translateY(20px); transition:transform .6s 1.2s cubic-bezier(.16,1,.3,1), opacity .6s 1.2s ease-in; text-align:center }
        .screen-container .success-title{ font-size:56px; font-weight:800; margin:0 }
        .screen-container .success-subtitle{ font-size:20px; margin-top:8px }
        .ripple-effect{ position:absolute; top:50%; left:50%; width:250vmax; height:250vmax; border-radius:50%; background-color: var(--ripple-color-light); transform:translate(-50%,-50%) scale(0) }
        html[data-theme="dark"] .ripple-effect{ background-color: var(--ripple-color-dark) }
        html[data-theme="light"] .ripple-effect{ background-color: var(--ripple-color-light) }
        html[data-theme="light"] .success-title{ color:var(--dash-text-1-light) }
        html[data-theme="light"] .success-subtitle{ color:var(--dash-text-2-light) }
        html[data-theme="dark"] .success-title{ color:var(--dash-text-1-dark) }
        html[data-theme="dark"] .success-subtitle{ color:var(--dash-text-2-dark) }
        .screen-container.is-unlocked .home-screen{ transform:translateY(0); opacity:1 }
        .screen-container.is-unlocked .ripple-effect{ animation:ripple-animation 1.5s cubic-bezier(.22,1,.36,1) forwards }
        @keyframes ripple-animation{ from{ transform:translate(-50%,-50%) scale(0) } to{ transform:translate(-50%,-50%) scale(1) } }
        @media (prefers-reduced-motion: reduce){
            .screen-container.is-unlocked .ripple-effect{ animation:none; transform:translate(-50%,-50%) scale(1) }
            .screen-container .home-screen{ transition:none; opacity:1; transform:none }
        }
    </style>
    
    <script>
        // Function untuk mengupdate waktu berdasarkan waktu lokal PC
        function updateTimeBasedContent() {
            const now = new Date();
            const hour = now.getHours();
            const backgroundElement = document.querySelector('.background-animation');
            const timeOfDayElement = document.getElementById('timeOfDay');
            
            let timeOfDay = '';
            let bgClass = '';
            
            // Adjusted time ranges for better Indonesian context
            if (hour >= 5 && hour < 11) { timeOfDay = 'Pagi Gaes!'; bgClass = 'morning'; }
            else if (hour >= 11 && hour < 16) { timeOfDay = 'Siang Gaes!'; bgClass = 'afternoon'; }
            else if (hour >= 16 && hour < 19) { timeOfDay = 'Sore Gaes!'; bgClass = 'evening'; }
            else { timeOfDay = 'Malam Gaes!'; bgClass = 'night'; }
            
            if (timeOfDayElement) { timeOfDayElement.textContent = timeOfDay; }
            if (backgroundElement) {
                backgroundElement.classList.remove('morning','afternoon','evening','night');
                backgroundElement.classList.add(bgClass);
            }
        }
        document.addEventListener('DOMContentLoaded', function(){ updateTimeBasedContent(); setInterval(updateTimeBasedContent, 60000); });

        // Toggle password visibility
        function togglePassword(){
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            if (passwordInput.type === 'password') { passwordInput.type = 'text'; passwordIcon.setAttribute('icon','solar:eye-closed-outline'); }
            else { passwordInput.type = 'password'; passwordIcon.setAttribute('icon','solar:eye-outline'); }
        }
    </script>
</head>
<body>
    <div class="login-container">
        <!-- Dynamic Background -->
        <div class="background-animation morning"></div>
        
        <!-- Login Card -->
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo" style="background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 auto 48px !important;">
                    <img src="{{ asset('assets/images/company/logo.png') }}" alt="PPSolution Logo" style="height: 120px; width: auto; max-width: 200px; background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; cursor: pointer; display: block;" onmouseover="this.style.animation='spin 2s linear infinite'" onmouseout="this.style.animation='none'; this.style.transform='rotate(0deg)';">
                </div>
                <h1 class="login-title">Welcome Back! 👋</h1>
                <div class="time-greeting" id="timeGreeting">Selamat <span id="timeOfDay">Gaes!</span></div>
            </div>

            @if (session('error'))
            <div class="error-message"><iconify-icon icon="solar:danger-triangle-outline" style="margin-right: 8px;"></iconify-icon>{{ session('error') }}</div>
            @endif

            @if (session('success'))
            <div class="success-message"><iconify-icon icon="solar:check-circle-outline" style="margin-right: 8px;"></iconify-icon>{{ session('success') }}</div>
            @endif

            <form method="post" action="{{ url('/login') }}">
                @csrf
                <div class="form-group">
                    <input type="email" name="email" class="form-input" placeholder="Email" required autocomplete="username" value="{{ old('email') }}">
                    <iconify-icon icon="solar:letter-outline" class="input-icon"></iconify-icon>
                </div>

                <div class="form-group">
                    <input type="password" name="password" id="password" class="form-input" placeholder="Password" required autocomplete="current-password">
                    <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                        <iconify-icon icon="solar:eye-outline" id="password-icon"></iconify-icon>
                    </button>
                </div>

                <button type="submit" name="login" class="login-btn">Login</button>
            </form>

            <div style="margin-top: 24px; text-align: center;">
                <p style="color: #666; font-size: 14px; margin-bottom: 0;">
                    <a href="#" style="color: #667eea; text-decoration: none;">Forgot Password? Click here</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Success Screen (untuk animasi login) -->
    <div class="screen-container" id="success-screen">
        <div class="ripple-effect"></div>
        <div class="home-screen">
            <h1 class="success-title">🎉</h1>
            <p class="success-subtitle">Login Berhasil!</p>
        </div>
    </div>

    <script>
        // Jika ada redirect setelah login sukses
        @if (session('login_success'))
        document.addEventListener('DOMContentLoaded', function() {
            const successScreen = document.getElementById('success-screen');
            successScreen.classList.add('is-unlocked');
            
            setTimeout(function() {
                window.location.href = '{{ url("/") }}';
            }, 2000);
        });
        @endif
    </script>
</body>
</html>
