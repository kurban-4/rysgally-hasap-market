{{-- resources/views/license.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>{{ __('app.license_title') }}</title>
</head>
<body>
    <div style="max-width: 400px; margin: 100px auto; text-align: center;">
        <h2>{{ __('app.license_page_title') }}</h2>
        <p>{{ __('app.license_desc') }}</p>

        @if($errors->any())
            <p style="color: red;">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="/license/activate">
            @csrf
            <input type="text" name="key" placeholder="RYSGALLY-XXXX-XXXX"
                   style="width: 100%; padding: 10px; margin: 10px 0;">
            <button type="submit" style="padding: 10px 30px;">
                {{ __('app.license_btn_activate') }}
            </button>
        </form>
    </div>
</body>
</html>