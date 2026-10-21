<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penawaran Berhasil Dikirim</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #1f2937;
        }
        .card {
            width: 100%;
            max-width: 520px;
            padding: 32px;
            box-sizing: border-box;
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
        h1 { font-size: 24px; }
        p { line-height: 1.6; color: #475569; }
        .reference {
            padding: 12px;
            background: #f1f5f9;
            border-radius: 8px;
            overflow-wrap: anywhere;
            font-family: monospace;
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
            Penawaran kamu telah tercatat dan siap ditinjau oleh tim procurement.
        </p>

        <p>Status penawaran:
            <strong>{{ $quotation->status }}</strong>
        </p>

        <p>Nomor referensi penawaran:</p>
        <div class="reference">{{ $quotation->public_token }}</div>

        <p>Simpan nomor referensi ini untuk kebutuhan komunikasi selanjutnya.</p>
        <p><a href="/">Kembali ke dashboard</a></p>
    </main>
</body>
</html>
