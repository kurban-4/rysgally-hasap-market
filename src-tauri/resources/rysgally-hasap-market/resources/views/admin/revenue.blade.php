@extends('layouts.app')

@section('content')
@include('app.navbar')
<div class="container-fluid px-4 py-4" style="background: #f4f7f6; min-height: 100vh;">

    {{-- ШАПКА --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('boss.dashboard') }}" class="btn bg-white border-0 rounded-3 p-3 shadow-sm transition-hover">
            <i class="bi bi-arrow-left fs-5" style="color: #E8722A;"></i>
        </a>
        <div class="p-4 bg-white rounded-4 shadow-sm flex-grow-1 d-flex justify-content-between align-items-center" style="border-left: 6px solid #E8722A;">
            <div>
                <h2 class="fw-black mb-0" style="color: #1a3a3a;">{{ __('app.revenue_all_time') }}</h2>
                <p class="text-muted small fw-bold text-uppercase mb-0 mt-1" style="letter-spacing: 0.1em;">{{ __('app.revenue_subtitle') }}</p>
            </div>
            <div class="p-3 rounded-4 text-white d-none d-md-flex align-items-center justify-content-center shadow-sm" style="background: #E8722A; width: 52px; height: 52px;">
                <i class="bi bi-cash-stack fs-4"></i>
            </div>
        </div>
    </div>

    {{-- STAT CARDS --}}
    <div class="row g-3 mb-4">

        {{-- TODAY --}}
        <div class="col-6 col-xl-3">

            <a href="{{ route('boss.revenue') }}?from={{ now()->format('Y-m-d') }}&to={{ now()->format('Y-m-d') }}"
                class="text-decoration-none "
                style="background: {{ request('from') == now()->format('Y-m-d') && request('to') == now()->format('Y-m-d') ? '#E8722A' : '#e0f2f4' }}; color: {{ request('from') == now()->format('Y-m-d') && request('to') == now()->format('Y-m-d') ? 'white' : '#E8722A' }};">
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
            <a href="{{ route('boss.revenue') }}?from={{ now()->startOfWeek()->format('Y-m-d') }}&to={{ now()->endOfWeek()->format('Y-m-d') }}"
                class=" text-decoration-none "
                style="background: #fef3c7; color: #92400e;">
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
            <a href="{{ route('boss.revenue') }}?from={{ now()->startOfMonth()->format('Y-m-d') }}&to={{ now()->endOfMonth()->format('Y-m-d') }}"
                class="text-decoration-none "
                style="background: #ede9fe; color: #5b21b6;">
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
            <a href="{{ route('boss.revenue') }}"
                class=" text-decoration-none "
                style="background: #f1f5f9; color: #475569;">
                <div class="card bg-white border-0 rounded-4 p-4 shadow-sm h-100 transition-hover"
                    style="border-top: 4px solid #64748b !important; cursor: pointer;">
                    <p class="text-muted small fw-bold text-uppercase mb-2">
                        Всё время
                        <span class="d-block text-lowercase fw-normal mt-1">
                            (до {{ now()->format('d.m.Y') }})
                        </span>
                    </p>
                    <h3 class="fw-black mb-0" style="color: #1e293b;">{{ number_format($totalEarned, 2) }}</h3>
                    <small class="text-muted">TMT</small>
                </div>
            </a>
        </div>

    </div>

    {{-- ФИЛЬТР ПО ДАТЕ --}}
    <div class="card bg-white border-0 rounded-4 shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('boss.revenue') }}">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-auto">
                    <p class="fw-black mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-funnel-fill" style="color: #E8722A;"></i>
                        {{ __('app.revenue_filter') }}
                    </p>
                </div>
                <div class="col-12 col-md-3">
                    <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.08em;">{{ __('app.revenue_from') }}</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control border-0 rounded-3 fw-bold" style="background: #f4f7f6; color: #E8722A;">
                </div>
                <div class="col-12 col-md-3">
                    <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.08em;">{{ __('app.revenue_to') }}</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control border-0 rounded-3 fw-bold" style="background: #f4f7f6; color: #E8722A;">
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn fw-black rounded-3 px-4 text-white" style="background: #E8722A;">
                        <i class="bi bi-search me-1"></i> {{ __('app.revenue_search') }}
                    </button>
<a href="{{ route('boss.revenue.export') }}?from={{ request('from') }}&to={{ request('to') }}"
   class="btn fw-bold rounded-3 px-3 btn-outline-success"
   title="Export to Excel">
    <i class="bi bi-file-earmark-excel-fill"></i>
</a>
                    @if(request('from') || request('to'))
                    <a href="{{ route('boss.revenue') }}" class="btn btn-light fw-bold rounded-3 px-4">
                        <i class="bi bi-x-lg me-1"></i> {{ __('app.revenue_reset') }}
                    </a>
                    @endif
                </div>

                {{-- БЫСТРЫЕ ФИЛЬТРЫ --}}
                <div class="col-12 d-flex gap-2 flex-wrap pt-1">
                    <a href="{{ route('boss.revenue') }}?from={{ now()->format('Y-m-d') }}&to={{ now()->format('Y-m-d') }}"
                        class="badge rounded-pill px-3 py-2 text-decoration-none fw-bold transition-hover"
                        style="background: {{ request('from') == now()->format('Y-m-d') && request('to') == now()->format('Y-m-d') ? '#E8722A' : '#e0f2f4' }}; color: {{ request('from') == now()->format('Y-m-d') && request('to') == now()->format('Y-m-d') ? 'white' : '#E8722A' }};">
                        {{ __('app.revenue_today_short') }} ({{ now()->format('d.m') }})
                    </a>
                    <a href="{{ route('boss.revenue') }}?from={{ now()->startOfWeek()->format('Y-m-d') }}&to={{ now()->endOfWeek()->format('Y-m-d') }}"
                        class="badge rounded-pill px-3 py-2 text-decoration-none fw-bold transition-hover"
                        style="background: #fef3c7; color: #92400e;">
                        {{ __('app.revenue_this_week') }} ({{ now()->startOfWeek()->format('d.m') }}-{{ now()->endOfWeek()->format('d.m') }})
                    </a>
                    <a href="{{ route('boss.revenue') }}?from={{ now()->startOfMonth()->format('Y-m-d') }}&to={{ now()->endOfMonth()->format('Y-m-d') }}"
                        class="badge rounded-pill px-3 py-2 text-decoration-none fw-bold transition-hover"
                        style="background: #ede9fe; color: #5b21b6;">
                        {{ __('app.revenue_this_month') }} ({{ now()->format('m.Y') }})
                    </a>
                    <a href="{{ route('boss.revenue') }}"
                        class="badge rounded-pill px-3 py-2 text-decoration-none fw-bold transition-hover"
                        style="background: #f1f5f9; color: #475569;">
                        Всё время
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
            <span class="fw-black" style="color: #E8722A;">{{ __('app.revenue_period_results') }}</span>
            <span class="text-dark fw-bold ms-2">
                {{ request('from') ? \Carbon\Carbon::parse(request('from'))->format('d.m.Y') : __('app.revenue_period_start') }}
                —
                {{ request('to') ? \Carbon\Carbon::parse(request('to'))->format('d.m.Y') : __('app.revenue_period_end') }}
            </span>
            <span class="ms-3 fw-black" style="color: #E8722A;">{{ __('app.revenue_period_total') }} {{ number_format($filteredTotal, 2) }} TMT</span>
        </div>
    </div>
    @endif

    {{-- TILLS TABLE --}}
    <div class="card bg-white border-0 rounded-4 shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-black mb-0" style="color: #1a3a3a;">{{ __('app.revenue_tills') }}</h5>
            <span class="badge rounded-pill px-3 py-2 fw-bold"
                style="background: #e0f2f4; color: #E8722A;">
                {{ $tills->count() }} {{ __('app.revenue_tills_count', ['count' => $tills->count()]) }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th class="ps-3 py-3 text-muted small fw-bold text-uppercase">#</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">{{ __('app.admin_table_till_name') }}</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-center">{{ __('app.admin_today') }}</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-center">{{ __('app.admin_week') }}</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-center">{{ __('app.admin_month') }}</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-end pe-3">{{ __('app.admin_all_time') }}</th>
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
                            <span class="text-muted fw-bold">{{ __('app.revenue_no_tills') }}</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
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

    input[type="date"]:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(16, 122, 132, 0.15);
        border: 1px solid #E8722A !important;
    }
</style>
@endsection