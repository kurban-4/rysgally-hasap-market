<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Storage;

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
        'category'       => 'nullable',
        'received_price' => 'nullable|numeric',
        'barcode'        => 'nullable|string',
        'product_code'   => 'nullable|string',
        'expiry_date'    => 'nullable|date',
        'produced_date'  => 'nullable|date',
    ]);

    // Выбираем количество в зависимости от типа
    $quantity = ($request->unit_type === 'weight') 
                ? $request->quantity_weight 
                : $request->quantity_units;

    $product = Product::create([
        'name'          => $request->name,
        'product_code'  => $request->product_code,
        'barcode'       => $request->barcode,
        'description'   => $request->description,
        'price'         => $request->price,
        'discount'      => $request->discount ?? 0,
        'category'      => $request->category ?? 'General',
        'manufacturer'  => $request->manufacturer ?? 'Unknown',
        'produced_date' => $request->produced_date,
        'expiry_date'   => $request->expiry_date, // Обязательно сохраняем дату!
        'received_price'=> $request->received_price,
        'unit_type'     => $request->unit_type,
        'unit_label'    => $request->unit_label ?? ($request->unit_type === 'weight' ? 'kg' : 'pcs'),
    ]);

    Storage::create([
        'product_id'     => $product->id,
        'quantity'       => $quantity ?? 0,
        'category'       => $request->category ?? 'General',
        'selling_price'  => $request->price,
        'received_price' => $request->received_price,
        'discount'       => $request->discount ?? 0,
        'expiry_date'    => $request->expiry_date, // Дублируем в сторадж для логики сроков
        'batch_number'   => $request->batch_number,
    ]);

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
