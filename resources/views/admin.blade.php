<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - RoK Joki Admin</title>
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
        .sidebar a.admin-link { color: #ffc107; }
        .sidebar a.admin-link:hover, .sidebar a.admin-link.active { background-color: #ffc107; color: #212529; }
        .content { margin-left: 250px; padding: 30px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 24px; }
        .card-header { background-color: #fff; border-bottom: 2px solid #edf2f9; font-weight: 700; border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
        .badge-admin { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .badge-operator { background-color: #6c757d; }
        .btn-xs { padding: 2px 8px; font-size: 0.75rem; }
        .farm-stat-badge { font-size: 0.75rem; padding: 3px 8px; border-radius: 20px; display: inline-block; margin: 2px; }
        .table-hover tbody tr:hover { background-color: #f0f4ff; }
        .page-header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; border-radius: 12px; padding: 24px; margin-bottom: 28px; }
        .stat-card { border-radius: 12px; padding: 20px; color: white; margin-bottom: 16px; }
        .input-farm { width: 60px; text-align: center; }
        .adjust-form { display: inline-flex; align-items: center; gap: 4px; }
        @media (max-width: 768px) { .sidebar { display: none; } .content { margin-left: 0; padding: 15px; } }
    </style>
</head>
<body>

<!-- Sidebar -->
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
            <div>
                <div class="text-white fw-semibold" style="font-size:13px;">{{ auth()->user()->name }}</div>
                <div class="text-warning" style="font-size:11px;"><i class="bi bi-shield-fill-check"></i> Admin</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>
    @endauth
</div>

<!-- Main Content -->
<div class="content">

    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="h3 mb-1 fw-bold"><i class="bi bi-shield-lock-fill me-2"></i>Panel Admin</h1>
                <p class="mb-0 opacity-75">Manajemen pengguna & kontrol data macul karakter</p>
            </div>
            <div class="text-end">
                <div class="badge bg-warning text-dark fs-6 px-3 py-2">
                    <i class="bi bi-people-fill me-1"></i>{{ $users->count() }} User
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Flash -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <div class="fs-2 fw-bold">{{ $users->count() }}</div>
                <div class="opacity-80"><i class="bi bi-people"></i> Total User</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                <div class="fs-2 fw-bold">{{ $users->where('is_admin', true)->count() }}</div>
                <div class="opacity-80"><i class="bi bi-shield-check"></i> Admin</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                <div class="fs-2 fw-bold">{{ $characters->count() }}</div>
                <div class="opacity-80"><i class="bi bi-person-badge"></i> Total Char</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                <div class="fs-2 fw-bold">{{ $characters->sum('farm_count') }}</div>
                <div class="opacity-80"><i class="bi bi-hammer"></i> Total Macul</div>
            </div>
        </div>
    </div>

    <!-- CARD: Manajemen Pengguna -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="bi bi-people-fill text-primary me-2"></i>Manajemen Pengguna</span>
            <span class="badge bg-primary">{{ $users->count() }} user</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Bergabung</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $i => $user)
                        <tr class="{{ $user->id === auth()->id() ? 'table-warning' : '' }}">
                            <td class="px-4">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                                         style="width:36px;height:36px;font-size:14px;background:{{ $user->is_admin ? 'linear-gradient(135deg,#667eea,#764ba2)' : '#6c757d' }}">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        @if($user->id === auth()->id())
                                            <small class="text-muted">(kamu)</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><span class="text-muted">{{ $user->email }}</span></td>
                            <td>
                                @if($user->is_admin)
                                    <span class="badge badge-admin px-2 py-1"><i class="bi bi-shield-check me-1"></i>Admin</span>
                                @else
                                    <span class="badge badge-operator px-2 py-1"><i class="bi bi-person me-1"></i>Operator</span>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $user->created_at ? $user->created_at->diffForHumans() : '-' }}</small></td>
                            <td class="text-center">
                                @if($user->id !== auth()->id())
                                    <div class="d-flex gap-2 justify-content-center">
                                        <!-- Toggle Admin -->
                                        <form action="/admin/user/{{ $user->id }}/toggle-admin" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $user->is_admin ? 'btn-outline-secondary' : 'btn-outline-primary' }}"
                                                    title="{{ $user->is_admin ? 'Jadikan Operator' : 'Jadikan Admin' }}"
                                                    onclick="return confirm('Ubah status {{ $user->name }} jadi {{ $user->is_admin ? 'Operator' : 'Admin' }}?')">
                                                <i class="bi {{ $user->is_admin ? 'bi-person-dash' : 'bi-shield-plus' }}"></i>
                                                {{ $user->is_admin ? 'Jadikan Operator' : 'Jadikan Admin' }}
                                            </button>
                                        </form>
                                        <!-- Reset Password User -->
                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                                title="Reset Password"
                                                onclick="showResetPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                            <i class="bi bi-key"></i> Sandi
                                        </button>
                                        <!-- Hapus User -->
                                        <form action="/admin/user/{{ $user->id }}/delete" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('⚠️ Yakin hapus user {{ $user->name }}? Aksi ini tidak bisa dibatalkan!')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted small"><i class="bi bi-lock"></i> Akun kamu</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @if($users->isEmpty())
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada user terdaftar</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CARD: Adjust Jumlah Macul Per Karakter -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="bi bi-hammer text-success me-2"></i>Adjust Jumlah Macul Per Karakter</span>
            <span class="badge bg-success">{{ $characters->count() }} char</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabel-macul">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">IGN</th>
                            <th>KD</th>
                            <th>Total Macul</th>
                            <th>Detail Per Worker</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($characters as $char)
                        <tr id="char-row-{{ $char->id }}">
                            <td class="px-4">
                                <div class="fw-semibold">{{ $char->ign }}</div>
                                <small class="text-muted">{{ $char->gameAccount->email ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary">KD #{{ $char->kd_number }}</span>
                            </td>
                            <td>
                                <span class="fw-bold fs-5 text-success" id="total-{{ $char->id }}">{{ $char->farm_count }}</span>x
                            </td>
                            <td>
                                @if(!empty($char->farmer_stats))
                                    @foreach($char->farmer_stats as $worker => $cnt)
                                        @if($cnt > 0)
                                        <span class="farm-stat-badge bg-light border text-dark">
                                            <i class="bi bi-person-fill text-primary"></i> {{ $worker }}: <strong>{{ $cnt }}x</strong>
                                        </span>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-success" onclick="showAdjustModal({{ $char->id }}, '{{ addslashes($char->ign) }}', {{ json_encode($char->farmer_stats ?? []) }}, {{ json_encode($workers) }})">
                                    <i class="bi bi-pencil-square"></i> Adjust
                                </button>
                            </td>
                        </tr>
                        @endforeach
                        @if($characters->isEmpty())
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada karakter terdaftar</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div><!-- /.content -->

<!-- Modal Adjust Macul -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-hammer me-2"></i>Adjust Macul: <span id="modal-char-name"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-body-content">
                <!-- diisi via JS -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const adjustModal = new bootstrap.Modal(document.getElementById('adjustModal'));

function showAdjustModal(charId, charName, farmerStats, allWorkers) {
    document.getElementById('modal-char-name').textContent = charName;

    // Gabungkan worker yang sudah ada di stats + semua user terdaftar
    const workerSet = new Set([...Object.keys(farmerStats), ...allWorkers]);
    const workers = [...workerSet];

    let html = `<div class="mb-3 text-muted small"><i class="bi bi-info-circle"></i> Gunakan tombol +/- atau set langsung ke angka tertentu.</div>`;

    workers.forEach(worker => {
        const current = farmerStats[worker] ?? 0;
        html += `
        <div class="border rounded p-3 mb-2">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-semibold"><i class="bi bi-person-fill text-primary"></i> ${worker}</span>
                <span class="badge bg-success" id="badge-${charId}-${worker.replace(/\s/g,'_')}">${current}x</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button class="btn btn-outline-danger btn-sm" onclick="doAdjust(${charId}, '${worker}', 'decrement', 0)">
                    <i class="bi bi-dash-lg"></i> Kurangi
                </button>
                <button class="btn btn-outline-success btn-sm" onclick="doAdjust(${charId}, '${worker}', 'increment', 0)">
                    <i class="bi bi-plus-lg"></i> Tambah
                </button>
                <div class="input-group input-group-sm" style="width:140px;">
                    <input type="number" id="set-input-${charId}-${worker.replace(/\s/g,'_')}"
                           class="form-control text-center" value="${current}" min="0" placeholder="Set">
                    <button class="btn btn-outline-primary" onclick="setFarm(${charId}, '${worker}')">Set</button>
                </div>
                <button class="btn btn-outline-secondary btn-sm" onclick="if(confirm('Reset macul ${worker} untuk char ini?')) doAdjust(${charId}, '${worker}', 'reset', 0)">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
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
            // Update badge di modal
            const key = worker.replace(/\s/g, '_');
            const badge = document.getElementById(`badge-${charId}-${key}`);
            if (badge) badge.textContent = (data.farmer_stats[worker] ?? 0) + 'x';

            // Update input set
            const input = document.getElementById(`set-input-${charId}-${key}`);
            if (input) input.value = data.farmer_stats[worker] ?? 0;

            // Update total di tabel
            const total = document.getElementById(`total-${charId}`);
            if (total) total.textContent = data.farm_count;

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
    t.className = `alert alert-${type} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
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
</script>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-key-fill me-1"></i> Reset Password</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formResetPassword" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <p class="small text-muted mb-2">Ganti password user: <strong id="resetTargetUser" class="text-dark"></strong></p>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Password Baru:</label>
                        <input type="text" name="password" class="form-control form-control-sm" required minlength="6" placeholder="Minimal 6 karakter" value="password123">
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-warning fw-bold"><i class="bi bi-check-circle me-1"></i> Simpan Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
