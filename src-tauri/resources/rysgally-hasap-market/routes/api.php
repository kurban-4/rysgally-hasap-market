<?php

use App\Models\Product; 
use App\Models\Till;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $page = max(1, (int) $request->query('page', 1));
    $perPage = 50; // Items per page to reduce memory usage
    
    $query = Product::select(['id', 'name', 'price', 'product_code', 'barcode'])
        ->where('price', '>', 0)
        ->orderBy('id');
    
    $total = $query->count();
    $products = $query->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();
    
    return response()->json([
        'data' => $products,
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => (int) ceil($total / $perPage),
        ]
    ]);
});

Route::post('/setup-device', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required'
    ]);

    $till = Till::firstOrCreate([
        'name' => 'Касса №' . $validated['name']
    ]);

    return response()->json([
        'id' => $till->id,
        'name' => $till->name
    ]);
});