<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Storage;
use Illuminate\Support\Collection;

class CustomerController extends Controller
{
    public function index()
    {
        $orders = Sale::select('transaction_id', 'till_id')
            ->selectRaw('SUM(total_price) as total_sum')
            ->selectRaw('MAX(created_at) as order_time')
            ->with('till')
            ->groupBy('transaction_id', 'till_id')
            ->orderBy('order_time', 'desc')
            ->get();

        return view('sales.customers.index', compact('orders'));
    }

    public function show($transaction_id)
    {
        $db_id = $this->normalizeTransactionId($transaction_id);
        $receipt = $this->buildReceipt($db_id);

        if ($receipt === null) {
            return redirect()->route('sales.customers.index');
        }

        return view('sales.customers.show', [
            'lineItems' => $receipt['lines'],
            'transaction_id' => $transaction_id,
            'total' => $receipt['total'],
            'orderDate' => $receipt['orderDate'],
            'subtotalBeforeDiscount' => $receipt['subtotalBeforeDiscount'],
            'discountAmount' => $receipt['discountAmount'],
        ]);
    }

    public function exportAll()
    {
        $orders = Sale::select('transaction_id')
            ->selectRaw('SUM(total_price) as total_sum')
            ->selectRaw('MAX(created_at) as order_time')
            ->groupBy('transaction_id')
            ->orderBy('order_time', 'desc')
            ->get();

        $filename = 'transactions_' . date('d_m_Y') . '.xls';

        $xml = '<?xml version="1.0"?>
    <?mso-application progid="Excel.Sheet"?>
    <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
     xmlns:o="urn:schemas-microsoft-com:office:office"
     xmlns:x="urn:schemas-microsoft-com:office:excel"
     xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
     xmlns:html="http://www.w3.org/TR/REC-html40">
     <Styles>
      <Style ss:ID="header">
       <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
       <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
       </Borders>
       <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>
       <Interior ss:Color="#E8722A" ss:Pattern="Solid"/>
      </Style>
      <Style ss:ID="cell">
       <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
       </Borders>
      </Style>
     </Styles>
     <Worksheet ss:Name="Sales">
      <Table ss:ExpandedColumnCount="3">
       <Column ss:Width="150"/>
       <Column ss:Width="120"/>
       <Column ss:Width="100"/>
       <Row ss:Height="20">
        <Cell ss:StyleID="header"><Data ss:Type="String">ID ТРАНЗАКЦИИ</Data></Cell>
        <Cell ss:StyleID="header"><Data ss:Type="String">ДАТА И ВРЕМЯ</Data></Cell>
        <Cell ss:StyleID="header"><Data ss:Type="String">СУММА (TMT)</Data></Cell>
       </Row>';

        foreach ($orders as $order) {
            $xml .= '<Row>
        <Cell ss:StyleID="cell"><Data ss:Type="String">' . $order->transaction_id . '</Data></Cell>
        <Cell ss:StyleID="cell"><Data ss:Type="String">' . $order->order_time . '</Data></Cell>
        <Cell ss:StyleID="cell"><Data ss:Type="Number">' . number_format($order->total_sum, 2, '.', '') . '</Data></Cell>
       </Row>';
        }

        $xml .= '</Table></Worksheet></Workbook>';

        return response($xml)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    public function exportSingle($transaction_id)
    {
        $db_id = $this->normalizeTransactionId($transaction_id);
        $receipt = $this->buildReceipt($db_id);

        if ($receipt === null) {
            return redirect()->back();
        }

        $filename = 'order_' . str_replace('#', '', $db_id) . '.xls';

        $xml = '<?xml version="1.0"?>
    <?mso-application progid="Excel.Sheet"?>
    <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
     xmlns:o="urn:schemas-microsoft-com:office:office"
     xmlns:x="urn:schemas-microsoft-com:office:excel"
     xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
     xmlns:html="http://www.w3.org/TR/REC-html40">
     <Styles>
      <Style ss:ID="title">
       <Font ss:FontName="Calibri" ss:Size="14" ss:Bold="1"/>
      </Style>
      <Style ss:ID="header">
       <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
       <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
       </Borders>
       <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>
       <Interior ss:Color="#E8722A" ss:Pattern="Solid"/>
      </Style>
      <Style ss:ID="cell">
       <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
       </Borders>
      </Style>
      <Style ss:ID="total">
       <Font ss:FontName="Calibri" ss:Bold="1" ss:Color="#E8722A"/>
       <Borders>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
       </Borders>
      </Style>
     </Styles>
     <Worksheet ss:Name="Order Details">
      <Table ss:ExpandedColumnCount="6">
       <Column ss:Width="180"/>
       <Column ss:Width="120"/>
       <Column ss:Width="80"/>
       <Column ss:Width="80"/>
       <Column ss:Width="60"/>
       <Column ss:Width="100"/>

       <Row ss:Height="25">
        <Cell ss:StyleID="title"><Data ss:Type="String">ДЕТАЛИ ЧЕКА ' . $db_id . '</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">Дата: ' . $receipt['orderDate']->format('d.m.Y H:i') . '</Data></Cell>
       </Row>
       <Row></Row>

       <Row ss:Height="20">
        <Cell ss:StyleID="header"><Data ss:Type="String">ТОВАР</Data></Cell>
        <Cell ss:StyleID="header"><Data ss:Type="String">ШТРИХ-КОД</Data></Cell>
        <Cell ss:StyleID="header"><Data ss:Type="String">КОЛ-ВО</Data></Cell>
        <Cell ss:StyleID="header"><Data ss:Type="String">ЦЕНА</Data></Cell>
        <Cell ss:StyleID="header"><Data ss:Type="String">СКИДКА %</Data></Cell>
        <Cell ss:StyleID="header"><Data ss:Type="String">ИТОГО</Data></Cell>
       </Row>';

        foreach ($receipt['lines'] as $line) {
            $xml .= '<Row>
        <Cell ss:StyleID="cell"><Data ss:Type="String">' . htmlspecialchars($line['name'], ENT_XML1) . '</Data></Cell>
        <Cell ss:StyleID="cell"><Data ss:Type="String">' . htmlspecialchars($line['barcode'], ENT_XML1) . '</Data></Cell>
        <Cell ss:StyleID="cell"><Data ss:Type="String">' . $line['qty_display'] . ' ' . $line['unit'] . '</Data></Cell>
        <Cell ss:StyleID="cell"><Data ss:Type="Number">' . number_format($line['unit_price_original'], 2, '.', '') . '</Data></Cell>
        <Cell ss:StyleID="cell"><Data ss:Type="Number">' . (int) $line['discount_percent'] . '</Data></Cell>
        <Cell ss:StyleID="cell"><Data ss:Type="Number">' . number_format($line['line_total'], 2, '.', '') . '</Data></Cell>
       </Row>';
        }

        $xml .= '<Row></Row>
       <Row>
        <Cell ss:StyleID="cell"><Data ss:Type="String">Промежуточный итог</Data></Cell>
        <Cell ss:Index="6" ss:StyleID="cell"><Data ss:Type="Number">' . number_format($receipt['subtotalBeforeDiscount'], 2, '.', '') . '</Data></Cell>
       </Row>
       <Row>
        <Cell ss:StyleID="cell"><Data ss:Type="String">Скидка (TMT)</Data></Cell>
        <Cell ss:Index="6" ss:StyleID="cell"><Data ss:Type="Number">' . number_format($receipt['discountAmount'], 2, '.', '') . '</Data></Cell>
       </Row>
       <Row>
        <Cell ss:Index="5" ss:StyleID="total"><Data ss:Type="String">ИТОГО:</Data></Cell>
        <Cell ss:StyleID="total"><Data ss:Type="Number">' . number_format($receipt['total'], 2, '.', '') . '</Data></Cell>
       </Row>';

        $xml .= '</Table></Worksheet></Workbook>';

        return response($xml)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    private function normalizeTransactionId(string $transaction_id): string
    {
        return str_starts_with($transaction_id, '#') ? $transaction_id : '#' . $transaction_id;
    }

    /**
     * @return array{lines: array<int, array<string, mixed>>, orderDate: \Illuminate\Support\Carbon, total: float, subtotalBeforeDiscount: float, discountAmount: float}|null
     */
    private function buildReceipt(string $db_id): ?array
    {
        $sales = Sale::where('transaction_id', $db_id)->with('product')->orderBy('id')->get();

        if ($sales->isEmpty()) {
            return null;
        }

        $rawItems = $this->extractRawLineItems($sales);
        if ($rawItems === []) {
            return null;
        }

        $productIds = array_values(array_unique(array_filter(array_map(
            fn (array $item) => (int) ($item['product_id'] ?? 0),
            $rawItems
        ))));

        $products = Product::with('barcodes')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $storagesByProduct = Storage::whereIn('product_id', $productIds)
            ->get()
            ->groupBy('product_id');

        $lines = [];
        foreach ($rawItems as $item) {
            $lines[] = $this->normalizeLineItem($item, $products, $storagesByProduct);
        }

        $subtotalBeforeDiscount = round(array_sum(array_column($lines, 'line_subtotal')), 2);
        $total = round(array_sum(array_column($lines, 'line_total')), 2);
        $discountAmount = round(max(0, $subtotalBeforeDiscount - $total), 2);

        return [
            'lines' => $lines,
            'orderDate' => $sales->max('created_at'),
            'total' => $total,
            'subtotalBeforeDiscount' => $subtotalBeforeDiscount,
            'discountAmount' => $discountAmount,
        ];
    }

    /**
     * @param  Collection<int, Sale>  $sales
     * @return array<int, array<string, mixed>>
     */
    private function extractRawLineItems(Collection $sales): array
    {
        $primary = $sales->first();
        $decoded = json_decode((string) ($primary->items_json ?? ''), true);

        if (is_array($decoded) && $decoded !== []) {
            return $decoded;
        }

        if ($sales->count() > 1) {
            return $sales->map(function (Sale $sale) {
                return [
                    'product_id' => $sale->product_id,
                    'name' => $sale->product?->name,
                    'quantity' => $sale->quantity,
                    'price' => $sale->price,
                    'total_price' => $sale->total_price,
                    'sale_type' => $sale->sale_type,
                    'discount' => (int) ($sale->discount ?? 0),
                ];
            })->all();
        }

        return [[
            'product_id' => $primary->product_id,
            'name' => $primary->product?->name,
            'quantity' => $primary->quantity,
            'price' => $primary->price,
            'total_price' => $primary->total_price,
            'sale_type' => $primary->sale_type,
            'discount' => (int) ($primary->discount ?? 0),
        ]];
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Collection<int, Storage>>  $storagesByProduct
     * @return array<string, mixed>
     */
    private function normalizeLineItem(array $item, Collection $products, Collection $storagesByProduct): array
    {
        $productId = (int) ($item['product_id'] ?? 0);
        $product = $products->get($productId);

        $saleType = (string) ($item['sale_type'] ?? $product?->unit_type ?? 'piece');
        $unit = $saleType === 'weight' ? 'kg' : 'pcs';
        $quantity = (float) ($item['quantity'] ?? 0);
        $unitPrice = (float) ($item['price'] ?? 0);
        $discountPercent = max(0, (int) ($item['discount'] ?? 0));
        $priceOverridden = (bool) ($item['price_overridden'] ?? false);
        $listPrice = isset($item['list_price']) ? (float) $item['list_price'] : null;
        $defaultPrice = isset($item['default_price']) ? (float) $item['default_price'] : null;

        $lineTotal = round((float) ($item['total_price'] ?? ($quantity * $unitPrice)), 2);

        if ($priceOverridden) {
            $originalUnitPrice = $listPrice ?? $defaultPrice ?? $unitPrice;
            $lineSubtotal = round($quantity * $originalUnitPrice, 2);
        } elseif ($discountPercent > 0 && $unitPrice > 0) {
            $denominator = 1 - ($discountPercent / 100);
            $originalUnitPrice = $denominator > 0
                ? round($unitPrice / $denominator, 2)
                : $unitPrice;
            $lineSubtotal = round($quantity * $originalUnitPrice, 2);
        } else {
            $originalUnitPrice = $listPrice ?? $unitPrice;
            $lineSubtotal = round($quantity * $originalUnitPrice, 2);
        }

        $lineDiscount = round(max(0, $lineSubtotal - $lineTotal), 2);

        $qtyDisplay = $unit === 'kg'
            ? number_format($quantity, 3, '.', '')
            : (string) (int) $quantity;

        return [
            'name' => (string) ($item['name'] ?? $product?->name ?? __('app.receipt_product_deleted')),
            'barcode' => $this->resolveBarcode($product, $storagesByProduct->get($productId)),
            'quantity' => $quantity,
            'qty_display' => $qtyDisplay,
            'unit' => $unit,
            'unit_price' => $unitPrice,
            'unit_price_original' => $originalUnitPrice,
            'discount_percent' => $discountPercent,
            'price_overridden' => $priceOverridden,
            'default_price' => $defaultPrice,
            'line_discount' => $lineDiscount,
            'line_subtotal' => $lineSubtotal,
            'line_total' => $lineTotal,
        ];
    }

    private function resolveBarcode(?Product $product, ?Collection $storages): string
    {
        if ($product === null) {
            return '—';
        }

        if (! empty($product->barcode)) {
            return (string) $product->barcode;
        }

        $extra = $product->relationLoaded('barcodes')
            ? $product->barcodes->first()?->barcode
            : null;

        if (! empty($extra)) {
            return (string) $extra;
        }

        if ($storages !== null) {
            foreach ($storages as $storage) {
                if (! empty($storage->barcode)) {
                    return (string) $storage->barcode;
                }
            }
        }

        return '—';
    }
}
