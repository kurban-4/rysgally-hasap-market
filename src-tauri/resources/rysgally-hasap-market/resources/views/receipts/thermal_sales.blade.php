{{-- Thermal Receipt for XPrinter-P201A (80mm) --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Thermal Receipt</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 4mm;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 72mm;
            margin: 0;
            padding: 0;
            background: white;
            color: black;
        }
        
        .thermal-receipt {
            width: 100%;
            padding: 2mm;
        }
        
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 4mm;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 2mm 0;
        }
        
        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        
        .store-address {
            font-size: 10px;
            margin-bottom: 1mm;
        }
        
        .receipt-info {
            margin-bottom: 4mm;
            font-size: 10px;
        }
        
        .receipt-info div {
            margin: 1mm 0;
        }
        
        .items-table {
            margin-bottom: 4mm;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            margin: 1mm 0;
            font-size: 11px;
        }
        
        .item-name {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 40mm;
        }
        
        .item-details {
            text-align: right;
            min-width: 30mm;
        }
        
        .separator {
            border-top: 1px dashed #000;
            margin: 2mm 0;
        }
        
        .totals {
            margin-bottom: 4mm;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 1mm 0;
            font-size: 11px;
        }
        
        .grand-total {
            font-weight: bold;
            font-size: 12px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 1mm 0;
            margin-top: 2mm;
        }
        
        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 4mm;
        }
        
        .thank-you {
            font-weight: bold;
            margin-bottom: 2mm;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="thermal-receipt">
        <!-- Header -->
        <div class="header">
            <div class="store-name">RysgallyMarket</div>
            <div class="store-address">Ashgabat, Turkmenistan</div>
            <div>TEL: +993 XX XXX-XXX</div>
        </div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <div><strong>Receipt #:</strong> {{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</div>
            <div><strong>Date:</strong> {{ $sale->created_at->format('d.m.Y H:i') }}</div>
            <div><strong>Cashier:</strong> {{ auth()->user()->name }}</div>
            @if($sale->till_id)
                <div><strong>Till:</strong> #{{ $sale->till_id }}</div>
            @endif
        </div>

        <!-- Separator -->
        <div class="separator"></div>

        <!-- Items -->
        <div class="items-table">
            @php
                $items = json_decode($sale->items_json, true) ?? [];
                $subtotal = 0;
                // Debug: log items data
                \Log::info('Thermal receipt items: ', $items);
            @endphp
            
            @foreach($items as $item)
                @php
                    $itemTotal = $item['quantity'] * $item['price'];
                    $subtotal += $itemTotal;
                @endphp
                <div class="item-row">
                    <div class="item-name">{{ $item['name'] }}</div>
                    <div class="item-details">
                        {{ number_format($item['quantity'], ($item['sale_type'] == 'weight' ? 3 : 0)) }} × {{ number_format($item['price'], 2) }}
                    </div>
                </div>
                <div class="item-row">
                    <div class="item-name"></div>
                    <div class="item-details">{{ number_format($itemTotal, 2) }} TMT</div>
                </div>
            @endforeach
        </div>

        <!-- Separator -->
        <div class="separator"></div>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <div>Subtotal:</div>
                <div>{{ number_format($subtotal, 2) }} TMT</div>
            </div>
            
            @if($sale->discount > 0)
                <div class="total-row">
                    <div>Discount:</div>
                    <div>-{{ number_format($sale->discount, 2) }} TMT</div>
                </div>
            @endif
            
            <div class="total-row grand-total">
                <div><strong>TOTAL:</strong></div>
                <div><strong>{{ number_format($sale->total_price, 2) }} TMT</strong></div>
            </div>
            
            <div class="total-row">
                <div>Payment:</div>
                <div>CASH</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">THANK YOU FOR YOUR PURCHASE!</div>
            <div>Please come again</div>
            <div style="margin-top: 4mm; font-size: 9px;">
                {{ $sale->created_at->format('d.m.Y H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>
