<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $printerHost = Setting::get('printer_host', '192.168.1.100');
        $printerPort = Setting::get('printer_port', '9100');
        return view('settings.index', compact('printerHost', 'printerPort'));
    }

    public function save(Request $request)
    {
        Setting::set('printer_host', $request->input('printer_host'));
        Setting::set('printer_port', $request->input('printer_port', '9100'));
        return back()->with('success', 'Settings saved!');
    }

    public function testPrint()
    {
        $printer = new \App\Services\ThermalPrinterService();
        $result = $printer->testPrint();
        if ($result) {
            return back()->with('success', 'Test page printed!');
        }
        return back()->with('error', 'Printer not reachable. Check IP.');
    }
}