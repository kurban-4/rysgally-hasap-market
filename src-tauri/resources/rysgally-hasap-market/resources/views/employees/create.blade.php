@extends('layouts.app')

@section('content')

@include('app.navbar')
<style>
    :root { --main-color: #107A87; }
    .btn-main { background-color: var(--main-color); color: white; border: none; transition: 0.3s; }
    .btn-main:hover { background-color: #0d636d; color: white; transform: translateY(-2px); }
    .form-control:focus { border-color: var(--main-color); box-shadow: 0 0 0 0.25 darkcyan; }
    .card-custom { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .header-gradient { 
        background: linear-gradient(135deg, #107A87 0%, #0a4d53 100%); 
        color: white; 
        border-radius: 20px 20px 0 0; 
        padding: 30px;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-custom">
                <div class="header-gradient text-center">
                    <h2 class="fw-bold mb-0">{{__("app.form_new_employee")}}</h2>
                    <p class="opacity-75 mb-0">{{__("app.form_subtitle")}}</p>
                </div>
                
                <div class="card-body p-5">
                    <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">{{__("app.label_fullname")}}</label>
                                <input type="text" name="name" class="form-control form-control-lg bg-light" placeholder="{{ __('app.placeholder_name') }}" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">{{__("app.label_position")}}</label>
                                <input type="text" name="position" class="form-control form-control-lg bg-light" placeholder="{{ __('app.placeholder_position') }}" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">{{__("app.label_email")}}</label>
                            <input type="email" name="email" class="form-control form-control-lg bg-light" placeholder="example@mail.com" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">{{__("app.label_phone")}}</label>
                                <input type="text" name="phone" class="form-control form-control-lg bg-light" placeholder="+993 (___) ___--__">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">{{__("app.shift")}}</label>
                                <input type="text" name="schedule" class="form-control form-control-lg bg-light" placeholder="{{ __('app.placeholder_shift') }}">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">{{__("app.salary")}}</label>
                                <input type="text" name="salary" class="form-control form-control-lg bg-light" placeholder="2000...">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">{{__("app.label_hire_date")}}</label>
                                <input type="date" name="hire_date" class="form-control form-control-lg bg-light">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">{{__("app.label_bio")}}</label>
                            <textarea name="bio" class="form-control bg-light" rows="4" placeholder="{{ __('app.placeholder_bio') }}"></textarea>
                        </div>

                        <hr class="my-4 text-muted">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('employees.index') }}" class="text-decoration-none text-muted fw-bold">
                                <i class="bi bi-arrow-left"></i> {{__("app.btn_back_list")}}
                            </a>
                            <button type="submit" class="btn btn-main btn-lg px-5 rounded-pill shadow">
                                {{__("app.btn_save_profile")}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection