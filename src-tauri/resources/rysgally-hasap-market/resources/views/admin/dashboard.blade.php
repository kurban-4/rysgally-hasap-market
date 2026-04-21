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
                    <small class="text-muted">TMT</small>
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
                    <small class="text-muted">TMT</small>
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
                    <small class="text-muted">TMT</small>
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
                    <small class="text-muted">TMT</small>
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
                <small class="text-muted">TMT</small>
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
                <small class="text-muted">TMT</small>
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
                {{ number_format($expense->amount, 2) }} TMT
            </span>
        </div>
        @empty
        <p class="text-muted fw-bold mb-0 text-center py-3">{{ __('app.admin_no_expenses') }}</p>
        @endforelse

        @if($expenses->count() > 0)
        <div class="d-flex justify-content-end mt-3">
            <span class="fw-black" style="color: #ef4444; font-size: 1.1rem;">
                {{ __('app.admin_total') }} {{ number_format($totalExpenses, 2) }} TMT
            </span>
        </div>
        @endif
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
</style>

@endsection