@extends('layouts.app')

@section('content')
@include('app.navbar')

<style>
    :root {
        --main-teal: #E8722A;
        --main-teal-hover: #C85A1A;
    }

    .btn-main {
        background-color: var(--main-teal);
        border-color: var(--main-teal);
        color: white;
    }

    .btn-main:hover {
        background-color: var(--main-teal-hover);
        color: white;
    }

    .text-main {
        color: var(--main-teal);
    }

    .form-control:focus {
        border-color: var(--main-teal);
        box-shadow: 0 0 0 0.25 dashed rgba(20, 122, 125, 0.25);
    }
</style>

<div class="container py-5">
    <div class="card shadow-lg border-0 rounded-4 mx-auto" style="max-width: 900px;">
        <div class="card-header bg-white border-0 pt-4 ps-4">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="fw-bold text-main mb-0">
                    <i class="fas fa-plus-circle me-2"></i>{{ __('app.wholesale_storage_create_title') }}
                </h3>
                <a href="{{ route('wholesale_storage.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('app.wholesale_storage_create_back') }}
                </a>
            </div>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('wholesale_storage.store') }}" method="POST">
                @csrf
                <div class="row g-4">

                    {{-- MANUAL product NAME --}}
                    <div class="col-12">
                        <label class="form-label fw-bold small text-muted">
                            <i class="bi bi-capsule me-1"></i> {{ __('app.wholesale_storage_create_product_name') }}
                        </label>

                        <input type="text" name="product_name"
                            class="form-control border-0 bg-light p-3"
                            placeholder="{{ __('app.wholesale_storage_create_product_placeholder') }}"
                            required style="border-radius: 12px;">
                    </div>

                    {{-- REAL product ID (OPTIONAL NOW!) --}}
                    <div class="col-12">
                        <label class="form-label fw-bold small text-muted">
                            <i class="bi bi-list-check me-1"></i> {{ __('app.wholesale_storage_create_select_product') }}
                        </label>

                        <select name="product_id" class="form-select p-3 bg-light border-0" style="border-radius: 12px;">
                            <option value="">{{ __('app.wholesale_storage_create_new_product') }}</option>
                            @foreach($products as $med)
                                <option value="{{ $med->id }}"
                                        data-unit-type="{{ $med->unit_type ?? 'piece' }}"
                                        data-units="{{ $med->units_per_box ?? 1 }}">
                                    {{ $med->name }}
                                </option>
                            @endforeach
                        </select>

                        <small class="text-muted">
                            {{ __('app.wholesale_storage_create_select_help') }}
                        </small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('app.wholesale_storage_create_unit_type') }}</label>
                        <select name="unit_type" id="unit_type" class="form-select bg-light border-0">
                            <option value="piece" {{ old('unit_type') === 'piece' ? 'selected' : '' }}>{{ __('app.wholesale_storage_create_unit_item') }}</option>
                            <option value="weight" {{ old('unit_type') === 'weight' ? 'selected' : '' }}>{{ __('app.wholesale_storage_create_unit_weight') }}</option>
                        </select>
                        <small class="text-muted">{{ __('app.wholesale_storage_create_unit_help') }}</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold" id="quantity_label">{{ __('app.wholesale_storage_create_quantity') }}</label>
                        <input type="number" step="any" name="quantity" id="quantity" class="form-control bg-light border-0" placeholder="{{ __('app.wholesale_storage_create_quantity_placeholder') }}" required>
                        <small class="text-muted" id="quantity_help">{{ __('app.wholesale_storage_create_quantity_help') }}</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('app.wholesale_storage_create_batch_number') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0 text-muted">#</span>
                            <input type="text" name="batch_number" class="form-control bg-light border-0" placeholder="{{ __('app.wholesale_storage_create_batch_placeholder') }}">
                        </div>
                    </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-danger">{{ __('app.wholesale_storage_create_expiry_date') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-calendar-event text-danger"></i></span>
                                <input type="date" name="expiry_date" class="form-control bg-light border-0" required>
                            </div>
                        </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('app.wholesale_storage_create_received_price') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0 text-muted">$</span>
                            <input type="number" step="0.01" name="received_price" class="form-control bg-light border-0" placeholder="90" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('app.wholesale_storage_create_selling_price') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0 text-muted">$</span>
                            <input type="number" step="0.01" id="selling_price" name="selling_price" class="form-control bg-light border-0" placeholder="60" required>
                        </div>
                    </div>
                    
                    <div class="col-12 mt-4 text-center">
                        <button type="submit" class="btn btn-main btn-lg w-100 rounded-pill py-3 fw-bold shadow">
                            <i class="fas fa-check-circle me-2"></i>{{ __('app.wholesale_storage_create_btn_save') }}
                        </button>
                        <a href="{{ route('wholesale_storage.index') }}" class="btn btn-link text-muted mt-2 text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i> {{ __('app.wholesale_storage_create_back') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>



@endsection
