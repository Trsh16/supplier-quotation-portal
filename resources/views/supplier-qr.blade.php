
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Supplier</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: white;
            padding: 32px;
            border-radius: 16px;
            text-align: center;
            max-width: 400px;
            width: calc(100% - 64px);
            box-shadow: 0 4px 20px #00000012;
        }
        img {
            width: 280px;
            max-width: 100%;
            height: auto;
        }
        .url {
            overflow-wrap: anywhere;
            color: #555;
            font-size: 14px;
        }
        a {
            display: inline-block;
            margin-top: 12px;
            color: #175cd3;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Supplier Quotation Portal</h1>
        <p>Scan QR Code untuk membuka form penawaran supplier.</p>

        <img src="{{ route('supplier-qr.image') }}"
             alt="QR Code Supplier">

        <!-- <p class="url">{{ $url }}</p> -->

        <a href="{{ $url }}">Buka Form Penawaran</a>
    </main>
</body>
</html>
