<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Shift;
use App\Models\Product;
use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    public function index()
    {
        $cart      = session()->get('pos_cart', []);
        $cartTotal = array_sum(array_column($cart, 'total_price'));

        $totalMoney = Sale::whereDate('created_at', today())->sum('total_price');
        $salesCount = Sale::whereDate('created_at', today())->sum('quantity');

        $activeShift = Shift::where('user_id', auth()->id())
                            ->where('status', 'active')
                            ->first();

        return view('sales.index', compact('cart', 'cartTotal', 'totalMoney', 'salesCount', 'activeShift'));
    }

    public function startShift()
    {
        $existing = Shift::where('user_id', auth()->id())
                         ->where('status', 'active')
                         ->first();

        if (!$existing) {
            Shift::create([
                'user_id'   => auth()->id(),
                'opened_at' => Carbon::now(),
                'status'    => 'active',
            ]);
        }

        return back()->with('success', 'Смена начата!');
    }

public function addToCart(Request $request)
{
    $activeShift = Shift::where('user_id', auth()->id())->where('status', 'active')->first();
    if (!$activeShift) {
        return response()->json(['success' => false, 'message' => 'Сначала начните смену!'], 403);
    }

    $barcode = $request->barcode;
    // Берем количество из запроса, если оно есть (для продажи 100кг вручную)
    $manualQty = $request->has('quantity') ? (float)$request->quantity : null;
    
    $parsed = parseWeightBarcode($barcode);

    if ($parsed['is_weight']) {
        $productCode = trim($parsed['product_code']);
        $product = Product::where('product_code', $productCode)->first();
        
        $qtySold = $manualQty ?? ($parsed['weight_grams'] / 1000);
        $storage = $product ? Storage::where('product_id', $product->id)->first() : null;
    } else {
        // Обычный товар (Snickers и т.д.)
        $storage = Storage::where('barcode', $barcode)->first();
        if ($storage) {
            $product = $storage->product;
        } else {
            $product = Product::where('barcode', $barcode)->first();
            $storage = $product ? Storage::where('product_id', $product->id)->first() : null;
        }
        $qtySold = $manualQty ?? 1; 
    }

    if (!$product || !$storage) {
        return response()->json(['success' => false, 'message' => 'Product not found in storage!'], 404);
    }

    // Check if product is out of stock
    if ($storage->quantity <= 0) {
        return response()->json(['success' => false, 'message' => 'Product is out of stock!'], 400);
    }

    // Check if there's enough quantity for the sale
    if ($storage->quantity < $qtySold) {
        return response()->json(['success' => false, 'message' => 'Insufficient stock! Available: ' . $storage->quantity], 400);
    }

    $cart = session()->get('pos_cart', []);
    $cartId = uniqid();
    
    // Use storage selling_price for transferred products, fallback to product price
    $unitPrice = $storage->selling_price ?? $product->getFinalPriceAttribute();
    $discount = (int) ($storage->discount ?? $product->discount ?? 0);
    $finalPrice = $discount > 0 ? $unitPrice * (1 - $discount / 100) : $unitPrice;
    
    $cart[$cartId] = [
        'id'              => $cartId,
        'product_id'      => $product->id,
        'storage_id'      => $storage->id,
        'name'            => $product->name,
        'sale_type'       => $product->unit_type,
        'quantity'        => $qtySold,
        'price'           => $finalPrice,
        'total_price'     => round($finalPrice * $qtySold, 2),
        'units_to_deduct' => $qtySold,
        'discount'        => $discount,
    ];

    session()->put('pos_cart', $cart);

    return response()->json(['success' => true, 'message' => 'Добавлено: ' . $product->name]);
}
    public function removeFromCart($id)
    {
        $cart = session()->get('pos_cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('pos_cart', $cart);
        }
        return redirect()->back();
    }

    public function updateCart(Request $request, $id)
    {
        $quantity = (float) $request->input('quantity');
        
        if ($quantity <= 0) {
            return response()->json(['success' => false, 'message' => 'Quantity must be positive']);
        }

        $cart = session()->get('pos_cart', []);
        
        if (!isset($cart[$id])) {
            return response()->json(['success' => false, 'message' => 'Item not found in cart']);
        }

        $item = &$cart[$id];
        $saleType = $item['sale_type'];
        
        // For non-weight items, ensure quantity is integer
        if ($saleType !== 'weight') {
            $quantity = round($quantity);
        }

        // Check storage availability
        $storage = Storage::find($item['storage_id'] ?? null) 
                   ?? Storage::where('product_id', $item['product_id'])->first();
                   
        if (!$storage) {
            return response()->json(['success' => false, 'message' => 'Storage not found']);
        }

        if ($storage->quantity < $quantity) {
            return response()->json(['success' => false, 'message' => 'Insufficient stock. Available: ' . $storage->quantity]);
        }

        // Update item
        $item['quantity'] = $quantity;
        $item['total_price'] = $quantity * $item['price'];
        $item['units_to_deduct'] = $quantity;

        session()->put('pos_cart', $cart);

        return response()->json(['success' => true, 'message' => 'Quantity updated']);
    }

    public function scanBarcode(Request $request)
    {
        $request->validate(['barcode' => 'required|string', 'till_id' => 'nullable|integer']);

        $barcode = $request->input('barcode');
        $parsed = parseWeightBarcode($barcode);

        if ($parsed['is_weight'] ?? false) {
            $productCode = $parsed['product_code'];
            $grams = $parsed['weight_grams'];

            $product = Product::where('product_code', $productCode)
                        ->orWhere('barcode', $productCode)
                        ->first();

            if (! $product) {
                Log::warning('Weight barcode scanned but product not found', ['barcode' => $barcode, 'product_code' => $productCode]);
                return response()->json(['error' => 'Product not found for code '.$productCode], 404);
            }

            if (($product->unit_type ?? 'piece') !== 'weight') {
                Log::warning('Weight barcode for non-weight product', ['product_id' => $product->id, 'barcode' => $barcode]);
                return response()->json(['error' => 'Product is not configured as weight type'], 422);
            }

            $quantity = $grams / 1000;
            
            // Get storage entry to use correct pricing
            $storage = Storage::where('product_id', $product->id)->first();
            
            // Check if product exists in storage
            if (!$storage) {
                return response()->json(['error' => 'Product not found in storage'], 404);
            }
            
            // Check if product is out of stock
            if ($storage->quantity <= 0) {
                return response()->json(['error' => 'Product is out of stock!'], 400);
            }
            
            // Check if there's enough quantity for the sale
            if ($storage->quantity < $quantity) {
                return response()->json(['error' => 'Insufficient stock! Available: ' . $storage->quantity . ' kg'], 400);
            }
            
            $unitPrice = $storage->selling_price ?? $product->price ?? 0;
            $discount = (int) ($storage->discount ?? $product->discount ?? 0);
            $finalPrice = $discount > 0 ? $unitPrice * (1 - $discount / 100) : $unitPrice;
            $rowTotal = round($finalPrice * $quantity, 2);

            return response()->json([
                'product_id' => $product->id,
                'name' => $product->name,
                'unit_type' => 'weight',
                'quantity' => $quantity,
                'price' => $finalPrice,
                'row_total' => $rowTotal,
            ]);
        }

        $product = Product::where('barcode', $barcode)->first();
        if (! $product) {
            Log::warning('Barcode scanned but product not found', ['barcode' => $barcode]);
            return response()->json(['error' => 'Product not found'], 404);
        }
        
        // Get storage entry to use correct pricing
        $storage = Storage::where('product_id', $product->id)->first();
        
        // Check if product exists in storage
        if (!$storage) {
            return response()->json(['error' => 'Product not found in storage'], 404);
        }
        
        // Check if product is out of stock
        if ($storage->quantity <= 0) {
            return response()->json(['error' => 'Product is out of stock!'], 400);
        }
        
        // Check if there's enough quantity for the sale
        if ($storage->quantity < 1) {
            return response()->json(['error' => 'Insufficient stock! Available: ' . $storage->quantity], 400);
        }
        
        $unitPrice = $storage->selling_price ?? $product->price ?? 0;
        $discount = (int) ($storage->discount ?? $product->discount ?? 0);
        $finalPrice = $discount > 0 ? $unitPrice * (1 - $discount / 100) : $unitPrice;

        return response()->json([
            'product_id' => $product->id,
            'name' => $product->name,
            'unit_type' => $product->unit_type ?? 'piece',
            'quantity' => 1,
            'price' => $finalPrice,
            'row_total' => $finalPrice,
        ]);
    }

   public function checkout(Request $request)
{
    $cart = session()->get('pos_cart', []);
    if (empty($cart)) return redirect()->back()->with('error', 'Корзина пуста');

    $tillId = $request->input('till_id');
    if (empty($tillId) || $tillId === '0') {
        // Default to the first available till if none provided
        $defaultTill = \App\Models\Till::first();
        $tillId = $defaultTill ? $defaultTill->id : null;
        if ($tillId) {
            \Log::info('Checkout: No till_id provided, defaulting to till ' . $tillId);
        }
    } else {
        $tillId = (int) $tillId;
        if (! \App\Models\Till::where('id', $tillId)->exists()) {
            \Log::warning('Checkout: provided till_id not found, defaulting to first available', ['provided' => $request->input('till_id')]);
            $defaultTill = \App\Models\Till::first();
            $tillId = $defaultTill ? $defaultTill->id : null;
        }
    }

    DB::beginTransaction();
    try {
        // ВАЛИДАЦИЯ: убедимся, что все продукты и склады существуют
        foreach ($cart as $item) {
            $productExists = \App\Models\Product::where('id', $item['product_id'])->exists();
            $storageExists = isset($item['storage_id']) && \App\Models\Storage::where('id', $item['storage_id'])->exists();

            if (! $productExists || ! $storageExists) {
                DB::rollBack();
                \Log::warning('Checkout aborted: missing product or storage', ['item' => $item]);
                return redirect()->back()->with('error', 'Ошибка: один из товаров отсутствует в базе. Проверьте корзину.');
            }
        }

        $transactionId = '#ORD-' . date('YmdHis');

        // Debug: Log cart data
        \Log::info('Cart data: ', $cart);
        
        // Check if cart is empty
        if (empty($cart)) {
            return redirect()->back()->with('error', 'Корзина пуста');
        }

        // Create single sale record with all items in JSON
        $saleItems = [];
        foreach ($cart as $item) {
            $saleItems[] = [
                'product_id'     => $item['product_id'],
                'name'           => $item['name'] ?? 'Unknown Product',
                'quantity'       => $item['quantity'],
                'price'          => $item['price'],
                'total_price'    => $item['total_price'],
                'sale_type'      => $item['sale_type'],
                'discount'       => (int) ($item['discount'] ?? 0),
            ];

            $storage = Storage::find($item['storage_id'] ?? null)
                       ?? Storage::where('product_id', $item['product_id'])->first();

            if ($storage) {
                $storage->decrement('quantity', $item['units_to_deduct']);
            }
        }
        
        \Log::info('Sale items to save: ', $saleItems);

        // Create single sale record with all items in JSON
        $firstItem = reset($cart);
        $firstProductId = $firstItem['product_id'] ?? null;
        
        $sale = Sale::create([
            'transaction_id' => $transactionId,
            'product_id'     => $firstProductId, // first product_id
            'quantity'       => array_sum(array_column($saleItems, 'quantity')),
            'price'          => $firstItem['price'] ?? 0, // first price
            'total_price'    => array_sum(array_column($saleItems, 'total_price')),
            'items_json'     => json_encode($saleItems),
            'sale_type'      => $firstItem['sale_type'] ?? 'piece',
            'till_id'        => $tillId,
            'discount'       => array_sum(array_column($saleItems, 'discount')),
        ]);

        DB::commit();
        session()->forget('pos_cart');
        
        // Return JSON for auto printing
        return response()->json([
            'success' => true,
            'message' => 'Checkout completed!',
            'receipt_id' => $sale->id,
            'receipt_url' => route('receipt.thermal.print', $sale->id)
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Checkout failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return redirect()->back()->with('error', 'Ошибка: ' . $e->getMessage());
    }
}


    public function closeShift(Request $request)
    {
        $shift = Shift::where('user_id', auth()->id())
                      ->where('status', 'active')
                      ->first();

        if ($shift) {
            $revenue = Sale::where('created_at', '>=', $shift->opened_at)
                           ->sum('total_price');

            $shift->update([
                'closed_at'     => Carbon::now(),
                'total_revenue' => $revenue,
                'status'        => 'closed',
            ]);
        }

        return redirect()->route('sales.report')->with('success', 'Смена закрыта!');
    }

    public function showReport(Request $request)
    {
        $sales = Sale::with('product')->whereDate('created_at', today())->get();

        $monthlySalesTotal = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_price');

        $report = [
            'total_money'   => $sales->sum('total_price'),
            'monthly_total' => $monthlySalesTotal,
            'total_items'   => $sales->sum('quantity'),
            'start_time'    => $sales->min('created_at'),
            'end_time'      => now(),
            'products'      => $sales->groupBy('product_id'),
        ];

        return view('sales.report', compact('report'));
    }

    public function destroy($id)
    {
        $sale    = Sale::findOrFail($id);
        $storage = Storage::where('product_id', $sale->product_id)->first();

        if ($storage) {
            $storage->increment('quantity', $sale->quantity);
        }

        $sale->delete();
        return redirect()->back()->with('success', 'Sale removed and inventory restored.');
    }

    /**
     * Print thermal receipt for sale
     */
    public function printThermalReceipt($saleId)
    {
        try {
            \Log::info('Attempting to print thermal receipt for sale ID: ' . $saleId);
            
            $sale = Sale::findOrFail($saleId);
            
            \Log::info('Sale found: ' . $sale->id);
            \Log::info('Items JSON: ' . $sale->items_json);
            
            // Return thermal receipt view
            return view('receipts.thermal_sales', compact('sale'));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Sale not found for thermal receipt: ' . $saleId);
            return redirect()->route('sales.index')->with('error', 'Sale not found for receipt printing.');
        } catch (\Exception $e) {
            \Log::error('Error printing thermal receipt: ' . $e->getMessage());
            return redirect()->route('sales.index')->with('error', 'Error printing receipt: ' . $e->getMessage());
        }
    }
    
    /**
     * Test thermal receipt printing
     */
    public function testThermalPrint()
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
}