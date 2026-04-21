<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $orders = Sale::select('transaction_id')
            ->selectRaw('SUM(total_price) as total_sum')
            ->selectRaw('MAX(created_at) as order_time')
            ->groupBy('transaction_id')
            ->orderBy('order_time', 'desc')
            ->get();

        // FIX: Removed 'admin.' from the path
        return view('sales.customers.index', compact('orders'));
    }

    public function show($transaction_id)
    {
        // 1. Logic to handle the # symbol if it was stripped in the URL
        $db_id = str_starts_with($transaction_id, '#') ? $transaction_id : '#' . $transaction_id;

        $items = Sale::where('transaction_id', $db_id)
            ->with('product') 
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('sales.customers.index');
        }

        $total = $items->sum('total_price');
        $orderDate = $items->first()->created_at;

        $subtotalBeforeDiscount = $items->sum(function (Sale $item) {
            $d = (float) ($item->discount ?? 0);
            $q = (float) $item->quantity;
            $p = (float) $item->price;
            if ($d <= 0) {
                return round($q * $p, 2);
            }
            $den = 1 - ($d / 100);
            if ($den <= 0) {
                return round($q * $p, 2);
            }

            return round(($q * $p) / $den, 2);
        });

        $discountAmount = round(max(0, $subtotalBeforeDiscount - $total), 2);

        // FIX: Removed 'admin.' from the path
        return view('sales.customers.show', compact(
            'items',
            'transaction_id',
            'total',
            'orderDate',
            'subtotalBeforeDiscount',
            'discountAmount'
        ));
    }
public function exportAll()
{
    $orders = Sale::select('transaction_id')
        ->selectRaw('SUM(total_price) as total_sum')
        ->selectRaw('MAX(created_at) as order_time')
        ->groupBy('transaction_id')
        ->orderBy('order_time', 'desc')
        ->get();

    $filename = "transactions_" . date('d_m_Y') . ".xls";

    // Формируем XML со стилями (цвета, границы, шрифты)
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
    $db_id = str_starts_with($transaction_id, '#') ? $transaction_id : '#' . $transaction_id;
    $items = Sale::where('transaction_id', $db_id)->with('product')->get();

    if ($items->isEmpty()) return redirect()->back();

    $filename = "order_" . str_replace('#', '', $db_id) . ".xls";

    // XML со стилями: header (бирюзовый), cell (сетка), total (жирный)
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
       <Column ss:Width="80"/>
       <Column ss:Width="80"/>
       <Column ss:Width="80"/>
       <Column ss:Width="60"/>
       <Column ss:Width="100"/>
       
       <Row ss:Height="25">
        <Cell ss:StyleID="title"><Data ss:Type="String">ДЕТАЛИ ЧЕКА ' . $db_id . '</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">Дата: ' . $items->first()->created_at->format('d.m.Y H:i') . '</Data></Cell>
       </Row>
       <Row></Row> {{-- Пустая строка --}}
       
       <Row ss:Height="20">
        <Cell ss:StyleID="header"><Data ss:Type="String">ТОВАР</Data></Cell>
        <Cell ss:StyleID="header"><Data ss:Type="String">АРТИКУЛ</Data></Cell>
        <Cell ss:StyleID="header"><Data ss:Type="String">КОЛ-ВО</Data></Cell>
        <Cell ss:StyleID="header"><Data ss:Type="String">ЦЕНА</Data></Cell>
        <Cell ss:StyleID="header"><Data ss:Type="String">СКИДКА %</Data></Cell>
        <Cell ss:StyleID="header"><Data ss:Type="String">ИТОГО</Data></Cell>
       </Row>';

    foreach ($items as $item) {
        $xml .= '<Row>
        <Cell ss:StyleID="cell"><Data ss:Type="String">' . ($item->product->name ?? 'Удален') . '</Data></Cell>
        <Cell ss:StyleID="cell"><Data ss:Type="String">' . $item->product_id . '</Data></Cell>
        <Cell ss:StyleID="cell"><Data ss:Type="Number">' . $item->quantity . '</Data></Cell>
        <Cell ss:StyleID="cell"><Data ss:Type="Number">' . number_format($item->price, 2, '.', '') . '</Data></Cell>
        <Cell ss:StyleID="cell"><Data ss:Type="Number">' . (int) ($item->discount ?? 0) . '</Data></Cell>
        <Cell ss:StyleID="cell"><Data ss:Type="Number">' . number_format($item->total_price, 2, '.', '') . '</Data></Cell>
       </Row>';
    }

    $subGross = $items->sum(function (Sale $item) {
        $d = (float) ($item->discount ?? 0);
        $q = (float) $item->quantity;
        $p = (float) $item->price;
        if ($d <= 0) {
            return round($q * $p, 2);
        }
        $den = 1 - ($d / 100);

        return $den <= 0 ? round($q * $p, 2) : round(($q * $p) / $den, 2);
    });
    $discAmt = round(max(0, $subGross - $items->sum('total_price')), 2);

    $xml .= '<Row></Row>
       <Row>
        <Cell ss:StyleID="cell"><Data ss:Type="String">Промежуточный итог</Data></Cell>
        <Cell ss:Index="6" ss:StyleID="cell"><Data ss:Type="Number">' . number_format($subGross, 2, '.', '') . '</Data></Cell>
       </Row>
       <Row>
        <Cell ss:StyleID="cell"><Data ss:Type="String">Скидка (TMT)</Data></Cell>
        <Cell ss:Index="6" ss:StyleID="cell"><Data ss:Type="Number">' . number_format($discAmt, 2, '.', '') . '</Data></Cell>
       </Row>
       <Row>
        <Cell ss:Index="5" ss:StyleID="total"><Data ss:Type="String">ИТОГО:</Data></Cell>
        <Cell ss:StyleID="total"><Data ss:Type="Number">' . number_format($items->sum('total_price'), 2, '.', '') . '</Data></Cell>
       </Row>';

    $xml .= '</Table></Worksheet></Workbook>';

    return response($xml)
        ->header('Content-Type', 'application/vnd.ms-excel')
        ->header('Content-Disposition', "attachment; filename=\"$filename\"");
}
}