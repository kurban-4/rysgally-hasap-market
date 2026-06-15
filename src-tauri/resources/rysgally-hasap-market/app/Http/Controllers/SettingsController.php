<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ThermalPrinterService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
{
    $printerHost           = Setting::get('printer_host', '192.168.1.100');
    $printerPort           = Setting::get('printer_port', '9100');
    $printerConnectionType = Setting::get('printer_connection_type', 'ethernet');
    $printerUsbDevice      = Setting::get('printer_usb_device', '');
    return view('settings.index', compact('printerHost', 'printerPort', 'printerConnectionType', 'printerUsbDevice'));
}

public function save(Request $request)
{
    Setting::set('printer_host', $request->input('printer_host'));
    Setting::set('printer_port', $request->input('printer_port', '9100'));
    Setting::set('printer_connection_type', $request->input('printer_connection_type', 'ethernet'));
    Setting::set('printer_usb_device', $request->input('printer_usb_device', ''));
    return back()->with('success', 'Settings saved!');
}

    public function testPrint()
    {
        $printer = new ThermalPrinterService();
        $result = $printer->testPrint();
        if ($result) {
            return back()->with('success', 'Test page printed successfully!');
        }
        return back()->with('error', 'Printer not reachable. Check connection settings.');
    }

    public function testPrinterConnection(Request $request)
    {
        $printerService = new ThermalPrinterService();
        $result = $printerService->testConnection();
        
        if ($result['success']) {
            return response()->json($result, 200);
        }
        
        return response()->json($result, 400);
    }

    public function getPrinterSettings()
    {
        $printerService = new ThermalPrinterService();
        return response()->json($printerService->getPrinterInfo());
    }

    public function getAvailablePrinterUSBDevices()
    {
        $devices = ThermalPrinterService::getAvailableUSBDevices();
        return response()->json(['devices' => $devices]);
    }
}
