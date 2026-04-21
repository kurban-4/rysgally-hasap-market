@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main">
        @php
            $isWeightProduct = ($product->unit_type ?? 'piece') === 'weight';
            $effDiscount = $storage ? (int) ($storage->discount ?? 0) : (int) ($product->discount ?? 0);
            $listPrice = (float) ($storage?->selling_price ?? $product->price ?? 0);
            $finalPrice = $effDiscount > 0 ? round($listPrice * (1 - $effDiscount / 100), 2) : $listPrice;
            $marketCodeDisplay = $isWeightProduct
                ? trim((string) ($product->product_code ?? ($storage?->barcode ?? '')))
                : trim((string) ($storage?->barcode ?? ($product->barcode ?? '')));
        @endphp

        <header class="page-header">
            <a href="{{ route('storage.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="header-info">
                <div class="header-eyebrow">
                    <span class="type-tag type-{{ $product->unit_type ?? 'box' }}">
    @if(($product->unit_type ?? 'box') === 'weight') 
        <i class="bi bi-speedometer2 me-1"></i>Weighable
    @elseif(($product->unit_type ?? 'box') === 'piece') {{-- Исправлено с unit на piece --}}
        <i class="bi bi-123 me-1"></i>Unit
    @else 
        <i class="bi bi-box-seam me-1"></i>Boxed
    @endif
</span>
                    <span class="header-cat">{{ $product->category }}</span>
                </div>
                <h4 class="header-title">{{ $product->name }}</h4>
            </div>
            <div class="ms-auto d-flex gap-2">
                @if($storage)
                <a href="{{ route('storage.edit', $storage->id) }}" class="btn-edit">
                    <i class="bi bi-pencil-square me-1"></i>
                    <span>{{ __('app.btn_edit_product') }}</span>
                </a>
                @endif
            </div>
        </header>

        <div class="workspace custom-scrollbar">
            <div class="detail-wrap">

                {{-- ══ HERO BANNER ══ --}}
                <div class="hero-banner">
                    <div class="hero-left">
                        <div class="hero-icon">
                            @if(($product->unit_type??'box')==='weight')
                                <i class="bi bi-speedometer2"></i>
                            @elseif(($product->unit_type??'box')==='piece')
                                <i class="bi bi-123"></i>
                            @else
                                <i class="bi bi-box-seam"></i>
                            @endif
                        </div>
                        <div>
                            <div class="hero-name">{{ $product->name }}</div>
                            <div class="hero-meta">
                                ID #{{ str_pad($product->id, 5, '0', STR_PAD_LEFT) }}
                                @if($marketCodeDisplay !== '')
                                    · <span class="text-muted">{{ $isWeightProduct ? 'Code' : 'Barcode' }}</span> {{ $marketCodeDisplay }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="hero-price-block">
                        @if($effDiscount > 0)
                            <div class="price-was">${{ number_format($listPrice, 2) }}</div>
                        @endif
                        <div class="price-now">
                            ${{ number_format($finalPrice, 2) }}
                        </div>
                        <div class="price-per">per {{ $product->unit_label ?? 'piece' }}</div>
                        @if($effDiscount > 0)
                            <div class="discount-pill">{{ $effDiscount }}% OFF</div>
                        @endif
                    </div>

                    {{-- Decorative --}}
                    <div class="hero-deco"></div>
                </div>

                {{-- ══ MAIN GRID ══ --}}
                <div class="detail-grid">

                    {{-- LEFT --}}
                    <div class="detail-left">

                        {{-- STOCK HIGHLIGHT --}}
                        <div class="detail-card stock-card">
                            <div class="stock-inner">
                                <div class="stock-ring">
                                    @php
    $qty = $storage?->quantity ?? 0;
    // Fix: cast to float to strip trailing zeros
    $cleanQty = ($product->unit_type ?? 'piece') === 'weight' ? (float)$qty : (int)$qty;
    $displayQty = $cleanQty . ' ' . ($product->unit_label ?? ($product->unit_type === 'weight' ? 'kg' : 'pcs'));
    $isLow = $qty < 10;
@endphp
                                    <div class="ring-number">
    {{ $cleanQty }}
</div>
                                    <div class="ring-unit">{{ $product->unit_label ?? 'pcs' }}</div>
                                </div>
                                <div class="stock-info">
                                    <div class="stock-label">In Stock</div>
                                    <div class="stock-display">{{ $displayQty }}</div>
                                    <div class="stock-status {{ $isLow ? 'low' : 'ok' }}">
                                        <i class="bi bi-{{ $isLow ? 'exclamation-triangle-fill' : 'check-circle-fill' }}"></i>
                                        {{ $isLow ? 'Low Stock' : 'Sufficient' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- DESCRIPTION --}}
                        @if($product->description)
                        <div class="detail-card">
                            <div class="card-head">
                                <div class="card-head-icon"><i class="bi bi-file-text"></i></div>
                                Description
                            </div>
                            <p class="desc-text">{{ $product->description }}</p>
                        </div>
                        @endif

                        {{-- DETAILS GRID --}}
<div class="detail-card">
    <div class="card-head">
        <div class="card-head-icon"><i class="bi bi-info-circle"></i></div>
        Details
    </div>
    <div class="meta-grid">
        <div class="meta-item">
            <div class="meta-icon"><i class="bi bi-building"></i></div>
            <div><div class="meta-label">Manufacturer</div><div class="meta-val">{{ $product->manufacturer ?: '—' }}</div></div>
        </div>
        
        @if($isWeightProduct)
        <div class="meta-item">
            <div class="meta-icon"><i class="bi bi-tag"></i></div>
            <div>
                <div class="meta-label">Product code</div>
                <div class="meta-val mono"><strong>{{ $marketCodeDisplay !== '' ? $marketCodeDisplay : '—' }}</strong></div>
            </div>
        </div>
        @else
        <div class="meta-item">
            <div class="meta-icon"><i class="bi bi-upc-scan"></i></div>
            <div><div class="meta-label">Barcode</div><div class="meta-val mono">{{ $marketCodeDisplay !== '' ? $marketCodeDisplay : '—' }}</div></div>
        </div>
        @endif

        <div class="meta-item">
            <div class="meta-icon"><i class="bi bi-tag"></i></div>
            <div><div class="meta-label">Selling Price</div><div class="meta-val">${{ number_format($storage?->selling_price ?? $product->price ?? 0, 2) }}</div></div>
        </div>
        <div class="meta-item">
            <div class="meta-icon"><i class="bi bi-cash-coin"></i></div>
            <div><div class="meta-label">Purchase Price</div><div class="meta-val">${{ number_format($storage?->received_price ?? $product->received_price ?? 0, 2) }}</div></div>
        </div>
        @if($storage && $storage->batch_number)
        <div class="meta-item">
            <div class="meta-icon"><i class="bi bi-hash"></i></div>
            <div><div class="meta-label">Batch No.</div><div class="meta-val mono">{{ $storage->batch_number }}</div></div>
        </div>
        @endif
    </div>
</div>  {{-- closes detail-card (Details) --}}

    </div> {{-- closes detail-left --}}

                    {{-- RIGHT --}}
                    <div class="detail-right">

                        {{-- DATES --}}
                        <div class="detail-card">
                            <div class="card-head">
                                <div class="card-head-icon"><i class="bi bi-calendar3"></i></div>
                                Dates
                            </div>
                            <div class="date-row">
                                <div class="date-icon green"><i class="bi bi-calendar-check"></i></div>
                                <div>
                                    <div class="date-label">{{ __('app.label_production_date_short') }}</div>
                                    <div class="date-val">{{ $product->produced_date ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="date-row mt-3">
                                <div class="date-icon red"><i class="bi bi-calendar-x"></i></div>
                                <div>
                                    <div class="date-label">{{ __('app.label_expiry_date_short') }}</div>
                                    @php
                                        $expiry = $storage?->expiry_date ?? $product->expiry_date ?? null;
                                        $isExpired = $expiry && \Carbon\Carbon::parse($expiry)->isPast();
                                        $isExpiringSoon = $expiry && !$isExpired && \Carbon\Carbon::parse($expiry)->diffInDays(now()) <= 30;
                                    @endphp
                                    <div class="date-val {{ $isExpired ? 'danger' : ($isExpiringSoon ? 'warn' : '') }}">
                                        {{ $expiry ? \Carbon\Carbon::parse($expiry)->format('d M Y') : '—' }}
                                        @if($isExpired) <span class="expired-badge">Expired</span>
                                        @elseif($isExpiringSoon) <span class="expiring-badge">Soon</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- QUICK STATS --}}
                        <div class="detail-card stats-card">
                            <div class="card-head">
                                <div class="card-head-icon"><i class="bi bi-graph-up"></i></div>
                                Quick Stats
                            </div>
                            <div class="stat-rows">
                                <div class="stat-row">
                                    <span class="stat-label">Category</span>
                                    <span class="stat-val">{{ $product->category ?? '—' }}</span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">Item type</span>
                                    <span class="stat-val capitalize">{{ $product->unit_type ?? 'box' }}</span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">Unit</span>
                                    <span class="stat-val">{{ $product->unit_label ?? 'pcs' }}</span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">Discount</span>
                                    <span class="stat-val {{ $effDiscount > 0 ? 'orange' : '' }}">
                                        {{ $effDiscount > 0 ? $effDiscount.'%' : '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- ACTIONS --}}
                        @if($storage)
                        <a href="{{ route('storage.edit', $storage->id) }}" class="btn-action primary">
                            <i class="bi bi-pencil-square"></i>
                            <span>{{ __('app.btn_edit_product') }}</span>
                        </a>

                        <form action="{{ route('storage.destroy', $storage->id) }}" method="POST"
                              onsubmit="return confirm('Delete this item from storage?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-action danger">
                                <i class="bi bi-trash3"></i>
                                <span>{{ __('app.btn_remove_product') }}</span>
                            </button>
                        </form>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap');

:root {
    --ora: #E8722A;
    --ora-dark: #C4561A;
    --ora-light: #FFF0E6;
    --ora-glow: rgba(232,114,42,0.25);
    --green: #2E7D32;
    --green-light: #E8F5E9;
    --red: #C62828;
    --red-light: #FFEBEE;
    --amber: #F9A825;
    --bg: #FBF7F3;
    --card: #FFFFFF;
    --border: #EDE4DA;
    --text: #1A0A00;
    --muted: #8B7355;
    --shadow: 0 2px 14px rgba(26,10,0,0.06);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'DM Sans', sans-serif; background: var(--bg); }

/* LAYOUT */
.desktop-app-layout { position: fixed; inset: 0; display: flex; overflow: hidden; }
.desktop-app-layout .sidebar-wrapper { position: relative !important; flex-shrink: 0; height: 100%; }
.app-main { flex: 1; min-width: 0; display: flex; flex-direction: column; overflow: hidden; height: 100%; }

/* HEADER */
.page-header {
    height: 70px; background: var(--card);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; padding: 0 24px; gap: 14px; flex-shrink: 0;
}
.btn-back {
    width: 38px; height: 38px; min-width: 38px; border-radius: 11px;
    background: var(--ora-light); color: var(--ora); border: none;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; font-size: 1rem; transition: 0.18s; cursor: pointer;
}
.btn-back:hover { background: var(--ora); color: white; transform: translateX(-2px); }
.header-info { display: flex; flex-direction: column; gap: 2px; }
.header-eyebrow { display: flex; align-items: center; gap: 8px; }
.type-tag {
    display: inline-flex; align-items: center;
    font-size: 0.62rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px;
    padding: 3px 9px; border-radius: 50px;
}
.type-box    { background: var(--ora-light); color: var(--ora); }
.type-weight { background: #E3F2FD; color: #1565C0; }
.type-unit   { background: var(--green-light); color: var(--green); }
.header-cat { font-size: 0.72rem; color: var(--muted); font-weight: 600; }
.header-title { font-family: 'Sora', sans-serif; font-size: 1.05rem; font-weight: 700; color: var(--text); }
.btn-edit {
    display: flex; align-items: center; gap: 7px;
    background: var(--ora); color: white; border: none;
    border-radius: 11px; padding: 9px 16px;
    font-size: 0.82rem; font-weight: 700; text-decoration: none;
    transition: 0.18s; white-space: nowrap;
    box-shadow: 0 4px 12px var(--ora-glow);
}
.btn-edit:hover { background: var(--ora-dark); color: white; transform: translateY(-1px); }

/* WORKSPACE */
.workspace { flex: 1; overflow-y: auto; padding: 24px; }
.detail-wrap { max-width: 1120px; margin: 0 auto; }

/* HERO BANNER */
.hero-banner {
    background: linear-gradient(135deg, #1A0A00 0%, #2E1100 50%, #3D1A00 100%);
    border-radius: 22px;
    padding: 32px 36px;
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 22px;
    position: relative; overflow: hidden;
    box-shadow: 0 12px 40px rgba(26,10,0,0.2);
}
.hero-deco {
    position: absolute; bottom: -60px; right: -40px;
    width: 220px; height: 220px; border-radius: 50%;
    background: radial-gradient(circle, rgba(232,114,42,0.15), transparent 70%);
    pointer-events: none;
}
.hero-left { display: flex; align-items: center; gap: 20px; z-index: 1; }
.hero-icon {
    width: 64px; height: 64px; border-radius: 18px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--ora), var(--ora-dark));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; color: white;
    box-shadow: 0 6px 20px var(--ora-glow);
}
.hero-name {
    font-family: 'Sora', sans-serif;
    font-size: 1.6rem; font-weight: 800; color: white; line-height: 1.1;
}
.hero-meta { font-size: 0.72rem; color: rgba(255,255,255,0.45); margin-top: 5px; font-family: 'JetBrains Mono', monospace; }
.hero-price-block { text-align: right; z-index: 1; }
.price-was { font-size: 1rem; color: rgba(255,255,255,0.4); text-decoration: line-through; }
.price-now { font-family: 'Sora', sans-serif; font-size: 2.8rem; font-weight: 800; color: white; line-height: 1; }
.price-per { font-size: 0.68rem; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 3px; }
.discount-pill {
    display: inline-block; margin-top: 6px;
    background: var(--ora); color: white;
    font-size: 0.7rem; font-weight: 800; padding: 3px 10px; border-radius: 50px;
    box-shadow: 0 3px 10px var(--ora-glow);
}

/* DETAIL GRID */
.detail-grid { display: grid; grid-template-columns: 1fr 300px; gap: 18px; align-items: start; }
.detail-left, .detail-right { display: flex; flex-direction: column; gap: 16px; }

/* CARD */
.detail-card {
    background: var(--card); border-radius: 18px; padding: 22px;
    border: 1px solid var(--border); box-shadow: var(--shadow);
}
.card-head {
    display: flex; align-items: center; gap: 9px;
    font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px;
    color: var(--muted); margin-bottom: 18px;
}
.card-head-icon {
    width: 28px; height: 28px; border-radius: 8px;
    background: var(--ora-light); color: var(--ora);
    display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0;
}

/* STOCK CARD */
.stock-card { border-left: 4px solid var(--ora); }
.stock-inner { display: flex; align-items: center; gap: 24px; }
.stock-ring {
    width: 90px; height: 90px; border-radius: 50%; flex-shrink: 0;
    background: conic-gradient(var(--ora) 0% 70%, var(--ora-light) 70% 100%);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    box-shadow: 0 0 0 5px white, 0 0 0 6px var(--border);
    position: relative;
}
.stock-ring::before {
    content: '';
    position: absolute; width: 68px; height: 68px; border-radius: 50%;
    background: white;
}
.ring-number {
    font-family: 'Sora', sans-serif; font-size: 1.4rem; font-weight: 800;
    color: var(--ora); position: relative; z-index: 1; line-height: 1;
}
.ring-unit { font-size: 0.58rem; font-weight: 700; color: var(--muted); position: relative; z-index: 1; }
.stock-label { font-size: 0.62rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; color: var(--muted); margin-bottom: 4px; }
.stock-display { font-family: 'Sora', sans-serif; font-size: 1.15rem; font-weight: 700; color: var(--text); }
.stock-status {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px;
    padding: 4px 11px; border-radius: 50px; margin-top: 8px;
}
.stock-status.ok  { background: var(--green-light); color: var(--green); }
.stock-status.low { background: var(--red-light); color: var(--red); }

/* META GRID */
.meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.meta-item {
    display: flex; align-items: flex-start; gap: 10px;
    background: #FDFAF7; border-radius: 12px; padding: 12px;
    border: 1px solid var(--border);
}
.meta-icon { font-size: 0.95rem; color: var(--ora); margin-top: 2px; flex-shrink: 0; }
.meta-label { font-size: 0.58rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; color: var(--muted); margin-bottom: 2px; }
.meta-val { font-size: 0.85rem; font-weight: 700; color: var(--text); }
.meta-val.mono { font-family: 'JetBrains Mono', monospace; font-size: 0.78rem; }
.desc-text { color: var(--muted); font-size: 0.9rem; line-height: 1.7; }

/* DATES */
.date-row { display: flex; align-items: center; gap: 14px; }
.mt-3 { margin-top: 16px; }
.date-icon {
    width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
}
.date-icon.green { background: var(--green-light); color: var(--green); }
.date-icon.red   { background: var(--red-light); color: var(--red); }
.date-label { font-size: 0.6rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; color: var(--muted); margin-bottom: 3px; }
.date-val { font-size: 0.9rem; font-weight: 700; color: var(--text); }
.date-val.danger { color: var(--red); }
.date-val.warn { color: var(--amber); }
.expired-badge {
    display: inline-block; background: var(--red); color: white;
    font-size: 0.6rem; font-weight: 800; padding: 2px 7px; border-radius: 50px; margin-left: 6px;
}
.expiring-badge {
    display: inline-block; background: var(--amber); color: white;
    font-size: 0.6rem; font-weight: 800; padding: 2px 7px; border-radius: 50px; margin-left: 6px;
}

/* STATS CARD */
.stat-rows { display: flex; flex-direction: column; gap: 10px; }
.stat-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); }
.stat-row:last-child { border-bottom: none; }
.stat-label { font-size: 0.72rem; color: var(--muted); font-weight: 600; }
.stat-val { font-size: 0.82rem; font-weight: 700; color: var(--text); }
.stat-val.capitalize { text-transform: capitalize; }
.stat-val.orange { color: var(--ora); }

/* ACTIONS */
.btn-action {
    width: 100%; display: flex; align-items: center; justify-content: center; gap: 9px;
    padding: 14px; border-radius: 14px; border: none;
    font-weight: 700; font-size: 0.88rem; cursor: pointer;
    transition: 0.18s; text-decoration: none; font-family: 'DM Sans', sans-serif;
    margin-bottom: 10px;
}
.btn-action.primary {
    background: linear-gradient(135deg, var(--ora), var(--ora-dark)); color: white;
    box-shadow: 0 5px 16px var(--ora-glow);
}
.btn-action.primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px var(--ora-glow); color: white; }
.btn-action.danger { background: var(--red-light); color: var(--red); border: 1.5px solid #FFCDD2; }
.btn-action.danger:hover { background: var(--red); color: white; }
.detail-right form { margin: 0; }

/* SCROLLBAR */
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #D4C4B0; border-radius: 10px; }

/* RESPONSIVE */
@media (max-width: 1020px) { .detail-grid { grid-template-columns: 1fr; } }
@media (max-width: 767px) {
    .desktop-app-layout { position: relative; inset: auto; flex-direction: column; min-height: 100vh; height: auto !important; overflow: auto !important; }
    .desktop-app-layout .sidebar-wrapper { position: fixed !important; }
    .app-main { height: auto !important; overflow: auto !important; }
    .page-header { padding: 0 14px 0 68px; }
    .btn-edit span { display: none; }
    .workspace { padding: 14px; }
    .hero-banner { flex-direction: column; gap: 20px; align-items: flex-start; padding: 24px; }
    .hero-price-block { text-align: left; }
    .hero-name { font-size: 1.3rem; }
    .meta-grid { grid-template-columns: 1fr; }
}
</style>
@endsection