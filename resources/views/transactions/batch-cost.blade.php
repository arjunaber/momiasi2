@extends('layouts.app')
@section('title', 'Update Biaya Bulanan')
@section('page-title', 'Update Biaya Bulanan')
@section('page-subtitle', 'Update biaya iklan, COGS, dan fee secara massal per periode')

@section('topbar-actions')
    <a href="{{ route('transactions.index') }}" class="btn-ghost-momi">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card-light">
                <div class="card-header-light">
                    <h3 class="card-title-light"><i class="bi bi-pencil-square me-2"></i>Update Biaya per Periode</h3>
                    <span style="font-size:11px;color:#888;">Update biaya untuk semua transaksi dalam periode tertentu</span>
                </div>
                <div class="card-body-light">
                    <form method="POST" action="{{ route('transactions.batch-update-costs') }}">
                        @csrf

                        <div class="alert alert-info"
                            style="background:rgba(0,139,139,0.06);border:1px solid rgba(0,139,139,0.15);border-radius:8px;padding:12px 16px;font-size:13px;color:#555;">
                            <i class="bi bi-info-circle me-2" style="color:var(--clr-teal);"></i>
                            <strong>Tips:</strong> Isi hanya kolom yang ingin diupdate. Biaya akan dibagi rata ke semua
                            transaksi.
                        </div>

                        <div class="row g-3">
                            {{-- Periode --}}
                            <div class="col-md-6">
                                <label class="form-label-light">Periode Bulan *</label>
                                <select name="period" class="form-select-light" required>
                                    <option value="">— Pilih Periode —</option>
                                    @foreach ($periods as $period)
                                        <option value="{{ $period }}"
                                            {{ old('period') == $period ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::parse($period . '-01')->format('F Y') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('period')
                                    <div style="font-size:11px;color:var(--clr-magenta);margin-top:3px;">{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Marketplace --}}
                            <div class="col-md-6">
                                <label class="form-label-light">Marketplace</label>
                                <select name="marketplace_id" class="form-select-light">
                                    <option value="">— Semua Marketplace —</option>
                                    @foreach ($marketplaces as $mp)
                                        <option value="{{ $mp->id }}"
                                            {{ old('marketplace_id') == $mp->id ? 'selected' : '' }}>
                                            {{ $mp->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small style="font-size:11px;color:#888;">Kosongkan untuk semua marketplace</small>
                            </div>
                        </div>

                        <hr style="border-color:var(--border);margin:20px 0;">

                        <div class="row g-3">
                            {{-- Advertising Spend --}}
                            <div class="col-md-6">
                                <label class="form-label-light">Total Advertising Spend (Rp)</label>
                                <input type="number" name="advertising_spend" class="form-control-light"
                                    value="{{ old('advertising_spend') }}" min="0" step="1000"
                                    placeholder="Total biaya iklan bulan ini">
                                <small style="font-size:11px;color:#888;">Akan dibagi rata ke semua transaksi</small>
                            </div>

                            {{-- COGS Percentage --}}
                            <div class="col-md-6">
                                <label class="form-label-light">COGS (% dari Revenue)</label>
                                <input type="number" name="cogs_percentage" class="form-control-light"
                                    value="{{ old('cogs_percentage') }}" min="0" max="100" step="0.01"
                                    placeholder="Contoh: 70">
                                <small style="font-size:11px;color:#888;">Persentase HPP dari revenue</small>
                            </div>

                            {{-- Platform Fee Percentage --}}
                            <div class="col-md-6">
                                <label class="form-label-light">Platform Fee (% dari Revenue)</label>
                                <input type="number" name="platform_fee_percentage" class="form-control-light"
                                    value="{{ old('platform_fee_percentage') }}" min="0" max="100"
                                    step="0.01" placeholder="Contoh: 5">
                                <small style="font-size:11px;color:#888;">Persentase komisi platform</small>
                            </div>

                            {{-- Shipping Subsidy --}}
                            <div class="col-md-6">
                                <label class="form-label-light">Total Shipping Subsidy (Rp)</label>
                                <input type="number" name="shipping_subsidy" class="form-control-light"
                                    value="{{ old('shipping_subsidy') }}" min="0" step="1000"
                                    placeholder="Total subsidi ongkir">
                                <small style="font-size:11px;color:#888;">Akan dibagi rata ke semua transaksi</small>
                            </div>

                            {{-- Discount --}}
                            <div class="col-md-6">
                                <label class="form-label-light">Total Discount (Rp)</label>
                                <input type="number" name="discount" class="form-control-light"
                                    value="{{ old('discount') }}" min="0" step="1000" placeholder="Total diskon">
                                <small style="font-size:11px;color:#888;">Akan dibagi rata ke semua transaksi</small>
                            </div>
                        </div>

                        <div style="border-top:1px solid var(--border);margin:20px 0;"></div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn-primary-momi" style="padding:10px 30px;">
                                <i class="bi bi-check2-circle me-2"></i>Update Biaya
                            </button>
                            <a href="{{ route('transactions.index') }}" class="btn-ghost-momi" style="padding:10px 30px;">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Preview Data yang Akan Diupdate --}}
            <div class="card-light mt-4">
                <div class="card-header-light">
                    <h3 class="card-title-light"><i class="bi bi-database me-2"></i>Preview Data</h3>
                </div>
                <div class="card-body-light">
                    <div class="row text-center">
                        <div class="col-3">
                            <div style="font-size:24px;font-weight:700;color:var(--clr-teal);">
                                {{ number_format($stats['total_transactions'] ?? 0) }}
                            </div>
                            <div style="font-size:12px;color:#888;">Total Transaksi</div>
                        </div>
                        <div class="col-3">
                            <div style="font-size:24px;font-weight:700;color:var(--clr-teal);">
                                {{ number_format($stats['total_periods'] ?? 0) }}
                            </div>
                            <div style="font-size:12px;color:#888;">Total Periode</div>
                        </div>
                        <div class="col-3">
                            <div style="font-size:24px;font-weight:700;color:var(--green);">
                                Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}
                            </div>
                            <div style="font-size:12px;color:#888;">Total Revenue</div>
                        </div>
                        <div class="col-3">
                            <div style="font-size:24px;font-weight:700;color:var(--clr-magenta);">
                                {{ number_format($stats['avg_profit_margin'] ?? 0, 1) }}%
                            </div>
                            <div style="font-size:12px;color:#888;">Rata-rata Margin</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
