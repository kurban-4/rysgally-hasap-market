<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class ThermalReceiptController extends Controller
{
    /**
     * Print thermal receipt for sale
     */
    public function printSalesReceipt($saleId)
    {
        $sale = Sale::with(['product'])->findOrFail($saleId);
        
        // Return thermal receipt view
        return view('receipts.thermal_sales', compact('sale'));
    }
    
    /**
     * Test thermal receipt printing
     */
    public function testPrint()
    {
        // Create test sale data
        $testSale = new \stdClass();
        $testSale->id = 123456;
        $testSale->created_at = now();
        $testSale->total_price = 45.50;
        $testSale->discount = 5.00;
        $testSale->till_id = 1;
        $testSale->items_json = json_encode([
            [
                'name' => 'Test Product 1',
                'quantity' => 2,
                'price' => 15.00,
                'sale_type' => 'piece'
            ],
            [
                'name' => 'Test Weight Product',
                'quantity' => 0.500,
                'price' => 60.00,
                'sale_type' => 'weight'
            ]
        ]);
        
        return view('receipts.thermal_sales', ['sale' => $testSale]);
    }
    
    /**
     * Print receipt via JavaScript (for XPrinter-P201A)
     */
    public function printReceiptJS($saleId)
    {
        $sale = Sale::with(['product'])->findOrFail($saleId);
        
        return response()->json([
            'success' => true,
            'sale' => $sale,
            'print_url' => route('receipt.thermal.print', $saleId)
        ]);
    }
}
