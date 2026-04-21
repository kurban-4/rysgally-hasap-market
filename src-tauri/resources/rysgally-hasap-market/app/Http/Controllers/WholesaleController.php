<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WholesaleInvoice;
use App\Models\WholesaleItem;
use App\Models\WholesaleMarketTransfer;
use App\Models\WholesaleStorage;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class WholesaleController extends Controller
{
    public function index(Request $request)
    {
        $query = WholesaleInvoice::with(['items.product']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%{$search}%")
                    ->orWhere('invoice_no', 'LIKE', "%{$search}%")
                    ->orWhereHas('items.product', function ($mq) use ($search) {
                        $mq->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59',
            ]);
        } elseif ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            $query->whereDate('created_at', today());
        }

        $invoices = $query->latest()->get();

        $transferQuery = WholesaleMarketTransfer::query()->with('user');
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $transferQuery->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59',
            ]);
        } elseif ($request->filled('date')) {
            $transferQuery->whereDate('created_at', $request->date);
        } else {
            $transferQuery->whereDate('created_at', today());
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $transferQuery->where(function ($q) use ($search) {
                $q->where('product_name', 'LIKE', "%{$search}%")
                    ->orWhere('market_barcode', 'LIKE', "%{$search}%");
            });
        }
        $marketTransfers = $transferQuery->latest()->get();

        $recentTransactions = collect();
        foreach ($invoices as $invoice) {
            $recentTransactions->push((object) [
                'kind' => 'invoice',
                'at'   => $invoice->created_at,
                'invoice' => $invoice,
            ]);
        }
        foreach ($marketTransfers as $transfer) {
            $recentTransactions->push((object) [
                'kind' => 'transfer',
                'at'   => $transfer->created_at,
                'transfer' => $transfer,
            ]);
        }
        $recentTransactions = $recentTransactions->sortByDesc('at')->values();

        $products = Product::with(['wholesaleStorage' => function ($q) {
            $q->where('quantity', '>', 0);
        }])->get();

        $totalRevenue    = $invoices->sum('total_amount');
        $totalInvoices   = $invoices->count();
        $uniqueCustomers = $invoices->pluck('customer_name')->unique()->count();

        return view('wholesale.index', compact(
            'invoices',
            'recentTransactions',
            'totalRevenue',
            'totalInvoices',
            'uniqueCustomers',
            'products'
        ));
    }

    public function autocomplete(Request $request)
    {
        $search = $request->term;

        $customers = WholesaleInvoice::where('customer_name', 'LIKE', "%{$search}%")
            ->distinct()->limit(5)->pluck('customer_name')->toArray();

        $products = Product::where('name', 'LIKE', "%{$search}%")
            ->limit(5)->pluck('name')->toArray();

        return response()->json(array_unique(array_merge($customers, $products)));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT  →  wholesale_invoices_YYYY-MM-DD.xlsx
    // Same filters as index(). No extra packages — pure PHP ZipArchive + XML.
    // ─────────────────────────────────────────────────────────────────────────
public function exportExcel(Request $request)
{
    $query = WholesaleInvoice::with(['items.product']);

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('customer_name', 'LIKE', "%{$search}%")
              ->orWhereHas('items.product', function ($mq) use ($search) {
                  $mq->where('name', 'LIKE', "%{$search}%");
              });
        });
    }

    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('created_at', [
            $request->from_date . ' 00:00:00',
            $request->to_date . ' 23:59:59',
        ]);
    } elseif ($request->filled('date')) {
        $query->whereDate('created_at', $request->date);
    }

    $invoices = $query->latest()->get();

    $headers = ['Invoice No', 'Customer', 'Date', 'products', 'Total Amount ($)', 'Status'];
    $rows    = [];

    foreach ($invoices as $invoice) {
        $meds = $invoice->items->map(fn($i) =>
            ($i->product->name ?? '?') . ' x' . $i->display_quantity
        )->implode(', ');

        $rows[] = [
            $invoice->invoice_no,
            $invoice->customer_name,
            $invoice->created_at->format('Y-m-d H:i'),
            $meds,
            number_format($invoice->total_amount, 2, '.', ''),
            $invoice->status ?? 'Completed',
        ];
    }

    $rows[] = [];
    $rows[] = ['', '', '', 'TOTAL', number_format($invoices->sum('total_amount'), 2, '.', ''), ''];

    $fileName = 'wholesale_invoices_' . date('Y-m-d') . '.xlsx';
    $binary   = self::buildXlsx($headers, $rows);

    return response($binary, 200, [
        'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        'Content-Length'      => strlen($binary),
        'Cache-Control'       => 'max-age=0',
    ]);
}

    // ─────────────────────────────────────────────────────────────────────────
    // Shared xlsx builder — called by both controllers
    // ─────────────────────────────────────────────────────────────────────────
    public static function buildXlsx(array $headers, array $rows): string
    {
        $strings = [];
        $si      = 0;
        $getIdx  = function (string $val) use (&$strings, &$si): int {
            if (!isset($strings[$val])) $strings[$val] = $si++;
            return $strings[$val];
        };

        // Header row
        $sheetRows = '<row r="1">';
        foreach ($headers as $col => $h) {
            $addr = self::colLetter($col) . '1';
            $sheetRows .= '<c r="' . $addr . '" t="s" s="1"><v>' . $getIdx((string)$h) . '</v></c>';
        }
        $sheetRows .= '</row>';

        // Data rows
        foreach ($rows as $ri => $row) {
            $rowNum     = $ri + 2;
            $sheetRows .= '<row r="' . $rowNum . '">';
            foreach ($row as $ci => $cell) {
                $addr  = self::colLetter($ci) . $rowNum;
                $value = (string) $cell;
                if ($value !== '' && is_numeric($value)) {
                    $sheetRows .= '<c r="' . $addr . '"><v>' . $value . '</v></c>';
                } else {
                    $sheetRows .= '<c r="' . $addr . '" t="s"><v>' . $getIdx($value) . '</v></c>';
                }
            }
            $sheetRows .= '</row>';
        }

        // Shared strings
        $ssCount = count($strings);
        $ssXml   = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                 . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
                 . ' count="' . $ssCount . '" uniqueCount="' . $ssCount . '">';
        foreach (array_keys($strings) as $str) {
            $ssXml .= '<si><t xml:space="preserve">' . htmlspecialchars($str, ENT_XML1, 'UTF-8') . '</t></si>';
        }
        $ssXml .= '</sst>';

        // Sheet
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . $sheetRows . '</sheetData></worksheet>';

        // Styles (index 0 = normal, index 1 = bold for header)
        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'   // s="0" normal
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/>'   // s="1" bold
            . '</cellXfs>'
            . '</styleSheet>';

        // Workbook
        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

        $pkgRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml',        $contentTypes);
        $zip->addFromString('_rels/.rels',                $pkgRels);
        $zip->addFromString('xl/workbook.xml',            $workbookXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
        $zip->addFromString('xl/worksheets/sheet1.xml',   $sheetXml);
        $zip->addFromString('xl/sharedStrings.xml',       $ssXml);
        $zip->addFromString('xl/styles.xml',              $stylesXml);
        $zip->close();

        $binary = file_get_contents($tmp);
        unlink($tmp);
        return $binary;
    }

    // 0-based column index → Excel letter (0=A, 25=Z, 26=AA …)
    public static function colLetter(int $index): string
    {
        $letter = '';
        $index++;
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index  = intdiv($index, 26);
        }
        return $letter;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Everything below is YOUR ORIGINAL CODE — untouched
    // ─────────────────────────────────────────────────────────────────────────

    public function create()
    {
        $products = Product::whereHas('wholesaleStorage', function ($query) {
            $query->where('quantity', '>', 0);
        })->withSum('wholesaleStorage as total_stock', 'quantity')->get();

        return view('wholesale.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'items'         => 'required|array|min:1',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $invoice = WholesaleInvoice::create([
                    'invoice_no'    => 'INV-' . strtoupper(uniqid()),
                    'customer_name' => $request->customer_name,
                    'total_amount'  => 0,
                ]);

                $grandTotal = 0;

                foreach ($request->items as $itemData) {
                    $product  = Product::findOrFail($itemData['product_id']);
                    $qtyToSell = (int) $itemData['qty'];

                    $totalStock = WholesaleStorage::where('product_id', $product->id)->sum('quantity');
                    if ($totalStock < $qtyToSell) {
                        throw new \Exception("Not enough stock for {$product->name}. Available: {$totalStock}");
                    }

                    $batches = WholesaleStorage::where('product_id', $product->id)
                        ->where('quantity', '>', 0)
                        ->orderBy('expiry_date', 'asc')
                        ->get();

                    $remaining    = $qtyToSell;
                    $unitPrice    = 0;
                    $firstBatchId = null;

                    foreach ($batches as $batch) {
                        if ($remaining <= 0) break;
                        if ($firstBatchId === null) {
                            $unitPrice    = $batch->selling_price;
                            $firstBatchId = $batch->id;
                        }
                        $take = min($batch->quantity, $remaining);
                        $batch->decrement('quantity', $take);
                        $remaining -= $take;
                    }

                    $totalUnits = $qtyToSell * ($product->units_per_box ?? 1);
                    $product->decrement('total_quantity_units', $totalUnits);

                    $discount = $itemData['discount'] ?? 0;
                    $rowTotal = ($unitPrice * $qtyToSell) * (1 - ($discount / 100));
                    $grandTotal += $rowTotal;

                    // Get first batch info for expiry date and batch number
                    $firstBatch = WholesaleStorage::where('product_id', $product->id)
                        ->where('quantity', '>', 0)
                        ->orderBy('expiry_date', 'asc')
                        ->first();

                    WholesaleItem::create([
                        'wholesale_invoice_id' => $invoice->id,
                        'product_id'          => $product->id,
                        'unit_type'           => $product->unit_type ?? 'piece',
                        'quantity'             => $qtyToSell,
                        'unit_price'           => $unitPrice,
                        'discount_percent'     => $itemData['discount'] ?? 0,
                        'row_total'            => $rowTotal,
                        'expiry_date_text'     => $firstBatch ? $firstBatch->expiry_date->format('Y-m-d') : null,
                        'batch_number_text'    => $firstBatch ? $firstBatch->batch_number : null,
                    ]);
                }

                $invoice->update(['total_amount' => $grandTotal]);

                return redirect()->route('wholesale.index')->with('success', 'Wholesale invoice created!');
            });
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'transfer_quantity' => 'nullable|numeric|min:0',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = (float) ($request->transfer_quantity ?? 0);

        if ($quantity == 0) {
            return redirect()->back()->with('error', 'No quantity to transfer.');
        }

        $totalUnits = $quantity * ($product->units_per_box ?? 1);

        $totalStock = WholesaleStorage::where('product_id', $product->id)->sum('quantity');
        if ($totalStock < $quantity) {
            return redirect()->back()->with('error', 'Not enough stock in wholesale warehouse.');
        }

        // For now, just decrement the stock, assuming transfer to retail or something
        // In reality, this might need to update retail storage or create a transfer record
        $batches = WholesaleStorage::where('product_id', $product->id)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        $remaining = $quantity;
        foreach ($batches as $batch) {
            if ($remaining <= 0) break;
            $take = min($batch->quantity, $remaining);
            $batch->decrement('quantity', $take);
            $remaining -= $take;
        }

        $product->decrement('total_quantity_units', $totalUnits);

        return redirect()->route('wholesale.index')->with('success', 'Stock transferred successfully.');
    }

    public function show($id)
    {
        $invoice = WholesaleInvoice::with('items.product')->findOrFail($id);
        return view('wholesale.show', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice   = WholesaleInvoice::with('items.product')->findOrFail($id);
        $products = Product::whereHas('wholesaleStorage', function ($query) {
            $query->where('quantity', '>', 0);
        })->get();

        return view('wholesale.edit', compact('invoice', 'products'));
    }

    public function update(Request $request, $id)
    {
        $invoice = WholesaleInvoice::with('items')->findOrFail($id);

        try {
            DB::transaction(function () use ($request, $invoice) {
                $newTotal = 0;

                foreach ($request->items as $itemData) {
                    $item         = WholesaleItem::findOrFail($itemData['id']);
                    $storageEntry = \App\Models\WholesaleStorage::where('product_id', $item->product_id)->first();
                    $newQuantity  = $itemData['quantity'];
                    $oldQuantity  = $item->quantity;
                    $diff         = $newQuantity - $oldQuantity;

                    if ($storageEntry) {
                        if ($diff > 0) {
                            if ($storageEntry->quantity < $diff) {
                                throw new \Exception("Not enough stock for product: " . $item->product->name);
                            }
                            $storageEntry->decrement('quantity', $diff);
                        } elseif ($diff < 0) {
                            $storageEntry->increment('quantity', abs($diff));
                        }
                    }

                    $unitPrice = $storageEntry ? $storageEntry->selling_price : $item->unit_price;
                    $discount  = $itemData['discount'] ?? $item->discount_percent;
                    $rowTotal  = ($newQuantity * $unitPrice) * (1 - ($discount / 100));

                    $item->update([
                        'quantity'         => $newQuantity,
                        'unit_price'       => $unitPrice,
                        'discount_percent' => $discount,
                        'row_total'        => $rowTotal,
                    ]);

                    $newTotal += $rowTotal;
                }

                $invoice->update([
                    'customer_name' => $request->customer_name,
                    'total_amount'  => $newTotal,
                ]);
            });

            return redirect()->route('wholesale.index')->with('success', 'Invoice Updated Successfully!');
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function destroy($id)
    {
        $invoice = WholesaleInvoice::with('items')->findOrFail($id);

        DB::transaction(function () use ($invoice) {
            foreach ($invoice->items as $item) {
                $storageEntry = WholesaleStorage::where('product_id', $item->product_id)->first();
                if ($storageEntry) {
                    $storageEntry->increment('quantity', $item->quantity);
                }
            }
            $invoice->delete();
        });

        return redirect()->back()->with('success', 'Invoice cancelled. Stock restored to wholesale warehouse.');
    }
}