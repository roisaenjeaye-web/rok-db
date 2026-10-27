<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Operator - RoK Joki</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e2229 0%, #111418 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }
        .login-card {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            width: 100%;
            max-width: 440px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .login-header {
            background-color: #1a1e24;
            color: #ffffff;
            padding: 30px 25px 25px;
            text-align: center;
            border-bottom: 3px solid #0d6efd;
        }
        .brand {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .brand span {
            color: #0d6efd;
        }
        .form-control {
            border-radius: 8px;
            padding: 11px 15px;
            font-size: 14px;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
            border-color: #0d6efd;
        }
        .input-group-text {
            border-radius: 8px 0 0 8px;
            background-color: #f8f9fa;
        }
        .input-group .form-control {
            border-radius: 0 8px 8px 0;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.35);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="brand"><i class="bi bi-shield-shaded text-primary"></i> RoK<span>Joki</span></div>
        <p class="text-white-50 small mb-0">Masuk untuk mencatat log panen & kelola kingdom</p>
    </div>

    <div class="p-4 p-md-5">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm small py-2">
                <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm small py-2">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">EMAIL OPERATOR</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="nama@email.com" value="{{ old('email') }}" required autofocus autocomplete="email">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">PASSWORD</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key text-muted"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                    <label class="form-check-label small text-muted" for="remember">
                        Ingat Saya
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 shadow-sm mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
            </button>

            <div class="text-center pt-2 border-top">
                <span class="small text-muted">Belum punya akun operator?</span>
                <a href="{{ route('register') }}" class="small fw-bold text-primary text-decoration-none ms-1">
                    Daftar di sini
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
