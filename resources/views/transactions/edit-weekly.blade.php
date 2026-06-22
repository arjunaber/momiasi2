@extends('layouts.app')
@section('title', 'Edit Biaya Mingguan')
@section('page-title', 'Edit Biaya Mingguan')
@section('page-subtitle', 'Update biaya untuk transaksi dalam rentang minggu tertentu')

@section('topbar-actions')
    <a href="{{ route('transactions.index') }}" class="btn-ghost-momi">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Filter Form --}}
            <div class="card-light mb-4">
                <div class="card-body-light">
                    <form method="GET" action="{{ route('transactions.edit-weekly') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label-light">Tanggal Mulai</label>
                            <input type="date" name="week_start" class="form-control-light" value="{{ $weekStart }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-light">Tanggal Akhir</label>
                            <input type="date" name="week_end" class="form-control-light" value="{{ $weekEnd }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-light">Marketplace</label>
                            <select name="marketplace_id" class="form-select-light">
                                <option value="">— Semua —</option>
                                @foreach ($marketplaces as $mp)
                                    <option value="{{ $mp->id }}" {{ $marketplaceId == $mp->id ? 'selected' : '' }}>
                                        {{ $mp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn-primary-momi w-100">
                                <i class="bi bi-filter me-2"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Summary --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card-light"
                        style="background:var(--bg-teal-light);border:1px solid var(--border);border-radius:10px;padding:16px;">
                        <div style="font-size:12px;color:#888;">Total Transaksi</div>
                        <div style="font-size:24px;font-weight:700;color:var(--clr-teal);">
                            {{ $summary['total_transactions'] }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-light"
                        style="background:var(--bg-teal-light);border:1px solid var(--border);border-radius:10px;padding:16px;">
                        <div style="font-size:12px;color:#888;">Total Revenue</div>
                        <div style="font-size:24px;font-weight:700;color:var(--clr-teal);">Rp
                            {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-light"
                        style="background:var(--bg-teal-light);border:1px solid var(--border);border-radius:10px;padding:16px;">
                        <div style="font-size:12px;color:#888;">Total Profit</div>
                        <div style="font-size:24px;font-weight:700;color:var(--green);">Rp
                            {{ number_format($summary['total_profit'], 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-light"
                        style="background:var(--bg-teal-light);border:1px solid var(--border);border-radius:10px;padding:16px;">
                        <div style="font-size:12px;color:#888;">Total Advertising Spend</div>
                        <div style="font-size:24px;font-weight:700;color:var(--clr-magenta);">Rp
                            {{ number_format($summary['total_advertising_spend'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            {{-- Edit Form --}}
            <form method="POST" action="{{ route('transactions.update-weekly') }}">
                @csrf

                <div class="card-light mb-4">
                    <div class="card-header-light">
                        <h3 class="card-title-light"><i class="bi bi-pencil me-2"></i>Update Biaya Massal</h3>
                    </div>
                    <div class="card-body-light">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label-light">Advertising Spend</label>
                                <input type="number" name="advertising_spend" class="form-control-light" min="0"
                                    step="1000" placeholder="Per transaksi">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-light">Platform Fee</label>
                                <input type="number" name="platform_fee" class="form-control-light" min="0"
                                    step="1000" placeholder="Per transaksi">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-light">Discount</label>
                                <input type="number" name="discount" class="form-control-light" min="0"
                                    step="1000" placeholder="Per transaksi">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-light">Shipping Subsidy</label>
                                <input type="number" name="shipping_subsidy" class="form-control-light" min="0"
                                    step="1000" placeholder="Per transaksi">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-light">COGS (% dari Revenue)</label>
                                <input type="number" name="cogs_percentage" class="form-control-light" min="0"
                                    max="100" step="0.01" placeholder="Contoh: 70">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Transactions List --}}
                <div class="card-light">
                    <div class="card-header-light">
                        <h3 class="card-title-light"><i class="bi bi-list-ul me-2"></i>Transaksi Minggu Ini</h3>
                        <span style="font-size:12px;color:#888;">
                            <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
                            <label for="selectAll" style="cursor:pointer;margin-left:5px;">Pilih Semua</label>
                        </span>
                    </div>
                    <div class="card-body-light" style="padding:0;">
                        <div style="overflow-x:auto;">
                            <table class="table-light" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th style="width:40px;text-align:center;">
                                            <input type="checkbox" id="selectAllTable" onchange="toggleAll(this)">
                                        </th>
                                        <th>Tanggal</th>
                                        <th>Marketplace</th>
                                        <th>Produk</th>
                                        <th>Qty</th>
                                        <th style="text-align:right;">Revenue</th>
                                        <th style="text-align:right;">Profit</th>
                                        <th style="text-align:right;">Margin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $tx)
                                        <tr>
                                            <td style="text-align:center;">
                                                <input type="checkbox" name="transaction_ids[]"
                                                    value="{{ $tx->id }}" class="row-checkbox">
                                            </td>
                                            <td>{{ $tx->transaction_date->format('d M Y') }}</td>
                                            <td><span class="badge"
                                                    style="background:{{ $tx->marketplace->color ?? '#888' }};color:#fff;padding:2px 10px;border-radius:20px;font-size:11px;">{{ $tx->marketplace->name ?? '-' }}</span>
                                            </td>
                                            <td>{{ $tx->product->name ?? '-' }}</td>
                                            <td>{{ $tx->quantity }}</td>
                                            <td style="text-align:right;">Rp
                                                {{ number_format($tx->revenue, 0, ',', '.') }}</td>
                                            <td
                                                style="text-align:right;color:{{ $tx->profit >= 0 ? 'var(--green)' : 'var(--red)' }};">
                                                Rp {{ number_format($tx->profit, 0, ',', '.') }}
                                            </td>
                                            <td
                                                style="text-align:right;color:{{ $tx->profit_margin >= 0 ? 'var(--green)' : 'var(--red)' }};">
                                                {{ number_format($tx->profit_margin, 1) }}%
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" style="text-align:center;padding:40px 20px;color:#888;">
                                                <i class="bi bi-inbox"
                                                    style="font-size:30px;display:block;margin-bottom:10px;"></i>
                                                Tidak ada transaksi pada periode ini
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($transactions->count() > 0)
                        <div class="card-footer-light">
                            <button type="submit" class="btn-primary-momi">
                                <i class="bi bi-check2-circle me-2"></i>Update Transaksi Terpilih
                            </button>
                            <span style="font-size:12px;color:#888;margin-left:15px;">
                                <span id="selectedCount">0</span> transaksi dipilih
                            </span>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleAll(master) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = master.checked);
            updateCount();
        }

        function updateCount() {
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = checked;
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.addEventListener('change', updateCount);
            });
            updateCount();
        });
    </script>
@endpush
