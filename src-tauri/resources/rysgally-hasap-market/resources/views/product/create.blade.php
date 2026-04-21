@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main">

        {{-- Page header --}}
        <header class="page-header">
            <a href="{{ route('storage.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="header-eyebrow">Storage</div>
                <h4 class="header-title">Add New Product</h4>
            </div>
        </header>

        <div class="workspace custom-scrollbar">
            <div class="form-wrap">

                @if($errors->any())
                <div class="alert-error mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                </div>
                @endif

                <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-grid">

                        {{-- ══ LEFT COLUMN ══ --}}
                        <div class="form-col">

                            {{-- IDENTITY CARD --}}
                            <div class="form-card">
                                <div class="card-eyebrow">
                                    <span class="card-dot orange"></span> Product Identity
                                </div>

                                <div class="field-row">
                                    <div class="field-full">
                                        <label class="lbl">Product Name *</label>
                                        <input type="text" name="name" class="inp" value="{{ old('name') }}"
                                               placeholder="e.g. Organic Apples" required>
                                    </div>
                                </div>

                                <div class="field-row two-col">
                                    <div>
                                        <label class="lbl">Category</label>
                                        <input list="cat-list" name="category" class="inp"
                                               value="{{ old('category') }}" placeholder="e.g. Produce">
                                        <datalist id="cat-list">
                                            @foreach($categories as $c)<option value="{{ $c }}">@endforeach
                                        </datalist>
                                    </div>
                                    <div>
                                        <label class="lbl">Manufacturer / Origin</label>
                                        <input type="text" name="manufacturer" class="inp"
                                               value="{{ old('manufacturer') }}" placeholder="e.g. Local Farm">
                                    </div>
                                </div>

                                <div>
                                    <label class="lbl">Description</label>
                                    <textarea name="description" class="inp" rows="3"
                                              placeholder="Optional product notes...">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            {{-- ITEM TYPE CARD --}}
                            <div class="form-card">
                                <div class="card-eyebrow">
                                    <span class="card-dot green"></span> Item Type
                                </div>

                                <div class="type-selector">
                                    <label class="type-opt" id="opt-weight">
                                        <input type="radio" name="unit_type" value="weight"
                                               {{ old('unit_type')==='weight'?'checked':'' }}
                                               onchange="switchType('weight')">
                                        <div class="type-card">
                                            <i class="bi bi-speedometer2"></i>
                                            <div class="type-name">Weighable</div>
                                            <div class="type-desc">Sold by kg, g, litre</div>
                                        </div>
                                    </label>
                                    <label class="type-opt" id="opt-unit">
                                        <input type="radio" name="unit_type" value="piece"
                                               {{ old('unit_type')==='unit'?'checked':'' }}
                                               onchange="switchType('unit')">
                                        <div class="type-card">
                                            <i class="bi bi-123"></i>
                                            <div class="type-name">Unit / Each</div>
                                            <div class="type-desc">Single loose items</div>
                                        </div>
                                    </label>
                                </div>

                                {{-- Dynamic fields --}}
                                
                                <div id="fields-weight" class="mt-4 type-fields d-none">
                                    <div class="field-row two-col">
                                        <div>
                                            <label class="lbl">Quantity to add</label>
                                            <div class="inp-suffix-wrap">
                                                <input type="number" name="quantity_weight" class="inp" step="0.001" min="0" value="{{ old('quantity_weight',0) }}" placeholder="0.000">
                                                <span class="inp-suffix" id="weight-suffix">kg</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="lbl">Scale / Unit</label>
                                            <div class="unit-chips" id="chips-weight">
                                                @foreach(['kg','g','litre','ml'] as $u)
                                                <label class="chip">
                                                    <input type="radio" name="unit_label" value="{{ $u }}" onchange="document.getElementById('weight-suffix').textContent=this.value">
                                                    <span>{{ $u }}</span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="fields-unit" class="mt-4 type-fields d-none">
                                    <div class="field-row two-col">
                                        <div>
                                            <label class="lbl">Quantity (pcs)</label>
                                            <input type="number" name="quantity_units" class="inp" min="0" value="{{ old('quantity_units',0) }}" placeholder="0">
                                        </div>
                                        <div>
                                            <label class="lbl">Unit Label</label>
                                            <div class="unit-chips">
                                                @foreach(['pcs','each','piece'] as $u)
                                                <label class="chip">
                                                    <input type="radio" name="unit_label" value="{{ $u }}" {{ old('unit_label','pcs')===$u?'checked':'' }}>
                                                    <span>{{ $u }}</span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- BARCODE CARD --}}
                            <div class="form-card">
                                <div class="card-eyebrow">
                                    <span class="card-dot amber"></span> Identification
                                </div>
                                <div class="field-row two-col">
                                    <div>
                                        <label class="lbl">Barcode</label>
                                        <div class="inp-icon-wrap">
                                            <i class="bi bi-upc-scan"></i>
                                            <input type="text" name="barcode" class="inp" value="{{ old('barcode') }}" placeholder="Scan or enter">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="lbl">Product Code</label>
                                        <input type="text" name="product_code" class="inp" value="{{ old('product_code') }}" placeholder="For weight barcodes">
                                    </div>
                                </div>
                                <div class="field-row two-col mt-3">
                                    <div>
                                        <label class="lbl">Produced Date</label>
                                        <input type="date" name="produced_date" class="inp" value="{{ old('produced_date') }}">
                                    </div>
                                    <div>
                                        <label class="lbl">Expiry Date</label>
                                        <input type="date" name="expiry_date" class="inp" value="{{ old('expiry_date') }}">
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- ══ RIGHT COLUMN ══ --}}
                        <div class="form-col right-col">

                            {{-- PRICE CARD --}}
                            <div class="form-card pricing-card">
                                <div class="card-eyebrow">
                                    <span class="card-dot orange"></span> Pricing
                                </div>

                                <div class="price-preview" id="pricePreview">
                                    <div class="price-display" id="priceDisplay">$0.00</div>
                                    <div id="discountBadge" class="discount-badge d-none"></div>
                                </div>

                                <div class="field-row mt-3">
                                    <label class="lbl">Selling Price *</label>
                                    <div class="inp-icon-wrap">
                                        <span class="inp-prefix">$</span>
                                        <input type="number" name="price" id="priceInput"
                                               class="inp" step="0.01" min="0"
                                               value="{{ old('price') }}" placeholder="0.00" required>
                                    </div>
                                </div>

                                <div class="field-row mt-3">
                                    <label class="lbl">Received / Purchase Price</label>
                                    <div class="inp-icon-wrap">
                                        <span class="inp-prefix">$</span>
                                        <input type="number" name="received_price"
                                               class="inp" step="0.01" min="0"
                                               value="{{ old('received_price') }}" placeholder="0.00">
                                    </div>
                                </div>

                                <div class="field-row mt-3">
                                    <label class="lbl">Discount (%)</label>
                                    <div class="discount-slider-wrap">
                                        <input type="range" name="discount" id="discountRange"
                                               min="0" max="100" step="1"
                                               value="{{ old('discount',0) }}"
                                               class="discount-range"
                                               oninput="syncDiscount(this.value)">
                                        <div class="discount-value-wrap">
                                            <input type="number" id="discountInput" min="0" max="100"
                                                   class="discount-num" value="{{ old('discount',0) }}"
                                                   oninput="syncDiscountFromInput(this.value)">
                                            <span>%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- BATCH CARD --}}
                            <div class="form-card">
                                <div class="card-eyebrow">
                                    <span class="card-dot green"></span> Batch Info
                                </div>
                                <div class="field-row">
                                    <label class="lbl">Batch Number</label>
                                    <input type="text" name="batch_number" class="inp"
                                           value="{{ old('batch_number') }}" placeholder="e.g. BT-2024-001">
                                </div>
                            </div>

                            {{-- SAVE BUTTON --}}
                            <button type="submit" class="btn-save">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>{{ __('app.btn_save_product') }}</span>
                            </button>

                            <p class="save-note">* Required fields. Stock will be updated immediately.</p>

                        </div>
                    </div>
                </form>

            </div>
        </div>
    </main>
</div>

<script>

function switchType(type) {
    const weightFields = document.getElementById('fields-weight');
    const unitFields = document.getElementById('fields-unit');
    
    if (type === 'weight') {
        weightFields.classList.remove('d-none');
        unitFields.classList.add('d-none');
    } else {
        weightFields.classList.add('d-none');
        unitFields.classList.remove('d-none');
    }
}document.addEventListener('DOMContentLoaded', function() {
    // Ищем unit_type, а не item_type
    const checked = document.querySelector('input[name="unit_type"]:checked');
    // Если ничего не выбрано, по умолчанию открываем 'piece' (или 'unit' по вашему ID)
    switchType(checked ? checked.value : 'piece');
    updatePrice();
});
// ── Price calculator ─────────────────────────────────────────────
const priceInput    = document.getElementById('priceInput');
const discountRange = document.getElementById('discountRange');
const discountInput = document.getElementById('discountInput');
const priceDisplay  = document.getElementById('priceDisplay');
const badge         = document.getElementById('discountBadge');

function updatePrice() {
    const p = parseFloat(priceInput?.value || 0);
    const d = parseInt(discountRange?.value || 0);
    const final = d > 0 ? p * (1 - d/100) : p;
    priceDisplay.textContent = '$' + final.toFixed(2);
    if (d > 0 && p > 0) {
        badge.textContent = d + '% OFF';
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

function syncDiscount(v) {
    discountInput.value = v;
    updatePrice();
}
function syncDiscountFromInput(v) {
    const clamped = Math.min(100, Math.max(0, parseInt(v)||0));
    discountRange.value = clamped;
    discountInput.value = clamped;
    updatePrice();
}

if (priceInput)    priceInput.addEventListener('input', updatePrice);
if (discountRange) discountRange.addEventListener('input', updatePrice);
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap');

:root {
    --ora: #E8722A;
    --ora-dark: #C4561A;
    --ora-light: #FFF0E6;
    --ora-glow: rgba(232,114,42,0.2);
    --green: #2E7D32;
    --green-light: #E8F5E9;
    --amber: #F9A825;
    --amber-light: #FFFDE7;
    --bg: #FBF7F3;
    --card-bg: #FFFFFF;
    --border: #EDE4DA;
    --text: #1A0A00;
    --muted: #8B7355;
}

*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'DM Sans', sans-serif; background: var(--bg); }

/* ── LAYOUT ── */
.desktop-app-layout { position: fixed; inset: 0; display: flex; overflow: hidden; }
.desktop-app-layout .sidebar-wrapper { position: relative !important; flex-shrink: 0; height: 100%; }
.app-main { flex: 1; min-width: 0; display: flex; flex-direction: column; overflow: hidden; height: 100%; }

/* ── PAGE HEADER ── */
.page-header {
    height: 68px;
    background: var(--card-bg);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; padding: 0 24px; gap: 16px;
    flex-shrink: 0;
}
.btn-back {
    width: 38px; height: 38px; min-width: 38px;
    border-radius: 11px; border: none;
    background: var(--ora-light); color: var(--ora);
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; font-size: 1rem; cursor: pointer; transition: 0.18s;
}
.btn-back:hover { background: var(--ora); color: white; transform: translateX(-2px); }
.header-eyebrow { font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); }
.header-title { font-family: 'Sora', sans-serif; font-size: 1.05rem; font-weight: 700; color: var(--text); margin: 0; }

/* ── WORKSPACE ── */
.workspace { flex: 1; overflow-y: auto; padding: 24px; }
.form-wrap { max-width: 1120px; margin: 0 auto; }

/* ── FORM GRID ── */
.form-grid { display: grid; grid-template-columns: 1fr 360px; gap: 20px; align-items: start; }
.form-col { display: flex; flex-direction: column; gap: 16px; }

/* ── CARD ── */
.form-card {
    background: var(--card-bg);
    border-radius: 20px;
    padding: 24px;
    border: 1px solid var(--border);
    box-shadow: 0 2px 12px rgba(26,10,0,0.04);
}
.card-eyebrow {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;
    color: var(--muted); margin-bottom: 20px;
}
.card-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.card-dot.orange { background: var(--ora); box-shadow: 0 0 8px var(--ora-glow); }
.card-dot.green  { background: var(--green); }
.card-dot.amber  { background: var(--amber); }

/* ── FIELDS ── */
.field-row { margin-bottom: 0; }
.field-full { width: 100%; }
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.mt-3 { margin-top: 14px; }
.mt-4 { margin-top: 18px; }
.mt-2 { margin-top: 10px; }
.lbl {
    display: block;
    font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px;
    color: var(--muted); margin-bottom: 7px;
}
.inp {
    width: 100%; border: 1.5px solid var(--border);
    border-radius: 12px; padding: 11px 14px;
    font-size: 0.875rem; font-family: 'DM Sans', sans-serif;
    background: #FDFAF7; color: var(--text);
    outline: none; transition: 0.18s;
}
.inp:focus { border-color: var(--ora); background: white; box-shadow: 0 0 0 3px rgba(232,114,42,0.1); }
textarea.inp { resize: vertical; }

.inp-icon-wrap {
    display: flex; align-items: center;
    background: #FDFAF7; border: 1.5px solid var(--border); border-radius: 12px; overflow: hidden;
    transition: 0.18s;
}
.inp-icon-wrap:focus-within { border-color: var(--ora); background: white; box-shadow: 0 0 0 3px rgba(232,114,42,0.1); }
.inp-icon-wrap i { padding: 0 12px; color: var(--muted); font-size: 0.9rem; flex-shrink: 0; }
.inp-prefix { padding: 0 12px; color: var(--muted); font-size: 0.9rem; font-weight: 600; flex-shrink: 0; }
.inp-icon-wrap .inp { border: none; background: transparent; box-shadow: none; padding-left: 0; }
.inp-icon-wrap .inp:focus { box-shadow: none; }

.inp-suffix-wrap { display: flex; align-items: center; gap: 0; }
.inp-suffix-wrap .inp { flex: 1; border-radius: 12px 0 0 12px; }
.inp-suffix {
    background: var(--ora-light); color: var(--ora); border: 1.5px solid var(--border);
    border-left: none; border-radius: 0 12px 12px 0;
    padding: 11px 14px; font-size: 0.8rem; font-weight: 700;
    flex-shrink: 0; white-space: nowrap;
}

/* ── TYPE SELECTOR ── */
.type-selector { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; }
.type-opt input { display: none; }
.type-card {
    border: 1.5px solid var(--border); border-radius: 14px;
    padding: 16px 12px; text-align: center; cursor: pointer;
    transition: 0.18s; background: #FDFAF7;
}
.type-card i { font-size: 1.4rem; color: var(--muted); margin-bottom: 8px; display: block; }
.type-name { font-size: 0.8rem; font-weight: 700; color: var(--text); line-height: 1.2; }
.type-desc { font-size: 0.65rem; color: var(--muted); margin-top: 3px; }
.type-opt input:checked + .type-card,
.type-active .type-card {
    border-color: var(--ora);
    background: var(--ora-light);
    box-shadow: 0 0 0 3px rgba(232,114,42,0.12);
}
.type-opt input:checked + .type-card i { color: var(--ora); }

/* ── UNIT CHIPS ── */
.unit-chips { display: flex; flex-wrap: wrap; gap: 7px; }
.chip input { display: none; }
.chip span {
    display: inline-block; padding: 5px 12px; border-radius: 50px;
    font-size: 0.75rem; font-weight: 700;
    border: 1.5px solid var(--border); background: #FDFAF7;
    cursor: pointer; transition: 0.15s; color: var(--muted);
}
.chip input:checked + span {
    background: var(--ora); color: white; border-color: var(--ora);
    box-shadow: 0 2px 8px var(--ora-glow);
}

/* ── PRICING CARD ── */
.pricing-card { border-top: 3px solid var(--ora); }
.price-preview {
    background: linear-gradient(135deg, #1A0A00, #2E1500);
    border-radius: 14px; padding: 24px;
    text-align: center; margin-bottom: 6px;
    position: relative; overflow: hidden;
}
.price-preview::before {
    content: '';
    position: absolute; bottom: -20px; right: -20px;
    width: 100px; height: 100px; border-radius: 50%;
    background: rgba(232,114,42,0.1);
    pointer-events: none;
}
.price-display {
    font-family: 'Sora', sans-serif;
    font-size: 2.6rem; font-weight: 800; color: white; line-height: 1;
}
.discount-badge {
    display: inline-block; margin-top: 8px;
    background: var(--ora); color: white;
    font-size: 0.72rem; font-weight: 800;
    padding: 4px 12px; border-radius: 50px;
    box-shadow: 0 3px 10px var(--ora-glow);
}

.discount-slider-wrap { display: flex; align-items: center; gap: 12px; }
.discount-range {
    flex: 1; height: 4px; border-radius: 4px;
    accent-color: var(--ora); cursor: pointer;
}
.discount-value-wrap { display: flex; align-items: center; gap: 4px; flex-shrink: 0; }
.discount-num {
    width: 52px; border: 1.5px solid var(--border); border-radius: 9px;
    padding: 7px 8px; font-size: 0.9rem; font-weight: 700;
    text-align: center; background: #FDFAF7; color: var(--text);
    outline: none; font-family: 'DM Sans', sans-serif;
}
.discount-num:focus { border-color: var(--ora); }

/* ── SAVE BUTTON ── */
.btn-save {
    width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px;
    background: linear-gradient(135deg, var(--ora), var(--ora-dark));
    color: white; border: none; border-radius: 16px; padding: 17px;
    font-family: 'Sora', sans-serif; font-size: 1rem; font-weight: 700;
    cursor: pointer; transition: 0.2s;
    box-shadow: 0 6px 20px var(--ora-glow);
}
.btn-save:hover { transform: translateY(-2px); box-shadow: 0 10px 30px var(--ora-glow); }
.save-note { font-size: 0.72rem; color: var(--muted); text-align: center; margin-top: 10px; }

/* ── ERROR ── */
.alert-error {
    display: flex; align-items: flex-start; gap: 10px;
    background: #FFF5F5; border: 1.5px solid #FEC5C5;
    border-radius: 14px; padding: 14px 18px;
    font-size: 0.83rem; color: #C53030; font-weight: 600;
}

/* ── SCROLLBAR ── */
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #D4C4B0; border-radius: 10px; }

.d-none { display: none !important; }

/* ── RESPONSIVE ── */
@media (max-width: 1060px) { .form-grid { grid-template-columns: 1fr; } }
@media (max-width: 767px) {
    .desktop-app-layout { position: relative; inset: auto; flex-direction: column; min-height: 100vh; height: auto !important; overflow: auto !important; }
    .desktop-app-layout .sidebar-wrapper { position: fixed !important; }
    .app-main { height: auto !important; overflow: auto !important; }
    .page-header { padding: 0 14px 0 68px; }
    .workspace { padding: 14px; }
    .two-col { grid-template-columns: 1fr; }
    .type-selector { grid-template-columns: 1fr 1fr; }
}
</style>
@endsection