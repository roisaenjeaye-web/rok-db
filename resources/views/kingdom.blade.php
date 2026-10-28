<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Stok & Gaji Joki - RoK Joki Admin</title>
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/mobile.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }
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

<!-- Sidebar Menu -->
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

<!-- Main Content -->
@php $isAdmin = $isAdmin ?? false; @endphp
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">Dashboard Stok & Resume Gaji</h3>
            <p class="text-muted small mb-0">Semua KD otomatis sinkron dengan data akun ternak & pencatatan gaji joki manual</p>
        </div>
        <span class="badge bg-primary px-3 py-2 fs-6"><i class="bi bi-globe me-1"></i> Total: {{ $kingdoms->count() }} KD Aktif</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-1 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Card 1: Resume Gaji & Log Macul Joki (Simple List) -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3 gap-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <span class="fs-5 fw-bold text-dark"><i class="bi bi-wallet2 text-success me-2"></i>Resume Gaji & Log Macul Joki</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    Total Macul: {{ $totalOverallFarm }}x
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                @if($isAdmin)
                <button type="button" class="btn btn-sm btn-success fw-bold px-3" data-bs-toggle="modal" data-bs-target="#modalTambahGaji">
                    <i class="bi bi-plus-circle me-1"></i> Input Gaji Manual
                </button>
                @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 11px;" title="Fitur ini hanya untuk Admin">
                    <i class="bi bi-lock-fill me-1"></i> Input Gaji (Admin Only)
                </span>
                @endif
            </div>
        </div>

        <div class="card-body p-3">
            <!-- 3 Ringkasan Utama -->
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <div class="bg-light p-2 rounded border text-center">
                        <span class="text-muted small fw-bold">TOTAL OPERATOR JOKI</span>
                        <div class="fs-5 fw-bold text-dark mt-1">{{ count($resumeJoki) }} Orang</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light p-2 rounded border text-center">
                        <span class="text-muted small fw-bold">TOTAL PANEN / MACUL</span>
                        <div class="fs-5 fw-bold text-primary mt-1">{{ $totalOverallFarm }}x Selesai</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light p-2 rounded border text-center">
                        <span class="text-muted small fw-bold">TOTAL GAJI TERCATAT</span>
                        <div class="fs-5 fw-bold text-success mt-1">Rp {{ number_format($totalOverallSalary, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <!-- List Simpel per User/Joki -->
            <div class="d-flex flex-column gap-2 mb-3">
                @forelse($resumeJoki as $joki)
                    <div class="p-3 bg-light rounded border d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-xs" style="width: 42px; height: 42px; font-size: 16px;">
                                {{ strtoupper(substr($joki['name'], 0, 1)) }}
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold fs-6 text-dark">{{ $joki['name'] }}</span>
                                    <span class="badge bg-primary-subtle text-primary border px-2">
                                        <i class="bi bi-hammer me-1"></i>{{ $joki['total_farm'] }}x Macul
                                    </span>
                                </div>
                                <div class="text-muted small mt-1">
                                    @if(count($joki['kd_breakdown']) > 0)
                                        <span class="text-secondary fw-semibold">Rincian KD:</span>
                                        @foreach($joki['kd_breakdown'] as $kdStat)
                                            <span class="badge bg-white text-dark border me-1">
                                                KD #{{ $kdStat['kd_number'] }}: <b class="text-primary">{{ $kdStat['count'] }}x</b>
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted fst-italic" style="font-size: 11px;">Belum ada riwayat macul di KD manapun</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <div class="text-muted small" style="font-size: 11px;">Total Gaji Terinput</div>
                            <div class="fs-5 fw-bold text-success">
                                Rp {{ number_format($joki['total_salary'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3 text-muted small">
                        Belum ada data joki yang tercatat.
                    </div>
                @endforelse
            </div>

            <!-- Tabel Riwayat Catatan Gaji (Bisa Edit & Delete) -->
            <div class="border rounded bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark small">
                        <i class="bi bi-journal-text text-primary me-1"></i> Riwayat Input Gaji (Bisa Edit & Hapus)
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary border">{{ $salaries->count() }} Data</span>
                </div>

                @if($salaries->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12px;">
                            <thead class="table-light">
                                <tr>
                                    <th>TANGGAL</th>
                                    <th>OPERATOR JOKI</th>
                                    <th>KETERANGAN</th>
                                    <th class="text-end">JUMLAH GAJI</th>
                                    <th class="text-center" style="width: 100px;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salaries as $sal)
                                    <tr>
                                        <td>{{ $sal->date ? $sal->date->format('d M Y') : $sal->created_at->format('d M Y') }}</td>
                                        <td><b class="text-primary">{{ $sal->worker_name }}</b></td>
                                        <td>{{ $sal->notes ?: '-' }}</td>
                                        <td class="text-end fw-bold text-success">Rp {{ number_format($sal->amount, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                @if($isAdmin)
                                                <!-- Tombol Edit Modal -->
                                                <button type="button" class="btn btn-xs btn-outline-warning text-dark px-2" data-bs-toggle="modal" data-bs-target="#modalEditGaji{{ $sal->id }}" title="Edit Gaji">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>

                                                <!-- Tombol Hapus -->
                                                <form action="/gaji/{{ $sal->id }}/hapus" method="POST" class="m-0 d-inline" onsubmit="return confirm('Hapus catatan gaji ini?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-outline-danger px-2" title="Hapus Gaji">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </form>
                                                @else
                                                <span class="text-muted" style="font-size: 11px;" title="Hanya Admin yang bisa edit/hapus gaji">
                                                    <i class="bi bi-lock-fill"></i>
                                                </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal Edit Gaji untuk baris ini -->
                                    <div class="modal fade" id="modalEditGaji{{ $sal->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="/gaji/{{ $sal->id }}/update" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-1"></i> Edit Catatan Gaji</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-3">
                                                        <div class="mb-3">
                                                            <label class="form-label text-muted small fw-bold">PILIH OPERATOR JOKI</label>
                                                            <select class="form-select" name="worker_name" required>
                                                                @foreach($allWorkers as $w)
                                                                    <option value="{{ $w }}" {{ $sal->worker_name == $w ? 'selected' : '' }}>{{ $w }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label text-muted small fw-bold">NOMINAL GAJI (RP)</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text bg-light">Rp</span>
                                                                <input type="number" class="form-control" name="amount" value="{{ (int)$sal->amount }}" required min="0">
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label text-muted small fw-bold">KETERANGAN / CATATAN (OPSIONAL)</label>
                                                            <input type="text" class="form-control" name="notes" value="{{ $sal->notes }}" placeholder="Misal: Gaji Pokok, Bonus, dll">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label text-muted small fw-bold">TANGGAL</label>
                                                            <input type="date" class="form-control" name="date" value="{{ $sal->date ? $sal->date->format('Y-m-d') : $sal->created_at->format('Y-m-d') }}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer p-2">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-sm btn-warning fw-bold text-dark"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-3 text-muted small">
                        Belum ada catatan input gaji. Klik tombol <b>"Input Gaji Manual"</b> di atas untuk menambahkan.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Row: Database Stok Farm (Auto Sinkron dari Akun & Char) -->
    <div class="row">
        <!-- Kolom Kiri: Form Tambah / Update KD Manual (Opsional) -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header text-primary d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-plus-circle me-1"></i> Input / Tambah KD Manual</span>
                    <span class="badge bg-light text-muted border">Opsional</span>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info border-0 p-2 mb-3 small" style="font-size: 11px;">
                        <i class="bi bi-info-circle-fill me-1"></i> <b>Info:</b> Semua KD dari akun ternak otomatis tampil di tabel sebelah kanan tanpa perlu diinput manual. Formulir ini hanya jika ingin menambah KD baru secara mandiri.
                    </div>
                    <form action="/stok/tambah" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">NOMOR KD</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">#</span>
                                <input type="number" class="form-control" name="kd_number" required placeholder="Contoh: 3124">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">⏱️ WAKTU JEDA MACUL (JAM)</label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0.5" max="48" class="form-control" name="cooldown_hours" value="4.0" placeholder="4.0">
                                <span class="input-group-text">Jam Sekali</span>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">🌽 FOOD (M)</label>
                                <input type="number" step="0.1" class="form-control" name="food" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">🪵 WOOD (M)</label>
                                <input type="number" step="0.1" class="form-control" name="wood" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">🪨 STONE (M)</label>
                                <input type="number" step="0.1" class="form-control" name="stone" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">🪙 GOLD (M)</label>
                                <input type="number" step="0.1" class="form-control" name="gold" value="0">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-save me-2"></i>Simpan Stok</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tabel Data Stok Kingdom (Semua KD dari Akun & Char) -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white text-dark d-flex justify-content-between align-items-center py-3">
                    <span class="fs-6 fw-bold"><i class="bi bi-table text-primary me-2"></i>Database Stok Farm (Sinkron Otomatis)</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-check-circle me-1"></i>Semua KD Terdaftar
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle m-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th class="ps-4">KINGDOM</th>
                                    <th>JEDA MACUL</th>
                                    <th>FOOD</th>
                                    <th>WOOD</th>
                                    <th>STONE</th>
                                    <th>GOLD</th>
                                    <th class="text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse ($kingdoms as $kd)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3">#{{ $kd->kd_number }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold">
                                            <i class="bi bi-clock-history me-1"></i>{{ $kd->cooldown_hours ?? 4 }} Jam
                                        </span>
                                    </td>
                                    <td class="fw-medium text-success">{{ $kd->food }}M</td>
                                    <td class="fw-medium text-success">{{ $kd->wood }}M</td>
                                    <td class="fw-medium text-secondary">{{ $kd->stone }}M</td>
                                    <td class="fw-medium text-warning">{{ $kd->gold }}M</td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="/stok/{{ $kd->id }}/edit" class="btn btn-sm btn-outline-warning text-dark" title="Edit Stok & Jeda"><i class="bi bi-pencil-fill"></i></a>
                                            <form action="/stok/{{ $kd->id }}/hapus" method="POST" onsubmit="return confirm('Hapus KD #{{ $kd->kd_number }} beserta stoknya?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash-fill"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i> Belum ada data Kingdom.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Gaji Manual -->
<div class="modal fade" id="modalTambahGaji" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/gaji/simpan" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle text-success me-1"></i> Input Catatan Gaji Joki</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">PILIH OPERATOR JOKI</label>
                        <select class="form-select" name="worker_name" required>
                            @foreach($allWorkers as $w)
                                <option value="{{ $w }}">{{ $w }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">NOMINAL GAJI (RP)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" class="form-control" name="amount" required min="0" placeholder="Contoh: 500000">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">KETERANGAN / CATATAN (OPSIONAL)</label>
                        <input type="text" class="form-control" name="notes" placeholder="Misal: Gaji Pokok Minggu Ini / Bonus Order">
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-muted small fw-bold">TANGGAL</label>
                        <input type="date" class="form-control" name="date" value="{{ now()->toDateString() }}">
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success fw-bold"><i class="bi bi-check-circle me-1"></i> Simpan Gaji</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/satset.js"></script>
</body>
</html>