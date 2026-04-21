@extends('layouts.app')
@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')
    <main class="app-main">
        <header class="main-header">
            <div class="header-left">
                <h4 class="fw-bold mb-0">{{ __('app.wholesale_storage_title') }}</h4>
                <p class="text-muted small mb-0 d-none d-md-block">{{ __('app.wholesale_storage_subtitle') }}</p>
            </div>
            <a href="{{ route('wholesale_storage.create') }}" class="btn-teal ms-auto">
                <i class="bi bi-plus-circle-dotted me-1 me-md-2"></i>
                <span class="d-none d-md-inline">{{ __('app.wholesale_storage_btn_new') }}</span>
            </a>
            <a href="{{ route('wholesale_storage.export') }}" class="btn-light-sm text-success">
    <i class="bi bi-file-earmark-excel-fill"></i>
</a>
        </header>

        <div class="workspace custom-scrollbar">
            <div class="ws-inner">

                {{-- KPI cards --}}
                <div class="kpi-row">
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background:rgba(232,114,42,0.1);color:#E8722A;"><i class="bi bi-tags"></i></div>
                        <div>
                            <div class="kpi-label">{{ __('app.wholesale_storage_kpi_quantity') }}</div>
                            <div class="kpi-value">{{ number_format($inventory->sum(fn ($i) => (float) $i->quantity), 3) }}</div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background:rgba(34,197,94,0.1);color:#22c55e;"><i class="bi bi-graph-up-arrow"></i></div>
                        <div>
                            <div class="kpi-label">{{ __('app.wholesale_storage_kpi_value') }}</div>
                            <div class="kpi-value">${{ number_format($inventory->sum(fn($i)=>$i->quantity*$i->received_price),2) }}</div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background:rgba(59,130,246,0.1);color:#3b82f6;"><i class="bi bi-stack"></i></div>
                        <div>
                            <div class="kpi-label">{{ __('app.wholesale_storage_kpi_batches') }}</div>
                            <div class="kpi-value">{{ $inventory->count() }}</div>
                        </div>
                    </div>
                </div>

                {{-- Table (desktop) --}}
                <div class="table-card d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table inv-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">{{ __('app.wholesale_storage_table_product') }}</th>
                                    <th class="text-center">{{ __('app.wholesale_storage_table_qty') }}</th>
                                    <th class="text-center">{{ __('app.wholesale_storage_table_received') }}</th>
                                    <th class="text-center">{{ __('app.wholesale_storage_table_price') }}</th>
                                    <th class="text-center">{{ __('app.wholesale_storage_table_batch') }}</th>
                                    <th class="text-center">{{ __('app.wholesale_storage_table_expiry') }}</th>
                                    <th class="pe-4 text-end">{{ __('app.wholesale_storage_table_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inventory as $item)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-dark">{{ $item->product->name }}</div>
                                        <small class="text-muted">{{ $item->product->manufacturer }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($item->is_weight)
                                            <span class="stock-badge {{ $item->quantity < 5 ? 'low' : 'ok' }}">
                                                {{ number_format($item->quantity, 3) }} {{ __('app.unit_kg') }}
                                            </span>
                                        @else
                                            <span class="stock-badge {{ $item->quantity < 10 ? 'low' : 'ok' }}">
                                                {{ number_format($item->quantity) }} {{ __('app.unit_items') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center text-muted font-monospace">${{ number_format($item->received_price,2) }}</td>
                                    <td class="text-center fw-bold text-teal fs-6">${{ number_format($item->selling_price,2) }}</td>
                                    <td class="text-center">
                                        <div class="small fw-bold text-muted">#{{ $item->batch_number ?? __('text_na') }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($item->expiry_date)
                                            @php
                                                $expired = \Carbon\Carbon::parse($item->expiry_date)->isPast();
                                                $urgent  = \Carbon\Carbon::parse($item->expiry_date)->diffInMonths(now()) < 3;
                                            @endphp
                                            <span class="{{ ($expired||$urgent) ? 'text-danger fw-bold' : 'text-muted' }}">
                                                <i class="bi bi-calendar-x me-1"></i>{{ \Carbon\Carbon::parse($item->expiry_date)->format('d.m.Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">{{ __('app.text_no_date') }}</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <button class="act-btn transfer" data-bs-toggle="modal"
                                                data-bs-target="#transferModal{{ $item->id }}" title="{{ __('app.wholesale_storage_transfer') }}">
                                                <i class="bi bi-truck"></i>
                                            </button>
                                            <form action="{{ route('wholesale_storage.destroy', $item->id) }}" method="POST" class="m-0">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="act-btn delete"
                                                    onclick="return confirm('{{ __('app.wholesale_storage_remove_confirm') }}')">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($inventory->hasPages())
                    <div class="p-3 border-top">{{ $inventory->links() }}</div>
                    @endif
                </div>

                {{-- Mobile cards --}}
                <div class="d-md-none mobile-list">
                    @foreach($inventory as $item)
                    @php
                        $expired = $item->expiry_date && \Carbon\Carbon::parse($item->expiry_date)->isPast();
                        $urgent  = $item->expiry_date && \Carbon\Carbon::parse($item->expiry_date)->diffInMonths(now()) < 3;
                    @endphp
                    <div class="mobile-batch-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold text-dark">{{ $item->product->name }}</div>
                                <small class="text-muted">{{ $item->product->manufacturer }}</small>
                            </div>
                            <span class="fw-bold text-teal">${{ number_format($item->selling_price,2) }}</span>
                        </div>
                        <div class="mobile-batch-meta">
                            <div><span class="meta-label">{{ __('app.wholesale_storage_table_qty') }}</span>
                                @if($item->is_weight)
                                    <span class="stock-badge {{ $item->quantity < 5 ? 'low' : 'ok' }}">
                                        {{ number_format($item->quantity, 3) }} {{ __('app.unit_kg') }}
                                    </span>
                                @else
                                    <span class="stock-badge {{ $item->quantity < 10 ? 'low' : 'ok' }}">
                                        {{ number_format($item->quantity) }} {{ __('app.unit_items') }}
                                    </span>
                                @endif
                            </div>
                            <div><span class="meta-label">{{ __('app.wholesale_storage_label_received') }}</span><span class="text-muted small">${{ number_format($item->received_price,2) }}</span></div>
                            <div><span class="meta-label">{{ __('app.wholesale_storage_table_batch') }}</span><span class="text-muted small">#{{ $item->batch_number ?? __('app.text_na') }}</span></div>
                            <div><span class="meta-label">{{ __('app.wholesale_storage_label_expiry') }}</span>
                                <span class="{{ ($expired||$urgent) ? 'text-danger fw-bold' : 'text-muted' }} small">
                                    {{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('d.m.Y') : '—' }}
                                </span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-2">
                            <button class="act-btn transfer flex-grow-1" data-bs-toggle="modal" data-bs-target="#transferModal{{ $item->id }}">
                                <i class="bi bi-truck me-1"></i> {{ __('app.wholesale_storage_transfer') }}
                            </button>
                            <form action="{{ route('wholesale_storage.destroy', $item->id) }}" method="POST" class="m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="act-btn delete" onclick="return confirm('{{ __('app.wholesale_storage_remove_short_confirm') }}')">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>
    </main>
</div>

@foreach($inventory as $item)
    @include('wholesale_storage.transfer_modal', ['item' => $item])
@endforeach

<style>
*, *::before, *::after { box-sizing:border-box; }
body { margin:0; padding:0; font-family:'Inter',sans-serif; background:#f4f7f7; }
.desktop-app-layout { position:fixed; inset:0; display:flex; overflow:hidden; }
.desktop-app-layout .sidebar-wrapper { position:relative !important; flex-shrink:0; height:100%; }
.app-main { flex:1; min-width:0; display:flex; flex-direction:column; overflow:hidden; height:100%; }

.main-header { height:68px; background:white; border-bottom:1px solid #e8edf2; display:flex; align-items:center; padding:0 24px; gap:14px; flex-shrink:0; }
.btn-teal { background:#E8722A; color:white; border:none; border-radius:11px; padding:9px 16px; font-weight:700; font-size:0.82rem; display:flex; align-items:center; text-decoration:none; transition:0.2s; white-space:nowrap; }
.btn-teal:hover { background:#C85A1A; color:white; }

.workspace { flex:1; overflow-y:auto; padding:20px 24px; }
.ws-inner { max-width:1200px; margin:0 auto; }

.kpi-row { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px; }
.kpi-card { background:white; border-radius:16px; padding:20px 22px; display:flex; align-items:center; gap:14px; border:1px solid #e8edf2; box-shadow:0 2px 8px rgba(0,0,0,0.03); transition:0.2s; }
.kpi-card:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,0.07); }
.kpi-icon { width:50px; height:50px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
.kpi-label { font-size:0.62rem; text-transform:uppercase; font-weight:800; color:#a0aec0; margin-bottom:2px; }
.kpi-value { font-size:1.3rem; font-weight:800; color:#2d3748; }

.table-card { background:white; border-radius:16px; overflow:hidden; border:1px solid #e8edf2; box-shadow:0 2px 8px rgba(0,0,0,0.03); }
.inv-table thead th { background:#f8fafc; color:#a0aec0; font-size:0.7rem; text-transform:uppercase; font-weight:800; padding:14px; border:none; white-space:nowrap; }
.inv-table tbody tr { transition:background 0.15s; }
.inv-table tbody tr:hover { background:#fbfcfd; }
.inv-table td { vertical-align:middle; padding:14px; border-bottom:1px solid #f4f7f7; }
.text-teal { color:#E8722A; }

.stock-badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:0.72rem; font-weight:700; }
.stock-badge.ok  { background:rgba(22,163,74,0.1); color:#16a34a; }
.stock-badge.low { background:rgba(239,68,68,0.1); color:#ef4444; }

.act-btn { background:#f4f7f6; color:#555; border:1px solid #e2e8f0; border-radius:8px; width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; font-size:0.8rem; cursor:pointer; transition:0.2s; }
.act-btn.transfer:hover { background:#E8722A; color:white; border-color:#E8722A; }
.act-btn.delete:hover  { background:#ef4444; color:white; border-color:#ef4444; }

.mobile-list { margin-bottom:16px; }
.mobile-batch-card { background:white; border-radius:14px; padding:14px 16px; margin-bottom:10px; border:1px solid #e8edf2; }
.mobile-batch-meta { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:4px; }
.meta-label { display:block; font-size:0.58rem; text-transform:uppercase; font-weight:800; color:#a0aec0; margin-bottom:2px; }
.act-btn.transfer { width:auto; padding:7px 14px; gap:4px; font-size:0.8rem; border-radius:8px; justify-content:center; }

.custom-scrollbar::-webkit-scrollbar { width:6px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background:#cbd5e0; border-radius:10px; }

@media (max-width:1100px) { .kpi-row { grid-template-columns:repeat(2,1fr); } }
@media (max-width:767px) {
    .desktop-app-layout { position:relative; inset:auto; min-height:100vh; height:auto !important; flex-direction:column; overflow:auto !important; }
    .desktop-app-layout .sidebar-wrapper { position:fixed !important; }
    .app-main { height:auto !important; overflow:auto !important; }
    .main-header { padding:0 14px 0 68px; height:60px; }
    .workspace { padding:12px; }
    .kpi-row { grid-template-columns:1fr 1fr; }
}
@media print { .sidebar-wrapper,.main-header { display:none !important; } .desktop-app-layout { display:block; } body { background:white; } }
</style>
@endsection