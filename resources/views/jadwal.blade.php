<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

@php $isAdmin = $isAdmin ?? false; @endphp

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">Jadwal Eksekusi Farm (Minimalist View)</h3>
            <p class="text-muted small mb-0">Pantau dan panen serentak akun farm berdasarkan Kingdom dengan sistem otomatis multi-operator</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($isAdmin)
                <span class="badge bg-danger px-2 py-1" style="font-size: 11px;" title="Anda login sebagai Admin">
                    <i class="bi bi-shield-lock-fill me-1"></i>Admin
                </span>
            @endif
            <a href="/akun" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-gear-fill me-1"></i> Kelola Char
            </a>
        </div>
    </div>

    @php
        $activeWorker = $activeWorker ?? (auth()->check() && !auth()->user()->isAdmin() ? auth()->user()->name : ($knownWorkers[0] ?? 'Operator'));
    @endphp


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
    <!-- Filter & Pencarian Cepat KD (Satset) + Pengingat WA -->
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
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button type="button" class="btn btn-sm btn-success rounded-pill px-2 py-1 fw-bold shadow-xs d-flex align-items-center gap-1" id="btnBotShareAll" style="font-size: 12px;" title="Kirim Rekap Otomatis via Bot Fonnte (Tanpa Buka WA)">
                    <i class="bi bi-robot fs-6"></i> <span>Bot Rekap</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 fw-bold shadow-xs d-flex align-items-center gap-1" id="btnShareAllWA" style="font-size: 12px;" title="Buka Chat WhatsApp Rekap">
                    <i class="bi bi-whatsapp fs-6"></i> <span>Manual WA</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 28px; height: 28px; font-size: 12px;" data-bs-toggle="modal" data-bs-target="#modalSettingWA" title="Pengaturan Bot WA Fonnte">
                    <i class="bi bi-gear-fill"></i>
                </button>
                <div class="input-group input-group-sm position-relative" style="min-width: 130px; max-width: 190px;">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="inputSearchKD" name="search_kd_number_no_autofill" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other" class="form-control border-start-0 border-end-0" placeholder="Cari KD #...">
                    <button class="btn btn-outline-secondary border-start-0 d-none" type="button" id="btnClearSearchKD" title="Hapus pencarian">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="kd-card-container">
        <!-- Empty State saat filter atau pencarian tidak menemukan KD -->
        <div class="col-12 py-5 text-center text-muted d-none" id="kd-filter-empty">
            <div class="mb-3">
                <i class="bi bi-search fs-1 text-secondary opacity-75" id="empty-state-icon"></i>
            </div>
            <h5 class="fw-bold text-dark" id="empty-state-title">Tidak Ada Kingdom yang Sesuai</h5>
            <p class="small text-muted mb-3 mx-auto" style="max-width: 480px;" id="empty-state-desc">
                Tidak ada data Kingdom yang cocok dengan filter atau pencarian saat ini.
            </p>
            <div class="d-flex justify-content-center gap-2 flex-wrap" id="empty-state-actions">
                <button type="button" class="btn btn-sm btn-dark rounded-pill px-3" id="btnResetFilterToAll">
                    <i class="bi bi-grid-fill me-1"></i> Tampilkan Semua KD ({{ count($groupedByKD) }})
                </button>
            </div>
        </div>
        @forelse ($groupedByKD as $kd => $chars)
            @php
                $kdCooldownHours = isset($kingdoms[$kd]) ? (float)$kingdoms[$kd]->cooldown_hours : 4.0;
                $kdCooldownMinutes = (int) round($kdCooldownHours * 60);

                $totalChars = $chars->count();
                $totalAkun = $chars->pluck('game_account_id')->unique()->count();
                $readyCount = 0;
                $maxCooldownEnd = null;

                foreach ($chars as $c) {
                    if (!$c->last_farmed_at || now()->greaterThanOrEqualTo($c->last_farmed_at->copy()->addMinutes($kdCooldownMinutes))) {
                        $readyCount++;
                    } else {
                        $end = $c->last_farmed_at->copy()->addMinutes($kdCooldownMinutes);
                        if ($maxCooldownEnd === null || $end->greaterThan($maxCooldownEnd)) {
                            $maxCooldownEnd = $end;
                        }
                    }
                }

                $cooldownText = '';
                if ($maxCooldownEnd) {
                    $sisaMenit = abs((int) round(now()->diffInMinutes($maxCooldownEnd)));
                    $jam = floor($sisaMenit / 60);
                    $menit = $sisaMenit % 60;
                    $cooldownText = ($jam > 0 ? $jam.'j ' : '') . $menit.'m';
                }

                // Ambil statistik per worker untuk KD ini
                $kdStatList = isset($kdStats[$kd]) ? $kdStats[$kd] : collect();
            @endphp

            <div class="col-xl-4 col-lg-6 mb-4 kd-card-wrapper" data-kd="{{ $kd }}" data-has-ready="{{ $readyCount > 0 ? 'true' : 'false' }}">
                <div class="card h-100 border-top border-4 {{ $readyCount > 0 ? 'border-danger' : 'border-secondary' }}">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="m-0 fw-bold {{ $readyCount > 0 ? 'text-danger' : 'text-secondary' }}">
                                <i class="bi bi-geo-alt-fill"></i> KD #{{ $kd }}
                            </h5>
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCooldownKD{{ $kd }}" title="Ubah Waktu Jeda KD Ini">
                                <i class="bi bi-clock-history me-1"></i>{{ $kdCooldownHours }}j
                            </button>
                        </div>
                        <div class="kd-status-badge">
                            @if($readyCount == 0)
                                <span class="badge bg-secondary"><i class="bi bi-lock-fill"></i> All Cooldown</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-unlock-fill"></i> Ready</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body">
                        @if($readyCount > 0)
                            @php
                                $currentKd = isset($kingdoms[$kd]) ? $kingdoms[$kd] : null;
                                $repliedAt = $currentKd ? $currentKd->replied_at : null;
                                $minsSinceReply = $repliedAt ? abs((int) round(now()->diffInMinutes($repliedAt))) : null;
                            @endphp
                            @if($repliedAt)
                                @if($minsSinceReply < 30)
                                    <div class="alert alert-info py-1 px-2 mb-2 d-flex justify-content-between align-items-center rounded-2 border-0 shadow-xs" style="font-size: 11px;">
                                        <span class="text-truncate me-1">
                                            <i class="bi bi-person-check-fill text-primary me-1"></i> Dibalas: <b>{{ $currentKd->replied_by ?: 'Operator' }}</b>
                                        </span>
                                        <span class="badge bg-primary text-white" title="Sisa tenggat waktu sebelum spam 5 menit">Tenggat {{ 30 - $minsSinceReply }}m</span>
                                    </div>
                                @else
                                    <div class="alert alert-danger py-1 px-2 mb-2 d-flex justify-content-between align-items-center rounded-2 border-danger shadow-xs" style="font-size: 11px;">
                                        <span class="text-truncate me-1 fw-bold text-danger">
                                            <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> OVERDUE ({{ $minsSinceReply }}m)! Belum diceklis
                                        </span>
                                        <span class="badge bg-danger text-white">Spam 5m Aktif</span>
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-warning py-1 px-2 mb-2 d-flex justify-content-between align-items-center rounded-2 border-0 shadow-xs" style="font-size: 11px;">
                                    <span class="text-truncate me-1 text-dark">
                                        <i class="bi bi-bell-fill text-warning me-1"></i> Belum Dibalas (Spam 5m)
                                    </span>
                                    <button type="button" class="btn btn-xs btn-outline-dark rounded-pill px-2 py-0 fw-bold btn-ack-kd" data-kd="{{ $kd }}" title="Tandai Anda sedang mengerjakan KD ini">
                                        <i class="bi bi-hand-thumbs-up me-1"></i>Saya OTW
                                    </button>
                                </div>
                            @endif
                        @endif

                        <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                            <span class="text-muted small fw-bold"><i class="bi bi-envelope-paper"></i> TOTAL AKUN</span>
                            <span class="fw-bold fs-6">{{ $totalAkun }} Email <span class="text-muted fw-normal">({{ $totalChars }} Char)</span></span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                            <span class="text-muted small fw-bold"><i class="bi bi-check-circle-fill text-success"></i> SIAP PANEN</span>
                            <span class="fw-bold fs-6 text-success">{{ $readyCount }} Char</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                            <span class="text-muted small fw-bold"><i class="bi bi-hourglass-split text-info"></i> SIKLUS PANEN</span>
                            <span class="fw-bold fs-6 text-primary">
                                Tiap {{ $kdCooldownHours }} Jam
                                <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none ms-1" data-bs-toggle="modal" data-bs-target="#modalCooldownKD{{ $kd }}">
                                    <i class="bi bi-pencil-square"></i> Ubah
                                </button>
                            </span>
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
                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none small text-primary me-1" data-bs-toggle="modal" data-bs-target="#modalCooldownKD{{ $kd }}">
                                        <i class="bi bi-clock"></i> Jeda
                                    </button>
                                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none small text-muted" data-bs-toggle="modal" data-bs-target="#modalAdjustKD{{ $kd }}">
                                        <i class="bi bi-sliders"></i> Log
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row g-2">
                                @forelse($knownWorkers as $wName)
                                    @php
                                        $wStat = $kdStatList->firstWhere('worker_name', $wName);
                                        $wCount = $wStat ? $wStat->farm_count : 0;
                                        $colClass = count($knownWorkers) === 1 ? 'col-12' : 'col-6';
                                    @endphp
                                    <div class="{{ $colClass }}">
                                        <div class="bg-white p-2 rounded border text-center shadow-xs">
                                            <div class="text-muted small fw-bold text-truncate"><i class="bi bi-person-fill text-primary"></i> {{ $wName }}</div>
                                            <div class="fs-4 fw-bold text-primary my-1">{{ $wCount }}<span class="fs-6 text-muted fw-normal">x</span></div>
                                            <div class="d-flex justify-content-center gap-1">
                                                <form action="/jadwal/kd/{{ $kd }}/adjust-log" method="POST" class="m-0 form-adjust-log">
                                                    @csrf
                                                    <input type="hidden" name="worker_name" value="{{ $wName }}">
                                                    <input type="hidden" name="action" value="decrement">
                                                    <button type="submit" class="btn btn-xs btn-outline-danger btn-decrement" title="Kurangi 1 (-1)" {{ $wCount <= 0 ? 'disabled' : '' }}>
                                                        -1
                                                    </button>
                                                </form>
                                                <form action="/jadwal/kd/{{ $kd }}/adjust-log" method="POST" class="m-0 form-adjust-log">
                                                    @csrf
                                                    <input type="hidden" name="worker_name" value="{{ $wName }}">
                                                    <input type="hidden" name="action" value="increment">
                                                    <button type="submit" class="btn btn-xs btn-outline-success btn-increment" title="Tambah 1 (+1)">
                                                        +1
                                                    </button>
                                                </form>
                                                <form action="/jadwal/kd/{{ $kd }}/adjust-log" method="POST" class="m-0 form-adjust-log" data-confirm="Reset log {{ $wName }} di KD #{{ $kd }} jadi 0?">
                                                    @csrf
                                                    <input type="hidden" name="worker_name" value="{{ $wName }}">
                                                    <input type="hidden" name="action" value="reset">
                                                    <button type="submit" class="btn btn-xs btn-outline-secondary btn-reset" title="Reset ke 0">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center text-muted small py-2">
                                        Belum ada operator terdaftar
                                    </div>
                                @endforelse
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
                                <div class="d-flex gap-2">
                                    <button class="btn btn-secondary flex-grow-1 fw-bold" disabled>
                                        <i class="bi bi-clock-history me-1"></i> Sedang Cooldown
                                        @if($cooldownText)
                                            <span class="d-block small fw-normal text-white-50" style="font-size: 11px;">Tunggu {{ $cooldownText }} lagi</span>
                                        @endif
                                    </button>
                                    {{-- Tombol Get Ready: Force reset cooldown semua char di KD ini --}}
                                    <form action="/jadwal/kd/{{ $kd }}/force-ready" method="POST" class="m-0 form-force-ready">
                                        @csrf
                                        <button type="submit" class="btn btn-warning fw-bold px-3 h-100" title="Selesaikan lebih awal — reset cooldown semua char KD ini jadi siap panen sekarang!">
                                            <i class="bi bi-check2-circle me-1"></i>Get Ready
                                        </button>
                                    </form>
                                </div>
                            @endif

                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-secondary flex-grow-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKD{{ $kd }}" aria-expanded="false">
                                    <i class="bi bi-list-ul me-1"></i> Rincian Char ({{ $totalChars }})
                                </button>
                                <button type="button" class="btn btn-sm btn-success fw-bold btn-bot-notify-kd px-2 py-1 d-flex align-items-center gap-1 shadow-xs"
                                    data-kd="{{ $kd }}"
                                    title="Kirim Otomatis via Bot Fonnte (Tanpa Buka WhatsApp)">
                                    <i class="bi bi-robot"></i> <span style="font-size: 11px;">Bot</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success fw-bold btn-wa-single-kd px-2 py-1 d-flex align-items-center gap-1"
                                    data-kd="{{ $kd }}" 
                                    data-ready="{{ $readyCount }}" 
                                    data-total="{{ $totalChars }}" 
                                    data-cooldown="{{ $cooldownText }}" 
                                    data-cooldown-hours="{{ $kdCooldownHours }}" 
                                    data-target-time="{{ $maxCooldownEnd ? $maxCooldownEnd->timestamp : 0 }}"
                                    data-worker="{{ $activeWorker }}"
                                    title="Buka Chat WhatsApp (Direct WA)">
                                    <i class="bi bi-whatsapp"></i> <span style="font-size: 11px;">WA</span>
                                </button>
                            </div>

                            <div class="collapse mt-2" id="collapseKD{{ $kd }}">
                                <div class="card card-body bg-light p-2 mb-0 border-0" style="max-height: 220px; overflow-y: auto;">
                                    @foreach($chars as $char)
                                        @php
                                            $charOnCooldown = false;
                                            $charCooldownText = '';
                                            if ($char->last_farmed_at) {
                                                $charEnd = $char->last_farmed_at->copy()->addMinutes($kdCooldownMinutes);
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
                                                    <div class="d-inline-flex align-items-center gap-1">
                                                        <span class="badge bg-secondary" style="font-size: 10px;">
                                                            <i class="bi bi-clock"></i> {{ $charCooldownText }}
                                                        </span>
                                                        <form action="/akun/char/{{ $char->id }}/force-ready" method="POST" class="d-inline m-0 form-force-ready">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-warning rounded-pill px-2 py-0 fw-bold" style="font-size: 10px;" title="Reset jeda char ini jadi Siap Panen sekarang!">
                                                                <i class="bi bi-check2"></i> Ready
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <form action="/akun/char/{{ $char->id }}/farm" method="POST" class="d-inline m-0">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-success rounded-pill px-2 py-0 fw-bold" style="font-size: 10px;" title="Panen Karakter Ini">
                                                            <i class="bi bi-play-fill"></i> Panen
                                                        </button>
                                                    </form>
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
                                        @foreach($knownWorkers as $w)
                                            <option value="{{ $w }}">{{ $w }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">AKSI</label>
                                    <select name="action" class="form-select form-select-sm" onchange="document.getElementById('customInputKD{{ $kd }}').classList.toggle('d-none', this.value !== 'set')">
                                        <option value="increment">Tambah 1 (+1)</option>
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

            <!-- Modal Atur Cooldown / Waktu Jeda Panen KD #{{ $kd }} -->
            <div class="modal fade" id="modalCooldownKD{{ $kd }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-light py-2">
                            <h6 class="modal-title fw-bold">
                                <i class="bi bi-clock-history text-primary me-1"></i> Jeda Panen KD #{{ $kd }}
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-3">
                            <p class="text-muted small mb-3">
                                Atur siklus jeda waktu hitung mundur panen khusus untuk <b>KD #{{ $kd }}</b>.
                            </p>
                            <form action="/jadwal/kd/{{ $kd }}/set-cooldown" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">WAKTU JEDA (JAM)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.5" min="0.5" max="48" name="cooldown_hours" id="inputCooldownKD{{ $kd }}" class="form-control fw-bold fs-5 text-center text-primary" value="{{ $kdCooldownHours }}" required>
                                        <span class="input-group-text fw-bold">Jam Sekali</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="document.getElementById('inputCooldownKD{{ $kd }}').value = 1;">1 Jam</button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="document.getElementById('inputCooldownKD{{ $kd }}').value = 2;">2 Jam</button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="document.getElementById('inputCooldownKD{{ $kd }}').value = 3;">3 Jam</button>
                                        <button type="button" class="btn btn-xs btn-outline-primary fw-bold" onclick="document.getElementById('inputCooldownKD{{ $kd }}').value = 4;">4 Jam</button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="document.getElementById('inputCooldownKD{{ $kd }}').value = 5;">5 Jam</button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="document.getElementById('inputCooldownKD{{ $kd }}').value = 6;">6 Jam</button>
                                    </div>
                                </div>
                                <div class="alert alert-info py-2 px-3 small mb-3 border-0 bg-info bg-opacity-10 text-dark">
                                    <i class="bi bi-info-circle me-1 text-primary"></i> <i>Misal diisi 2 jam, maka hitung mundur siap panen KD #{{ $kd }} dihitung 2 jam setelah panen terakhir.</i>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold py-2 shadow-sm">
                                    <i class="bi bi-save me-1"></i> Simpan Jeda KD #{{ $kd }}
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

    <!-- Modal Pengaturan Bot WhatsApp Otomatis (Fonnte API) -->
    <div class="modal fade" id="modalSettingWA" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-robot me-1"></i> Pengaturan Bot WhatsApp Otomatis (Fonnte)
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <!-- Panduan Singkat Fonnte -->
                    <div class="alert alert-success bg-success-subtle border border-success-subtle py-2 px-3 small mb-3">
                        <div class="fw-bold mb-1"><i class="bi bi-lightning-charge-fill text-success me-1"></i> Cara Pakai Bot Otomatis (100% Gratis):</div>
                        <ol class="ps-3 mb-0" style="font-size: 11px;">
                            <li>Buka & daftar gratis di <a href="https://fonnte.com" target="_blank" class="fw-bold text-success text-decoration-underline">fonnte.com <i class="bi bi-box-arrow-up-right"></i></a>.</li>
                            <li>Scan QR dengan nomor WA Anda (sama seperti buka WhatsApp Web).</li>
                            <li>Salin <b>API Token</b> dari Fonnte dan tempel di bawah ini.</li>
                            <li>Aktifkan <b>Auto-Send</b>: Bot otomatis kirim notif saat KD siap panen tanpa Anda klik manual!</li>
                        </ol>
                    </div>

                    <!-- Input Token Fonnte -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-muted m-0">1. FONNTE API TOKEN</label>
                            <span id="badgeDeviceStatus" class="badge bg-secondary" style="font-size: 10px;">Belum Dicek</span>
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-key-fill text-warning"></i></span>
                            <input type="password" id="inputFonnteToken" class="form-control" placeholder="Tempel Token Fonnte (contoh: a1b2c3d4...)">
                            <button class="btn btn-outline-secondary" type="button" id="btnToggleTokenVisibility" title="Lihat/Sembunyikan Token">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text small" style="font-size: 11px;">
                            Token aman tersimpan di database lokal Laragon Anda.
                        </div>
                    </div>

                    <!-- Input Nomor WA Tujuan / ID Grup -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-muted m-0">2. NOMOR WA ATAU ID GRUP TUJUAN</label>
                            <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 fw-bold" id="btnFetchWAGroups" style="font-size: 11px;">
                                <i class="bi bi-people-fill me-1"></i> Pilih Dari Grup WA
                            </button>
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-telephone-fill text-success"></i></span>
                            <input type="text" id="inputSettingWAPhone" class="form-control" placeholder="Contoh: 08123456789 atau 120363xxxx@g.us">
                        </div>
                        
                        <!-- Container List Grup WhatsApp dari Fonnte -->
                        <div id="containerGroupList" class="mt-2 p-2 bg-light border rounded small d-none" style="max-height: 180px; overflow-y: auto;">
                            <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                                <span class="fw-bold text-dark" style="font-size: 11px;"><i class="bi bi-whatsapp text-success me-1"></i>Pilih Grup Tujuan:</span>
                                <button type="button" class="btn-close" style="font-size: 8px;" id="btnCloseGroupList"></button>
                            </div>
                            <div id="groupListItems" class="d-flex flex-column gap-1">
                                <!-- Diisi via Javascript satset.js -->
                            </div>
                        </div>

                        <div class="form-text small" style="font-size: 11px;">
                            Bisa nomor pribadi (<code>08xx</code>), beberapa nomor dipisah koma, atau <b>ID Grup</b> (<code>...120363xxx@g.us</code>).
                        </div>
                    </div>

                    <!-- Switch Auto Send -->
                    <div class="card bg-light border-0 p-2 mb-3">
                        <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="checkAutoNotify" checked style="cursor: pointer; width: 36px; height: 20px;">
                            <label class="form-check-label fw-bold small text-dark" for="checkAutoNotify" style="cursor: pointer;">
                                🚀 Aktifkan Pengingat Otomatis (Auto-Bot)
                            </label>
                        </div>
                        <div class="text-muted small ps-5 mt-1" style="font-size: 11px;">
                            Bot otomatis mengirim pesan WhatsApp setiap ada KD yang masa jedanya habis tanpa perlu Anda memencet tombol manual!
                        </div>
                    </div>

                    <!-- Panduan Aturan Spam & Anti-Lupa -->
                    <div class="alert alert-light border small p-2 mb-3 shadow-xs">
                        <div class="fw-bold text-dark mb-1 d-flex align-items-center">
                            <i class="bi bi-shield-check text-success me-1 fs-6"></i> Aturan Spam & Anti-Lupa Bot:
                        </div>
                        <ul class="mb-0 ps-3" style="font-size: 11px;">
                            <li><b>Belum Dibalas:</b> Jika tidak ada yang membalas chat WA bot, bot otomatis <b>spam setiap 5 menit sekali</b>.</li>
                            <li><b>Sudah Dibalas:</b> Saat ada balasan WA (misal "otw") atau klik <i>Saya OTW</i> di web, bot memberi waktu <b>30 menit</b> untuk menyelesaikan panen.</li>
                            <li><b>Overdue (> 30 Menit):</b> Jika dalam 30 menit belum dikelarin/diceklis di web, bot otomatis <b>spam setiap 5 menit sekali</b> sampai selesai panen!</li>
                        </ul>
                    </div>

                    <!-- Input Webhook Fonnte untuk Deteksi Balasan -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-muted m-0">3. WEBHOOK FONNTE (DETEKSI BALASAN WA)</label>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 fw-bold" id="btnCopyWebhookUrl" style="font-size: 11px;">
                                <i class="bi bi-clipboard me-1"></i> Salin URL
                            </button>
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-link-45deg text-info"></i></span>
                            <input type="text" id="inputWebhookUrl" class="form-control" readonly value="{{ url('/api/fonnte/webhook') }}">
                        </div>
                        <div class="form-text small" style="font-size: 11px;">
                            Tempel URL ini di menu <b>Device Settings -> Webhook</b> pada Fonnte & set <b>Auto Read: On</b> agar balasan member grup langsung tercatat otomatis!
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-primary btn-sm fw-bold py-2 shadow-sm" id="btnSaveBotSettings">
                            <i class="bi bi-save me-1"></i> Simpan Pengaturan Bot
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-success btn-sm flex-grow-1 fw-bold" id="btnTestBotSend">
                                <i class="bi bi-send-check me-1"></i> Tes Kirim Pesan via Bot
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnTestDeviceConn" title="Cek status koneksi WA ke Fonnte">
                                <i class="bi bi-broadcast me-1"></i> Cek Device
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/satset.js?v={{ time() }}"></script>
<script>
(function() {
    'use strict';

    // 1. Filter Buttons & Anti-Autofill Search
    const filterBtns = document.querySelectorAll('.btn-filter-kd');
    const searchInput = document.getElementById('inputSearchKD');
    const clearSearchBtn = document.getElementById('btnClearSearchKD');
    const cards = document.querySelectorAll('.kd-card-wrapper');
    const emptyState = document.getElementById('kd-filter-empty');
    const emptyIcon = document.getElementById('empty-state-icon');
    const emptyTitle = document.getElementById('empty-state-title');
    const emptyDesc = document.getElementById('empty-state-desc');
    const resetFilterBtn = document.getElementById('btnResetFilterToAll');

    let activeFilter = 'all';

    // Bersihkan otomatis jika browser mengautofill email/teks non-KD
    if (searchInput) {
        const val = (searchInput.value || '').trim();
        if (val.includes('@') || val.includes('.com') || val.includes('.net')) {
            searchInput.value = '';
        }
    }

    function updateFilterButtonStyles() {
        filterBtns.forEach(btn => {
            const filter = btn.getAttribute('data-filter');
            btn.classList.remove('active', 'btn-dark', 'btn-danger', 'btn-secondary', 'btn-outline-dark', 'btn-outline-danger', 'btn-outline-secondary');

            if (filter === activeFilter) {
                btn.classList.add('active');
                if (filter === 'all') btn.classList.add('btn-dark');
                else if (filter === 'ready') btn.classList.add('btn-danger');
                else if (filter === 'cooldown') btn.classList.add('btn-secondary');
            } else {
                if (filter === 'all') btn.classList.add('btn-outline-secondary');
                else if (filter === 'ready') btn.classList.add('btn-outline-danger');
                else if (filter === 'cooldown') btn.classList.add('btn-outline-secondary');
            }
        });
    }

    function applyFilters() {
        let visibleCount = 0;
        const currentSearch = searchInput ? searchInput.value.trim().toLowerCase().replace('#', '') : '';

        if (clearSearchBtn) {
            clearSearchBtn.classList.toggle('d-none', currentSearch.length === 0);
        }

        cards.forEach(card => {
            const kdNum = (card.getAttribute('data-kd') || '').toLowerCase();
            const hasReady = card.getAttribute('data-has-ready') === 'true';

            let matchFilter = true;
            if (activeFilter === 'ready') matchFilter = hasReady;
            else if (activeFilter === 'cooldown') matchFilter = !hasReady;

            let matchSearch = true;
            if (currentSearch) matchSearch = kdNum.includes(currentSearch);

            if (matchFilter && matchSearch) {
                card.classList.remove('d-none');
                visibleCount++;
            } else {
                card.classList.add('d-none');
            }
        });

        if (emptyState) {
            if (visibleCount === 0) {
                emptyState.classList.remove('d-none');
                if (currentSearch) {
                    if (emptyIcon) emptyIcon.className = 'bi bi-search fs-1 text-secondary opacity-75';
                    if (emptyTitle) emptyTitle.innerText = `Tidak ada Kingdom yang cocok dengan "${currentSearch}"`;
                    if (emptyDesc) emptyDesc.innerText = 'Pastikan nomor Kingdom yang dicari benar (contoh: 2359 atau 3868).';
                } else if (activeFilter === 'ready') {
                    if (emptyIcon) emptyIcon.className = 'bi bi-clock-history fs-1 text-warning opacity-75';
                    if (emptyTitle) emptyTitle.innerText = 'Semua Kingdom Sedang Masa Jeda (Cooldown)';
                    if (emptyDesc) emptyDesc.innerText = 'Belum ada akun farm yang siap panen saat ini. Silakan klik tombol di bawah untuk melihat KD yang sedang cooldown atau semua KD.';
                } else if (activeFilter === 'cooldown') {
                    if (emptyIcon) emptyIcon.className = 'bi bi-check-circle fs-1 text-success opacity-75';
                    if (emptyTitle) emptyTitle.innerText = 'Tidak Ada Kingdom yang Sedang Cooldown';
                    if (emptyDesc) emptyDesc.innerText = 'Semua Kingdom sudah siap panen!';
                }
            } else {
                emptyState.classList.add('d-none');
            }
        }
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            activeFilter = this.getAttribute('data-filter') || 'all';
            updateFilterButtonStyles();
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            clearSearchBtn.classList.add('d-none');
            applyFilters();
        });
    }

    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (clearSearchBtn) clearSearchBtn.classList.add('d-none');
            activeFilter = 'all';
            updateFilterButtonStyles();
            applyFilters();
        });
    }

    // 2. Intercept Form Adjust Log (-1 / +1 / Reset) via AJAX Satset
    document.querySelectorAll('form[action*="/adjust-log"]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            const confirmMsg = form.getAttribute('data-confirm');
            if (confirmMsg && !window.confirm(confirmMsg)) {
                e.preventDefault();
                return;
            }

            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.style.opacity = '0.6';
            }

            try {
                const formData = new FormData(form);
                const targetUrl = form.getAttribute('action');
                const response = await fetch(targetUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    const parentBox = form.closest('.bg-white.p-2');
                    if (parentBox && typeof data.farm_count !== 'undefined') {
                        const countEl = parentBox.querySelector('.fs-4.fw-bold');
                        if (countEl) countEl.innerHTML = `${data.farm_count}<span class="fs-6 text-muted fw-normal">x</span>`;

                        const decBtn = parentBox.querySelector('button.btn-decrement, button[title*="Kurangi"]');
                        if (decBtn) decBtn.disabled = (data.farm_count <= 0);
                    } else {
                        sessionStorage.setItem('satset_scroll_pos', window.scrollY);
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'Gagal memproses perubahan log');
                }
            } catch (err) {
                form.submit();
            } finally {
                if (btn) {
                    btn.style.opacity = '1';
                    btn.innerHTML = originalHtml;
                    const parentBox = form.closest('.bg-white.p-2');
                    if (btn.classList.contains('btn-decrement') && parentBox) {
                        const currentNum = parseInt(parentBox.querySelector('.fs-4.fw-bold')?.innerText || '0', 10);
                        btn.disabled = (currentNum <= 0);
                    } else if (!btn.classList.contains('btn-decrement')) {
                        btn.disabled = false;
                    }
                }
            }
        });
    });

    updateFilterButtonStyles();
    applyFilters();

    // 3. AJAX Handler untuk tombol Get Ready (Force Reset Cooldown KD)
    document.querySelectorAll('form.form-force-ready').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Loading...';
            }

            try {
                const formData = new FormData(form);
                const response = await fetch(form.getAttribute('action'), {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    // Reload halaman dengan posisi scroll dijaga
                    sessionStorage.setItem('satset_scroll_pos', window.scrollY);
                    // Tampilkan toast singkat
                    const toast = document.createElement('div');
                    toast.className = 'alert alert-success alert-dismissible position-fixed shadow-lg border-0 fw-bold';
                    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; font-size: 13px;';
                    toast.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i>${data.message}<button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>`;
                    document.body.appendChild(toast);
                    setTimeout(() => { window.location.reload(); }, 1000);
                } else {
                    alert(data.message || 'Gagal melakukan Get Ready');
                    if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
                }
            } catch (err) {
                form.submit();
            }
        });
    });

    // 4. Restore scroll position after reload (satset UX)
    const savedScroll = sessionStorage.getItem('satset_scroll_pos');
    if (savedScroll) {
        window.scrollTo(0, parseInt(savedScroll, 10));
        sessionStorage.removeItem('satset_scroll_pos');
    }
})();
</script>
</body>
</html>