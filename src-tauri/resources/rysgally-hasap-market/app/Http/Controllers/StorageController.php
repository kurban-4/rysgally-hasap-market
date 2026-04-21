<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Storage;
use App\Models\Product;

    use App\Http\Controllers\WholesaleController; // Make sure this is imported
class StorageController extends Controller
{
    public function index(Request $request)
    {
        $f_category = $request->category;
        $f_status   = $request->status;

        $lowStockCount = Storage::where('quantity', '<', 10)->count();

        $expirySoonCount = Storage::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30)->toDateString())
            ->where('expiry_date', '>=', now()->toDateString())
            ->count();

        $expirySoonCount += Product::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30)->toDateString())
            ->where('expiry_date', '>=', now()->toDateString())
            ->whereHas('storage', function($q) {
                $q->whereNull('expiry_date');
            })
            ->count();

        $query = Storage::with('product');

        if ($f_category) $query->where('category', $f_category);
        if ($f_status == 'low') $query->where('quantity', '<', 10);
        if ($f_status == 'expiry_soon') {
            $query->whereNotNull('expiry_date')
                  ->where('expiry_date', '<=', now()->addDays(30)->toDateString())
                  ->where('expiry_date', '>=', now()->toDateString());
        }
        if ($f_status == 'expired') {
            $query->whereNotNull('expiry_date')
                  ->where('expiry_date', '<', now()->toDateString());
        }

        $storage = $query->latest()->paginate(50)->withQueryString();

        $storage->getCollection()->transform(function ($item) {
            $unitType = $item->product->unit_type ?? 'piece';
           if ($unitType === 'weight') {
    $item->display_amount = (float) $item->quantity; // Automatically drops trailing zeros
    $item->display_unit = 'kg';
}else {
                $item->display_amount = (int)$item->quantity;
                $item->display_unit = 'pcs';
            }
            return $item;
        });

        $categories = Storage::distinct()->pluck('category');

        return view('storage.index', compact('storage', 'categories', 'lowStockCount', 'expirySoonCount'));
    }

    public function edit($id)
    {
        $storage = Storage::with('product')->findOrFail($id);
        $categories = Product::distinct()->pluck('category');

        $unitType = $storage->product->unit_type ?? 'piece';
        if ($unitType === 'weight') {
    $amount = (float) $storage->quantity; 
    $unit = 'kg';
} else {
            $amount = (int)$storage->quantity;
            $unit = 'pcs';
        }

        return view('storage.edit', compact('storage', 'categories', 'amount', 'unit'));
    }

    public function update(Request $request, $id)
    {
        $storageEntry = Storage::findOrFail($id);
        $product = $storageEntry->product;

        $request->validate([
            'amount'         => 'required|numeric|min:0',
            'barcode'        => 'nullable|string',
            'category'       => 'required',
            'price'          => 'nullable|numeric',
            'discount'       => 'nullable|integer|min:0|max:100',
            'received_price' => 'nullable|numeric',
            'expiry_date'    => 'nullable|date',
        ]);

        $amount = $request->input('amount');

        $productAttrs = [
            'price'          => $request->price,
            'discount'       => $request->discount,
            'received_price' => $request->received_price,
            'expiry_date'    => $request->expiry_date,
        ];
        if (($product->unit_type ?? 'piece') === 'weight') {
            $productAttrs['product_code'] = $request->barcode;
        } else {
            $productAttrs['barcode'] = $request->barcode;
        }
        $product->update($productAttrs);

        $storageEntry->update([
            'barcode'        => $request->barcode,
            'quantity'       => $amount,
            'category'       => $request->category,
            'selling_price'  => $request->price,
            'received_price' => $request->received_price,
            'discount'       => $request->discount,
            'expiry_date'    => $request->expiry_date,
        ]);

        return redirect()->route('storage.index')->with('success', 'Data updated successfully!');
    }

    public function destroy($id)
    {
        Storage::findOrFail($id)->delete();
        return redirect()->route('storage.index')->with('success', 'Deleted!');
    }

public function export(Request $request)
{
    // 1. Re-apply the filters from your index method
    $f_category = $request->category;
    $f_status   = $request->status;

    $query = Storage::with('product');

    if ($f_category) $query->where('category', $f_category);
    if ($f_status == 'low') $query->where('quantity', '<', 10);
    if ($f_status == 'expiry_soon') {
        $query->whereNotNull('expiry_date')
              ->where('expiry_date', '<=', now()->addDays(30)->toDateString())
              ->where('expiry_date', '>=', now()->toDateString());
    }
    if ($f_status == 'expired') {
        $query->whereNotNull('expiry_date')
              ->where('expiry_date', '<', now()->toDateString());
    }

    $storageItems = $query->latest()->get();

    // 2. Define headers (following your wholesale style)
    $headers = [
        'ID',
        'Товар',
        'Категория',
        'Количество',
        'Ед. изм.',
        'Статус',
        'Срок годности',
    ];

    $rows = [];
    foreach ($storageItems as $item) {
        // Handle units logic
        $unitType = $item->product->unit_type ?? 'piece';
       if ($unitType === 'weight') {
    $amount = (float) $item->quantity; 
    $unit = 'kg';
} else {
            $amount = (int)$item->quantity;
            $unit = 'pcs';
        }

        // Handle Expiry
        $rawDate = $item->expiry_date ?? $item->product->expiry_date ?? null;
        $expiryStr = $rawDate ? \Carbon\Carbon::parse($rawDate)->format('d.m.Y') : '—';

        $rows[] = [
            $item->product_id,
            $item->product->name ?? '—',
            $item->category ?? '—',
            $amount,
            $unit,
            $item->quantity < 10 ? 'Мало' : 'Достаточно',
            $expiryStr,
        ];
    }

    $fileName = 'retail_storage_' . date('Y-m-d') . '.xlsx';

    // 3. Use your existing buildXlsx helper
    return response()->streamDownload(
        fn() => print WholesaleController::buildXlsx($headers, $rows),
        $fileName,
        [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control'       => 'max-age=0',
        ]
    );
}
    
}
