
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Penawaran | SupplyFlow</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            background: #f5f7fb;
            color: #172033;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .container {
            max-width: 850px;
            margin: 0 auto;
        }
        .panel {
            padding: 26px;
            background: white;
            border: 1px solid #e5e9f2;
            border-radius: 14px;
        }
        h1 { margin-top: 0; font-size: 25px; }
        .muted { color: #667085; font-size: 13px; line-height: 1.6; }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .field { margin-bottom: 16px; }
        label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 650;
        }
        input, select, textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d8deea;
            border-radius: 8px;
            background: white;
            color: #172033;
            font: inherit;
        }
        textarea { min-height: 90px; resize: vertical; }
        .buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 8px;
        }
        .button {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            padding: 11px 16px;
            background: #4f46e5;
            color: white;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .secondary { background: #eef0f6; color: #344054; }
        .errors {
            margin-bottom: 18px;
            padding: 12px 16px;
            border-radius: 8px;
            background: #fee2e2;
            color: #991b1b;
        }
        @media (max-width: 600px) {
            body { padding: 12px; }
            .panel { padding: 18px; }
            .grid { grid-template-columns: 1fr; gap: 0; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="panel">
        <h1>Edit Penawaran</h1>
        <p class="muted">
            Supplier: <strong>{{ $quotation->supplier->company_name ?? '—' }}</strong><br>
            Referensi: {{ $quotation->public_token }}<br>
            Status saat ini: {{ ucwords(str_replace('_', ' ', $quotation->status)) }}
        </p>

        @if ($errors->any())
            <div class="errors">
                <strong>Periksa kembali data berikut:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('quotations.update', $quotation) }}">
            @csrf
            @method('PUT')

            <div class="grid">
                <div class="field">
                    <label for="standard_good_id">Barang Standar *</label>
                    <select id="standard_good_id" name="standard_good_id" required>
                        <option value="">Pilih barang</option>
                        @foreach ($goods as $good)
                            <option value="{{ $good->id }}"
                                @selected(old('standard_good_id', $item->standard_good_id) == $good->id)>
                                {{ $good->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="supplier_item_name">Nama Barang Supplier *</label>
                    <input id="supplier_item_name" name="supplier_item_name"
                        value="{{ old('supplier_item_name', $item->supplier_item_name) }}"
                        maxlength="255" required>
                </div>

                <div class="field">
                    <label for="supplier_item_code">Kode Barang Supplier</label>
                    <input id="supplier_item_code" name="supplier_item_code"
                        value="{{ old('supplier_item_code', $item->supplier_item_code) }}"
                        maxlength="100">
                </div>

                <div class="field">
                    <label for="supplier_unit_text">Nama Satuan Supplier *</label>
                    <input id="supplier_unit_text" name="supplier_unit_text"
                        value="{{ old('supplier_unit_text', $item->supplier_unit_text) }}"
                        maxlength="100" required>
                </div>

                <div class="field">
                    <label for="unit_id">Satuan Terstandar *</label>
                    <select id="unit_id" name="unit_id" required>
                        <option value="">Pilih satuan</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}"
                                @selected(old('unit_id', $item->unit_id) == $unit->id)>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="quoted_price">Harga Penawaran (Rp) *</label>
                    <input id="quoted_price" name="quoted_price" type="number"
                        min="0.01" step="0.01"
                        value="{{ old('quoted_price', $item->quoted_price) }}" required>
                </div>

                <div class="field">
                    <label for="minimum_order_quantity">Minimum Pemesanan *</label>
                    <input id="minimum_order_quantity" name="minimum_order_quantity"
                        type="number" min="0.0001" step="0.0001"
                        value="{{ old('minimum_order_quantity', $item->minimum_order_quantity) }}"
                        required>
                </div>

                <div class="field">
                    <label for="vat_type">Jenis PPN *</label>
                    <select id="vat_type" name="vat_type" required>
                        <option value="included"
                            @selected(old('vat_type', $quotation->vat_type) === 'included')>
                            Harga sudah termasuk PPN
                        </option>
                        <option value="excluded"
                            @selected(old('vat_type', $quotation->vat_type) === 'excluded')>
                            Harga belum termasuk PPN
                        </option>
                    </select>
                </div>

                <div class="field">
                    <label for="vat_rate">Tarif PPN (%) *</label>
                    <input id="vat_rate" name="vat_rate" type="number"
                        min="0" max="100" step="0.01"
                        value="{{ old('vat_rate', $quotation->vat_rate) }}" required>
                </div>

                <div class="field">
                    <label for="valid_until">Berlaku Sampai</label>
                    <input id="valid_until" name="valid_until" type="date"
                        min="{{ now()->toDateString() }}"
                        value="{{ old('valid_until', optional($quotation->valid_until)->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="field">
                <label for="notes">Catatan</label>
                <textarea id="notes" name="notes">{{ old('notes', $quotation->notes) }}</textarea>
            </div>

            <div class="buttons">
                <button class="button" type="submit">Simpan Perubahan</button>
                <a class="button secondary" href="{{ route('procurement.dashboard') }}">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
