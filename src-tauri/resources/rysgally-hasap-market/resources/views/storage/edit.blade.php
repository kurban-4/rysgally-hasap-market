@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main">

        <header class="main-header">
            <a href="{{ route('storage.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="mb-0">{{ __('app.storage_edit_title') }}</h4>
                <p class="text-muted small mb-0 d-none d-md-block">{{ $storage->product->name }}</p>
            </div>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="type-badge {{ $unit === 'kg' ? 'type-weight' : 'type-item' }}">
                    <i class="bi {{ $unit === 'kg' ? 'bi-speedometer2' : 'bi-boxes' }} me-1"></i>
                    {{ $unit === 'kg' ? __('app.storage_edit_unit_weight') : __('app.storage_edit_unit_item') }}
                </span>
                <span class="id-badge">#{{ str_pad($storage->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
        </header>

        <div class="workspace custom-scrollbar">
            <div class="form-container">

                @if(session('success'))
                    <div class="flash flash-success">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="flash flash-error">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('storage.update', $storage->id) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="edit-grid">

                        {{-- LEFT COLUMN ── quantity · barcode · expiry --}}
                        <div class="left-col">

                            {{-- QUANTITY: item or weight --}}
                            <div class="section-card">
                                <h6 class="section-title">
                                    <i class="bi {{ $unit === 'kg' ? 'bi-speedometer2' : 'bi-boxes' }} me-2"></i>
                                    {{ $unit === 'kg' ? __('app.storage_edit_qty_weight') : __('app.storage_edit_qty_item') }}
                                </h6>

                                <div class="input-row">
                                    <i class="bi {{ $unit === 'kg' ? 'bi-speedometer2' : 'bi-123' }} input-icon"></i>
                                    <input
                                        type="number"
                                        name="amount"
                                        class="field-input"
                                        value="{{ old('amount', $amount) }}"
                                        step="{{ $unit === 'kg' ? '0.001' : '1' }}"
                                        min="0"
                                        placeholder="{{ $unit === 'kg' ? '0.000' : '0' }}"
                                        required>
                                    <span class="unit-suffix {{ $unit === 'kg' ? 'suffix-weight' : 'suffix-item' }}">
                                        {{ $unit }}
                                    </span>
                                </div>

                                <small class="hint-text">
                                    @if($unit === 'kg')
                                        {{ __('app.storage_edit_hint_weight') }}
                                    @else
                                        {{ __('app.storage_edit_hint_items') }}
                                    @endif
                                </small>
                            </div>

                            {{-- Market scan: product code (weight) or barcode (piece) --}}
                            <div class="section-card">
                                <h6 class="section-title">
                                    @if($unit === 'kg')
                                        <i class="bi bi-tag me-2"></i>{{ __('app.storage_edit_code_weight') }}
                                    @else
                                        <i class="bi bi-upc-scan me-2"></i>{{ __('app.storage_edit_barcode') }}
                                    @endif
                                </h6>
                                <div class="input-row">
                                    <i class="bi {{ $unit === 'kg' ? 'bi-tag' : 'bi-upc-scan' }} input-icon"></i>
                                    <input type="text" name="barcode" class="field-input"
                                           value="{{ old('barcode', $storage->barcode ?? ($unit === 'kg' ? $storage->product->product_code : $storage->product->barcode)) }}"
                                           placeholder="{{ $unit === 'kg' ? __('app.storage_edit_code_placeholder') : __('app.storage_edit_barcode_placeholder') }}">
                                </div>
                                @if($unit === 'kg')
                                    <small class="hint-text">{{ __('app.storage_edit_code_hint') }}</small>
                                @endif
                            </div>

                            {{-- EXPIRY DATE --}}
                            <div class="section-card">
                                <h6 class="section-title"><i class="bi bi-calendar-event me-2"></i>{{ __('app.storage_edit_expiry_label') }}</h6>

                                @php
                                    $currentExpiry = $storage->expiry_date
                                        ? \Carbon\Carbon::parse($storage->expiry_date)->format('Y-m-d')
                                        : ($storage->product->expiry_date
                                            ? \Carbon\Carbon::parse($storage->product->expiry_date)->format('Y-m-d')
                                            : null);
                                    $daysLeft = $currentExpiry
                                        ? (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($currentExpiry)->startOfDay(), false)
                                        : null;
                                @endphp

                                @if($currentExpiry)
                                    <div class="expiry-preview mb-3 {{ $daysLeft <= 0 ? 'exp-red' : ($daysLeft <= 30 ? 'exp-orange' : 'exp-green') }}">
                                        <i class="bi bi-calendar3 me-2"></i>
                                        {{ __('app.storage_edit_expiry_current') }} <strong>{{ \Carbon\Carbon::parse($currentExpiry)->format('d.m.Y') }}</strong>
                                        &nbsp;—&nbsp;
                                        @if($daysLeft <= 0)
                                            {{ __('app.storage_edit_expiry_expired') }}
                                        @else
                                            {{ $daysLeft }} {{ __('app.storage_edit_expiry_days_left') }}
                                        @endif
                                    </div>
                                @endif

                                <div class="input-row">
                                    <i class="bi bi-calendar-event input-icon"></i>
                                    <input type="date" name="expiry_date" class="field-input"
                                           value="{{ old('expiry_date', $currentExpiry) }}">
                                </div>
                                <small class="hint-text">{{ __('app.storage_edit_expiry_hint') }}</small>
                            </div>

                        </div>

                        {{-- RIGHT COLUMN ── pricing · category · save --}}
                        <div class="right-col">

                            {{-- PRICING --}}
                            <div class="section-card">
                                <h6 class="section-title"><i class="bi bi-tag-fill me-2"></i>{{ __('app.storage_edit_prices_section') }}</h6>

                                <div class="two-col mb-3">
                                    <div>
                                        <label class="field-label">{{ __('app.storage_edit_price_sell') }}</label>
                                        <div class="input-row">
                                            <i class="bi bi-tag input-icon"></i>
                                            <input type="number" name="price" id="edit-price" class="field-input"
                                                   value="{{ old('price', $storage->selling_price ?? $storage->product->price) }}"
                                                   step="0.01" min="0" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="field-label">{{ __('app.storage_edit_price_cost') }}</label>
                                        <div class="input-row">
                                            <i class="bi bi-cash-coin input-icon"></i>
                                            <input type="number" name="received_price" class="field-input"
                                                   value="{{ old('received_price', $storage->received_price ?? $storage->product->received_price) }}"
                                                   step="0.01" min="0" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="field-label">{{ __('app.storage_edit_discount') }}</label>
                                    <div class="input-row">
                                        <i class="bi bi-percent input-icon"></i>
                                        <input type="number" name="discount" id="edit-discount" class="field-input"
                                               value="{{ old('discount', $storage->discount ?? $storage->product->discount) }}"
                                               min="0" max="100" placeholder="0">
                                    </div>
                                </div>

                                <div class="price-result">
                                    <div>
                                        <div class="price-result-label">{{ __('app.storage_edit_final_price') }}</div>
                                        <div class="price-result-value" id="final-price">0.00 TMT</div>
                                    </div>
                                    <div id="discount-badge"></div>
                                </div>
                            </div>

                            {{-- CATEGORY --}}
                            <div class="section-card">
                                <h6 class="section-title"><i class="bi bi-grid me-2"></i>{{ __('app.storage_edit_category') }}</h6>
                                <div class="input-row">
                                    <i class="bi bi-grid input-icon"></i>
                                    <select name="category" class="field-input field-select">
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}" {{ old('category', $storage->category) == $cat ? 'selected' : '' }}>
                                                {{ $cat }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn-save">
                                <i class="bi bi-check2-circle me-2"></i>{{ __('app.storage_edit_btn_save') }}
                            </button>

                        </div>
                    </div>
                </form>

            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const priceInput    = document.getElementById('edit-price');
    const discountInput = document.getElementById('edit-discount');
    const finalDisplay  = document.getElementById('final-price');
    const badgeArea     = document.getElementById('discount-badge');

    function updatePrice() {
        const price = parseFloat(priceInput?.value) || 0;
        const disc  = parseFloat(discountInput?.value) || 0;
        const final = price * (1 - disc / 100);
        if (finalDisplay) finalDisplay.textContent = final.toFixed(2) + ' TMT';
        if (badgeArea) badgeArea.innerHTML = disc > 0
            ? `<span class="discount-badge">-${disc}%</span>` : '';
    }

    [priceInput, discountInput].forEach(el => el?.addEventListener('input', updatePrice));
    updatePrice();
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap');

:root {
    --primary:      #E8722A;
    --primary-dark: #C4561A;
    --primary-soft: rgba(232,114,42,0.10);
    --primary-glow: rgba(232,114,42,0.20);
    --bg:           #FBF7F3;
    --border:       #EDE4DA;
    --text:         #1A0A00;
    --muted:        #8B7355;
}

*, *::before, *::after { box-sizing: border-box; }
body { margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); }

.desktop-app-layout { position: fixed; inset: 0; display: flex; overflow: hidden; }
.desktop-app-layout .sidebar-wrapper { position: relative !important; flex-shrink: 0; height: 100%; }
.app-main { flex: 1; min-width: 0; display: flex; flex-direction: column; overflow: hidden; height: 100%; }

.main-header {
    height: 68px; background: white; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; padding: 0 24px; gap: 14px; flex-shrink: 0;
}
.main-header h4 { font-family: 'Sora', sans-serif; font-weight: 800; font-size: 1rem; color: var(--text); }
.btn-back {
    width: 38px; height: 38px; min-width: 38px; border-radius: 11px;
    background: white; border: 1.5px solid var(--border); color: var(--muted);
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; transition: 0.2s; flex-shrink: 0;
}
.btn-back:hover { color: var(--primary); border-color: var(--primary); transform: translateX(-2px); }
.id-badge {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; padding: 5px 14px; border-radius: 20px;
    font-size: 0.72rem; font-weight: 800; white-space: nowrap; flex-shrink: 0;
}
.type-badge {
    padding: 5px 14px; border-radius: 20px;
    font-size: 0.72rem; font-weight: 800; white-space: nowrap; flex-shrink: 0;
}
.type-weight { background: rgba(59,130,246,0.1); color: #3b82f6; border: 1px solid rgba(59,130,246,0.2); }
.type-item   { background: var(--primary-soft); color: var(--primary); border: 1px solid rgba(232,114,42,0.2); }

.workspace { flex: 1; overflow-y: auto; padding: 24px; }
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }
.form-container { max-width: 900px; margin: 0 auto; }

.flash {
    border-radius: 12px; padding: 12px 18px; margin-bottom: 18px;
    font-weight: 600; font-size: 0.875rem; display: flex; align-items: flex-start; gap: 6px;
}
.flash-success { background: rgba(16,185,129,0.1); color: #059669; border: 1px solid rgba(16,185,129,0.2); }
.flash-error   { background: rgba(239,68,68,0.08); color: #dc2626; border: 1px solid rgba(239,68,68,0.2); }

.edit-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start; }

.section-card {
    background: white; border-radius: 16px; padding: 20px;
    border: 1px solid var(--border); margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(26,10,0,0.03);
}
.section-title {
    font-family: 'Sora', sans-serif; font-weight: 700;
    color: var(--primary); margin-bottom: 14px; font-size: 0.88rem;
}

.field-label {
    display: block; font-size: 0.6rem; text-transform: uppercase;
    font-weight: 800; color: var(--muted); letter-spacing: 0.5px; margin-bottom: 5px;
}
.input-row {
    display: flex; align-items: center; background: var(--bg);
    border: 1.5px solid var(--border); border-radius: 11px; overflow: hidden; transition: 0.2s;
}
.input-row:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); background: white; }
.input-icon { padding: 0 12px; color: var(--primary); font-size: 0.9rem; flex-shrink: 0; }
.field-input {
    flex: 1; border: none; background: transparent;
    padding: 11px 12px 11px 0; font-size: 0.875rem; color: var(--text); outline: none; width: 100%;
}
.field-select { cursor: pointer; }
.unit-suffix {
    padding: 0 14px 0 4px; font-weight: 800; font-size: 0.82rem; flex-shrink: 0; user-select: none;
}
.suffix-item   { color: var(--primary); }
.suffix-weight { color: #3b82f6; }
.hint-text { display: block; font-size: 0.78rem; color: var(--muted); margin-top: 8px; }
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.expiry-preview {
    display: flex; align-items: center; padding: 10px 14px;
    border-radius: 10px; font-size: 0.85rem; font-weight: 600; border: 1px solid transparent;
}
.exp-green  { background: rgba(16,185,129,0.08); color: #059669; border-color: rgba(16,185,129,0.2); }
.exp-orange { background: rgba(245,158,11,0.1);  color: #b45309; border-color: rgba(245,158,11,0.25); }
.exp-red    { background: rgba(239,68,68,0.08);  color: #dc2626; border-color: rgba(239,68,68,0.2); }

.price-result {
    background: var(--primary-soft); border-radius: 12px; padding: 14px 16px;
    display: flex; justify-content: space-between; align-items: center;
    border: 1px solid rgba(232,114,42,0.15); margin-top: 16px;
}
.price-result-label { font-size: 0.6rem; text-transform: uppercase; font-weight: 800; color: var(--muted); margin-bottom: 2px; }
.price-result-value { font-family: 'Sora', sans-serif; font-size: 1.4rem; font-weight: 800; color: var(--primary); }
.discount-badge {
    background: rgba(239,68,68,0.1); color: #dc2626;
    border: 1px solid rgba(239,68,68,0.2);
    padding: 5px 12px; border-radius: 20px; font-size: 0.72rem; font-weight: 800;
}

.btn-save {
    width: 100%; padding: 14px; border-radius: 14px; border: none;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: 0.2s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    box-shadow: 0 6px 16px var(--primary-glow);
}
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 10px 24px var(--primary-glow); }

@media (max-width: 1023px) { .edit-grid { grid-template-columns: 1fr; } }
@media (max-width: 767px) {
    .desktop-app-layout { position: relative; inset: auto; min-height: 100vh; height: auto !important; flex-direction: column; overflow: auto !important; }
    .desktop-app-layout .sidebar-wrapper { position: fixed !important; }
    .app-main { height: auto !important; overflow: auto !important; }
    .main-header { padding: 0 14px 0 68px; height: 60px; }
    .type-badge { display: none; }
    .workspace { padding: 12px; }
    .two-col { grid-template-columns: 1fr; }
}
@media print { .sidebar-wrapper, .main-header { display: none !important; } .desktop-app-layout { display: block; } }
</style>
@endsection