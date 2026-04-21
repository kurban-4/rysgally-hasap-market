<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Storage;
use App\Models\WholesaleMarketTransfer;
use App\Models\WholesaleStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WholesaleStorageController extends Controller
{
    public function index()
    {
        $inventory = WholesaleStorage::with('product')->latest()->paginate(20);

        $products = Product::with(['wholesaleStorage' => function ($q) {
                $q->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
            }])
            ->orderBy('name')
            ->get()
            ->filter(fn($m) => $m->wholesaleStorage->isNotEmpty())
            ->values();

        return view('wholesale_storage.index', compact('inventory', 'products'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('wholesale_storage.create', compact('products'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'product_id'    => 'nullable|exists:products,id',
        'product_name'  => 'required|string|max:255',
        'unit_type'     => 'required|in:piece,weight',
        'quantity'      => 'required|numeric|min:0.001',
        'received_price' => 'required|numeric|min:0',
        'selling_price'  => 'required|numeric|min:0',
        'batch_number'   => 'nullable|string',
        'expiry_date'    => 'required|date',
    ]);

    try {
        DB::transaction(function () use (&$validated) {

            if (empty($validated['product_id'])) {
                $existing = Product::where('name', $validated['product_name'])->first();
                if ($existing) {
                    $validated['product_id'] = $existing->id;
                    $product = $existing;
                } else {
                    $product = Product::create([
                        'name'                 => $validated['product_name'],
                        'category'             => 'General',
                        'unit_type'            => $validated['unit_type'],
                        'units_per_box'        => 1,
                        'total_quantity_units' => 0,
                    ]);
                    $validated['product_id'] = $product->id;
                }
            } else {
                $product = Product::findOrFail($validated['product_id']);
            }

            $product->update([
                'unit_type' => $validated['unit_type'],
            ]);

            WholesaleStorage::create([
                'product_id'    => $validated['product_id'],
                'product_name'  => $validated['product_name'],
                'quantity'      => $validated['quantity'],
                'received_price' => $validated['received_price'],
                'selling_price'  => $validated['selling_price'],
                'batch_number'   => $validated['batch_number'],
                'expiry_date'    => $validated['expiry_date'],
            ]);

            $product->increment('total_quantity_units', $validated['quantity']);
        });

        return redirect()->route('wholesale_storage.index')
            ->with('success', 'Batch added to inventory.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}

public function transferToMarket(Request $request)
{
    $transferQty = (float) ($request->input('transfer_qty') ?: $request->input('transfer_quantity') ?: 0);

    $request->merge([
        'transfer_qty'   => $transferQty,
        'received_price' => (float) $request->input('received_price', 0),
        'selling_price'  => (float) $request->input('selling_price', 0),
    ]);

    $validated = $request->validate([
        'product_id'           => 'required|exists:products,id',
        'product_name'         => 'nullable|string|max:255',
        'market_scan_code'     => 'nullable|string|max:255',
        'barcode'              => 'nullable|string|max:255',
        'wholesale_storage_id' => 'nullable|exists:wholesale_storages,id',
        'transfer_qty'         => 'nullable|numeric|min:0',
        'received_price'       => 'required|numeric|min:0',
        'selling_price'        => 'required|numeric|min:0',
        'batch_number'         => 'nullable|string',
        'expiry_date'          => 'nullable|date',
    ]);

    $product = Product::findOrFail($validated['product_id']);

    $manual = trim((string) ($validated['market_scan_code'] ?? $validated['barcode'] ?? ''));
    if ($product->unit_type === 'weight') {
        $scanCode = $manual !== '' ? $manual : trim((string) ($product->product_code ?? ''));
    } else {
        $scanCode = $manual !== '' ? $manual : trim((string) ($product->barcode ?? ''));
    }

    if ($scanCode === '') {
        return redirect()->back()->with(
            'error',
            $product->unit_type === 'weight'
                ? 'Set a product code on the product (or enter it here) before transferring weight goods.'
                : 'Set a barcode on the product (or enter it here) before transferring item goods.'
        );
    }

    if ($transferQty <= 0) {
        return redirect()->back()->with('error', 'Please specify a quantity to transfer.');
    }

    $scopedBatch = null;
    if (! empty($validated['wholesale_storage_id'])) {
        $scopedBatch = WholesaleStorage::where('id', $validated['wholesale_storage_id'])
            ->where('product_id', $validated['product_id'])
            ->where('quantity', '>', 0)
            ->first();

        if (! $scopedBatch) {
            return redirect()->back()->with('error', 'This wholesale batch is not available for transfer.');
        }

        if ($transferQty > (float) $scopedBatch->quantity) {
            return redirect()->back()->with('error',
                "Not enough stock in this batch. Requested: {$transferQty}, available: {$scopedBatch->quantity}."
            );
        }
    }

    $totalAvailable = WholesaleStorage::where('product_id', $validated['product_id'])->sum('quantity');

    if ($scopedBatch === null && $transferQty > $totalAvailable) {
        return redirect()->back()->with('error',
            "Not enough stock. Requested: {$transferQty}, available: {$totalAvailable}."
        );
    }

    if ($scopedBatch !== null) {
        $batches = collect([$scopedBatch]);
    } else {
        $batches = WholesaleStorage::where('product_id', $validated['product_id'])
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    if ($batches->isEmpty()) {
        return redirect()->back()->with('error', 'No wholesale batches found for this product.');
    }

    $amountToAdd = round($transferQty, 3);

    try {
        DB::transaction(function () use ($batches, $validated, $transferQty, $amountToAdd, $product, $scanCode) {

            $remaining = $transferQty;
            $sourceBatches = [];
            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }
                $take = min($remaining, (float) $batch->quantity);
                $batch->decrement('quantity', $take);
                $sourceBatches[] = [
                    'wholesale_storage_id' => $batch->id,
                    'quantity'             => $take,
                    'batch_number'         => $batch->batch_number,
                ];
                $remaining -= $take;
            }

            $existing = Storage::where('product_id', $validated['product_id'])
                ->where('barcode', $scanCode)
                ->first();

            $expiry = $validated['expiry_date'] ?? null;
            if ($expiry === null || $expiry === '') {
                $expiry = $batches->first()->expiry_date;
            }

            if ($existing) {
                $existing->increment('quantity', $amountToAdd);
                $existing->update([
                    'selling_price'  => $validated['selling_price'],
                    'received_price' => $validated['received_price'],
                ]);
                $storageRow = $existing->fresh();
            } else {
                $storageRow = Storage::create([
                    'product_id'     => $validated['product_id'],
                    'barcode'        => $scanCode,
                    'quantity'       => $amountToAdd,
                    'category'       => $product->category ?? 'General',
                    'expiry_date'    => $expiry,
                    'received_price' => $validated['received_price'],
                    'selling_price'  => $validated['selling_price'],
                    'batch_number'   => $validated['batch_number'] ?? null,
                ]);
            }

            if ($product->unit_type === 'weight') {
                $product->update(['product_code' => $scanCode]);
            } else {
                $product->update(['barcode' => $scanCode]);
            }

            $productName = $validated['product_name'] ?? $product->name;

            WholesaleMarketTransfer::create([
                'product_id'      => $product->id,
                'product_name'    => $productName,
                'quantity'        => $amountToAdd,
                'unit_type'       => $product->unit_type ?? 'piece',
                'market_barcode'  => $scanCode,
                'received_price'  => $validated['received_price'],
                'selling_price'   => $validated['selling_price'],
                'batch_number'    => $validated['batch_number'] ?? null,
                'expiry_date'     => $expiry,
                'storage_id'      => $storageRow->id,
                'source_batches'  => $sourceBatches,
                'user_id'         => auth()->id(),
            ]);
        });

        $unitLabel = $product->unit_type === 'weight' ? 'kg' : 'pcs';

        return redirect()->back()->with('success',
            "Transferred {$transferQty} {$unitLabel} to market storage."
        );
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Transfer error: ' . $e->getMessage());
    }
}

    /** Cached routes may still target transferToPharmacy; delegate to the real handler. */
    public function transferToPharmacy(Request $request)
    {
        return $this->transferToMarket($request);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT  →  wholesale_storage_YYYY-MM-DD.xlsx
    // Reuses WholesaleController::buildXlsx() — no duplication, no packages.
    // ─────────────────────────────────────────────────────────────────────────
    public function export(Request $request)
    {
        $query = WholesaleStorage::with('product');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', fn($mq) => $mq->where('name', 'LIKE', "%{$search}%"))
                  ->orWhere('product_name', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('expiry_date', [$request->from_date, $request->to_date]);
        }

        $batches = $query->latest()->get();

        $headers = [
            'Product',
            'Batch No',
            'Quantity',
            'Unit',
            'Received Price ($)',
            'Selling Price ($)',
            'Expiry Date',
            'Added On',
        ];

        $rows          = [];
        $totalQuantity = 0;

        foreach ($batches as $batch) {
            $unit = ($batch->product->unit_type ?? 'piece') === 'weight' ? 'kg' : 'pcs';
            $totalQuantity += (float) $batch->quantity;

            $rows[] = [
                $batch->product->name ?? $batch->product_name ?? '—',
                $batch->batch_number ?? '—',
                $batch->quantity,
                $unit,
                number_format($batch->received_price, 2, '.', ''),
                number_format($batch->selling_price, 2, '.', ''),
                $batch->expiry_date ?? '—',
                $batch->created_at->format('Y-m-d'),
            ];
        }

        $rows[] = [];
        $rows[] = ['TOTAL', '', $totalQuantity, '', '', '', '', '', ''];

        $fileName = 'wholesale_storage_' . date('Y-m-d') . '.xlsx';

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

    public function destroy($id)
    {
        $batch = WholesaleStorage::with('product')->findOrFail($id);

        DB::transaction(function () use ($batch) {
            if ($batch->product) {
                $batch->product->decrement('total_quantity_units', $batch->quantity);
            }
            $batch->delete();
        });

        return redirect()->back()->with('success', 'Wholesale batch removed.');
    }
}