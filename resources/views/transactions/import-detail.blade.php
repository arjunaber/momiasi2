@extends('layouts.app')
@section('title', 'Detail Import - ' . $csvImport->filename)
@section('page-title', 'Detail Import CSV')
@section('page-subtitle', $csvImport->filename . ' - ' . $csvImport->created_at->format('d M Y H:i'))

@section('topbar-actions')
    <a href="{{ route('transactions.import') }}" class="btn-ghost-momi">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Import
    </a>
@endsection

@section('content')
    {{-- SUMMARY CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card-light" style="border-left:4px solid var(--clr-teal);">
                <div class="card-body-light" style="padding:14px 18px;">
                    <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Total Baris</div>
                    <div style="font-size:24px;font-weight:700;color:var(--clr-teal);">
                        {{ number_format($csvImport->total_rows) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-light" style="border-left:4px solid var(--green);">
                <div class="card-body-light" style="padding:14px 18px;">
                    <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Berhasil</div>
                    <div style="font-size:24px;font-weight:700;color:var(--green);">
                        {{ number_format($csvImport->success_rows) }}
                        @if ($csvImport->total_rows > 0)
                            <span style="font-size:14px;color:#888;">({{ $csvImport->success_rate ?? 0 }}%)</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-light" style="border-left:4px solid var(--red);">
                <div class="card-body-light" style="padding:14px 18px;">
                    <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Gagal</div>
                    <div style="font-size:24px;font-weight:700;color:var(--red);">
                        {{ number_format($csvImport->failed_rows) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-light" style="border-left:4px solid #d97706;">
                <div class="card-body-light" style="padding:14px 18px;">
                    <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Duplikat</div>
                    <div style="font-size:24px;font-weight:700;color:#d97706;">
                        {{ number_format($csvImport->duplicate_rows ?? 0) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- INFO IMPORT --}}
    <div class="card-light mb-4">
        <div class="card-header-light">
            <h3 class="card-title-light"><i class="bi bi-info-circle me-2"></i>Informasi Import</h3>
        </div>
        <div class="card-body-light">
            <div class="row g-3">
                <div class="col-md-3">
                    <div style="font-size:11px;color:#888;">Nama File</div>
                    <div style="font-size:13px;font-weight:500;">{{ $csvImport->filename }}</div>
                </div>
                <div class="col-md-2">
                    <div style="font-size:11px;color:#888;">Status</div>
                    <span class="{{ $csvImport->status_badge_class ?? 'badge-completed' }}"
                        style="font-size:12px;padding:3px 12px;border-radius:20px;font-weight:600;">
                        <i class="bi {{ $csvImport->status_icon ?? 'bi-check-circle' }} me-1"></i>
                        {{ $csvImport->status_label ?? 'Selesai' }}
                    </span>
                </div>
                <div class="col-md-2">
                    <div style="font-size:11px;color:#888;">Ukuran File</div>
                    <div style="font-size:13px;font-weight:500;">{{ $csvImport->file_size_formatted ?? '—' }}</div>
                </div>
                <div class="col-md-2">
                    <div style="font-size:11px;color:#888;">Durasi</div>
                    <div style="font-size:13px;font-weight:500;">{{ $csvImport->duration_formatted ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div style="font-size:11px;color:#888;">Tipe Import</div>
                    <div style="font-size:13px;font-weight:500;">{{ ucfirst($csvImport->import_type ?? 'simple') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- FAILED ROWS --}}
    @if ($csvImport->failed_rows > 0 && !empty($failedRows))
        <div class="card-light mb-4">
            <div class="card-header-light" style="border-bottom-color:#FEE2E2;">
                <h3 class="card-title-light" style="color:var(--red);">
                    <i class="bi bi-x-circle me-2"></i>Data Gagal ({{ count($failedRows) }})
                </h3>
                <span style="font-size:11px;color:#888;">Baris yang gagal diimport karena error</span>
            </div>
            <div class="card-body-light" style="padding:0;">
                <div style="overflow-x:auto;max-height:400px;overflow-y:auto;">
                    <table class="table-custom" style="font-size:13px;">
                        <thead style="position:sticky;top:0;background:white;z-index:1;">
                            <tr>
                                <th style="width:80px;">Baris</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($failedRows as $row)
                                <tr style="background:#FFF8F8;">
                                    <td style="font-weight:700;color:var(--red);text-align:center;">
                                        #{{ $row['row'] }}
                                    </td>
                                    <td style="color:#555;font-family:monospace;font-size:12px;">
                                        {{ $row['error'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- DUPLICATE ROWS --}}
    @if ($csvImport->duplicate_rows > 0)
        <div class="card-light mb-4">
            <div class="card-header-light" style="border-bottom-color:#FEF3C7;">
                <h3 class="card-title-light" style="color:#d97706;">
                    <i class="bi bi-exclamation-triangle me-2"></i>Data Duplikat ({{ $csvImport->duplicate_rows }})
                </h3>
                <span style="font-size:11px;color:#888;">Baris yang dilewati karena data sudah ada</span>
            </div>
            <div class="card-body-light">
                <div style="background:#FFFBEB;border-radius:8px;padding:12px 16px;font-size:13px;color:#92400e;">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ $csvImport->duplicate_rows }} baris data duplikat dilewati karena sudah ada di database.
                    @if ($csvImport->duplicate_rows > 50)
                        <br><small style="color:#888;">(Hanya menampilkan 50 baris pertama)</small>
                    @endif
                </div>
                @if (!empty($duplicateRows) && count($duplicateRows) > 0)
                    <div
                        style="margin-top:12px;display:flex;flex-wrap:wrap;gap:6px;max-height:200px;overflow-y:auto;padding:8px;">
                        @foreach (array_slice($duplicateRows, 0, 50) as $row)
                            <span
                                style="background:#FEF3C7;color:#92400e;padding:2px 10px;border-radius:4px;font-size:12px;font-family:monospace;">
                                #{{ $row }}
                            </span>
                        @endforeach
                        @if (count($duplicateRows) > 50)
                            <span style="background:#F5F5F5;color:#888;padding:2px 10px;border-radius:4px;font-size:12px;">
                                +{{ count($duplicateRows) - 50 }} baris lainnya
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- SUCCESSFUL TRANSACTIONS --}}
    <div class="card-light">
        <div class="card-header-light">
            <h3 class="card-title-light">
                <i class="bi bi-check-circle me-2" style="color:var(--green);"></i>
                Transaksi Berhasil ({{ $transactions->total() }})
            </h3>
            <span style="font-size:11px;color:#888;">Data yang berhasil diimport</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Marketplace</th>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Profit</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td style="font-size:11px;color:#888;">{{ $loop->iteration }}</td>
                            <td style="white-space:nowrap;font-size:13px;">
                                {{ $tx->transaction_date->format('d M Y') }}
                            </td>
                            <td>
                                <span
                                    style="font-size:11px;padding:2px 10px;border-radius:20px;background:{{ $tx->marketplace->color ?? '#888' }}15;color:{{ $tx->marketplace->color ?? '#888' }};">
                                    {{ $tx->marketplace->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <div style="font-size:12px;font-weight:500;">{{ $tx->product->name ?? '-' }}</div>
                                <div style="font-size:10px;color:#aaa;font-family:monospace;">
                                    {{ $tx->product->sku ?? '-' }}</div>
                            </td>
                            <td style="text-align:center;font-weight:600;">{{ $tx->quantity }}</td>
                            <td class="text-end" style="font-weight:600;color:var(--clr-teal);">
                                Rp {{ number_format($tx->revenue, 0, ',', '.') }}
                            </td>
                            <td class="text-end"
                                style="font-weight:600;color:{{ $tx->profit >= 0 ? 'var(--green)' : 'var(--red)' }};">
                                Rp {{ number_format($tx->profit, 0, ',', '.') }}
                            </td>
                            <td>
                                <span
                                    style="font-size:11px;padding:2px 10px;border-radius:20px;background:rgba(22,163,74,0.1);color:var(--green);">
                                    {{ ucfirst($tx->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5" style="color:#aaa;">
                                <i class="bi bi-inbox"
                                    style="font-size:30px;display:block;margin-bottom:8px;color:var(--clr-mint);"></i>
                                Tidak ada transaksi berhasil dari import ini.
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
                    {{ $transactions->total() }}
                </div>
                {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- TOMBOL AKSI --}}
    <div class="d-flex gap-3 mt-4">
        <a href="{{ route('transactions.import') }}" class="btn-ghost-momi">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Import
        </a>
        @if ($csvImport->success_rows > 0)
            <a href="{{ route('transactions.index', ['period' => $csvImport->created_at->format('Y-m')]) }}"
                class="btn-ghost-momi">
                <i class="bi bi-eye me-1"></i>Lihat Transaksi
            </a>
        @endif
        @if ($csvImport->failed_rows > 0 && !empty($csvImport->error_log))
            <button type="button" class="btn-danger-soft" onclick="copyErrors()">
                <i class="bi bi-clipboard me-1"></i>Salin Error Log
            </button>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function copyErrors() {
            const errors = @json($failedRows ?? []);
            if (errors.length === 0) return;

            const text = errors.map(e => `Baris ${e.row}: ${e.error}`).join('\n');
            navigator.clipboard.writeText(text).then(() => {
                SwalMomi.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Error log telah disalin ke clipboard.',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            }).catch(() => {
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                SwalMomi.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Error log telah disalin ke clipboard.',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            });
        }
    </script>
@endpush
