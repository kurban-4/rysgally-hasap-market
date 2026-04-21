@extends('layouts.app')

@section('content')
@include('app.navbar')

<div class="container-fluid py-5" style="background-color: #F8F5F2; min-height: 100vh;">
    <form action="{{ route('wholesale.store') }}" method="POST" id="invoice-form">
        @csrf
        
        {{-- БЛОК ОШИБОК --}}
        @if ($errors->any())
            <div class="row justify-content-center mb-4">
                <div class="col-xl-11">
                    <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center" style="border-radius: 16px; background-color: #FFF0F0; color: #D32F2F;">
                        <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1">{{ __('app.wholesale_create_error_validation') }}</h6>
                            <ul class="mb-0 ps-3 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-xl-11">
                <div class="card invoice-card border-0 p-4 p-md-5">
                    
                    {{-- HEADER & CUSTOMER SECTION --}}
                    <div class="row align-items-center mb-5 pb-4 border-bottom header-section">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h2 class="fw-bolder mb-0 d-flex align-items-center" style="color: #2C3E50; letter-spacing: -0.5px;">
                                <div class="icon-wrapper me-3">
                                    <i class="bi bi-receipt-cutoff"></i>
                                </div>
                                {{ __('app.wholesale_create_heading') }}
                            </h2>
                        </div>
                        <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-3">
                            <div class="customer-input-wrapper flex-grow-1 text-end">
                                <label class="small fw-bold text-muted text-uppercase mb-1 d-block" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ __('app.wholesale_create_customer') }}</label>
                                <input type="text" name="customer_name" class="form-control form-control-lg border-0 shadow-sm custom-input"
                                    placeholder="{{ __('app.wholesale_create_customer_placeholder') }}" required value="{{ old('customer_name') }}">
                            </div>
                            <a href="{{ route('wholesale.index') }}" class="btn btn-light shadow-sm btn-back mt-4">
                                <i class="bi bi-arrow-left"></i>
                            </a>
                        </div>
                    </div>

                    {{-- PRODUCT SEARCH SECTION --}}
                    <div class="search-section mb-5 p-4 rounded-4 shadow-sm relative">
                        <label class="form-label fw-bold mb-3 d-flex align-items-center" style="color: #2C3E50;">
                            <span class="search-badge me-2"><i class="bi bi-search"></i></span> 
                            {{ __('app.wholesale_create_product') }}
                        </label>
                        <select id="product-adder" class="form-select form-select-lg custom-select shadow-sm">
                            <option value="">{{ __('app.wholesale_create_product_placeholder') }}</option>
                            @foreach($products as $product)
                                @if($product->total_stock > 0)
                                    @php $firstBatch = $product->wholesaleStorage->first(); @endphp
                                    <option value="{{ $product->id }}"
                                        data-price="{{ $firstBatch->selling_price ?? 0 }}"
                                        data-name="{{ $product->name }}"
                                        data-stock="{{ $product->total_stock }}"
                                        data-expiry="{{ $firstBatch->expiry_date ?? 'N/A' }}"
                                        data-unit_type="{{ $product->unit_type }}"
                                        data-units_per_box="{{ $product->units_per_box }}">
                                        {{ $product->name }} — ${{ number_format($firstBatch->selling_price ?? 0, 2) }} (Stock: {{ $product->total_stock }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- TABLE SECTION --}}
                    <div class="table-container mb-5 shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0" id="items-table">
                                <thead>
                                    <tr>
                                        <th class="ps-4">{{ __('app.wholesale_create_item_header') }}</th>
                                        <th class="text-center" width="130">{{ __('app.wholesale_create_qty_header') }}</th>
                                        <th class="text-center" width="100">{{ __('app.wholesale_create_item_col') }}</th>
                                        <th class="text-center" width="120">{{ __('app.wholesale_create_weight_col') }}</th>
                                        <th class="text-center" width="120">{{ __('app.wholesale_create_expiry_col') }}</th>
                                        <th class="text-center" width="110">{{ __('app.wholesale_create_discount_col') }}</th>
                                        <th class="text-center" width="130">{{ __('app.wholesale_create_price_col') }}</th>
                                        <th class="text-end" width="140">{{ __('app.wholesale_create_total_col') }}</th>
                                        <th class="text-center pe-4" width="60"></th>
                                    </tr>
                                </thead>
                                <tbody id="invoice-items-list">
                                    {{-- JS generates rows here --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- FOOTER & TOTALS --}}
                    <div class="row pt-3 g-4 align-items-end">
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <div class="info-box p-3 rounded-3 d-flex align-items-start shadow-sm">
                                <i class="bi bi-info-circle-fill fs-5 me-3" style="color: #A68A6D;"></i>
                                <p class="mb-0 text-muted small lh-lg">
                                    <strong>{{ __('app.wholesale_create_auto_calc') }}</strong> {{ __('app.wholesale_create_auto_calc_desc') }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="total-summary-box p-4 rounded-4 shadow-sm ms-lg-auto">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-3 header-section">
                                    <h6 class="text-muted text-uppercase fw-bold mb-0" style="letter-spacing: 1px;">{{ __('app.wholesale_create_grand_total') }}</h6>
                                    <h2 class="fw-bolder mb-0 text-end" style="color: #E8722A; letter-spacing: -1px;">
                                        $<span id="total-display">0.00</span>
                                    </h2>
                                </div>
                                <input type="hidden" name="grand_total" id="total-input" value="0">

                                <button type="submit" class="btn btn-finalize w-100 fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2">
                                    <i class="bi bi-check2-circle fs-5"></i> {{ __('app.wholesale_create_btn_finalize') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

body { 
    font-family: 'Plus Jakarta Sans', sans-serif; 
}

/* Base Card Styling */
.invoice-card {
    border-radius: 24px;
    box-shadow: 0 15px 40px rgba(139, 115, 85, 0.05) !important;
    background: #ffffff;
}

.header-section { border-color: #F0EAE1 !important; }

/* Icon Wrappers */
.icon-wrapper {
    background: linear-gradient(135deg, #FFF0E6, #FFE4D6);
    color: #E8722A;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 1.25rem;
}

.search-badge {
    background: #E8722A;
    color: white;
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 0.85rem;
}

/* Inputs & Selects */
.custom-input, .custom-select {
    border-radius: 12px;
    background-color: #FBF9F7;
    border: 1px solid transparent;
    font-weight: 500;
    color: #2C3E50;
    transition: all 0.25s ease;
}

.custom-select {
    padding: 14px 20px;
    font-size: 1rem;
    border: 1.5px solid #F0EAE1;
    background-color: #ffffff;
}

.custom-input:focus, .custom-select:focus {
    background-color: #ffffff;
    border-color: #E8722A !important;
    box-shadow: 0 0 0 4px rgba(232, 114, 42, 0.1) !important;
}

/* Table Container */
.table-container {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #F0EAE1;
    overflow: hidden;
}

#items-table thead {
    background: #FBF9F7;
    border-bottom: 2px solid #F0EAE1;
}

#items-table thead th {
    color: #8B7355;
    font-size: 0.75rem;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.8px;
    padding: 18px 12px;
}

#items-table tbody tr {
    border-bottom: 1px solid #F8F5F2;
    transition: background 0.2s ease;
}

#items-table tbody tr:last-child {
    border-bottom: none;
}

#items-table tbody tr:hover {
    background: #FDFAF7;
}

#items-table td {
    padding: 16px 12px;
    vertical-align: middle;
}

/* Dynamic Row Inputs */
.qty-input, .discount-input, .price-input {
    border: 1.5px solid #F0EAE1 !important;
    border-radius: 10px !important;
    background: #ffffff !important;
    font-weight: 600;
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #2C3E50;
    text-align: center;
    transition: all 0.2s ease;
    padding: 8px;
}

.price-input {
    background: #FBF9F7 !important;
    color: #8B7355;
}

.qty-input:focus, .discount-input:focus {
    border-color: #E8722A !important;
    box-shadow: 0 0 0 3px rgba(232,114,42,0.12) !important;
    outline: none;
}

/* Badges & Buttons */
.badge.bg-light {
    background: #FDF8F5 !important;
    color: #C4561A !important;
    border: 1px solid #F0EAE1 !important;
    font-weight: 600;
    font-size: 0.75rem;
    padding: 6px 10px !important;
    border-radius: 6px;
}

.remove-row {
    color: #ef4444 !important;
    border-color: transparent !important;
    background: #FFF5F5 !important;
    border-radius: 10px !important;
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease !important;
}

.remove-row:hover { 
    background: #ef4444 !important; 
    color: white !important; 
    transform: scale(1.05);
}

.btn-back {
    height: 48px;
    width: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #F0EAE1;
    color: #2C3E50;
    transition: all 0.2s ease;
}

.btn-back:hover {
    background: #2C3E50;
    color: white;
    border-color: #2C3E50;
}

/* Footer Section */
.info-box {
    background: #FBF9F7;
    border: 1px solid #F0EAE1;
}

.search-section {
    background: #ffffff;
    border: 1px solid #F0EAE1;
}

.total-summary-box {
    background: #FAFAFA;
    border: 1px solid #F0EAE1;
    max-width: 400px;
}

.btn-finalize {
    background: linear-gradient(135deg, #E8722A, #D65A18);
    color: white;
    border: none;
    border-radius: 14px;
    padding: 16px;
    font-size: 1.05rem;
    transition: all 0.3s ease;
}

.btn-finalize:hover {
    background: linear-gradient(135deg, #D65A18, #C4561A);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(232, 114, 42, 0.25) !important;
    color: white;
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const adder = document.getElementById('product-adder');
    const list = document.getElementById('invoice-items-list');
    let itemIndex = 0;

    adder.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) return;

        const id = opt.value;
        const name = opt.dataset.name;
        const price = opt.dataset.price;
        const stock = opt.dataset.stock;
        const expiry = opt.dataset.expiry || 'N/A';
        const unitType = opt.dataset.unit_type;

        if (document.querySelector(`input[value="${id}"].med-id-input`)) {
            alert('This product is already in the list!');
            this.value = "";
            return;
        }

        const row = document.createElement('tr');
        row.innerHTML = `
        <td class="ps-4">
            <input type="hidden" name="items[${itemIndex}][product_id]" value="${id}" class="med-id-input">
            <div class="fw-bold" style="color: #2C3E50;">${name}</div>
            <small class="text-muted fw-medium" style="font-size: 0.75rem;">Available: ${stock}</small>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][qty]" class="form-control qty-input" value="1" min="1" max="${stock}" required data-unit_type="${unitType}">
        </td>
        <td class="text-center fw-bold text-secondary">
            <span class="item-display">${unitType === 'weight' ? '-' : '1'}</span>
        </td>
        <td class="text-center fw-bold text-secondary">
            <span class="weight-display">${unitType === 'weight' ? '1 kg' : '-'}</span>
        </td>
        <td class="text-center">
            <span class="badge bg-light">${expiry}</span>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][discount]" class="form-control discount-input" value="0" min="0" max="100">
        </td>
        <td>
            <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control price-input" value="${price}" readonly>
        </td>
        <td class="text-end fw-bolder fs-6" style="color: #2C3E50;">
            $<span class="row-total-display">${price}</span>
            <input type="hidden" name="items[${itemIndex}][total]" class="row-total-hidden" value="${price}">
        </td>
        <td class="text-center pe-4">
            <button type="button" class="btn remove-row">
                <i class="bi bi-trash3"></i>
            </button>
        </td>
        `;
        list.appendChild(row);
        row.querySelector('.remove-row').addEventListener('click', function() {
            row.remove();
            calculateGrandTotal();
        });

        itemIndex++;
        this.value = "";
        calculateGrandTotal();
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('discount-input')) {
            const row = e.target.closest('tr');
            const qtyInput = row.querySelector('.qty-input');
            const qty = parseFloat(qtyInput.value) || 0;
            const unitType = qtyInput.dataset.unit_type;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const discount = parseFloat(row.querySelector('.discount-input').value) || 0;

            if (unitType === 'weight') {
    row.querySelector('.item-display').textContent = '-'; 
    row.querySelector('.weight-display').textContent = parseFloat(qty.toFixed(3)) + ' kg';
} else {
                row.querySelector('.item-display').textContent = Math.round(qty);
                row.querySelector('.weight-display').textContent = '-'; 
            }

            const total = (qty * price) * (1 - discount / 100);

            row.querySelector('.row-total-display').innerText = total.toFixed(2);
            row.querySelector('.row-total-hidden').value = total.toFixed(2);
 
            calculateGrandTotal();
        }
    });

    function calculateGrandTotal() {
        let grand = 0;
        document.querySelectorAll('.row-total-hidden').forEach(input => {
            grand += parseFloat(input.value) || 0;
        });
        document.getElementById('total-display').innerText = grand.toFixed(2);
        document.getElementById('total-input').value = grand.toFixed(2);
    }
});
</script>
@endsection