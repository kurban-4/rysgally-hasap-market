@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main">

        {{-- ══ PAGE HEADER ══ --}}
        <header class="main-header">
            <div class="header-info">
                <i class="bi bi-clock-history text-light me-2 fs-5"></i>
                <div class="ms-1">
                    <h4 class="mb-0 fw-black">{{ __('app.customers_title') }}</h4>
                    <p class="text-muted small mb-0 d-none d-md-block">
                        {{ __('app.customers_subtitle') }}
                    </p>
                </div>
            </div>

            <div class="header-stats ms-auto">
                <div class="mini-stat">
                    <span class="mini-label">{{ __('app.customers_orders') }}</span>
                    <span class="mini-value">{{ count($orders) }}</span>
                </div>
                <div class="mini-stat d-none d-md-flex">
                    <span class="mini-label">{{ __('app.customers_revenue') }}</span>
                    <span class="mini-value orange">
                        {{ number_format($orders->sum('total_sum'), 2) }}
                        <small>TMT</small>
                    </span>
                </div>
            </div>

            <div class="system-status">
                <span class="dot-pulse"></span>
                <span class="d-none d-lg-inline fw-bold" style="font-size:.7rem;color:#4ADE80;">{{ __('app.customers_live') }}</span>
            </div>

            <a href="{{ route('sales.customers.export.all') }}"
               class="btn-export d-none d-md-flex">
                <i class="bi bi-file-earmark-excel-fill"></i>
                <span>Export</span>
            </a>
        </header>

        {{-- ══ WORKSPACE ══ --}}
        <div class="workspace">
            <div class="orders-container">
                <div class="panel-card">

                    <div class="panel-header">
                        <h5 class="mb-0 fw-black">
                            <i class="bi bi-list-ul me-2 text-orange"></i>
                            {{ __('app.customers_log_heading') }}
                        </h5>
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchInput"
                                   placeholder="{{ __('app.customers_search_placeholder') }}"
                                   class="search-input">
                        </div>
                    </div>

                    {{-- Mobile cards --}}
                    <div class="orders-card-list d-md-none">
                        @forelse($orders as $order)
                        <div class="order-mobile-card">
                            <div class="order-mobile-left">
                                <div class="txn-id">
                                    <span class="hash">#</span>{{ ltrim($order->transaction_id, '#') }}
                                </div>
                                <div class="order-time">
                                    {{ \Carbon\Carbon::parse($order->order_time)->format('H:i · d M Y') }}
                                </div>
                            </div>
                            <div class="order-mobile-right">
                                <div class="fw-black text-orange">
                                    {{ number_format($order->total_sum, 2) }}
                                    <small>TMT</small>
                                </div>
                                <span class="badge-done">Выполнено</span>
                                <div class="mt-2">
                                    <a href="{{ route('sales.customers.show', ['transaction_id' => ltrim($order->transaction_id, '#')]) }}"
                                       class="btn-detail">
                                        {{ __('app.customers_link_details') }} <i class="bi bi-arrow-right-short"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="empty-state">
                            <i class="bi bi-folder-x"></i>
                            <p>{{ __('app.customers_no_data') }}</p>
                        </div>
                        @endforelse
                    </div>

                    {{-- Desktop table --}}
                    <div class="table-scroll-container d-none d-md-block">
                        <table class="table pos-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">{{ __('app.customers_table_id') }}</th>
                                    <th>{{ __('app.customers_table_date') }}</th>
                                    <th class="text-center">{{ __('app.customers_table_status') }}</th>
                                    <th class="text-end">{{ __('app.customers_table_amount') }}</th>
                                    <th class="text-center pe-3">{{ __('app.customers_table_action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                @forelse($orders as $order)
                                <tr>
                                    <td class="ps-4">
                                        <span class="transaction-id">
                                            <span class="hash">#</span>{{ ltrim($order->transaction_id, '#') }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($order->order_time)->format('H:i · d M Y') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-done">{{ __('app.customers_status_completed') }}</span>
                                    </td>
                                    <td class="text-center fw-black text-orange">
                                        {{ number_format($order->total_sum, 2) }}
                                        <small class="text-muted fw-normal">TMT</small>
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="{{ route('sales.customers.show', ['transaction_id' => ltrim($order->transaction_id, '#')]) }}"
                                           class="btn-detail">
                                            {{ __('app.customers_link_details') }} <i class="bi bi-arrow-right-short"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        {{ __('app.customers_no_data') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('#ordersTableBody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
            document.querySelectorAll('.order-mobile-card').forEach(card => {
                card.style.display = card.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        });
    }
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap');

:root {
    --ora: #E8722A;
    --ora-dark: #C4561A;
    --ora-light: #FFF0E6;
    --ora-glow: rgba(232,114,42,0.2);
    --bg: #FBF7F3;
    --card: #FFFFFF;
    --border: #EDE4DA;
    --text: #1A0A00;
    --muted: #8B7355;
    --shadow: 0 2px 14px rgba(26,10,0,0.06);
}
*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'DM Sans', sans-serif; background: var(--bg); }

/* LAYOUT */
.desktop-app-layout { position: fixed; inset: 0; display: flex; overflow: hidden; }
.desktop-app-layout .sidebar-wrapper { position: relative !important; flex-shrink: 0; height: 100%; }
.app-main { flex: 1; min-width: 0; display: flex; flex-direction: column; overflow: hidden; height: 100%; }

/* HEADER */
.main-header {
    height: 70px; background: var(--card);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; padding: 0 24px; gap: 16px; flex-shrink: 0;
}
.header-info { display: flex; align-items: center; }
.fw-black { font-family: 'Sora', sans-serif; font-weight: 800; color: var(--text); }

.header-stats { display: flex; gap: 20px; }
.mini-stat { display: flex; flex-direction: column; border-left: 2px solid var(--border); padding-left: 16px; }
.mini-label { font-size: 0.6rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.8px; color: var(--muted); }
.mini-value { font-family: 'Sora', sans-serif; font-size: 1.05rem; font-weight: 800; color: var(--text); }
.mini-value.orange { color: var(--ora); }
.mini-value small { font-size: 0.62rem; font-weight: 600; color: var(--muted); }

.system-status { display: flex; align-items: center; gap: 7px; }
.dot-pulse {
    width: 8px; height: 8px; border-radius: 50%;
    background: #4ADE80;
    animation: pulse-green 2s infinite;
}
@keyframes pulse-green {
    0%   { box-shadow: 0 0 0 0 rgba(74,222,128,0.7); }
    70%  { box-shadow: 0 0 0 6px rgba(74,222,128,0); }
    100% { box-shadow: 0 0 0 0 rgba(74,222,128,0); }
}

.btn-export {
    display: flex; align-items: center; gap: 7px;
    background: var(--ora-light); color: var(--ora);
    border: 1.5px solid rgba(232,114,42,0.25); border-radius: 11px;
    padding: 8px 14px; font-size: 0.78rem; font-weight: 700;
    text-decoration: none; transition: 0.18s; white-space: nowrap;
}
.btn-export:hover { background: var(--ora); color: white; }

/* WORKSPACE */
.workspace { flex: 1; overflow-y: auto; padding: 20px 24px; }
.orders-container { max-width: 1200px; margin: 0 auto; height: 100%; }

/* PANEL CARD */
.panel-card {
    background: var(--card); border-radius: 20px;
    border: 1px solid var(--border); box-shadow: var(--shadow);
    overflow: hidden;
}
.panel-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 18px 22px; border-bottom: 1px solid var(--border); flex-wrap: wrap; gap: 12px;
}
.text-orange { color: var(--ora) !important; }

/* SEARCH */
.search-box {
    position: relative; display: flex; align-items: center;
}
.search-box i {
    position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
    color: var(--muted); font-size: 0.85rem; pointer-events: none;
}
.search-input {
    padding: 8px 14px 8px 34px; border-radius: 11px;
    border: 1.5px solid var(--border); background: #FDFAF7;
    font-size: 0.83rem; color: var(--text); outline: none; width: 220px;
    font-family: 'DM Sans', sans-serif; transition: 0.18s;
}
.search-input:focus { border-color: var(--ora); box-shadow: 0 0 0 3px var(--ora-glow); background: white; }

/* TABLE */
.table-scroll-container { overflow-x: auto; }
.pos-table { width: 100%; border-collapse: collapse; }
.pos-table thead th {
    background: #FBF7F3; color: var(--muted);
    font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 800;
    padding: 14px 16px; border-bottom: 1px solid var(--border);
    white-space: nowrap; position: sticky; top: 0; z-index: 2;
}
.pos-table tbody tr { transition: background 0.18s; }
.pos-table tbody tr:hover { background: #FFF8F3; }
.pos-table tbody td {
    padding: 14px 16px; border-bottom: 1px solid #F5EDE4;
    font-size: 0.88rem; color: var(--text);
}
.pos-table tbody tr:last-child td { border-bottom: none; }

.transaction-id { font-family: 'JetBrains Mono', monospace; font-size: 0.82rem; font-weight: 600; color: #6B4E2A; }
.hash { color: var(--ora); font-weight: 800; margin-right: 2px; }

.badge-done {
    display: inline-flex; align-items: center;
    background: #E8F5E9; color: #2E7D32;
    font-size: 0.68rem; font-weight: 800; padding: 4px 10px; border-radius: 50px;
}

.btn-detail {
    display: inline-flex; align-items: center; gap: 4px;
    background: var(--ora-light); color: var(--ora);
    border: 1.5px solid rgba(232,114,42,0.2); border-radius: 9px;
    padding: 6px 12px; font-size: 0.78rem; font-weight: 700;
    text-decoration: none; transition: 0.15s; white-space: nowrap;
}
.btn-detail:hover { background: var(--ora); color: white; }

/* MOBILE CARDS */
.orders-card-list { padding: 10px; }
.order-mobile-card {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 14px 14px; border-bottom: 1px solid var(--border); gap: 12px;
}
.order-mobile-card:last-child { border-bottom: none; }
.txn-id { font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; font-weight: 700; color: #6B4E2A; }
.order-time { font-size: 0.72rem; color: var(--muted); margin-top: 3px; }
.order-mobile-right { text-align: right; }
.order-mobile-right .fw-black { font-size: 1rem; }

/* EMPTY */
.empty-state { text-align: center; padding: 48px 20px; color: var(--muted); }
.empty-state i { font-size: 2.2rem; opacity: 0.3; display: block; margin-bottom: 10px; }
.empty-state p { font-weight: 700; margin: 0; }

/* SCROLLBAR */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-thumb { background: #D4C4B0; border-radius: 10px; }
::-webkit-scrollbar-track { background: transparent; }

/* RESPONSIVE */
@media (max-width: 1023px) { .main-header { padding: 0 16px; height: 70px; } }
@media (max-width: 767px) {
    .desktop-app-layout { position: relative; inset: auto; flex-direction: column; min-height: 100vh; height: auto !important; overflow: auto !important; }
    .desktop-app-layout .sidebar-wrapper { position: fixed !important; }
    .app-main { height: auto !important; overflow: auto !important; }
    .main-header { padding: 0 14px 0 68px; height: 60px; }
    .workspace { padding: 12px; overflow: visible; }
    .orders-container { height: auto; }
}
</style>
@endsection
