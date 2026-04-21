{{-- Transfer from a specific wholesale batch → market storage (same product + scan code rules as POS) --}}
@php
    $p = $item->product;
    $defaultScan = ($p->unit_type ?? 'piece') === 'weight'
        ? ($p->product_code ?? '')
        : ($p->barcode ?? '');
    $scanLabel = ($p->unit_type ?? 'piece') === 'weight' ? __('app.wholesale_storage_transfer_scan_label') : __('app.wholesale_storage_transfer_barcode_label');
    $scanPlaceholder = ($p->unit_type ?? 'piece') === 'weight'
        ? __('app.wholesale_storage_transfer_scan_placeholder')
        : __('app.wholesale_storage_transfer_barcode_placeholder');
    $scanHint = ($p->unit_type ?? 'piece') === 'weight'
        ? __('app.wholesale_storage_transfer_scan_hint')
        : __('app.wholesale_storage_transfer_barcode_hint');
@endphp
<div class="modal fade" id="transferModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content transfer-modal">

            <div class="transfer-bar"></div>

            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="transfer-header-icon">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0 text-dark">{{ __('app.wholesale_storage_transfer_title') }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('wholesale_storage.transfer') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                <input type="hidden" name="product_name" value="{{ $p->name }}">
                <input type="hidden" name="wholesale_storage_id" value="{{ $item->id }}">
                <input type="hidden" name="received_price" value="{{ $item->received_price }}">
                <input type="hidden" name="selling_price" value="{{ $item->selling_price }}">
                                <input type="hidden" name="batch_number" value="{{ $item->batch_number ?? '' }}">
                <input type="hidden" name="expiry_date" value="{{ $item->expiry_date?->format('Y-m-d') }}">

                <div class="modal-body px-4 pt-4 pb-2">

                    <div class="med-pill">
                        <div class="med-pill-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-dark text-truncate">{{ $p->name }}</div>
                            <div class="text-muted small">{{ __('app.wholesale_storage_transfer_batch') }}: #{{ $item->batch_number ?? 'N/A' }}</div>
                        </div>
                        <span class="avail-badge">
                            {{ $item->display_quantity }}
                        </span>
                    </div>

                    <div class="transfer-flow">
                        <div class="flow-node from">
                            <i class="bi bi-building"></i>
                            <span>Wholesale</span>
                        </div>
                        <div class="flow-connector">
                            <div class="flow-line"></div>
                            <i class="bi bi-arrow-right-circle-fill flow-icon"></i>
                            <div class="flow-line"></div>
                        </div>
                        <div class="flow-node to">
                            <i class="bi bi-shop"></i>
                            <span>Market</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="qty-label d-block mb-1">{{ $scanLabel }}</label>
                        <input type="text" name="market_scan_code" class="form-control rounded-3 border-0 bg-light py-2 px-3"
                               value="{{ old('market_scan_code', $defaultScan) }}"
                               placeholder="{{ $scanPlaceholder }}"
                               autocomplete="off">
                        <div class="text-muted small mt-1">{{ $scanHint }}</div>
                    </div>

                    <div class="qty-section">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="qty-label">{{ __('app.wholesale_storage_transfer_quantity') }}</label>
                            <span class="max-hint">{{ __('app.wholesale_storage_transfer_available') }}: <strong>{{ $item->is_weight ? number_format($item->quantity, 3) : (int) $item->quantity }}</strong> @if($item->is_weight) kg @else pcs @endif</span>
                        </div>
                        <div class="qty-wrap" id="qtyWrap{{ $item->id }}">
                            <input
                                type="number"
                                name="transfer_qty"
                                class="qty-field"
                                placeholder="0"
                                min="{{ $item->is_weight ? '0.001' : '1' }}"
                                step="{{ $item->is_weight ? '0.001' : '1' }}"
                                max="{{ $item->quantity }}"
                                required
                                oninput="checkTransferQty(this, {{ $item->quantity }}, '{{ $item->id }}')"
                            >
                            <span class="qty-suffix">@if($item->is_weight) kg @else pcs @endif</span>
                        </div>
                        <div class="qty-error d-none" id="qtyError{{ $item->id }}">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            {{ __('app.wholesale_storage_transfer_error_stock') }}
                        </div>
                    </div>

                    <div class="transfer-note">
                        <i class="bi bi-info-circle-fill me-2 flex-shrink-0"></i>
                        <span>{{ __('app.wholesale_storage_transfer_note') }}</span>
                    </div>

                </div>

                <div class="modal-footer border-0 px-4 pt-2 pb-4 gap-2">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">{{ __('app.wholesale_storage_transfer_cancel') }}</button>
                    <button type="submit" class="btn-confirm" id="confirmBtn{{ $item->id }}">
                        <i class="bi bi-check2-circle me-2"></i>{{ __('app.wholesale_storage_transfer_btn') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
<style>
.transfer-modal {
    border: none !important;
    border-radius: 22px !important;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(0,0,0,0.12) !important;
}

.transfer-bar {
    height: 4px;
    background: linear-gradient(90deg, #E8722A 0%, #C85A1A 100%);
}
.transfer-header-icon {
    width: 34px; height: 34px;
    background: #E8722A; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 0.9rem; flex-shrink: 0;
}

.med-pill {
    display: flex; align-items: center; gap: 12px;
    background: #f8fafc; border: 1px solid #e8edf2;
    border-radius: 14px; padding: 13px 16px;
    margin-bottom: 18px;
}
.med-pill-icon {
    width: 40px; height: 40px; flex-shrink: 0;
    background: rgba(232,114,42,0.1); color: #E8722A;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
}
.avail-badge {
    background: #E8722A; color: white;
    padding: 4px 11px; border-radius: 20px;
    font-size: 0.7rem; font-weight: 700;
    white-space: nowrap; flex-shrink: 0;
}

.transfer-flow {
    display: flex; align-items: center; justify-content: center;
    gap: 10px; margin-bottom: 20px;
}
.flow-node {
    display: flex; flex-direction: column; align-items: center; gap: 5px;
    padding: 11px 18px; border-radius: 13px;
    font-size: 0.7rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.flow-node i { font-size: 1.3rem; }
.flow-node.from { background: rgba(232,114,42,0.08); color: #E8722A; border: 1.5px solid rgba(232,114,42,0.18); }
.flow-node.to   { background: rgba(16,185,129,0.08); color: #10b981; border: 1.5px solid rgba(16,185,129,0.18); }

.flow-connector {
    display: flex; align-items: center; gap: 0; flex: 1; max-width: 80px;
}
.flow-line {
    flex: 1; height: 2px;
    background: linear-gradient(90deg, rgba(232,114,42,0.2), rgba(16,185,129,0.2));
}
.flow-icon { font-size: 1.1rem; color: #E8722A; flex-shrink: 0; }

.qty-section { margin-bottom: 16px; }
.qty-label { font-size: 0.65rem; text-transform: uppercase; font-weight: 800; color: #a0aec0; letter-spacing: 0.5px; }
.max-hint { font-size: 0.72rem; color: #a0aec0; }

.qty-wrap {
    display: flex; align-items: center;
    background: #f8fafc; border: 2px solid #e8edf2;
    border-radius: 13px; overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.qty-wrap:focus-within {
    border-color: #E8722A;
    background: white;
    box-shadow: 0 0 0 3px rgba(232,114,42,0.1);
}
.qty-wrap.has-error { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }

.qty-field {
    flex: 1; border: none; background: transparent;
    padding: 13px 16px; font-size: 1.15rem; font-weight: 700;
    color: #2d3748; outline: none;
}
.qty-suffix {
    padding: 0 16px; font-size: 0.78rem;
    font-weight: 700; color: #a0aec0; flex-shrink: 0;
    border-left: 1px solid #e8edf2;
}
.qty-error {
    color: #ef4444; font-size: 0.75rem;
    font-weight: 600; margin-top: 6px;
    display: flex; align-items: center;
}

.transfer-note {
    display: flex; align-items: flex-start; gap: 0;
    background: rgba(232,114,42,0.06);
    border: 1px solid rgba(232,114,42,0.14);
    border-radius: 11px; padding: 11px 14px;
    font-size: 0.8rem; color: #E8722A; line-height: 1.45;
}
.btn-cancel {
    background: #f1f5f9; color: #64748b;
    border: none; border-radius: 10px;
    padding: 10px 22px; font-weight: 600; font-size: 0.875rem;
    cursor: pointer; transition: background 0.15s;
}
.btn-cancel:hover { background: #e2e8f0; }

.btn-confirm {
    background: #E8722A; color: white;
    border: none; border-radius: 10px;
    padding: 10px 24px; font-weight: 700; font-size: 0.875rem;
    cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center;
}
.btn-confirm:hover {
    background: #C85A1A;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(232,114,42,0.3);
}
.btn-confirm:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
</style>
@endonce

@once
<script>
function checkTransferQty(input, max, id) {
    const wrap  = document.getElementById('qtyWrap' + id);
    const error = document.getElementById('qtyError' + id);
    const btn   = document.getElementById('confirmBtn' + id);
    const val   = parseFloat(input.value);
    const over  = !isNaN(val) && val > max;

    wrap?.classList.toggle('has-error', over);
    error?.classList.toggle('d-none', !over);
    if (btn) btn.disabled = over;
}
</script>
@endonce
