@extends('layouts.app')

@section('content')
@include('app.navbar')
<div class="container-fluid px-4 py-4" style="background: #f4f7f6; min-height: 100vh;">

    {{-- ШАПКА --}}
    <div class="d-flex justify-content-between align-items-center mb-4 p-4 bg-white rounded-4 shadow-sm" style="border-left: 6px solid #ef4444;">
        <div>
            <h2 class="fw-black mb-0" style="color: #1a3a3a;">Управление <span class="text-danger">расходами</span></h2>
            <p class="text-muted small fw-bold text-uppercase mb-0" style="letter-spacing: 0.1em;">Учёт всех трат</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('boss.dashboard') }}" class="btn btn-light rounded-3 fw-bold">
                <i class="bi bi-arrow-left me-1"></i> Назад
            </a>
            <button class="btn text-white fw-black rounded-3 px-4" style="background: #E8722A;" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                + Добавить
            </button>
            <a href="{{ route('boss.expense.export') }}"
   class="btn btn-outline-success fw-bold rounded-3 px-3"
   title="Export to Excel">
    <i class="bi bi-file-earmark-excel-fill"></i>
</a>
        </div>
    </div>

    {{-- КАРТОЧКА ИТОГО --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 rounded-4 p-4 shadow-sm" style="border-left: 5px solid #ef4444 !important;">
                <p class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.1em;">Всего потрачено</p>
                <h2 class="fw-black text-danger mb-0">{{ number_format($totalExpenses, 2) }} TMT</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 p-4 shadow-sm" style="border-left: 5px solid #f59e0b !important;">
                <p class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.1em;">За этот месяц</p>
                <h2 class="fw-black mb-0" style="color: #f59e0b;">
                    {{ number_format(\App\Models\Expense::whereMonth('created_at', now()->month)->sum('amount'), 2) }} TMT
                </h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 p-4 shadow-sm" style="border-left: 5px solid #E8722A !important;">
                <p class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.1em;">За сегодня</p>
                <h2 class="fw-black mb-0" style="color: #E8722A;">
                    {{ number_format(\App\Models\Expense::whereDate('created_at', today())->sum('amount'), 2) }} TMT
                </h2>
            </div>
        </div>
    </div>

    {{-- ТАБЛИЦА --}}
    <div class="card border-0 rounded-4 shadow-sm p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th class="text-muted small fw-bold text-uppercase py-3" style="letter-spacing: 0.08em;">Дата</th>
                        <th class="text-muted small fw-bold text-uppercase py-3" style="letter-spacing: 0.08em;">Описание</th>
                        <th class="text-muted small fw-bold text-uppercase py-3 text-end" style="letter-spacing: 0.08em;">Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $ex)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td class="py-3">
                            <small class="text-muted fw-bold">{{ $ex->created_at->format('d.m.Y') }}</small><br>
                            <small class="text-muted" style="font-size: 11px;">{{ $ex->created_at->format('H:i') }}</small>
                        </td>
                        <td class="py-3 fw-bold text-dark">{{ $ex->title }}</td>
                        <td class="py-3 text-end">
                            <span class="fw-black text-danger">-{{ number_format($ex->amount, 2) }} TMT</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $expenses->links() }}</div>
    </div>
</div>

{{-- МОДАЛЬНОЕ ОКНО --}}
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 p-2">
            <form action="{{ route('boss.expense.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <h5 class="fw-black mb-1">Новый расход</h5>
                    <p class="text-muted small mb-4">Заполните данные о трате</p>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted text-uppercase mb-1" style="letter-spacing: 0.08em;">Описание</label>
                        <input type="text" name="title" class="form-control border-0 rounded-3 p-3" style="background: #f4f7f6;" placeholder="На что потратили?" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted text-uppercase mb-1" style="letter-spacing: 0.08em;">Сумма (TMT)</label>
                        <input type="number" name="amount" step="0.01" class="form-control border-0 rounded-3 p-3" style="background: #f4f7f6;" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 fw-bold px-4" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn text-white fw-black rounded-3 px-4" style="background: #E8722A;">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection