@extends('layouts.app')

@section('content')
@include('app.navbar')
<div class="container-fluid px-4 py-4" style="background: #f4f7f6; min-height: 100vh;">

    {{-- ШАПКА --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('boss.dashboard') }}" class="btn bg-white border-0 rounded-3 p-3 shadow-sm">
            <i class="bi bi-arrow-left fs-5" style="color: #E8722A;"></i>
        </a>
        <div class="p-4 bg-white rounded-4 shadow-sm flex-grow-1 d-flex justify-content-between align-items-center" style="border-left: 6px solid #E8722A;">
            <div>
                <h2 class="fw-black mb-0" style="color: #1a3a3a;">{{ $till->name }}</h2>
                <p class="text-muted small fw-bold text-uppercase mb-0 mt-1" style="letter-spacing: 0.1em;">{{ __('app.till_detail_subtitle') }}</p>
            </div>
            <div class="p-3 rounded-4 text-white d-none d-md-flex align-items-center justify-content-center shadow-sm" style="background: #E8722A; width: 52px; height: 52px;">
                <i class="bi bi-calculator-fill fs-4"></i>
            </div>
        </div>
    </div>

    {{-- КАРТОЧКИ СТАТИСТИКИ --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <a href="{{ route('boss.till.show', $till->id) }}?from={{ now()->format('Y-m-d') }}&to={{ now()->format('Y-m-d') }}"
                class=" text-decoration-none "
                style="background: {{ request('from') == now()->format('Y-m-d') && request('to') == now()->format('Y-m-d') ? '#E8722A' : '#e0f2f4' }}; color: {{ request('from') == now()->format('Y-m-d') && request('to') == now()->format('Y-m-d') ? 'white' : '#E8722A' }};">
                <div class="card bg-white border-0 rounded-4 p-4 shadow-sm h-100" style="border-top: 4px solid #E8722A !important;">
                    <p class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                        {{ __('app.admin_today') }}
                        <span class="d-block text-lowercase fw-normal mt-1" style="font-size: 0.65rem; letter-spacing: normal;">({{ now()->format('d.m.Y') }})</span>
                    </p>
                    <h3 class="fw-black mb-0" style="color: #E8722A;">{{ number_format($dayEarned, 2) }}</h3>
                    <small class="text-muted">{{ __('app.currency_tmt') }}</small>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('boss.till.show', $till->id) }}?from={{ now()->startOfWeek()->format('Y-m-d') }}&to={{ now()->endOfWeek()->format('Y-m-d') }}"
                class="text-decoration-none "
                style="background: #fef3c7; color: #92400e;">
                <div class="card bg-white border-0 rounded-4 p-4 shadow-sm h-100" style="border-top: 4px solid #f59e0b !important;">
                    <p class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                        {{ __('app.admin_week') }}
                        <span class="d-block text-lowercase fw-normal mt-1" style="font-size: 0.65rem; letter-spacing: normal;">({{ now()->startOfWeek()->format('d.m.Y') }} — {{ now()->endOfWeek()->format('d.m.Y') }})</span>
                    </p>
                    <h3 class="fw-black mb-0" style="color: #f59e0b;">{{ number_format($weekEarned, 2) }}</h3>
                    <small class="text-muted">{{ __('app.currency_tmt') }}</small>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('boss.till.show', $till->id) }}?from={{ now()->startOfMonth()->format('Y-m-d') }}&to={{ now()->endOfMonth()->format('Y-m-d') }}"
                class="text-decoration-none "
                style="background: #ede9fe; color: #5b21b6;">
                <div class="card bg-white border-0 rounded-4 p-4 shadow-sm h-100" style="border-top: 4px solid #8b5cf6 !important;">
                    <p class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                        {{ __('app.admin_month') }}
                        <span class="d-block text-lowercase fw-normal mt-1" style="font-size: 0.65rem; letter-spacing: normal;">({{ now()->startOfMonth()->format('d.m.Y') }} — {{ now()->endOfMonth()->format('d.m.Y') }})</span>
                    </p>
                    <h3 class="fw-black mb-0" style="color: #8b5cf6;">{{ number_format($monthEarned, 2) }}</h3>
                    <small class="text-muted">{{ __('app.currency_tmt') }}</small>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('boss.till.show', $till->id) }}"
                class=" text-decoration-none "
                style="background: #f1f5f9; color: #475569;">
                <div class="card bg-white border-0 rounded-4 p-4 shadow-sm h-100" style="border-top: 4px solid #64748b !important;">
                    <p class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                        {{ __('app.revenue_all_time') }}
                        <span class="d-block text-lowercase fw-normal mt-1" style="font-size: 0.65rem; letter-spacing: normal;">(до {{ now()->format('d.m.Y') }})</span>
                    </p>
                    <h3 class="fw-black mb-0" style="color: #1e293b;">{{ number_format($totalEarned, 2) }}</h3>
                    <small class="text-muted">{{ __('app.currency_tmt') }}</small>
                </div>
            </a>
        </div>
    </div>

    {{-- ФИЛЬТР ПО ДАТЕ --}}
    <div class="card bg-white border-0 rounded-4 shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('boss.till.show', $till->id) }}">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-auto">
                    <p class="fw-black mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-funnel-fill" style="color: #E8722A;"></i>
                        {{ __('app.till_filter_dates') }}
                    </p>
                </div>
                <div class="col-12 col-md-3">
                    <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.08em;">От</label>
                    <input type="date" name="from" value="{{ request('from') }}"
                        class="form-control border-0 rounded-3 fw-bold"
                        style="background: #f4f7f6; color: #E8722A;">
                </div>
                <div class="col-12 col-md-3">
                    <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.08em;">До</label>
                    <input type="date" name="to" value="{{ request('to') }}"
                        class="form-control border-0 rounded-3 fw-bold"
                        style="background: #f4f7f6; color: #E8722A;">
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn fw-black rounded-3 px-4 text-white" style="background: #E8722A;">
                        <i class="bi bi-search me-1"></i> {{ __('app.revenue_search') }}
                    </button>
                    <a href="{{ route('boss.till.export', $till->id) }}?from={{ request('from') }}&to={{ request('to') }}"
   class="btn btn-outline-success fw-bold rounded-3 px-3"
   title="Export to Excel">
    <i class="bi bi-file-earmark-excel-fill"></i>
</a>
                    @if(request('from') || request('to'))
                    <a href="{{ route('boss.till.show', $till->id) }}" class="btn btn-light fw-bold rounded-3 px-4">
                        <i class="bi bi-x-lg me-1"></i> {{ __('app.revenue_reset') }}
                    </a>
                    @endif
                </div>
                

                {{-- БЫСТРЫЕ ФИЛЬТРЫ --}}
                <div class="col-12 d-flex gap-2 flex-wrap pt-1">
                    <a href="{{ route('boss.till.show', $till->id) }}?from={{ now()->format('Y-m-d') }}&to={{ now()->format('Y-m-d') }}"
                        class="badge rounded-pill px-3 py-2 text-decoration-none fw-bold transition-hover"
                        style="background: {{ request('from') == now()->format('Y-m-d') && request('to') == now()->format('Y-m-d') ? '#E8722A' : '#e0f2f4' }}; color: {{ request('from') == now()->format('Y-m-d') && request('to') == now()->format('Y-m-d') ? 'white' : '#E8722A' }};">
                        {{ __('app.admin_today') }} ({{ now()->format('d.m') }})
                    </a>
                    <a href="{{ route('boss.till.show', $till->id) }}?from={{ now()->startOfWeek()->format('Y-m-d') }}&to={{ now()->endOfWeek()->format('Y-m-d') }}"
                        class="badge rounded-pill px-3 py-2 text-decoration-none fw-bold transition-hover"
                        style="background: #fef3c7; color: #92400e;">
                        {{ __('app.revenue_this_week') }} ({{ now()->startOfWeek()->format('d.m') }}-{{ now()->endOfWeek()->format('d.m') }})
                    </a>
                    <a href="{{ route('boss.till.show', $till->id) }}?from={{ now()->startOfMonth()->format('Y-m-d') }}&to={{ now()->endOfMonth()->format('Y-m-d') }}"
                        class="badge rounded-pill px-3 py-2 text-decoration-none fw-bold transition-hover"
                        style="background: #ede9fe; color: #5b21b6;">
                        {{ __('app.revenue_this_month') }} ({{ now()->format('m.Y') }})
                    </a>
                    <a href="{{ route('boss.till.show', $till->id) }}"
                        class="badge rounded-pill px-3 py-2 text-decoration-none fw-bold transition-hover"
                        style="background: #f1f5f9; color: #475569;">
                        {{ __('app.revenue_all_time') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
    {{-- ПЕРИОД --}}
    @if(request('from') || request('to'))
    <div class="alert border-0 rounded-4 mb-4 d-flex align-items-center gap-3 shadow-sm" style="background: #e0f2f4;">
        <i class="bi bi-calendar-check fs-5" style="color: #E8722A;"></i>
        <div>
            <span class="fw-black" style="color: #E8722A;">Показаны результаты:</span>
            <span class="text-dark fw-bold ms-2">
                {{ request('from') ? \Carbon\Carbon::parse(request('from'))->format('d.m.Y') : __('app.revenue_period_start') }}
                —
                {{ request('to') ? \Carbon\Carbon::parse(request('to'))->format('d.m.Y') : __('app.revenue_period_end') }}
            </span>
            <span class="ms-3 fw-black" style="color: #E8722A;">{{ __('app.revenue_period_total') }} {{ number_format($filteredTotal, 2) }} TMT</span>
        </div>
    </div>
    @endif

    {{-- ТАБЛИЦА ПРОДАЖ --}}
    <div class="card bg-white border-0 rounded-4 shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-black mb-0" style="color: #1a3a3a;">
                {{ __('app.till_sold_products') }}
                <span class="text-muted fw-normal fs-6 ms-2">
                    @if(request('from') || request('to'))
                    {{ __('app.till_selected_period') }}
                    @else
                    {{ __('app.till_all_time') }}
                    @endif
                </span>
            </h5>
            <span class="badge rounded-pill px-3 py-2 fw-bold" style="background: #e0f2f4; color: #E8722A;">
                {{ count($soldproducts) }} {{ __('app.till_items_count', ['count' => count($soldproducts)]) }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th class="ps-3 py-3 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.08em; width: 50px;">#</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.08em;">{{ __('app.till_product_name') }}</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-center" style="letter-spacing: 0.08em;">
                            {{ __('app.admin_today') }}
                            <div class="text-lowercase fw-normal mt-1" style="font-size: 0.65rem; letter-spacing: normal;">{{ now()->format('d.m') }}</div>
                        </th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-center" style="letter-spacing: 0.08em;">
                            {{ __('app.admin_week') }}
                            <div class="text-lowercase fw-normal mt-1" style="font-size: 0.65rem; letter-spacing: normal;">{{ now()->startOfWeek()->format('d.m') }}-{{ now()->endOfWeek()->format('d.m') }}</div>
                        </th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-center" style="letter-spacing: 0.08em;">
                            {{ __('app.admin_month') }}
                            <div class="text-lowercase fw-normal mt-1" style="font-size: 0.65rem; letter-spacing: normal;">{{ now()->format('m.Y') }}</div>
                        </th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-center" style="letter-spacing: 0.08em;">{{ __('app.till_quantity') }}</th>
                        <th class="pe-3 py-3 text-muted small fw-bold text-uppercase text-end" style="letter-spacing: 0.08em;">{{ __('app.till_total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($soldproducts as $med)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td class="ps-3 py-3 text-muted small fw-bold">{{ $loop->iteration }}</td>
                        <td class="py-3">
                            <span class="fw-black text-dark">{{ $med['name'] }}</span>
                        </td>
                        <td class="py-3 text-center">
                            @if($med['day_qty'] > 0)
                            <span class="badge rounded-pill px-2 py-1 fw-bold" style="background: #e0f2f4; color: #E8722A; font-size: 11px;">
                                {{ $med['day_qty'] }} {{ __('app.till_pieces') }}
                            </span>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            @if($med['week_qty'] > 0)
                            <span class="badge rounded-pill px-2 py-1 fw-bold" style="background: #fef3c7; color: #92400e; font-size: 11px;">
                                {{ $med['week_qty'] }} {{ __('app.till_pieces') }}
                            </span>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            @if($med['month_qty'] > 0)
                            <span class="badge rounded-pill px-2 py-1 fw-bold" style="background: #ede9fe; color: #5b21b6; font-size: 11px;">
                                {{ $med['month_qty'] }} {{ __('app.till_pieces') }}
                            </span>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge rounded-pill px-2 py-1 fw-bold" style="background: #f1f5f9; color: #475569; font-size: 11px;">
                                {{ $med['quantity'] }} {{ __('app.till_pieces') }}
                            </span>
                        </td>
                        <td class="pe-3 py-3 text-end fw-black" style="color: #E8722A; font-size: 1rem;">
                            {{ number_format($med['total'], 2) }} {{ __('app.currency_tmt') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox text-muted d-block mb-2" style="font-size: 2rem; opacity: 0.4;"></i>
                            <span class="text-muted fw-bold">{{ __('app.till_no_sales') }}</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($soldproducts) > 0)
                <tfoot>
                    <tr style="border-top: 2px solid #E8722A; background: #f4f7f6;">
                        <td colspan="6" class="ps-3 py-3 fw-black text-end text-uppercase" style="letter-spacing: 0.08em; color: #475569;">{{ __('app.till_total') }}:</td>
                        <td class="pe-3 py-3 text-end fw-black" style="color: #E8722A; font-size: 1.2rem;">
                            {{ number_format($soldproducts->sum('total'), 2) }} {{ __('app.currency_tmt') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- FINANCIAL SUMMARY --}}
    @if(count($soldproducts) > 0)
    <div class="row g-3 mt-4">
        <div class="col-12">
            <div class="card bg-white border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-black mb-4" style="color: #1a3a3a;">
                        <i class="bi bi-cash-stack me-2" style="color: #E8722A;"></i>
                        {{ __('app.till_financial_summary') }}
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <div class="financial-card received">
                                <div class="financial-icon">
                                    <i class="bi bi-arrow-down-circle"></i>
                                </div>
                                <div class="financial-content">
                                    <div class="financial-label">{{ __('app.till_received_price') }}</div>
                                    <div class="financial-value">{{ number_format($totalReceivedPrice, 2) }}</div>
                                    <div class="financial-unit">{{ __('app.currency_tmt') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="financial-card selling">
                                <div class="financial-icon">
                                    <i class="bi bi-arrow-up-circle"></i>
                                </div>
                                <div class="financial-content">
                                    <div class="financial-label">{{ __('app.till_selling_price') }}</div>
                                    <div class="financial-value">{{ number_format($totalSellingPrice, 2) }}</div>
                                    <div class="financial-unit">{{ __('app.currency_tmt') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="financial-card profit">
                                <div class="financial-icon">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                                <div class="financial-content">
                                    <div class="financial-label">{{ __('app.till_net_profit') }}</div>
                                    <div class="financial-value {{ $netProfit >= 0 ? 'positive' : 'negative' }}">
                                        {{ number_format($netProfit, 2) }}
                                    </div>
                                    <div class="financial-unit">{{ __('app.currency_tmt') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="financial-card margin">
                                <div class="financial-icon">
                                    <i class="bi bi-percent"></i>
                                </div>
                                <div class="financial-content">
                                    <div class="financial-label">{{ __('app.till_profit_margin') }}</div>
                                    <div class="financial-value {{ $profitMargin >= 0 ? 'positive' : 'negative' }}">
                                        {{ number_format($profitMargin, 1) }}%
                                    </div>
                                    <div class="financial-unit">{{ __('app.till_percentage') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
    .transition-hover {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .transition-hover:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
    }

    input[type="date"]:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(16, 122, 132, 0.15);
    }

    /* FINANCIAL CARDS */
    .financial-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        transition: all 0.2s ease;
    }
    
    .financial-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .financial-card.received {
        border-left: 4px solid #3b82f6;
        background: rgba(59, 130, 246, 0.05);
    }
    
    .financial-card.selling {
        border-left: 4px solid #10b981;
        background: rgba(16, 185, 129, 0.05);
    }
    
    .financial-card.profit {
        border-left: 4px solid #f59e0b;
        background: rgba(245, 158, 11, 0.05);
    }
    
    .financial-card.margin {
        border-left: 4px solid #8b5cf6;
        background: rgba(139, 92, 246, 0.05);
    }
    
    .financial-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    
    .financial-card.received .financial-icon {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }
    
    .financial-card.selling .financial-icon {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }
    
    .financial-card.profit .financial-icon {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    
    .financial-card.margin .financial-icon {
        background: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
    }
    
    .financial-content {
        flex: 1;
    }
    
    .financial-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 4px;
    }
    
    .financial-value {
        font-size: 1.5rem;
        font-weight: 800;
        font-family: 'JetBrains Mono', monospace;
        color: #1f2937;
        line-height: 1;
    }
    
    .financial-value.positive {
        color: #10b981;
    }
    
    .financial-value.negative {
        color: #ef4444;
    }
    
    .financial-unit {
        font-size: 0.875rem;
        font-weight: 600;
        color: #9ca3af;
        margin-top: 2px;
    }
    
    @media (max-width: 767px) {
        .financial-card {
            padding: 16px;
            gap: 12px;
        }
        
        .financial-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        
        .financial-value {
            font-size: 1.25rem;
        }
    }
        border: 1px solid #E8722A !important;
    }
</style>
@endsection