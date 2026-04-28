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
        document.addEventListener('DOMContentLoaded', function() {
            const deviceType = localStorage.getItem('device_type'); // 'till' или 'manager'
            const tillId = localStorage.getItem('till_id');

            // 1. Если компьютер новый - показываем окно регистрации
            if (!deviceType) {
                renderSetupOverlay();
            }

            // 2. Умное добавление till_id перед отправкой любой формы
            if (deviceType === 'till' && tillId) {
                document.addEventListener('submit', function(e) {
                    // Проверяем, есть ли уже такое поле, чтобы не дублировать
                    if (!e.target.querySelector('input[name="till_id"]')) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'till_id';
                        input.value = tillId;
                        e.target.appendChild(input);
                    }
                });
            }
        });

        function renderSetupOverlay() {
            const overlay = document.createElement('div');
            overlay.style = "position:fixed;top:0;left:0;width:100%;height:100%;background:#1a202c;z-index:9999;display:flex;align-items:center;justify-content:center;font-family:sans-serif;";
            overlay.innerHTML = `
                <div style="background:white;padding:40px;border-radius:12px;text-align:center;width:400px;box-shadow:0 10px 25px rgba(0, 255, 213, 0.5);">
                    <h2 style="color:#2d3748;margin-bottom:10px;">Регистрация устройства</h2>
                    
                    <input type="number" id="setup_input" placeholder="0, 1, 2..." 
                           style="width:100%;padding:15px;border:2px solid #e2e8f0;border-radius:8px;margin-bottom:20px;font-size:24px;text-align:center;font-weight:bold;">
                    
                    <button onclick="saveDevice()" 
                            style="width:100%;padding:15px;background:#4a5568;color:white;border:none;border-radius:8px;font-weight:bold;cursor:pointer;font-size:16px;">
                        ПОДТВЕРДИТЬ
                    </button>
                </div>
            `;
            document.body.appendChild(overlay);
        }

        async function saveDevice() {
            const val = document.getElementById('setup_input').value.trim();
            if (val === '') return alert("Пожалуйста, введите число!");

            if (val === '0') {
                localStorage.setItem('device_type', 'manager');
                localStorage.setItem('till_id', '0');
                location.reload();
            } else {
                try {
                    const response = await fetch('/api/setup-device', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            // Берем токен из meta-тега
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        // Отправляем просто число, слово "Касса №" подставит сам бэкенд
                        body: JSON.stringify({ name: val }) 
                    });
                    
                    if (!response.ok) throw new Error('Ошибка сервера');
                    
                    const data = await response.json();
                    
                    localStorage.setItem('device_type', 'till');
                    localStorage.setItem('till_id', data.id);
                    location.reload();
                } catch (error) {
                    alert("Ошибка связи с сервером! Проверьте подключение.");
                }
            }
        }
        // Show page only after all CSS is loaded
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('loaded');
        });
    </script>
</body>
</html>