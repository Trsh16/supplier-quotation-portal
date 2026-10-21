<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Procurement Dashboard | SupplyFlow</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f5f7fb;
            color: #172033;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: 230px;
            flex-shrink: 0;
            padding: 28px 18px;
            background: #fff;
            border-right: 1px solid #e8ebf2;
        }
        .brand { font-size: 21px; font-weight: 800; margin-bottom: 42px; }
        .brand span { color: #4f46e5; }
        .nav-label {
            margin: 24px 10px 10px;
            color: #98a1b3;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .nav-item {
            display: block;
            margin: 5px 0;
            padding: 12px;
            border-radius: 9px;
            color: #68738a;
            font-size: 14px;
            text-decoration: none;
        }
        .nav-item.active { background: #eeedff; color: #4f46e5; font-weight: 700; }
        main { flex: 1; min-width: 0; }
        header {
            height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 34px;
            background: white;
            border-bottom: 1px solid #e8ebf2;
        }
        .header-title { font-weight: 700; }
        .avatar {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #eeedff;
            color: #4f46e5;
            font-weight: 800;
        }
        .content { max-width: 1500px; margin: auto; padding: 32px; }
        .page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 26px;
        }
        h1 { margin: 0 0 8px; font-size: 27px; letter-spacing: -.5px; }
        .muted { color: #8993a7; font-size: 13px; line-height: 1.6; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .button {
            display: inline-block;
            border: 0;
            border-radius: 9px;
            padding: 11px 15px;
            background: #4f46e5;
            color: white;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }
        .button.secondary { background: white; color: #475066; border: 1px solid #dfe3ed; }
        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat, .panel {
            background: white;
            border: 1px solid #e9ecf3;
            border-radius: 14px;
        }
        .stat { padding: 21px; }
        .stat-label { color: #8993a7; font-size: 13px; margin-bottom: 12px; }
        .stat-value { font-size: 29px; font-weight: 800; }
        .stat-note { margin-top: 8px; color: #8993a7; font-size: 12px; }
        .panel { overflow: hidden; }
        .panel-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            padding: 22px 24px;
            border-bottom: 1px solid #edf0f5;
        }
        .panel-heading h2 { margin: 0 0 5px; font-size: 16px; }
        .search-form { display: flex; gap: 8px; }
        .search {
            width: 280px;
            max-width: 100%;
            padding: 11px 13px;
            border: 1px solid #dfe3ed;
            border-radius: 9px;
            font: inherit;
            font-size: 13px;
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th {
            padding: 14px 18px;
            background: #fafbfe;
            color: #8993a7;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .5px;
            white-space: nowrap;
        }
        td {
            padding: 17px 18px;
            border-top: 1px solid #f0f2f7;
            font-size: 13px;
            vertical-align: top;
        }
        .company { font-weight: 700; margin-bottom: 5px; }
        .subtext { color: #8993a7; font-size: 11px; line-height: 1.6; overflow-wrap: anywhere; }
        .price { font-weight: 750; white-space: nowrap; }
        .status {
            display: inline-block;
            padding: 6px 9px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #475569;
            font-size: 10px;
            font-weight: 750;
            white-space: nowrap;
        }
        .status.submitted { background: #dbeafe; color: #1d4ed8; }
        .status.under_review { background: #fef3c7; color: #92400e; }
        .status.accepted { background: #dcfce7; color: #166534; }
        .status.rejected, .status.expired { background: #fee2e2; color: #991b1b; }
        .empty { padding: 48px 20px; text-align: center; color: #8993a7; }
        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 17px 24px;
            border-top: 1px solid #edf0f5;
            color: #8993a7;
            font-size: 12px;
        }
        .pagination { display: flex; gap: 8px; }
        .pagination a {
            padding: 8px 12px;
            border: 1px solid #dfe3ed;
            border-radius: 8px;
            color: #475066;
            text-decoration: none;
        }
        .pagination a.disabled { opacity: .4; pointer-events: none; }
        .token { max-width: 150px; overflow-wrap: anywhere; }
        @media (max-width: 1100px) {
            .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .content { padding: 24px 18px; }
            header { padding: 0 20px; }
        }
        @media (max-width: 650px) {
            .sidebar { display: none; }
            .content { padding: 20px 12px; }
            header { height: 62px; padding: 0 15px; }
            .stats { grid-template-columns: 1fr 1fr; gap: 10px; }
            .stat { padding: 15px; }
            .stat-value { font-size: 24px; }
            .panel-heading { padding: 17px; }
            .search-form { width: 100%; }
            .search { width: 100%; }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">Supply<span>Flow</span></div>

        <div class="nav-label">PROCUREMENT</div>
        <a class="nav-item active" href="{{ route('procurement.dashboard') }}">▦ &nbsp; Dashboard</a>

    </aside>

    <main>
        <header>
            <div class="header-title">Procurement Dashboard</div>
            <div class="avatar">P</div>
        </header>

        <div class="content">
            <div class="page-heading">
                <div>
                    <h1>Penawaran Supplier</h1>
                    <div class="muted">Pantau penawaran yang masuk dan tinjau harga dari supplier.</div>
                </div>
                <div class="actions">
                    <a class="button secondary" href="{{ route('supplier-qr') }}">Lihat QR Supplier</a>
                    <a class="button" href="{{ route('supplier-offer.create') }}">＋ Form Penawaran</a>
                </div>
            </div>

            <section class="stats">
                <div class="stat">
                    <div class="stat-label">Total Penawaran</div>
                    <div class="stat-value">{{ number_format($stats['total']) }}</div>
                    <div class="stat-note">Semua status</div>
                </div>
                <div class="stat">
                    <div class="stat-label">Penawaran Masuk</div>
                    <div class="stat-value">{{ number_format($stats['submitted']) }}</div>
                    <div class="stat-note">Menunggu ditinjau</div>
                </div>
                <div class="stat">
                    <div class="stat-label">Sedang Ditinjau</div>
                    <div class="stat-value">{{ number_format($stats['under_review']) }}</div>
                    <div class="stat-note">Dalam proses procurement</div>
                </div>
                <div class="stat">
                    <div class="stat-label">Diterima</div>
                    <div class="stat-value">{{ number_format($stats['accepted']) }}</div>
                    <div class="stat-note">Penawaran berstatus accepted</div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-heading">
                    <div>
                        <h2>Daftar Penawaran Masuk</h2>
                        <div class="muted">Data diambil langsung dari database penawaran supplier.</div>
                    </div>
                    <form class="search-form" method="GET" action="{{ route('procurement.dashboard') }}">
                        <input class="search" type="search" name="search"
                            value="{{ $search }}"
                            placeholder="Cari perusahaan, barang, email...">
                        <button class="button" type="submit">Cari</button>
                    </form>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Barang Ditawarkan</th>
                            <th>Harga / Satuan</th>
                            <th>PPN</th>
                            <th>Tanggal Masuk</th>
                            <th>Status</th>
                            <th>Referensi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($quotations as $quotation)
                            @php
                                $item = $quotation->items->first();
                                $status = $quotation->status;
                            @endphp
                            <tr>
                                <td>
                                    <div class="company">
                                        {{ $quotation->supplier->company_name ?? 'Supplier tidak diketahui' }}
                                    </div>
                                    <div class="subtext">
                                        {{ $quotation->supplier->email ?? 'Email tidak tersedia' }}
                                    </div>
                                    <div class="subtext">
                                        Sales: {{ $quotation->sales_name ?: '—' }}
                                    </div>
                                </td>
                                <td>
                                    @if ($item)
                                        <div class="company">{{ $item->supplier_item_name }}</div>
                                        <div class="subtext">
                                            Kode: {{ $item->supplier_item_code ?: '—' }}
                                        </div>
                                        <div class="subtext">
                                            Satuan: {{ $item->supplier_unit_text }}
                                        </div>
                                        @if ($quotation->items->count() > 1)
                                            <div class="subtext">
                                                + {{ $quotation->items->count() - 1 }} barang lainnya
                                            </div>
                                        @endif
                                    @else
                                        <span class="subtext">Belum ada rincian barang</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item)
                                        <div class="price">
                                            Rp {{ number_format((float) $item->quoted_price, 0, ',', '.') }}
                                        </div>
                                        <div class="subtext">per {{ $item->supplier_unit_text }}</div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    {{ $quotation->vat_type === 'included' ? 'Termasuk' : 'Belum termasuk' }}
                                    <div class="subtext">{{ number_format((float) $quotation->vat_rate, 2, ',', '.') }}%</div>
                                </td>
                                <td>
                                    {{ $quotation->submitted_at ? $quotation->submitted_at->format('d/m/Y H:i') : $quotation->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <span class="status {{ $status }}">
                                        {{ ucwords(str_replace('_', ' ', $status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="subtext token">{{ $quotation->public_token }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty">
                                    Belum ada penawaran yang cocok.
                                    @if ($search)
                                        Coba kata kunci lain.
                                    @else
                                        Penawaran baru dari supplier akan muncul di sini.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div>
                        @if ($quotations->total() > 0)
                            Menampilkan {{ $quotations->firstItem() }}–{{ $quotations->lastItem() }}
                            dari {{ $quotations->total() }} penawaran
                        @else
                            Tidak ada data untuk ditampilkan
                        @endif
                    </div>
                    <div class="pagination">
                        <a class="{{ $quotations->onFirstPage() ? 'disabled' : '' }}"
                           href="{{ $quotations->previousPageUrl() ?? '#' }}">Sebelumnya</a>
                        <a class="{{ $quotations->hasMorePages() ? '' : 'disabled' }}"
                           href="{{ $quotations->nextPageUrl() ?? '#' }}">Berikutnya</a>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>
</body>
</html>
