@extends('layouts.app')
@section('content')
@include('app.navbar')

<div class="shifts-page">

    {{-- HERO HEADER --}}
    <div class="page-hero">
        <div class="hero-content">
            <div class="hero-icon-wrap">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <h1 class="page-title">{{ __('app.shift_logs_title') }}</h1>
                <p class="page-sub">{{ now()->format('d.m.Y') }} — {{ __('app.shift_logs_subtitle') }}</p>
            </div>
            <a href="{{ route('boss.dashboard') }}" class="btn btn-outline-light">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
        <div class="hero-stats">
            <div class="hero-stat">
                <div class="hstat-icon active-icon">
                    <i class="bi bi-person-check"></i>
                </div>
                <div>
                    <div class="hstat-label">{{ __('app.shift_active_count') }}</div>
                    <div class="hstat-value text-success">{{ $activeCount }}</div>
                </div>
            </div>
            <div class="hero-stat">
                <div class="hstat-icon rev-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="hstat-label">{{ __('app.shift_total_revenue') }}</div>
                    <div class="hstat-value teal">{{ number_format($totalRevenue, 2) }} TMT</div>
                </div>
            </div>
            <div class="hero-stat">
                <a href="{{ route('boss.shifts.export') }}"
   class="hero-stat text-decoration-none"
   style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.15); padding:12px 18px; border-radius:14px; display:flex; align-items:center; gap:10px; color:white; font-weight:700;"
   title="Export to Excel">
    <i class="bi bi-file-earmark-excel-fill" style="font-size:1.3rem; color:#6ee7b7;"></i>
    <span style="font-size:0.78rem; opacity:0.85;">Export</span>
</a>
            </div>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="table-wrap">

        {{-- Desktop table --}}
        <div class="shift-table-card d-none d-md-block">
            <table class="shift-table">
                <thead>
                    <tr>
                        <th>{{ __('app.shift_table_seller') }}</th>
                        <th>{{ __('app.shift_table_start') }}</th>
                        <th>{{ __('app.shift_table_end') }}</th>
                        <th>{{ __('app.shift_table_worked') }}</th>
                        <th>{{ __('app.shift_table_revenue') }}</th>
                        <th>{{ __('app.shift_table_status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                    <tr class="shift-row">
                        <td>
                            <div class="seller-cell">
                                <div class="seller-avatar">
                                    {{ strtoupper(substr($shift->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="seller-name">{{ $shift->user->name ?? '—' }}</div>
                                    <div class="seller-email">{{ $shift->user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="time-cell">
                                <i class="bi bi-play-circle-fill text-success me-2"></i>
                                {{ $shift->opened_at->format('d.m.Y') }}
                                <span class="time-badge">{{ $shift->opened_at->format('H:i') }}</span>
                            </div>
                        </td>
                        <td>
                            @if($shift->closed_at)
                                <div class="time-cell">
                                    <i class="bi bi-stop-circle-fill text-danger me-2"></i>
                                    {{ $shift->closed_at->format('d.m.Y') }}
                                    <span class="time-badge red">{{ $shift->closed_at->format('H:i') }}</span>
                                </div>
                            @else
                                <span class="still-working">
                                    <span class="pulse-dot"></span>
                                    {{ __('app.shift_status_working') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($shift->closed_at)
                                @php
                                    $h = $shift->opened_at->diffInHours($shift->closed_at);
                                    $m = $shift->opened_at->diffInMinutes($shift->closed_at) % 60;
                                @endphp
                                <div class="duration-pill">
                                    <i class="bi bi-hourglass-split me-1"></i>
                                    {{ $h }}ч {{ $m }}м
                                </div>
                            @else
                                @php
                                    $h = $shift->opened_at->diffInHours(now());
                                    $m = $shift->opened_at->diffInMinutes(now()) % 60;
                                @endphp
                                <div class="duration-pill active">
                                    <i class="bi bi-hourglass me-1"></i>
                                    {{ $h }}ч {{ $m }}м
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="revenue-cell">
                                {{ number_format($shift->total_revenue, 2) }}
                                <span class="rev-currency">TMT</span>
                            </div>
                        </td>
                        <td>
                            @if($shift->status === 'active')
                                <span class="status-badge status-active">
                                    <span class="status-dot"></span>
                                    Активна
                                </span>
                            @else
                                <span class="status-badge status-closed">
                                    <i class="bi bi-check2 me-1"></i>
                                    Закрыта
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            <p>Смен пока нет</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="mobile-shifts d-md-none">
            @forelse($shifts as $shift)
            <div class="mobile-shift-card">
                <div class="msc-top">
                    <div class="seller-cell">
                        <div class="seller-avatar">
                            {{ strtoupper(substr($shift->user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <div class="seller-name">{{ $shift->user->name ?? '—' }}</div>
                            <div class="seller-email">{{ $shift->user->email ?? '' }}</div>
                        </div>
                    </div>
                    @if($shift->status === 'active')
                        <span class="status-badge status-active">
                            <span class="status-dot"></span> Активна
                        </span>
                    @else
                        <span class="status-badge status-closed">
                            <i class="bi bi-check2 me-1"></i> Закрыта
                        </span>
                    @endif
                </div>
                <div class="msc-body">
                    <div class="msc-row">
                        <span class="msc-label">Начало</span>
                        <span class="msc-val">{{ $shift->opened_at->format('d.m.Y H:i') }}</span>
                    </div>
                    <div class="msc-row">
                        <span class="msc-label">Конец</span>
                        <span class="msc-val">
                            @if($shift->closed_at)
                                {{ $shift->closed_at->format('d.m.Y H:i') }}
                            @else
                                <span class="still-working"><span class="pulse-dot"></span> Работает</span>
                            @endif
                        </span>
                    </div>
                    <div class="msc-row">
                        <span class="msc-label">Отработано</span>
                        @if($shift->closed_at)
                            @php $h=$shift->opened_at->diffInHours($shift->closed_at); $m=$shift->opened_at->diffInMinutes($shift->closed_at)%60; @endphp
                            <span class="msc-val fw-bold">{{ $h }}ч {{ $m }}м</span>
                        @else
                            @php $h=$shift->opened_at->diffInHours(now()); $m=$shift->opened_at->diffInMinutes(now())%60; @endphp
                            <span class="msc-val text-success fw-bold">{{ $h }}ч {{ $m }}м</span>
                        @endif
                    </div>
                    <div class="msc-row">
                        <span class="msc-label">Выручка</span>
                        <span class="msc-val fw-bold teal">{{ number_format($shift->total_revenue, 2) }} TMT</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <p>Смен пока нет</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrap">
            {{ $shifts->links('pagination::bootstrap-5') }}
        </div>

    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap');

:root {
    --teal: #E8722A;
    --teal-dark: #0b5c64;
    --teal-light: #e0f4f5;
    --teal-mid: rgba(16,122,132,0.1);
    --bg: #f0f5f5;
    --white: #ffffff;
    --border: rgba(16,122,132,0.1);
    --text: #1a2e30;
    --muted: #6b8a8d;
    --success: #10b981;
    --danger: #ef4444;
    --shadow: 0 4px 20px rgba(16,122,132,0.08);
    --shadow-md: 0 8px 30px rgba(16,122,132,0.12);
}

*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); }

/* ── PAGE ── */
.shifts-page {
    min-height: 100vh;
    padding: 28px 28px 40px;
    max-width: 1300px;
    margin: 0 auto;
}

/* ── HERO ── */
.page-hero {
    background: linear-gradient(135deg, var(--teal) 0%, #0d9aa6 100%);
    border-radius: 22px;
    padding: 28px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    box-shadow: 0 12px 40px rgba(16,122,132,0.25);
    position: relative;
    overflow: hidden;
}
.page-hero::before {
    content: '';
    position: absolute; top: -50px; right: -50px;
    width: 200px; height: 200px; border-radius: 50%;
    background: rgba(255,255,255,0.06);
    pointer-events: none;
}
.hero-content { display: flex; align-items: center; gap: 18px; z-index: 1; }
.hero-icon-wrap {
    width: 56px; height: 56px; border-radius: 16px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: white; flex-shrink: 0;
}
.page-title { font-size: 1.6rem; font-weight: 800; color: white; margin: 0; line-height: 1.1; }
.page-sub { font-size: 0.78rem; color: rgba(255,255,255,0.65); font-weight: 600; margin: 4px 0 0; text-transform: uppercase; letter-spacing: 0.5px; }

.hero-stats { display: flex; gap: 20px; z-index: 1; }
.hero-stat {
    display: flex; align-items: center; gap: 12px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.15);
    padding: 12px 18px; border-radius: 14px;
    backdrop-filter: blur(8px);
}
.hstat-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.active-icon { background: rgba(16,185,129,0.2); color: #6ee7b7; }
.rev-icon { background: rgba(255,255,255,0.15); color: white; }
.hstat-label { font-size: 0.65rem; text-transform: uppercase; font-weight: 700; color: rgba(255,255,255,0.65); letter-spacing: 0.5px; margin-bottom: 2px; }
.hstat-value { font-size: 1.1rem; font-weight: 800; color: white; line-height: 1; }
.hstat-value.text-success { color: #6ee7b7 !important; }
.hstat-value.teal { color: white; }

/* ── TABLE CARD ── */
.table-wrap { }

.shift-table-card {
    background: var(--white);
    border-radius: 20px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    overflow: hidden;
    margin-bottom: 16px;
}

.shift-table {
    width: 100%;
    border-collapse: collapse;
}
.shift-table thead th {
    background: #f8fbfb;
    padding: 14px 20px;
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    font-weight: 800;
    color: var(--muted);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.shift-table thead th:first-child { padding-left: 28px; }
.shift-row { transition: background 0.15s; }
.shift-row:hover { background: #f5fafa; }
.shift-table td {
    padding: 16px 20px;
    border-bottom: 1px solid rgba(16,122,132,0.05);
    vertical-align: middle;
}
.shift-table td:first-child { padding-left: 28px; }
.shift-table tbody tr:last-child td { border-bottom: none; }

/* ── SELLER CELL ── */
.seller-cell { display: flex; align-items: center; gap: 12px; }
.seller-avatar {
    width: 38px; height: 38px; border-radius: 11px;
    background: var(--teal-light); color: var(--teal);
    font-size: 0.9rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; border: 2px solid rgba(16,122,132,0.12);
}
.seller-name { font-size: 0.9rem; font-weight: 700; color: var(--text); }
.seller-email { font-size: 0.72rem; color: var(--muted); }

/* ── TIME CELL ── */
.time-cell { display: flex; align-items: center; font-size: 0.85rem; font-weight: 600; color: var(--text); }
.time-badge {
    background: var(--teal-light); color: var(--teal);
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.75rem; font-weight: 600;
    padding: 3px 9px; border-radius: 6px; margin-left: 8px;
}
.time-badge.red { background: rgba(239,68,68,0.08); color: var(--danger); }

/* ── STILL WORKING ── */
.still-working {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: 0.82rem; font-weight: 600; color: var(--success);
}
.pulse-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--success);
    animation: pulse-anim 2s infinite;
    flex-shrink: 0;
}
@keyframes pulse-anim {
    0% { box-shadow: 0 0 0 0 rgba(16,185,129,0.6); }
    70% { box-shadow: 0 0 0 6px rgba(16,185,129,0); }
    100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
}

/* ── DURATION PILL ── */
.duration-pill {
    display: inline-flex; align-items: center;
    background: #f1f5f9; color: #475569;
    font-size: 0.78rem; font-weight: 700;
    padding: 5px 12px; border-radius: 50px;
    white-space: nowrap;
}
.duration-pill.active {
    background: rgba(16,185,129,0.1); color: var(--success);
}

/* ── REVENUE ── */
.revenue-cell {
    font-size: 1rem; font-weight: 800; color: var(--teal);
    font-family: 'JetBrains Mono', monospace;
}
.rev-currency { font-size: 0.7rem; font-weight: 600; color: var(--muted); margin-left: 4px; font-family: 'Plus Jakarta Sans', sans-serif; }

/* ── STATUS BADGES ── */
.status-badge {
    display: inline-flex; align-items: center;
    font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px;
    padding: 5px 12px; border-radius: 50px; white-space: nowrap;
}
.status-active { background: rgba(16,185,129,0.1); color: var(--success); }
.status-closed { background: #f1f5f9; color: #64748b; }
.status-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--success); margin-right: 6px;
    animation: pulse-anim 2s infinite;
    flex-shrink: 0;
}

/* ── EMPTY STATE ── */
.empty-state {
    text-align: center; padding: 60px 20px;
    color: var(--muted);
}
.empty-state i { font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 10px; }
.empty-state p { font-weight: 700; margin: 0; }

/* ── MOBILE CARDS ── */
.mobile-shifts { display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px; }
.mobile-shift-card {
    background: var(--white);
    border-radius: 16px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    overflow: hidden;
}
.msc-top {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px; border-bottom: 1px solid var(--border);
}
.msc-body { padding: 12px 16px; display: flex; flex-direction: column; gap: 10px; }
.msc-row { display: flex; justify-content: space-between; align-items: center; }
.msc-label { font-size: 0.68rem; text-transform: uppercase; font-weight: 800; color: var(--muted); letter-spacing: 0.5px; }
.msc-val { font-size: 0.85rem; font-weight: 600; color: var(--text); }

/* ── PAGINATION ── */
.pagination-wrap { display: flex; justify-content: center; padding: 8px 0; }
.pagination-wrap .page-link {
    border-radius: 50% !important; margin: 0 3px;
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    color: var(--teal); border: 1px solid var(--border);
    background: white; font-weight: 700; font-size: 0.85rem;
    transition: 0.2s;
}
.pagination-wrap .page-item.active .page-link {
    background: var(--teal) !important; color: white; border-color: var(--teal);
}
.pagination-wrap .page-link:hover { background: var(--teal-light); }

/* ── HELPERS ── */
.teal { color: var(--teal) !important; }
.fw-bold { font-weight: 700 !important; }

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
    .shifts-page { padding: 16px; }
    .page-hero { flex-direction: column; align-items: flex-start; gap: 20px; padding: 22px; }
    .hero-stats { width: 100%; }
    .hero-stat { flex: 1; }
    .page-title { font-size: 1.3rem; }
}
@media (max-width: 576px) {
    .hero-stats { flex-direction: column; gap: 10px; }
    .hero-stat { flex: none; }
}
</style>
@endsection