@extends('layouts.app')
@section('title', 'Tambah Transaksi')
@section('page-title', 'Tambah Transaksi')
@section('page-subtitle', 'Input transaksi penjualan baru')

@section('topbar-actions')
    <a href="{{ route('transactions.index') }}" class="btn-ghost-momi"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('transactions.store') }}">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card-light mb-4">
                    <div class="card-header-light">
                        <h3 class="card-title-light"><i class="bi bi-info-circle me-2"></i>Informasi Transaksi</h3>
                    </div>
                    <div class="card-body-light">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-light">Marketplace *</label>
                                <select name="marketplace_id" class="form-select-light" required>
                                    <option value="">— Pilih —</option>
                                    @foreach ($marketplaces as $mp)
                                        <option value="{{ $mp->id }}"
                                            {{ old('marketplace_id') == $mp->id ? 'selected' : '' }}>{{ $mp->name }}</option>
                                    @endforeach
                                </select>
                                @error('marketplace_id')
                                    <div style="font-size:11px;color:var(--clr-magenta);margin-top:3px;">{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-light">Produk *</label>
                                <select name="product_id" class="form-select-light" required>
                                    <option value="">— Pilih —</option>
                                    @foreach ($products as $prod)
                                        <option value="{{ $prod->id }}"
                                            {{ old('product_id') == $prod->id ? 'selected' : '' }}>
                                            {{ $prod->name }}{{ $prod->size ? ' (' . $prod->size . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div style="font-size:11px;color:var(--clr-magenta);margin-top:3px;">{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-light">Tanggal Transaksi *</label>
                                <input type="date" name="transaction_date" class="form-control-light"
                                    value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-light">Order ID</label>
                                <input type="text" name="order_id" class="form-control-light"
                                    value="{{ old('order_id') }}" placeholder="ORD-001 (opsional)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-light">Status *</label>
                                <select name="status" class="form-select-light" required>
                                    <option value="completed" {{ old('status', 'completed') === 'completed' ? 'selected' : '' }}>
                                        Selesai</option>
                                    <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan
                                    </option>
                                    <option value="returned" {{ old('status') === 'returned' ? 'selected' : '' }}>Retur</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-light">Kota Pembeli</label>
                                <input type="text" name="customer_city" class="form-control-light"
                                    value="{{ old('customer_city') }}" placeholder="Jakarta">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-light mb-4">
                    <div class="card-header-light">
                        <h3 class="card-title-light"><i class="bi bi-cash-stack me-2"></i>Data Keuangan</h3>
                        <span style="font-size:11px;color:#888;">Profit dihitung otomatis</span>
                    </div>
                    <div class="card-body-light">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label-light">Qty Terjual *</label>
                                <input type="number" name="quantity" id="qty" class="form-control-light"
                                    value="{{ old('quantity', 1) }}" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-light">Harga Satuan (Rp) *</label>
                                <input type="number" name="unit_price" id="unitPrice" class="form-control-light"
                                    value="{{ old('unit_price', 0) }}" min="0" step="1000" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-light">Revenue (Rp) *</label>
                                <input type="number" name="revenue" id="revenue" class="form-control-light"
                                    value="{{ old('revenue', 0) }}" min="0" step="1000" required>
                            </div>
                            <div style="border-top:1px solid var(--border);margin:0 12px;"></div>
                            <div class="col-md-4">
                                <label class="form-label-light">HPP / COGS (Rp)</label>
                                <input type="number" name="cogs" id="cogs" class="form-control-light"
                                    value="{{ old('cogs', 0) }}" min="0" step="1000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-light">Biaya Iklan (Rp)</label>
                                <input type="number" name="advertising_spend" id="adspend" class="form-control-light"
                                    value="{{ old('advertising_spend', 0) }}" min="0" step="1000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-light">Komisi Platform (Rp)</label>
                                <input type="number" name="platform_fee" id="platformFee" class="form-control-light"
                                    value="{{ old('platform_fee', 0) }}" min="0" step="1000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-light">Subsidi Ongkir (Rp)</label>
                                <input type="number" name="shipping_subsidy" id="shipping" class="form-control-light"
                                    value="{{ old('shipping_subsidy', 0) }}" min="0" step="1000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-light">Diskon (Rp)</label>
                                <input type="number" name="discount" id="discount" class="form-control-light"
                                    value="{{ old('discount', 0) }}" min="0" step="1000">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-light">
                    <div class="card-body-light">
                        <label class="form-label-light">Catatan</label>
                        <textarea name="notes" class="form-control-light" rows="3" placeholder="Opsional...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-light" style="position:sticky;top:70px;">
                    <div class="card-header-light">
                        <h3 class="card-title-light"><i class="bi bi-calculator me-2"></i>Preview Profit</h3>
                    </div>
                    <div class="card-body-light">
                        <div
                            style="background:#F0FAFA;border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:16px;">
                            <div class="d-flex justify-content-between mb-2">
                                <span style="font-size:13px;color:#888;">Revenue</span>
                                <span id="pRev" style="font-size:13px;font-weight:600;color:var(--clr-teal);">Rp
                                    0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span style="font-size:13px;color:#888;">Total Biaya</span>
                                <span id="pCost" style="font-size:13px;font-weight:600;color:var(--clr-magenta);">Rp
                                    0</span>
                            </div>
                            <div style="border-top:1px dashed var(--border);margin:10px 0;"></div>
                            <div class="d-flex justify-content-between">
                                <span style="font-size:14px;font-weight:700;">Profit</span>
                                <span id="pProfit" style="font-size:18px;font-weight:800;color:var(--green);">Rp
                                    0</span>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span style="font-size:12px;color:#888;">Margin</span>
                                <span id="pMargin" style="font-size:12px;font-weight:600;color:var(--green);">0%</span>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary-momi w-100"
                            style="padding:12px;justify-content:center;">
                            <i class="bi bi-save me-2"></i>Simpan Transaksi
                        </button>
                        <a href="{{ route('transactions.index') }}" class="btn-ghost-momi w-100 mt-2"
                            style="padding:10px;justify-content:center;">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        function fmtRp(n) {
            return 'Rp ' + Math.max(0, Math.round(n)).toLocaleString('id-ID');
        }

        function recalc() {
            const rev = parseFloat(document.getElementById('revenue').value) || 0;
            const cogs = parseFloat(document.getElementById('cogs').value) || 0;
            const ads = parseFloat(document.getElementById('adspend').value) || 0;
            const fee = parseFloat(document.getElementById('platformFee').value) || 0;
            const ship = parseFloat(document.getElementById('shipping').value) || 0;
            const disc = parseFloat(document.getElementById('discount').value) || 0;
            const cost = cogs + ads + fee + ship + disc;
            const p = rev - cost;
            const m = rev > 0 ? (p / rev * 100).toFixed(1) : 0;
            const clr = p >= 0 ? 'var(--green)' : 'var(--red)';
            document.getElementById('pRev').textContent = fmtRp(rev);
            document.getElementById('pCost').textContent = fmtRp(cost);
            document.getElementById('pProfit').textContent = fmtRp(p);
            document.getElementById('pMargin').textContent = m + '%';
            document.getElementById('pProfit').style.color = clr;
            document.getElementById('pMargin').style.color = clr;
        }
        document.getElementById('qty').addEventListener('input', function() {
            const price = parseFloat(document.getElementById('unitPrice').value) || 0;
            document.getElementById('revenue').value = (this.value || 0) * price;
            recalc();
        });
        document.getElementById('unitPrice').addEventListener('input', function() {
            const qty = parseFloat(document.getElementById('qty').value) || 0;
            document.getElementById('revenue').value = qty * (this.value || 0);
            recalc();
        });
        ['revenue', 'cogs', 'adspend', 'platformFee', 'shipping', 'discount'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', recalc);
        });
        recalc();
    </script>
@endpush
