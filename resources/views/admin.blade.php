<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Panel Admin - RoK Joki</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/mobile.css">
    <style>
        :root {
            --app-bg: #f8fafc;
            --card-border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }
        body { 
            background-color: var(--app-bg); 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            color: var(--text-main);
            overflow-x: hidden; 
        }
        .sidebar { 
            min-height: 100vh; 
            width: 250px; 
            background-color: #0f172a; 
            position: fixed; 
            top: 0; 
            left: 0; 
            padding-top: 20px; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
            z-index: 100; 
        }
        .sidebar .brand { color: #fff; font-size: 20px; font-weight: 700; text-align: center; margin-bottom: 24px; }
        .sidebar .brand span { color: #3b82f6; }
        .sidebar a { color: #94a3b8; text-decoration: none; padding: 11px 20px; display: block; font-weight: 500; font-size: 14px; transition: 0.2s; margin-bottom: 4px; }
        .sidebar a:hover, .sidebar a.active { background-color: #1e293b; color: #fff; border-left: 3px solid #3b82f6; }
        .sidebar a i { margin-right: 10px; font-size: 1rem; }
        .sidebar a.admin-link { color: #f59e0b; }
        .sidebar a.admin-link.active { background-color: #1e293b; color: #fbbf24; border-left-color: #f59e0b; }
        .content { margin-left: 250px; padding: 24px 30px; }
        
        .card { 
            border: 1px solid var(--card-border); 
            border-radius: 12px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.04); 
            background: #ffffff;
            margin-bottom: 20px; 
        }
        .card-header { 
            background-color: #ffffff; 
            border-bottom: 1px solid var(--card-border); 
            font-weight: 600; 
            border-radius: 12px 12px 0 0 !important; 
            padding: 14px 18px; 
        }
        
        /* Stat Cards Minimalist */
        .stat-box {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            margin-bottom: 14px;
        }
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .badge-role {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge-role-admin { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-role-operator { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        /* Mobile Card User & Character */
        .mobile-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 10px;
            transition: 0.15s;
        }
        .mobile-card:hover {
            border-color: #cbd5e1;
        }
        
        .farm-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #334155;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 16px;
            margin: 2px 2px 2px 0;
        }

        @media (max-width: 991.98px) {
            .content { margin-left: 0 !important; padding: 12px !important; }
            .page-title-box { margin-bottom: 14px !important; }
            .stat-box { padding: 10px 12px; }
            .stat-icon { width: 36px; height: 36px; font-size: 16px; }
        }
    </style>
</head>
<body>

<!-- Sidebar Menu (Desktop) -->
<div class="sidebar">
    <div>
        <div class="brand"><i class="bi bi-shield-shaded"></i> RoK<span>Joki</span></div>
        <a href="/stok"><i class="bi bi-box-seam"></i> Dashboard Stok</a>
        <a href="/kalkulator"><i class="bi bi-calculator"></i> Kalkulator Pajak</a>
        <a href="/riwayat"><i class="bi bi-journal-text"></i> Buku Kas</a>
        <a href="/akun"><i class="bi bi-people"></i> Manajemen Ternak</a>
        <a href="/jadwal"><i class="bi bi-alarm"></i> Jadwal Farm (KD)</a>
        <a href="/admin" class="admin-link active"><i class="bi bi-shield-lock"></i> Panel Admin</a>
    </div>
    @auth
    <div class="p-3 border-top border-secondary-subtle">
        <div class="d-flex align-items-center mb-2">
            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 34px; height: 34px; font-size: 14px;">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="text-truncate">
                <div class="text-white fw-semibold small text-truncate">{{ auth()->user()->name }}</div>
                <div class="text-warning text-truncate" style="font-size:11px;"><i class="bi bi-shield-fill-check"></i> Admin</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm w-100 py-1" style="font-size: 11px;">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
        </form>
    </div>
    @endauth
</div>

<!-- Main Content -->
<div class="content">

    <!-- Simple Clean Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 page-title-box">
        <div>
            <h4 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill text-warning"></i> Panel Admin
            </h4>
            <p class="text-muted small m-0">Kontrol pengguna & data riwayat macul karakter</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="/stok" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 12px;">
                <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Kembali ke </span>Stok
            </a>
            <span class="badge bg-dark px-2 py-1 fw-normal" style="font-size: 12px;">
                <i class="bi bi-people me-1"></i> {{ $users->count() }} User
            </span>
        </div>
    </div>

    <!-- Alert Flash -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm py-2 px-3 small mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-6"></i>
            {{ session('success') }}
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm py-2 px-3 small mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
            {{ session('error') }}
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Simple Modern Stats Row (HP & Desktop Friendly) -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3">
            <div class="stat-box">
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark lh-1">{{ $users->count() }}</div>
                    <div class="text-muted small mt-1">Total User</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-box">
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark lh-1">{{ $users->where('is_admin', true)->count() }}</div>
                    <div class="text-muted small mt-1">Admin</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-box">
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark lh-1">{{ $characters->count() }}</div>
                    <div class="text-muted small mt-1">Total Char</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-box">
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-hammer"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark lh-1">{{ $characters->sum('farm_count') }}</div>
                    <div class="text-muted small mt-1">Total Macul</div>
                </div>
            </div>
        </div>
    </div>

    <!-- CARD 1: Manajemen Pengguna -->
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
            <span class="small fw-bold text-dark">
                <i class="bi bi-people text-primary me-1"></i> Manajemen Pengguna
            </span>
            <span class="badge bg-secondary-subtle text-secondary border small">{{ $users->count() }} Akun</span>
        </div>
        
        <div class="card-body p-2 p-md-0">
            <!-- Tampilan Desktop (Tabel Rapi) -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light text-muted">
                        <tr>
                            <th class="ps-3" style="width: 40px;">#</th>
                            <th>NAMA & EMAIL</th>
                            <th>STATUS</th>
                            <th>BERGABUNG</th>
                            <th class="text-center" style="width: 240px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $i => $user)
                        <tr class="{{ $user->id === auth()->id() ? 'table-warning bg-opacity-25' : '' }}">
                            <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                                         style="width:32px;height:32px;font-size:13px;background:{{ $user->is_admin ? '#f59e0b' : '#64748b' }}">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">
                                            {{ $user->name }}
                                            @if($user->id === auth()->id())
                                                <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">Kamu</span>
                                            @endif
                                        </div>
                                        <div class="text-muted" style="font-size: 11px;">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->is_admin)
                                    <span class="badge-role badge-role-admin"><i class="bi bi-shield-fill-check"></i> Admin</span>
                                @else
                                    <span class="badge-role badge-role-operator"><i class="bi bi-person"></i> Operator</span>
                                @endif
                            </td>
                            <td><span class="text-muted" style="font-size: 11px;">{{ $user->created_at ? $user->created_at->diffForHumans() : '-' }}</span></td>
                            <td class="text-center">
                                @if($user->id !== auth()->id())
                                    <div class="d-flex gap-1 justify-content-center">
                                        <!-- Reset Password -->
                                        <button type="button" class="btn btn-xs btn-outline-warning text-dark"
                                                title="Reset Sandi"
                                                onclick="showResetPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                            <i class="bi bi-key-fill me-1"></i> Sandi
                                        </button>
                                        <!-- Toggle Admin -->
                                        <form action="/admin/user/{{ $user->id }}/toggle-admin" method="POST" class="d-inline m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-xs {{ $user->is_admin ? 'btn-outline-secondary' : 'btn-outline-primary' }}"
                                                    onclick="return confirm('Ubah status {{ $user->name }} jadi {{ $user->is_admin ? 'Operator' : 'Admin' }}?')">
                                                <i class="bi {{ $user->is_admin ? 'bi-person-dash' : 'bi-shield-plus' }}"></i>
                                                {{ $user->is_admin ? 'Jadikan Op' : 'Jadikan Admin' }}
                                            </button>
                                        </form>
                                        <!-- Hapus User -->
                                        <form action="/admin/user/{{ $user->id }}/delete" method="POST" class="d-inline m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-danger"
                                                    onclick="return confirm('⚠️ Yakin hapus user {{ $user->name }}? Aksi ini tidak bisa dibatalkan!')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted small" style="font-size: 11px;"><i class="bi bi-lock me-1"></i> Akun Utama</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-3 text-muted">Belum ada user terdaftar</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Tampilan Mobile (Card List Praktis & Ramah Jari HP) -->
            <div class="d-md-none">
                @forelse($users as $user)
                <div class="mobile-card {{ $user->id === auth()->id() ? 'border-warning' : '' }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                                 style="width:36px;height:36px;font-size:14px;background:{{ $user->is_admin ? '#f59e0b' : '#64748b' }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 14px;">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                        <span class="badge bg-warning text-dark ms-1" style="font-size: 9px;">Kamu</span>
                                    @endif
                                </div>
                                <div class="text-muted small" style="font-size: 11px;">{{ $user->email }}</div>
                            </div>
                        </div>
                        <div>
                            @if($user->is_admin)
                                <span class="badge-role badge-role-admin"><i class="bi bi-shield-fill-check"></i> Admin</span>
                            @else
                                <span class="badge-role badge-role-operator"><i class="bi bi-person"></i> Operator</span>
                            @endif
                        </div>
                    </div>

                    @if($user->id !== auth()->id())
                    <div class="d-flex gap-1 pt-2 border-top mt-2">
                        <button type="button" class="btn btn-sm btn-outline-warning text-dark flex-grow-1 fw-semibold py-1"
                                style="font-size: 11px;"
                                onclick="showResetPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                            <i class="bi bi-key-fill me-1"></i> Reset Sandi
                        </button>
                        <form action="/admin/user/{{ $user->id }}/toggle-admin" method="POST" class="d-inline m-0 flex-grow-1">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $user->is_admin ? 'btn-outline-secondary' : 'btn-outline-primary' }} w-100 fw-semibold py-1"
                                    style="font-size: 11px;"
                                    onclick="return confirm('Ubah status {{ $user->name }} jadi {{ $user->is_admin ? 'Operator' : 'Admin' }}?')">
                                <i class="bi {{ $user->is_admin ? 'bi-person-dash' : 'bi-shield-plus' }} me-1"></i>
                                {{ $user->is_admin ? 'Jadikan Op' : 'Jadikan Admin' }}
                            </button>
                        </form>
                        <form action="/admin/user/{{ $user->id }}/delete" method="POST" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2"
                                    onclick="return confirm('⚠️ Yakin hapus user {{ $user->name }}?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="text-muted small text-center pt-2 border-top mt-2" style="font-size: 11px;">
                        <i class="bi bi-lock me-1"></i> Akun aktif kamu saat ini
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center py-3 text-muted small">Belum ada user terdaftar</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- CARD 2: Adjust Macul Karakter -->
    <div class="card mb-4">
        <div class="card-header py-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-dark">
                    <i class="bi bi-hammer text-success me-1"></i> Adjust Macul Karakter
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle small">{{ $characters->count() }} Char</span>
            </div>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="filterCharInput" class="form-control border-start-0 border-end-0" placeholder="Ketik IGN atau Nomor KD..." onkeyup="filterCharacters(this.value)">
                <button class="btn btn-outline-secondary border-start-0" type="button" onclick="document.getElementById('filterCharInput').value=''; filterCharacters('');" title="Hapus pencarian">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>

        <div class="card-body p-2 p-md-0">
            <!-- Tampilan Desktop (Tabel Rapi) -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0" id="tabel-macul" style="font-size: 13px;">
                    <thead class="table-light text-muted">
                        <tr>
                            <th class="ps-3">IGN KARAKTER</th>
                            <th style="width: 90px;">KD</th>
                            <th style="width: 120px;">TOTAL MACUL</th>
                            <th>DETAIL PER OPERATOR JOKI</th>
                            <th class="text-center" style="width: 100px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($characters as $char)
                        <tr id="char-row-{{ $char->id }}" class="char-item" data-ign="{{ strtolower($char->ign) }}" data-kd="{{ $char->kd_number }}">
                            <td class="ps-3">
                                <div class="fw-bold text-dark">{{ $char->ign }}</div>
                                <div class="text-muted small" style="font-size: 11px;">{{ $char->gameAccount->email ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">KD #{{ $char->kd_number }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-success fs-6" id="total-{{ $char->id }}">{{ $char->farm_count }}</span>x Macul
                            </td>
                            <td>
                                @if(!empty($char->farmer_stats))
                                    @foreach($char->farmer_stats as $worker => $cnt)
                                        @if($cnt > 0)
                                        <span class="farm-pill">
                                            <i class="bi bi-person text-secondary"></i> {{ $worker }}: <strong class="text-primary">{{ $cnt }}x</strong>
                                        </span>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="text-muted fst-italic small" style="font-size: 11px;">Belum ada log</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-xs btn-outline-success fw-semibold" onclick="showAdjustModal({{ $char->id }}, '{{ addslashes($char->ign) }}', {{ json_encode($char->farmer_stats ?? []) }}, {{ json_encode($workers) }})">
                                    <i class="bi bi-pencil-square me-1"></i> Adjust
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada karakter terdaftar</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Tampilan Mobile (Card List Super Ringkas & Nyaman di Layar HP) -->
            <div class="d-md-none" id="mobile-char-list">
                @forelse($characters as $char)
                <div class="mobile-card char-item" data-ign="{{ strtolower($char->ign) }}" data-kd="{{ $char->kd_number }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 14px;">{{ $char->ign }}</div>
                            <div class="text-muted" style="font-size: 11px;">{{ $char->gameAccount->email ?? '-' }}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1" style="font-size: 10px;">KD #{{ $char->kd_number }}</span>
                            <span class="badge bg-success text-white fw-bold" id="total-mob-{{ $char->id }}" style="font-size: 11px;">{{ $char->farm_count }}x</span>
                        </div>
                    </div>

                    <!-- Breakdown Workers -->
                    <div class="mb-2">
                        @if(!empty($char->farmer_stats))
                            @foreach($char->farmer_stats as $worker => $cnt)
                                @if($cnt > 0)
                                <span class="farm-pill">
                                    <i class="bi bi-person text-secondary"></i> {{ $worker }}: <b class="text-primary">{{ $cnt }}x</b>
                                </span>
                                @endif
                            @endforeach
                        @else
                            <span class="text-muted fst-italic" style="font-size: 10px;">Belum ada riwayat macul</span>
                        @endif
                    </div>

                    <!-- Tombol Adjust Thumb-Friendly -->
                    <button type="button" class="btn btn-sm btn-outline-success w-100 fw-bold py-1" style="font-size: 12px;"
                            onclick="showAdjustModal({{ $char->id }}, '{{ addslashes($char->ign) }}', {{ json_encode($char->farmer_stats ?? []) }}, {{ json_encode($workers) }})">
                        <i class="bi bi-sliders me-1"></i> Atur / Adjust Log Macul
                    </button>
                </div>
                @empty
                <div class="text-center py-3 text-muted small">Belum ada karakter terdaftar</div>
                @endforelse
            </div>
        </div>
    </div>

</div><!-- /.content -->

<!-- Modal Adjust Macul (Tetap Menggunakan ID & Struktur yang Sama Persis) -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-hammer me-1"></i> Adjust Macul: <span id="modal-char-name"></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" id="modal-body-content">
                <!-- diisi via JS -->
            </div>
            <div class="modal-footer p-2 bg-light">
                <button type="button" class="btn btn-sm btn-secondary w-100" data-bs-dismiss="modal">Selesai / Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-key-fill me-1"></i> Reset Sandi User</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formResetPassword" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <p class="small text-muted mb-2">Ganti password untuk user: <strong id="resetTargetUser" class="text-dark"></strong></p>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">PASSWORD BARU</label>
                        <input type="text" name="password" class="form-control form-control-sm" required minlength="6" placeholder="Minimal 6 karakter" value="password123">
                    </div>
                </div>
                <div class="modal-footer p-2 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-warning fw-bold"><i class="bi bi-check-circle me-1"></i> Simpan Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/satset.js"></script>
<script>
const adjustModal = new bootstrap.Modal(document.getElementById('adjustModal'));

function showAdjustModal(charId, charName, farmerStats, allWorkers) {
    document.getElementById('modal-char-name').textContent = charName;

    // Gabungkan worker yang sudah ada di stats + semua user terdaftar
    const workerSet = new Set([...Object.keys(farmerStats), ...allWorkers]);
    const workers = [...workerSet];

    let html = `<div class="mb-2 text-muted small" style="font-size: 11px;"><i class="bi bi-info-circle me-1"></i> Gunakan tombol +/- atau ketik angka custom untuk mengatur hitungan panen joki.</div>`;

    if (workers.length === 0) {
        html += `<div class="text-center py-3 text-muted small">Belum ada operator joki terdaftar.</div>`;
    }

    workers.forEach(worker => {
        const current = farmerStats[worker] ?? 0;
        const key = worker.replace(/\s/g,'_');
        html += `
        <div class="border rounded p-2 mb-2 bg-light">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-bold small text-dark"><i class="bi bi-person-fill text-primary me-1"></i> ${worker}</span>
                <span class="badge bg-success" id="badge-${charId}-${key}">${current}x Macul</span>
            </div>
            <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap">
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-outline-danger btn-sm px-2 py-1" onclick="doAdjust(${charId}, '${worker}', 'decrement', 0)" title="Kurangi 1">
                        <i class="bi bi-dash"></i> 1
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm px-2 py-1" onclick="doAdjust(${charId}, '${worker}', 'increment', 0)" title="Tambah 1">
                        <i class="bi bi-plus"></i> 1
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" title="Reset ke 0" onclick="if(confirm('Reset macul ${worker} untuk char ini?')) doAdjust(${charId}, '${worker}', 'reset', 0)">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
                <div class="input-group input-group-sm" style="width:115px;">
                    <input type="number" id="set-input-${charId}-${key}"
                           class="form-control text-center px-1" value="${current}" min="0" placeholder="0">
                    <button type="button" class="btn btn-primary px-2" onclick="setFarm(${charId}, '${worker}')">Set</button>
                </div>
            </div>
        </div>`;
    });

    document.getElementById('modal-body-content').innerHTML = html;
    adjustModal.show();
}

function doAdjust(charId, worker, action, count) {
    fetch(`/admin/char/${charId}/adjust-farm`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                         || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ action, worker, count })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const key = worker.replace(/\s/g, '_');
            const badge = document.getElementById(`badge-${charId}-${key}`);
            if (badge) badge.textContent = (data.farmer_stats[worker] ?? 0) + 'x Macul';

            const input = document.getElementById(`set-input-${charId}-${key}`);
            if (input) input.value = data.farmer_stats[worker] ?? 0;

            // Update total di tabel & card mobile
            const total = document.getElementById(`total-${charId}`);
            if (total) total.textContent = data.farm_count;
            const totalMob = document.getElementById(`total-mob-${charId}`);
            if (totalMob) totalMob.textContent = data.farm_count + 'x';

            showToast(data.message, 'success');
        } else {
            showToast('Gagal update!', 'danger');
        }
    })
    .catch(() => showToast('Terjadi error!', 'danger'));
}

function setFarm(charId, worker) {
    const key = worker.replace(/\s/g, '_');
    const input = document.getElementById(`set-input-${charId}-${key}`);
    const count = parseInt(input?.value || '0');
    doAdjust(charId, worker, 'set', count);
}

function showToast(msg, type) {
    const t = document.createElement('div');
    t.className = `alert alert-${type} alert-dismissible fade show position-fixed bottom-0 end-0 m-3 shadow`;
    t.style.zIndex = '9999';
    t.innerHTML = msg + `<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

function showResetPasswordModal(userId, userName) {
    document.getElementById('formResetPassword').action = `/admin/user/${userId}/reset-password`;
    document.getElementById('resetTargetUser').innerText = userName;
    new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}

function filterCharacters(keyword) {
    const q = keyword.toLowerCase().trim();
    document.querySelectorAll('.char-item').forEach(el => {
        const ign = el.getAttribute('data-ign') || '';
        const kd = el.getAttribute('data-kd') || '';
        if (ign.includes(q) || kd.includes(q)) {
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    });
}
</script>
</body>
</html>
