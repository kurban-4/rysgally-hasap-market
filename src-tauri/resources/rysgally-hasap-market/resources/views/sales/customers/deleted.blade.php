@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main">

        
        <header class="main-header">
            <div class="header-info">
                <i class="bi bi-trash3 text-light me-2 fs-5"></i>
                <div class="ms-1">
                    <h4 class="mb-0 fw-black">Deleted Transactions</h4>
                    <p class="text-muted small mb-0 d-none d-md-block">
                        Archive of deleted customer transactions
                    </p>
                </div>
            </div>

            <div class="header-stats ms-auto">
                <div class="mini-stat">
                    <span class="mini-label">Deleted Orders</span>
                    <span class="mini-value">{{ count($orders) }}</span>
                </div>
                <div class="mini-stat d-none d-md-flex">
                    <span class="mini-label">Archived Revenue</span>
                    <span class="mini-value orange">
                        {{ number_format($orders->sum('total_sum'), 2) }}
                        <small>TMT</small>
                    </span>
                </div>
            </div>

            <div class="system-status">
                <span class="d-none d-lg-inline fw-bold" style="font-size:.7rem;color:#666;">Read-only archive</span>
            </div>

            <a href="{{ route('sales.customers.index') }}"
               class="btn-export d-none d-md-flex">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Active</span>
            </a>
        </header>

        
        <div class="workspace">
            <div class="orders-container">
                <div class="panel-card">

                    <div class="panel-header">
                        <h5 class="mb-0 fw-black">
                            <i class="bi bi-archive me-2 text-muted"></i>
                            Deleted Transaction Log
                        </h5>
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchInput"
                                   placeholder="Search deleted transactions..."
                                   class="search-input">
                        </div>
                    </div>

                    
                    <div class="orders-card-list d-md-none">
                        @forelse($orders as $order)
                        <div class="order-mobile-card">
                            <div class="order-mobile-left">
                                <div class="txn-id">
                                    <span class="hash">#</span>{{ ltrim($order->transaction_id, '#') }}
                                </div>
                                <div class="order-time">
                                    {{ \Carbon\Carbon::parse($order->order_time)->format('H:i · d M Y') }}
                                </div>
                                <div class="order-till">
                                    <i class="bi bi-cash-stack me-1"></i>
                                    {{ $order->till->name ?? '—' }}
                                </div>
                            </div>
                            <div class="order-mobile-right">
                                <div class="fw-black text-orange">
                                    {{ number_format($order->total_sum, 2) }}
                                    <small>TMT</small>
                                </div>
                                <span class="badge-done" style="background: #FFF5F5; color: #e53e3e;">Deleted</span>
                                <div class="mt-2">
                                    <form action="{{ route('sales.customers.restore', ltrim($order->transaction_id, '#')) }}" method="POST" style="width: 100%;">
                                        @csrf
                                        <button type="submit" class="btn-restore" style="width: 100%;">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="empty-state">
                            <i class="bi bi-folder-x"></i>
                            <p>No deleted transactions</p>
                        </div>
                        @endforelse
                    </div>

                    
                    <div class="table-scroll-container d-none d-md-block">
                        <table class="table pos-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Transaction ID</th>
                                    <th>Date & Time</th>
                                    <th>Till</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                @forelse($orders as $order)
                                <tr>
                                    <td class="ps-4">
                                        <span class="transaction-id">
                                            <span class="hash">#</span>{{ ltrim($order->transaction_id, '#') }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($order->order_time)->format('H:i · d M Y') }}
                                    </td>
                                    <td>
                                        <span class="till-badge">
                                            <i class="bi bi-cash-stack me-1"></i>
                                            {{ $order->till->name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-done" style="background: #FFF5F5; color: #e53e3e;">Deleted</span>
                                    </td>
                                    <td class="text-center fw-black text-orange">
                                        {{ number_format($order->total_sum, 2) }}
                                        <small class="text-muted fw-normal">TMT</small>
                                    </td>
                                    <td class="text-center pe-4">
                                        <form action="{{ route('sales.customers.restore', ltrim($order->transaction_id, '#')) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="btn-restore">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        No deleted transactions
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('#ordersTableBody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
            document.querySelectorAll('.order-mobile-card').forEach(card => {
                card.style.display = card.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        });
    }
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&display=swap');

:root {
    --ora: #E8722A;
    --ora-dark: #C4561A;
    --ora-light: #FFF0E6;
    --ora-glow: rgba(232,114,42,0.2);
    --bg: #FBF7F3;
    --card: #FFFFFF;
    --border: #E8DDD0;
    --text: #4A3520;
    --muted: #997A6A;
    --shadow: 0 2px 12px rgba(74, 53, 32, 0.08);
}

* { box-sizing: border-box; }
body { font-family: 'Sora', sans-serif; color: var(--text); background: var(--bg); }

.main-header {
    display: flex; align-items: center; padding: 0 24px; gap: 16px; flex-shrink: 0;
    height: 80px; background: var(--card); border-bottom: 1px solid var(--border);
    box-shadow: var(--shadow);
}
.header-info { display: flex; align-items: center; }
.fw-black { font-family: 'Sora', sans-serif; font-weight: 800; color: var(--text); }

.header-stats { display: flex; gap: 20px; }
.mini-stat { display: flex; flex-direction: column; border-left: 2px solid var(--border); padding-left: 16px; }
.mini-label { font-size: 0.6rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.8px; color: var(--muted); }
.mini-value { font-family: 'Sora', sans-serif; font-size: 1.05rem; font-weight: 800; color: var(--text); }
.mini-value.orange { color: var(--ora); }
.mini-value small { font-size: 0.62rem; font-weight: 600; color: var(--muted); }

.system-status { display: flex; align-items: center; gap: 7px; }

.btn-export {
    display: flex; align-items: center; gap: 7px;
    background: var(--ora-light); color: var(--ora);
    border: 1.5px solid rgba(232,114,42,0.25); border-radius: 11px;
    padding: 8px 14px; font-size: 0.78rem; font-weight: 700;
    text-decoration: none; transition: 0.18s; white-space: nowrap;
}
.btn-export:hover { background: var(--ora); color: white; }

.workspace { flex: 1; overflow-y: auto; padding: 20px 24px; }
.orders-container { max-width: 1200px; margin: 0 auto; height: 100%; }

.panel-card {
    background: var(--card); border-radius: 20px;
    border: 1px solid var(--border); box-shadow: var(--shadow);
    overflow: hidden;
}
.panel-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 20px; border-bottom: 1px solid var(--border); gap: 16px;
}
.panel-header h5 { margin: 0; }

.search-box { position: relative; }
.search-input { width: 250px; padding: 8px 12px 8px 32px; border-radius: 9px; border: 1px solid var(--border); font-size: 0.87rem; }
.search-input:focus { border-color: var(--ora); box-shadow: 0 0 0 3px var(--ora-glow); background: white; }
.search-box i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 0.9rem; }

.table-scroll-container { overflow-x: auto; }
.pos-table { width: 100%; border-collapse: collapse; }
.pos-table thead th {
    background: #FBF7F3; color: var(--muted);
    font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 800;
    padding: 14px 16px; border-bottom: 1px solid var(--border);
    white-space: nowrap; position: sticky; top: 0; z-index: 2;
}
.pos-table tbody tr { transition: background 0.18s; }
.pos-table tbody tr:hover { background: #FFF8F3; }
.pos-table tbody td {
    padding: 14px 16px; border-bottom: 1px solid #F5EDE4;
    font-size: 0.88rem; color: var(--text);
}
.pos-table tbody tr:last-child td { border-bottom: none; }

.transaction-id { font-family: 'JetBrains Mono', monospace; font-size: 0.82rem; font-weight: 600; color: #6B4E2A; }
.hash { color: var(--ora); font-weight: 800; margin-right: 2px; }

.till-badge {
    display: inline-flex; align-items: center;
    background: rgba(232,114,42,0.08); color: var(--ora);
    font-size: 0.75rem; font-weight: 700;
    padding: 4px 10px; border-radius: 50px;
    white-space: nowrap;
}

.badge-done {
    display: inline-flex; align-items: center;
    background: #E8F5E9; color: #2E7D32;
    font-size: 0.72rem; font-weight: 700; padding: 4px 10px; border-radius: 50px;
}

.btn-detail {
    display: inline-flex; align-items: center; gap: 4px;
    background: var(--ora-light); color: var(--ora);
    border: 1px solid rgba(232,114,42,0.3); border-radius: 8px;
    padding: 6px 12px; font-size: 0.75rem; font-weight: 700;
    text-decoration: none; cursor: pointer; transition: 0.18s cubic-bezier(0.34, 1.56, 0.64, 1); white-space: nowrap;
    font-family: inherit; border: 1.5px solid rgba(232,114,42,0.2); border-radius: 9px;
}
.btn-detail:hover { background: var(--ora); color: white; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(232,114,42,0.25); }
.btn-detail:active { transform: translateY(0); }

.btn-restore {
    display: inline-flex; align-items: center; gap: 4px;
    background: #E8F5E9; color: #2E7D32;
    border: 1.5px solid #C8E6C9; border-radius: 9px;
    padding: 6px 12px; font-size: 0.78rem; font-weight: 700;
    text-decoration: none; cursor: pointer; transition: 0.18s cubic-bezier(0.34, 1.56, 0.64, 1); white-space: nowrap;
    font-family: inherit;
}
.btn-restore:hover { background: #2E7D32; color: white; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(46,125,50,0.3); }
.btn-restore:active { transform: translateY(0); }

.orders-card-list { display: flex; flex-direction: column; }
.order-mobile-card {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 14px 14px; border-bottom: 1px solid var(--border); gap: 12px;
}
.order-mobile-card:last-child { border-bottom: none; }
.txn-id { font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; font-weight: 700; color: #6B4E2A; }
.order-time { font-size: 0.72rem; color: var(--muted); margin-top: 3px; }
.order-till { font-size: 0.68rem; color: var(--ora); font-weight: 600; margin-top: 2px; }
.order-mobile-right { text-align: right; }
.order-mobile-right .fw-black { font-size: 1rem; }

.empty-state { text-align: center; padding: 48px 20px; color: var(--muted); }
.empty-state i { font-size: 2.2rem; opacity: 0.3; display: block; margin-bottom: 10px; }
.empty-state p { font-weight: 700; margin: 0; }

::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-thumb { background: #D4C4B0; border-radius: 10px; }
::-webkit-scrollbar-track { background: transparent; }

@media (max-width: 1023px) { .main-header { padding: 0 16px; height: 70px; } }
@media (max-width: 767px) {
    .desktop-app-layout { position: relative; inset: auto; flex-direction: column; min-height: 100vh; height: auto !important; overflow: auto !important; }
    .desktop-app-layout .sidebar-wrapper { position: fixed !important; }
    .app-main { height: auto !important; overflow: auto !important; }
    .main-header { padding: 0 14px 0 68px; height: 60px; }
    .workspace { padding: 12px; overflow: visible; }
    .orders-container { height: auto; }
}
</style>
@endsection
