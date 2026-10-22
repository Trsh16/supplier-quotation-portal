
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bukti Penerimaan Penawaran Supplier</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #1f2937;
        }

        .card {
            width: 100%;
            max-width: 560px;
            padding: 32px;
            background: white;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 4px 18px #0000000d;
        }

        .check {
            display: grid;
            place-items: center;
            margin: 0 auto 18px;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #dcfce7;
            color: #166534;
            font-size: 34px;
        }

        h1 {
            font-size: 24px;
            color: #173b65;
        }

        p {
            line-height: 1.6;
            color: #475569;
        }

        .reference {
            padding: 14px;
            background: #f1f5f9;
            border-radius: 8px;
            overflow-wrap: anywhere;
            font-family: monospace;
        }

        .button {
            display: inline-block;
            margin: 8px 4px;
            padding: 12px 18px;
            border-radius: 8px;
            background: #173b65;
            color: white;
            text-decoration: none;
            border: 0;
            font-size: 14px;
            cursor: pointer;
        }

        .secondary {
            background: #e2e8f0;
            color: #1f2937;
        }

        @media print {
            body {
                display: block;
                background: white;
                padding: 0;
            }

            .card {
                max-width: none;
                box-shadow: none;
                border: 1px solid #ddd;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="check">✓</div>

        <h1>Penawaran Berhasil Dikirim</h1>

        <p>
            Terima kasih,
            <strong>{{ $quotation->supplier->company_name ?? 'Supplier' }}</strong>.
        </p>

        <p>
            Penawaran kamu telah berhasil tercatat dalam sistem
            dan siap ditinjau oleh tim procurement.
        </p>

        <p>Status penawaran:</p>
        <p><strong>{{ ucfirst($quotation->status) }}</strong></p>

        <p>Nomor referensi penawaran:</p>
        <div class="reference">{{ $quotation->public_token }}</div>

        <p>
            Waktu pengiriman:
            <strong>
                {{ $quotation->submitted_at
                    ? $quotation->submitted_at->format('d/m/Y H:i:s')
                    : '-' }}
            </strong>
        </p>

        <p>Simpan bukti ini untuk referensi komunikasi selanjutnya.</p>

        <div class="no-print">
            <button class="button" onclick="window.print()">
                Cetak / Simpan PDF
            </button>

            <a class="button secondary" href="{{ route('supplier-offer.create') }}">
                Kirim Penawaran Lain
            </a>
        </div>
    </main>
</body>
</html>
