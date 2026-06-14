@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Settings</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mt-3">
        <div class="card-header">Thermal Printer</div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.save') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Printer IP address</label>
                    <input type="text" name="printer_host" class="form-control" value="{{ $printerHost }}" placeholder="192.168.1.100">
                    <small class="text-muted">Hold FEED button 3-5 sec to print IP</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Port</label>
                    <input type="text" name="printer_port" class="form-control" value="{{ $printerPort }}" style="width: 120px">
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>

            <form method="POST" action="{{ route('settings.test-print') }}" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-secondary">Test print</button>
            </form>
        </div>
    </div>
</div>
@endsection