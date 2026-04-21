@extends('layouts.app')

@section('content')
@include('app.navbar')
<style>
    :root {
        --main-color: #E8722A;
        --main-hover: #0d636b;
    }

    body {
        background-color: #f4f7f6;
        font-family: 'Inter', sans-serif;
    }

    .bg-main {
        background-color: var(--main-color) !important;
    }

    .text-main {
        color: var(--main-color) !important;
    }

    .btn-main {
        background-color: var(--main-color);
        color: white;
        border: none;
        transition: 0.3s;
    }

    .btn-main:hover {
        background-color: var(--main-hover);
        color: white;
        transform: translateY(-2px);
    }

    .btn-outline-main {
        border: 2px solid var(--main-color);
        color: var(--main-color);
        font-weight: 600;
    }

    .btn-outline-main:hover {
        background-color: var(--main-color);
        color: white;
    }
</style>


<div class="container py-5">
    <div class="card border-0 shadow-lg rounded-5 overflow-hidden mb-5">
        <div class="row g-0">
            <div class="col-lg-5 p-5 text-white d-flex flex-column justify-content-center" style="background: linear-gradient(45deg, #E8722A 0%, #0a4d53 100%);">
                <div class="mb-4">
                    <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center text-main shadow-lg" style="width: 120px; height: 120px;">
                        <i class="bi bi-person-badge-fill" style="font-size: 4rem;"></i>
                    </div>
                </div>
                <h1 class="display-3 fw-bold">{{ $employee->name }}</h1>
                <p class="fs-4 opacity-75">Position: {{ $employee->position }}</p>
                <div class="mt-4">
                    <a href="{{ route('employees.index') }}" class="btn btn-light rounded-pill px-4 fw-bold text-main">
                        <i class="bi bi-arrow-left me-2"></i>{{__("app.btn_back_list")}}
                    </a>
                </div>
            </div>

            <div class="col-lg-7 p-5 bg-white">
                <h3 class="fw-bold mb-5 text-dark border-bottom pb-3">{{__("app.employee_card")}}</h3>

                <div class="row g-5">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="p-3 rounded-4 bg-light text-main me-3 shadow-sm"><i class="bi bi-telephone-fill fs-4"></i></div>
                            <div>
                                <div class="text-muted small fw-bold text-uppercase">{{__("app.label_phone_short")}}</div>
                                <div class="fs-5 fw-bold text-dark">{{ $employee->phone }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="p-3 rounded-4 bg-light text-main me-3 shadow-sm"><i class="bi bi-cash-stack fs-4"></i></div>
                            <div>
                                <div class="text-muted small fw-bold text-uppercase">{{__("app.label_salary_monthly")}}</div>
                                <div class="fs-5 fw-bold text-success">${{ number_format($employee->salary, 0) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="p-3 rounded-4 bg-light text-main me-3 shadow-sm"><i class="bi bi-calendar3 fs-4"></i></div>
                            <div>
                                <div class="text-muted small fw-bold text-uppercase">{{__("app.label_schedule_work")}}</div>
                                <div class="fs-5 fw-bold text-dark">{{ $employee->schedule }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="p-3 rounded-4 bg-light text-main me-3 shadow-sm"><i class="bi bi-shield-check fs-4"></i></div>
                            <div>
                                <div class="text-muted small fw-bold text-uppercase">{{__("app.label_access")}}</div>
                                <div class="fs-5 fw-bold text-dark">{{__("app.role_admin")}}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-top d-flex gap-3">
                    <button class="btn btn-main px-5 rounded-pill shadow">{{__("app.btn_edit_data")}}</button>
                    <button class="btn btn-outline-danger px-4 rounded-pill">{{__("app.btn_remove_system")}}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection