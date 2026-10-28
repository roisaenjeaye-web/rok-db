<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Stok KD #{{ $kingdom->kd_number }} - RoK Joki Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        .sidebar { min-height: 100vh; width: 250px; background-color: #212529; position: fixed; top: 0; left: 0; padding-top: 20px; box-shadow: 2px 0 5px rgba(0,0,0,0.1); display: flex; flex-direction: column; justify-content: space-between; z-index: 100; }
        .sidebar .brand { color: #fff; font-size: 22px; font-weight: 700; text-align: center; margin-bottom: 30px; letter-spacing: 1px; }
        .sidebar .brand span { color: #0d6efd; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; font-weight: 500; transition: 0.3s; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background-color: #0d6efd; color: #fff; border-radius: 0 25px 25px 0; margin-right: 20px; }
        .sidebar a i { margin-right: 10px; font-size: 1.1rem; }
        .content { margin-left: 250px; padding: 30px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); margin-bottom: 20px; }
        .card-header { background-color: #fff; border-bottom: 1px solid #edf2f9; font-weight: 600; border-radius: 10px 10px 0 0 !important; padding: 15px 20px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        <div class="brand"><i class="bi bi-shield-shaded"></i> RoK<span>Joki</span></div>
        <a href="/stok" class="active"><i class="bi bi-box-seam"></i> Dashboard Stok</a>
        <a href="/kalkulator"><i class="bi bi-calculator"></i> Kalkulator Pajak</a>
        <a href="/riwayat"><i class="bi bi-journal-text"></i> Buku Kas</a>
        <a href="/akun"><i class="bi bi-people"></i> Manajemen Ternak</a>
        <a href="/jadwal"><i class="bi bi-alarm"></i> Jadwal Farm (KD)</a>
        @if(auth()->check() && auth()->user()->isAdmin())
        <a href="/admin" style="color:#ffc107;"><i class="bi bi-shield-lock"></i> Panel Admin</a>
        @endif
    </div>

    @auth
    <div class="p-3 border-top border-secondary-subtle">
        <div class="d-flex align-items-center mb-2">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 34px; height: 34px; font-size: 14px;">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="text-truncate">
                <div class="text-white fw-bold small text-truncate">{{ auth()->user()->name }}</div>
                <div class="text-secondary text-truncate" style="font-size: 11px;">{{ auth()->user()->email }}</div>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger w-100 py-1" style="font-size: 11px;">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
        </form>
    </div>
    @endauth
</div>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark m-0">Edit Stok Kingdom <span class="text-primary">#{{ $kingdom->kd_number }}</span></h3>
        <a href="/stok" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Terjadi kesalahan input:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header text-primary"><i class="bi bi-pencil-square me-1"></i> Form Update Stok Farm</div>
                <div class="card-body p-4">
                    <form action="/stok/{{ $kingdom->id }}/update" method="POST">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">NOMOR KINGDOM (PERMANEN)</label>
                                <input type="text" class="form-control bg-light" value="#{{ $kingdom->kd_number }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">⏱️ WAKTU JEDA PANEN (JAM)</label>
                                <div class="input-group">
                                    <input type="number" step="0.5" min="0.5" max="48" class="form-control fw-bold" name="cooldown_hours" value="{{ old('cooldown_hours', $kingdom->cooldown_hours ?? 4.0) }}" required>
                                    <span class="input-group-text">Jam Sekali</span>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">🌽 STOK FOOD (M)</label>
                                <input type="number" step="0.1" class="form-control" name="food" value="{{ old('food', $kingdom->food) }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">🪵 STOK WOOD (M)</label>
                                <input type="number" step="0.1" class="form-control" name="wood" value="{{ old('wood', $kingdom->wood) }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">🪨 STOK STONE (M)</label>
                                <input type="number" step="0.1" class="form-control" name="stone" value="{{ old('stone', $kingdom->stone) }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">🪙 STOK GOLD (M)</label>
                                <input type="number" step="0.1" class="form-control" name="gold" value="{{ old('gold', $kingdom->gold) }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2"><i class="bi bi-save me-2"></i>Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>