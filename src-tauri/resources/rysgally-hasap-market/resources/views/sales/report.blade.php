@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main">
        <header class="main-header d-print-none">
            <a href="{{ route('sales.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold mb-0" style="color: #E8722A;">{{__("app.report_title")}}</h4>
                <p class="text-muted small mb-0 d-none d-md-block">{{__("app.report_cashier")}} {{ auth()->user()->name }}</p>
            </div>
            <div class="ms-auto d-print-none">
                <button onclick="window.print()" class="btn-print-action">
                    <i class="bi bi-printer me-1 me-md-2"></i>
                    <span class="d-none d-md-inline">{{__("app.btn_print_report_final")}}</span>
                </button>
            </div>
        </header>

        <div class="workspace custom-scrollbar">
            <div class="report-container">

                {{-- Shift timing --}}
                <div class="shift-times-card panel-card mb-4">
                    <div class="shift-time-item">
                        <small class="time-label">{{__("app.report_shift_open")}}</small>
                        <span class="time-value">{{ $report['start_time'] ? $report['start_time']->format('H:i  d.m.Y') : '---' }}</span>
                    </div>
                    <div class="shift-divider"><i class="bi bi-arrow-right text-muted opacity-50"></i></div>
                    <div class="shift-time-item text-md-end">
                        <small class="time-label">{{__("app.report_shift_close")}}</small>
                        <span class="time-value">{{ $report['end_time']->format('H:i  d.m.Y') }}</span>
                    </div>
                </div>

                {{-- Summary stats --}}
                <div class="summary-grid mb-4">
                    <div class="summary-card">
                        <div class="summary-icon bg-teal-soft"><i class="bi bi-box2-heart text-teal"></i></div>
                        <div class="summary-data">
                            <span class="summary-label">{{__("app.report_total_sold")}}</span>
                            <span class="summary-value">{{ $report['total_items'] }} <small>ед.</small></span>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-icon bg-green-soft"><i class="bi bi-cash-stack text-success"></i></div>
                        <div class="summary-data">
                            <span class="summary-label">{{__("app.report_total_cash")}}</span>
                            <span class="summary-value text-success">{{ number_format($report['total_money'], 2) }} <small>TMT</small></span>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-icon bg-blue-soft"><i class="bi bi-calendar-month text-primary"></i></div>
                        <div class="summary-data">
                            <span class="summary-label">{{__("app.report_revenue_month")}} {{ now()->translatedFormat('F') }}</span>
                            <span class="summary-value">{{ number_format($report['monthly_total'], 2) }} <small>TMT</small></span>
                        </div>
                    </div>
                </div>

                {{-- Items table --}}
                <div class="panel-card">
                    <div class="table-header">
                        <h6 class="fw-bold mb-0">{{__("app.report_sold_products")}}</h6>
                    </div>

                    {{-- Desktop table --}}
                    <table class="table table-sm report-table m-0 d-none d-sm-table">
                        <thead>
                            <tr>
                                <th class="ps-4">{{__("app.report_table_name")}}</th>
                                <th class="text-center">{{__("app.report_table_qty")}}</th>
                                <th class="text-end pe-4">{{__("app.report_table_sum")}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report['products'] as $productId => $items)
                            <tr>
                                <td class="ps-4 py-3 fw-bold">{{ $items->first()->product->name ?? 'Товар удален' }}</td>
                                <td class="text-center py-3">{{ $items->sum('quantity') }}</td>
                                <td class="text-end pe-4 py-3 fw-bold text-teal">{{ number_format($items->sum('total_price'), 2) }}</td>
                                <td class="text-center py-3">
    @php
        $isWeight = $items->first()->sale_type === 'weight';
        $totalQty = $items->sum('quantity');
    @endphp
    {{ $isWeight ? number_format($totalQty, 3) . ' кг' : (int)$totalQty . ' шт.' }}
</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Mobile list --}}
                    <div class="d-sm-none report-items-mobile">
                        @foreach($report['products'] as $productId => $items)
                        <div class="report-item-row">
                            <div class="fw-bold">{{ $items->first()->product->name ?? 'Товар удален' }}</div>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="text-muted small">{{ $items->sum('quantity') }} ед.</span>
                                <span class="fw-bold text-teal">{{ number_format($items->sum('total_price'), 2) }} TMT</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Grand total row --}}
                    <div class="table-grand-total">
                        <div class="grand-row">
                            <div class="grand-item">
                                <span class="grand-label">{{__("app.report_cash_shift")}}</span>
                                <span class="grand-value text-teal">{{ number_format($report['total_money'], 2) }} <small>TMT</small></span>
                            </div>
                            <div class="grand-item">
                                <span class="grand-label">{{__("app.report_items_shift")}}</span>
                                <span class="grand-value">{{ $report['total_items'] }} <small>ед.</small></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="report-footer">
                    <p class="text-muted small mb-1">{{__("app.report_cashier")}} {{ auth()->user()->name }}</p>
                    <p class="text-muted" style="font-size: 10px;">{{ config('app.name') }} POS System — {{ date('Y') }}</p>
                </div>

            </div>
        </div>
    </main>
</div>

<style>
.desktop-app-layout { display: flex; width: 100%; overflow: hidden; }
.app-main { flex: 1; display: flex; flex-direction: column; background: #f4f7f6; min-width: 0; overflow: hidden; }


.main-header {
    height: 70px; background: white;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    flex-shrink: 0; display: flex; align-items: center;
    padding: 0 24px; gap: 16px;
}

.btn-back {
    width: 40px; height: 40px; min-width: 40px;
    border-radius: 12px; background: white;
    border: 1px solid rgba(0,0,0,0.08); color: #718096;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; transition: 0.2s; flex-shrink: 0;
}
.btn-back:hover { color: #E8722A; transform: translateX(-2px); }

.btn-print-action {
    background: #E8722A; color: white;
    font-weight: 700; border-radius: 12px; padding: 9px 20px;
    border: none; transition: 0.2s;
    display: flex; align-items: center; gap: 6px; white-space: nowrap;
}
.btn-print-action:hover { background: #0c5e66; transform: translateY(-1px); }

.workspace { flex: 1; overflow-y: auto; padding: 24px; }
.report-container { max-width: 860px; margin: 0 auto; }
.panel-card {
    background: white; border-radius: 20px;
    border: 1px solid rgba(0,0,0,0.04);
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    overflow: hidden;
}


.shift-times-card {
    display: flex; align-items: center;
    padding: 20px 28px; gap: 20px;
    border-left: 4px solid #E8722A;
    flex-wrap: wrap;
}
.shift-time-item { flex: 1; min-width: 140px; }
.shift-divider { font-size: 1.2rem; flex-shrink: 0; }
.time-label { display: block; font-size: 0.62rem; text-transform: uppercase; font-weight: 800; color: #a0aec0; letter-spacing: 0.5px; margin-bottom: 4px; }
.time-value { font-weight: 700; font-size: 0.95rem; color: #2d3748; }


.summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }

.summary-card {
    background: white; border-radius: 16px; padding: 20px;
    display: flex; align-items: center; gap: 14px;
    border: 1px solid rgba(0,0,0,0.04);
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}

.summary-icon {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; flex-shrink: 0;
}
.bg-teal-soft { background: rgba(16,122,132,0.1); }
.bg-green-soft { background: rgba(72,187,120,0.1); }
.bg-blue-soft { background: rgba(66,153,225,0.1); }
.text-teal { color: #E8722A; }

.summary-label { display: block; font-size: 0.65rem; text-transform: uppercase; font-weight: 800; color: #a0aec0; letter-spacing: 0.5px; margin-bottom: 4px; }
.summary-value { font-size: 1.2rem; font-weight: 800; color: #2d3748; }
.table-header { padding: 16px 20px; border-bottom: 1px solid #f4f7f7; }

.report-table thead th {
    background: #f8fafc; color: #a0aec0;
    font-size: 0.68rem; text-transform: uppercase; font-weight: 800;
    padding: 14px; border-bottom: 1px solid #edf2f7;
}
.report-table td { border-bottom: 1px solid #f8fafc; }
.report-table tbody tr:hover { background: #f8fbfb; }

.report-items-mobile { padding: 0; }
.report-item-row { padding: 14px 16px; border-bottom: 1px solid #f4f7f7; }
.report-item-row:last-child { border-bottom: none; }

.table-grand-total {
    padding: 20px 28px; margin: 0;
    background: rgba(16,122,132,0.04);
    border-top: 2px dashed #e2e8f0;
}
.grand-row { display: flex; gap: 32px; flex-wrap: wrap; }
.grand-item { display: flex; flex-direction: column; }
.grand-label { font-size: 0.65rem; text-transform: uppercase; font-weight: 800; color: #a0aec0; letter-spacing: 0.5px; margin-bottom: 4px; }
.grand-value { font-size: 1.2rem; font-weight: 800; color: #2d3748; }

.report-footer { text-align: center; padding: 20px; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }

@media (max-width: 1023px) {
    .summary-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 767px) {
    .app-main { overflow-y: auto; }
    .desktop-app-layout { overflow: auto; }
    .main-header { padding: 0 15px 0 70px; height: 60px; }
    .workspace { padding: 12px; }

    .summary-grid { grid-template-columns: 1fr; }
    .summary-card { padding: 14px; }

    .shift-times-card { padding: 16px; flex-direction: column; gap: 8px; align-items: flex-start; }
    .shift-divider { transform: rotate(90deg); }

    .table-grand-total { padding: 14px 16px; }
    .grand-row { gap: 16px; }
}

@media print {
    .sidebar-wrapper, .main-header, .d-print-none { display: none !important; }
    .desktop-app-layout { display: block; }
    .app-main { overflow: visible; }
    body { background: white; }
    .panel-card { box-shadow: none; border: 1px solid #eee; }
    .workspace { overflow: visible; padding: 0; }
}

</style>
@endsection