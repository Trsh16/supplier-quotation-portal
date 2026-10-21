<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Penawaran Supplier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px 16px;
            background: #f3f6fa;
            color: #1f2937;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 760px;
            margin: 0 auto;
            padding: 28px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 20px #0000000b;
        }
        h1 { margin-top: 0; color: #173b65; }
        h2 { margin-top: 28px; font-size: 18px; }
        label {
            display: block;
            margin: 14px 0 6px;
            font-weight: 600;
        }
        input, select, textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font: inherit;
        }
        button {
            margin-top: 24px;
            padding: 13px 20px;
            width: 100%;
            border: 0;
            border-radius: 8px;
            background: #173b65;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .error { color: #b91c1c; }
        .hint { color: #64748b; line-height: 1.5; }
    </style>
</head>
<body>
<main class="container">
    <h1>Form Penawaran Supplier</h1>
    <p class="hint">Isi informasi perusahaan dan barang yang ingin ditawarkan.</p>

    @if ($errors->any())
        <div class="error">
            <strong>Periksa kembali data berikut:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('supplier-offer.store') }}">
        @csrf

        <h2>Informasi Perusahaan</h2>

        <label for="company_name">Nama Perusahaan *</label>
        <input id="company_name" name="company_name" value="{{ old('company_name') }}" required>

        <label for="email">Email Perusahaan *</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>

        <label for="phone">Nomor Telepon Perusahaan</label>
        <input id="phone" name="phone" value="{{ old('phone') }}">

        <label for="address">Alamat Perusahaan</label>
        <textarea id="address" name="address" rows="3">{{ old('address') }}</textarea>

        <h2>Kontak Sales</h2>

        <label for="sales_name">Nama Sales *</label>
        <input id="sales_name" name="sales_name" value="{{ old('sales_name') }}" required>

        <label for="sales_email">Email Sales *</label>
        <input id="sales_email" type="email" name="sales_email" value="{{ old('sales_email') }}" required>

        <label for="sales_phone">Nomor Telepon Sales</label>
        <input id="sales_phone" name="sales_phone" value="{{ old('sales_phone') }}">

        <h2>Barang yang Ditawarkan</h2>

        <label for="standard_good_id">Barang Standar</label>
        <select id="standard_good_id" name="standard_good_id">
            <option value="">Pilih barang (opsional)</option>
            @foreach ($goods as $good)
                <option value="{{ $good->id }}" @selected(old('standard_good_id') == $good->id)>
                    {{ $good->name }}
                </option>
            @endforeach
        </select>

        <label for="supplier_item_name">Nama Barang dari Supplier *</label>
        <input id="supplier_item_name" name="supplier_item_name" value="{{ old('supplier_item_name') }}" required>

        <label for="supplier_item_code">Kode Barang Supplier</label>
        <input id="supplier_item_code" name="supplier_item_code" value="{{ old('supplier_item_code') }}">

        <label for="supplier_unit_text">Satuan Barang *</label>
        <input id="supplier_unit_text" name="supplier_unit_text" placeholder="Contoh: pcs, kg, dus" value="{{ old('supplier_unit_text') }}" required>

        <label for="unit_id">Satuan Standar (opsional)</label>
        <select id="unit_id" name="unit_id">
            <option value="">Pilih satuan</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>
                    {{ $unit->name }} ({{ $unit->code }})
                </option>
            @endforeach
        </select>

        <label for="quoted_price">Harga Penawaran per Satuan (IDR) *</label>
        <input id="quoted_price" type="number" name="quoted_price" min="0.01" step="0.01" value="{{ old('quoted_price') }}" required>

        <label for="minimum_order_quantity">Minimum Pembelian *</label>
        <input id="minimum_order_quantity" type="number" name="minimum_order_quantity" min="0.0001" step="0.0001" value="{{ old('minimum_order_quantity', 1) }}" required>

        <label for="vat_type">Status PPN *</label>
        <select id="vat_type" name="vat_type" required>
            <option value="excluded" @selected(old('vat_type') === 'excluded')>Harga belum termasuk PPN</option>
            <option value="included" @selected(old('vat_type') === 'included')>Harga sudah termasuk PPN</option>
        </select>

        <label for="vat_rate">Tarif PPN (%)</label>
        <input id="vat_rate" type="number" name="vat_rate" min="0" max="100" step="0.01" value="{{ old('vat_rate', 0) }}">

        <label for="valid_until">Penawaran Berlaku Sampai</label>
        <input id="valid_until" type="date" name="valid_until" value="{{ old('valid_until') }}">

        <label for="notes">Catatan Tambahan</label>
        <textarea id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>

        <button type="submit">Kirim Penawaran</button>
    </form>
</main>
</body>
</html>
