@extends('layouts.app')
@section('title', 'Import CSV')
@section('page-title', 'Import CSV Transaksi')
@section('page-subtitle', 'Upload data transaksi massal dari file CSV')

@section('topbar-actions')
    <a href="{{ route('transactions.template') }}" class="btn-primary-momi"><i class="bi bi-download me-1"></i>Download
        Template</a>
    <a href="{{ route('transactions.index') }}" class="btn-ghost-momi"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
@endsection

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card-light h-100">
                <div class="card-header-light">
                    <h3 class="card-title-light"><i class="bi bi-cloud-upload me-2"></i>Unggah File CSV</h3>
                </div>
                <div class="card-body-light">
                    <form method="POST" action="{{ route('transactions.import.post') }}" enctype="multipart/form-data"
                        id="importForm">
                        @csrf
                        <div id="dropZone" onclick="document.getElementById('csvFile').click()"
                            style="border:2px dashed #D0EEEE;border-radius:14px;padding:40px 24px;text-align:center;cursor:pointer;transition:all .2s;background:#FAFFFE;">
                            <input type="file" id="csvFile" name="csv_file" accept=".csv,.txt" style="display:none;"
                                required>
                            <div id="dropDefault">
                                <div
                                    style="width:54px;height:54px;background:var(--accent-soft);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                                    <i class="bi bi-file-earmark-spreadsheet"
                                        style="font-size:26px;color:var(--clr-teal);"></i>
                                </div>
                                <div style="font-size:15px;font-weight:600;color:#333;margin-bottom:5px;">Klik atau seret
                                    file CSV ke sini</div>
                                <div style="font-size:13px;color:#aaa;">Format: .csv atau .txt · Maksimal 10 MB</div>
                            </div>
                            <div id="dropSelected" style="display:none;">
                                <div
                                    style="width:54px;height:54px;background:rgba(22,163,74,0.1);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                                    <i class="bi bi-file-earmark-check" style="font-size:26px;color:var(--green);"></i>
                                </div>
                                <div id="selName"
                                    style="font-size:14px;font-weight:600;color:var(--green);margin-bottom:3px;"></div>
                                <div id="selSize" style="font-size:12px;color:#aaa;"></div>
                            </div>
                        </div>
                        @error('csv_file')
                            <div
                                style="background:rgba(198,28,140,0.08);border:1px solid rgba(198,28,140,.2);color:var(--clr-magenta);border-radius:8px;padding:10px 12px;font-size:12px;margin-top:10px;">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                        <button type="submit" id="importBtn" class="btn-primary-momi w-100 mt-4"
                            style="padding:12px;justify-content:center;font-size:14px;" disabled>
                            <i class="bi bi-cloud-upload me-2"></i>Mulai Import
                        </button>
                    </form>
                    <div id="importProgress" style="display:none;margin-top:14px;">
                        <div
                            style="display:flex;justify-content:space-between;font-size:12px;color:#888;margin-bottom:5px;">
                            <span>Memproses...</span><span id="pctLabel">0%</span>
                        </div>
                        <div class="progress-light" style="height:8px;">
                            <div id="progressBar" class="progress-bar"
                                style="width:0%;background:var(--clr-teal);transition:width .3s;height:8px;border-radius:4px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-light h-100">
                <div class="card-header-light">
                    <h3 class="card-title-light"><i class="bi bi-info-circle me-2"></i>Panduan Format CSV</h3>
                </div>
                <div class="card-body-light" style="font-size:13px;">
                    <p style="color:#888;margin-bottom:14px;">Baris pertama harus berisi header. Kolom wajib:</p>
                    @foreach ([['transaction_date', 'Format: YYYY-MM-DD (cth: 2025-01-15)'], ['marketplace_slug', 'Nilai: shopee atau tiktok'], ['product_sku', 'SKU produk terdaftar (cth: MOM-001, LM-001)'], ['quantity', 'Jumlah unit terjual (angka ≥ 1)'], ['revenue', 'Total pendapatan (angka, tanpa titik/koma)']] as [$col, $desc])
                        <div style="display:flex;gap:10px;padding:7px 0;border-bottom:1px solid var(--border);">
                            <code
                                style="background:var(--accent-soft);color:var(--clr-teal);padding:2px 8px;border-radius:4px;font-size:11px;white-space:nowrap;flex-shrink:0;">{{ $col }}</code>
                            <span style="color:#666;font-size:12px;">{{ $desc }}</span>
                        </div>
                    @endforeach

                    <div class="mt-3">
                        <div
                            style="font-size:10px;color:var(--clr-teal);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;font-weight:700;">
                            SKU Produk Tersedia</div>
                        <div style="display:flex;flex-wrap:wrap;gap:5px;">
                            @foreach (\App\Models\Product::active()->orderBy('sku')->get() as $prod)
                                <span title="{{ $prod->name }}"
                                    style="background:#F0FAFA;color:var(--clr-teal);padding:3px 8px;border-radius:4px;font-size:11px;border:1px solid var(--border);font-family:monospace;cursor:help;">{{ $prod->sku }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Import --}}
    <div class="card-light">
        <div class="card-header-light">
            <h3 class="card-title-light"><i class="bi bi-clock-history me-2"></i>Riwayat Import</h3>
            <span style="font-size:11px;color:#888;">{{ $imports->total() }} file</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>File</th>
                        <th>Oleh</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Berhasil</th>
                        <th class="text-center">Gagal</th>
                        <th>Ukuran</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $imp)
                        <tr>
                            <td style="white-space:nowrap;">
                                <div style="font-size:13px;font-weight:500;">{{ $imp->created_at->format('d M Y') }}</div>
                                <div style="font-size:10px;color:#aaa;">{{ $imp->created_at->format('H:i') }}</div>
                            </td>
                            <td>
                                <div style="font-size:12px;font-weight:500;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                    title="{{ $imp->filename }}">
                                    <i class="bi bi-file-earmark-spreadsheet me-1"
                                        style="color:var(--green);"></i>{{ $imp->filename }}
                                </div>
                            </td>
                            <td>
                                <div style="font-size:12px;">{{ $imp->user->name ?? '—' }}</div>
                            </td>
                            <td class="text-center">
                                @php
                                    $sm = [
                                        'completed' => ['Selesai', 'badge-completed', 'bi-check-circle'],
                                        'processing' => ['Diproses', 'badge-pending', 'bi-hourglass-split'],
                                        'pending' => ['Menunggu', 'badge-pending', 'bi-clock'],
                                        'failed' => ['Gagal', 'badge-cancelled', 'bi-x-circle'],
                                    ];
                                    [$sl, $sc, $si] = $sm[$imp->status] ?? ['—', '', 'bi-circle'];
                                @endphp
                                <span class="{{ $sc }}"
                                    style="font-size:11px;padding:3px 9px;border-radius:20px;font-weight:600;">
                                    <i class="bi {{ $si }} me-1"></i>{{ $sl }}
                                </span>
                            </td>
                            <td class="text-center" style="font-weight:600;">{{ number_format($imp->total_rows) }}</td>
                            <td class="text-center" style="color:var(--green);font-weight:700;">
                                {{ number_format($imp->success_rows) }}</td>
                            <td class="text-center">
                                @if ($imp->failed_rows > 0)
                                    <span style="color:var(--red);font-weight:700;">{{ $imp->failed_rows }}</span>
                                @else
                                    <span style="color:#ccc;">—</span>
                                @endif
                            </td>
                            <td style="font-size:12px;color:#888;">{{ $imp->file_size_formatted }}</td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    @if ($imp->failed_rows > 0 && $imp->error_log)
                                        <button type="button" class="btn-edit-soft btn-view-error"
                                            data-log="{{ $imp->error_log }}" title="Lihat Error">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn-danger-soft btn-delete-import"
                                        data-id="{{ $imp->id }}" data-filename="{{ $imp->filename }}"
                                        data-count="{{ $imp->success_rows }}" title="Hapus file & transaksi terkait">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5" style="color:#aaa;">
                                <i class="bi bi-inbox"
                                    style="font-size:30px;display:block;margin-bottom:8px;color:var(--clr-mint);"></i>
                                Belum ada riwayat import.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($imports->hasPages())
            <div class="d-flex align-items-center justify-content-between px-4 py-3"
                style="border-top:1px solid var(--border);">
                <div style="font-size:12px;color:#888;">
                    Menampilkan {{ $imports->firstItem() }}–{{ $imports->lastItem() }} dari {{ $imports->total() }}
                </div>
                {{ $imports->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <form id="deleteImportForm" method="POST" style="display:none;">@csrf @method('DELETE')</form>
@endsection

@push('styles')
    <style>
        #dropZone:hover,
        #dropZone.dragover {
            border-color: var(--clr-teal);
            background: rgba(0, 139, 139, 0.04);
        }
    </style>
@endpush

@push('scripts')
    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('csvFile');
        const importBtn = document.getElementById('importBtn');

        function handleFile(file) {
            if (!file) return;
            if (!file.name.endsWith('.csv') && !file.name.endsWith('.txt')) {
                SwalMomi.fire({
                    icon: 'error',
                    title: 'Format Salah',
                    text: 'Hanya file .csv atau .txt yang diterima.'
                });
                return;
            }
            document.getElementById('selName').textContent = file.name;
            document.getElementById('selSize').textContent = (file.size / 1048576).toFixed(2) + ' MB';
            document.getElementById('dropDefault').style.display = 'none';
            document.getElementById('dropSelected').style.display = 'block';
            dropZone.style.borderColor = 'var(--green)';
            importBtn.disabled = false;
        }

        fileInput.addEventListener('change', () => handleFile(fileInput.files[0]));
        dropZone.addEventListener('dragover', e => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            if (file) {
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
                handleFile(file);
            }
        });

        document.getElementById('importForm').addEventListener('submit', function() {
            importBtn.disabled = true;
            importBtn.innerHTML =
                '<span style="display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.3);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;margin-right:8px;"></span>Memproses...';
            const prog = document.getElementById('importProgress');
            const bar = document.getElementById('progressBar');
            const pct = document.getElementById('pctLabel');
            prog.style.display = 'block';
            let p = 0;
            const t = setInterval(() => {
                p = Math.min(p + Math.random() * 8, 90);
                bar.style.width = p + '%';
                pct.textContent = Math.round(p) + '%';
            }, 300);
            window.addEventListener('beforeunload', () => clearInterval(t));
        });

        document.querySelectorAll('.btn-view-error').forEach(btn => {
            btn.addEventListener('click', function() {
                SwalMomi.fire({
                    icon: 'warning',
                    title: 'Log Error Import',
                    html: `<div style="text-align:left;background:#FFF8F8;border-radius:8px;padding:12px;font-size:12px;font-family:monospace;max-height:300px;overflow-y:auto;color:var(--red);line-height:1.8;">${this.dataset.log.replace(/\n/g,'<br>')}</div>`,
                    width: 600,
                });
            });
        });

        document.querySelectorAll('.btn-delete-import').forEach(btn => {
            btn.addEventListener('click', function() {
                const {
                    id,
                    filename,
                    count
                } = this.dataset;
                SwalMomi.fire({
                    icon: 'warning',
                    title: 'Hapus File Import?',
                    html: `
                <div style="text-align:left;">
                    <p style="color:#666;margin-bottom:12px;">File dan semua transaksi terkait akan dihapus:</p>
                    <div style="background:#F9FFFE;border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:10px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                            <i class="bi bi-file-earmark-spreadsheet" style="color:var(--green);font-size:18px;"></i>
                            <strong style="font-size:13px;">${filename}</strong>
                        </div>
                        <div style="font-size:12px;color:#888;">
                            <i class="bi bi-trash3 me-1" style="color:var(--red);"></i>
                            <strong style="color:var(--red);">${count} transaksi</strong> akan dihapus permanen
                        </div>
                    </div>
                    <p style="font-size:12px;color:var(--red);margin:0;">⚠ Tindakan ini tidak dapat dibatalkan.</p>
                </div>`,
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i>Ya, Hapus Semua',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                    width: 480,
                }).then(r => {
                    if (r.isConfirmed) {
                        const f = document.getElementById('deleteImportForm');
                        f.action = `/csv-imports/${id}`;
                        f.submit();
                    }
                });
            });
        });
    </script>
@endpush
