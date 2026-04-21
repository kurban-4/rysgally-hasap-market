{{--
    SYNC SCRIPT: Runs during HTML parsing, BEFORE first paint.
--}}
<script>
(function() {
    if (document.querySelector('.desktop-app-layout')) {
        document.body.classList.add('desktop-app-mode');
    } else {
        document.body.classList.add('normal-page-layout');
    }
})();
</script>

<aside class="sidebar-wrapper" id="sidebar-wrapper">

    <div class="sidebar-brand">
        <a class="d-flex align-items-center gap-3 text-decoration-none" href="{{ route('welcome') }}">
            <div class="brand-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 104 0v-4M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="sidebar-brand-text">
                <h5 class="fw-black mb-0 text-white lh-1" style="letter-spacing:-0.8px;font-size:0.95rem;">RysgallyMarket</h5>
                <small class="text-uppercase fw-bold" style="font-size:0.5rem;letter-spacing:2.5px;color:rgba(255,255,255,0.5);">Fresh · Fast · Smart</small>
            </div>
        </a>
    </div>

    <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close menu">
        <i class="bi bi-x-lg"></i>
    </button>

    <nav class="sidebar-nav">
        <ul class="nav flex-column gap-1">
@php
    $navLinks = [
        ['route'=>'boss.dashboard',           'label'=>'Admin',             'icon'=>'speedometer2',     'active'=>request()->routeIs('boss.*')],
        ['route'=>'employees.index',          'label'=>'Employees',         'icon'=>'people',           'active'=>request()->routeIs('employees.*')],
        ['route'=>'storage.index',            'label'=>'Storage',           'icon'=>'box-seam',         'active'=>request()->routeIs('storage.*') || request()->routeIs('product.*')],
        ['route'=>'sales.index',              'label'=>'Sales',             'icon'=>'receipt',          'active'=>request()->routeIs('sales.index') || (request()->routeIs('sales.*') && !request()->routeIs('sales.customers.*'))],
        ['route'=>'wholesale.index',          'label'=>'Wholesale',         'icon'=>'truck',            'active'=>request()->routeIs('wholesale.index') || (request()->routeIs('wholesale.*') && !request()->routeIs('wholesale_storage.*'))],
        ['route'=>'wholesale_storage.index',  'label'=>'Whsl. Storage',     'icon'=>'building-fill',    'active'=>request()->routeIs('wholesale_storage.*')],
        ['route'=>'sales.customers.index',    'label'=>'Customers',         'icon'=>'person-badge',     'active'=>request()->routeIs('sales.customers.*')],
    ];
@endphp
            @foreach($navLinks as $link)
            <li class="nav-item">
                <a class="sidebar-link {{ $link['active'] ? 'active' : '' }}"
                   href="{{ route($link['route']) }}"
                   onclick="closeSidebarOnMobile()">
                    <span class="link-icon-wrap">
                        <i class="bi bi-{{ $link['icon'] }}"></i>
                    </span>
                    <span class="link-label">{{ $link['label'] }}</span>
                    @if($link['active'])<span class="active-pip"></span>@endif
                </a>
            </li>
            @endforeach
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="dropup mb-2">
            <button class="footer-btn w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-translate" style="opacity:0.55;font-size:0.85rem;"></i>
                    <span class="link-label small fw-semibold">
                        @if(app()->getLocale()=='en') English
                        @elseif(app()->getLocale()=='tm') Türkmençe
                        @else Русский @endif
                    </span>
                </div>
                <i class="bi bi-chevron-up link-label" style="font-size:0.6rem;opacity:0.4;"></i>
            </button>
            <ul class="dropdown-menu lang-dropdown border-0 shadow-lg p-2 mb-2">
                <li><a class="dropdown-item lang-item rounded-3 {{ app()->getLocale()=='en'?'active-lang':'' }}" href="{{ route('lang.switch',['locale'=>'en']) }}">
                    <span>🇺🇸</span><span class="small fw-semibold">English</span>
                    @if(app()->getLocale()=='en')<i class="bi bi-check2 ms-auto" style="color:var(--ora);"></i>@endif
                </a></li>
                <li><a class="dropdown-item lang-item rounded-3 {{ app()->getLocale()=='tm'?'active-lang':'' }}" href="{{ route('lang.switch',['locale'=>'tm']) }}">
                    <span>🇹🇲</span><span class="small fw-semibold">Türkmençe</span>
                    @if(app()->getLocale()=='tm')<i class="bi bi-check2 ms-auto" style="color:var(--ora);"></i>@endif
                </a></li>
                <li><a class="dropdown-item lang-item rounded-3 {{ app()->getLocale()=='ru'?'active-lang':'' }}" href="{{ route('lang.switch',['locale'=>'ru']) }}">
                    <span>🇷🇺</span><span class="small fw-semibold">Русский</span>
                    @if(app()->getLocale()=='ru')<i class="bi bi-check2 ms-auto" style="color:var(--ora);"></i>@endif
                </a></li>
            </ul>
        </div>

        <div class="dropup">
            <div class="footer-btn user-btn" data-bs-toggle="dropdown" style="cursor:pointer;">
                <div class="user-avatar">
                    <i class="bi bi-person-fill" style="font-size:0.85rem;color:white;"></i>
                </div>
                <span class="link-label small fw-semibold text-white text-truncate" style="max-width:120px;">
                    {{ auth()->user()->name ?? 'Admin' }}
                </span>
                <i class="bi bi-chevron-up link-label ms-auto" style="font-size:0.6rem;opacity:0.4;"></i>
            </div>
            <ul class="dropdown-menu border-0 shadow-lg p-2 mb-2" style="border-radius:16px;min-width:200px;">
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-3 p-3 rounded-3 text-danger fw-bold" style="font-size:0.85rem;">
                            <i class="bi bi-box-arrow-left"></i> Sign Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</aside>

<button class="sidebar-hamburger" id="sidebarHamburger" aria-label="Open menu">
    <i class="bi bi-list"></i>
</button>
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>

<style>
:root {
    --sidebar-w: 248px;
    --sidebar-collapsed-w: 68px;
    --ora: #E8722A;
    --ora-dark: #C4561A;
    --ora-deeper: #9C3D0E;
    --ora-light: rgba(232,114,42,0.12);
    --ora-glow: rgba(232,114,42,0.35);
    --sidebar-bg1: #1C0D00;
    --sidebar-bg2: #2E1505;
    --sidebar-text: rgba(255,255,255,0.62);
}
*, *::before, *::after { box-sizing: border-box; }

html { height: 100%; margin: 0; padding: 0; overflow: hidden; }
body { height: 100%; margin: 0; padding: 0; font-family: 'Inter', sans-serif; background: #FBF7F3; overflow: hidden; }

body.normal-page-layout { overflow: auto; padding-left: var(--sidebar-w); }
body.normal-page-layout .sidebar-wrapper { position: fixed; top: 0; bottom: 0; left: 0; width: var(--sidebar-w); }
body.desktop-app-mode { padding-left: 0; overflow: hidden; }

.desktop-app-layout { position: fixed; inset: 0; display: flex; flex-direction: row; overflow: hidden; }
.desktop-app-layout .sidebar-wrapper { position: relative !important; flex-shrink: 0; height: 100%; }
.desktop-app-layout .app-main { flex: 1; min-width: 0; display: flex; flex-direction: column; overflow: hidden; height: 100%; }
body.desktop-app-mode .sidebar-hamburger { display: none !important; }
body.desktop-app-mode .sidebar-backdrop { display: none !important; }

/* ── SIDEBAR SHELL ── */
.sidebar-wrapper {
    width: var(--sidebar-w);
    background: linear-gradient(180deg, var(--sidebar-bg1) 0%, var(--sidebar-bg2) 100%);
    display: flex; flex-direction: column;
    z-index: 1050;
    border-right: 1px solid rgba(232,114,42,0.15);
    overflow: hidden;
    transition: width 0.28s ease, transform 0.28s ease;
    position: relative;
}

/* Subtle warm glow on left edge */
.sidebar-wrapper::before {
    content: '';
    position: absolute;
    top: 0; left: 0; bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, transparent, var(--ora), transparent);
    opacity: 0.6;
    pointer-events: none;
}

/* ── BRAND ── */
.sidebar-brand {
    padding: 22px 18px 16px;
    flex-shrink: 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.brand-icon {
    width: 42px; height: 42px;
    background: linear-gradient(135deg, var(--ora), var(--ora-dark));
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 14px var(--ora-glow);
}

/* ── NAV ── */
.sidebar-nav { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 14px 10px; scrollbar-width: none; }
.sidebar-nav::-webkit-scrollbar { display: none; }

.sidebar-link {
    position: relative;
    display: flex; align-items: center; gap: 11px;
    padding: 10px 12px;
    border-radius: 12px;
    color: var(--sidebar-text) !important;
    text-decoration: none;
    font-size: 0.76rem; font-weight: 600;
    white-space: nowrap; overflow: hidden;
    transition: all 0.18s;
    margin-bottom: 2px;
}
.sidebar-link:hover {
    color: rgba(255,255,255,0.9) !important;
    background: rgba(232,114,42,0.1);
    transform: translateX(3px);
}
.sidebar-link.active {
    color: #fff !important;
    background: linear-gradient(90deg, rgba(232,114,42,0.22), rgba(232,114,42,0.08));
    border: 1px solid rgba(232,114,42,0.2);
}
.link-icon-wrap {
    width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 9px;
    background: rgba(255,255,255,0.05);
    flex-shrink: 0;
    font-size: 0.9rem;
    transition: background 0.18s;
}
.sidebar-link.active .link-icon-wrap {
    background: var(--ora);
    color: white;
    box-shadow: 0 3px 10px var(--ora-glow);
}
.sidebar-link:hover .link-icon-wrap { background: rgba(232,114,42,0.18); }

.active-pip {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--ora);
    box-shadow: 0 0 8px var(--ora-glow);
}

/* ── FOOTER ── */
.sidebar-footer { padding: 10px 10px 14px; border-top: 1px solid rgba(255,255,255,0.05); flex-shrink: 0; }
.footer-btn {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    padding: 9px 11px; border-radius: 11px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.07);
    color: var(--sidebar-text); font-size: 0.77rem;
    cursor: pointer; transition: background 0.15s;
    margin-bottom: 6px;
}
.footer-btn:hover { background: rgba(255,255,255,0.09); }
.user-btn { justify-content: flex-start; }
.user-avatar {
    width: 28px; height: 28px;
    background: linear-gradient(135deg, var(--ora), var(--ora-dark));
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 8px var(--ora-glow);
}
.lang-dropdown, .dropdown-menu { border-radius: 16px !important; }
.lang-item { display: flex; align-items: center; gap: 10px; padding: 8px 10px !important; font-size: 0.83rem; }
.active-lang { background: var(--ora-light) !important; }

/* ── CLOSE BTN ── */
.sidebar-close-btn {
    display: none; position: absolute; top: 13px; right: 13px;
    background: rgba(255,255,255,0.1); border: none; color: white;
    width: 32px; height: 32px; border-radius: 9px;
    align-items: center; justify-content: center;
    cursor: pointer; z-index: 10; font-size: 0.85rem;
}

/* ── HAMBURGER ── */
.sidebar-hamburger {
    display: none; position: fixed; top: 13px; left: 13px; z-index: 1200;
    background: var(--ora); border: none; color: white;
    width: 42px; height: 42px; border-radius: 12px; font-size: 1.35rem;
    align-items: center; justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 16px var(--ora-glow);
}
.sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1100; }
.sidebar-backdrop.active { display: block; animation: bdfade 0.2s ease; }
@keyframes bdfade { from { opacity:0; } to { opacity:1; } }

/* ── TABLET ── */
@media (max-width:1023px) and (min-width:768px) {
    .sidebar-wrapper { width: var(--sidebar-collapsed-w) !important; }
    body.normal-page-layout { padding-left: var(--sidebar-collapsed-w); }
    .sidebar-brand-text, .link-label { display: none !important; }
    .sidebar-link { justify-content: center; padding: 11px !important; transform: none !important; }
    .link-icon-wrap { width: 36px; height: 36px; }
    .sidebar-link::after {
        content: attr(data-label);
        position: absolute; left: calc(var(--sidebar-collapsed-w) + 10px);
        background: #1C0D00; color: #fff; border: 1px solid rgba(232,114,42,0.3);
        padding: 5px 12px; border-radius: 9px;
        font-size: 0.72rem; white-space: nowrap;
        opacity: 0; pointer-events: none;
        transition: opacity 0.15s; z-index: 3000;
    }
    .sidebar-link:hover::after { opacity: 1; }
    .sidebar-footer { padding: 8px; }
    .footer-btn { justify-content: center; padding: 10px; }
    .footer-btn .link-label, .footer-btn .bi-chevron-up { display: none !important; }
}

/* ── MOBILE ── */
@media (max-width:767px) {
    body.normal-page-layout { padding-left: 0 !important; overflow: auto; }
    body.desktop-app-mode   { overflow: auto !important; }
    html, body { overflow: auto !important; }
    .desktop-app-layout { position: relative !important; inset: auto !important; flex-direction: column; min-height: 100vh; height: auto !important; overflow: auto !important; }
    .desktop-app-layout .sidebar-wrapper { position: fixed !important; }
    .desktop-app-layout .app-main { height: auto !important; overflow: auto !important; }
    .sidebar-wrapper { position: fixed !important; top: 0; bottom: 0; left: 0; width: 265px !important; height: 100% !important; transform: translateX(-100%); z-index: 1150; }
    .sidebar-wrapper.sidebar-open { transform: translateX(0); box-shadow: 8px 0 40px rgba(0,0,0,0.35); }
    body.desktop-app-mode .sidebar-hamburger { display: flex !important; }
    .sidebar-hamburger { display: flex !important; }
    .sidebar-close-btn  { display: flex !important; }
    .sidebar-brand-text, .link-label { display: block !important; opacity: 1 !important; }
    .sidebar-wrapper.sidebar-open ~ .sidebar-hamburger { display: none !important; }
}
</style>

<script>
(function() {
    window.openSidebar = function() {
        document.getElementById('sidebar-wrapper')?.classList.add('sidebar-open');
        document.getElementById('sidebarBackdrop')?.classList.add('active');
        document.body.style.overflow = 'hidden';
    };
    window.closeSidebar = function() {
        document.getElementById('sidebar-wrapper')?.classList.remove('sidebar-open');
        document.getElementById('sidebarBackdrop')?.classList.remove('active');
        document.body.style.overflow = '';
    };
    window.closeSidebarOnMobile = function() {
        if (window.innerWidth < 768) closeSidebar();
    };
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('sidebarHamburger')?.addEventListener('click', function() {
            const w = document.getElementById('sidebar-wrapper');
            w?.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
        });
        document.getElementById('sidebarCloseBtn')?.addEventListener('click', closeSidebar);
        document.querySelectorAll('.sidebar-link').forEach(function(link) {
            var lbl = link.querySelector('.link-label');
            if (lbl) link.setAttribute('data-label', lbl.textContent.trim());
        });
    });
})();
</script>