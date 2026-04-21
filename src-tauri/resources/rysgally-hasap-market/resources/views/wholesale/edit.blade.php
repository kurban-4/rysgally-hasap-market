@extends('layouts.app')

@section('content')
<style>
    :root {
        --sirin-teal: #E8722A;
        --sirin-light: #f4f7f7;
    }

    .edit-container {
        max-width: 1100px;
        margin: 2rem auto;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .glass-card {
        background: white;
        border-radius: 20px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    .card-header-sirin {
        background: var(--sirin-teal);
        color: white;
        padding: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sirin-input-group {
        background: var(--sirin-light);
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }

    .table-modern thead {
        background: var(--sirin-light);
        border-radius: 8px;
    }

    .table-modern th {
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 1px;
        color: #666;
        padding: 1.2rem;
        border: none;
    }

    .editable-input {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 8px 12px;
        width: 100%;
        transition: all 0.2s;
    }

    .editable-input:focus {
        border-color: var(--sirin-teal);
        outline: none;
        box-shadow: 0 0 0 3px rgba(16, 122, 132, 0.1);
    }

    .total-badge {
        background: var(--sirin-light);
        padding: 1.5rem;
        border-radius: 12px;
        text-align: right;
    }

    .btn-update {
        background: var(--sirin-teal);
        color: white;
        border: none;
        padding: 1rem 2.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-update:hover {
        background: #0c626a;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(16, 122, 132, 0.3);
    }
</style>

<div class="edit-container">
    <form action="{{ route('wholesale.update', $invoice->id) }}" method="POST" id="edit-invoice-form">
        @csrf
        @method('PUT')

        <div class="glass-card">
            <div class="card-header-sirin">
                <div>
                    <h2 class="mb-0 fw-bold">{{ __('app.wholesale_edit_heading') }}</h2>
                    <span class="opacity-75">Ref: #{{ $invoice->invoice_no }}</span>
                </div>
                <div class="text-end">
                    <p class="mb-0 small">{{ __('receipt_label_date') }}</p>
                    <h5 class="mb-0">{{ $invoice->created_at->format('d M, Y') }}</h5>
                </div>
            </div>

            <div class="p-4 p-md-5">
                <div class="sirin-input-group">
                    <label class="form-label fw-bold small text-muted text-uppercase">{{ __('app.wholesale_edit_customer_label') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-person text-teal"></i></span>
                        <input type="text" name="customer_name" class="form-control border-0 bg-white p-3"
                            value="{{ $invoice->customer_name }}" style="border-radius: 0 8px 8px 0;" placeholder="{{ __('app.wholesale_edit_customer_placeholder') }}">
                    </div>
                </div>

                <h5 class="mb-4 fw-bold"><i class="bi bi-box-seam me-2"></i>{{ __('app.wholesale_edit_table_product') }}</h5>
                <div class="table-responsive">
                    <table class="table table-modern align-middle" id="items-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">{{ __('app.wholesale_edit_table_product') }}</th>
                                <th>{{ __('app.wholesale_edit_table_qty') }}</th>
                                <th>{{ __('app.wholesale_edit_table_item') }}</th>
                                <th>{{ __('app.wholesale_edit_table_weight') }}</th>
                                <th>{{ __('app.wholesale_edit_table_price') }}</th>
                                <th>{{ __('app.wholesale_edit_table_discount') }}</th>
                                <th class="text-end">{{ __('app.wholesale_edit_table_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $index => $item)
                            <tr>
                                <td>
                                    <input type="hidden" name="items[{{$index}}][id]" value="{{ $item->id }}">
                                    <div class="fw-bold text-dark">{{ $item->product->name }}</div>
                                    <small class="text-muted">Batch: #{{ $item->batch_id ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <input type="number" name="items[{{$index}}][quantity]"
                                        class="editable-input qty-input" value="{{ $item->quantity }}" min="1">
                                </td>
                                <td class="text-center fw-bold">{{ number_format($item->item) }}</td>
                                <td class="text-center fw-bold">{{ $item->weight > 0 ? number_format($item->weight, 3) . ' kg' : '—' }}</td>
                                <td>
                                    <input type="number" name="items[{{$index}}][price]" step="0.01"
                                        class="editable-input price-input" value="{{ $item->unit_price }}">
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="items[{{$index}}][discount]" step="0.1"
                                            class="editable-input discount-input" value="{{ $item->discount_percent }}" min="0" max="100">
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-teal row-total">
                                    {{ number_format(($item->quantity * $item->price) - $item->discount, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-5 align-items-center">
                    <div class="col-md-6">
                        <a href="{{ route('wholesale.index') }}" class="btn btn-link text-muted text-decoration-none">
                            <i class="bi bi-arrow-left me-2"></i> {{ __('expenses_btn_cancel') }} & {{ __('wholesale_edit_back') }}
                        </a>
                    </div>
                    <div class="col-md-6">
                        <div class="total-badge">
                            <span class="text-muted text-uppercase small fw-bold">{{ __('app.wholesale_edit_grand_total') }}</span>
                            <h2 class="mb-0 fw-bold" style="color: var(--sirin-teal);" id="grand-total-display">
                                {{ number_format($invoice->total_amount, 2) }} TMT
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn-update">
                        <i class="bi bi-check2-circle me-2"></i> {{ __('app.wholesale_edit_btn_update') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('input', function(e) {
        if (e.target.matches('.qty-input, .price-input, .discount-input')) {
            let grandTotal = 0;
            const rows = document.querySelectorAll('#items-table tbody tr');

            rows.forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                const discountPercent = parseFloat(row.querySelector('.discount-input').value) || 0;
                const subtotal = qty * price;
                const rowTotal = subtotal * (1 - (discountPercent / 100));

                row.querySelector('.row-total').textContent = rowTotal.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                grandTotal += rowTotal;
            });

            document.getElementById('grand-total-display').textContent = grandTotal.toLocaleString('en-US', {
                minimumFractionDigits: 2
            }) + ' TMT';
        }
    });
</script>
@endsection