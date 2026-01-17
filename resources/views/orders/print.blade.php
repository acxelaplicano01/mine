<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notas de Entrega</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .delivery-note {
            page-break-after: always;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .delivery-note:last-child {
            page-break-after: auto;
        }
        
        .header {
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .order-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .order-info-section {
            flex: 1;
        }
        
        .order-info-section h3 {
            font-size: 14px;
            margin-bottom: 8px;
            color: #666;
        }
        
        .order-info-section p {
            margin-bottom: 3px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th {
            background-color: #f5f5f5;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #333;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .items-table tr:last-child td {
            border-bottom: 2px solid #333;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totals {
            margin-top: 20px;
            text-align: right;
        }
        
        .totals-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 8px;
        }
        
        .totals-label {
            width: 150px;
            font-weight: bold;
            text-align: right;
            margin-right: 20px;
        }
        
        .totals-value {
            width: 100px;
            text-align: right;
        }
        
        .total-final {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 8px;
            margin-top: 8px;
        }
        
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #333;
        }
        
        .notes h3 {
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 11px;
        }
        
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .delivery-note {
                page-break-after: always;
            }
            
            .delivery-note:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    @foreach($orders as $order)
    <div class="delivery-note">
        <div class="header">
            <h1>Nota de Entrega</h1>
            <p><strong>Pedido:</strong> #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</p>
            <p><strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>
        
        <div class="order-info">
            <div class="order-info-section">
                <h3>Cliente</h3>
                <p><strong>{{ $order->customer?->name ?? 'Sin cliente' }}</strong></p>
                @if($order->customer?->email)
                <p>{{ $order->customer->email }}</p>
                @endif
                @if($order->customer?->phone)
                <p>Tel: {{ $order->customer->phone }}</p>
                @endif
                @if($order->customer?->address)
                <p>{{ $order->customer->address }}</p>
                @endif
            </div>
            
            <div class="order-info-section">
                <h3>Estado del Pedido</h3>
                <p><strong>Pago:</strong> {{ $order->statusOrder?->name ?? 'Sin estado' }}</p>
                <p><strong>Preparación:</strong> {{ $order->statusPreparedOrder?->name ?? 'Sin estado' }}</p>
                @if($order->envio)
                <p><strong>Envío:</strong> {{ $order->envio->address ?? 'Pendiente' }}</p>
                @else
                <p><strong>Recogida en tienda</strong></p>
                @endif
            </div>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th>Cantidad</th>
                    <th>Producto</th>
                    <th class="text-right">Precio Unit.</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td>
                        <strong>{{ $item->product?->name ?? 'Producto no disponible' }}</strong>
                        @if($item->variant)
                        <br><small>{{ $item->variant->name }}</small>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->price, 2) }} L</td>
                    <td class="text-right">{{ number_format($item->price * $item->quantity, 2) }} L</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="totals">
            <div class="totals-row">
                <div class="totals-label">Subtotal:</div>
                <div class="totals-value">{{ number_format($order->subtotal_price ?? $order->total_price, 2) }} L</div>
            </div>
            
            @if($order->discount_amount > 0)
            <div class="totals-row">
                <div class="totals-label">Descuento:</div>
                <div class="totals-value">-{{ number_format($order->discount_amount, 2) }} L</div>
            </div>
            @endif
            
            @if($order->tax_amount > 0)
            <div class="totals-row">
                <div class="totals-label">Impuestos:</div>
                <div class="totals-value">{{ number_format($order->tax_amount, 2) }} L</div>
            </div>
            @endif
            
            @if($order->shipping_cost > 0)
            <div class="totals-row">
                <div class="totals-label">Envío:</div>
                <div class="totals-value">{{ number_format($order->shipping_cost, 2) }} L</div>
            </div>
            @endif
            
            <div class="totals-row total-final">
                <div class="totals-label">TOTAL:</div>
                <div class="totals-value">{{ number_format($order->total_price, 2) }} L</div>
            </div>
        </div>
        
        @if($order->note)
        <div class="notes">
            <h3>Notas del pedido:</h3>
            <p>{{ $order->note }}</p>
        </div>
        @endif
        
        <div class="footer">
            <p>Gracias por su compra</p>
        </div>
    </div>
    @endforeach
    
    <script>
        // Abrir automáticamente el diálogo de impresión
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
