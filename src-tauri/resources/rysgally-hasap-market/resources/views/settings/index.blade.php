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
                    <label class="form-label">Connection type</label>
                    <select name="printer_connection_type" class="form-select" id="connectionType" onchange="toggleFields()">
                        <option value="ethernet" {{ ($printerConnectionType ?? 'ethernet') == 'ethernet' ? 'selected' : '' }}>Ethernet (Network)</option>
                        <option value="usb" {{ ($printerConnectionType ?? '') == 'usb' ? 'selected' : '' }}>USB</option>
                    </select>
                </div>

                <div id="ethernetFields">
                    <div class="mb-3">
                        <label class="form-label">Printer IP address</label>
                        <input type="text" name="printer_host" class="form-control" value="{{ $printerHost ?? '192.168.1.100' }}" placeholder="192.168.1.100">
                        <small class="text-muted">Hold FEED button 3-5 sec to print IP</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Port</label>
                        <input type="text" name="printer_port" class="form-control" value="{{ $printerPort ?? '9100' }}" style="width: 120px">
                    </div>
                </div>

                <div id="usbFields" style="display:none">
                    <div class="mb-3">
                        <label class="form-label">USB Device</label>
                        <input type="text" name="printer_usb_device" class="form-control" value="{{ $printerUsbDevice ?? '' }}" placeholder="/dev/tty.Printer001-171F or COM3">
                        <small class="text-muted">On Mac: /dev/tty.Printer001-171F | On Windows: COM3</small>
                    </div>
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

<script>
function toggleFields() {
    const type = document.getElementById('connectionType').value;
    document.getElementById('ethernetFields').style.display = type === 'ethernet' ? 'block' : 'none';
    document.getElementById('usbFields').style.display = type === 'usb' ? 'block' : 'none';
}
toggleFields();
</script>
@endsection