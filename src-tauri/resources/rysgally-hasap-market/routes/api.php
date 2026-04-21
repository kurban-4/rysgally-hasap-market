<?php

use App\Models\Product; 
use App\Models\Till;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    
    return Product::all();
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