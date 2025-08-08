<?php
// Debug toggle button issue
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Toggle Button</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .debug-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        
        .toggle-debug {
            border: 2px solid red;
            padding: 10px;
            margin: 10px 0;
        }
        
        /* Modern Theme Toggle Button */
        .modern-theme-toggle {
            position: relative !important;
            cursor: pointer !important;
            border: 2px solid blue !important;
            outline: none !important;
            background: yellow !important;
            padding: 0 !important;
            margin: 10px !important;
            width: 70px !important;
            height: 35px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.3s ease !important;
            opacity: 1 !important;
            visibility: visible !important;
            z-index: 1000 !important;
        }

        .toggle-track {
            position: relative !important;
            width: 70px !important;
            height: 35px !important;
            background: linear-gradient(135deg, #87CEEB 0%, #B0E0E6 100%) !important;
            border-radius: 17.5px !important;
            padding: 3px !important;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
            overflow: hidden !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .toggle-thumb {
            position: relative !important;
            width: 29px !important;
            height: 29px !important;
            background: #ffffff !important;
            border-radius: 50% !important;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
            z-index: 10 !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .sun-disc {
            position: absolute !important;
            width: 20px !important;
            height: 20px !important;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%) !important;
            border-radius: 50% !important;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1) !important;
            opacity: 1 !important;
            transform: scale(1) !important;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5) !important;
            z-index: 5 !important;
            visibility: visible !important;
            display: block !important;
        }

        .moon-disc {
            position: absolute !important;
            width: 20px !important;
            height: 20px !important;
            background: linear-gradient(135deg, #E5E7EB 0%, #D1D5DB 100%) !important;
            border-radius: 50% !important;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1) !important;
            opacity: 0 !important;
            transform: scale(0.8) !important;
            box-shadow: 0 0 10px rgba(229, 231, 235, 0.5) !important;
            z-index: 5 !important;
            visibility: visible !important;
            display: block !important;
        }

        /* Dark mode styles */
        [data-theme="dark"] .toggle-track {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important;
        }

        [data-theme="dark"] .toggle-thumb {
            transform: translateX(35px) !important;
            background: #374151 !important;
        }

        [data-theme="dark"] .sun-disc {
            opacity: 0 !important;
            transform: scale(0.8) !important;
        }

        [data-theme="dark"] .moon-disc {
            opacity: 1 !important;
            transform: scale(1) !important;
        }
    </style>
</head>
<body>
    <div class="debug-container">
        <h2>Debug Toggle Button</h2>
        <p>Current theme: <span id="current-theme">light</span></p>
        
        <div class="toggle-debug">
            <h3>Test 1: Basic Toggle Button</h3>
            <button type="button" data-theme-toggle class="modern-theme-toggle">
                <div class="toggle-track">
                    <div class="toggle-thumb">
                        <div class="sun-disc"></div>
                        <div class="moon-disc"></div>
                    </div>
                </div>
            </button>
        </div>
        
        <div class="toggle-debug">
            <h3>Test 2: Simple Button (for comparison)</h3>
            <button style="background: red; color: white; padding: 10px; border: none;">Simple Button</button>
        </div>
        
        <div class="toggle-debug">
            <h3>Test 3: Toggle with different class name</h3>
            <button type="button" class="test-toggle-button" style="background: green; color: white; padding: 10px; border: none;">Test Toggle</button>
        </div>
    </div>

    <script>
        // Debug script
        console.log('Debug script loaded');
        
        const button = document.querySelector("[data-theme-toggle]");
        const themeSpan = document.getElementById("current-theme");
        let currentTheme = localStorage.getItem("theme") || "light";

        console.log('Button found:', button);
        console.log('Theme span found:', themeSpan);

        function updateTheme(theme) {
            document.documentElement.setAttribute("data-theme", theme);
            if (themeSpan) {
                themeSpan.textContent = theme;
            }
            localStorage.setItem("theme", theme);
            console.log('Theme updated to:', theme);
        }

        if (button) {
            console.log('Button exists, adding event listener');
            updateTheme(currentTheme);

            button.addEventListener("click", () => {
                console.log('Button clicked');
                const newTheme = currentTheme === "dark" ? "light" : "dark";
                currentTheme = newTheme;
                updateTheme(newTheme);
            });
        } else {
            console.log('Button not found!');
        }
    </script>
</body>
</html> 