@extends('layouts.app')

@section('content')
<div class="desktop-top-layout">
    @include('app.navbar')

    <main class="app-main">
        <div class="receipt-card animate-slide-up">
            <div class="workspace overflow-auto">
                <div class="receipt-container">

                    {{-- ══ RECEIPT HEADER ══ --}}
                    <div class="receipt-header d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-1">
                                {{ __('app.receipt_label_transaction') }}:
                                <span class="txn-code">#{{ ltrim($transaction_id, '#') }}</span>
                            </h4>
                            <div class="text-muted small">
                                {{ $orderDate->format('d.m.Y · H:i') }}
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="window.print()"
                                    class="btn-print-action d-print-none">
                                <i class="bi bi-printer me-1"></i> Печать
                            </button>
                            <a href="{{ route('sales.customers.export.single', ['transaction_id' => ltrim($transaction_id, '#')]) }}"
   class="btn-export-action d-print-none">
    <i class="bi bi-file-earmark-excel-fill me-1"></i> XLS
</a>
<a href="{{ route('sales.customers.index') }}"
   class="btn-back d-print-none">
    <i class="bi bi-arrow-left"></i>
</a>

                        </div>
                    </div>

                    {{-- ══ BRAND STRIP ══ --}}
                    <div class="receipt-brand">
                        <div class="brand-cart-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 104 0v-4M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"
                                      stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="brand-name">RysgallyMarket</div>
                            <div class="brand-sub">{{ __('app.receipt_title') }}</div>
                        </div>
                    </div>

                    {{-- ══ META GRID ══ --}}
                    <div class="receipt-meta-grid">
                        <div>
                            <div class="receipt-label">{{ __('app.receipt_label_transaction') }}</div>
                            <div class="receipt-value mono">#{{ ltrim($transaction_id, '#') }}</div>
                        </div>
                        <div>
                            <div class="receipt-label">{{ __('app.receipt_label_date') }}</div>
                            <div class="receipt-value">{{ $orderDate->format('d.m.Y') }}</div>
                            <div class="receipt-time">{{ $orderDate->format('H:i') }}</div>
                        </div>
                    </div>

                    {{-- ══ ITEMS TABLE (desktop) ══ --}}
                    <table class="receipt-table w-100 mt-4 d-none d-md-table">
                        <thead>
                            <tr>
                                <th class="text-start">{{ __('app.receipt_table_product') }}</th>
                                <th class="text-start">{{ __('app.receipt_table_sku') }}</th>
                                <th class="text-center">{{ __('app.receipt_table_qty') }}</th>
                                <th class="text-end">{{ __('app.receipt_table_price') }}</th>
                                <th class="text-center">{{ __('app.receipt_table_discount') }}</th>
                                <th class="text-end pe-4">{{ __('app.receipt_table_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            @php
                                $unit        = ($item->product->unit_type ?? 'piece') === 'weight' ? 'kg' : 'pcs';
                                $qtyDisplay  = $unit === 'kg'
                                    ? number_format((float)$item->quantity, 3, '.', '')
                                    : (int)$item->quantity;
                                $productCode = $item->product->product_code ?? ($item->product->barcode ?? '—');
                            @endphp
                            <tr>
                                <td class="text-bold text-dark">{{ $item->product->name ?? __('app.receipt_product_deleted') }}</td>
                                <td class="text-muted text-sm mono">{{ $productCode }}</td>
                                <td class="text-center fw-medium">{{ $qtyDisplay }} {{ $unit }}</td>
                                <td class="text-end">{{ number_format($item->price, 2) }}</td>
                                <td class="text-center">
                                    @if((int) ($item->discount ?? 0) > 0)
                                        <span class="disc-pill-receipt">-{{ (int) $item->discount }}%</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4 fw-black text-dark">{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- ══ ITEMS MOBILE ══ --}}
                    <div class="receipt-items-mobile d-md-none">
                        @foreach($items as $item)
                        @php
                            $unit        = ($item->product->unit_type ?? 'piece') === 'weight' ? 'kg' : 'pcs';
                            $qtyDisplay  = $unit === 'kg'
                                ? number_format((float)$item->quantity, 3, '.', '')
                                : (int)$item->quantity;
                            $productCode = $item->product->product_code ?? ($item->product->barcode ?? '—');
                        @endphp
                        <div class="receipt-item-row">
                            <div class="item-name">{{ $item->product->name ?? __('app.receipt_product_deleted') }}</div>
                            <div class="item-meta">{{ $productCode }}@if((int) ($item->discount ?? 0) > 0) · <span class="text-danger fw-semibold">-{{ (int) $item->discount }}%</span>@endif</div>
                            <div class="item-total">{{ number_format($item->total_price, 2) }}</div>
                            <div class="item-qty">{{ $qtyDisplay }} {{ $unit }} × {{ number_format($item->price, 2) }}</div>
                        </div>
                        @endforeach
                    </div>

                    {{-- ══ TOTALS ══ --}}
                    <div class="receipt-footer mt-4">
                        <div class="totals-block">
                            <div class="total-row">
                                <span class="text-muted">{{ __('app.receipt_subtotal') }}</span>
                                <span class="fw-medium">{{ number_format($subtotalBeforeDiscount, 2) }}</span>
                            </div>
                            <div class="total-row">
                                <span class="text-muted">{{ __('app.receipt_discount_total') }}</span>
                                <span class="fw-bold {{ $discountAmount > 0 ? 'text-danger' : 'text-muted' }}">
                                    @if($discountAmount > 0)
                                        −{{ number_format($discountAmount, 2) }} <small>TMT</small>
                                    @else
                                        0.00 <small>TMT</small>
                                    @endif
                                </span>
                            </div>
                            <div class="total-divider"></div>
                            <div class="total-row">
                                <span class="text-uppercase fw-black" style="letter-spacing:.5px;">{{ __('app.receipt_to_pay') }}</span>
                                <span class="grand-amount">
                                    {{ number_format($total, 2) }}
                                    <small>TMT</small>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- ══ WATERMARK / BARCODE ══ --}}
                    <div class="receipt-watermark mt-4">
                        <div class="receipt-barcode">
                            {{ str_pad(ltrim($transaction_id, '#'), 20, '0', STR_PAD_LEFT) }}
                        </div>
                        <div class="receipt-thanks">{{ __('app.receipt_thank_you') }}</div>
                    </div>

                </div>
            </div>
        </div>
    </main>
</div>

<script>
@if(session('auto_print'))
    window.addEventListener('afterprint', function() {
        window.location.href = "{{ route('sales.index') }}";
    });
@endif
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;600&display=swap');

:root {
    --ora: #E8722A;
    --ora-dark: #C4561A;
    --ora-light: #FFF0E6;
    --ora-glow: rgba(232,114,42,0.2);
    --bg: #FBF7F3;
    --border: #EDE4DA;
    --text: #1A0A00;
    --muted: #8B7355;
}
*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'DM Sans', sans-serif; background: var(--bg); }

/* ── LAYOUT ── */
.desktop-top-layout { display: flex; width: 100%; overflow: hidden; height: 100vh; }
.app-main { flex: 1; display: flex; flex-direction: column; min-width: 0; position: relative; overflow: hidden; }

/* ── RECEIPT CARD ── */
.receipt-card {
    background: var(--bg);
    flex: 1; display: flex; flex-direction: column; overflow: hidden;
}
.workspace { flex: 1; padding: 24px; overflow-y: auto; }
.receipt-container {
    max-width: 740px; margin: 0 auto;
    background: white; border-radius: 24px;
    box-shadow: 0 20px 60px rgba(26,10,0,0.09);
    overflow: hidden;
}

/* ── HEADER ── */
.receipt-header {
    padding: 32px 36px;
    border-bottom: 2px dashed var(--border);
}
.receipt-header h4 { font-family: 'Sora', sans-serif; font-weight: 800; font-size: 1.1rem; color: var(--text); }
.txn-code { font-family: 'JetBrains Mono', monospace; color: var(--ora); font-size: 1rem; }

.btn-print-action {
    background: linear-gradient(135deg, var(--ora), var(--ora-dark));
    color: white; border: none; border-radius: 11px;
    padding: 9px 16px; font-size: 0.8rem; font-weight: 700;
    cursor: pointer; transition: 0.18s; display: inline-flex; align-items: center;
    box-shadow: 0 4px 12px var(--ora-glow);
}
.btn-print-action:hover { transform: translateY(-1px); box-shadow: 0 6px 18px var(--ora-glow); }

.btn-export-action {
    background: #E8F5E9; color: #2E7D32;
    border: 1.5px solid #C8E6C9; border-radius: 11px;
    padding: 9px 14px; font-size: 0.8rem; font-weight: 700;
    text-decoration: none; display: inline-flex; align-items: center;
    transition: 0.18s;
}
.btn-export-action:hover { background: #2E7D32; color: white; }

.btn-back {
    width: 38px; height: 38px;
    background: #FDFAF7; border: 1.5px solid var(--border); border-radius: 11px;
    display: inline-flex; align-items: center; justify-content: center;
    color: var(--muted); text-decoration: none; transition: 0.18s;
}
.btn-back:hover { background: var(--ora-light); color: var(--ora); }

/* ── BRAND ── */
.receipt-brand {
    background: linear-gradient(135deg, #1A0A00, #2E1100);
    padding: 16px 36px;
    display: flex; align-items: center; gap: 14px;
}
.brand-cart-icon {
    width: 40px; height: 40px; border-radius: 11px;
    background: linear-gradient(135deg, var(--ora), var(--ora-dark));
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; box-shadow: 0 4px 12px var(--ora-glow);
}
.brand-name { font-family: 'Sora', sans-serif; font-size: 1rem; font-weight: 800; color: white; }
.brand-sub { font-size: 0.62rem; text-transform: uppercase; letter-spacing: 2px; color: rgba(255,255,255,0.45); margin-top: 2px; }

/* ── META ── */
.receipt-meta-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 16px; padding: 20px 36px;
    border-bottom: 1px solid var(--border);
}
.receipt-label { font-size: 0.6rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.6px; color: var(--muted); margin-bottom: 4px; }
.receipt-value { font-size: 1.05rem; font-weight: 800; color: var(--text); }
.receipt-value.mono { font-family: 'JetBrains Mono', monospace; font-size: 0.9rem; color: var(--ora); }
.receipt-time { font-size: 0.8rem; color: var(--muted); margin-top: 2px; }

/* ── TABLE ── */
.receipt-table { border-collapse: collapse; }
.receipt-table thead th {
    background: #FBF7F3; color: var(--muted);
    font-size: 0.65rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;
    padding: 12px 16px; border-bottom: 1px solid var(--border); border-top: 1px solid var(--border);
}
.receipt-table td {
    padding: 14px 16px; border-bottom: 1px solid #F5EDE4;
    font-size: 0.88rem;
}
.receipt-table tbody tr:last-child td { border-bottom: none; }
.receipt-table tbody tr:hover { background: #FFF8F3; }
.text-bold { font-weight: 700; }
.text-sm { font-size: 0.78rem; }
.mono { font-family: 'JetBrains Mono', monospace; }
.fw-black { font-family: 'Sora', sans-serif; font-weight: 800; }

.disc-pill-receipt {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 800;
    background: rgba(220, 53, 69, 0.1);
    color: #c82333;
}

/* ── MOBILE ITEMS ── */
.receipt-items-mobile { padding: 0 16px; }
.receipt-item-row {
    display: grid;
    grid-template-columns: 1fr auto;
    grid-template-rows: auto auto;
    gap: 2px 8px; padding: 14px 0;
    border-bottom: 1px solid var(--border);
}
.receipt-item-row:last-child { border-bottom: none; }
.item-name  { font-weight: 700; font-size: 0.9rem; grid-column: 1; }
.item-meta  { font-size: 0.72rem; color: var(--muted); grid-column: 1; font-family: 'JetBrains Mono', monospace; }
.item-total { font-size: 0.9rem; font-weight: 800; color: var(--text); grid-column: 2; grid-row: 1; text-align: right; align-self: center; }
.item-qty   { font-size: 0.72rem; color: var(--muted); grid-column: 2; grid-row: 2; text-align: right; }

/* ── FOOTER TOTALS ── */
.receipt-footer { padding: 0 36px 32px; }
.totals-block { max-width: 320px; margin-left: auto; }
.total-row {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 8px; font-size: 0.9rem;
}
.total-divider {
    border-top: 2px solid var(--border); margin: 14px 0;
}
.grand-amount {
    font-family: 'Sora', sans-serif;
    font-size: 1.5rem; font-weight: 800; color: var(--ora);
}
.grand-amount small { font-size: 0.7rem; font-weight: 600; color: var(--muted); margin-left: 4px; }

/* ── WATERMARK ── */
.receipt-watermark { text-align: center; padding: 20px 36px 32px; }
.receipt-barcode {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.7rem; letter-spacing: 3px; color: var(--muted);
    margin-bottom: 8px;
}
.receipt-thanks {
    font-size: 0.78rem; color: var(--muted); font-weight: 600;
}

/* ── ANIMATION ── */
.animate-slide-up { animation: slideUp 0.4s cubic-bezier(0.16,1,0.3,1); }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* ── PRINT ── */
@media print {
    .btn-back, .btn-print-action, .btn-export-action, .d-print-none,
    .sidebar-wrapper, .app-navbar { display: none !important; }

    .workspace { padding: 0 !important; margin: 0 !important; background: white !important; }
    .receipt-container { max-width: 100% !important; width: 100% !important; margin: 0 !important;
        box-shadow: none !important; border-radius: 0 !important; border: none !important; }
    .receipt-card { box-shadow: none !important; border: none !important; border-radius: 0 !important; }
    .desktop-top-layout { display: block; }

    @page { margin: 0; size: auto; }
}

/* ── RESPONSIVE ── */
@media (max-width: 767px) {
    .desktop-top-layout { flex-direction: column; height: auto; overflow: auto; }
    .app-main { overflow: auto; }
    .workspace { padding: 12px; }
    .receipt-header { padding: 20px 16px; }
    .receipt-brand { padding: 14px 16px; }
    .receipt-meta-grid { padding: 16px; }
    .receipt-footer { padding: 0 16px 24px; }
    .receipt-watermark { padding: 16px; }
    .receipt-header .d-flex.gap-2 { flex-wrap: wrap; }
}
</style>
@endsection
