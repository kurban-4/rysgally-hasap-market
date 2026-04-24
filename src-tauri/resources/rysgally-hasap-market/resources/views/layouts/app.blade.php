<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rysgally Hasap Market</title>
    
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/market-icon.svg') }}">
</head>
<body>
    @yield('content')

<script>
    function setCookie(name, value, days = 365) {
        const d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value};expires=${d.toUTCString()};path=/`;
    }

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const deviceType = getCookie('device_type');
        const tillId = getCookie('till_id');

        if (!deviceType) {
            renderSetupOverlay();
        }

        if (deviceType === 'till' && tillId) {
            document.addEventListener('submit', function(e) {
                if (!e.target.querySelector('input[name="till_id"]')) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'till_id';
                    input.value = tillId;
                    e.target.appendChild(input);
                }
            });
        }

        document.body.classList.add('loaded');
    });

    function renderSetupOverlay() {
        const overlay = document.createElement('div');
        overlay.style = "position:fixed;top:0;left:0;width:100%;height:100%;background:#1a202c;z-index:9999;display:flex;align-items:center;justify-content:center;font-family:sans-serif;";
        overlay.innerHTML = `
            <div style="background:white;padding:40px;border-radius:12px;text-align:center;width:400px;box-shadow:0 10px 25px rgba(0,0,0,0.2);">
                <h2 style="color:#2d3748;margin-bottom:20px;">Регистрация устройства</h2>
                <p style="color:#718096;margin-bottom:20px;">Введите 0 для менеджера, или номер кассы (1, 2, 3...)</p>
                <input type="number" id="setup_input" placeholder="0, 1, 2..." 
                       style="width:100%;padding:15px;border:2px solid #e2e8f0;border-radius:8px;margin-bottom:20px;font-size:24px;text-align:center;font-weight:bold;">
                <button onclick="saveDevice()" 
                        style="width:100%;padding:15px;background:#4a5568;color:white;border:none;border-radius:8px;font-weight:bold;cursor:pointer;font-size:16px;">
                    ПОДТВЕРДИТЬ
                </button>
                <div id="setup_error" style="color:red;margin-top:10px;display:none;"></div>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    async function saveDevice() {
        const val = document.getElementById('setup_input').value.trim();
        const errorDiv = document.getElementById('setup_error');
        
        if (val === '') {
            errorDiv.textContent = 'Пожалуйста, введите число!';
            errorDiv.style.display = 'block';
            return;
        }

        if (val === '0') {
            setCookie('device_type', 'manager');
            setCookie('till_id', '0');
            location.reload();
            return;
        }

        try {
            const response = await fetch('/api/setup-device', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: val })
            });

            if (!response.ok) throw new Error('Ошибка сервера');

            const data = await response.json();
            setCookie('device_type', 'till');
            setCookie('till_id', data.id);
            location.reload();
        } catch (error) {
            errorDiv.textContent = 'Ошибка связи с сервером!';
            errorDiv.style.display = 'block';
        }
    }
</script>
</body>
</html>