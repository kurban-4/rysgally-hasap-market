@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main bg-light-mesh">
        <div class="welcome-scroll">
            <div class="welcome-inner">

                <header class="welcome-header animate-fade-in">
                    <div class="brand-logo-mini">
                        <i class="bi bi-heart-pulse-fill text-white"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-0 text-dark">{{ __('app.welcome_heading') }}</h2>
                        <p class="text-muted mb-0 welcome-sub">{{ __('app.welcome_subheading') }}</p>
                    </div>
                </header>

                <div class="nav-grid animate-grid">

                    <a href="{{ route('sales.index') }}" class="nav-card primary-card">
                        <div class="card-content">
                            <div class="icon-wrapper icon-primary">
                                <i class="bi bi-cart-check-fill"></i>
                            </div>
                            <div class="text-wrapper">
                                <h3>{{ __('app.welcome_retail_sales') }}</h3>
                                <p>{{ __('app.welcome_retail_sales_desc') }}</p>
                            </div>
                        </div>
                        <div class="card-arrow"><i class="bi bi-arrow-right"></i></div>
                    </a>

                    <a href="{{ route('sales.customers.index') }}" class="nav-card customers-card">
                        <div class="card-content">
                            <div class="icon-wrapper icon-customers">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="text-wrapper">
                                <h3>{{ __('app.welcome_customers') }}</h3>
                                <p>{{ __('app.welcome_customers_desc') }}</p>
                            </div>
                        </div>
                        <div class="card-arrow"><i class="bi bi-arrow-right"></i></div>
                    </a>

                    <a href="{{ route('sales.customers.index') }}" class="nav-card">
                        <div class="card-content">
                            <div class="icon-wrapper icon-blue">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div class="text-wrapper">
                                <h3>{{ __('app.welcome_order_history') }}</h3>
                                <p>{{ __('app.welcome_order_history_desc') }}</p>
                            </div>
                        </div>
                        <div class="card-arrow"><i class="bi bi-arrow-right"></i></div>
                    </a>

                    <a href="{{ route('storage.index') }}" class="nav-card">
                        <div class="card-content">
                            <div class="icon-wrapper icon-teal">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div class="text-wrapper">
                                <h3>{{ __('app.welcome_retail_storage') }}</h3>
                                <p>{{ __('app.welcome_retail_storage_desc') }}</p>
                            </div>
                        </div>
                        <div class="card-arrow"><i class="bi bi-arrow-right"></i></div>
                    </a>

                    <a href="{{ route('wholesale.index') }}" class="nav-card">
                        <div class="card-content">
                            <div class="icon-wrapper icon-purple">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div class="text-wrapper">
                                <h3>{{ __('app.welcome_wholesale_sales') }}</h3>
                                <p>{{ __('app.welcome_wholesale_sales_desc') }}</p>
                            </div>
                        </div>
                        <div class="card-arrow"><i class="bi bi-arrow-right"></i></div>
                    </a>

                    <a href="{{ route('wholesale_storage.index') }}" class="nav-card">
                        <div class="card-content">
                            <div class="icon-wrapper icon-orange">
                                <i class="bi bi-shop"></i>
                            </div>
                            <div class="text-wrapper">
                                <h3>{{ __('app.welcome_wholesale_storage') }}</h3>
                                <p>{{ __('app.welcome_wholesale_storage_desc') }}</p>
                            </div>
                        </div>
                        <div class="card-arrow"><i class="bi bi-arrow-right"></i></div>
                    </a>

                    <a href="{{ route('employees.index') }}" class="nav-card">
                        <div class="card-content">
                            <div class="icon-wrapper icon-gray">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="text-wrapper">
                                <h3>{{ __('app.welcome_employees') }}</h3>
                                <p>{{ __('app.welcome_employees_desc') }}</p>
                            </div>
                        </div>
                        <div class="card-arrow"><i class="bi bi-arrow-right"></i></div>
                    </a>

                </div>
            </div>
        </div>
    </main>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap');

:root {
    --primary: #E8722A;
    --primary-dark: #C4561A;
    --bg-light: #FBF7F3;
    --text-main: #1A0A00;
    --text-muted: #8B7355;
    --border: #EDE4DA;
}

*, *::before, *::after { box-sizing: border-box; }
body { margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; background: var(--bg-light); }

.desktop-app-layout {
    position: fixed;
    inset: 0;
    display: flex;
    overflow: hidden;
}

.desktop-app-layout .sidebar-wrapper {
    position: relative !important;
    flex-shrink: 0;
    height: 100%;
}

.app-main {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    height: 100%;
}

.bg-light-mesh {
    background-color: var(--bg-light);
    background-size: 30px 30px;
}

.welcome-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 40px 32px;
}
.welcome-scroll::-webkit-scrollbar { width: 5px; }
.welcome-scroll::-webkit-scrollbar-track { background: transparent; }
.welcome-scroll::-webkit-scrollbar-thumb { background: #D4C4B0; border-radius: 10px; }

.welcome-inner {
    max-width: 1200px;
    margin: 0 auto;
}

.welcome-header {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-bottom: 36px;
}

.brand-logo-mini {
    width: 50px; height: 50px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 8px 20px rgba(232,114,42,0.3);
    flex-shrink: 0;
}

.welcome-sub { font-size: 0.95rem; margin-top: 2px; }

.nav-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

.nav-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 26px 24px;
    border-radius: 20px;
    text-decoration: none;
    color: var(--text-main);
    border: 1px solid var(--border);
    box-shadow: 0 2px 12px rgba(26,10,0,0.04);
    transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    gap: 16px;
}

.nav-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 4px; height: 100%;
    background: transparent;
    transition: background 0.28s ease;
    border-radius: 4px 0 0 4px;
}

.nav-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(26,10,0,0.08);
    color: var(--text-main);
}
.nav-card:hover::before { background: var(--primary); }

.primary-card {
    grid-column: span 2;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white !important;
    border: none;
    box-shadow: 0 8px 24px rgba(232,114,42,0.3);
}
.primary-card::before { background: rgba(255,255,255,0.3) !important; }
.primary-card:hover { box-shadow: 0 16px 40px rgba(232,114,42,0.4); color: white !important; }
.primary-card .text-wrapper h3 { color: white !important; }
.primary-card .text-wrapper p   { color: rgba(255,255,255,0.75) !important; }

.customers-card {
    background: linear-gradient(135deg, #1A0A00 0%, #2E1400 100%);
    color: white !important;
    border: none;
    box-shadow: 0 8px 24px rgba(26,10,0,0.18);
}
.customers-card::before { background: var(--primary) !important; }
.customers-card:hover   { box-shadow: 0 16px 40px rgba(26,10,0,0.26); color: white !important; }
.customers-card .text-wrapper h3 { color: white !important; }
.customers-card .text-wrapper p  { color: rgba(255,255,255,0.6) !important; }

.card-content {
    display: flex;
    align-items: flex-start;
    gap: 18px;
    min-width: 0;
    flex: 1;
}

.icon-wrapper {
    width: 56px; height: 56px;
    border-radius: 15px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
}

.icon-primary   { background: rgba(255,255,255,0.2);  color: white; }
.icon-customers { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(232,114,42,0.35); }
.icon-blue      { background: rgba(13,110,253,0.1);  color: #0d6efd; }
.icon-teal      { background: rgba(13,169,131,0.1);  color: #0DA983; }
.icon-purple    { background: rgba(111,66,193,0.1);  color: #6f42c1; }
.icon-orange    { background: rgba(232,114,42,0.12); color: var(--primary); }
.icon-gray      { background: #F5F0EA; color: #8B7355; border: 1px solid var(--border); }

.text-wrapper { min-width: 0; }
.text-wrapper h3 {
    font-size: 1.05rem; font-weight: 700;
    margin-bottom: 6px;
    transition: color 0.2s;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    font-family: 'Sora', sans-serif;
}
.text-wrapper p {
    font-size: 0.8rem; color: var(--text-muted);
    margin: 0; line-height: 1.45;
}
.nav-card:not(.primary-card):not(.customers-card):hover .text-wrapper h3 { color: var(--primary); }

.card-arrow {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: white;
    border: 2px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: #C4B4A0;
    font-size: 1.1rem;
    transition: all 0.28s ease;
    flex-shrink: 0;
}
.nav-card:not(.primary-card):not(.customers-card):hover .card-arrow {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
    transform: translateX(4px);
    box-shadow: 0 4px 14px rgba(232,114,42,0.3);
}
.primary-card .card-arrow {
    background: transparent;
    border-color: rgba(255,255,255,0.4);
    color: white;
}
.primary-card:hover .card-arrow {
    background: white;
    border-color: white;
    color: var(--primary);
    transform: translateX(4px);
}
.customers-card .card-arrow {
    background: rgba(255,255,255,0.08);
    border-color: rgba(255,255,255,0.2);
    color: rgba(255,255,255,0.75);
}
.customers-card:hover .card-arrow {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
    transform: translateX(4px);
    box-shadow: 0 4px 14px rgba(232,114,42,0.3);
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}

.animate-grid > * {
    opacity: 0;
    animation: slideUp 0.5s ease-out forwards;
}
.animate-grid > *:nth-child(1) { animation-delay: 0.05s; }
.animate-grid > *:nth-child(2) { animation-delay: 0.12s; }
.animate-grid > *:nth-child(3) { animation-delay: 0.19s; }
.animate-grid > *:nth-child(4) { animation-delay: 0.26s; }
.animate-grid > *:nth-child(5) { animation-delay: 0.33s; }
.animate-grid > *:nth-child(6) { animation-delay: 0.40s; }
.animate-grid > *:nth-child(7) { animation-delay: 0.47s; }

@keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp {
    from { opacity: 0; transform: translateY(22px); }
    to   { opacity: 1; transform: translateY(0); }
}

@media (max-width: 1023px) {
    .nav-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
    .primary-card { grid-column: span 2; }
    .welcome-scroll { padding: 28px 20px; }
}
@media (max-width: 767px) {
    
    .desktop-app-layout {
        position: relative !important;
        inset: auto !important;
        min-height: 100vh;
        height: auto !important;
        flex-direction: column;
        overflow: auto !important;
    }
    .desktop-app-layout .sidebar-wrapper { position: fixed !important; }
    .app-main { height: auto !important; overflow: auto !important; }

    .welcome-scroll { padding: 16px 12px 16px 12px; }

    .welcome-header { padding-left: 56px; margin-bottom: 20px; gap: 12px; }
    .brand-logo-mini { width: 40px; height: 40px; font-size: 1.2rem; }
    .welcome-header h2 { font-size: 1.2rem; }
    .welcome-sub { font-size: 0.82rem; }

    .nav-grid { grid-template-columns: 1fr; gap: 10px; }
    .primary-card, .customers-card { grid-column: span 1; }

    .nav-card { padding: 18px 16px; border-radius: 16px; }
    .icon-wrapper { width: 46px; height: 46px; font-size: 1.3rem; border-radius: 12px; }
    .text-wrapper h3 { font-size: 0.95rem; }
    .text-wrapper p  { font-size: 0.75rem; }
    .card-arrow { width: 36px; height: 36px; font-size: 0.95rem; }
}
</style>
@endsection