<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Supplier Management</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f5f7fb;
            color: #172033;
        }

        .layout { display: flex; min-height: 100vh; }

        .sidebar {
            width: 230px;
            padding: 28px 18px;
            background: #fff;
            border-right: 1px solid #e8ebf2;
        }

        .brand { font-size: 20px; font-weight: 750; margin-bottom: 42px; }
        .brand span { color: #4f46e5; }

        .nav-label {
            font-size: 11px;
            color: #98a1b3;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 24px 10px 10px;
        }

        .nav-item {
            display: block;
            padding: 12px;
            margin: 5px 0;
            border-radius: 9px;
            color: #68738a;
            font-size: 14px;
        }

        .nav-item.active {
            background: #eeedff;
            color: #4f46e5;
            font-weight: 650;
        }

        main { flex: 1; min-width: 0; }

        header {
            height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 36px;
            background: #fff;
            border-bottom: 1px solid #e8ebf2;
        }

        .header-title { font-weight: 650; }
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #eeedff;
            color: #4f46e5;
            font-weight: 700;
        }

        .content { padding: 34px 36px; max-width: 1400px; margin: auto; }
        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }

        h1 { margin: 0 0 8px; font-size: 27px; letter-spacing: -0.6px; }
        .muted { color: #8993a7; font-size: 14px; }

        button {
            border: 0;
            cursor: pointer;
            border-radius: 9px;
            padding: 11px 16px;
            font: inherit;
            font-size: 13px;
            font-weight: 650;
        }

        .primary { background: #4f46e5; color: #fff; }
        .primary:hover { background: #4338ca; }
        .secondary { background: #f0f1f6; color: #475066; }
        button:disabled { opacity: .5; cursor: not-allowed; }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .stat, .panel {
            background: #fff;
            border: 1px solid #e9ecf3;
            border-radius: 14px;
        }

        .stat { padding: 22px; }
        .stat-label { color: #8993a7; font-size: 13px; margin-bottom: 12px; }
        .stat-value { font-size: 27px; font-weight: 750; }

        .panel { overflow: hidden; }
        .panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 24px;
            border-bottom: 1px solid #edf0f5;
        }

        .panel-heading h2 { font-size: 16px; margin: 0 0 5px; }
        .search {
            width: 280px;
            max-width: 100%;
            padding: 11px 13px;
            border: 1px solid #dfe3ed;
            border-radius: 9px;
            font: inherit;
            font-size: 13px;
            outline: none;
        }

        .search:focus, input:focus, textarea:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 3px #4f46e51a;
        }

        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th {
            padding: 14px 24px;
            background: #fafbfe;
            color: #8993a7;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 700;
        }

        td {
            padding: 17px 24px;
            border-top: 1px solid #f0f2f7;
            font-size: 13px;
            vertical-align: middle;
        }

        .company { font-weight: 650; margin-bottom: 5px; }
        .subtext { color: #8993a7; font-size: 12px; }
        .status {
            display: inline-block;
            border-radius: 20px;
            padding: 6px 10px;
            background: #e8f8ef;
            color: #16834b;
            font-size: 11px;
            font-weight: 700;
        }

        .empty { padding: 48px 20px; text-align: center; color: #8993a7; }
        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 17px 24px;
            border-top: 1px solid #edf0f5;
            font-size: 12px;
            color: #8993a7;
        }

        .pagination { display: flex; gap: 8px; }
        .pagination button { padding: 8px 12px; }

        .notice {
            padding: 12px 15px;
            border-radius: 9px;
            margin-bottom: 18px;
            font-size: 13px;
        }

        .notice.success { background: #e8f8ef; color: #16834b; }
        .notice.error { background: #fff0f0; color: #bd3333; }
        [hidden] { display: none !important; }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: #11182780;
            display: grid;
            place-items: center;
            padding: 20px;
            z-index: 10;
        }

        .modal {
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            background: #fff;
            border-radius: 16px;
            padding: 26px;
            box-shadow: 0 24px 80px #0002;
        }

        .modal-top {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 24px;
        }

        .modal-top h2 { margin: 0 0 7px; font-size: 20px; }
        .close {
            font-size: 22px;
            background: transparent;
            color: #7c8598;
            padding: 0 5px;
        }

        .field { margin-bottom: 17px; }
        label { display: block; font-size: 13px; font-weight: 650; margin-bottom: 8px; }
        label span { color: #8993a7; font-weight: 400; }

        input, textarea {
            width: 100%;
            border: 1px solid #dfe3ed;
            border-radius: 9px;
            padding: 12px;
            font: inherit;
            font-size: 13px;
            outline: none;
        }

        textarea { resize: vertical; min-height: 85px; }
        .field-error { color: #bd3333; font-size: 12px; margin-top: 6px; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px; }

        @media (max-width: 900px) {
            .sidebar { width: 190px; }
            .content { padding: 24px 18px; }
            header { padding: 0 20px; }
            .stats { gap: 10px; }
            .stat { padding: 16px; }
            .panel-heading { align-items: stretch; flex-direction: column; }
            .search { width: 100%; }
        }

        @media (max-width: 600px) {
            .sidebar { display: none; }
            .content { padding: 22px 12px; }
            header { height: 62px; padding: 0 15px; }
            .page-heading { align-items: flex-start; flex-direction: column; }
            .stats { grid-template-columns: 1fr; }
            .stat-value { font-size: 23px; }
            .panel-heading { padding: 17px; }
            th, td { padding: 13px 15px; }
            .table-footer { padding: 15px; }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">Supply<span>Flow</span></div>
        <div class="nav-label">WORKSPACE</div>
        <div class="nav-item active">▦ &nbsp; Suppliers</div>
        <div class="nav-item">▤ &nbsp; Standard Goods</div>
        <div class="nav-item">⇄ &nbsp; Quotations</div>
        <div class="nav-label">SYSTEM</div>
        <div class="nav-item">⚙ &nbsp; Settings</div>
    </aside>

    <main>
        <header>
            <div class="header-title">Supplier Portal</div>
            <div class="avatar">A</div>
        </header>

        <div class="content">
            <div class="page-heading">
                <div>
                    <h1>Suppliers</h1>
                    <div class="muted">Manage supplier information and company contacts.</div>
                </div>
                <button class="primary" id="openModal">+ Add Supplier</button>
            </div>

            <div id="notice" class="notice" hidden role="status"></div>

            <div class="stats">
                <div class="stat">
                    <div class="stat-label">Total Suppliers</div>
                    <div class="stat-value" id="totalSuppliers">—</div>
                </div>
                <div class="stat">
                    <div class="stat-label">Current Page</div>
                    <div class="stat-value" id="currentPage">1</div>
                </div>
                <div class="stat">
                    <div class="stat-label">Records Per Page</div>
                    <div class="stat-value">10</div>
                </div>
            </div>

            <section class="panel">
                <div class="panel-heading">
                    <div>
                        <h2>Supplier Directory</h2>
                        <div class="muted">Your registered supplier companies</div>
                    </div>
                    <input class="search" id="searchInput" type="search"
                           placeholder="Search company or email..." aria-label="Search suppliers">
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody id="supplierRows">
                        <tr><td colspan="5" class="empty">Loading suppliers...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div id="paginationInfo">Loading...</div>
                    <div class="pagination">
                        <button class="secondary" id="previousPage" disabled>Previous</button>
                        <button class="secondary" id="nextPage" disabled>Next</button>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>

<div class="modal-backdrop" id="supplierModal" hidden>
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-top">
            <div>
                <h2 id="modalTitle">Add Supplier</h2>
                <div class="muted">Enter the supplier's company information.</div>
            </div>
            <button class="close" id="closeModal" aria-label="Close dialog">&times;</button>
        </div>

        <form id="supplierForm">
            <div class="field">
                <label for="company_name">Company Name *</label>
                <input id="company_name" name="company_name" maxlength="255" required>
                <div class="field-error" data-error="company_name"></div>
            </div>

            <div class="field">
                <label for="email">Email <span>(optional)</span></label>
                <input id="email" name="email" type="email" maxlength="255">
                <div class="field-error" data-error="email"></div>
            </div>

            <div class="field">
                <label for="phone">Phone <span>(optional)</span></label>
                <input id="phone" name="phone" maxlength="50">
                <div class="field-error" data-error="phone"></div>
            </div>

            <div class="field">
                <label for="address">Address <span>(optional)</span></label>
                <textarea id="address" name="address" maxlength="1000"></textarea>
                <div class="field-error" data-error="address"></div>
            </div>

            <div id="formError" class="notice error" hidden role="alert"></div>

            <div class="modal-actions">
                <button type="button" class="secondary" id="cancelModal">Cancel</button>
                <button type="submit" class="primary" id="saveSupplier">Save Supplier</button>
            </div>
        </form>
    </section>
</div>

<script>
    const rows = document.getElementById('supplierRows');
    const searchInput = document.getElementById('searchInput');
    const notice = document.getElementById('notice');
    const modal = document.getElementById('supplierModal');
    const form = document.getElementById('supplierForm');
    const saveButton = document.getElementById('saveSupplier');

    let currentPage = 1;
    let lastPage = 1;
    let searchTimer;

    function showNotice(message, type = 'success') {
        notice.textContent = message;
        notice.className = `notice ${type}`;
        notice.hidden = false;
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, character => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[character]);
    }

    async function loadSuppliers(page = 1) {
        currentPage = page;
        rows.innerHTML = '<tr><td colspan="5" class="empty">Loading suppliers...</td></tr>';

        const params = new URLSearchParams({ page: String(page) });
        const search = searchInput.value.trim();
        if (search) params.set('search', search);

        try {
            const response = await fetch(`/api/suppliers?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('Failed to load suppliers.');

            const result = await response.json();
            const suppliers = result.data ?? [];

            document.getElementById('totalSuppliers').textContent = result.total ?? suppliers.length;
            document.getElementById('currentPage').textContent = result.current_page ?? page;
            currentPage = result.current_page ?? page;
            lastPage = result.last_page ?? 1;

            document.getElementById('paginationInfo').textContent =
                suppliers.length
                    ? `Showing ${result.from}–${result.to} of ${result.total} suppliers`
                    : 'No suppliers found';

            document.getElementById('previousPage').disabled = currentPage <= 1;
            document.getElementById('nextPage').disabled = currentPage >= lastPage;

            if (!suppliers.length) {
                rows.innerHTML = '<tr><td colspan="5" class="empty">No suppliers found. Add a supplier to get started.</td></tr>';
                return;
            }

            rows.innerHTML = suppliers.map(supplier => `
                <tr>
                    <td>
                        <div class="company">${escapeHtml(supplier.company_name)}</div>
                        <div class="subtext">ID: ${escapeHtml(supplier.id)}</div>
                    </td>
                    <td>${escapeHtml(supplier.email || '—')}</td>
                    <td>${escapeHtml(supplier.phone || '—')}</td>
                    <td>${escapeHtml(supplier.address || '—')}</td>
                    <td><span class="status">Registered</span></td>
                </tr>
            `).join('');
        } catch (error) {
            rows.innerHTML = '<tr><td colspan="5" class="empty">Could not load suppliers. Please try again.</td></tr>';
            document.getElementById('paginationInfo').textContent = 'Unable to load data';
            showNotice(error.message, 'error');
        }
    }

    function openModal() {
        form.reset();
        clearErrors();
        document.getElementById('formError').hidden = true;
        modal.hidden = false;
        document.getElementById('company_name').focus();
    }

    function closeModal() {
        modal.hidden = true;
    }

    function clearErrors() {
        document.querySelectorAll('[data-error]').forEach(element => element.textContent = '');
    }

    function showFieldErrors(errors) {
        Object.entries(errors || {}).forEach(([field, messages]) => {
            const target = document.querySelector(`[data-error="${field}"]`);
            if (target) target.textContent = messages[0] || 'Invalid value';
        });
    }

    document.getElementById('openModal').addEventListener('click', openModal);
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('cancelModal').addEventListener('click', closeModal);

    modal.addEventListener('click', event => {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal.hidden) closeModal();
    });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadSuppliers(1), 300);
    });

    document.getElementById('previousPage').addEventListener('click', () => {
        if (currentPage > 1) loadSuppliers(currentPage - 1);
    });

    document.getElementById('nextPage').addEventListener('click', () => {
        if (currentPage < lastPage) loadSuppliers(currentPage + 1);
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        clearErrors();

        const formError = document.getElementById('formError');
        formError.hidden = true;
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';

        const payload = Object.fromEntries(new FormData(form).entries());

        try {
            const response = await fetch('/api/suppliers', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                if (response.status === 422 && result.errors) {
                    showFieldErrors(result.errors);
                    formError.textContent = result.message || 'Please check the form fields.';
                } else {
                    formError.textContent = result.message || 'Unable to save supplier.';
                }
                formError.hidden = false;
                return;
            }

            closeModal();
            searchInput.value = '';
            showNotice(result.message || 'Supplier added successfully.');
            await loadSuppliers(1);
        } catch (error) {
            formError.textContent = 'Unable to connect to the server. Please try again.';
            formError.hidden = false;
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = 'Save Supplier';
        }
    });

    loadSuppliers();
</script>
</body>
</html>
