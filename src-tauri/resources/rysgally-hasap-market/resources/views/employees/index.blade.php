@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main">
        <header class="main-header">
            <div class="header-left">
                <h4 class="fw-bold mb-0 text-dark">{{__("app.personnel_title")}}</h4>
                <p class="text-muted small mb-0 d-none d-md-block">{{__("app.personnel_subtitle")}}</p>
            </div>
            
            <div class="header-right">
                @if(session('success'))
                    <div class="alert alert-success py-2 px-3 mb-0 rounded-3 small shadow-sm animate-fade-in d-none d-md-block">
                        {{session('success')}}
                    </div>
                @endif
                <a href="{{route('employees.create')}}" class="btn btn-teal-action shadow-sm">
                    <i class="bi bi-plus-lg"></i>
                    <span class="btn-label">{{__("app.btn_add_profile")}}</span>
                </a>
            </div>
        </header>

        <div class="workspace custom-scrollbar p-3 p-md-4">
            @if(session('success'))
                <div class="alert alert-success py-2 px-3 mb-3 rounded-3 small shadow-sm animate-fade-in d-md-none">
                    {{session('success')}}
                </div>
            @endif

            <div class="employee-list">
                @foreach($employees as $employee)
                <div class="employee-glass-card shadow-sm">
                    <div class="card-inner">
                        {{-- Avatar --}}
                        <div class="avatar-square">
                            {{ substr($employee->name, 0, 1) }}
                        </div>
                        
                        {{-- Name + ID --}}
                        <div class="emp-identity">
                            <h5 class="fw-bold mb-0 text-dark">{{ $employee->name }}</h5>
                            <span class="text-teal smaller fw-bold ls-1">ID: #{{ str_pad($employee->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>

                            <div class="stat-item">
                                <div class="stat-label">{{__("app.label_salary")}}</div>
                                <div class="stat-value text-success">${{ number_format($employee->salary, 0) }}</div>
                            </div>
                            <div class="stat-item d-none d-sm-block">
                                <div class="stat-label">{{__("app.label_schedule")}}</div>
                                <div class="stat-value text-dark">{{ $employee->schedule }}</div>
                            </div>
                        </div>

                        {{-- Action --}}
                        <div class="emp-action">
                            <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-outline-teal-sm">
                                <span class="d-none d-md-inline">{{__("app.btn_details")}}</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach

                @if($employees->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-people display-1 opacity-25 d-block mb-3 text-teal"></i>
                    <p class="text-muted fw-medium">No employees found</p>
                    <a href="{{route('employees.create')}}" class="btn btn-teal-action mt-2">
                        <i class="bi bi-plus-lg me-2"></i>Add First Employee
                    </a>
                </div>
                @endif
            </div>
        </div>
    </main>
</div>

<style>
.desktop-app-layout { display: flex; width: 100%; overflow: hidden; }
.app-main { flex: 1; display: flex; flex-direction: column; background-color: #f4f7f6; min-width: 0; overflow: hidden; }

.main-header {
    height: 70px;
    background: white;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    gap: 12px;
}

.header-left h4 { font-size: 1.1rem; }
.header-right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }

.workspace { flex: 1; overflow-y: auto; }
.employee-list { display: flex; flex-direction: column; gap: 12px; }
.employee-glass-card {
    background: white;
    border-radius: 16px;
    border: 1px solid rgba(0,0,0,0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.employee-glass-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
}

.card-inner {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    flex-wrap: wrap;
}

.avatar-square {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, #E8722A 0%, #0a4d53 100%);
    border-radius: 12px;
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; font-weight: 800;
    box-shadow: 0 5px 15px rgba(16, 122, 132, 0.2);
    flex-shrink: 0;
}

.emp-identity { min-width: 140px; flex: 1; }
.emp-identity h5 { font-size: 1rem; }

.emp-stats {
    display: flex;
    gap: 24px;
    flex: 2;
    border-left: 1px solid #f1f5f9;
    padding-left: 20px;
}

.stat-item { flex: 1; min-width: 60px; }

.emp-action { margin-left: auto; flex-shrink: 0; }

.text-teal { color: #E8722A; }
.stat-label { font-size: 0.62rem; color: #a0aec0; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
.stat-value { font-size: 0.95rem; font-weight: 700; }
.smaller { font-size: 0.75rem; }
.ls-1 { letter-spacing: 0.5px; }
.btn-teal-action {
    background: #E8722A; color: white;
    border-radius: 10px; padding: 9px 16px;
    font-weight: 600; border: none;
    display: flex; align-items: center; gap: 6px;
    white-space: nowrap;
}
.btn-teal-action:hover { background: #0d636b; color: white; }

.btn-outline-teal-sm {
    border: 1.5px solid #E8722A; color: #E8722A;
    border-radius: 8px; font-weight: 600;
    font-size: 0.85rem; padding: 7px 14px;
    transition: 0.2s; display: flex; align-items: center; gap: 4px;
    white-space: nowrap;
}
.btn-outline-teal-sm:hover { background: #E8722A; color: white; }
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.animate-fade-in { animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }
@media (max-width: 1023px) {
    .emp-stats { gap: 12px; padding-left: 12px; }
    .stat-value { font-size: 0.85rem; }
}
@media (max-width: 767px) {
    .app-main { overflow-y: auto; }
    .desktop-app-layout { overflow: auto; }

    .main-header { padding: 0 15px 0 70px; height: 60px; }
    .header-left h4 { font-size: 1rem; }
    .btn-label { display: none; }
    .btn-teal-action { padding: 9px 12px; }

    .workspace { padding: 12px !important; }

    .card-inner {
        display: grid;
        grid-template-columns: auto 1fr auto;
        grid-template-rows: auto auto;
        gap: 10px 12px;
        padding: 14px;
    }

    .avatar-square { width: 46px; height: 46px; font-size: 1.1rem; grid-row: 1 / 3; }
    .emp-identity { grid-column: 2; grid-row: 1; }
    .emp-stats {
        grid-column: 2;
        grid-row: 2;
        border-left: none;
        padding-left: 0;
        border-top: 1px solid #f1f5f9;
        padding-top: 8px;
        gap: 16px;
    }
    .emp-action { grid-column: 3; grid-row: 1 / 3; }
    .stat-label { font-size: 0.58rem; }
    .stat-value { font-size: 0.82rem; }
}
</style>
@endsection