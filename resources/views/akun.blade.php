<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ternak Farm - RoK Joki Admin</title>
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
        .card { border: none; border-radius: 10px; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); margin-bottom: 20px; }
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
        <a href="/akun" class="active"><i class="bi bi-people"></i> Manajemen Ternak</a>
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

@php $isAdmin = $isAdmin ?? false; @endphp

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">Manajemen Akun & Karakter (Ternak)</h3>
            <p class="text-muted small mb-0">Kelola akun Gmail, suntik karakter massal, dan monitoring per Kingdom</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($isAdmin)
                <span class="badge bg-danger px-2 py-1" style="font-size: 11px;" title="Anda login sebagai Admin">
                    <i class="bi bi-shield-lock-fill me-1"></i>Admin
                </span>
            @endif
            <a href="/jadwal" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-alarm me-1"></i> Ke Jadwal Farm
            </a>
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

    <div class="row">
        <!-- Kolom Form (Tambah Akun & Suntik Char) -->
        <div class="col-lg-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-header text-primary"><i class="bi bi-google me-1"></i> 1. Tambah Akun Gmail</div>
                <div class="card-body p-4">
                    <form action="/akun/tambah" method="POST">
                        @csrf
                        <div class="mb-3">
                            <input type="email" class="form-control" name="email" required placeholder="Email (Contoh: rois@gmail.com)">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="password" placeholder="Password (Opsional)">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="notes" placeholder="Catatan (Bind Lilith, dll)">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-plus-circle me-1"></i> Simpan Akun</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header text-success"><i class="bi bi-person-plus-fill me-1"></i> 2. Suntik Char Massal</div>
                <div class="card-body p-4">
                    <form action="/akun/tambah-char" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">PILIH AKUN GMAIL</label>
                            <select class="form-select" name="game_account_id" required>
                                <option value="">-- Pilih Email --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->email }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">KD CHAR</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">#</span>
                                    <input type="number" class="form-control" name="kd_number" required placeholder="2357">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">JML CHAR</label>
                                <input type="number" class="form-control" name="jumlah" required min="1" value="1">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">IGN (OPSIONAL)</label>
                            <input type="text" class="form-control" name="ign" placeholder="Misal: Farm KD 2359">
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold"><i class="bi bi-rocket-takeoff me-1"></i> Suntik Char</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="text-dark fw-bold small"><i class="bi bi-person-lines-fill text-primary me-1"></i> 3. Kelola Akun Gmail ({{ $accounts->count() }})</span>
                    <button class="btn btn-xs btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAllAccounts">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
                <div class="collapse show" id="collapseAllAccounts">
                    <div class="card-body p-2" style="max-height: 380px; overflow-y: auto;">
                        @forelse($accounts as $acc)
                            <div class="p-2 border rounded bg-white mb-2 shadow-xs d-flex justify-content-between align-items-center">
                                <div class="text-truncate me-2" style="max-width: 68%;">
                                    <div class="fw-bold small text-truncate text-dark">{{ $acc->email }}</div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        @if($acc->password)
                                            <i class="bi bi-key text-warning"></i> {{ $acc->password }} |
                                        @endif
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 9px;">{{ $acc->characters->count() }} Char</span>
                                        @if($acc->notes)
                                            <span class="badge bg-light text-secondary border fw-normal ms-1" style="font-size: 9px;">{{ $acc->notes }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditAcc{{ $acc->id }}" title="Edit Akun">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="/akun/{{ $acc->id }}/hapus" method="POST" class="m-0 d-inline" onsubmit="return confirm('Yakin ingin menghapus akun {{ $acc->email }} beserta SEMUA karakternya? Data tidak bisa dikembalikan!');">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus Akun">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-3 text-muted small">Belum ada akun terdaftar</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Data Akun & Char Dikategorikan per KD (Format Card Ringkas seperti Jadwal) -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark m-0"><i class="bi bi-grid-fill text-primary me-2"></i>Daftar Akun per Kingdom</h5>
                <span class="badge bg-secondary-subtle text-secondary border">Total: {{ $groupedByKD->count() }} Kingdom</span>
            </div>

            <div class="row g-3">
                @forelse ($groupedByKD as $kd => $kdChars)
                    @php
                        $kdCooldownHours = isset($kingdoms[$kd]) ? (float)$kingdoms[$kd]->cooldown_hours : 4.0;
                        $kdCooldownMinutes = (int) round($kdCooldownHours * 60);

                        $totalChars = $kdChars->count();
                        $uniqueAccs = $kdChars->pluck('game_account_id')->unique();
                        $totalAkun = $uniqueAccs->count();
                        $readyCount = 0;
                        $maxCooldownEnd = null;

                        foreach ($kdChars as $c) {
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
                            $sisaMenit = now()->diffInMinutes($maxCooldownEnd);
                            $jam = floor($sisaMenit / 60);
                            $menit = $sisaMenit % 60;
                            $cooldownText = ($jam > 0 ? $jam.'j ' : '') . $menit.'m';
                        }

                        $accountsInKd = $kdChars->groupBy('game_account_id');
                    @endphp

                    <div class="col-md-6 mb-2 kd-card-wrapper" data-kd="{{ $kd }}" data-has-ready="{{ $readyCount > 0 ? 'true' : 'false' }}">
                        <div class="card h-100 border-top border-4 {{ $readyCount > 0 ? 'border-danger' : 'border-secondary' }} shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center bg-light py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="m-0 fw-bold {{ $readyCount > 0 ? 'text-danger' : 'text-secondary' }}">
                                        <i class="bi bi-geo-alt-fill"></i> KD #{{ $kd }}
                                    </h5>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1" style="font-size: 10px;">
                                        <i class="bi bi-clock-history me-1"></i>Jeda {{ $kdCooldownHours }} Jam
                                    </span>
                                </div>
                                <div class="kd-status-badge">
                                    @if($readyCount == 0)
                                        <span class="badge bg-secondary"><i class="bi bi-lock-fill"></i> All Cooldown</span>
                                    @else
                                        <span class="badge bg-success"><i class="bi bi-unlock-fill"></i> Ready</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="card-body d-flex flex-column p-3">
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

                                <!-- Tombol Aksi & Rincian -->
                                <div class="d-flex flex-column gap-2 mt-auto">
                                    @if($readyCount > 0)
                                        <form action="/jadwal/kd/{{ $kd }}/farm" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-danger w-100 fw-bold shadow-sm py-2">
                                                <i class="bi bi-lightning-charge-fill me-1"></i> Panen Serentak ({{ $readyCount }} Char)
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-secondary w-100 fw-bold py-2" disabled>
                                            <i class="bi bi-clock-history me-1"></i> Sedang Cooldown
                                        </button>
                                    @endif

                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-secondary flex-grow-1 py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKD{{ $kd }}" aria-expanded="false">
                                            <i class="bi bi-list-ul me-1"></i> Rincian Char ({{ $totalChars }})
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success fw-bold btn-bot-notify-kd px-2 py-2 d-flex align-items-center gap-1 shadow-xs"
                                            data-kd="{{ $kd }}"
                                            title="Kirim Otomatis via Bot Fonnte (Tanpa Buka WhatsApp)">
                                            <i class="bi bi-robot"></i> <span style="font-size: 11px;">Bot</span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success fw-bold btn-wa-single-kd px-2 py-2 d-flex align-items-center gap-1"
                                            data-kd="{{ $kd }}" 
                                            data-ready="{{ $readyCount }}" 
                                            data-total="{{ $totalChars }}" 
                                            data-cooldown="{{ $cooldownText }}" 
                                            data-cooldown-hours="{{ $kdCooldownHours }}" 
                                            data-target-time="{{ $maxCooldownEnd ? $maxCooldownEnd->timestamp : 0 }}"
                                            data-worker="{{ $activeWorker ?? '' }}"
                                            title="Buka Chat WhatsApp (Direct WA)">
                                            <i class="bi bi-whatsapp"></i> <span style="font-size: 11px;">WA</span>
                                        </button>
                                    </div>

                                    <!-- Collapse Rincian Akun & Karakter di KD ini -->
                                    <div class="collapse mt-2" id="collapseKD{{ $kd }}">
                                        <div class="card card-body bg-light p-2 mb-0 border" style="max-height: 260px; overflow-y: auto;">
                                            @foreach($accountsInKd as $accId => $charsOfAcc)
                                                @php
                                                    $acc = $charsOfAcc->first()->gameAccount;
                                                    $canBulkFarm = false;
                                                    foreach($charsOfAcc as $c) {
                                                        if(!$c->last_farmed_at || now()->greaterThanOrEqualTo($c->last_farmed_at->copy()->addMinutes($kdCooldownMinutes))) {
                                                            $canBulkFarm = true;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                <div class="bg-white border rounded p-2 mb-2 shadow-xs">
                                                    <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                                                        <div class="text-truncate me-1" style="font-size: 11px;">
                                                            <b class="text-dark"><i class="bi bi-envelope-fill text-primary me-1"></i> {{ $acc->email ?? 'Akun' }}</b>
                                                            @if($acc)
                                                                <button type="button" class="btn btn-link text-primary p-0 ms-1" style="font-size: 11px; text-decoration: none;" data-bs-toggle="modal" data-bs-target="#modalEditAcc{{ $acc->id }}" title="Edit Akun Gmail">
                                                                    <i class="bi bi-pencil-square"></i>
                                                                </button>
                                                            @endif
                                                            @if($acc && $acc->password)
                                                                <span class="text-muted ms-1 small"><i class="bi bi-key"></i> {{ $acc->password }}</span>
                                                            @endif
                                                            @if($acc && $acc->notes)
                                                                <span class="badge bg-light text-secondary border fw-normal ms-1" style="font-size: 9px;">{{ $acc->notes }}</span>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex align-items-center gap-1">
                                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 9px;">{{ $charsOfAcc->count() }} Char</span>
                                                            @if($canBulkFarm && $acc)
                                                                <form action="/akun/char-bulk/farm" method="POST" class="m-0 d-inline">
                                                                    @csrf
                                                                    <input type="hidden" name="game_account_id" value="{{ $acc->id }}">
                                                                    <input type="hidden" name="kd_number" value="{{ $kd }}">
                                                                    <button type="submit" class="btn btn-xs btn-outline-success px-1" title="Panen semua char email ini di KD #{{ $kd }}">
                                                                        <i class="bi bi-check2-all"></i> Selesai
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="d-flex flex-column gap-1">
                                                        @foreach($charsOfAcc as $char)
                                                            @php
                                                                $charOnCooldown = false;
                                                                $charCooldownText = '';
                                                                if($char->last_farmed_at) {
                                                                    $charEnd = $char->last_farmed_at->copy()->addMinutes($kdCooldownMinutes);
                                                                    if(now()->lessThan($charEnd)) {
                                                                        $charOnCooldown = true;
                                                                        $sisaMenitChar = now()->diffInMinutes($charEnd);
                                                                        $jChar = floor($sisaMenitChar / 60);
                                                                        $mChar = $sisaMenitChar % 60;
                                                                        $charCooldownText = ($jChar > 0 ? $jChar.'j ' : '') . $mChar.'m';
                                                                    }
                                                                }
                                                            @endphp
                                                            <div class="d-flex justify-content-between align-items-center bg-light rounded px-2 py-1" style="font-size: 11px;">
                                                                <div>
                                                                    <span class="fw-semibold text-dark">{{ $char->ign }}</span>
                                                                    <span class="text-muted ms-1" style="font-size: 10px;">(Log: {{ $char->farm_count }}x)</span>
                                                                </div>
                                                                <div class="d-flex align-items-center gap-1">
                                                                    @if($charOnCooldown)
                                                                        <span class="badge bg-secondary" style="font-size: 9px;">
                                                                            <i class="bi bi-clock-history"></i> {{ $charCooldownText }}
                                                                        </span>
                                                                    @else
                                                                        <form action="/akun/char/{{ $char->id }}/farm" method="POST" class="m-0 d-inline">
                                                                            @csrf
                                                                            <button type="submit" class="btn btn-xs btn-success py-0 px-2" style="font-size: 9px;">
                                                                                <i class="bi bi-check2"></i> Selesai
                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                    <div class="dropdown d-inline">
                                                                        <button class="btn btn-xs btn-outline-secondary border-0 py-0 px-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu Aksi Char">
                                                                            <i class="bi bi-three-dots-vertical"></i>
                                                                        </button>
                                                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="font-size: 12px; min-width: 170px;">
                                                                            <li>
                                                                                <button type="button" class="dropdown-item py-1" data-bs-toggle="modal" data-bs-target="#modalAdjustChar{{ $char->id }}">
                                                                                    <i class="bi bi-sliders text-primary me-2"></i> Atur Log / Jeda
                                                                                </button>
                                                                            </li>
                                                                            <li>
                                                                                <button type="button" class="dropdown-item py-1" data-bs-toggle="modal" data-bs-target="#modalEditChar{{ $char->id }}">
                                                                                    <i class="bi bi-pencil-square text-warning me-2"></i> Edit Karakter
                                                                                </button>
                                                                            </li>
                                                                            <li><hr class="dropdown-divider my-1"></li>
                                                                            @if($isAdmin)
                                                                            <li>
                                                                                <form action="/akun/char/{{ $char->id }}/hapus" method="POST" class="m-0" onsubmit="return confirm('Yakin hapus karakter {{ $char->ign }}?');">
                                                                                    @csrf
                                                                                    <button type="submit" class="dropdown-item text-danger py-1">
                                                                                        <i class="bi bi-trash me-2"></i> Hapus Karakter
                                                                                    </button>
                                                                                </form>
                                                                            </li>
                                                                            @else
                                                                            <li>
                                                                                <span class="dropdown-item text-muted py-1" style="cursor: default; font-size: 12px;">
                                                                                    <i class="bi bi-lock-fill me-2 text-secondary"></i> Hapus (Admin Only)
                                                                                </span>
                                                                            </li>
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary"></i>
                                <h5 class="fw-bold">Belum Ada Karakter di Kingdom Manapun</h5>
                                <p class="text-muted small">Tambahkan akun Gmail dan suntikkan karakter ke Kingdom melalui panel di sebelah kiri.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Daftar Akun yang Belum Memiliki Char -->
            @if(isset($unassignedAccounts) && $unassignedAccounts->count() > 0)
                <div class="card border-top border-4 border-warning shadow-sm mt-3 mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="fw-bold text-dark small">
                            <i class="bi bi-exclamation-circle-fill text-warning me-1"></i> Akun Gmail Belum Memiliki Karakter ({{ $unassignedAccounts->count() }})
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            @foreach($unassignedAccounts as $uAcc)
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-center">
                                        <div class="text-truncate me-2">
                                            <div class="fw-bold small text-truncate">{{ $uAcc->email }}</div>
                                            <div class="text-muted" style="font-size: 11px;"><i class="bi bi-key"></i> {{ $uAcc->password ?? 'Kosong' }}</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">0 Char</span>
                                            <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditAcc{{ $uAcc->id }}" title="Edit Akun">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="/akun/{{ $uAcc->id }}/hapus" method="POST" class="m-0 d-inline" onsubmit="return confirm('Yakin ingin menghapus akun {{ $uAcc->email }}?');">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus Akun">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- MODALS EDIT AKUN GMAIL -->
@foreach($accounts as $acc)
    <div class="modal fade" id="modalEditAcc{{ $acc->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light py-2">
                    <h6 class="modal-title fw-bold text-primary"><i class="bi bi-pencil-square me-1"></i> Edit Akun Gmail</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/akun/{{ $acc->id }}/update" method="POST">
                    @csrf
                    <div class="modal-body p-3">
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted">EMAIL</label>
                            <input type="email" name="email" value="{{ $acc->email }}" required class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted">PASSWORD (OPSIONAL)</label>
                            <input type="text" name="password" value="{{ $acc->password }}" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted">CATATAN</label>
                            <input type="text" name="notes" value="{{ $acc->notes }}" class="form-control form-control-sm" placeholder="Bind Lilith, dll">
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-save me-1"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- MODALS EDIT & ADJUST LOG CHAR -->
@foreach($characters as $char)
    <!-- Modal Edit Char -->
    <div class="modal fade" id="modalEditChar{{ $char->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light py-2">
                    <h6 class="modal-title fw-bold text-warning"><i class="bi bi-pencil-square me-1"></i> Edit Karakter</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/akun/char/{{ $char->id }}/update" method="POST">
                    @csrf
                    <div class="modal-body p-3">
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted">IGN / NAMA CHAR</label>
                            <input type="text" name="ign" value="{{ $char->ign }}" required class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted">NOMOR KD</label>
                            <input type="number" name="kd_number" value="{{ $char->kd_number }}" required class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted">PINDAH KE AKUN GMAIL</label>
                            <select name="game_account_id" class="form-select form-select-sm" required>
                                @foreach($accounts as $a)
                                    <option value="{{ $a->id }}" {{ $char->game_account_id == $a->id ? 'selected' : '' }}>
                                        {{ $a->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-save me-1"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Atur Log / Jeda Char -->
    <div class="modal fade" id="modalAdjustChar{{ $char->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light py-2">
                    <h6 class="modal-title fw-bold text-primary"><i class="bi bi-sliders me-1"></i> Atur Log & Jeda: {{ $char->ign }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/akun/char/{{ $char->id }}/adjust-log" method="POST">
                    @csrf
                    <div class="modal-body p-3">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">OPERATOR JOKI</label>
                            <select name="worker_name" class="form-select form-select-sm">
                                @foreach($knownWorkers as $w)
                                    <option value="{{ $w }}" {{ ($activeWorker ?? '') === $w ? 'selected' : '' }}>{{ $w }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">AKSI PENGATURAN</label>
                            <select name="action" class="form-select form-select-sm" onchange="document.getElementById('customInputChar{{ $char->id }}').classList.toggle('d-none', this.value !== 'set')">
                                <option value="reset_cooldown">✨ Reset Jeda (Langsung Jadikan Siap Panen)</option>
                                <option value="decrement">Kurangi Log 1 (-1)</option>
                                <option value="reset">Reset Log Jadi 0</option>
                                <option value="set">Atur Angka Tertentu (Custom Log)</option>
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="customInputChar{{ $char->id }}">
                            <label class="form-label small fw-bold text-muted">JUMLAH LOG BARU</label>
                            <input type="number" name="custom_count" class="form-control form-control-sm" min="0" placeholder="Contoh: 50">
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-check-circle me-1"></i> Terapkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/satset-v2.js?v={{ time() }}"></script>
</body>
</html>