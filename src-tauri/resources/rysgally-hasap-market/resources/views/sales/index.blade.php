@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
@include('app.navbar')
   
    <main class="app-main">
        @if(session('error'))
    <div class="alert alert-danger mx-4 mt-3">{{ session('error') }}</div>
@endif
        @if(session('success'))
    <div class="alert alert-success mx-4 mt-3">
        {{ session('success') }}
    </div>
@endif
        <header class="main-header">
            {{-- Mobile: hamburger spacer --}}
            <div class="mobile-header-spacer d-none"></div>
            
            <div class="stat-card">
                <div class="stat-icon bg-teal-light"><i class="bi bi-currency-dollar text-teal"></i></div>
                <div class="stat-data">
                    <span class="label">{{ __('app.revenue_today') }}</span>
                    <span class="value">{{ number_format($totalMoney ?? 0, 2) }} <small>TMT</small></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon bg-blue-light"><i class="bi bi-cart-check text-primary"></i></div>
                <div class="stat-data">
                    <span class="label">{{ __('app.sold_today') }}</span>
                    <span class="value">{{ $salesCount ?? 0 }} <small>шт.</small></span>
                </div>
            </div>

            <div class="stat-card stat-card-hide-mobile">
                <div class="stat-icon bg-red-light"><i class="bi bi-exclamation-octagon text-danger"></i></div>
                <div class="stat-data">
                    <span class="label">{{__("app.stat_low_stock_pos")}}</span>
                    <span class="value text-danger">{{ \App\Models\Storage::where('quantity', '<', 10)->count() }} <small>{{__("app.unit_pcs")}}.</small></span>
                </div>
            </div>

            <div class="system-status ms-auto">
                <span class="dot pulse"></span>
                <span class="status-label">{{__("app.status_system_ready")}}</span>
            </div>
        </header>

        <div class="workspace">
            
            <div class="workspace-left">
                <div class="scanner-panel panel-card">
                    <form id="main-sales-form" class="scanner-form">
                        @csrf


                        <div class="d-flex gap-2">
    <input type="number" name="quantity" id="manual-qty" class="form-control" value="1" step="0.001" style="width: 80px; border-radius: 14px; border: 2px solid #cbd5e0; font-weight: bold; text-align: center;">
    
    <div class="barcode-wrapper flex-grow-1">
        <i class="bi bi-upc-scan scan-icon"></i>
        <input type="text" name="barcode" id="barcode-focus" class="barcode-input" autofocus required placeholder="{{ __('app.scan_barcode_placeholder') }}" autocomplete="off">
        <button type="submit" class="btn-submit-scan">
            {{ __('app.to_receipt') }} <i class="bi bi-arrow-return-left ms-1"></i>
        </button>
    </div>
</div>
                    </form>
                </div>

                <div class="table-panel panel-card">
                    <div class="panel-header">
                        <h5>{{ __('app.current_receipt') }}</h5>
                        <span class="badge-custom"><strong id="last-scan-display">{{ __('app.receipt_items', ['count' => count($cart)]) }}</strong></span>
                    </div>
                    <div class="table-scroll-container">
                        <table class="table pos-table">
                            <thead>
                                <tr>
                                    <th>{{__("app.table_preparation")}}</th>
                                    <th class="text-center">{{__("app.table_quantity_short")}} / ТИП</th>
                                    <th class="text-end">{{__("app.table_price")}}</th>
                                    <th class="text-center"><i class="bi bi-x-circle"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cart as $id => $item)
                                <tr>
                                    <td>
                                        <div class="med-name">
                                            <i class="bi bi-box-seam text-teal me-2"></i>
                                            {{ $item['name'] }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" 
                                               data-cart-id="{{ $id }}"
                                               data-sale-type="{{ $item['sale_type'] }}"
                                               value="{{ $item['sale_type'] === 'weight' ? $item['quantity'] : (int)$item['quantity'] }}" 
                                               step="{{ $item['sale_type'] === 'weight' ? '0.001' : '1' }}"
                                               class="qty-input-ajax"
                                               {{ $item['sale_type'] === 'weight' ? 'min="0.001"' : 'min="1"' }}
                                               style="width: 70px; text-align: center; padding: 4px 8px; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 16px; line-height: 1.5;">
                                        <span class="qty-unit ms-1 small" style="font-size: 0.85rem; color: #666;">
                                            {{ $item['sale_type'] === 'weight' ? 'kg' : 'pcs.' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-teal fs-5">{{ number_format($item['total_price'], 2) }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('sales.cart.remove', $id) }}" method="POST" class="m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-delete-row text-danger" title="{{ __('app.remove_from_receipt') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-cart-x fs-1 d-block mb-2 opacity-25"></i>
                                        {{ __('app.receipt_empty') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 
                RIGHT PANEL: On mobile this slides up as a drawer.
                On tablet/desktop it stays as a sidebar column.
            --}}
            <div class="workspace-right" id="workspaceRight">
                
                <div class="cart-summary-panel panel-card mb-3 p-4 bg-teal-light text-center" style="border: 2px solid var(--primary);">
                    <h6 class="text-uppercase fw-bold mb-2" style="color: var(--primary-dark);">{{ __('app.total_to_pay') }}</h6>
                    <h1 class="total-amount fw-bold text-teal mb-4">{{ number_format($cartTotal ?? 0, 2) }} <small class="total-currency">TMT</small></h1>
                    
                    <form action="{{ route('sales.cart.checkout') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="till_id" id="checkout_till_id" value="">
                        <button type="submit" class="action-btn btn-checkout text-white" id="btn-pay">
                            <i class="bi bi-wallet2 fs-4"></i> CHECKOUT <span class="hotkey dark ms-2">F12</span>
                        </button>
                    </form>
                </div>

                <div class="action-panel panel-card mb-3">
                    <h6 class="panel-title">{{__("app.shift_management_title")}}</h6>

                    {{-- УПРАВЛЕНИЕ СМЕНОЙ --}}
                    @if(isset($activeShift) && $activeShift)
                        {{-- Смена открыта --}}
                        <div class="shift-info-badge mb-3">
                            <i class="bi bi-clock-history me-2"></i>
                            {{ __('app.shift_from', ['time' => $activeShift->opened_at->format('H:i')]) }}
                        </div>

                        <button class="action-btn btn-print mb-3" onclick="window.print()">
                            <i class="bi bi-printer"></i> {{__("app.btn_print_report")}}
                            <span class="hotkey dark">F10</span>
                        </button>

                        <form action="{{ route('sales.close') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="action-btn btn-close-shift">
                                <i class="bi bi-door-closed"></i> {{__("app.btn_close_shift")}}
                            </button>
                        </form>
                    @else
                        {{-- Смена закрыта --}}
                        <div class="shift-warning-badge mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ __('app.shift_not_started') }}
                        </div>

                        <form action="{{ route('sales.start_shift') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="action-btn btn-start-shift">
                                <i class="bi bi-play-circle"></i> {{ __('app.btn_start_shift') }}
                            </button>
                        </form>
                    @endif
                </div>

                <div class="alert-panel bg-dark">
                    <h6 class="text-uppercase small fw-bold opacity-50 mb-3 text-white">{{__("app.inventory_card_title")}}</h6>
                    <div class="d-flex align-items-center">
                        <div class="alert-icon"><i class="bi bi-exclamation-triangle-fill text-warning"></i></div>
                        <div class="ms-3">
                            <h2 class="display-6 fw-bold mb-0 text-warning lh-1">{{ \App\Models\Storage::where('quantity', '<', 10)->count() }}</h2>
                            <p class="mb-0 fw-bold text-white">{{__("app.label_out_of_stock")}}</p>
                        </div>
                    </div>
                    <a href="{{ route('storage.index') }}" class="btn-go-storage mt-3">{{__("app.link_go_to_storage")}} <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>

        </div>

        {{-- Mobile: floating checkout button (visible only on small screens) --}}
        <div class="mobile-checkout-bar d-none" id="mobileCheckoutBar">
            <div class="mobile-checkout-total">
                <span class="label">Итого</span>
                <span class="value">{{ number_format($cartTotal ?? 0, 2) }} TMT</span>
            </div>
            <button class="mobile-checkout-btn" onclick="toggleRightPanel()">
                <i class="bi bi-wallet2"></i> Оплатить
            </button>
        </div>

    </main>
</div>

<style>
/* Make number input spinners (arrows) bigger */
.qty-input-ajax::-webkit-outer-spin-button,
.qty-input-ajax::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    height: 28px;
    opacity: 1;
}

.qty-input-ajax {
    appearance: textfield;
    position: relative;
}

/* Larger spinner buttons */
input[type="number"].qty-input-ajax {
    padding-right: 2px;
}
</style>

<script>
// Translation strings for JavaScript
const translations = {
    qty_must_be_positive: "{{ __('app.qty_must_be_positive') }}",
    error_qty_update: "{{ __('app.error_qty_update') }}",
    error_qty_insufficient: "{{ __('app.error_qty_insufficient') }}"
};

document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation for quantity inputs
    document.addEventListener('focus', function(e) {
        if (e.target.classList.contains('qty-input-ajax')) {
            e.target.value = '';
        }
    }, true);
    
    document.addEventListener('keypress', function(e) {
        if (e.target.classList.contains('qty-input-ajax') && e.key === 'Enter') {
            e.preventDefault();
            const event = new Event('change', { bubbles: true });
            e.target.dispatchEvent(event);
        }
    });
    
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-input-ajax')) {
            const input = e.target;
            const cartId = input.getAttribute('data-cart-id');
            const saleType = input.getAttribute('data-sale-type');
            let quantity = parseFloat(input.value);
            
            // For non-weight items, ensure it's an integer
            if (saleType !== 'weight') {
                quantity = Math.round(quantity);
                input.value = quantity;
            }
            
            if (quantity <= 0) {
                alert(translations.qty_must_be_positive);
                input.value = input.getAttribute('data-previous-value');
                return;
            }
            
            input.setAttribute('data-previous-value', quantity);
            
            const formData = new FormData();
            formData.append('quantity', quantity);
            formData.append('_method', 'PATCH');
            
            fetch(`/admin/sales/cart/${cartId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success === false) {
                    alert(data.message || translations.error_qty_update);
                    location.reload();
                } else {
                    // Update total price and cart summary without reload
                    fetch(window.location.href)
                        .then(res => res.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            
                            const oldTable = document.querySelector('.table-scroll-container');
                            const newTable = doc.querySelector('.table-scroll-container');
                            if (oldTable && newTable) oldTable.innerHTML = newTable.innerHTML;

                            const oldSummary = document.querySelector('.cart-summary-panel');
                            const newSummary = doc.querySelector('.cart-summary-panel');
                            if (oldSummary && newSummary) oldSummary.innerHTML = newSummary.innerHTML;
                            updateMobileTotal();
                        });
                }
            })
            .catch(error => {
                console.error('Error updating quantity:', error);
                alert(translations.error_qty_update);
            });
        }
    });
    
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('qty-input-ajax')) {
            const input = e.target;
            const cartId = input.getAttribute('data-cart-id');
            const saleType = input.getAttribute('data-sale-type');
            let quantity = parseFloat(input.value);
            
            // For non-weight items, ensure it's an integer
            if (saleType !== 'weight') {
                quantity = Math.round(quantity);
                input.value = quantity;
            }
            
            if (quantity <= 0) {
                alert(translations.qty_must_be_positive);
                input.value = input.getAttribute('data-previous-value');
                return;
            }
            
            input.setAttribute('data-previous-value', quantity);
            
            const formData = new FormData();
            formData.append('quantity', quantity);
            formData.append('_method', 'PATCH');
            
            fetch(`/admin/sales/cart/${cartId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success === false) {
                    alert(data.message || translations.error_qty_update);
                    location.reload();
                } else {
                    // Update total price and cart summary without reload
                    fetch(window.location.href)
                        .then(res => res.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            
                            const oldTable = document.querySelector('.table-scroll-container');
                            const newTable = doc.querySelector('.table-scroll-container');
                            if (oldTable && newTable) {
                                oldTable.innerHTML = newTable.innerHTML;
                            }

                            const oldSummary = document.querySelector('.cart-summary-panel');
                            const newSummary = doc.querySelector('.cart-summary-panel');
                            if (oldSummary && newSummary) oldSummary.innerHTML = newSummary.innerHTML;
                            updateMobileTotal();
                        });
                }
            })
            .catch(error => {
                console.error('Error updating quantity:', error);
                alert(translations.error_qty_update);
            });
        }
    });
    
    // Store initial values for all inputs
    document.querySelectorAll('.qty-input-ajax').forEach(input => {
        input.setAttribute('data-previous-value', input.value);
    });
    
    const input = document.getElementById('barcode-focus');
    const form = document.getElementById('main-sales-form');
    
    // Set till_id from localStorage if device is a till
    const deviceType = localStorage.getItem('device_type');
    const tillId = localStorage.getItem('till_id');
    const checkoutTillField = document.getElementById('checkout_till_id');
    
    if (deviceType === 'till' && tillId && checkoutTillField) {
        checkoutTillField.value = tillId;
    }

    if (input) {
        input.focus();
        document.addEventListener('click', (e) => {
            if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'A' && e.target.tagName !== 'INPUT') {
                if (window.innerWidth > 767) {
                    input.focus();
                }
            }
        });
    }
    setInterval(() => {
        const now = new Date();
        const clock = document.getElementById('realtime-clock');
        if (clock) clock.innerText = now.toLocaleTimeString('ru-RU');
    }, 1000);
    if (form) {
        form.addEventListener('submit', function(e) {
    e.preventDefault(); 
    const formData = new FormData(form);

    // Теперь в formData будет только barcode и csrf, 
    // что идеально подходит для нашего нового контроллера.
    fetch("{{ route('sales.cart.add') }}", {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    
                    fetch(window.location.href)
                        .then(res => res.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            
                            const oldTable = document.querySelector('.table-scroll-container');
                            const newTable = doc.querySelector('.table-scroll-container');
                            if (oldTable && newTable) oldTable.innerHTML = newTable.innerHTML;

                            const oldSummary = document.querySelector('.cart-summary-panel');
                            const newSummary = doc.querySelector('.cart-summary-panel');
                            if (oldSummary && newSummary) oldSummary.innerHTML = newSummary.innerHTML;
                            updateMobileTotal();
                            
                            if (input && window.innerWidth > 767) input.focus();
                        });
                } else {
                    alert(data.message || 'Ошибка добавления товара');
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                location.reload();
            });
        });
    }
    window.addEventListener('keydown', function(e) {
        
       
         if (
            e.key === 'F9' || e.keyCode === 120 || 
            e.key === 'F12' || e.keyCode === 123 ||
            (e.key === 'Enter' && (e.metaKey || e.ctrlKey))
        ) {
            e.preventDefault();
            e.stopPropagation();
            const btnPay = document.getElementById('btn-pay');
            if (btnPay) {
                const payForm = btnPay.closest('form');
                if (payForm) payForm.submit();
                else btnPay.click();
            }
        }
    }, true); 
    setupResponsive();
    window.addEventListener('resize', setupResponsive);
});

window.toggleRightPanel = function() {
    const panel = document.getElementById('workspaceRight');
    if (panel) {
        panel.classList.toggle('panel-open');
        document.body.style.overflow = panel.classList.contains('panel-open') ? 'hidden' : '';
    }
};

function setupResponsive() {
    const isMobile = window.innerWidth < 768;
    const mobileCheckoutBar = document.getElementById('mobileCheckoutBar');
    const mobileHeaderSpacer = document.querySelector('.mobile-header-spacer');

    if (mobileCheckoutBar) {
        mobileCheckoutBar.style.display = isMobile ? 'flex' : 'none';
    }
    if (mobileHeaderSpacer) {
        mobileHeaderSpacer.style.display = isMobile ? 'block' : 'none';
    }
    
    updateMobileTotal();
}

function updateMobileTotal() {
    const totalEl = document.querySelector('.cart-summary-panel h1, .cart-summary-panel .total-amount');
    const mobileValueEl = document.querySelector('.mobile-checkout-total .value');
    if (totalEl && mobileValueEl) {
        mobileValueEl.textContent = totalEl.textContent.trim();
    }
}


</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap');

:root {
    --primary: #E8722A;
    --primary-dark: #C4561A;
    --bg-color: #FBF7F3;
    --panel-bg: #ffffff;
    --text-main: #1A0A00;
    --text-muted: #8B7355;
    --border-color: #EDE4DA;
}
body { margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; background: var(--bg-color); }
.desktop-app-layout { display: flex; width: 100vw; overflow: hidden; }
.app-main { 
    flex: 1; 
    display: flex; 
    flex-direction: column; 
    overflow: hidden; 
    min-width: 0;
}
.main-header { 
    height: 80px; 
    background: white; 
    display: flex; 
    align-items: center; 
    padding: 0 25px; 
    gap: 20px; 
    border-bottom: 1px solid var(--border-color); 
    flex-shrink: 0;
    overflow: hidden;
}

.stat-card { 
    display: flex; 
    align-items: center; 
    gap: 15px; 
    padding-right: 20px; 
    border-right: 1px solid var(--border-color); 
    flex-shrink: 0;
}

.stat-icon { 
    width: 45px; height: 45px; 
    border-radius: 12px; 
    display: flex; justify-content: center; align-items: center; 
    font-size: 1.3rem; 
    flex-shrink: 0;
}

.bg-teal-light { background: rgba(232,114,42,0.1); }
.text-teal { color: var(--primary); }
.bg-blue-light { background: rgba(13,110,253,0.1); }
.bg-red-light { background: rgba(220,53,69,0.1); }
.stat-data .label { display: block; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; white-space: nowrap; }
.stat-data .value { font-size: 1.2rem; font-weight: 800; color: var(--text-main); white-space: nowrap; }

.system-status { 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    font-size: 0.8rem; 
    font-weight: 600; 
    color: #48bb78; 
    background: rgba(72,187,120,0.1); 
    padding: 8px 15px; 
    border-radius: 20px; 
    white-space: nowrap;
    flex-shrink: 0;
}

.dot.pulse { 
    width: 8px; height: 8px; 
    background: #48bb78; 
    border-radius: 50%; 
    animation: pulse-green 2s infinite; 
    flex-shrink: 0;
}
.workspace { 
    flex: 1; 
    display: flex; 
    gap: 20px; 
    padding: 20px; 
    overflow: hidden; 
}

.workspace-left { 
    flex: 1; 
    display: flex; 
    flex-direction: column; 
    gap: 20px; 
    min-width: 0; 
    overflow: hidden;
}

.workspace-right { 
    width: 320px; 
    display: flex; 
    flex-direction: column; 
    overflow-y: auto;
    gap: 0;
    flex-shrink: 0;
}

.panel-card { 
    background: white; 
    border-radius: 18px; 
    box-shadow: 0 2px 12px rgba(26,10,0,0.05); 
    border: 1px solid var(--border-color); 
}

.scanner-panel { padding: 20px; flex-shrink: 0; }

.type-toggles { display: flex; gap: 10px; margin-bottom: 15px; }

.toggle-btn { 
    flex: 1; text-align: center; padding: 10px; 
    border: 2px solid var(--border-color); border-radius: 12px; 
    cursor: pointer; font-weight: 700; color: var(--text-muted); 
    transition: 0.2s; font-size: 0.85rem; 
}

.btn-check:checked + .toggle-btn { 
    border-color: var(--primary); 
    background: rgba(232,114,42,0.06); 
    color: var(--primary); 
}

.hotkey { 
    background: #EDE4DA; color: #6B4E2A; 
    padding: 2px 6px; border-radius: 4px; 
    font-size: 0.7rem; margin-left: 5px; 
}

.btn-check:checked + .toggle-btn .hotkey { background: var(--primary); color: white; }

.barcode-wrapper { 
    display: flex; 
    background: #FDFAF7; 
    border: 2px solid var(--border-color); 
    border-radius: 14px; 
    overflow: hidden; 
    transition: 0.2s; 
}

.barcode-wrapper:focus-within { 
    border-color: var(--primary); 
    box-shadow: 0 0 0 3px rgba(232,114,42,0.15); 
    background: white; 
}

.scan-icon { padding: 15px; font-size: 1.5rem; color: var(--primary); }

.barcode-input { 
    flex: 1; border: none; background: transparent; 
    font-size: 1.2rem; font-weight: 600; color: var(--text-main); 
    outline: none; min-width: 0;
    font-family: 'DM Sans', sans-serif;
}

.btn-submit-scan { 
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; border: none; 
    padding: 0 25px; font-weight: 700; cursor: pointer; 
    transition: 0.2s; white-space: nowrap; flex-shrink: 0;
    font-family: 'DM Sans', sans-serif;
}

.btn-submit-scan:hover { background: linear-gradient(135deg, var(--primary-dark), #9C3D0E); }
.table-panel { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }

.panel-header { 
    padding: 15px 20px; 
    border-bottom: 1px solid var(--border-color); 
    display: flex; justify-content: space-between; align-items: center; 
    flex-shrink: 0;
}

.panel-header h5 { margin: 0; font-weight: 700; color: var(--text-main); font-family: 'Sora', sans-serif; }

.badge-custom { 
    background: var(--bg-color); padding: 6px 12px; 
    border-radius: 8px; font-size: 0.8rem; color: var(--text-muted); 
    white-space: nowrap; border: 1px solid var(--border-color);
}

.table-scroll-container { flex: 1; overflow-y: auto; }

.pos-table th { 
    position: sticky; top: 0; background: #FBF7F3; 
    font-size: 0.72rem; text-transform: uppercase; 
    color: var(--text-muted); padding: 12px 20px; 
    border-bottom: 1px solid var(--border-color); z-index: 2; 
    white-space: nowrap; font-weight: 800; letter-spacing: 0.5px;
}

.pos-table td { padding: 12px 20px; vertical-align: middle; border-bottom: 1px solid #F5EDE4; }

.med-name { font-weight: 600; color: var(--text-main); }

.qty-badge { 
    background: var(--bg-color); color: #6B4E2A; 
    padding: 4px 12px; border-radius: 20px; 
    font-size: 0.85rem; font-weight: 700; white-space: nowrap;
    border: 1px solid var(--border-color);
}

.btn-delete-row { 
    background: none; border: none; color: #C4B4A0; 
    cursor: pointer; transition: 0.2s; font-size: 1.2rem; 
}
.btn-delete-row:hover { color: #e53e3e; transform: scale(1.1); }

.total-amount { font-size: 2.5rem; line-height: 1; font-family: 'Sora', sans-serif; }
.total-currency { font-size: 1rem; }

.action-panel { padding: 20px; }

.panel-title { 
    font-weight: 800; color: var(--text-muted); 
    text-transform: uppercase; margin-bottom: 15px; font-size: 0.75rem;
    letter-spacing: 0.6px;
}

.action-btn { 
    width: 100%; padding: 15px; border-radius: 12px; border: none; 
    font-weight: 700; font-size: 1rem; cursor: pointer; 
    display: flex; justify-content: center; align-items: center; 
    gap: 10px; transition: 0.2s; 
    font-family: 'DM Sans', sans-serif;
}

.btn-checkout { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); box-shadow: 0 4px 14px rgba(232,114,42,0.3); }
.btn-checkout:hover { background: linear-gradient(135deg, var(--primary-dark), #9C3D0E); box-shadow: 0 6px 20px rgba(232,114,42,0.4); }

.btn-print { background: var(--bg-color); color: var(--text-main); border: 1px solid var(--border-color); }
.btn-print:hover { background: var(--border-color); }
.btn-print .hotkey.dark { background: #D4C4B0; color: #4A3520; }
.btn-close-shift { background: #fff5f5; color: #e53e3e; border: 1px solid #feb2b2; }
.btn-close-shift:hover { background: #e53e3e; color: white; }

.alert-panel { 
    padding: 25px; border-radius: 18px; 
    background: linear-gradient(135deg, #1A0A00, #2E1100);
    color: white; 
    box-shadow: 0 10px 30px rgba(26,10,0,0.2); 
}

.alert-icon { 
    width: 50px; height: 50px; 
    background: rgba(255,193,7,0.2); border-radius: 12px; 
    display: flex; justify-content: center; align-items: center; font-size: 1.5rem; 
    flex-shrink: 0;
}

.btn-go-storage { 
    display: inline-block; padding: 10px 20px; 
    background: rgba(232,114,42,0.2); color: white; 
    text-decoration: none; border-radius: 8px; 
    font-size: 0.85rem; font-weight: 600; transition: 0.2s;
    border: 1px solid rgba(232,114,42,0.3);
}
.btn-go-storage:hover { background: var(--primary); color: white; }

.mobile-checkout-bar {
    display: none;
    position: fixed;
    bottom: 0; left: 0; right: 0;
    background: white;
    border-top: 2px solid var(--primary);
    padding: 12px 20px;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    z-index: 900;
    box-shadow: 0 -4px 20px rgba(26,10,0,0.1);
}

.mobile-checkout-total .label {
    display: block; font-size: 0.7rem; text-transform: uppercase; 
    color: var(--text-muted); font-weight: 700;
}

.mobile-checkout-total .value {
    font-size: 1.3rem; font-weight: 800; color: var(--primary);
    font-family: 'Sora', sans-serif;
}

.mobile-checkout-btn {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; border: none;
    padding: 12px 24px; border-radius: 12px; 
    font-weight: 700; font-size: 1rem; cursor: pointer;
    display: flex; align-items: center; gap: 8px;
    white-space: nowrap;
    box-shadow: 0 4px 14px rgba(232,114,42,0.3);
}


@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(72, 187, 120, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(72, 187, 120, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(72, 187, 120, 0); }
}
@media (max-width: 1023px) and (min-width: 768px) {
    .main-header { padding: 0 15px; gap: 12px; }
    
    .stat-data .value { font-size: 1rem; }
    
    .workspace { padding: 15px; gap: 15px; }
    
    .workspace-right { width: 280px; }
    
    .total-amount { font-size: 2rem; }
    
    .hotkey { display: none; }
    
    .status-label { display: none; } 
    
    .system-status { padding: 8px; }
    .scan-icon { padding: 12px; font-size: 1.2rem; }
    .barcode-input { font-size: 1rem; }
    .btn-submit-scan { padding: 0 15px; font-size: 0.85rem; }
}
@media (max-width: 767px) {
    body.desktop-app-mode {
        overflow: auto !important;
    }

    .desktop-app-layout { 
        flex-direction: column; 
        height: auto !important;
        min-height: 100vh;
        overflow: auto !important;
    }

    .app-main { 
        overflow: auto !important; 
        height: auto;
    }
    .main-header { 
        height: auto;
        min-height: 64px;
        padding: 10px 15px 10px 70px; 
        flex-wrap: wrap;
        gap: 10px;
    }

    .stat-card { 
        padding-right: 12px; 
        gap: 8px; 
    }

    .stat-card-hide-mobile { display: none !important; }
    
    .stat-icon { width: 36px; height: 36px; font-size: 1rem; }
    .stat-data .label { font-size: 0.6rem; }
    .stat-data .value { font-size: 0.95rem; }

    .status-label { display: none; }
    .system-status { padding: 6px 10px; }
    .workspace { 
        flex-direction: column; 
        padding: 12px; 
        gap: 12px;
        overflow: visible !important;
        padding-bottom: 90px; 
    }

    .workspace-left { overflow: visible; }
    .table-panel { 
        height: 350px; 
        flex: none;
    }
    .workspace-right {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        width: 100% !important;
        max-height: 80vh;
        background: var(--bg-color);
        border-radius: 24px 24px 0 0;
        box-shadow: 0 -8px 30px rgba(26,10,0,0.15);
        z-index: 950;
        overflow-y: auto;
        padding: 20px 15px 30px;
        transform: translateY(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .workspace-right.panel-open {
        transform: translateY(0);
    }
    .workspace-right::before {
        content: '';
        display: block;
        width: 40px; height: 4px;
        background: var(--border-color);
        border-radius: 2px;
        margin: 0 auto 20px;
    }

    .total-amount { font-size: 2.5rem; }
    .hotkey { display: none; }
    .scanner-panel { padding: 15px; }
    .type-toggles { gap: 8px; }
    .toggle-btn { padding: 10px 6px; font-size: 0.75rem; }
    .scan-icon { padding: 12px; font-size: 1.2rem; }
    .barcode-input { font-size: 1rem; }
    .btn-submit-scan { padding: 0 12px; font-size: 0.8rem; }

    .panel-header { padding: 12px 15px; }
    .panel-header h5 { font-size: 0.95rem; }
    .pos-table th { padding: 10px 12px; font-size: 0.65rem; }
    .pos-table td { padding: 10px 12px; }
    .med-name { font-size: 0.85rem; }

    
    .mobile-checkout-bar { display: flex !important; }
}

@media print {
    .sidebar-wrapper,
    .scanner-panel,
    .main-header,
    .workspace-right,
    .mobile-checkout-bar,
    .sidebar-hamburger { display: none !important; }

    .workspace { padding: 0; }
    .workspace-left { width: 100%; }
    .table-panel { height: auto; overflow: visible; }
    .table-scroll-container { overflow: visible; }
}
.shift-info-badge {
    background: rgba(232,114,42,0.1);
    color: var(--primary);
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 700;
    display: flex;
    align-items: center;
}
.shift-warning-badge {
    background: rgba(255,193,7,0.12);
    color: #b7791f;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 700;
    display: flex;
    align-items: center;
}
.btn-start-shift {
    background: #E8722A;
    color: white;
    border: none;
    width: 100%;
}
.btn-start-shift:hover { background: #6B4E2A; color: white; }
</style>

<script>
// Auto-print receipt after checkout
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.querySelector('form[action="{{ route("sales.cart.checkout") }}"]');
    const checkoutButton = document.getElementById('btn-pay');
    
    if (checkoutForm && checkoutButton) {
        checkoutButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const formData = new FormData(checkoutForm);
            const tillIdField = document.getElementById('checkout_till_id');
            
            // Set till_id if not set
            if (!tillIdField.value) {
                const deviceType = localStorage.getItem('device_type');
                const tillId = localStorage.getItem('till_id');
                if (deviceType === 'till' && tillId) {
                    tillIdField.value = tillId;
                }
            }
            
            // Disable button
            checkoutButton.disabled = true;
            checkoutButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
            
            // Submit checkout via AJAX
            fetch(checkoutForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.receipt_url) {
                    // Show success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success mx-4 mt-3';
                    alertDiv.innerHTML = data.message + ' <small>(Printing receipt...)</small>';
                    document.querySelector('.app-main').insertBefore(alertDiv, document.querySelector('.app-main').firstChild);
                    
                    // Auto-print receipt
                    setTimeout(() => {
                        const printWindow = window.open(data.receipt_url, '_blank', 'width=400,height=600');
                        
                        if (printWindow) {
                            printWindow.onload = function() {
                                setTimeout(() => {
                                    printWindow.print();
                                    setTimeout(() => {
                                        printWindow.close();
                                    }, 1000);
                                }, 500);
                            };
                        } else {
                            // Fallback: open in same window
                            window.location.href = data.receipt_url;
                        }
                    }, 1000);
                    
                    // Clear cart after delay
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                    
                } else {
                    // Handle error
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Checkout error:', error);
                // Fallback to normal form submission
                checkoutForm.submit();
            })
            .finally(() => {
                // Re-enable button after delay
                setTimeout(() => {
                    checkoutButton.disabled = false;
                    checkoutButton.innerHTML = '<i class="bi bi-wallet2 fs-4"></i> CHECKOUT <span class="hotkey dark ms-2">F12</span>';
                }, 3000);
            });
        });
    }
});
</script>
@endsection