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
    // ─── CART HELPERS ───────────────────────────────────────────
    private function getActiveCartId(): int
    {
        return session()->get('pos_active_cart', 1);
    }

    private function getActiveCart(): array
    {
        $cartId = $this->getActiveCartId();
        return session()->get("pos_carts.$cartId.items", []);
    }

    private function saveActiveCart(array $cart): void
    {
        $cartId = $this->getActiveCartId();
        session()->put("pos_carts.$cartId.items", $cart);
    }

    // ─── INDEX ──────────────────────────────────────────────────
    public function index()
    {
        $activeCartId = session()->get('pos_active_cart', 1);
        $carts = session()->get('pos_carts', [
            1 => ['items' => [], 'label' => 'Client 1']
        ]);

        if (!isset($carts[$activeCartId])) {
            $activeCartId = array_key_first($carts);
            session()->put('pos_active_cart', $activeCartId);
        }

        $cart      = $carts[$activeCartId]['items'] ?? [];
        $cartTotal = array_sum(array_column($cart, 'total_price'));

        $totalMoney = Sale::whereDate('created_at', today())->sum('total_price');
        $salesCount = Sale::whereDate('created_at', today())->sum('quantity');

        $activeShift = Shift::where('user_id', auth()->id())
                            ->where('status', 'active')
                            ->first();

        return view('sales.index', compact(
            'cart', 'cartTotal', 'totalMoney', 'salesCount',
            'activeShift', 'carts', 'activeCartId'
        ));
    }

    // ─── MULTI-CART ─────────────────────────────────────────────
    public function switchCart(Request $request)
    {
        $cartId = (int) $request->input('cart_id');
        $carts  = session()->get('pos_carts', []);

        if (isset($carts[$cartId])) {
            session()->put('pos_active_cart', $cartId);
        }

        return response()->json(['success' => true, 'cart_id' => $cartId]);
    }

    public function newCart(Request $request)
    {
        $carts = session()->get('pos_carts', [
            1 => ['items' => [], 'label' => 'Client 1']
        ]);

        $newId          = max(array_keys($carts)) + 1;
        $carts[$newId]  = ['items' => [], 'label' => 'Client ' . $newId];

        session()->put('pos_carts', $carts);
        session()->put('pos_active_cart', $newId);

        return response()->json(['success' => true, 'cart_id' => $newId]);
    }

    public function closeCart(Request $request)
    {
        $cartId = (int) $request->input('cart_id');
        $carts  = session()->get('pos_carts', []);

        if (count($carts) <= 1) {
            return response()->json(['success' => false, 'message' => 'Cannot close last cart']);
        }

        unset($carts[$cartId]);
        session()->put('pos_carts', $carts);

        $newActiveId = array_key_first($carts);
        session()->put('pos_active_cart', $newActiveId);

        return response()->json(['success' => true, 'cart_id' => $newActiveId]);
    }

    // ─── SHIFT ──────────────────────────────────────────────────
    public function startShift(Request $request)
    {
        $existing = Shift::where('user_id', auth()->id())
                         ->where('status', 'active')
                         ->first();

        if (!$existing) {
            $tillId = $request->input('till_id');
            $tillId = ($tillId && $tillId !== '0') ? (int) $tillId : null;

            Shift::create([
                'user_id'   => auth()->id(),
                'till_id'   => $tillId,
                'opened_at' => Carbon::now(),
                'status'    => 'active',
            ]);
        }

        return back()->with('success', 'Смена начата!');
    }

    // ─── ADD TO CART ────────────────────────────────────────────
    public function addToCart(Request $request)
    {
        $activeShift = Shift::where('user_id', auth()->id())->where('status', 'active')->first();
        if (!$activeShift) {
            return response()->json(['success' => false, 'message' => 'Сначала начните смену!'], 403);
        }

        $barcode   = $request->barcode;
        $manualQty = $request->has('quantity') ? (float) $request->quantity : null;
        $parsed    = parseWeightBarcode($barcode);

        if ($parsed['is_weight']) {
            $productCode = trim($parsed['product_code']);
            $product     = Product::where('product_code', $productCode)->first();
            $qtySold     = $manualQty ?? ($parsed['weight_grams'] / 1000);
            $storage     = $product ? Storage::where('product_id', $product->id)->first() : null;
        } else {
            $productBarcode = \App\Models\ProductBarcode::where('barcode', $barcode)->first();
            if ($productBarcode) {
                $product = $productBarcode->product;
                $storage = $product ? Storage::where('product_id', $product->id)->first() : null;
            } else {
                $storage = Storage::where('barcode', $barcode)->first();
                if ($storage) {
                    $product = $storage->product;
                } else {
                    $product = Product::where('barcode', $barcode)->first();
                    $storage = $product ? Storage::where('product_id', $product->id)->first() : null;
                }
            }
            $qtySold = $manualQty ?? 1;
        }

        if (!$product || !$storage) {
            return response()->json(['success' => false, 'message' => 'Product not found in storage!'], 404);
        }

        if ($storage->quantity <= 0) {
            return response()->json(['success' => false, 'message' => 'Product is out of stock!'], 400);
        }

        if ($storage->quantity < $qtySold) {
            return response()->json(['success' => false, 'message' => 'Insufficient stock! Available: ' . $storage->quantity], 400);
        }

        $cart       = $this->getActiveCart();
        $cartItemId = uniqid();
        $cart[$cartItemId] = $this->makeCartLine($product, $storage, $qtySold, $cartItemId);
        $this->saveActiveCart($cart);

        return response()->json(['success' => true, 'message' => 'Добавлено: ' . $product->name]);
    }

    // ─── REMOVE FROM CART ───────────────────────────────────────
    public function removeFromCart($id)
    {
        $cart = $this->getActiveCart();
        if (isset($cart[$id])) {
            unset($cart[$id]);
            $this->saveActiveCart($cart);
        }
        return redirect()->back();
    }

    // ─── UPDATE CART QTY ────────────────────────────────────────
    public function updateCart(Request $request, $id)
    {
        $quantity = (float) $request->input('quantity');

        if ($quantity <= 0) {
            return response()->json(['success' => false, 'message' => 'Quantity must be positive']);
        }

        $cart = $this->getActiveCart();

        if (!isset($cart[$id])) {
            return response()->json(['success' => false, 'message' => 'Item not found in cart']);
        }

        $item     = &$cart[$id];
        $saleType = $item['sale_type'];

        if ($saleType !== 'weight') {
            $quantity = round($quantity);
        }

        $storage = Storage::find($item['storage_id'] ?? null)
                   ?? Storage::where('product_id', $item['product_id'])->first();

        if (!$storage) {
            return response()->json(['success' => false, 'message' => 'Storage not found']);
        }

        if ($storage->quantity < $quantity) {
            return response()->json(['success' => false, 'message' => 'Insufficient stock. Available: ' . $storage->quantity]);
        }

        $item['quantity']       = $quantity;
        $item['total_price']    = round($quantity * (float) $item['price'], 2);
        $item['units_to_deduct'] = $quantity;

        $this->saveActiveCart($cart);

        return $this->cartJsonResponse($cart, 'Quantity updated');
    }

    // ─── UPDATE CART PRICE ──────────────────────────────────────
    public function updateCartPrice(Request $request, $id)
    {
        $price = (float) $request->input('price');

        if ($price < 0) {
            return response()->json(['success' => false, 'message' => 'Price cannot be negative']);
        }

        $cart = $this->getActiveCart();

        if (!isset($cart[$id])) {
            return response()->json(['success' => false, 'message' => 'Item not found in cart']);
        }

        $item        = &$cart[$id];
        $listPrice   = (float) ($item['list_price'] ?? $item['price']);
        $defaultUnit = (float) ($item['default_price'] ?? $item['price']);
        $roundedPrice = round($price, 2);

        $item['price']          = $roundedPrice;
        $item['price_overridden'] = abs($roundedPrice - $defaultUnit) >= 0.01;
        $item['total_price']    = round((float) $item['quantity'] * $roundedPrice, 2);

        if (!isset($item['list_price'])) {
            $item['list_price'] = $listPrice;
        }

        $this->saveActiveCart($cart);

        return $this->cartJsonResponse($cart, 'Price updated');
    }

    // ─── SCAN BARCODE ───────────────────────────────────────────
    public function scanBarcode(Request $request)
    {
        $request->validate(['barcode' => 'required|string', 'till_id' => 'nullable|integer']);

        $barcode = $request->input('barcode');
        $parsed  = parseWeightBarcode($barcode);

        if ($parsed['is_weight'] ?? false) {
            $productCode = $parsed['product_code'];
            $grams       = $parsed['weight_grams'];

            $product = Product::where('product_code', $productCode)
                        ->orWhere('barcode', $productCode)
                        ->first();

            if (!$product) {
                Log::warning('Weight barcode scanned but product not found', ['barcode' => $barcode, 'product_code' => $productCode]);
                return response()->json(['error' => 'Product not found for code ' . $productCode], 404);
            }

            if (($product->unit_type ?? 'piece') !== 'weight') {
                Log::warning('Weight barcode for non-weight product', ['product_id' => $product->id, 'barcode' => $barcode]);
                return response()->json(['error' => 'Product is not configured as weight type'], 422);
            }

            $quantity = $grams / 1000;
            $storage  = Storage::where('product_id', $product->id)->first();

            if (!$storage) return response()->json(['error' => 'Product not found in storage'], 404);
            if ($storage->quantity <= 0) return response()->json(['error' => 'Product is out of stock!'], 400);
            if ($storage->quantity < $quantity) return response()->json(['error' => 'Insufficient stock! Available: ' . $storage->quantity . ' kg'], 400);

            $unitPrice  = $storage->selling_price ?? $product->price ?? 0;
            $discount   = (int) ($storage->discount ?? $product->discount ?? 0);
            $finalPrice = $discount > 0 ? $unitPrice * (1 - $discount / 100) : $unitPrice;
            $rowTotal   = round($finalPrice * $quantity, 2);

            return response()->json([
                'product_id' => $product->id,
                'name'       => $product->name,
                'unit_type'  => 'weight',
                'quantity'   => $quantity,
                'price'      => $finalPrice,
                'row_total'  => $rowTotal,
            ]);
        }

        $productBarcode = \App\Models\ProductBarcode::where('barcode', $barcode)->first();
        if ($productBarcode) {
            $product = $productBarcode->product;
        } else {
            $storage = Storage::where('barcode', $barcode)->first();
            if ($storage) {
                $product = $storage->product;
            } else {
                $product = Product::where('barcode', $barcode)->first();
            }
        }

        if (!$product) {
            Log::warning('Barcode scanned but product not found', ['barcode' => $barcode]);
            return response()->json(['error' => 'Product not found'], 404);
        }

        $storage = Storage::where('product_id', $product->id)->first();

        if (!$storage) return response()->json(['error' => 'Product not found in storage'], 404);
        if ($storage->quantity <= 0) return response()->json(['error' => 'Product is out of stock!'], 400);
        if ($storage->quantity < 1) return response()->json(['error' => 'Insufficient stock! Available: ' . $storage->quantity], 400);

        $unitPrice  = $storage->selling_price ?? $product->price ?? 0;
        $discount   = (int) ($storage->discount ?? $product->discount ?? 0);
        $finalPrice = $discount > 0 ? $unitPrice * (1 - $discount / 100) : $unitPrice;

        return response()->json([
            'product_id' => $product->id,
            'name'       => $product->name,
            'unit_type'  => $product->unit_type ?? 'piece',
            'quantity'   => 1,
            'price'      => $finalPrice,
            'row_total'  => $finalPrice,
        ]);
    }

    // ─── CHECKOUT ───────────────────────────────────────────────
    public function checkout(Request $request)
    {
        $cart = $this->getActiveCart();
        if (empty($cart)) return redirect()->back()->with('error', 'Корзина пуста');

        $tillId = $request->input('till_id');
        if (empty($tillId) || $tillId === '0') {
            $defaultTill = \App\Models\Till::first();
            $tillId      = $defaultTill ? $defaultTill->id : null;
        } else {
            $tillId = (int) $tillId;
            if (!\App\Models\Till::where('id', $tillId)->exists()) {
                $defaultTill = \App\Models\Till::first();
                $tillId      = $defaultTill ? $defaultTill->id : null;
            }
        }

        DB::beginTransaction();
        try {
            foreach ($cart as $item) {
                $productExists = \App\Models\Product::where('id', $item['product_id'])->exists();
                $storageExists = isset($item['storage_id']) && \App\Models\Storage::where('id', $item['storage_id'])->exists();

                if (!$productExists || !$storageExists) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Ошибка: один из товаров отсутствует в базе.');
                }
            }

            $transactionId = '#ORD-' . date('YmdHis');
            $saleItems     = [];

            foreach ($cart as $item) {
                $saleItems[] = [
                    'product_id'       => $item['product_id'],
                    'name'             => $item['name'] ?? 'Unknown Product',
                    'quantity'         => $item['quantity'],
                    'list_price'       => (float) ($item['list_price'] ?? $item['price']),
                    'default_price'    => (float) ($item['default_price'] ?? $item['price']),
                    'price'            => $item['price'],
                    'total_price'      => $item['total_price'],
                    'sale_type'        => $item['sale_type'],
                    'discount'         => (int) ($item['discount'] ?? 0),
                    'price_overridden' => (bool) ($item['price_overridden'] ?? false),
                ];

                $storage = Storage::find($item['storage_id'] ?? null)
                           ?? Storage::where('product_id', $item['product_id'])->first();

                if ($storage) {
                    $storage->decrement('quantity', $item['units_to_deduct']);
                }
            }

            $firstItem     = reset($cart);
            $firstProductId = $firstItem['product_id'] ?? null;

            $sale = Sale::create([
                'transaction_id' => $transactionId,
                'product_id'     => $firstProductId,
                'quantity'       => array_sum(array_column($saleItems, 'quantity')),
                'price'          => $firstItem['price'] ?? 0,
                'total_price'    => array_sum(array_column($saleItems, 'total_price')),
                'items_json'     => json_encode($saleItems),
                'sale_type'      => $firstItem['sale_type'] ?? 'piece',
                'till_id'        => $tillId,
                'discount'       => array_sum(array_column($saleItems, 'discount')),
            ]);

            DB::commit();

            // Удали активную корзину после checkout
            $activeCartId = $this->getActiveCartId();
            $carts        = session()->get('pos_carts', []);
            unset($carts[$activeCartId]);

            if (empty($carts)) {
                $carts[1] = ['items' => [], 'label' => 'Client 1'];
                session()->put('pos_active_cart', 1);
            } else {
                session()->put('pos_active_cart', array_key_first($carts));
            }
            session()->put('pos_carts', $carts);

            // Печать
            $printerService = new \App\Services\ThermalPrinterService();
            $printerService->printReceipt($sale);

            return response()->json([
                'success'     => true,
                'message'     => 'Checkout completed!',
                'receipt_id'  => $sale->id,
                'receipt_url' => route('receipt.thermal.print', $sale->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    // ─── CLOSE SHIFT ────────────────────────────────────────────
    public function closeShift(Request $request)
    {
        $shift = Shift::where('user_id', auth()->id())
                      ->where('status', 'active')
                      ->first();

        if ($shift) {
            $revenue = Sale::where('created_at', '>=', $shift->opened_at)->sum('total_price');
            $shift->update([
                'closed_at'     => Carbon::now(),
                'total_revenue' => $revenue,
                'status'        => 'closed',
            ]);
        }

        return redirect()->route('sales.report')->with('success', 'Смена закрыта!');
    }

    // ─── REPORT ─────────────────────────────────────────────────
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

    // ─── DESTROY ────────────────────────────────────────────────
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

    // ─── THERMAL RECEIPT ────────────────────────────────────────
    public function printThermalReceipt($saleId)
    {
        try {
            $sale = Sale::findOrFail($saleId);
            return view('receipts.thermal_sales', compact('sale'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('sales.index')->with('error', 'Sale not found.');
        } catch (\Exception $e) {
            return redirect()->route('sales.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function testThermalPrint()
    {
        $testSale              = new \stdClass();
        $testSale->id          = 123456;
        $testSale->created_at  = now();
        $testSale->total_price = 45.50;
        $testSale->discount    = 5.00;
        $testSale->till_id     = 1;
        $testSale->items_json  = json_encode([
            ['name' => 'Test Product 1',    'quantity' => 2,     'price' => 15.00, 'sale_type' => 'piece'],
            ['name' => 'Test Weight Product','quantity' => 0.500, 'price' => 60.00, 'sale_type' => 'weight'],
        ]);

        return view('receipts.thermal_sales', ['sale' => $testSale]);
    }

    // ─── PRIVATE HELPERS ────────────────────────────────────────
    private function resolveUnitPricing(Storage $storage, Product $product): array
    {
        $listPrice   = (float) ($storage->selling_price ?? $product->price ?? 0);
        $discount    = (int) ($storage->discount ?? $product->discount ?? 0);
        $defaultPrice = $discount > 0
            ? round($listPrice * (1 - $discount / 100), 2)
            : round($listPrice, 2);

        return [
            'list_price'    => round($listPrice, 2),
            'default_price' => $defaultPrice,
            'price'         => $defaultPrice,
            'discount'      => $discount,
        ];
    }

    private function makeCartLine(Product $product, Storage $storage, float $quantity, string $cartId): array
    {
        $pricing   = $this->resolveUnitPricing($storage, $product);
        $unitPrice = $pricing['price'];

        return [
            'id'               => $cartId,
            'product_id'       => $product->id,
            'storage_id'       => $storage->id,
            'name'             => $product->name,
            'sale_type'        => $product->unit_type,
            'quantity'         => $quantity,
            'list_price'       => $pricing['list_price'],
            'default_price'    => $pricing['default_price'],
            'price'            => $unitPrice,
            'total_price'      => round($unitPrice * $quantity, 2),
            'units_to_deduct'  => $quantity,
            'discount'         => $pricing['discount'],
            'price_overridden' => false,
        ];
    }

    private function cartJsonResponse(array $cart, string $message = 'OK'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success'    => true,
            'message'    => $message,
            'cart_total' => round(array_sum(array_column($cart, 'total_price')), 2),
            'cart_count' => count($cart),
        ]);
    }
}