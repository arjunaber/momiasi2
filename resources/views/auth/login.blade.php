<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Momiasi ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --teal: #008B8B;
            --teal-light: #46B8A7;
            --mint: #B8E8DD;
            --mint-soft: #CDEFE7;
            --magenta: #C61C8C;
            --deep-mag: #D000A8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #F5F5F5;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(145deg, var(--teal) 0%, var(--teal-light) 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .login-left::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(198, 28, 140, 0.15);
            pointer-events: none;
        }

        .brand-area {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .brand-logo-box {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .brand-logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: white;
            margin-bottom: 4px;
        }

        .brand-name span {
            color: var(--mint);
        }

        .brand-tagline {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
        }

        .feature-list {
            width: 100%;
            max-width: 320px;
            position: relative;
            z-index: 1;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 8px;
            backdrop-filter: blur(4px);
        }

        .feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
            flex-shrink: 0;
        }

        .feature-title {
            font-size: 13px;
            font-weight: 600;
            color: white;
        }

        .feature-desc {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.75);
            margin-top: 1px;
        }

        .mp-chips {
            display: flex;
            gap: 8px;
            margin-top: 32px;
            position: relative;
            z-index: 1;
        }

        .mp-chip {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .mp-chip.shopee {
            background: rgba(238, 77, 45, .2);
            color: #ff7a5c;
            border: 1px solid rgba(238, 77, 45, .3);
        }

        .mp-chip.tiktok {
            background: rgba(255, 255, 255, .15);
            color: white;
            border: 1px solid rgba(255, 255, 255, .25);
        }

        /* Right panel */
        .login-right {
            width: 440px;
            flex-shrink: 0;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .login-form-wrap {
            width: 100%;
            max-width: 360px;
        }

        .form-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #222;
            margin-bottom: 4px;
        }

        .form-sub {
            font-size: 13px;
            color: #666;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #008B8B;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 15px;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            background: #F9FFFE;
            border: 1.5px solid #D0EEEE;
            color: #333;
            border-radius: 10px;
            padding: 10px 11px 10px 36px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all .2s;
            outline: none;
        }

        .form-input:focus {
            border-color: #008B8B;
            box-shadow: 0 0 0 3px rgba(0, 139, 139, 0.12);
            background: white;
        }

        .form-input::placeholder {
            color: #bbb;
        }

        .err-msg {
            font-size: 12px;
            color: #C61C8C;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .remember-row input[type=checkbox] {
            accent-color: #008B8B;
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        .remember-row label {
            font-size: 13px;
            color: #666;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #008B8B, #46B8A7);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #007070, #3aa596);
            box-shadow: 0 6px 20px rgba(0, 139, 139, 0.3);
            transform: translateY(-1px);
        }

        .demo-box {
            margin-top: 20px;
            padding: 14px;
            background: #F9FFFE;
            border: 1px solid #D0EEEE;
            border-radius: 10px;
        }

        .demo-label {
            font-size: 10px;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .demo-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #666;
            padding: 3px 0;
        }

        .demo-row span {
            color: #333;
            font-weight: 500;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 18px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #eee;
        }

        .divider span {
            font-size: 11px;
            color: #aaa;
            white-space: nowrap;
        }

        @media(max-width:768px) {
            .login-left {
                display: none;
            }

            body {
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <div class="login-left">
        <div class="brand-area">
            <div class="brand-logo-box">
                <img src="{{ asset('images/logo.png') }}" alt="Momiasi" onerror="this.style.display='none'">
            </div>
            <div class="brand-name">momi<span>asi</span></div>
            <div class="brand-tagline">ERP Reporting System</div>
        </div>

        <div class="feature-list">
            <div class="feature-item">
                <div class="feature-icon"><i class="bi bi-bar-chart-line"></i></div>
                <div>
                    <div class="feature-title">Dashboard Analitik</div>
                    <div class="feature-desc">Pantau performa penjualan real-time</div>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="bi bi-file-earmark-arrow-up"></i></div>
                <div>
                    <div class="feature-title">Import Data CSV</div>
                    <div class="feature-desc">Upload transaksi dari marketplace</div>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="bi bi-arrow-left-right"></i></div>
                <div>
                    <div class="feature-title">Perbandingan Platform</div>
                    <div class="feature-desc">Shopee vs TikTok Shop dalam satu view</div>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="bi bi-lightbulb"></i></div>
                <div>
                    <div class="feature-title">Insight Otomatis</div>
                    <div class="feature-desc">Rekomendasi bisnis berbasis data</div>
                </div>
            </div>
        </div>

        <div class="mp-chips">
            <div class="mp-chip shopee"><i class="bi bi-shop"></i> Shopee</div>
            <div class="mp-chip tiktok"><i class="bi bi-tiktok"></i> TikTok Shop</div>
        </div>
    </div>

    <div class="login-right">
        <div class="login-form-wrap">
            <h1 class="form-heading">Masuk ke ERP</h1>
            <p class="form-sub">Gunakan akun yang diberikan administrator</p>

            @if ($errors->has('email'))
                <div
                    style="background:#FFF0F8;border:1px solid rgba(198,28,140,.25);color:#C61C8C;border-radius:10px;padding:11px 14px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                    <i class="bi bi-exclamation-circle-fill"></i>{{ $errors->first('email') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" id="email" name="email" class="form-input"
                            value="{{ old('email') }}" placeholder="admin@momiasi.com" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-input" placeholder="••••••••"
                            required>
                    </div>
                </div>

                <div class="remember-row">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya selama 30 hari</label>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk ke Sistem
                </button>
            </form>

            <div class="demo-box">
                <div class="demo-label">Akun Demo</div>
                <div class="demo-row">Admin <span>admin@momiasi.com / password</span></div>
                <div class="demo-row">Manager <span>manager@momiasi.com / password</span></div>
            </div>
        </div>
    </div>

</body>

</html>
