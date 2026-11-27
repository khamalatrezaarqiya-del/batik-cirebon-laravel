<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nota {{ $order->order_code }} · Batik Daffa</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #F7F5F0;
            color: #24231F;
            padding: 24px;
            line-height: 1.5;
            font-size: 13.5px;
        }
        .nota {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #E8E4DC;
            border-radius: 8px;
            padding: 32px;
        }
        .head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            padding-bottom: 20px;
            border-bottom: 1px solid #E8E4DC;
            margin-bottom: 20px;
        }
        .brand { font-size: 20px; font-weight: 700; letter-spacing: .12em; }
        .brand span { color: #A27B55; font-weight: 500; }
        .addr { font-size: 12px; color: #585651; max-width: 340px; margin-top: 6px; }
        .code { text-align: right; }
        .code strong { display: block; font-size: 16px; }
        .code small { color: #77756F; }
        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 24px;
            margin-bottom: 24px;
        }
        .meta label { display: block; font-size: 11px; color: #77756F; text-transform: uppercase; letter-spacing: .04em; }
        .meta p { font-weight: 500; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #E8E4DC; text-align: left; }
        th { font-size: 11.5px; color: #585651; background: #F7F5F0; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .totals { margin-left: auto; max-width: 300px; }
        .totals div { display: flex; justify-content: space-between; padding: 5px 0; font-variant-numeric: tabular-nums; }
        .totals .grand {
            border-top: 1px solid #24231F;
            margin-top: 6px;
            padding-top: 10px;
            font-weight: 700;
            font-size: 15px;
        }
        .foot {
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px solid #E8E4DC;
            font-size: 11.5px;
            color: #77756F;
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }
        .actions {
            max-width: 720px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid #24231F;
            background: #24231F;
            color: #fff;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn.ghost { background: #fff; color: #24231F; border-color: #D3CEC3; }
        @media print {
            body { background: #fff; padding: 0; }
            .nota { border: 0; padding: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <a href="{{ url('/admin/orders') }}" class="btn ghost">Kembali ke Admin</a>
        <button type="button" class="btn" onclick="window.print()">Cetak Nota / Simpan PDF</button>
    </div>

    <div class="nota">
        <div class="head">
            <div>
                <div class="brand">BATIK <span>DAFFA</span></div>
                <p class="addr">
                    Jl. Raya Panembahan No. 52/468, Panembahan, Kec. Plered, Kabupaten Cirebon, Jawa Barat 45154
                </p>
            </div>
            <div class="code">
                <strong>NOTA PESANAN</strong>
                <div>{{ $order->order_code }}</div>
                <small>{{ $order->created_at?->format('d M Y, H:i') }}</small>
            </div>
        </div>

        <div class="meta">
            <div>
                <label>Penerima</label>
                <p>{{ $order->recipient_name }} ({{ $order->phone }})</p>
            </div>
            <div>
                <label>Metode &amp; Status Bayar</label>
                <p>
                    {{ match($order->payment_method) {
                        'transfer' => 'Transfer Bank (Lunas)',
                        'dp50' => 'DP 50% (Transfer Bank)',
                        'ewallet' => 'QRIS / E-Wallet',
                        'cod' => 'COD (Bayar di Tempat)',
                        default => $order->payment_method,
                    } }}
                    · {{ ucfirst($order->payment_status ?: 'pending') }}
                </p>
            </div>
            <div style="grid-column: 1 / -1;">
                <label>Alamat Pengiriman</label>
                <p>{{ $order->shipping_address }}</p>
            </div>
            @if ($order->note)
                <div style="grid-column: 1 / -1;">
                    <label>Catatan</label>
                    <p>{{ $order->note }}</p>
                </div>
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Ukuran</th>
                    <th class="num">Qty</th>
                    <th class="num">Harga Satuan</th>
                    <th class="num">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product?->nama ?? 'Produk Batik Daffa' }}</td>
                        <td>{{ $item->size ?: '—' }}</td>
                        <td class="num">{{ $item->qty }}</td>
                        <td class="num">Rp {{ number_format((int) $item->unit_price, 0, ',', '.') }}</td>
                        <td class="num">Rp {{ number_format((int) $item->line_total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div>
                <span>Subtotal</span>
                <span>Rp {{ number_format((int) $order->subtotal, 0, ',', '.') }}</span>
            </div>
            <div>
                <span>Ongkos Kirim</span>
                <span>Rp {{ number_format((int) $order->shipping_cost, 0, ',', '.') }}</span>
            </div>
            <div class="grand">
                <span>Total</span>
                <span>Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</span>
            </div>
            @if ($order->payment_method === 'dp50')
                <div style="color: #69715B; font-weight: 600;">
                    <span>Nominal DP 50%</span>
                    <span>Rp {{ number_format((int) ceil($order->grand_total / 2), 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <div class="foot">
            <span>Garansi tukar ukuran &amp; cacat produksi (robek/berlubang).</span>
            <span>Instagram: @daffabatikcirebon</span>
        </div>
    </div>
</body>
</html>
