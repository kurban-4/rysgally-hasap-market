<?php
namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class ThermalPrinterService
{
    private string $host;
    private int $port;

    public function __construct()
{
    $this->host = \App\Models\Setting::get('printer_host', '192.168.1.100');
    $this->port = (int) \App\Models\Setting::get('printer_port', 9100);
}

    public function printReceipt($sale): bool
    {
        try {
            $items = json_decode($sale->items_json, true) ?? [];

            $connector = new NetworkPrintConnector($this->host, $this->port, 3);
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
        $connector = new NetworkPrintConnector($this->host, $this->port, 3);
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
}