<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Momiasi ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            /* Momiasi Brand Colors */
            --clr-teal: #008B8B;
            --clr-teal-light: #46B8A7;
            --clr-mint: #B8E8DD;
            --clr-mint-soft: #CDEFE7;
            --clr-logo: #46B8A7;
            --clr-magenta: #C61C8C;
            --clr-deep-mag: #D000A8;
            --clr-white: #FFFFFF;
            --clr-bg: #F5F5F5;
            --clr-text: #666666;
            --clr-text-dark: #333333;

            /* UI Vars */
            --sidebar-w: 240px;
            --bg-sidebar: #008B8B;
            --bg-card: #FFFFFF;
            --bg-card2: #F5F5F5;
            --border: #E0F0EE;
            --accent: #008B8B;
            --accent-soft: rgba(0, 139, 139, 0.12);
            --accent-glow: rgba(0, 139, 139, 0.2);
            --shopee: #EE4D2D;
            --shopee-soft: rgba(238, 77, 45, 0.1);
            --tiktok: #010101;
            --tiktok-soft: rgba(1, 1, 1, 0.08);
            --green: #16a34a;
            --green-soft: rgba(22, 163, 74, 0.1);
            --red: #dc2626;
            --red-soft: rgba(220, 38, 38, 0.1);
            --yellow: #d97706;
            --yellow-soft: rgba(217, 119, 6, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--clr-bg);
            color: var(--clr-text-dark);
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--bg-sidebar);
            display: flex;
            flex-direction: column;
            z-index: 100;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 20px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-logo-wrap {
            width: 42px;
            height: 42px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .brand-logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-logo-text .logo-main {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 16px;
            font-weight: 800;
            color: white;
            line-height: 1.1;
        }

        .brand-logo-text .logo-sub {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.7);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar-nav {
            padding: 12px 10px;
            flex: 1;
        }

        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255, 255, 255, 0.5);
            padding: 10px 8px 5px;
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 11px;
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.15s;
            margin-bottom: 2px;
        }

        .nav-item-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .nav-item-link.active {
            background: white;
            color: var(--clr-teal);
            font-weight: 600;
        }

        .nav-item-link .nav-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.15);
            flex-shrink: 0;
        }

        .nav-item-link.active .nav-icon {
            background: var(--accent-soft);
            color: var(--clr-teal);
        }

        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px 10px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--clr-magenta);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }

        .user-name {
            font-size: 12px;
            font-weight: 600;
            color: white;
        }

        .user-role {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.6);
            text-transform: capitalize;
        }

        /* ── Main ── */
        .main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 6px rgba(0, 139, 139, 0.08);
        }

        .topbar-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 17px;
            font-weight: 700;
            color: var(--clr-teal);
        }

        .topbar-subtitle {
            font-size: 12px;
            color: var(--clr-text);
            margin-top: 1px;
        }

        .page-body {
            padding: 20px 24px;
            flex: 1;
        }

        /* ── Cards ── */
        .card-light {
            background: white;
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }

        .card-header-light {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-body-light {
            padding: 18px;
        }

        .card-title-light {
            font-size: 14px;
            font-weight: 600;
            color: var(--clr-teal);
            margin: 0;
        }

        /* ── Stat cards ── */
        .stat-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 139, 139, 0.12);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .stat-card.teal::before {
            background: var(--clr-teal);
        }

        .stat-card.magenta::before {
            background: var(--clr-magenta);
        }

        .stat-card.green::before {
            background: var(--green);
        }

        .stat-card.yellow::before {
            background: var(--yellow);
        }

        .stat-card.shopee::before {
            background: var(--shopee);
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            margin-bottom: 12px;
        }

        .stat-icon.teal {
            background: var(--accent-soft);
            color: var(--clr-teal);
        }

        .stat-icon.magenta {
            background: rgba(198, 28, 140, 0.1);
            color: var(--clr-magenta);
        }

        .stat-icon.green {
            background: var(--green-soft);
            color: var(--green);
        }

        .stat-icon.yellow {
            background: var(--yellow-soft);
            color: var(--yellow);
        }

        .stat-icon.shopee {
            background: var(--shopee-soft);
            color: var(--shopee);
        }

        .stat-label {
            font-size: 11px;
            color: var(--clr-text);
            font-weight: 500;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--clr-teal);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .stat-sub {
            font-size: 11px;
            color: var(--clr-text);
            margin-top: 4px;
        }

        .badge-up {
            background: var(--green-soft);
            color: var(--green);
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-down {
            background: var(--red-soft);
            color: var(--red);
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 20px;
            font-weight: 600;
        }

        /* ── Tables ── */
        .table-custom {
            color: var(--clr-text-dark);
            width: 100%;
            border-collapse: collapse;
        }

        .table-custom thead th {
            background: #F0FAFA;
            color: var(--clr-teal);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 2px solid var(--border);
            padding: 11px 14px;
            white-space: nowrap;
        }

        .table-custom tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.12s;
        }

        .table-custom tbody tr:hover {
            background: #F0FAFA;
        }

        .table-custom tbody td {
            padding: 11px 14px;
            font-size: 13px;
            vertical-align: middle;
        }

        .table-custom tbody tr:last-child {
            border-bottom: none;
        }

        /* ── Badges ── */
        .badge-shopee {
            background: var(--shopee-soft);
            color: var(--shopee);
            border: 1px solid rgba(238, 77, 45, .25);
        }

        .badge-tiktok {
            background: rgba(1, 1, 1, 0.06);
            color: #333;
            border: 1px solid rgba(1, 1, 1, 0.15);
        }

        .badge-completed {
            background: var(--green-soft);
            color: var(--green);
        }

        .badge-cancelled {
            background: var(--red-soft);
            color: var(--red);
        }

        .badge-returned {
            background: var(--red-soft);
            color: var(--red);
        }

        .badge-pending {
            background: var(--yellow-soft);
            color: var(--yellow);
        }

        /* ── Forms ── */
        .form-control-light,
        .form-select-light {
            background: white;
            border: 1px solid #D0E8E8;
            color: var(--clr-text-dark);
            border-radius: 8px;
            padding: 8px 11px;
            font-size: 13px;
            width: 100%;
            transition: border-color 0.15s;
            font-family: 'Inter', sans-serif;
        }

        .form-control-light:focus,
        .form-select-light:focus {
            border-color: var(--clr-teal);
            outline: none;
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .form-control-light::placeholder {
            color: #aaa;
        }

        .form-label-light {
            font-size: 11px;
            font-weight: 600;
            color: var(--clr-teal);
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
        }

        /* ── Buttons ── */
        .btn-primary-momi {
            background: var(--clr-teal);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .btn-primary-momi:hover {
            background: #007070;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--accent-glow);
        }

        .btn-magenta-momi {
            background: var(--clr-magenta);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .btn-magenta-momi:hover {
            background: #a01575;
            color: white;
            transform: translateY(-1px);
        }

        .btn-ghost-momi {
            background: white;
            color: var(--clr-teal);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .btn-ghost-momi:hover {
            border-color: var(--clr-teal);
            color: var(--clr-teal);
            background: var(--accent-soft);
        }

        .btn-danger-soft {
            background: var(--red-soft);
            color: var(--red);
            border: 1px solid rgba(220, 38, 38, .2);
            border-radius: 8px;
            padding: 5px 11px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-danger-soft:hover {
            background: rgba(220, 38, 38, .18);
        }

        .btn-edit-soft {
            background: var(--accent-soft);
            color: var(--clr-teal);
            border: 1px solid rgba(0, 139, 139, .2);
            border-radius: 8px;
            padding: 5px 11px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-edit-soft:hover {
            background: rgba(0, 139, 139, .18);
        }

        /* ── Progress ── */
        .progress-light {
            background: var(--clr-mint);
            border-radius: 4px;
            height: 6px;
        }

        .progress-light .progress-bar {
            border-radius: 4px;
        }

        /* ── Pagination ── */
        .pagination {
            --bs-pagination-bg: white;
            --bs-pagination-border-color: var(--border);
            --bs-pagination-color: var(--clr-teal);
            --bs-pagination-hover-bg: var(--accent-soft);
            --bs-pagination-hover-color: var(--clr-teal);
            --bs-pagination-active-bg: var(--clr-teal);
            --bs-pagination-active-border-color: var(--clr-teal);
        }

        /* ── SweetAlert2 light theme ── */
        .swal2-popup.swal-momi-popup {
            border-radius: 16px !important;
            font-family: 'Inter', sans-serif !important;
            border: 1px solid var(--border) !important;
        }

        .swal-btn-confirm {
            border-radius: 8px !important;
            font-weight: 600 !important;
            background: var(--clr-teal) !important;
        }

        .swal-btn-cancel {
            border-radius: 8px !important;
            background: #F5F5F5 !important;
            color: #666 !important;
            border: 1px solid #ddd !important;
        }

        /* ── Sortable th ── */
        th.sortable {
            cursor: pointer;
            user-select: none;
        }

        th.sortable:hover {
            color: var(--clr-magenta);
        }

        /* ── Misc ── */
        .mp-stat-label {
            font-size: 11px;
            color: var(--clr-text);
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .mp-stat-value {
            font-size: 15px;
            font-weight: 700;
            color: var(--clr-teal);
        }

        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: var(--clr-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--clr-mint);
            border-radius: 3px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo-wrap">
                {{-- Ganti src dengan path logo kamu --}}
                <img src="{{ asset('images/logo.png') }}" alt="Momiasi"
                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div
                    style="display:none;width:100%;height:100%;background:white;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:var(--clr-teal);">
                    M</div>
            </div>
            <div class="brand-logo-text">
                <div class="logo-main">Momiasi</div>
                <div class="logo-sub">ERP Dashboard</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu Utama</div>
            <a href="{{ route('dashboard') }}"
                class="nav-item-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-grid-1x2"></i></div>
                <span>Dashboard</span>
            </a>
            <div class="nav-section-label" style="margin-top:6px;">Transaksi</div>
            <a href="{{ route('transactions.index') }}"
                class="nav-item-link {{ request()->routeIs('transactions.index') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-receipt"></i></div>
                <span>Daftar Transaksi</span>
            </a>
            <a href="{{ route('transactions.create') }}"
                class="nav-item-link {{ request()->routeIs('transactions.create') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-plus-circle"></i></div>
                <span>Tambah Transaksi</span>
            </a>
            <a href="{{ route('transactions.import') }}"
                class="nav-item-link {{ request()->routeIs('transactions.import') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-file-earmark-arrow-up"></i></div>
                <span>Import CSV</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div style="flex:1;overflow:hidden;">
                    <div class="user-name text-truncate">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ auth()->user()->role }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        style="background:none;border:none;color:rgba(255,255,255,0.7);cursor:pointer;padding:0;"
                        title="Keluar">
                        <i class="bi bi-box-arrow-right" style="font-size:17px;"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main -->
    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                <div class="topbar-subtitle">@yield('page-subtitle', 'Momiasi ERP System')</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @yield('topbar-actions')
                <div style="width:1px;height:28px;background:var(--border);margin:0 4px;"></div>
                <div style="font-size:12px;color:var(--clr-text);">
                    <i class="bi bi-calendar3 me-1" style="color:var(--clr-teal);"></i>{{ now()->format('d M Y') }}
                </div>
            </div>
        </div>

        <div class="page-body">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <script>
        window.SwalMomi = Swal.mixin({
            customClass: {
                popup: 'swal-momi-popup',
                confirmButton: 'swal-btn-confirm',
                cancelButton: 'swal-btn-cancel',
            },
            buttonsStyling: false,
        });

        @if (session('success'))
            SwalMomi.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: @json(session('success')),
                toast: true,
                position: 'top-end',
                timer: 4000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        @endif
        @if (session('error'))
            SwalMomi.fire({
                icon: 'error',
                title: 'Gagal!',
                text: @json(session('error')),
                toast: true,
                position: 'top-end',
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        @endif

        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#666666';
    </script>

    @stack('scripts')
</body>

</html>
