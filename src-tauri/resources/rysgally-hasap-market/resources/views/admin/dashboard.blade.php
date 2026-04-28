@extends('layouts.app')

@section('content')
@include('app.navbar')

<div class="container-fluid px-4 py-4" style="background: #f4f7f6; min-height: 100vh;">

    {{-- HEADER --}}
{{-- HEADER --}}
<div class="p-4 bg-white rounded-4 shadow-sm mb-4 d-flex justify-content-between align-items-center"
    style="border-left: 6px solid #E8722A;">
    <div>
        <h2 class="fw-black mb-0" style="color: #1a3a3a;">{{ __('app.admin_dashboard_title') }}</h2>
        <p class="text-muted small fw-bold text-uppercase mb-0 mt-1" style="letter-spacing: 0.1em;">
            {{ __('app.admin_dashboard_subtitle') }} — {{ now()->format('d.m.Y') }}
        </p>
    </div>
    <div class="d-flex align-items-center gap-3">

        {{-- КНОПКА СМЕНЫ --}}
        <a href="{{ route('admin.shifts') }}"
           class="btn fw-bold px-4 py-2 rounded-3 d-flex align-items-center gap-2"
           style="background: #e0f2f4; color: #E8722A; border: none; transition: 0.2s;"
           onmouseover="this.style.background='#E8722A'; this.style.color='white';"
           onmouseout="this.style.background='#e0f2f4'; this.style.color='#E8722A';">
            <i class="bi bi-clock-history"></i>
            <span class="d-none d-md-inline">{{ __('app.admin_shift_logs') }}</span>
        </a>

        <div class="p-3 rounded-4 text-white d-none d-md-flex align-items-center justify-content-center shadow-sm"
            style="background: #E8722A; width: 52px; height: 52px;">
            <i class="bi bi-speedometer2 fs-4"></i>
        </div>
    </div>
</div>

    {{-- STAT CARDS --}}
    <div class="row g-3 mb-4">

        {{-- TODAY --}}
        <div class="col-6 col-xl-3">
            <a href="{{ route('boss.revenue') }}" class="text-decoration-none">
                <div class="card bg-white border-0 rounded-4 p-4 shadow-sm h-100 transition-hover"
                    style="border-top: 4px solid #E8722A !important; cursor: pointer;">
                    <p class="text-muted small fw-bold text-uppercase mb-2">
                        {{ __('app.admin_today') }}
                        <span class="d-block text-lowercase fw-normal mt-1">
                            ({{ now()->format('d.m.Y') }})
                        </span>
                    </p>
                    <h3 class="fw-black mb-0" style="color: #E8722A;">{{ number_format($dayEarned, 2) }}</h3>
                    <small class="text-muted">{{ __('app.currency_tmt') }}</small>
                </div>
            </a>
        </div>

        {{-- WEEK --}}
        <div class="col-6 col-xl-3">
            <a href="{{ route('boss.revenue') }}" class="text-decoration-none">
                <div class="card bg-white border-0 rounded-4 p-4 shadow-sm h-100 transition-hover"
                    style="border-top: 4px solid #f59e0b !important; cursor: pointer;">
                    <p class="text-muted small fw-bold text-uppercase mb-2">
                        {{ __('app.admin_week') }}
                        <span class="d-block text-lowercase fw-normal mt-1">
                            ({{ now()->startOfWeek()->format('d.m.Y') }} — {{ now()->endOfWeek()->format('d.m.Y') }})
                        </span>
                    </p>
                    <h3 class="fw-black mb-0" style="color: #f59e0b;">{{ number_format($weekEarned, 2) }}</h3>
                    <small class="text-muted">{{ __('app.currency_tmt') }}</small>
                </div>
            </a>
        </div>

        {{-- MONTH --}}
        <div class="col-6 col-xl-3">
            <a href="{{ route('boss.revenue') }}" class="text-decoration-none">
                <div class="card bg-white border-0 rounded-4 p-4 shadow-sm h-100 transition-hover"
                    style="border-top: 4px solid #8b5cf6 !important; cursor: pointer;">
                    <p class="text-muted small fw-bold text-uppercase mb-2">
                        {{ __('app.admin_month') }}
                        <span class="d-block text-lowercase fw-normal mt-1">
                            ({{ now()->startOfMonth()->format('d.m.Y') }} — {{ now()->endOfMonth()->format('d.m.Y') }})
                        </span>
                    </p>
                    <h3 class="fw-black mb-0" style="color: #8b5cf6;">{{ number_format($monthEarned, 2) }}</h3>
                    <small class="text-muted">{{ __('app.currency_tmt') }}</small>
                </div>
            </a>
        </div>

        {{-- ALL TIME --}}
        <div class="col-6 col-xl-3">
            <a href="{{ route('boss.revenue') }}" class="text-decoration-none">
                <div class="card bg-white border-0 rounded-4 p-4 shadow-sm h-100 transition-hover"
                    style="border-top: 4px solid #64748b !important; cursor: pointer;">
                    <p class="text-muted small fw-bold text-uppercase mb-2">
                        {{ __('app.admin_all_time') }}
                        <span class="d-block text-lowercase fw-normal mt-1">
                            {{ __('app.admin_days_left_format', ['date' => now()->format('d.m.Y')]) }}
                        </span>
                    </p>
                    <h3 class="fw-black mb-0" style="color: #1e293b;">{{ number_format($totalEarned, 2) }}</h3>
                    <small class="text-muted">{{ __('app.currency_tmt') }}</small>
                </div>
            </a>
        </div>

    </div>

    {{-- EXPENSES + NET PROFIT --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="card bg-white border-0 rounded-4 p-4 shadow-sm h-100"
                style="border-top: 4px solid #ef4444 !important;">
                <p class="text-muted small fw-bold text-uppercase mb-2">{{ __('app.admin_expenses_today') }}</p>
                <h3 class="fw-black mb-0" style="color: #ef4444;">{{ number_format($totalExpenses, 2) }}</h3>
                <small class="text-muted">{{ __('app.currency_tmt') }}</small>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card bg-white border-0 rounded-4 p-4 shadow-sm h-100"
                style="border-top: 4px solid #10b981 !important;">
                <p class="text-muted small fw-bold text-uppercase mb-2">{{ __('app.admin_net_profit') }}</p>
                <h3 class="fw-black mb-0"
                    style="color: {{ $netProfit >= 0 ? '#10b981' : '#ef4444' }};">
                    {{ number_format($netProfit, 2) }}
                </h3>
                <small class="text-muted">{{ __('app.currency_tmt') }}</small>
            </div>
        </div>
    </div>

    {{-- TILLS TABLE --}}
    <div class="card bg-white border-0 rounded-4 shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-black mb-0" style="color: #1a3a3a;">{{ __('app.admin_tills') }}</h5>
            <span class="badge rounded-pill px-3 py-2 fw-bold"
                style="background: #e0f2f4; color: #E8722A;">
                {{ __('app.admin_tills_count', ['count' => $tills->count()]) }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th class="ps-3 py-3 text-muted small fw-bold text-uppercase">{{ __('app.admin_table_till_number') }}</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">{{ __('app.admin_table_till_name') }}</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-center">{{ __('app.admin_table_today') }}</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-center">{{ __('app.admin_table_week') }}</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-center">{{ __('app.admin_table_month') }}</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-end pe-3">{{ __('app.admin_table_all_time') }}</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-center">{{ __('app.admin_table_details') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($tills as $till)
                    <tr onclick="window.location='{{ route('boss.till.show', $till['id']) }}'"
                        style="cursor: pointer; border-bottom: 1px solid #f1f5f9;"
                        class="transition-hover">

                        <td class="ps-3 py-3 text-muted small fw-bold">{{ $loop->iteration }}</td>

                        <td class="py-3">
                            <span class="fw-black text-dark">{{ $till['name'] }}</span>
                        </td>

                        <td class="py-3 text-center">
                            <span class="badge rounded-pill px-2 py-1 fw-bold"
                                style="background: #e0f2f4; color: #E8722A; font-size: 11px;">
                                {{ number_format($till['day_rev'], 2) }} TMT
                            </span>
                        </td>

                        <td class="py-3 text-center">
                            <span class="badge rounded-pill px-2 py-1 fw-bold"
                                style="background: #fef3c7; color: #92400e; font-size: 11px;">
                                {{ number_format($till['week_rev'], 2) }} TMT
                            </span>
                        </td>

                        <td class="py-3 text-center">
                            <span class="badge rounded-pill px-2 py-1 fw-bold"
                                style="background: #ede9fe; color: #5b21b6; font-size: 11px;">
                                {{ number_format($till['month_rev'], 2) }} TMT
                            </span>
                        </td>

                        <td class="pe-3 py-3 text-end fw-black" style="color: #E8722A;">
                            {{ number_format($till['all_time_rev'], 2) }} TMT
                        </td>

                        <td class="py-3 text-center">
                            <i class="bi bi-bar-chart-line text-primary"></i>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox text-muted d-block mb-2" style="font-size: 2rem; opacity: 0.4;"></i>
                            <span class="text-muted fw-bold">{{ __('app.admin_no_tills') }}</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

    {{-- TODAY EXPENSES --}}
    <div class="card bg-white border-0 rounded-4 shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-black mb-0" style="color: #1a3a3a;">{{ __('app.admin_expenses_header') }}</h5>
            <a href="{{ route('boss.expense.index') }}"
                class="btn btn-sm fw-bold rounded-3 px-3"
                style="background: #f4f7f6; color: #E8722A;">
                <i class="bi bi-list-ul me-1"></i> {{ __('app.admin_all_expenses') }}
            </a>
        </div>

        @forelse($expenses as $expense)
        <div class="d-flex justify-content-between align-items-center py-2"
            style="border-bottom: 1px solid #f1f5f9;">
            <span class="fw-bold text-dark">{{ $expense->title }}</span>
            <span class="fw-black" style="color: #ef4444;">
                {{ number_format($expense->amount, 2) }} {{ __('app.currency_tmt') }}
            </span>
        </div>
        @empty
        <p class="text-muted fw-bold mb-0 text-center py-3">{{ __('app.admin_no_expenses') }}</p>
        @endforelse

        @if($expenses->count() > 0)
        <div class="d-flex justify-content-end mt-3">
            <span class="fw-black" style="color: #ef4444; font-size: 1.1rem;">
                {{ __('app.admin_total') }} {{ number_format($totalExpenses, 2) }} {{ __('app.currency_tmt') }}
            </span>
        </div>
        @endif
    </div>

    {{-- TOTAL FINANCIAL SUMMARY --}}
    <div class="card bg-white border-0 rounded-4 shadow-sm p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-black mb-0" style="color: #1a3a3a;">
                <i class="bi bi-cash-stack me-2" style="color: #E8722A;"></i>
                {{ __('app.till_financial_summary_all') }}
            </h5>
            <span class="badge bg-light text-dark fw-bold">
                <i class="bi bi-calculator me-1"></i>
                {{ __('app.till_all_tills') }}
            </span>
        </div>
        
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
                        <div class="financial-value {{ $totalNetProfit >= 0 ? 'positive' : 'negative' }}">
                            {{ number_format($totalNetProfit, 2) }}
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
                        <div class="financial-value {{ $totalProfitMargin >= 0 ? 'positive' : 'negative' }}">
                            {{ number_format($totalProfitMargin, 1) }}%
                        </div>
                        <div class="financial-unit">{{ __('app.till_percentage') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .transition-hover {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .transition-hover:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
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
</style>

@endsection