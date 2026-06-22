@extends('layouts.app')
@section('title', 'Import CSV Transaksi')
@section('page-title', 'Import CSV Transaksi')
@section('page-subtitle', 'Upload data transaksi massal dari file CSV')

@section('topbar-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('transactions.download-template') }}" class="btn-primary-momi">
            <i class="bi bi-download me-1"></i>Download Template CSV
        </a>
        <a href="{{ route('transactions.index') }}" class="btn-ghost-momi">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
@endsection

@section('content')
    {{-- ALERT --}}
    @if (session('success'))
        <div class="alert alert-success"
            style="background:rgba(22,163,74,0.08);border:1px solid rgba(22,163,74,0.2);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
            <div style="font-weight:600;color:var(--green);">{{ session('success') }}</div>
            @if (session('duplicates') && session('duplicates') > 0)
                <div style="font-size:13px;color:#d97706;margin-top:4px;">
                    ⚠ {{ session('duplicates') }} baris duplikat dilewati (tidak diimport)
                </div>
            @endif
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger"
            style="background:rgba(220,38,38,0.08);border:1px solid rgba(220,38,38,0.2);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
            <i class="bi bi-x-circle-fill me-2" style="color:var(--red);"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        {{-- KOLOM KIRI: UPLOAD --}}
        <div class="col-lg-7">
            <div class="card-light">
                <div class="card-header-light">
                    <h3 class="card-title-light"><i class="bi bi-cloud-upload me-2"></i>Upload File CSV</h3>
                    <span style="font-size:11px;color:#888;">Format .csv atau .txt · Maks 10MB</span>
                </div>
                <div class="card-body-light">
                    <form method="POST" action="{{ route('transactions.import.process') }}" enctype="multipart/form-data"
                        id="importForm">
                        @csrf

                        {{-- Drop Zone --}}
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
                                <div id="selRows" style="font-size:11px;color:#888;margin-top:4px;"></div>
                            </div>
                        </div>

                        @error('csv_file')
                            <div
                                style="background:rgba(198,28,140,0.08);border:1px solid rgba(198,28,140,.2);color:var(--clr-magenta);border-radius:8px;padding:10px 12px;font-size:12px;margin-top:10px;">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror

                        {{-- Progress --}}
                        <div id="importProgress" style="display:none;margin-top:14px;">
                            <div
                                style="display:flex;justify-content:space-between;font-size:12px;color:#888;margin-bottom:5px;">
                                <span id="progressLabel">Memproses...</span>
                                <span id="pctLabel">0%</span>
                            </div>
                            <div class="progress-light" style="height:8px;">
                                <div id="progressBar"
                                    style="width:0%;background:var(--clr-teal);transition:width .3s;height:8px;border-radius:4px;">
                                </div>
                            </div>
                            <div id="progressDetail" style="font-size:11px;color:#888;margin-top:5px;text-align:center;">
                            </div>
                        </div>

                        <button type="submit" id="importBtn" class="btn-primary-momi w-100 mt-4"
                            style="padding:12px;justify-content:center;font-size:14px;" disabled>
                            <i class="bi bi-cloud-upload me-2"></i>Mulai Import
                        </button>
                    </form>

                    {{-- Info Penting --}}
                    <div
                        style="margin-top:16px;padding:12px 16px;background:#F0FAFA;border-radius:8px;border:1px solid var(--border);font-size:12px;color:#888;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                            <i class="bi bi-info-circle" style="color:var(--clr-teal);"></i>
                            <strong style="color:#555;">Info Penting:</strong>
                        </div>
                        <ul style="margin:4px 0 0 20px;padding:0;">
                            <li>Data yang sudah ada <strong>TIDAK</strong> akan diimport ulang (otomatis cegah duplikat)
                            </li>
                            <li>Format tanggal: <strong>YYYY-MM-DD</strong> (contoh: 2026-06-01)</li>
                            <li>Pastikan SKU produk sudah terdaftar di database</li>
                            <li>Marketplace tersedia: <strong>shopee, tiktok</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: PANDUAN SINGKAT --}}
        <div class="col-lg-5">
            <div class="card-light">
                <div class="card-header-light">
                    <h3 class="card-title-light"><i class="bi bi-info-circle me-2"></i>Format CSV</h3>
                </div>
                <div class="card-body-light" style="font-size:13px;">
                    <p style="color:#888;margin-bottom:10px;">Baris pertama = header. Kolom wajib:</p>

                    <div style="display:flex;gap:10px;padding:5px 0;border-bottom:1px solid var(--border);">
                        <code
                            style="background:var(--accent-soft);color:var(--clr-teal);padding:2px 8px;border-radius:4px;font-size:11px;white-space:nowrap;">transaction_date</code>
                        <span style="color:#666;font-size:12px;">Format: YYYY-MM-DD</span>
                    </div>
                    <div style="display:flex;gap:10px;padding:5px 0;border-bottom:1px solid var(--border);">
                        <code
                            style="background:var(--accent-soft);color:var(--clr-teal);padding:2px 8px;border-radius:4px;font-size:11px;white-space:nowrap;">marketplace_slug</code>
                        <span style="color:#666;font-size:12px;">shopee / tiktok</span>
                    </div>
                    <div style="display:flex;gap:10px;padding:5px 0;border-bottom:1px solid var(--border);">
                        <code
                            style="background:var(--accent-soft);color:var(--clr-teal);padding:2px 8px;border-radius:4px;font-size:11px;white-space:nowrap;">product_sku</code>
                        <span style="color:#666;font-size:12px;">SKU produk (MOM-001, LM-001, dll)</span>
                    </div>
                    <div style="display:flex;gap:10px;padding:5px 0;border-bottom:1px solid var(--border);">
                        <code
                            style="background:var(--accent-soft);color:var(--clr-teal);padding:2px 8px;border-radius:4px;font-size:11px;white-space:nowrap;">quantity</code>
                        <span style="color:#666;font-size:12px;">Jumlah unit (angka ≥ 1)</span>
                    </div>
                    <div style="display:flex;gap:10px;padding:5px 0;">
                        <code
                            style="background:var(--accent-soft);color:var(--clr-teal);padding:2px 8px;border-radius:4px;font-size:11px;white-space:nowrap;">revenue</code>
                        <span style="color:#666;font-size:12px;">Total pendapatan (angka)</span>
                    </div>

                    <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
                        <a href="{{ route('transactions.download-template') }}" class="btn-ghost-momi"
                            style="font-size:13px;padding:6px 16px;">
                            <i class="bi bi-download me-1"></i>Download Template CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RIWAYAT IMPORT --}}
    <div class="card-light mt-4">
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
                        <th>Status</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Berhasil</th>
                        <th class="text-center">Gagal</th>
                        <th class="text-center">Duplikat</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $imp)
                        <tr>
                            <td style="white-space:nowrap;font-size:13px;">
                                {{ $imp->created_at->format('d M Y H:i') }}
                            </td>
                            <td>
                                <div style="font-size:12px;font-weight:500;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                    title="{{ $imp->filename }}">
                                    <i class="bi bi-file-earmark-spreadsheet me-1" style="color:var(--green);"></i>
                                    {{ $imp->filename }}
                                </div>
                                <div style="font-size:10px;color:#aaa;margin-top:2px;">
                                    <span class="badge"
                                        style="background:#F0FAFA;color:var(--clr-teal);padding:1px 8px;border-radius:10px;font-weight:400;font-size:10px;">
                                        {{ $imp->import_type ?? 'simple' }}
                                    </span>
                                    @if ($imp->duplicate_rows > 0)
                                        <span class="badge"
                                            style="background:#FEF3C7;color:#d97706;padding:1px 8px;border-radius:10px;font-weight:400;font-size:10px;">
                                            ⚠ duplikat
                                        </span>
                                    @endif
                                    @if ($imp->failed_rows > 0)
                                        <span class="badge"
                                            style="background:#FEE2E2;color:#dc2626;padding:1px 8px;border-radius:10px;font-weight:400;font-size:10px;">
                                             error
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="{{ $imp->status_badge_class ?? 'badge-completed' }}"
                                    style="font-size:11px;padding:3px 9px;border-radius:20px;font-weight:600;">
                                    <i class="bi {{ $imp->status_icon ?? 'bi-check-circle' }} me-1"></i>
                                    {{ $imp->status_label ?? 'Selesai' }}
                                </span>
                            </td>
                            <td class="text-center" style="font-weight:600;">{{ number_format($imp->total_rows) }}</td>
                            <td class="text-center" style="color:var(--green);font-weight:700;">
                                {{ number_format($imp->success_rows) }}
                                @if ($imp->total_rows > 0)
                                    <span
                                        style="font-size:9px;color:#888;display:block;">{{ $imp->success_rate ?? 0 }}%</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($imp->failed_rows > 0)
                                    <span style="color:var(--red);font-weight:700;cursor:pointer;"
                                        onclick="showFailedRows('{{ $imp->id }}')"
                                        title="Klik untuk lihat detail error">
                                        {{ $imp->failed_rows }}
                                        <i class="bi bi-info-circle" style="font-size:11px;color:#888;"></i>
                                    </span>
                                @else
                                    <span style="color:#ccc;">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($imp->duplicate_rows > 0)
                                    <span style="color:#d97706;font-weight:600;cursor:pointer;"
                                        onclick="showDuplicateRows('{{ $imp->id }}')"
                                        title="Klik untuk lihat detail duplikat">
                                        {{ $imp->duplicate_rows }}
                                        <i class="bi bi-info-circle" style="font-size:11px;color:#888;"></i>
                                    </span>
                                @else
                                    <span style="color:#ccc;">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    @if ($imp->failed_rows > 0 || $imp->duplicate_rows > 0)
                                        <a href="{{ route('transactions.import.detail', $imp->id) }}"
                                            class="btn-edit-soft" title="Lihat Detail Import">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @else
                                        <span style="color:#ccc;font-size:11px;">—</span>
                                    @endif

                                    {{-- Tombol Delete --}}
                                    <button type="button" class="btn-danger-soft btn-delete-import"
                                        data-id="{{ $imp->id }}" data-filename="{{ $imp->filename }}"
                                        data-count="{{ $imp->success_rows }}" title="Hapus">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5" style="color:#aaa;">
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

    {{-- MODAL DETAIL FAILED ROWS --}}
    <div id="failedModal"
        style="display:none;position:fixed;inset:0;z-index:1050;align-items:center;justify-content:center;">
        <div class="modal-backdrop" style="position:absolute;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);"
            onclick="closeModal('failedModal')"></div>
        <div
            style="position:relative;background:white;border-radius:16px;width:min(700px,95vw);max-height:80vh;overflow:hidden;z-index:1;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div
                style="padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:white;">
                <div>
                    <h4 style="font-size:16px;font-weight:700;color:var(--red);margin:0;">
                        <i class="bi bi-x-circle me-2"></i>Data Gagal
                    </h4>
                    <span id="failedCount" style="font-size:12px;color:#888;"></span>
                </div>
                <button onclick="closeModal('failedModal')"
                    style="background:none;border:none;font-size:20px;color:#888;cursor:pointer;">&times;</button>
            </div>
            <div style="padding:16px 22px;overflow-y:auto;max-height:60vh;">
                <div id="failedList" style="font-size:13px;font-family:monospace;white-space:pre-wrap;"></div>
            </div>
            <div
                style="padding:12px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;background:white;">
                <button onclick="closeModal('failedModal')" class="btn-ghost-momi">Tutup</button>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL DUPLICATE ROWS --}}
    <div id="duplicateModal"
        style="display:none;position:fixed;inset:0;z-index:1050;align-items:center;justify-content:center;">
        <div class="modal-backdrop" style="position:absolute;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);"
            onclick="closeModal('duplicateModal')"></div>
        <div
            style="position:relative;background:white;border-radius:16px;width:min(700px,95vw);max-height:80vh;overflow:hidden;z-index:1;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div
                style="padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:white;">
                <div>
                    <h4 style="font-size:16px;font-weight:700;color:#d97706;margin:0;">
                        <i class="bi bi-exclamation-triangle me-2"></i>Data Duplikat
                    </h4>
                    <span id="duplicateCount" style="font-size:12px;color:#888;"></span>
                </div>
                <button onclick="closeModal('duplicateModal')"
                    style="background:none;border:none;font-size:20px;color:#888;cursor:pointer;">&times;</button>
            </div>
            <div style="padding:16px 22px;overflow-y:auto;max-height:60vh;">
                <div id="duplicateList" style="font-size:13px;font-family:monospace;white-space:pre-wrap;"></div>
            </div>
            <div
                style="padding:12px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;background:white;">
                <button onclick="closeModal('duplicateModal')" class="btn-ghost-momi">Tutup</button>
            </div>
        </div>
    </div>

    <form id="deleteImportForm" method="POST" style="display:none;">@csrf @method('DELETE')</form>
@endsection

@push('scripts')
    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('csvFile');
        const importBtn = document.getElementById('importBtn');

        function handleFile(file) {
            if (!file) return;

            const ext = file.name.split('.').pop().toLowerCase();
            if (!['csv', 'txt'].includes(ext)) {
                SwalMomi.fire({
                    icon: 'error',
                    title: 'Format Salah',
                    text: 'Hanya file .csv atau .txt yang diterima.'
                });
                fileInput.value = '';
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                SwalMomi.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar',
                    text: 'Maksimal ukuran file adalah 10 MB.'
                });
                fileInput.value = '';
                return;
            }

            document.getElementById('selName').textContent = file.name;
            document.getElementById('selSize').textContent = (file.size / 1048576).toFixed(2) + ' MB';

            const reader = new FileReader();
            reader.onload = function(e) {
                const lines = e.target.result.split('\n').filter(line => line.trim() !== '');
                const rowCount = Math.max(0, lines.length - 1);
                document.getElementById('selRows').textContent = '~ ' + rowCount.toLocaleString() + ' baris data';
            };
            reader.readAsText(file);

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

        document.getElementById('importForm').addEventListener('submit', function(e) {
            if (!fileInput.files || !fileInput.files[0]) {
                e.preventDefault();
                SwalMomi.fire({
                    icon: 'warning',
                    title: 'Pilih File',
                    text: 'Silakan pilih file CSV terlebih dahulu.'
                });
                return;
            }

            importBtn.disabled = true;
            importBtn.innerHTML = `
            <span style="display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.3);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;margin-right:8px;"></span>
            Memproses...
        `;

            const prog = document.getElementById('importProgress');
            const bar = document.getElementById('progressBar');
            const pct = document.getElementById('pctLabel');
            prog.style.display = 'block';
            let p = 0;
            const t = setInterval(() => {
                p = Math.min(p + Math.random() * 6, 90);
                bar.style.width = p + '%';
                pct.textContent = Math.round(p) + '%';
            }, 300);
            window.addEventListener('beforeunload', () => clearInterval(t));
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
                    title: 'Hapus Import?',
                    html: `
                    File <strong>${filename}</strong> dan ${count} transaksi akan dihapus.
                    <br><small style="color:#888;">Tindakan ini tidak dapat dibatalkan.</small>
                `,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                }).then(r => {
                    if (r.isConfirmed) {
                        const f = document.getElementById('deleteImportForm');
                        f.action = `/csv-imports/${id}`;
                        f.submit();
                    }
                });
            });
        });

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
            document.body.style.overflow = '';
        }

        function showFailedRows(importId) {
            // Ambil data dari server
            fetch(`/transactions/import/${importId}/detail`)
                .then(response => response.json())
                .then(data => {
                    const modal = document.getElementById('failedModal');
                    const list = document.getElementById('failedList');
                    const count = document.getElementById('failedCount');

                    if (data.failed_rows && data.failed_rows.length > 0) {
                        list.innerHTML = data.failed_rows.map(row =>
                            `<div style="padding:6px 10px;border-bottom:1px solid #f0f0f0;color:#555;">
                        <span style="color:var(--red);font-weight:600;">Baris ${row.row}:</span> 
                        ${row.error}
                    </div>`
                        ).join('');
                        count.textContent = `Total ${data.failed_rows.length} data gagal`;
                    } else {
                        list.innerHTML =
                            '<div style="color:#888;text-align:center;padding:20px;">Tidak ada data gagal</div>';
                        count.textContent = '';
                    }

                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                })
                .catch(() => {
                    SwalMomi.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat detail import.'
                    });
                });
        }

        function showDuplicateRows(importId) {
            fetch(`/transactions/import/${importId}/detail`)
                .then(response => response.json())
                .then(data => {
                    const modal = document.getElementById('duplicateModal');
                    const list = document.getElementById('duplicateList');
                    const count = document.getElementById('duplicateCount');

                    if (data.duplicate_rows && data.duplicate_rows.length > 0) {
                        list.innerHTML = data.duplicate_rows.map(row =>
                            `<div style="padding:6px 10px;border-bottom:1px solid #f0f0f0;color:#555;">
                        <span style="color:#d97706;font-weight:600;">Baris ${row}:</span> 
                        Data sudah ada (duplikat)
                    </div>`
                        ).join('');
                        count.textContent = `Total ${data.duplicate_rows.length} data duplikat`;
                    } else {
                        list.innerHTML =
                            '<div style="color:#888;text-align:center;padding:20px;">Tidak ada data duplikat</div>';
                        count.textContent = '';
                    }

                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                })
                .catch(() => {
                    SwalMomi.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat detail import.'
                    });
                });
        }

        // Tutup modal dengan ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                    const modal = backdrop.closest('[id$="Modal"]');
                    if (modal && modal.style.display === 'flex') {
                        modal.style.display = 'none';
                        document.body.style.overflow = '';
                    }
                });
            }
        });
    </script>
@endpush
