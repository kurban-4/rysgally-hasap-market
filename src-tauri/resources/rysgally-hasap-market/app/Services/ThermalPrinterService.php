<?php
namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use App\Models\Setting;

class ThermalPrinterService
{
    private string $connectionType;
    private string $host;
    private int $port;
    private string $usbDevice;
    private int $baudRate;

    public function __construct()
    {
        $this->connectionType = Setting::get('printer_connection_type', 'ethernet');
        $this->host = Setting::get('printer_host', '192.168.1.100');
        $this->port = (int) Setting::get('printer_port', 9100);
        $this->usbDevice = Setting::get('printer_usb_device', '/dev/ttyUSB0');
        $this->baudRate = (int) Setting::get('printer_baud_rate', 115200);
    }

    private function getConnector()
    {
        if ($this->connectionType === 'usb') {
            return $this->getUSBConnector();
        }
        return $this->getEthernetConnector();
    }

    private function getEthernetConnector()
    {
        try {
            return new NetworkPrintConnector($this->host, $this->port, 3);
        } catch (\Exception $e) {
            throw new \Exception("Ethernet printer connection failed: Cannot reach {$this->host}:{$this->port}. Check your printer's IP address and port.");
        }
    }

    private function getUSBConnector()
    {
        // For USB, we'll use FilePrintConnector which works with serial devices
        if (!file_exists($this->usbDevice)) {
            throw new \Exception("USB printer device not found: {$this->usbDevice}. Check the USB device settings.");
        }

        try {
            // On Windows, use the COM port directly
            if (PHP_OS_FAMILY === 'Windows') {
                $connector = new FilePrintConnector("\\\\.\\{$this->usbDevice}");
            } else {
                // On Linux/macOS, use the device path
                $connector = new FilePrintConnector($this->usbDevice);
            }
            return $connector;
        } catch (\Exception $e) {
            throw new \Exception("Cannot open USB printer device: {$this->usbDevice}. Error: " . $e->getMessage());
        }
    }

    public function printReceipt($sale): bool
    {
        try {
            $items = json_decode($sale->items_json, true) ?? [];
            $connector = $this->getConnector();
            $printer = new Printer($connector);

            // Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text("RysgallyMarket\n");
            $printer->setTextSize(1, 1);
            $printer->text("Ashgabat, Turkmenistan\n");
            $printer->feed(1);

            // Meta
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Receipt #: " . str_pad($sale->id, 6, '0', STR_PAD_LEFT) . "\n");
            $printer->text("Date    : " . $sale->created_at->format('d.m.Y H:i') . "\n");
            $printer->text("Till    : #" . $sale->till_id . "\n");
            $printer->text(str_repeat('-', 32) . "\n");

            // Items
            foreach ($items as $item) {
                $isWeight = ($item['sale_type'] ?? 'piece') === 'weight';
                $qty = $isWeight
                    ? number_format($item['quantity'], 3) . ' kg'
                    : number_format($item['quantity'], 0) . ' pcs';

                $itemTotal = number_format($item['quantity'] * $item['price'], 2);

                $printer->text($item['name'] . "\n");
                $printer->text("  " . $qty . " x " . number_format($item['price'], 2) . " = " . $itemTotal . " TMT\n");
            }

            $printer->text(str_repeat('-', 32) . "\n");

            // Total
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setTextSize(1, 2);
            $printer->text("TOTAL: " . number_format($sale->total_price, 2) . " TMT\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            $printer->text("Payment: CASH\n");
            $printer->feed(1);

            // Footer
            $printer->setEmphasis(true);
            $printer->text("THANK YOU!\n");
            $printer->setEmphasis(false);
            $printer->text("Please come again\n");
            $printer->feed(2);

            // Cut
            $printer->cut();
            $printer->close();

            return true;

        } catch (\Exception $e) {
            \Log::error('Thermal print failed: ' . $e->getMessage());
            return false;
        }
    }

    public function testPrint(): bool
    {
        try {
            $connector = $this->getConnector();
            $printer = new Printer($connector);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("=== TEST PRINT ===\n");
            $printer->text("RysgallyMarket\n");
            $printer->text("Printer connected!\n");
            $printer->feed(2);
            $printer->cut();
            $printer->close();
            return true;
        } catch (\Exception $e) {
            \Log::error('Test print failed: ' . $e->getMessage());
            return false;
        }
    }

    public function testConnection(): array
    {
        try {
            if ($this->connectionType === 'usb') {
                return $this->testUSBConnection();
            }
            return $this->testEthernetConnection();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function testEthernetConnection(): array
    {
        try {
            $fp = @fsockopen($this->host, $this->port, $errno, $errstr, 3);
            
            if ($fp) {
                fclose($fp);
                return [
                    'success' => true,
                    'message' => "Printer connected successfully at {$this->host}:{$this->port}"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Cannot connect to printer at {$this->host}:{$this->port}. Error: $errstr ($errno)"
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Connection test failed: " . $e->getMessage()
            ];
        }
    }

    private function testUSBConnection(): array
    {
        try {
            if (!file_exists($this->usbDevice)) {
                return [
                    'success' => false,
                    'message' => "USB device not found: {$this->usbDevice}"
                ];
            }

            // Try to open the device
            $handle = @fopen($this->usbDevice, 'r+b');
            if (!$handle) {
                return [
                    'success' => false,
                    'message' => "Cannot open USB device: {$this->usbDevice}. Make sure the printer is connected and the device has proper permissions."
                ];
            }

            fclose($handle);
            return [
                'success' => true,
                'message' => "USB printer connected successfully at {$this->usbDevice}"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "USB connection test failed: " . $e->getMessage()
            ];
        }
    }

    public static function getAvailableUSBDevices(): array
    {
        $devices = [];

        if (PHP_OS_FAMILY === 'Linux') {
            // On Linux, check /dev/ttyUSB* and /dev/ttyACM*
            $usbDevices = glob('/dev/ttyUSB*');
            $acmDevices = glob('/dev/ttyACM*');
            $devices = array_merge($usbDevices ?? [], $acmDevices ?? []);
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            // On macOS, check /dev/tty.usbserial* and /dev/cu.usbserial*
            $devices = glob('/dev/tty.usbserial*') ?? [];
            $cuDevices = glob('/dev/cu.usbserial*') ?? [];
            $devices = array_merge($devices, $cuDevices);
        } elseif (PHP_OS_FAMILY === 'Windows') {
            // On Windows, return common COM ports
            $devices = [];
            for ($i = 1; $i <= 9; $i++) {
                $port = "COM{$i}";
                // Check if device exists
                $handle = @fopen("\\\\.\\{$port}", 'r+b');
                if ($handle) {
                    fclose($handle);
                    $devices[] = $port;
                }
            }
        }

        return $devices;
    }

    public function getPrinterInfo(): array
    {
        return [
            'connection_type' => $this->connectionType,
            'host' => $this->connectionType === 'ethernet' ? $this->host : null,
            'port' => $this->connectionType === 'ethernet' ? $this->port : null,
            'usb_device' => $this->connectionType === 'usb' ? $this->usbDevice : null,
            'baud_rate' => $this->connectionType === 'usb' ? $this->baudRate : null,
        ];
    }
}
