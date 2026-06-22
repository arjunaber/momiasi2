@extends('layouts.app')
@section('title', 'Daftar Transaksi')
@section('page-title', 'Daftar Transaksi')
@section('page-subtitle', 'Kelola semua transaksi Momiasi & Little Mommies')

@section('topbar-actions')
    <a href="{{ route('transactions.create') }}" class="btn-primary-momi"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
    <a href="{{ route('transactions.import') }}" class="btn-ghost-momi"><i class="bi bi-upload me-1"></i>Import CSV</a>
@endsection

@section('content')

    {{-- Filter --}}
    <div class="card-light mb-4">
        <div class="card-header-light">
            <h3 class="card-title-light"><i class="bi bi-funnel me-2"></i>Filter Transaksi</h3>
            @if (request()->hasAny(['marketplace_id', 'period', 'status', 'search', 'date_from', 'date_to']))
                <a href="{{ route('transactions.index') }}" class="btn-ghost-momi"
                    style="font-size:12px;padding:5px 10px;"><i class="bi bi-x me-1"></i>Reset</a>
            @endif
        </div>
        <div class="card-body-light">
            <form method="GET" action="{{ route('transactions.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label-light">Marketplace</label>
                        <select name="marketplace_id" class="form-select-light">
                            <option value="">Semua Platform</option>
                            @foreach ($marketplaces as $mp)
                                <option value="{{ $mp->id }}" {{ request('marketplace_id') == $mp->id ? 'selected' : '' }}>
                                    {{ $mp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-light">Periode</label>
                        <select name="period" class="form-select-light">
                            <option value="">Semua</option>
                            @foreach ($periods as $p)
                                <option value="{{ $p }}" {{ request('period') == $p ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($p . '-01')->format('M Y') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-light">Status</label>
                        <select name="status" class="form-select-light">
                            <option value="">Semua</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan
                            </option>
                            <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Retur</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-light">Dari</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="form-control-light">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-light">Sampai</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control-light">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn-primary-momi w-100" style="justify-content:center;padding:8px;"><i
                                class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-5">
                        <label class="form-label-light">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control-light"
                            placeholder="Order ID / Nama Produk / Kota...">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card-light">
        <div class="card-header-light">
            <h3 class="card-title-light">
                <i class="bi bi-table me-2"></i>Data Transaksi
                <span
                    style="background:var(--accent-soft);color:var(--clr-teal);font-size:11px;padding:2px 9px;border-radius:20px;margin-left:8px;font-weight:600;">{{ $transactions->total() }}</span>
            </h3>
            <a href="{{ route('transactions.template') }}" class="btn-ghost-momi" style="font-size:12px;padding:5px 10px;">
                <i class="bi bi-download me-1"></i>Template CSV
            </a>
        </div>
        <div style="overflow-x:auto;">
            <table class="table-custom">
                <thead>
                    <tr>
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
                            <td style="white-space:nowrap;">
                                <div style="font-size:13px;font-weight:500;color:#333;">
                                    {{ $tx->transaction_date->format('d M Y') }}</div>
                                <div style="font-size:10px;color:#aaa;">{{ $tx->period_month }}</div>
                            </td>
                            <td><span
                                    style="font-size:11px;font-family:monospace;color:#888;">{{ $tx->order_id ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="badge-{{ $tx->marketplace->slug }}"
                                    style="font-size:11px;padding:3px 9px;border-radius:20px;font-weight:600;">
                                    {{ $tx->marketplace->name }}
                                </span>
                            </td>
                            <td>
                                <div style="font-size:12px;font-weight:500;max-width:170px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#333;"
                                    title="{{ $tx->product->name }}">
                                    {{ $tx->product->name }}
                                </div>
                                <div style="font-size:10px;color:#aaa;">{{ $tx->product->sku }}</div>
                            </td>
                            <td class="text-end" style="font-weight:600;color:#333;">{{ $tx->quantity }}</td>
                            <td class="text-end" style="font-weight:600;color:var(--clr-teal);">Rp
                                {{ number_format($tx->revenue, 0, ',', '.') }}</td>
                            <td class="text-end" style="color:var(--clr-magenta);">Rp
                                {{ number_format($tx->advertising_spend, 0, ',', '.') }}</td>
                            <td class="text-end"
                                style="font-weight:700;color:{{ $tx->profit >= 0 ? 'var(--green)' : 'var(--red)' }};">
                                Rp {{ number_format($tx->profit, 0, ',', '.') }}
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
                                    style="font-size:11px;padding:3px 9px;border-radius:20px;font-weight:600;">{{ $sl }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn-edit-soft btn-edit-tx"
                                        data-id="{{ $tx->id }}" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn-danger-soft btn-delete-tx"
                                        data-id="{{ $tx->id }}" data-label="{{ $tx->order_id ?? 'ID #' . $tx->id }}"
                                        title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5" style="color:#aaa;">
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
            <div class="d-flex align-items-center justify-content-between px-4 py-3"
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
                style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:white;z-index:2;">
                <div>
                    <h4 style="font-size:17px;font-weight:700;color:var(--clr-teal);margin:0;">Edit Transaksi</h4>
                    <div id="editSubtitle" style="font-size:11px;color:#888;margin-top:1px;">—</div>
                </div>
                <button type="button" id="closeModal"
                    style="background:#F5F5F5;border:1px solid #ddd;border-radius:8px;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#666;">
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
                <input type="hidden" id="edit_id">
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
                            <input type="text" name="notes" id="edit_notes" class="form-control-light">
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
                    style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end;position:sticky;bottom:0;background:white;">
                    <button type="button" id="cancelModal" class="btn-ghost-momi">Batal</button>
                    <button type="submit" id="editSubmitBtn" class="btn-primary-momi" style="justify-content:center;">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display:none;">@csrf @method('DELETE')</form>
@endsection

@push('scripts')
    <script>
        function fmtRp(n) {
            return 'Rp ' + Math.max(0, Math.round(n)).toLocaleString('id-ID');
        }

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
                        `Order: ${tx.order_id||'ID #'+tx.id}`;
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
        ]
        .forEach(id => document.getElementById(id)?.addEventListener('input', recalcEdit));
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
                        `<div>• ${e}</div>`).join('') : data.message;
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
    </script>
@endpush
