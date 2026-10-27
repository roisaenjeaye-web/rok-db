<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Farm - RoK Joki Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/mobile.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        .sidebar { min-height: 100vh; width: 250px; background-color: #212529; position: fixed; top: 0; left: 0; padding-top: 20px; box-shadow: 2px 0 5px rgba(0,0,0,0.1); display: flex; flex-direction: column; justify-content: space-between; z-index: 100; }
        .sidebar .brand { color: #fff; font-size: 22px; font-weight: 700; text-align: center; margin-bottom: 30px; letter-spacing: 1px; }
        .sidebar .brand span { color: #0d6efd; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; font-weight: 500; transition: 0.3s; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background-color: #0d6efd; color: #fff; border-radius: 0 25px 25px 0; margin-right: 20px; }
        .sidebar a i { margin-right: 10px; font-size: 1.1rem; }
        .content { margin-left: 250px; padding: 30px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); margin-bottom: 20px; transition: 0.2s; }
        .card:hover { transform: translateY(-3px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1); }
        .card-header { background-color: #fff; border-bottom: 1px solid #edf2f9; font-weight: 600; border-radius: 10px 10px 0 0 !important; padding: 15px 20px; }
        .btn-xs { padding: 0.15rem 0.4rem; font-size: 0.75rem; line-height: 1.2; border-radius: 0.2rem; }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        <div class="brand"><i class="bi bi-shield-shaded"></i> RoK<span>Joki</span></div>
        <a href="/stok"><i class="bi bi-box-seam"></i> Dashboard Stok</a>
        <a href="/kalkulator"><i class="bi bi-calculator"></i> Kalkulator Pajak</a>
        <a href="/riwayat"><i class="bi bi-journal-text"></i> Buku Kas</a>
        <a href="/akun"><i class="bi bi-people"></i> Database Akun</a>
        <a href="/jadwal" class="active"><i class="bi bi-alarm"></i> Jadwal Farm (KD)</a>
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
        <div>
            <h3 class="fw-bold text-dark m-0">Jadwal Eksekusi Farm (Minimalist View)</h3>
            <p class="text-muted small mb-0">Pantau dan panen serentak akun farm berdasarkan Kingdom dengan sistem otomatis multi-operator</p>
        </div>
        <a href="/akun" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-gear-fill me-1"></i> Kelola Char
        </a>
    </div>

    <!-- Status Operator Joki Login -->
    @php
        $activeWorker = auth()->user()->name ?? 'Rois';
        $allWorkers = $knownWorkers ?? ['Rois', 'Therta'];
    @endphp
    <div class="card bg-white border-0 shadow-sm mb-4">
        <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    <i class="bi bi-circle-fill" style="font-size: 8px;"></i> Online
                </span>
                <span class="text-dark small fw-bold">
                    Login sebagai: <span class="text-primary fs-6">{{ $activeWorker }}</span>
                </span>
            </div>
            <div class="text-muted small">
                <i class="bi bi-lightning-charge text-warning"></i> Semua panen otomatis tercatat atas nama <b>{{ $activeWorker }}</b>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter & Pencarian Cepat KD (Satset) -->
    <div class="card bg-white border-0 shadow-sm mb-3">
        <div class="card-body p-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-1 overflow-auto py-1" style="white-space: nowrap;">
                <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 py-1 btn-filter-kd active" data-filter="all" style="font-size: 12px;">
                    <i class="bi bi-grid-fill me-1"></i> Semua ({{ count($groupedByKD) }})
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 btn-filter-kd" data-filter="ready" style="font-size: 12px;">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Siap Panen Saja
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 btn-filter-kd" data-filter="cooldown" style="font-size: 12px;">
                    <i class="bi bi-clock-history me-1"></i> Sedang Cooldown
                </button>
            </div>
            <div class="ms-auto" style="min-width: 130px; max-width: 200px;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="inputSearchKD" class="form-control border-start-0" placeholder="Cari KD #...">
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse ($groupedByKD as $kd => $chars)
            @php
                $totalChars = $chars->count();
                $totalAkun = $chars->pluck('game_account_id')->unique()->count();
                $readyCount = 0;
                $maxCooldownEnd = null;

                foreach ($chars as $c) {
                    if (!$c->last_farmed_at || now()->greaterThanOrEqualTo($c->last_farmed_at->copy()->addHours(4))) {
                        $readyCount++;
                    } else {
                        $end = $c->last_farmed_at->copy()->addHours(4);
                        if ($maxCooldownEnd === null || $end->greaterThan($maxCooldownEnd)) {
                            $maxCooldownEnd = $end;
                        }
                    }
                }

                $cooldownText = '';
                if ($maxCooldownEnd) {
                    $sisaMenit = now()->diffInMinutes($maxCooldownEnd);
                    $jam = floor($sisaMenit / 60);
                    $menit = $sisaMenit % 60;
                    $cooldownText = ($jam > 0 ? $jam.'j ' : '') . $menit.'m';
                }

                // Ambil statistik per worker untuk KD ini
                $kdStatList = isset($kdStats[$kd]) ? $kdStats[$kd] : collect();
                $roisStat = $kdStatList->firstWhere('worker_name', 'Rois');
                $thertaStat = $kdStatList->firstWhere('worker_name', 'Therta');
                $roisCount = $roisStat ? $roisStat->farm_count : 0;
                $thertaCount = $thertaStat ? $thertaStat->farm_count : 0;

                // Worker lain yang ada datanya di KD ini
                $otherStats = $kdStatList->whereNotIn('worker_name', ['Rois', 'Therta']);
            @endphp

            <div class="col-xl-4 col-lg-6 mb-4 kd-card-wrapper" data-kd="{{ $kd }}" data-has-ready="{{ $readyCount > 0 ? 'true' : 'false' }}">
                <div class="card h-100 border-top border-4 {{ $readyCount > 0 ? 'border-danger' : 'border-secondary' }}">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                        <h5 class="m-0 fw-bold {{ $readyCount > 0 ? 'text-danger' : 'text-secondary' }}">
                            <i class="bi bi-geo-alt-fill"></i> KD #{{ $kd }}
                        </h5>
                        <div class="kd-status-badge">
                            @if($readyCount == 0)
                                <span class="badge bg-secondary"><i class="bi bi-lock-fill"></i> All Cooldown</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-unlock-fill"></i> Ready</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                            <span class="text-muted small fw-bold"><i class="bi bi-envelope-paper"></i> TOTAL AKUN</span>
                            <span class="fw-bold fs-6">{{ $totalAkun }} Email <span class="text-muted fw-normal">({{ $totalChars }} Char)</span></span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                            <span class="text-muted small fw-bold"><i class="bi bi-check-circle-fill text-success"></i> SIAP PANEN</span>
                            <span class="fw-bold fs-6 text-success">{{ $readyCount }} Char</span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small fw-bold"><i class="bi bi-stopwatch-fill text-warning"></i> JEDA TERLAMA</span>
                            <span class="fw-bold fs-6 {{ $cooldownText ? 'text-warning' : 'text-success' }}" data-target-time="{{ $maxCooldownEnd ? $maxCooldownEnd->timestamp : 0 }}">
                                {{ $cooldownText ? 'Tunggu ' . $cooldownText : 'Semua Siap!' }}
                            </span>
                        </div>

                        <!-- REKAP LOG JOKI KD INI -->
                        <div class="bg-light p-3 rounded mb-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-bold">
                                    <i class="bi bi-people-fill text-primary me-1"></i> LOG JOKI KD #{{ $kd }}
                                </span>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none small text-muted" data-bs-toggle="modal" data-bs-target="#modalAdjustKD{{ $kd }}">
                                    <i class="bi bi-sliders"></i> Atur / Reset
                                </button>
                            </div>
                            
                            <div class="row g-2">
                                <!-- Rois -->
                                <div class="col-6">
                                    <div class="bg-white p-2 rounded border text-center shadow-xs">
                                        <div class="text-muted small fw-bold text-truncate"><i class="bi bi-person-fill text-primary"></i> Rois</div>
                                        <div class="fs-4 fw-bold text-primary my-1">{{ $roisCount }}<span class="fs-6 text-muted fw-normal">x</span></div>
                                        <div class="d-flex justify-content-center gap-1">
                                            <form action="/jadwal/kd/{{ $kd }}/adjust-log" method="POST" class="m-0">
                                                @csrf
                                                <input type="hidden" name="worker_name" value="Rois">
                                                <input type="hidden" name="action" value="decrement">
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Kurangi 1 (-1)" {{ $roisCount <= 0 ? 'disabled' : '' }}>
                                                    -1
                                                </button>
                                            </form>
                                            <form action="/jadwal/kd/{{ $kd }}/adjust-log" method="POST" class="m-0" onsubmit="return confirm('Reset log Rois di KD #{{ $kd }} jadi 0?');">
                                                @csrf
                                                <input type="hidden" name="worker_name" value="Rois">
                                                <input type="hidden" name="action" value="reset">
                                                <button type="submit" class="btn btn-xs btn-outline-secondary" title="Reset ke 0">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Therta -->
                                <div class="col-6">
                                    <div class="bg-white p-2 rounded border text-center shadow-xs">
                                        <div class="text-muted small fw-bold text-truncate"><i class="bi bi-person-fill text-info"></i> Therta</div>
                                        <div class="fs-4 fw-bold text-info my-1">{{ $thertaCount }}<span class="fs-6 text-muted fw-normal">x</span></div>
                                        <div class="d-flex justify-content-center gap-1">
                                            <form action="/jadwal/kd/{{ $kd }}/adjust-log" method="POST" class="m-0">
                                                @csrf
                                                <input type="hidden" name="worker_name" value="Therta">
                                                <input type="hidden" name="action" value="decrement">
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Kurangi 1 (-1)" {{ $thertaCount <= 0 ? 'disabled' : '' }}>
                                                    -1
                                                </button>
                                            </form>
                                            <form action="/jadwal/kd/{{ $kd }}/adjust-log" method="POST" class="m-0" onsubmit="return confirm('Reset log Therta di KD #{{ $kd }} jadi 0?');">
                                                @csrf
                                                <input type="hidden" name="worker_name" value="Therta">
                                                <input type="hidden" name="action" value="reset">
                                                <button type="submit" class="btn btn-xs btn-outline-secondary" title="Reset ke 0">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Worker Tambahan (jika ada) -->
                                @foreach($otherStats as $os)
                                    <div class="col-12">
                                        <div class="bg-white p-2 rounded border d-flex justify-content-between align-items-center">
                                            <span class="small fw-bold text-muted"><i class="bi bi-person"></i> {{ $os->worker_name }}: <b>{{ $os->farm_count }}x</b></span>
                                            <form action="/jadwal/kd/{{ $kd }}/adjust-log" method="POST" class="m-0 d-inline">
                                                @csrf
                                                <input type="hidden" name="worker_name" value="{{ $os->worker_name }}">
                                                <input type="hidden" name="action" value="decrement">
                                                <button type="submit" class="btn btn-xs btn-outline-danger">-1</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-flex flex-column gap-2">
                            @if($readyCount > 0)
                                <form action="/jadwal/kd/{{ $kd }}/farm" method="POST" class="m-0">
                                    @csrf
                                    <input type="hidden" name="worker_name" value="{{ $activeWorker }}">
                                    <button type="submit" class="btn btn-danger w-100 fw-bold shadow-sm py-2">
                                        <i class="bi bi-lightning-charge-fill me-1"></i> Panen Serentak ({{ $readyCount }} Char)
                                        <span class="d-block text-white-50 small fw-normal" style="font-size: 11px;">Joki: <b>{{ $activeWorker }}</b></span>
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-secondary w-100 fw-bold" disabled>
                                    <i class="bi bi-clock-history me-1"></i> Sedang Cooldown
                                </button>
                            @endif

                            <button class="btn btn-sm btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKD{{ $kd }}" aria-expanded="false">
                                <i class="bi bi-list-ul me-1"></i> Rincian Char ({{ $totalChars }})
                            </button>

                            <div class="collapse mt-2" id="collapseKD{{ $kd }}">
                                <div class="card card-body bg-light p-2 mb-0 border-0" style="max-height: 220px; overflow-y: auto;">
                                    @foreach($chars as $char)
                                        @php
                                            $charOnCooldown = false;
                                            $charCooldownText = '';
                                            if ($char->last_farmed_at) {
                                                $charEnd = $char->last_farmed_at->copy()->addHours(4);
                                                if (now()->lessThan($charEnd)) {
                                                    $charOnCooldown = true;
                                                    $sMenit = now()->diffInMinutes($charEnd);
                                                    $jC = floor($sMenit / 60);
                                                    $mC = $sMenit % 60;
                                                    $charCooldownText = ($jC > 0 ? $jC.'j ' : '') . $mC.'m';
                                                }
                                            }
                                        @endphp
                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-secondary-subtle small">
                                            <div>
                                                <strong>{{ $char->ign }}</strong>
                                                <div class="text-muted" style="font-size: 11px;">
                                                    {{ $char->gameAccount->email ?? '-' }} | Farm: <span class="text-primary fw-bold">{{ $char->farm_count }}x</span>
                                                    @if(!empty($char->farmer_stats))
                                                        <span class="text-secondary ms-1">
                                                            (@foreach($char->farmer_stats as $wName => $wCount)
                                                                {{ $wName }}: {{ $wCount }}x{{ !$loop->last ? ', ' : '' }}
                                                            @endforeach)
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                @if($charOnCooldown)
                                                    <span class="badge bg-secondary" style="font-size: 10px;">
                                                        <i class="bi bi-clock"></i> {{ $charCooldownText }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-success" style="font-size: 10px;">
                                                        <i class="bi bi-check-circle"></i> Ready
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Atur / Reset Log KD #{{ $kd }} -->
            <div class="modal fade" id="modalAdjustKD{{ $kd }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title fw-bold"><i class="bi bi-sliders text-primary me-2"></i>Atur & Reset Log KD #{{ $kd }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-3">Sesuaikan jumlah login jika salah panen, atau reset catatan kerja kembali ke 0.</p>
                            
                            <form action="/jadwal/kd/{{ $kd }}/adjust-log" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">PILIH OPERATOR</label>
                                    <select name="worker_name" class="form-select form-select-sm" required>
                                        @foreach($allWorkers as $w)
                                            <option value="{{ $w }}">{{ $w }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">AKSI</label>
                                    <select name="action" class="form-select form-select-sm" onchange="document.getElementById('customInputKD{{ $kd }}').classList.toggle('d-none', this.value !== 'set')">
                                        <option value="decrement">Kurangi 1 (-1)</option>
                                        <option value="reset">Reset Jadi 0</option>
                                        <option value="set">Atur Angka Tertentu (Custom)</option>
                                    </select>
                                </div>
                                <div class="mb-3 d-none" id="customInputKD{{ $kd }}">
                                    <label class="form-label small fw-bold text-muted">JUMLAH BARU</label>
                                    <input type="number" name="custom_count" class="form-control form-control-sm" min="0" placeholder="Contoh: 100">
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="apply_to_chars" value="1" id="applyCheckKD{{ $kd }}" checked>
                                    <label class="form-check-label small text-muted" for="applyCheckKD{{ $kd }}">
                                        Terapkan juga ke masing-masing karakter di KD ini
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                                    <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary"></i>
                        <h5 class="fw-bold">Belum ada Karakter atau Kingdom</h5>
                        <p class="text-muted mb-3">Silakan tambahkan akun dan karakter terlebih dahulu melalui menu Database Akun.</p>
                        <a href="/akun" class="btn btn-primary"><i class="bi bi-people me-1"></i> Buka Database Akun</a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/satset.js"></script>
</body>
</html>