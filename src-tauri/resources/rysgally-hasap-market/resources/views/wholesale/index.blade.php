@extends('layouts.app')
@section('content')
@include('app.navbar')
<div class="container-fluid py-4" style="background-color: #f8fafb; min-height: 100vh;">
    <div class="row justify-content-center">
        <div class="col-xl-11">

            {{-- Перенеси алерты сюда --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px;">
                    <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

           @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px;">
                    <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- ДОБАВЬ ВОТ ЭТОТ БЛОК ДЛЯ ВАЛИДАЦИИ --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px;">
                    <i class="bi bi-exclamation-octagon me-2"></i> <strong>{{ __('app.wholesale_create_error_validation') }}</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            {{-- КОНЕЦ ДОБАВЛЕННОГО БЛОКА --}}

            <div class="row mb-4 align-items-center">
                {{-- Дальше твой код заголовка --}}
                <div class="col-md-6">
                    <h2 class="fw-bold mb-1" style="color: #2c3e50; letter-spacing: -0.5px;">{{ __('app.wholesale_summary_title') }}</h2>
                    <nav aria-label="breadcrumb">
                        
                        <ol class="breadcrumb mb-0">
                            
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none" style="color: #E8722A;">{{ __('app.wholesale_summary_breadcrumb') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('app.wholesale_summary_breadcrumb2') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2">
                    <button type="button" class="btn btn-outline-dark px-4 py-2 shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#transferToMarketModal"
                        style="border-radius: 12px; border: 1px solid #E8722A; color: #E8722A; transition: 0.3s; background: white;">
                        <i class="bi bi-arrow-left-right me-2"></i> {{ __('app.wholesale_btn_transfer') }}
                    </button>

                    <a href="{{ route('wholesale.create') }}" class="btn text-white px-4 py-2 shadow-sm border-0"
                        style="background: linear-gradient(135deg, #E8722A 0%, #C85A1A 100%); border-radius: 12px; transition: 0.3s;">
                        <i class="bi bi-plus-circle me-2"></i> {{ __('app.wholesale_btn_create') }}
                    </a>
                </div>
            </div>

            {{-- Статистика (карточки) --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3" style="border-radius: 18px; border-left: 5px solid #E8722A !important;">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-light p-3 me-3">
                                <i class="bi bi-files fs-4" style="color: #E8722A;"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 small uppercase fw-bold">{{ __('app.wholesale_card_invoices') }}</h6>
                                <h4 class="fw-bold mb-0">{{ $invoices->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3" style="border-radius: 18px; border-left: 5px solid #ffc107 !important;">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-light p-3 me-3">
                                <i class="bi bi-cash-stack fs-4 text-warning"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 small uppercase fw-bold">{{ __('app.wholesale_card_revenue') }}</h6>
                                <h4 class="fw-bold mb-0">${{ number_format($invoices->sum('total_amount'), 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3" style="border-radius: 18px; border-left: 5px solid #0dcaf0 !important;">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-light p-3 me-3">
                                <i class="bi bi-people fs-4 text-info"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 small uppercase fw-bold">{{ __('app.wholesale_card_customers') }}</h6>
                                <h4 class="fw-bold mb-0">{{ $invoices->unique('customer_name')->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Таблица транзакций --}}
            <div class="card border-0 shadow-sm mb-5" style="border-radius: 20px; overflow: hidden;">
                <div class="card-header bg-white py-3 border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0 fw-bold" style="color: #2c3e50;">Recent Transactions</h5>
                        </div>
                        <div class="col text-end">
                            <button class="btn btn-sm btn-light rounded-pill"><i class="bi bi-filter me-1"></i> Filter</button>
                            <button type="button" onclick="exportData()" class="btn btn-outline-success border-0 shadow-sm rounded-pill">
                                <i class="bi bi-file-earmark-excel-fill fs-5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 wholesale-recent-table">
                        <thead style="background-color: #fcfdfe;">
                            <tr>
                                <th class="ps-4 py-3 text-muted fw-normal">Type</th>
                                <th class="py-3 text-muted fw-normal">Reference</th>
                                <th class="py-3 text-muted fw-normal">Details</th>
                                <th class="py-3 text-muted fw-normal">Status</th>
                                <th class="py-3 text-muted fw-normal">Amount / Qty</th>
                                <th class="py-3 text-muted fw-normal">Date</th>
                                <th class="pe-4 py-3 text-end text-muted fw-normal">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $row)
                                @if($row->kind === 'invoice')
                                    @php $invoice = $row->invoice @endphp
                                    <tr class="wholesale-row-invoice">
                                        <td class="ps-4">
                                            <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background: linear-gradient(135deg, #E8722A22 0%, #E8722A11 100%); color: #C85A1A; border: 1px solid #E8722A44;">
                                                <i class="bi bi-receipt me-1"></i>Sale
                                            </span>
                                        </td>
                                        <td><span class="fw-bold text-dark">{{ $invoice->invoice_no }}</span></td>
                                        <td><span class="fw-bold">{{ $invoice->customer_name }}</span></td>
                                        <td><span class="badge bg-light text-success border">Completed</span></td>
                                        <td><div class="fw-bold text-primary">${{ number_format($invoice->total_amount, 2) }}</div></td>
                                        <td class="text-muted small">{{ $invoice->created_at->format('M d, Y · H:i') }}</td>
                                        <td class="pe-4 text-end">
                                            <a href="{{ route('wholesale.show', $invoice->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @else
                                    @php $t = $row->transfer @endphp
                                    <tr class="wholesale-row-transfer" style="background: linear-gradient(90deg, rgba(13, 202, 240, 0.06) 0%, transparent 12%);">
                                        <td class="ps-4">
                                            <span class="badge rounded-pill px-3 py-2 fw-semibold bg-light text-info border">
                                                <i class="bi bi-shop me-1"></i>To market
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $t->product_name }}</span>
                                        </td>
                                        <td>
                                            <div class="small text-muted font-monospace">{{ $t->market_barcode }}</div>
                                            @if($t->user)
                                                <div class="small text-muted mt-1"><i class="bi bi-person me-1"></i>{{ $t->user->name ?? $t->user->username ?? 'User' }}</div>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-light text-info border">In retail storage</span></td>
                                        <td>
                                            <div class="fw-bold" style="color: #0aa2c0;">
                                                @if(($t->unit_type ?? 'piece') === 'weight')
                                                    {{ number_format((float) $t->quantity, 3) }} kg
                                                @else
                                                    {{ number_format((float) $t->quantity, 0) }} pcs
                                                @endif
                                            </div>
                                            <div class="small text-muted">in ${{ number_format($t->received_price, 2) }} → sell ${{ number_format($t->selling_price, 2) }}</div>
                                        </td>
                                        <td class="text-muted small">{{ $t->created_at->format('M d, Y · H:i') }}</td>
                                        <td class="pe-4 text-end">
                                            @if($t->storage_id)
                                                <a href="{{ route('storage.edit', $t->storage_id) }}" class="btn btn-sm btn-outline-info">Storage</a>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No activity for this day.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL: Transfer to Market -->
<div class="modal fade" id="transferToMarketModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
      <div class="modal-header border-0 pt-4 px-4">
        <h5 class="modal-title fw-bold" style="color: #E8722A;">
          <i class="bi bi-arrow-left-right me-2"></i> {{ __('app.wholesale_transfer_modal_title') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="{{ route('wholesale_storage.transfer') }}" method="POST">
        @csrf
        <input type="hidden" name="product_name" id="product_name_hidden">

        <div class="modal-body p-4">
          <div class="row g-3">

            <!-- SELECT product -->
            <div class="col-12">
              <label class="form-label small fw-bold text-muted">
                <i class="bi bi-box-seam me-1"></i> {{ __('app.wholesale_transfer_modal_product') }}
              </label>
              <select name="product_id" id="product_id_transfer" class="form-select" required>
                <option value="">{{ __('app.wholesale_transfer_modal_select_product') }}</option>
                @foreach($products as $product)
                  @php $stock = $product->wholesaleStorage->sum('quantity'); @endphp
                  @if($stock > 0)
                    <option value="{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-stock="{{ $stock }}"
                            data-unit-type="{{ $product->unit_type ?? 'piece' }}"
                            data-product-code="{{ $product->product_code ?? '' }}"
                            data-barcode="{{ $product->barcode ?? '' }}">
                      {{ $product->name }}
                    </option>
                  @endif
                @endforeach
              </select>

              <div class="mt-2 small">
                <span>{{ __('app.wholesale_transfer_modal_stock_label') }}: <strong id="available_stock_badge" class="text-primary">0</strong> <span id="unit_label">pcs</span></span>
              </div>
            </div>

            <div class="col-md-6">
              <label id="scan_field_label" class="form-label small fw-bold text-muted">
                <i class="bi bi-upc-scan me-1"></i> {{ __('app.wholesale_transfer_modal_code_label') }}
              </label>
              <input type="text" name="market_scan_code" id="market_scan_input" class="form-control border-0 bg-light p-3"
                     placeholder="" style="border-radius: 12px;" autocomplete="off">              
            </div>

            <div class="col-md-6">
              <label id="transfer_label" class="form-label small fw-bold text-muted">
                <i class="bi bi-box-seam me-1"></i> {{ __('app.wholesale_transfer_modal_amount_label') }}
              </label>
              <input type="number" name="transfer_qty" id="transfer_quantity"
                     class="form-control border-0 bg-light p-3"
                     placeholder="0" style="border-radius: 12px;">
            </div>

            <div class="col-12">
              <div id="calc-preview" class="alert border-0 small mb-0"
                   style="background-color: #eef9fa; color: #E8722A; border-radius: 12px;">
                <i class="bi bi-calculator me-2"></i>
                <span id="calc_text">{{ __('app.wholesale_transfer_modal_calc_hint') }}</span>
              </div>
              <div id="stock_error_msg" class="text-danger small mt-2 d-none">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                {{ __('app.wholesale_transfer_modal_error_stock') }}
              </div>
            </div>

            <!-- PRICES -->
            <div class="col-md-4">
              <label class="form-label small fw-bold text-muted">{{ __('app.wholesale_transfer_modal_received_price') }}</label>
              <input type="number" step="0.01" name="received_price"
                     class="form-control border-0 bg-light p-3"
                     placeholder="{{ __('app.wholesale_transfer_modal_purchase_price') }}" required style="border-radius: 12px;">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-muted">{{ __('app.wholesale_transfer_modal_selling_price') }}</label>
              <input type="number" step="0.01" name="selling_price"
                     class="form-control border-0 bg-light p-3"
                     placeholder="{{ __('app.wholesale_transfer_modal_market_price') }}" required style="border-radius: 12px;">
            </div>


            <!-- BATCH + EXPIRY -->
            <div class="col-md-6">
              <label class="form-label small fw-bold text-muted">{{ __('app.wholesale_transfer_modal_batch') }}</label>
              <input type="text" name="batch_number"
                     class="form-control border-0 bg-light p-3"
                     placeholder="{{ __('app.wholesale_transfer_modal_batch_placeholder') }}" style="border-radius: 12px;">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-muted">{{ __('app.wholesale_transfer_modal_expiry') }}</label>
              <input type="date" name="expiry_date"
                     class="form-control border-0 bg-light p-3" style="border-radius: 12px;">
            </div>

          </div>
        </div>

        <div class="modal-footer border-0 pb-4 px-4">
          <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                  style="border-radius: 10px;">{{ __('app.wholesale_transfer_modal_cancel') }}</button>
          <button type="submit" id="submit_transfer_btn"
                  class="btn text-white px-4" disabled
                  style="background-color: #E8722A; border-radius: 10px;">
            {{ __('app.wholesale_transfer_modal_confirm') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const medSelect       = document.getElementById('product_id_transfer');
    const medNameHidden   = document.getElementById('product_name_hidden');
    const quantityInput   = document.getElementById('transfer_quantity');
    const stockBadge      = document.getElementById('available_stock_badge');
    const unitLabel       = document.getElementById('unit_label');
    const transferLabel   = document.getElementById('transfer_label');
    const scanInput       = document.getElementById('market_scan_input');
    const scanLabel       = document.getElementById('scan_field_label');
    const scanHint        = document.getElementById('scan_field_hint');
    const calcText        = document.getElementById('calc_text');
    const errorMsg        = document.getElementById('stock_error_msg');
    const submitBtn       = document.getElementById('submit_transfer_btn');

    let currentMaxQuantity = 0;
    let currentUnitType = 'piece';

    quantityInput.addEventListener('focus', function () {
        if (this.value === '0') this.value = '';
    });
    quantityInput.addEventListener('blur', function () {
        if (this.value === '' || isNaN(this.value)) this.value = '';
    });

    function updatePreview() {
        const quantity = parseFloat(quantityInput.value) || 0;

        if (quantity <= 0) {
            calcText.textContent = '{{ __("app.wholesale_transfer_js_enter_amount") }}';
            submitBtn.disabled = true;
            errorMsg.classList.add('d-none');
            return;
        }

        const unitName = currentUnitType === 'weight' ? 'kg' : 'pcs';
        const movesText = '{{ __("app.wholesale_transfer_js_moves_to_market") }}';
        calcText.textContent = movesText.replace('{0}', quantity).replace('{1}', unitName);

        if (quantity > currentMaxQuantity) {
            errorMsg.classList.remove('d-none');
            submitBtn.disabled = true;
        } else {
            errorMsg.classList.add('d-none');
            submitBtn.disabled = false;
        }
    }

    medSelect.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        if (!selected || !selected.value) {
            submitBtn.disabled = true;
            scanInput.value = '';
            return;
        }

        medNameHidden.value = selected.getAttribute('data-name') || '';
        currentMaxQuantity  = parseFloat(selected.getAttribute('data-stock')) || 0;
        currentUnitType     = selected.getAttribute('data-unit-type') || 'piece';

        const isWeight = currentUnitType === 'weight';
        const code = (isWeight ? (selected.getAttribute('data-product-code') || '') : (selected.getAttribute('data-barcode') || '')).trim();

        stockBadge.textContent = currentMaxQuantity;
        unitLabel.textContent = isWeight ? 'kg' : 'pcs';
        const weightLabel = '{{ __("app.wholesale_transfer_js_weight_label") }}';
        const piecesLabel = '{{ __("app.wholesale_transfer_js_pieces_label") }}';
        transferLabel.innerHTML = `<i class="bi bi-${isWeight ? 'speedometer2' : 'box-seam'} me-1"></i> ${isWeight ? weightLabel : piecesLabel}`;

        if (isWeight) {
            const productCodeLabel = '{{ __("app.wholesale_transfer_js_product_code_label") }}';
            scanLabel.innerHTML = `<i class="bi bi-tag me-1"></i> ${productCodeLabel}`;
            scanInput.placeholder = '{{ __("app.wholesale_transfer_js_product_code_placeholder") }}';
            scanHint.textContent = '{{ __("app.wholesale_transfer_js_product_code_hint") }}';
        } else {
            const barcodeLabel = '{{ __("app.wholesale_transfer_js_barcode_label") }}';
            scanLabel.innerHTML = `<i class="bi bi-upc-scan me-1"></i> ${barcodeLabel}`;
            scanInput.placeholder = '{{ __("app.wholesale_transfer_js_barcode_placeholder") }}';
            scanHint.textContent = '{{ __("app.wholesale_transfer_js_barcode_hint") }}';
        }
        scanInput.value = code;

        quantityInput.min = isWeight ? '0.001' : '1';
        quantityInput.step = isWeight ? '0.001' : '1';

        quantityInput.value = '';
        calcText.textContent = '{{ __("app.wholesale_transfer_js_enter_amount") }}';
        errorMsg.classList.add('d-none');
        submitBtn.disabled = true;
    });

    quantityInput.addEventListener('input', updatePreview);
});

function exportData() {
    window.location.href = "{{ route('wholesale.export') }}";
}
</script>
@endsection
