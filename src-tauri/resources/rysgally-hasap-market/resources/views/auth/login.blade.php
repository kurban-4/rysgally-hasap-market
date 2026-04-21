@extends('layouts.app')

@section('content')

<div class="login-root">

    {{-- Left panel: branding --}}
    <div class="login-left">
        <div class="brand-mark">
            <div class="brand-cart">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                    <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 104 0v-4M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"
                          stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <div class="brand-name">RysgallyMarket</div>
                <div class="brand-tagline">Fresh · Fast · Smart</div>
            </div>
        </div>

        <div class="hero-text">
            <div class="hero-eyebrow">Management System</div>
            <h1 class="hero-heading">Your market,<br><em>fully in control.</em></h1>
            <p class="hero-sub">Track inventory, manage sales, oversee wholesale — all from one beautiful dashboard.</p>
        </div>

        <div class="feature-pills">
            <div class="pill"><span class="pill-dot green"></span> Live inventory</div>
            <div class="pill"><span class="pill-dot amber"></span> Weight & unit items</div>
            <div class="pill"><span class="pill-dot blue"></span> Sales reports</div>
        </div>

        {{-- Decorative circles --}}
        <div class="deco-circle c1"></div>
        <div class="deco-circle c2"></div>
        <div class="deco-circle c3"></div>
    </div>

    {{-- Right panel: form --}}
    <div class="login-right">
        <div class="form-card">

            <div class="form-header">
                <div class="form-icon">
                    <i class="bi bi-lock-fill"></i>
                </div>
                <h2 class="form-title">{{ __('app.login_title') }}</h2>
                <p class="form-subtitle">{{ __('app.login_subtitle') }}</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field-group">
                    <label class="field-label">{{ __('app.label_username') }}</label>
                    <div class="field-wrap {{ $errors->has('username') ? 'has-error' : '' }}">
                        <i class="bi bi-person field-icon"></i>
                        <input
                            id="username" type="text" name="username"
                            class="field-input"
                            value="{{ old('username') }}"
                            required autofocus
                            placeholder="{{ __('app.placeholder_username') }}">
                    </div>
                    @error('username')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field-group">
                    <label class="field-label">{{ __('app.label_password') }}</label>
                    <div class="field-wrap {{ $errors->has('password') ? 'has-error' : '' }}">
                        <i class="bi bi-key field-icon"></i>
                        <input
                            id="password" type="password" name="password"
                            class="field-input"
                            required autocomplete="current-password"
                            placeholder="••••••••">
                        <button type="button" class="eye-btn" onclick="togglePwd()">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-login">
                    <span>{{ __('app.btn_login') }}</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="form-footer">
                &copy; {{ date('Y') }} RysgallyMarket — Secure Login
            </div>
        </div>
    </div>

</div>

<script>
function togglePwd() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

function setAppHeight() {
    document.documentElement.style.setProperty(
        '--app-height', `${window.innerHeight}px`
    );
}
window.addEventListener('resize', setAppHeight);
setAppHeight();
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:ital,wght@0,400;0,500;1,400&display=swap');

:root {
    --ora: #E8722A;
    --ora-dark: #C4561A;
    --ora-deeper: #8B3A0F;
    --ora-light: #FFF0E6;
    --ora-glow: rgba(232,114,42,0.3);
    --green: #2E7D32;
    --amber: #F9A825;
    --blue: #1565C0;
    --bg: #FBF7F3;
    --dark: #1A0A00;
    --app-height: 100%;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { 
    height: 100%; 
    overflow: hidden;
    margin: 0;
    padding: 0;
}
.login-root {
    display: flex;
    height: 100dvh; /* dynamic viewport height — работает в Tauri */
    min-height: -webkit-fill-available; /* для WebKit/Safari */
    overflow: hidden;
}

/* ── LEFT PANEL ── */
.login-left {
    flex: 0 0 48%;
    background: linear-gradient(145deg, #1A0A00 0%, #2E1100 40%, #3D1A00 70%, #1A0800 100%);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 40px 52px;
    position: relative;
    overflow: hidden;
}

.brand-mark {
    display: flex;
    align-items: center;
    gap: 14px;
    z-index: 2;
}
.brand-cart {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, var(--ora), var(--ora-dark));
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 6px 20px var(--ora-glow);
}
.brand-name {
    font-family: 'Sora', sans-serif;
    font-size: 1.2rem; font-weight: 800;
    color: white; line-height: 1;
}
.brand-tagline {
    font-size: 0.62rem; font-weight: 600;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase; letter-spacing: 2px;
    margin-top: 3px;
}

.hero-text { z-index: 2; }
.hero-eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px;
    color: var(--ora);
    background: rgba(232,114,42,0.12);
    border: 1px solid rgba(232,114,42,0.25);
    padding: 5px 14px; border-radius: 50px;
    margin-bottom: 20px;
}
.hero-heading {
    font-family: 'Sora', sans-serif;
    font-size: 3rem; font-weight: 800; line-height: 1.12;
    color: white;
    margin-bottom: 18px;
}
.hero-heading em {
    font-style: normal;
    background: linear-gradient(90deg, var(--ora), #F9C85A);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.hero-sub {
    font-size: 0.95rem; line-height: 1.7;
    color: rgba(255,255,255,0.5);
    max-width: 360px;
}

.feature-pills {
    display: flex; gap: 10px; flex-wrap: wrap;
    z-index: 2;
}
.pill {
    display: flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    padding: 7px 14px; border-radius: 50px;
    font-size: 0.78rem; font-weight: 600; color: rgba(255,255,255,0.7);
}
.pill-dot {
    width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
}
.pill-dot.green  { background: #4ADE80; box-shadow: 0 0 8px rgba(74,222,128,0.6); }
.pill-dot.amber  { background: #FCD34D; box-shadow: 0 0 8px rgba(252,211,77,0.6); }
.pill-dot.blue   { background: #60A5FA; box-shadow: 0 0 8px rgba(96,165,250,0.6); }

.deco-circle {
    position: absolute; border-radius: 50%;
    border: 1px solid rgba(232,114,42,0.12);
    pointer-events: none;
}
.c1 { width: 420px; height: 420px; bottom: -160px; right: -120px; }
.c2 { width: 260px; height: 260px; bottom: -60px; right: -20px; border-color: rgba(232,114,42,0.2); }
.c3 { width: 120px; height: 120px; bottom: 60px; right: 60px; background: rgba(232,114,42,0.06); border: none; }

/* ── RIGHT PANEL ── */
.login-right {
    flex: 1;
    display: flex; align-items: center; justify-content: center;
    background: var(--bg);
    padding: 40px 32px;
    overflow-y: auto;
}

.form-card {
    width: 100%;
    max-width: 420px;
}

.form-header { text-align: center; margin-bottom: 40px; }
.form-icon {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, var(--ora), var(--ora-dark));
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: white;
    margin: 0 auto 18px;
    box-shadow: 0 8px 24px var(--ora-glow);
}
.form-title {
    font-family: 'Sora', sans-serif;
    font-size: 1.7rem; font-weight: 800;
    color: var(--dark); margin-bottom: 8px;
}
.form-subtitle { font-size: 0.88rem; color: #8B7355; }

.field-group { margin-bottom: 20px; }
.field-label {
    display: block;
    font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;
    color: #6B4E2A; margin-bottom: 8px;
}
.field-wrap {
    display: flex; align-items: center;
    background: white;
    border: 1.5px solid #E8DDD3;
    border-radius: 14px;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.field-wrap:focus-within {
    border-color: var(--ora);
    box-shadow: 0 0 0 4px rgba(232,114,42,0.1);
}
.field-wrap.has-error { border-color: #E53E3E; }
.field-icon {
    padding: 0 14px;
    color: #B8936A; font-size: 0.95rem; flex-shrink: 0;
}
.field-input {
    flex: 1; border: none; background: transparent;
    padding: 14px 12px 14px 0;
    font-size: 0.95rem; font-family: 'DM Sans', sans-serif;
    color: var(--dark); outline: none;
}
.field-input::placeholder { color: #C4A98A; }
.eye-btn {
    background: none; border: none; cursor: pointer;
    padding: 0 14px; color: #B8936A;
    font-size: 0.9rem; flex-shrink: 0;
    transition: color 0.15s;
}
.eye-btn:hover { color: var(--ora); }
.field-error { display: block; font-size: 0.75rem; color: #E53E3E; margin-top: 6px; font-weight: 600; }

.btn-login {
    width: 100%;
    display: flex; align-items: center; justify-content: center; gap: 10px;
    background: linear-gradient(135deg, var(--ora), var(--ora-dark));
    color: white; border: none;
    padding: 16px;
    border-radius: 14px;
    font-family: 'Sora', sans-serif;
    font-size: 1rem; font-weight: 700;
    cursor: pointer; margin-top: 32px;
    transition: 0.2s;
    box-shadow: 0 6px 20px var(--ora-glow);
    letter-spacing: 0.3px;
}
.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px var(--ora-glow);
}
.btn-login:active { transform: translateY(0); }

.form-footer {
    text-align: center; margin-top: 32px;
    font-size: 0.72rem; color: #B8936A;
}

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
    .login-left { flex: 0 0 42%; padding: 32px 36px; }
    .hero-heading { font-size: 2.3rem; }
}
@media (max-width: 680px) {
    .login-root { flex-direction: column; height: auto; overflow: auto; }
    html, body { overflow: auto; height: auto; }
    .login-left { flex: none; padding: 36px 28px 48px; }
    .hero-heading { font-size: 2rem; }
    .hero-sub { display: none; }
    .deco-circle { display: none; }
    .login-right { padding: 32px 20px 48px; }
}
</style>
@endsection