@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main">

        <header class="main-header d-print-none">
            <div class="header-info">
                <div class="brand-icon-box">
                    <i class="bi bi-box-seam-fill text-white"></i>
                </div>
                <div class="ms-3">
                    <h4 class="mb-0">{{ __('app.storage_title') }}</h4>
                    <p class="text-muted small mb-0 d-none d-md-block">{{ __('app.storage_subtitle') }}</p>
                </div>
            </div>

            <div class="header-actions ms-auto">
                <a href="{{ route('storage.export', request()->all()) }}" class="btn-export">
                    <i class="bi bi-file-earmark-excel"></i>
                    <span class="d-none d-lg-inline ms-1">{{ __('app.storage_export') }}</span>
                </a>
                <a href="{{ route('product.create') }}" class="btn-add">
                    <i class="bi bi-plus-lg"></i>
                    <span class="btn-label">{{ __('app.storage_add_product') }}</span>
                </a>
            </div>
        </header>

        <div class="workspace">
            <div class="workspace-inner">

                {{-- KPI cards --}}
                <div class="stat-row">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary-soft"><i class="bi bi-archive"></i></div>
                        <div class="stat-data">
                            <div class="stat-label">{{ __('app.storage_total_items') }}</div>
                            <div class="stat-value">{{ $storage->total() }}</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon bg-danger-soft"><i class="bi bi-exclamation-triangle"></i></div>
                        <div class="stat-data">
                            <div class="stat-label">{{ __('app.storage_low_stock') }}</div>
                            <div class="stat-value text-danger">{{ $lowStockCount }}</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon bg-warning-soft"><i class="bi bi-clock-history"></i></div>
                        <div class="stat-data">
                            <div class="stat-label">{{ __('app.storage_expiring_soon') }}</div>
                            <div class="stat-value text-warning">{{ $expirySoonCount }}</div>
                        </div>
                    </div>
                </div>

                {{-- Search & Filters --}}
                <div class="filter-card">
                    <div class="filter-grid">

                        {{-- Live search --}}
                        <div class="search-wrapper">
                            <label class="field-label">{{ __('app.storage_search') }}</label>
                            <div class="search-input-group">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" id="product-search" class="search-input"
                                       placeholder="{{ __('app.storage_search_placeholder') }}" autocomplete="off">
                            </div>
                            <div id="search-results" class="search-dropdown d-none"></div>
                        </div>

                        {{-- Filters --}}
                        <form action="{{ route('storage.index') }}" method="GET" class="filters-form">
                            <div class="filter-field">
                                <label class="field-label">{{ __('app.storage_category_label') }}</label>
                                <select name="category" class="custom-select">
                                    <option value="">{{ __('app.storage_category_all') }}</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" @selected(request('category')==$cat)>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-field">
                                <label class="field-label">{{ __('app.storage_status_label') }}</label>
                                <select name="status" class="custom-select">
                                    <option value="">{{ __('app.storage_status_all') }}</option>
                                    <option value="low"         @selected(request('status')=='low')>{{ __('app.storage_status_low') }}</option>
                                    <option value="expiry_soon" @selected(request('status')=='expiry_soon')>{{ __('app.storage_status_expiry') }}</option>
                                    <option value="expired"     @selected(request('status')=='expired')>{{ __('app.storage_status_expired') }}</option>
                                </select>
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="btn-filter"><i class="bi bi-funnel"></i></button>
                                <a href="{{ route('storage.index') }}" class="btn-reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                            </div>
                        </form>

                    </div>
                </div>

                {{-- Desktop table --}}
                <div class="table-card d-none d-md-block">
                    <div class="table-responsive">
                        <table class="inventory-table w-100">
                            <thead>
                                <tr>
                                    <th class="ps-4">{{ __('app.storage_table_id') }}</th>
                                    <th>{{ __('app.storage_table_product') }}</th>
                                    <th>{{ __('app.storage_table_category') }}</th>
                                    <th class="text-center">{{ __('app.storage_table_quantity') }}</th>
                                    <th class="text-center">{{ __('app.storage_table_status') }}</th>
                                    <th class="text-center">{{ __('app.storage_table_expiry') }}</th>
                                    <th class="text-end pe-4">{{ __('app.storage_table_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($storage as $item)
                                <tr class="clickable-row" onclick="window.location='{{ route('product.show', $item->product_id) }}'">

                                    <td class="ps-4">
                                        <span class="ref-id">#{{ str_pad($item->product_id, 4, '0', STR_PAD_LEFT) }}</span>
                                    </td>

                                    <td>
                                        <div class="fw-bold">{{ $item->product->name ?? '—' }}</div>
                                        <small class="text-muted">{{ $item->product->manufacturer ?? '' }}</small>
                                    </td>

                                    <td>
                                        <span class="category-badge">{{ $item->category ?? '—' }}</span>
                                    </td>

                                    {{-- QUANTITY: item (pcs) or weight (kg) --}}
                                    <td class="text-center">
                                        <div class="qty-display">
                                            <span class="qty-value">{{ $item->display_amount }}</span>
                                            <span class="qty-unit {{ $item->display_unit === 'kg' ? 'unit-weight' : 'unit-item' }}">
                                                {{ $item->display_unit }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="text-center">
                                        <span class="status-pill {{ $item->quantity < 10 ? 'low' : 'ok' }}">
                                            {{ $item->quantity < 10 ? __('app.storage_status_low_text') : __('app.storage_status_ok_text') }}
                                        </span>
                                    </td>

                                    {{-- EXPIRY --}}
                                    <td class="text-center">
                                        @php
                                            $rawDate = $item->expiry_date ?? $item->product->expiry_date ?? null;
                                        @endphp
                                        @if($rawDate)
                                            @php
                                                $expDate  = \Carbon\Carbon::parse($rawDate);
                                                $daysLeft = (int) now()->startOfDay()->diffInDays($expDate->copy()->startOfDay(), false);
                                            @endphp
                                            <div class="expiry-col {{ $daysLeft <= 0 ? 'exp-red' : ($daysLeft <= 30 ? 'exp-orange' : 'exp-green') }}">
                                                <div class="expiry-col-date">{{ $expDate->format('d.m.Y') }}</div>
                                                <div class="expiry-col-sub">
                                                    @if($daysLeft <= 0) {{ __('app.storage_status_expired_text') }}
                                                    @elseif($daysLeft <= 30) {{ __('app.storage_days_left', ['days' => $daysLeft]) }}
                                                    @else {{ $daysLeft }} дн.
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>

                                    {{-- ACTIONS --}}
                                    <td class="text-end pe-4">
                                        <div class="row-actions" onclick="event.stopPropagation()">
                                            <a href="{{ route('storage.edit', $item->id) }}" class="act-btn edit" title="{{ __('app.storage_edit_title') }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('storage.destroy', $item->id) }}" method="POST"
                                                  onsubmit="return confirm('{{ __('app.storage_delete_confirm') }}')" class="m-0">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="act-btn delete" title="{{ __('app.storage_delete_title') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Mobile cards --}}
                <div class="mobile-list d-md-none">
                    @foreach($storage as $item)
                    <div class="mobile-card clickable-row" onclick="window.location='{{ route('product.show', $item->product_id) }}'">
                        <div class="mobile-card-left">
                            <div class="fw-bold">{{ $item->product->name ?? '—' }}</div>
                            <span class="ref-id">#{{ str_pad($item->product_id, 4, '0', STR_PAD_LEFT) }}</span>
                            <div class="mt-1 d-flex flex-wrap gap-1">
                                <span class="category-badge">{{ $item->category ?? '—' }}</span>
                                <span class="status-pill {{ $item->quantity < 10 ? 'low' : 'ok' }}">
                                    {{ $item->quantity < 10 ? 'Мало' : 'Достаточно' }}
                                </span>
                            </div>
                        </div>
                        <div class="mobile-card-right">
                            <div class="qty-display mb-2">
                                <span class="qty-value">{{ $item->display_amount }}</span>
                                <span class="qty-unit {{ $item->display_unit === 'kg' ? 'unit-weight' : 'unit-item' }}">
                                    {{ $item->display_unit }}
                                </span>
                            </div>
                            <div class="row-actions" onclick="event.stopPropagation()">
                                <a href="{{ route('storage.edit', $item->id) }}" class="act-btn edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('storage.destroy', $item->id) }}" method="POST"
                                      onsubmit="return confirm('Удалить?')" class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($storage->hasPages())
                <div class="pagination-wrap">
                    {{ $storage->links('pagination::bootstrap-5') }}
                </div>
                @endif

            </div>
        </div>
    </main>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap');

:root {
    --primary:      #E8722A;
    --primary-dark: #C4561A;
    --primary-soft: rgba(232,114,42,0.10);
    --primary-glow: rgba(232,114,42,0.20);
    --bg:           #FBF7F3;
    --border:       #EDE4DA;
    --text:         #1A0A00;
    --muted:        #8B7355;
}

*, *::before, *::after { box-sizing: border-box; }
body { margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); }

.desktop-app-layout { position: fixed; inset: 0; display: flex; overflow: hidden; }
.desktop-app-layout .sidebar-wrapper { position: relative !important; flex-shrink: 0; height: 100%; }
.app-main { flex: 1; min-width: 0; display: flex; flex-direction: column; overflow: hidden; height: 100%; }

/* ── HEADER ── */
.main-header {
    height: 70px; background: white; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; padding: 0 24px; gap: 16px; flex-shrink: 0;
}
.header-info { display: flex; align-items: center; }
.brand-icon-box {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border-radius: 12px; display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; box-shadow: 0 6px 14px var(--primary-glow); flex-shrink: 0;
}
.main-header h4 { font-family: 'Sora', sans-serif; font-weight: 800; font-size: 1rem; color: var(--text); }
.header-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

.btn-export {
    height: 40px; padding: 0 16px; border-radius: 11px;
    background: #E8F5E9; color: #2E7D32; border: 1.5px solid #C8E6C9;
    font-weight: 700; font-size: 0.82rem;
    display: flex; align-items: center; text-decoration: none; transition: 0.18s;
}
.btn-export:hover { background: #2E7D32; color: white; }
.btn-add {
    height: 40px; padding: 0 18px; border-radius: 11px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; border: none; font-weight: 700; font-size: 0.85rem;
    display: flex; align-items: center; gap: 6px; text-decoration: none;
    transition: 0.2s; white-space: nowrap; box-shadow: 0 4px 12px var(--primary-glow);
}
.btn-add:hover { transform: translateY(-1px); box-shadow: 0 6px 18px var(--primary-glow); color: white; }

/* ── WORKSPACE ── */
.workspace { flex: 1; overflow-y: auto; }
.workspace-inner { padding: 20px 24px; max-width: 1400px; margin: 0 auto; }
.workspace::-webkit-scrollbar { width: 5px; }
.workspace::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

/* ── STAT CARDS ── */
.stat-row { display: flex; gap: 14px; margin-bottom: 18px; }
.stat-card {
    background: white; border-radius: 18px; padding: 18px 22px;
    display: flex; align-items: center; gap: 14px; flex: 1;
    box-shadow: 0 2px 10px rgba(26,10,0,0.04); border: 1px solid var(--border);
}
.stat-icon {
    width: 46px; height: 46px; border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.bg-primary-soft { background: var(--primary-soft); color: var(--primary); }
.bg-danger-soft  { background: rgba(220,53,69,0.1);  color: #dc3545; }
.bg-warning-soft { background: rgba(245,158,11,0.1); color: #d97706; }
.stat-label { font-size: 0.6rem; text-transform: uppercase; font-weight: 800; color: var(--muted); margin-bottom: 2px; letter-spacing: 0.4px; }
.stat-value { font-family: 'Sora', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); line-height: 1; }
.text-danger  { color: #dc3545 !important; }
.text-warning { color: #d97706 !important; }

/* ── FILTER CARD ── */
.filter-card {
    background: white; border-radius: 18px; padding: 18px 22px;
    box-shadow: 0 2px 10px rgba(26,10,0,0.04); border: 1px solid var(--border); margin-bottom: 18px;
}
.filter-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 18px; align-items: end; }
.field-label {
    display: block; font-size: 0.6rem; text-transform: uppercase;
    font-weight: 800; color: var(--primary); letter-spacing: 0.5px; margin-bottom: 5px;
}
.search-wrapper { position: relative; }
.search-input-group {
    display: flex; align-items: center; background: var(--bg);
    border: 1.5px solid var(--border); border-radius: 11px; overflow: hidden; transition: 0.2s;
}
.search-input-group:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); background: white; }
.search-icon { padding: 0 12px; color: var(--muted); font-size: 0.9rem; }
.search-input { flex: 1; border: none; background: transparent; padding: 10px 12px 10px 0; font-size: 0.9rem; outline: none; }
.filters-form { display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: end; }
.filter-actions { display: flex; gap: 8px; }
.custom-select {
    border: 1.5px solid var(--border); border-radius: 11px; padding: 9px 14px;
    background: var(--bg); font-size: 0.875rem; color: var(--text); width: 100%; outline: none; transition: 0.2s;
}
.custom-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); background: white; }
.btn-filter, .btn-reset {
    height: 40px; width: 42px; border-radius: 10px; border: none;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.95rem; cursor: pointer; transition: 0.2s; text-decoration: none;
}
.btn-filter { background: var(--primary); color: white; box-shadow: 0 4px 10px var(--primary-glow); }
.btn-filter:hover { background: var(--primary-dark); }
.btn-reset { background: #F5EDE4; color: var(--muted); border: 1.5px solid var(--border); }
.btn-reset:hover { background: var(--border); }

/* ── TABLE ── */
.table-card {
    background: white; border-radius: 18px; overflow: hidden;
    box-shadow: 0 2px 10px rgba(26,10,0,0.04); border: 1px solid var(--border); margin-bottom: 18px;
}
.inventory-table { border-collapse: collapse; }
.inventory-table thead th {
    background: var(--bg); color: var(--muted); text-transform: uppercase;
    font-size: 0.65rem; font-weight: 800; padding: 14px 16px;
    border-bottom: 1px solid var(--border); white-space: nowrap; letter-spacing: 0.4px;
}
.inventory-table tbody tr { transition: background 0.15s; }
.inventory-table tbody tr:hover { background: #FFF8F3; }
.inventory-table td { border-bottom: 1px solid #F5EDE4; padding: 13px 16px; font-size: 0.875rem; }
.inventory-table tbody tr:last-child td { border-bottom: none; }
.clickable-row { cursor: pointer; }
.ref-id { font-size: 0.72rem; font-weight: 800; color: #C4B4A0; font-family: monospace; }
.category-badge {
    display: inline-block; background: var(--primary-soft); color: var(--primary);
    font-size: 0.65rem; font-weight: 800; padding: 3px 10px; border-radius: 20px;
    border: 1px solid rgba(232,114,42,0.2);
}

/* ── QUANTITY (item / weight) ── */
.qty-display { display: flex; align-items: baseline; gap: 5px; justify-content: center; }
.qty-value { font-family: 'Sora', sans-serif; font-weight: 800; font-size: 1.05rem; color: var(--text); }
.qty-unit {
    font-size: 0.68rem; font-weight: 800; text-transform: uppercase;
    padding: 2px 8px; border-radius: 5px; letter-spacing: 0.3px;
}
.unit-item   { background: var(--primary-soft); color: var(--primary); }
.unit-weight { background: rgba(59,130,246,0.1); color: #3b82f6; }

/* ── STATUS / EXPIRY ── */
.status-pill { display: inline-block; padding: 4px 11px; border-radius: 50px; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; }
.status-pill.ok  { background: rgba(16,185,129,0.1); color: #10b981; }
.status-pill.low { background: rgba(239,68,68,0.1);  color: #ef4444; }

.expiry-col {
    display: inline-flex; flex-direction: column; align-items: center;
    padding: 5px 12px; border-radius: 10px; border: 1px solid transparent; min-width: 88px;
}
.expiry-col-date { font-size: 0.78rem; font-weight: 700; font-family: monospace; }
.expiry-col-sub  { font-size: 0.6rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px; margin-top: 1px; }
.exp-green  { background: rgba(16,185,129,0.08); color: #059669; border-color: rgba(16,185,129,0.2); }
.exp-orange { background: rgba(245,158,11,0.1);  color: #b45309; border-color: rgba(245,158,11,0.25); animation: pulse 2s infinite; }
.exp-red    { background: rgba(239,68,68,0.08);  color: #dc2626; border-color: rgba(239,68,68,0.2); }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }

/* ── ACTIONS ── */
.row-actions { display: flex; gap: 6px; justify-content: flex-end; }
.act-btn {
    width: 34px; height: 34px; border-radius: 9px; border: none;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.85rem; cursor: pointer; transition: 0.15s; text-decoration: none;
}
.act-btn.edit   { background: var(--primary-soft); color: var(--primary); }
.act-btn.delete { background: rgba(239,68,68,0.1); color: #ef4444; }
.act-btn:hover  { transform: scale(1.1); }

/* ── SEARCH DROPDOWN ── */
.search-dropdown {
    position: absolute; width: 100%; top: calc(100% + 8px); left: 0;
    background: white; border-radius: 14px; z-index: 1000; overflow: hidden;
    border: 1px solid var(--border); box-shadow: 0 12px 30px rgba(26,10,0,0.1);
}
.search-result-item {
    width: 100%; background: white; border: none; cursor: pointer;
    display: flex; justify-content: space-between; align-items: center;
    padding: 12px 16px; transition: 0.15s; text-align: left;
}
.search-result-item:hover { background: var(--primary-soft); }
.result-name { font-weight: 700; font-size: 0.875rem; color: var(--text); }
.result-price { background: var(--primary); color: white; padding: 3px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }

/* ── MOBILE ── */
.mobile-list { margin-bottom: 18px; }
.mobile-card {
    background: white; border-radius: 16px; padding: 14px 16px;
    display: flex; justify-content: space-between; align-items: center;
    gap: 12px; margin-bottom: 10px; border: 1px solid var(--border);
    box-shadow: 0 2px 8px rgba(26,10,0,0.03);
}
.mobile-card-left { min-width: 0; flex: 1; font-size: 0.875rem; }
.mobile-card-right { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; flex-shrink: 0; }

/* ── PAGINATION ── */
.pagination-wrap { display: flex; justify-content: center; padding: 8px 0 24px; }
.pagination-wrap .page-link {
    border-radius: 50% !important; margin: 0 3px; width: 38px; height: 38px;
    display: flex; align-items: center; justify-content: center;
    color: var(--primary); border: 1.5px solid var(--border); background: white; font-weight: 700;
}
.pagination-wrap .page-item.active .page-link { background: var(--primary) !important; border-color: var(--primary); color: white; }
.pagination-wrap .page-link:hover { background: var(--primary-soft); }

/* ── RESPONSIVE ── */
@media (max-width: 1023px) {
    .filter-grid { grid-template-columns: 1fr; gap: 14px; }
    .filters-form { grid-template-columns: 1fr 1fr auto; }
    .workspace-inner { padding: 16px; }
}
@media (max-width: 900px) {
    .filters-form { grid-template-columns: 1fr; }
    .filter-actions { justify-content: flex-start; }
}
@media (max-width: 767px) {
    .desktop-app-layout { position: relative !important; inset: auto !important; min-height: 100vh; height: auto !important; flex-direction: column; overflow: auto !important; }
    .desktop-app-layout .sidebar-wrapper { position: fixed !important; }
    .app-main { height: auto !important; overflow: auto !important; }
    .main-header { padding: 0 14px 0 68px; height: 60px; }
    .btn-label { display: none; }
    .btn-add { padding: 0 12px; }
    .workspace { overflow: visible; }
    .workspace-inner { padding: 12px; }
    .stat-row { gap: 10px; flex-wrap: wrap; }
    .stat-card { padding: 14px 16px; min-width: calc(50% - 5px); }
    .stat-value { font-size: 1.2rem; }
    .filter-card { padding: 14px; }
    .filter-grid { grid-template-columns: 1fr; gap: 12px; }
    .filters-form { grid-template-columns: 1fr 1fr; gap: 10px; }
    .filter-actions { grid-column: 1 / -1; }
}
@media print {
    .sidebar-wrapper, .main-header, .filter-card { display: none !important; }
    .desktop-app-layout { display: block; }
    .app-main { overflow: visible; }
    body { background: white; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input   = document.getElementById('product-search');
    const results = document.getElementById('search-results');
    if (!input) return;

    input.addEventListener('input', function () {
        const query = this.value.trim();
        if (query.length < 2) { results.classList.add('d-none'); return; }

        fetch(`/admin/product/search?search=${encodeURIComponent(query)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            results.innerHTML = '';
            if (data.length > 0) {
                results.classList.remove('d-none');
                data.forEach(item => {
                    const btn = document.createElement('button');
                    btn.className = 'search-result-item';
                    btn.innerHTML = `<span class="result-name">${item.name}</span>
                                     <span class="result-price">${item.price} TMT</span>`;
                    btn.addEventListener('click', () => {
                        window.location.href = `/admin/inventory/${item.id}/edit`;
                    });
                    results.appendChild(btn);
                });
            } else {
                results.classList.add('d-none');
            }
        })
        .catch(() => results.classList.add('d-none'));
    });

    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.classList.add('d-none');
        }
    });
});
</script>
@endsection
