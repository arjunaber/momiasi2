@extends('layouts.app')
@section('title', 'Daftar Transaksi')
@section('page-title', 'Daftar Transaksi')
@section('page-subtitle', 'Kelola semua transaksi Momiasi & Little Mommies')

@section('topbar-actions')
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('transactions.create') }}" class="btn-primary-momi">
            <i class="bi bi-plus-lg me-1"></i>Tambah
        </a>
        <a href="{{ route('transactions.import') }}" class="btn-ghost-momi">
            <i class="bi bi-upload me-1"></i>Import CSV
        </a>
        <a href="{{ route('transactions.batch-cost') }}" class="btn-ghost-momi">
            <i class="bi bi-pencil-square me-1"></i>Update Biaya
        </a>
        <a href="{{ route('transactions.edit-weekly') }}" class="btn-ghost-momi">
            <i class="bi bi-calendar-week me-1"></i>Edit Mingguan
        </a>
        <a href="{{ route('transactions.export', request()->query()) }}" class="btn-ghost-momi">
            <i class="bi bi-download me-1"></i>Export
        </a>
    </div>
@endsection

@section('content')
    {{-- SUMMARY CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card-light" style="border-left:4px solid var(--clr-teal);">
                <div class="card-body-light" style="padding:14px 18px;">
                    <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Total Transaksi
                    </div>
                    <div style="font-size:24px;font-weight:700;color:var(--clr-teal);">
                        {{ number_format($summary['total_transactions'] ?? 0) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-light" style="border-left:4px solid var(--green);">
                <div class="card-body-light" style="padding:14px 18px;">
                    <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Total Revenue
                    </div>
                    <div style="font-size:24px;font-weight:700;color:var(--green);">
                        Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-light" style="border-left:4px solid var(--clr-magenta);">
                <div class="card-body-light" style="padding:14px 18px;">
                    <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Total Profit</div>
                    <div
                        style="font-size:24px;font-weight:700;color:{{ ($summary['total_profit'] ?? 0) >= 0 ? 'var(--green)' : 'var(--red)' }};">
                        Rp {{ number_format($summary['total_profit'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-light" style="border-left:4px solid #888;">
                <div class="card-body-light" style="padding:14px 18px;">
                    <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Rata-rata Margin
                    </div>
                    <div
                        style="font-size:24px;font-weight:700;color:{{ ($summary['avg_profit_margin'] ?? 0) >= 0 ? 'var(--green)' : 'var(--red)' }};">
                        {{ number_format($summary['avg_profit_margin'] ?? 0, 1) }}%
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="card-light mb-4">
        <div class="card-header-light">
            <h3 class="card-title-light"><i class="bi bi-funnel me-2"></i>Filter Transaksi</h3>
            @if (request()->hasAny(['marketplace_id', 'period', 'status', 'search', 'date_from', 'date_to']))
                <a href="{{ route('transactions.index') }}" class="btn-ghost-momi"
                    style="font-size:12px;padding:5px 10px;">
                    <i class="bi bi-x me-1"></i>Reset Filter
                </a>
            @endif
        </div>
        <div class="card-body-light">
            <form method="GET" action="{{ route('transactions.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label-light">Marketplace</label>
                        <select name="marketplace_id" class="form-select-light" onchange="this.form.submit()">
                            <option value="">Semua Platform</option>
                            @foreach ($marketplaces as $mp)
                                <option value="{{ $mp->id }}"
                                    {{ request('marketplace_id') == $mp->id ? 'selected' : '' }}>
                                    {{ $mp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-light">Periode</label>
                        <select name="period" class="form-select-light" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            @foreach ($periods as $p)
                                <option value="{{ $p }}" {{ request('period') == $p ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($p . '-01')->format('M Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-light">Status</label>
                        <select name="status" class="form-select-light" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai
                            </option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan
                            </option>
                            <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Retur</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-light">Dari</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="form-control-light" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-light">Sampai</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control-light"
                            onchange="this.form.submit()">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn-primary-momi w-100" style="justify-content:center;padding:8px;">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label-light">Cari</label>
                        <div class="d-flex gap-2">
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control-light" placeholder="Order ID / Nama Produk / SKU / Kota..."
                                id="searchInput">
                            <button type="submit" class="btn-primary-momi" style="padding:8px 16px;flex-shrink:0;">
                                <i class="bi bi-search"></i>
                            </button>
                            @if (request('search'))
                                <a href="{{ route('transactions.index', request()->except('search')) }}"
                                    class="btn-ghost-momi" style="padding:8px 12px;flex-shrink:0;">
                                    <i class="bi bi-x"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <span style="font-size:12px;color:#888;">
                            <i class="bi bi-info-circle me-1"></i>
                            Sorting:
                            <strong>{{ ucfirst(str_replace('_', ' ', request('sort', 'transaction_date'))) }}</strong>
                            {{ request('dir', 'desc') === 'asc' ? '⬆' : '⬇' }}
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- BULK ACTION --}}
    @if ($transactions->count() > 0)
        <div class="mb-3" id="bulkActions" style="display:none;">
            <div class="card-light" style="background:#F0FAFA;border:1px solid var(--border);">
                <div class="card-body-light"
                    style="padding:10px 16px;display:flex;align-items:center;gap:15px;flex-wrap:wrap;">
                    <span style="font-size:13px;font-weight:600;">
                        <span id="selectedCount">0</span> transaksi dipilih
                    </span>
                    <button type="button" class="btn-danger-soft" id="bulkDeleteBtn"
                        style="padding:6px 14px;font-size:13px;">
                        <i class="bi bi-trash3 me-1"></i>Hapus Terpilih
                    </button>
                    <button type="button" class="btn-ghost-momi" id="clearSelection"
                        style="padding:6px 14px;font-size:13px;">
                        <i class="bi bi-x me-1"></i>Batal Pilih
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- TABLE --}}
    <div class="card-light">
        <div class="card-header-light">
            <h3 class="card-title-light">
                <i class="bi bi-table me-2"></i>Data Transaksi
                <span
                    style="background:var(--accent-soft);color:var(--clr-teal);font-size:11px;padding:2px 9px;border-radius:20px;margin-left:8px;font-weight:600;">
                    {{ $transactions->total() }}
                </span>
            </h3>
            <div class="d-flex gap-2">
                @if ($transactions->count() > 0)
                    <button type="button" class="btn-ghost-momi" id="toggleSelectAll"
                        style="font-size:12px;padding:5px 10px;">
                        <i class="bi bi-check2-square me-1"></i>Pilih Semua
                    </button>
                @endif
                <span style="font-size:11px;color:#888;">
                    {{ $transactions->firstItem() ?? 0 }}-{{ $transactions->lastItem() ?? 0 }} dari
                    {{ $transactions->total() }}
                </span>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="table-custom" id="transactionTable">
                <thead>
                    <tr>
                        <th style="width:32px;text-align:center;">
                            <input type="checkbox" id="selectAllCheckbox" style="cursor:pointer;">
                        </th>
                        @php
                            $cs = request('sort', 'transaction_date');
                            $cd = request('dir', 'desc');
                            $cols = [
                                ['transaction_date', 'Tanggal', false],
                                ['order_id', 'Order ID', false],
                                [null, 'Platform', false],
                                [null, 'Produk', false],
                                ['quantity', 'Qty', true],
                                ['revenue', 'Revenue', true],
                                ['advertising_spend', 'Ad Spend', true],
                                ['profit', 'Profit', true],
                                ['profit_margin', 'Margin', true],
                                [null, 'Status', false],
                                [null, 'Aksi', false],
                            ];
                        @endphp
                        @foreach ($cols as [$field, $label, $alignRight])
                            @if ($field)
                                @php
                                    $active = $cs === $field;
                                    $newDir = $active && $cd === 'asc' ? 'desc' : 'asc';
                                    $params = array_merge(request()->except(['sort', 'dir', 'page']), [
                                        'sort' => $field,
                                        'dir' => $newDir,
                                    ]);
                                @endphp
                                <th class="sortable {{ $alignRight ? 'text-end' : '' }}">
                                    <a href="{{ route('transactions.index', $params) }}"
                                        style="color:inherit;text-decoration:none;display:flex;align-items:center;gap:4px;{{ $alignRight ? 'justify-content:flex-end' : '' }}">
                                        {{ $label }}
                                        <i class="bi {{ $active ? ($cd === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up' }}"
                                            style="font-size:10px;color:{{ $active ? 'var(--clr-magenta)' : '#ccc' }};"></i>
                                    </a>
                                </th>
                            @else
                                <th class="{{ $label === 'Aksi' ? 'text-center' : '' }}">{{ $label }}</th>
                            @endif
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" class="row-checkbox" value="{{ $tx->id }}"
                                    style="cursor:pointer;">
                            </td>
                            <td style="white-space:nowrap;">
                                <div style="font-size:13px;font-weight:500;color:#333;">
                                    {{ $tx->transaction_date->format('d M Y') }}
                                </div>
                                <div style="font-size:10px;color:#aaa;">{{ $tx->period_month }}</div>
                            </td>
                            <td>
                                <span style="font-size:11px;font-family:monospace;color:#888;">
                                    {{ $tx->order_id ?? '—' }}
                                </span>
                                @if ($tx->csvImport)
                                    <span style="font-size:9px;color:#aaa;display:block;"
                                        title="Dari import CSV #{{ $tx->csvImport->id }}">
                                        <i class="bi bi-upload"></i> CSV
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-{{ $tx->marketplace->slug ?? 'default' }}"
                                    style="font-size:11px;padding:3px 9px;border-radius:20px;font-weight:600;background:{{ $tx->marketplace->color ?? '#888' }}15;color:{{ $tx->marketplace->color ?? '#888' }};border:1px solid {{ $tx->marketplace->color ?? '#888' }}30;">
                                    {{ $tx->marketplace->name ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <div style="font-size:12px;font-weight:500;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#333;"
                                    title="{{ $tx->product->name ?? '—' }}">
                                    {{ $tx->product->name ?? '—' }}
                                </div>
                                <div style="font-size:10px;color:#aaa;font-family:monospace;">
                                    {{ $tx->product->sku ?? '—' }}
                                </div>
                            </td>
                            <td class="text-end" style="font-weight:600;color:#333;">{{ $tx->quantity }}</td>
                            <td class="text-end" style="font-weight:600;color:var(--clr-teal);">
                                Rp {{ number_format($tx->revenue, 0, ',', '.') }}
                            </td>
                            <td class="text-end" style="color:var(--clr-magenta);">
                                Rp {{ number_format($tx->advertising_spend, 0, ',', '.') }}
                            </td>
                            <td class="text-end"
                                style="font-weight:700;color:{{ $tx->profit >= 0 ? 'var(--green)' : 'var(--red)' }};">
                                Rp {{ number_format($tx->profit, 0, ',', '.') }}
                            </td>
                            <td class="text-end"
                                style="font-weight:600;color:{{ $tx->profit_margin >= 0 ? 'var(--green)' : 'var(--red)' }};">
                                {{ number_format($tx->profit_margin, 1) }}%
                            </td>
                            <td>
                                @php
                                    $sm = [
                                        'completed' => ['Selesai', 'badge-completed'],
                                        'cancelled' => ['Dibatalkan', 'badge-cancelled'],
                                        'returned' => ['Retur', 'badge-returned'],
                                        'pending' => ['Pending', 'badge-pending'],
                                    ];
                                    [$sl, $sc] = $sm[$tx->status] ?? ['—', ''];
                                @endphp
                                <span class="{{ $sc }}"
                                    style="font-size:11px;padding:3px 9px;border-radius:20px;font-weight:600;display:inline-block;">
                                    {{ $sl }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn-edit-soft btn-edit-tx"
                                        data-id="{{ $tx->id }}" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn-danger-soft btn-delete-tx"
                                        data-id="{{ $tx->id }}"
                                        data-label="{{ $tx->order_id ?? 'ID #' . $tx->id }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5" style="color:#aaa;">
                                <i class="bi bi-inbox"
                                    style="font-size:30px;display:block;margin-bottom:8px;color:var(--clr-mint);"></i>
                                Belum ada transaksi.
                                <a href="{{ route('transactions.create') }}" style="color:var(--clr-teal);">Tambah</a>
                                atau
                                <a href="{{ route('transactions.import') }}" style="color:var(--clr-teal);">Import
                                    CSV</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transactions->hasPages())
            <div class="d-flex align-items-center justify-content-between px-4 py-3 flex-wrap gap-2"
                style="border-top:1px solid var(--border);">
                <div style="font-size:12px;color:#888;">
                    Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} dari
                    {{ $transactions->total() }} data
                </div>
                {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- MODAL EDIT --}}
    <div id="editModal"
        style="display:none;position:fixed;inset:0;z-index:1050;align-items:center;justify-content:center;">
        <div id="editBackdrop" style="position:absolute;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);">
        </div>
        <div
            style="position:relative;background:white;border-radius:16px;width:min(780px,95vw);max-height:90vh;overflow-y:auto;z-index:1;box-shadow:0 20px 60px rgba(0,139,139,0.2);">
            <div
                style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:white;z-index:2;border-radius:16px 16px 0 0;">
                <div>
                    <h4 style="font-size:17px;font-weight:700;color:var(--clr-teal);margin:0;">Edit Transaksi</h4>
                    <div id="editSubtitle" style="font-size:11px;color:#888;margin-top:1px;">—</div>
                </div>
                <button type="button" id="closeModal"
                    style="background:#F5F5F5;border:1px solid #ddd;border-radius:8px;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#666;transition:all .2s;"
                    onmouseover="this.style.background='#eee'" onmouseout="this.style.background='#F5F5F5'">
                    <i class="bi bi-x-lg" style="font-size:13px;"></i>
                </button>
            </div>
            <div id="modalLoading" style="padding:50px;text-align:center;color:#aaa;">
                <div
                    style="width:30px;height:30px;border:2px solid #ddd;border-top-color:var(--clr-teal);border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 12px;">
                </div>
                Memuat data...
            </div>
            <form id="editForm" style="display:none;">
                @csrf
                <input type="hidden" id="edit_id" name="id">
                <div style="padding:20px 22px;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-light">Marketplace *</label>
                            <select name="marketplace_id" id="edit_marketplace_id" class="form-select-light" required>
                                @foreach ($marketplaces as $mp)
                                    <option value="{{ $mp->id }}">{{ $mp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-light">Produk *</label>
                            <select name="product_id" id="edit_product_id" class="form-select-light" required>
                                @foreach ($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-light">Tanggal *</label>
                            <input type="date" name="transaction_date" id="edit_transaction_date"
                                class="form-control-light" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-light">Order ID</label>
                            <input type="text" name="order_id" id="edit_order_id" class="form-control-light"
                                placeholder="Opsional">
                        </div>
                        <div class="col-12">
                            <div
                                style="border-top:1px solid var(--border);padding-top:8px;font-size:10px;color:var(--clr-teal);text-transform:uppercase;letter-spacing:1px;font-weight:700;">
                                Data Keuangan</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-light">Qty *</label>
                            <input type="number" name="quantity" id="edit_quantity" class="form-control-light"
                                min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-light">Harga Satuan (Rp) *</label>
                            <input type="number" name="unit_price" id="edit_unit_price" class="form-control-light"
                                min="0" step="100" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-light">Revenue (Rp) *</label>
                            <input type="number" name="revenue" id="edit_revenue" class="form-control-light"
                                min="0" step="100" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-light">COGS (Rp)</label>
                            <input type="number" name="cogs" id="edit_cogs" class="form-control-light"
                                min="0" step="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-light">Ad Spend (Rp)</label>
                            <input type="number" name="advertising_spend" id="edit_advertising_spend"
                                class="form-control-light" min="0" step="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-light">Komisi Platform (Rp)</label>
                            <input type="number" name="platform_fee" id="edit_platform_fee" class="form-control-light"
                                min="0" step="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-light">Subsidi Ongkir (Rp)</label>
                            <input type="number" name="shipping_subsidy" id="edit_shipping_subsidy"
                                class="form-control-light" min="0" step="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-light">Diskon (Rp)</label>
                            <input type="number" name="discount" id="edit_discount" class="form-control-light"
                                min="0" step="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-light">Status *</label>
                            <select name="status" id="edit_status" class="form-select-light" required>
                                <option value="completed">Selesai</option>
                                <option value="pending">Pending</option>
                                <option value="cancelled">Dibatalkan</option>
                                <option value="returned">Retur</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-light">Kota Pembeli</label>
                            <input type="text" name="customer_city" id="edit_customer_city"
                                class="form-control-light" placeholder="Jakarta">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-light">Catatan</label>
                            <input type="text" name="notes" id="edit_notes" class="form-control-light"
                                placeholder="Catatan tambahan">
                        </div>
                        {{-- Profit preview --}}
                        <div class="col-12">
                            <div
                                style="background:#F0FAFA;border:1px solid var(--border);border-radius:10px;padding:12px;display:flex;gap:20px;flex-wrap:wrap;align-items:center;">
                                <div>
                                    <div style="font-size:10px;color:#888;">Revenue</div>
                                    <div id="ep_rev" style="font-size:14px;font-weight:700;color:var(--clr-teal);">Rp 0
                                    </div>
                                </div>
                                <div style="color:#ccc;">−</div>
                                <div>
                                    <div style="font-size:10px;color:#888;">Total Biaya</div>
                                    <div id="ep_cost" style="font-size:14px;font-weight:700;color:var(--clr-magenta);">
                                        Rp 0</div>
                                </div>
                                <div style="color:#ccc;">=</div>
                                <div>
                                    <div style="font-size:10px;color:#888;">Est. Profit</div>
                                    <div id="ep_profit" style="font-size:17px;font-weight:800;">Rp 0</div>
                                </div>
                                <div>
                                    <div style="font-size:10px;color:#888;">Margin</div>
                                    <div id="ep_margin" style="font-size:14px;font-weight:700;">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end;position:sticky;bottom:0;background:white;border-radius:0 0 16px 16px;">
                    <button type="button" id="cancelModal" class="btn-ghost-momi">Batal</button>
                    <button type="submit" id="editSubmitBtn" class="btn-primary-momi" style="justify-content:center;">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- FORM DELETE --}}
    <form id="deleteForm" method="POST" style="display:none;">@csrf @method('DELETE')</form>

    {{-- FORM BULK DELETE --}}
    <form id="bulkDeleteForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="transaction_ids" id="bulkTransactionIds">
    </form>
@endsection

@push('styles')
    <style>
        .badge-completed {
            background: rgba(22, 163, 74, 0.12);
            color: var(--green);
        }

        .badge-pending {
            background: rgba(251, 191, 36, 0.12);
            color: #d97706;
        }

        .badge-cancelled {
            background: rgba(220, 38, 38, 0.08);
            color: var(--red);
        }

        .badge-returned {
            background: rgba(198, 28, 140, 0.08);
            color: var(--clr-magenta);
        }

        .sortable a {
            transition: color 0.2s;
        }

        .sortable a:hover {
            color: var(--clr-magenta) !important;
        }

        #editModal {
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .table-custom td {
            vertical-align: middle;
        }

        .card-light {
            transition: all 0.2s;
        }

        .card-light:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }

        .row-checkbox:checked {
            accent-color: var(--clr-teal);
        }

        #selectAllCheckbox {
            accent-color: var(--clr-teal);
        }
    </style>
@endpush

@push('scripts')
    <script>
        // ==================== FORMAT RUPIAH ====================
        function fmtRp(n) {
            return 'Rp ' + Math.max(0, Math.round(n)).toLocaleString('id-ID');
        }

        // ==================== MODAL EDIT ====================
        const modal = document.getElementById('editModal');
        const loading = document.getElementById('modalLoading');
        const form = document.getElementById('editForm');

        function openModal() {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        document.getElementById('closeModal').addEventListener('click', closeModal);
        document.getElementById('cancelModal').addEventListener('click', closeModal);
        document.getElementById('editBackdrop').addEventListener('click', closeModal);

        // Keyboard shortcut: ESC to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                closeModal();
            }
        });

        document.querySelectorAll('.btn-edit-tx').forEach(btn => {
            btn.addEventListener('click', async function() {
                openModal();
                loading.style.display = 'block';
                form.style.display = 'none';
                try {
                    const res = await fetch(`/transactions/${this.dataset.id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const tx = await res.json();
                    document.getElementById('edit_id').value = tx.id;
                    document.getElementById('edit_marketplace_id').value = tx.marketplace_id;
                    document.getElementById('edit_product_id').value = tx.product_id;
                    document.getElementById('edit_transaction_date').value = tx.transaction_date;
                    document.getElementById('edit_order_id').value = tx.order_id || '';
                    document.getElementById('edit_quantity').value = tx.quantity;
                    document.getElementById('edit_unit_price').value = tx.unit_price;
                    document.getElementById('edit_revenue').value = tx.revenue;
                    document.getElementById('edit_cogs').value = tx.cogs || 0;
                    document.getElementById('edit_advertising_spend').value = tx.advertising_spend || 0;
                    document.getElementById('edit_platform_fee').value = tx.platform_fee || 0;
                    document.getElementById('edit_shipping_subsidy').value = tx.shipping_subsidy || 0;
                    document.getElementById('edit_discount').value = tx.discount || 0;
                    document.getElementById('edit_status').value = tx.status;
                    document.getElementById('edit_customer_city').value = tx.customer_city || '';
                    document.getElementById('edit_notes').value = tx.notes || '';
                    document.getElementById('editSubtitle').textContent =
                        `Order: ${tx.order_id || 'ID #' + tx.id} · ${tx.marketplace?.name || ''} · ${tx.product?.name || ''}`;
                    loading.style.display = 'none';
                    form.style.display = 'block';
                    recalcEdit();
                } catch (e) {
                    closeModal();
                    SwalMomi.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data transaksi.'
                    });
                }
            });
        });

        // ==================== RECALCULATE EDIT ====================
        function recalcEdit() {
            const rev = parseFloat(document.getElementById('edit_revenue').value) || 0;
            const cogs = parseFloat(document.getElementById('edit_cogs').value) || 0;
            const ads = parseFloat(document.getElementById('edit_advertising_spend').value) || 0;
            const fee = parseFloat(document.getElementById('edit_platform_fee').value) || 0;
            const ship = parseFloat(document.getElementById('edit_shipping_subsidy').value) || 0;
            const disc = parseFloat(document.getElementById('edit_discount').value) || 0;
            const cost = cogs + ads + fee + ship + disc;
            const p = rev - cost;
            const m = rev > 0 ? (p / rev * 100).toFixed(1) : 0;
            const clr = p >= 0 ? 'var(--green)' : 'var(--red)';
            document.getElementById('ep_rev').textContent = fmtRp(rev);
            document.getElementById('ep_cost').textContent = fmtRp(cost);
            document.getElementById('ep_profit').textContent = fmtRp(p);
            document.getElementById('ep_margin').textContent = m + '%';
            document.getElementById('ep_profit').style.color = clr;
            document.getElementById('ep_margin').style.color = clr;
        }

        ['edit_revenue', 'edit_cogs', 'edit_advertising_spend', 'edit_platform_fee', 'edit_shipping_subsidy',
            'edit_discount'
        ].forEach(id => document.getElementById(id)?.addEventListener('input', recalcEdit));

        document.getElementById('edit_quantity').addEventListener('input', function() {
            const price = parseFloat(document.getElementById('edit_unit_price').value) || 0;
            document.getElementById('edit_revenue').value = (this.value || 0) * price;
            recalcEdit();
        });

        document.getElementById('edit_unit_price').addEventListener('input', function() {
            const qty = parseFloat(document.getElementById('edit_quantity').value) || 0;
            document.getElementById('edit_revenue').value = qty * (this.value || 0);
            recalcEdit();
        });

        // ==================== SUBMIT EDIT ====================
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const id = document.getElementById('edit_id').value;
            const btn = document.getElementById('editSubmitBtn');
            btn.disabled = true;
            btn.innerHTML =
                '<span style="display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.3);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;margin-right:6px;"></span>Menyimpan...';
            const fd = new FormData(this);
            fd.append('_method', 'PUT');
            try {
                const res = await fetch(`/transactions/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                });
                const data = await res.json();
                if (data.success) {
                    closeModal();
                    SwalMomi.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            showConfirmButton: false,
                            timerProgressBar: true
                        })
                        .then(() => location.reload());
                } else {
                    const errHtml = data.errors ? Object.values(data.errors).flat().map(e =>
                        `<div style="padding:2px 0;">• ${e}</div>`).join('') : data.message;
                    SwalMomi.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: `<div style="text-align:left;font-size:13px;">${errHtml}</div>`
                    });
                }
            } catch (err) {
                SwalMomi.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan jaringan.'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-save me-1"></i>Simpan Perubahan';
            }
        });

        // ==================== DELETE SINGLE ====================
        document.querySelectorAll('.btn-delete-tx').forEach(btn => {
            btn.addEventListener('click', function() {
                const {
                    id,
                    label
                } = this.dataset;
                SwalMomi.fire({
                    icon: 'warning',
                    title: 'Hapus Transaksi?',
                    html: `Transaksi <strong>${label}</strong> akan dihapus permanen.<br><small style="color:#888">Tindakan ini tidak dapat dibatalkan.</small>`,
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                    focusCancel: true,
                }).then(r => {
                    if (r.isConfirmed) {
                        const f = document.getElementById('deleteForm');
                        f.action = `/transactions/${id}`;
                        f.submit();
                    }
                });
            });
        });

        // ==================== BULK SELECTION ====================
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const selectAll = document.getElementById('selectAllCheckbox');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        const toggleSelectAllBtn = document.getElementById('toggleSelectAll');

        function updateBulkActions() {
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            if (checked > 0) {
                bulkActions.style.display = 'block';
                selectedCount.textContent = checked;
            } else {
                bulkActions.style.display = 'none';
            }
            // Update select all checkbox state
            const total = document.querySelectorAll('.row-checkbox').length;
            if (total > 0) {
                selectAll.checked = checked === total;
                selectAll.indeterminate = checked > 0 && checked < total;
            }
        }

        // Individual checkbox change
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkActions);
        });

        // Select All checkbox
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });

        // Toggle Select All button
        toggleSelectAllBtn?.addEventListener('click', function() {
            const allChecked = document.querySelectorAll('.row-checkbox:checked').length === document
                .querySelectorAll(
                    '.row-checkbox').length;
            checkboxes.forEach(cb => cb.checked = !allChecked);
            selectAll.checked = !allChecked;
            updateBulkActions();
            this.innerHTML = allChecked ?
                '<i class="bi bi-check2-square me-1"></i>Pilih Semua' :
                '<i class="bi bi-square me-1"></i>Batal Pilih';
        });

        // Clear Selection
        document.getElementById('clearSelection')?.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = false);
            selectAll.checked = false;
            updateBulkActions();
            toggleSelectAllBtn.innerHTML = '<i class="bi bi-check2-square me-1"></i>Pilih Semua';
        });

        // ==================== BULK DELETE ====================
        document.getElementById('bulkDeleteBtn')?.addEventListener('click', function() {
            const ids = Array.from(document.querySelectorAll('.row-checkbox:checked'))
                .map(cb => cb.value);

            if (ids.length === 0) return;

            SwalMomi.fire({
                icon: 'warning',
                title: 'Hapus Transaksi Terpilih?',
                html: `
                    <strong>${ids.length} transaksi</strong> akan dihapus permanen.<br>
                    <small style="color:#888">Tindakan ini tidak dapat dibatalkan.</small>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, Hapus Semua',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
                focusCancel: true,
            }).then(r => {
                if (r.isConfirmed) {
                    const f = document.getElementById('bulkDeleteForm');
                    document.getElementById('bulkTransactionIds').value = JSON.stringify(ids);
                    f.action = `/transactions/bulk-delete`;
                    f.submit();
                }
            });
        });

        // ==================== AUTO SUBMIT FILTER (for select/daterange) ====================
        // Already using onchange="this.form.submit()" in HTML

        // ==================== SEARCH WITH ENTER KEY ====================
        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('filterForm').submit();
            }
        });

        // ==================== INITIALIZE ====================
        updateBulkActions();
    </script>
@endpush
