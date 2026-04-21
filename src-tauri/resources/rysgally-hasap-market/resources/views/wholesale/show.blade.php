
@extends('layouts.app')
@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')
    <main class="app-main bg-mesh">
        <header class="main-header d-print-none">
            <a href="{{ route('wholesale.index') }}" class="btn-back"><i class="bi bi-arrow-left"></i></a>
            <div>
                <h4 class="fw-bold mb-0" style="color:#E8722A;">{{ __('app.wholesale_show_invoice_title') }} #{{ $invoice->invoice_no }}</h4>
                <p class="text-muted small mb-0 d-none d-md-block">{{ $invoice->created_at->format('M d, Y | H:i') }}</p>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('wholesale.edit', $invoice->id) }}" class="btn-teal">
                    <i class="bi bi-pencil-square me-1 me-md-2"></i><span class="d-none d-md-inline">{{ __('app.wholesale_show_btn_edit') }}</span>
                </a>
                <button onclick="window.print()" class="btn-dark-sm">
                    <i class="bi bi-printer me-1 me-md-2"></i><span class="d-none d-md-inline">{{ __('app.wholesale_show_btn_print') }}</span>
                </button>
                
            </div>
        </header>

        <div class="workspace custom-scrollbar">
            <div class="invoice-wrap">
                <div class="receipt-card">
                    <div class="receipt-top-bar"></div>
                    <div class="receipt-body">
                        <div class="inv-header-grid">
                            <div>
                                <div class="brand-row"><i class="bi bi-capsule me-2"></i>{{ __('app.wholesale_show_brand') }}<span style="color:#E8722A;">{{ __('app.wholesale_show_brand_emphasis') }}</span></div>
                                <p class="text-muted small mb-0">{{ __('app.wholesale_show_dept') }}</p>
                            </div>
                            <div class="text-sm-end">
                                <div class="inv-big-label">{{ __('app.wholesale_show_invoice_title') }}</div>
                                <div class="fw-bold text-dark">#{{ $invoice->invoice_no }}</div>
                                <div class="text-muted small">{{ $invoice->created_at->format('M d, Y | H:i') }}</div>
                            </div>
                        </div>

                        {{-- Customer / status --}}
                        <div class="customer-box">
                            <div>
                                <div class="box-label">{{ __('app.wholesale_show_customer') }}</div>
                                <h5 class="fw-bold mb-0" style="color:#E8722A;">{{ $invoice->customer_name }}</h5>
                                <small class="text-muted">{{ __('app.wholesale_show_partner') }}</small>
                            </div>
                            <div class="text-sm-end">
                                <div class="box-label">{{ __('app.wholesale_show_status') }}</div>
                                <span class="paid-badge"><i class="bi bi-check-circle-fill me-1"></i>{{ __('app.wholesale_show_status_paid') }}</span>
                            </div>
                        </div>
                        <div class="table-responsive mb-4">
                            <table class="table inv-table align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('app.wholesale_show_table_product') }}</th>
                                        <th class="text-center">{{ __('app.wholesale_show_table_expiry') }}</th>
                                        <th class="text-center">{{ __('app.wholesale_show_table_batch') }}</th>
                                        <th class="text-center">{{ __('app.wholesale_show_table_qty') }}</th>
                                        @if($invoice->items->where('unit_type', 'piece')->count() > 0)
                                        <th class="text-center">{{ __('app.wholesale_show_table_item') }}</th>
                                        @endif
                                        @if($invoice->items->where('unit_type', 'weight')->count() > 0)
                                        <th class="text-center">{{ __('app.wholesale_show_table_weight') }}</th>
                                        @endif
                                        <th class="text-center">{{ __('app.wholesale_show_table_price') }}</th>
                                        <th class="text-center">{{ __('app.wholesale_show_table_discount') }}</th>
                                        <th class="text-end">{{ __('app.wholesale_show_table_total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->items as $item)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $item->product->name }}</td>
                                        <td class="text-center">
                                            @if($item->expiry_date_text)
                                            <span class="expiry-pill">{{ \Carbon\Carbon::parse($item->expiry_date_text)->format('d.m.Y') }}</span>
                                            @else <span class="text-muted small">—</span> @endif
                                        </td>
                                        <td class="text-center">
                                            @if($item->batch_number_text)
                                            <span class="batch-pill">#{{ $item->batch_number_text }}</span>
                                            @else <span class="text-muted small">—</span> @endif
                                        </td>
                                        <td class="text-center fw-bold">{{ $item->display_quantity }}</td>
                                        @if($invoice->items->where('unit_type', 'piece')->count() > 0)
                                        <td class="text-center fw-bold">{{ $item->unit_type === 'piece' ? number_format($item->quantity) : '—' }}</td>
                                        @endif
                                        @if($invoice->items->where('unit_type', 'weight')->count() > 0)
                                        <td class="text-center fw-bold">{{ $item->unit_type === 'weight' ? number_format($item->quantity, 3) . ' kg' : '—' }}</td>
                                        @endif
                                        <td class="text-center text-muted">${{ number_format($item->unit_price,2) }}</td>
                                        <td class="text-center">
                                            @if($item->discount_percent > 0)
                                            <span class="disc-pill">-{{ $item->discount_percent }}%</span>
                                            @else <span class="text-muted">—</span> @endif
                                        </td>
                                        <td class="text-end fw-bold text-dark">${{ number_format($item->row_total,2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="totals-block">
                            <div class="total-row"><span class="text-muted small">{{ __('app.wholesale_show_subtotal') }}</span><span class="fw-bold">${{ number_format($invoice->items->sum(fn($i)=>$i->quantity*$i->unit_price),2) }}</span></div>
                            <div class="total-row"><span class="text-muted small">{{ __('app.wholesale_show_discount') }}</span><span class="fw-bold text-danger">-${{ number_format($invoice->items->sum(fn($i)=>($i->quantity*$i->unit_price)-$i->row_total),2) }}</span></div>
                            <div class="total-divider"></div>
                            <div class="grand-row"><span class="fw-bold text-uppercase small">{{ __('app.wholesale_show_grand_total') }}</span><span class="grand-val">${{ number_format($invoice->total_amount,2) }}</span></div>
                        </div>

                        <div class="text-center mt-5 opacity-25 small fw-bold text-uppercase">{{ __('app.wholesale_show_thank_you') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
*, *::before, *::after { box-sizing:border-box; }
body { margin:0; padding:0; font-family:'Inter',sans-serif; background:#f4f7f7; }
.desktop-app-layout { position:fixed; inset:0; display:flex; overflow:hidden; }
.desktop-app-layout .sidebar-wrapper { position:relative !important; flex-shrink:0; height:100%; }
.app-main { flex:1; min-width:0; display:flex; flex-direction:column; overflow:hidden; height:100%; }
.bg-mesh { background-color:#f4f7f7;; background-size:30px 30px; }

.main-header { height:68px; background:rgba(244,247,247,0.88); backdrop-filter:blur(14px); border-bottom:1px solid rgba(0,0,0,0.05); display:flex; align-items:center; padding:0 24px; gap:14px; flex-shrink:0; z-index:10; }
.btn-back { width:38px; height:38px; min-width:38px; border-radius:11px; background:white; border:1px solid #e8edf2; color:#718096; display:flex; align-items:center; justify-content:center; text-decoration:none; transition:0.2s; }
.btn-back:hover { color:#E8722A; transform:translateX(-2px); }
.btn-teal { background:#E8722A; color:white; border:none; border-radius:10px; padding:8px 16px; font-weight:700; font-size:0.82rem; display:flex; align-items:center; text-decoration:none; transition:0.2s; }
.btn-teal:hover { background:#C85A1A; color:white; }
.btn-dark-sm { background:#2d3748; color:white; border:none; border-radius:10px; padding:8px 16px; font-weight:700; font-size:0.82rem; display:flex; align-items:center; cursor:pointer; transition:0.2s; }

.workspace { flex:1; overflow-y:auto; padding:24px; }
.invoice-wrap { max-width:820px; margin:0 auto; }
.receipt-card { background:white; border-radius:24px; overflow:hidden; box-shadow:0 16px 48px rgba(0,0,0,0.08); }
.receipt-top-bar { height:8px; background:linear-gradient(90deg,#E8722A,#C85A1A); }
.receipt-body { padding:36px; }

.inv-header-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:28px; }
.brand-row { font-size:1.2rem; font-weight:800; color:#2d3748; margin-bottom:4px; }
.inv-big-label { font-size:1.8rem; font-weight:900; color:#e9ecef; letter-spacing:3px; text-transform:uppercase; }

.customer-box { display:flex; justify-content:space-between; align-items:flex-start; background:#fcfdfe; border:1px solid #f1f4f5; border-radius:14px; padding:20px 24px; margin-bottom:28px; flex-wrap:wrap; gap:12px; }
.box-label { font-size:0.62rem; text-transform:uppercase; font-weight:800; color:#a0aec0; margin-bottom:4px; }
.paid-badge { background:#d1e7dd; color:#0f5132; padding:6px 14px; border-radius:20px; font-size:0.75rem; font-weight:700; display:inline-block; }

.inv-table thead th { background:#fcfdfe; color:#a0aec0; font-size:0.68rem; text-transform:uppercase; font-weight:800; padding:16px; border:none; }
.inv-table td { padding:18px 16px; border-bottom:1px solid #f8fafc; }
.expiry-pill { background:#fff5f5; color:#e53e3e; border:1px solid #feb2b2; padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:600; }
.batch-pill  { background:#f0f5ff; color:#3182ce; border:1px solid #bee3f8; padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:600; }
.disc-pill   { background:#fff5f5; color:#e53e3e; border:1px solid #fecaca; padding:3px 8px; border-radius:6px; font-size:0.72rem; font-weight:700; }

.totals-block { max-width:320px; margin-left:auto; }
.total-row { display:flex; justify-content:space-between; margin-bottom:8px; }
.total-divider { border-top:2px solid #edf2f7; margin:10px 0; }
.grand-row { display:flex; justify-content:space-between; align-items:center; background:#E8722A; color:white; padding:12px 16px; border-radius:12px; }
.grand-val { font-size:1.4rem; font-weight:800; }

.custom-scrollbar::-webkit-scrollbar { width:6px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background:#cbd5e0; border-radius:10px; }

@media (max-width:767px) {
    .desktop-app-layout { position:relative; inset:auto; min-height:100vh; height:auto !important; flex-direction:column; overflow:auto !important; }
    .desktop-app-layout .sidebar-wrapper { position:fixed !important; }
    .app-main { height:auto !important; overflow:auto !important; }
    .main-header { padding:0 14px 0 68px; height:60px; }
    .workspace { padding:12px; }
    .receipt-body { padding:20px; }
    .inv-header-grid { grid-template-columns:1fr; }
    .totals-block { max-width:100%; }
}
@media print { .sidebar-wrapper,.main-header,.d-print-none { display:none !important; } .desktop-app-layout { display:block; } body { background:white; } .receipt-card { box-shadow:none; } .bg-mesh { background:white; } }
</style>
@endsection