<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Thermal Receipt</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 3mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            width: 74mm;
            margin: 0 auto;
            padding: 0;
            background: white;
            color: #000;
        }

        .receipt {
            width: 100%;
            padding: 2mm 1mm;
        }

        /* ── HEADER ── */
        .header {
            text-align: center;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
            border-bottom: 2px solid #000;
        }

        .store-name {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 1mm;
        }

        .store-subtitle {
            font-size: 9px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #333;
        }

        /* ── RECEIPT META ── */
        .meta {
            font-size: 10px;
            margin-bottom: 3mm;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            margin: 0.8mm 0;
        }

        .meta-label {
            color: #555;
        }

        .meta-value {
            font-weight: bold;
            text-align: right;
        }

        /* ── SEPARATOR ── */
        .sep-solid {
            border: none;
            border-top: 1px solid #000;
            margin: 2mm 0;
        }

        .sep-dashed {
            border: none;
            border-top: 1px dashed #000;
            margin: 2mm 0;
        }

        /* ── ITEMS ── */
        .items {
            margin: 2mm 0;
        }

        .item {
            margin: 2mm 0;
        }

        .item-name {
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 70mm;
            margin-bottom: 0.5mm;
        }

        .item-line {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #333;
        }

        .item-total {
            font-weight: bold;
            color: #000;
        }

        /* ── TOTALS ── */
        .totals {
            margin: 2mm 0;
            font-size: 11px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 1mm 0;
        }

        .total-label {
            color: #444;
        }

        .grand-total {
            font-size: 13px;
            font-weight: bold;
            padding: 2mm 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            margin: 2mm 0;
        }

        .discount-row {
            color: #555;
        }

        .payment-row {
            font-size: 10px;
            color: #444;
        }

        /* ── FOOTER ── */
        .footer {
            text-align: center;
            margin-top: 4mm;
            font-size: 10px;
        }

        .thank-you {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 1mm;
        }

        .footer-sub {
            font-size: 9px;
            color: #555;
            margin-top: 1mm;
        }

        .timestamp {
            font-size: 8px;
            color: #888;
            margin-top: 3mm;
            letter-spacing: 0.5px;
        }

        @media print {
            body { margin: 0; padding: 0; }
            .receipt { padding: 0; }
        }
    </style>
</head>
<body>
<div class="receipt">

    {{-- HEADER --}}
    <div class="header">
        <div class="store-name">RysgallyMarket</div>
        <div class="store-subtitle">Ashgabat, Turkmenistan</div>
    </div>

    {{-- META --}}
    <div class="meta">
        <div class="meta-row">
            <span class="meta-label">Receipt #</span>
            <span class="meta-value">{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Date</span>
            <span class="meta-value">{{ $sale->created_at->format('d.m.Y H:i') }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Cashier</span>
            <span class="meta-value">
                @auth {{ auth()->user()->name }} @else Guest @endauth
            </span>
        </div>
        @if($sale->till_id)
        <div class="meta-row">
            <span class="meta-label">Till</span>
            <span class="meta-value">#{{ $sale->till_id }}</span>
        </div>
        @endif
    </div>

    <hr class="sep-solid">

    {{-- ITEMS --}}
    @php
        $items    = json_decode($sale->items_json, true) ?? [];
        $subtotal = 0;
    @endphp

    <div class="items">
        @foreach($items as $item)
            @php
                $qty       = $item['quantity'];
                $price     = $item['price'];
                $itemTotal = $qty * $price;
                $subtotal += $itemTotal;
                $isWeight  = ($item['sale_type'] ?? 'piece') === 'weight';
            @endphp
            <div class="item">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-line">
                    <span>
                        {{ $isWeight ? number_format($qty, 3) . ' kg' : number_format($qty, 0) . ' pcs' }}
                        × {{ number_format($price, 2) }} TMT
                    </span>
                    <span class="item-total">{{ number_format($itemTotal, 2) }} TMT</span>
                </div>
            </div>
            @if(!$loop->last)
                <hr class="sep-dashed">
            @endif
        @endforeach
    </div>

    <hr class="sep-solid">

    {{-- TOTALS --}}
    <div class="totals">
        <div class="total-row">
            <span class="total-label">Subtotal</span>
            <span>{{ number_format($subtotal, 2) }} TMT</span>
        </div>

        @if($sale->discount > 0)
        <div class="total-row discount-row">
            <span class="total-label">Discount</span>
            <span>− {{ number_format($sale->discount, 2) }} TMT</span>
        </div>
        @endif

        <div class="total-row grand-total">
            <span>TOTAL</span>
            <span>{{ number_format($sale->total_price, 2) }} TMT</span>
        </div>

        <div class="total-row payment-row">
            <span class="total-label">Payment</span>
            <span>CASH</span>
        </div>
    </div>

    {{-- FOOTER --}}
    <hr class="sep-dashed">
    <div class="footer">
        <div class="thank-you">THANK YOU!</div>
        <div class="footer-sub">Please come again</div>
        <div class="timestamp">{{ $sale->created_at->format('d.m.Y H:i:s') }}</div>
    </div>

</div>
<script>
    window.onload = function() {
        window.print();
    }
</script>
</body>
</html>