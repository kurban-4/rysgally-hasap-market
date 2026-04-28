<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Storage;
use App\Models\ProductBarcode;
use App\Services\ScaleService;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(50);
        $storage = Storage::paginate(50);
        $categories = Product::distinct()->pluck('category');
        $lowStockCount = Storage::where('quantity', '<', 10)->count();

        return view('storage.index', compact('products', 'storage', 'categories', 'lowStockCount'));
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        $storage = Storage::where('product_id', $id)->latest()->first();

        if (!$product->price && $storage) {
            $product->price = $storage->selling_price;
        }
        if (!$product->received_price && $storage) {
            $product->received_price = $storage->received_price;
        }

        $totalUnits = $storage ? $storage->quantity : 0;
        if (($product->unit_type ?? 'piece') === 'weight') {
    $displayAmount = (float) $totalUnits; // Automatically drops trailing zeros
    $displayUnit = 'kg';
}else {
            $displayAmount = (int)$totalUnits;
            $displayUnit = 'pcs';
        }

        return view("product.show", compact('product', 'storage', 'displayAmount', 'displayUnit'));
    }

    public function create()
    {
        $categories = Product::distinct()->pluck('category');
        return view('product.create', compact('categories'));
    }

  public function store(Request $request)
{
    $request->validate([
        'name'           => 'required|string|max:255',
        'price'          => 'required|numeric',
        'discount'        => 'nullable|integer|min:0|max:100',
        'unit_type'      => 'required|in:piece,weight',
        'category'       => 'nullable|string|max:255',
        'manufacturer'   => 'nullable|string|max:255',
        'description'    => 'nullable|string|max:1000',
        'received_price' => 'nullable|numeric',
        'barcodes'       => 'nullable|array',
        'barcodes.*'     => 'nullable|string|max:255',
        'product_code'   => 'nullable|string|max:255',
        'expiry_date'    => 'nullable|date',
        'produced_date'  => 'nullable|date',
        'batch_number'   => 'nullable|string|max:255',
    ]);

    // Выбираем количество в зависимости от типа
    $quantity = ($request->unit_type === 'weight') 
                ? $request->quantity_weight 
                : $request->quantity_units;

    // Calculate profit margin
    $profitMargin = 0;
    if ($request->price > 0 && $request->received_price > 0) {
        $profitMargin = (($request->price - $request->received_price) / $request->received_price) * 100;
    }

    $product = Product::create([
        'name'          => $request->name,
        'product_code'  => $request->product_code,
        'description'   => $request->description,
        'price'         => $request->price,
        'discount'      => $request->discount ?? 0,
        'category'      => $request->category,
        'manufacturer'  => $request->manufacturer,
        'produced_date' => $request->produced_date,
        'expiry_date'   => $request->expiry_date,
        'received_price'=> $request->received_price,
        'profit_margin' => $profitMargin,
        'unit_type'     => $request->unit_type,
        'unit_label'    => $request->unit_label ?? ($request->unit_type === 'weight' ? 'kg' : 'pcs'),
    ]);

    // Save multiple barcodes for piece products only
    if ($request->unit_type === 'piece' && $request->has('barcodes')) {
        $barcodes = array_filter($request->barcodes, function($barcode) {
            return !empty(trim($barcode));
        });
        
        // Check for duplicate barcodes
        $duplicateBarcodes = [];
        foreach ($barcodes as $barcode) {
            $trimmedBarcode = trim($barcode);
            $existingBarcode = ProductBarcode::where('barcode', $trimmedBarcode)->first();
            
            if ($existingBarcode) {
                $duplicateBarcodes[] = $trimmedBarcode;
            }
        }
        
        if (!empty($duplicateBarcodes)) {
            // Delete the created product since barcodes are duplicates
            $product->delete();
            return redirect()->back()
                ->withInput()
                ->withErrors(['barcodes' => 'The following barcodes already exist: ' . implode(', ', $duplicateBarcodes)]);
        }
        
        foreach ($barcodes as $barcode) {
            ProductBarcode::create([
                'product_id' => $product->id,
                'barcode' => trim($barcode),
            ]);
        }
    }

    Storage::create([
        'product_id'     => $product->id,
        'quantity'       => $quantity ?? 0,
        'category'       => $request->category,
        'selling_price'  => $request->price,
        'received_price' => $request->received_price,
        'discount'       => $request->discount ?? 0,
        'expiry_date'    => $request->expiry_date,
        'batch_number'   => $request->batch_number,
    ]);

    // Auto-export weighable products to scale
    if (config('scale.auto_export_on_create') && $product->isWeight()) {
        try {
            $scaleService = new ScaleService();
            $scaleService->exportProduct($product);
        } catch (\Exception $e) {
            // Log error but don't fail the product creation
            \Log::error("Failed to export product to scale: " . $e->getMessage());
        }
    }

    return redirect()->route('storage.index')->with('success', 'Product added successfully!');
}

    public function search(Request $request)
    {
        $query = $request->get('search');
        if (empty($query)) return response()->json([]);
        $products = Product::where('name', 'LIKE', "%{$query}%")->limit(10)->get();
        return response()->json($products);
    }
}
