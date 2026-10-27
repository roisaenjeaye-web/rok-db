<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Pajak Trading Post - RoK Joki Admin</title>
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
        .mode-card { border: 2px solid #dee2e6; border-radius: 8px; padding: 12px; cursor: pointer; transition: 0.2s; }
        .mode-card:hover { border-color: #0d6efd; background-color: #f8faff; }
        .mode-card.active { border-color: #0d6efd; background-color: #eef4ff; }
        .form-check-input:checked { background-color: #0d6efd; border-color: #0d6efd; }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        <div class="brand"><i class="bi bi-shield-shaded"></i> RoK<span>Joki</span></div>
        <a href="/stok"><i class="bi bi-box-seam"></i> Dashboard Stok</a>
        <a href="/kalkulator" class="active"><i class="bi bi-calculator"></i> Kalkulator Pajak</a>
        <a href="/riwayat"><i class="bi bi-journal-text"></i> Buku Kas</a>
        <a href="/akun"><i class="bi bi-people"></i> Manajemen Ternak</a>
        <a href="/jadwal"><i class="bi bi-alarm"></i> Jadwal Farm (KD)</a>
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
            <h3 class="fw-bold text-dark m-0">Kalkulator Pajak Trading Post</h3>
            <p class="text-muted small mb-0">Hitung potongan pajak RSS (Food, Wood, Stone, Gold) dan sinkronisasi ke Dashboard Stok</p>
        </div>
        <a href="/stok" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-box-seam me-1"></i> Buka Dashboard Stok
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') ?? 'Terjadi kesalahan input pada formulir kalkulator.' }}
            @if($errors->any())
                <ul class="mb-0 mt-1 small">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Kolom Form Input Kalkulator -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm">
                <div class="card-header text-primary d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-sliders me-1"></i> Parameter Kalkulasi</span>
                    <span class="badge bg-primary-subtle text-primary border">Auto Sync</span>
                </div>
                <div class="card-body p-4">
                    <form action="/kalkulator/hitung" method="POST" id="calcForm">
                        @csrf

                        <!-- 1. Pilihan Mode Kalkulator -->
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">PILIH MODE KALKULASI</label>
                            @php
                                $selectedMode = old('calc_mode', session('calc_mode', 'deduct_tax'));
                            @endphp
                            
                            <div class="d-flex flex-column gap-2">
                                <label class="mode-card d-flex align-items-start {{ $selectedMode == 'deduct_tax' ? 'active' : '' }}" for="mode_deduct">
                                    <input class="form-check-input mt-1 me-2" type="radio" name="calc_mode" id="mode_deduct" value="deduct_tax" {{ $selectedMode == 'deduct_tax' ? 'checked' : '' }} onchange="updateModeUI()">
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 13px;">Total RSS - Pajak (Potong Pajak Langsung)</div>
                                        <div class="text-muted" style="font-size: 11px;">Contoh: Kirim <b>100M</b> - Pajak 20% ➔ Diterima <b>80M</b> (Pajak 20M)</div>
                                    </div>
                                </label>

                                <label class="mode-card d-flex align-items-start {{ $selectedMode == 'target_net' ? 'active' : '' }}" for="mode_target">
                                    <input class="form-check-input mt-1 me-2" type="radio" name="calc_mode" id="mode_target" value="target_net" {{ $selectedMode == 'target_net' ? 'checked' : '' }} onchange="updateModeUI()">
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 13px;">Target Diterima Bersih (Pajak Ditanggung Pengirim)</div>
                                        <div class="text-muted" style="font-size: 11px;">Contoh: Target Terima <b>100M</b> Bersih ➔ Wajib Kirim <b>125M</b> (Pajak 25M)</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- 2. Input RSS ke bawah (F, W, S, G) -->
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold d-flex justify-content-between align-items-center">
                                <span id="label_rss_input">INPUT JUMLAH RSS (M)</span>
                                <span class="badge bg-light text-muted border">Bisa isi salah satu / semua</span>
                            </label>

                            <div class="d-flex flex-column gap-2">
                                <!-- Food -->
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold" style="width: 105px;">🌽 Food</span>
                                    <input type="number" step="0.01" class="form-control rss-input" name="food" id="input_food" placeholder="0" value="{{ old('food', session('results.food.input', '')) }}" oninput="calculateRealtime()">
                                    <span class="input-group-text">M</span>
                                </div>

                                <!-- Wood -->
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold" style="width: 105px;">🪵 Wood</span>
                                    <input type="number" step="0.01" class="form-control rss-input" name="wood" id="input_wood" placeholder="0" value="{{ old('wood', session('results.wood.input', '')) }}" oninput="calculateRealtime()">
                                    <span class="input-group-text">M</span>
                                </div>

                                <!-- Stone -->
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold" style="width: 105px;">🪨 Stone</span>
                                    <input type="number" step="0.01" class="form-control rss-input" name="stone" id="input_stone" placeholder="0" value="{{ old('stone', session('results.stone.input', '')) }}" oninput="calculateRealtime()">
                                    <span class="input-group-text">M</span>
                                </div>

                                <!-- Gold -->
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold" style="width: 105px;">🪙 Gold</span>
                                    <input type="number" step="0.01" class="form-control rss-input" name="gold" id="input_gold" placeholder="0" value="{{ old('gold', session('results.gold.input', '')) }}" oninput="calculateRealtime()">
                                    <span class="input-group-text">M</span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Pajak TP (%) & Harga per 1M (Rp) -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">PAJAK TP (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" class="form-control" name="tax" id="input_tax" value="{{ old('tax', session('tax', 8)) }}" required oninput="calculateRealtime()">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">HARGA / 1M (OPSIONAL)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="price_per_m" id="input_price" placeholder="2500" value="{{ old('price_per_m', session('price_per_m', '')) }}" oninput="calculateRealtime()">
                                </div>
                            </div>
                        </div>

                        <!-- 4. Kategori Kingdom / KD (Opsional -> Masuk ke Dashboard Stok) -->
                        <div class="p-3 bg-light rounded border mb-3">
                            <div class="mb-2">
                                <label class="form-label text-dark small fw-bold mb-1">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> NOMOR KINGDOM / KD (OPSIONAL)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">#</span>
                                    <input type="number" class="form-control bg-white" name="kd_number" id="input_kd" placeholder="Contoh: 2359" value="{{ old('kd_number', session('kd_number', '')) }}" oninput="toggleKdActionUI()">
                                </div>
                                <div class="form-text small text-muted mt-1" style="font-size: 11px;">
                                    <i class="bi bi-info-circle text-primary"></i> <b>Logika Sistem:</b> Jika KD diisi (contoh: <code>2359</code>), hasil bersih RSS <b>langsung masuk ke Dashboard Stok</b>. Jika dikosongkan, hanya hitung di layar tanpa masuk ke dashboard manapun.
                                </div>
                            </div>

                            <!-- Opsi Aksi Stok KD -->
                            <div class="mt-2" id="box_stock_action">
                                <label class="form-label text-muted small fw-bold mb-1" style="font-size: 11px;">AKSI KE STOK KD</label>
                                @php $selectedAction = old('stock_action', session('stock_action', 'add')); @endphp
                                <select class="form-select form-select-sm" name="stock_action">
                                    <option value="add" {{ $selectedAction == 'add' ? 'selected' : '' }}>➕ Tambahkan ke Stok KD saat ini (Default)</option>
                                    <option value="set" {{ $selectedAction == 'set' ? 'selected' : '' }}>🔄 Timpa / Update Total Stok KD</option>
                                    <option value="deduct" {{ $selectedAction == 'deduct' ? 'selected' : '' }}>➖ Kurangi dari Stok KD (Jika terjual/dikirim keluar)</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
                            <i class="bi bi-calculator-fill me-2"></i> Hitung Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Hasil Kalkulasi & Ringkasan -->
        <div class="col-lg-7">
            @php
                $hasSessionResult = session('result');
                $isTargetMode = (session('calc_mode') === 'target_net');
            @endphp

            <div class="card shadow-sm border-top border-4 {{ $hasSessionResult && session('saved_to_dashboard') ? 'border-success' : 'border-primary' }}">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="fw-bold m-0 text-dark">
                        <i class="bi bi-receipt text-primary me-2"></i>Hasil Kalkulasi & Tagihan
                    </h5>
                    <div id="badge_mode_display">
                        @if($hasSessionResult)
                            @if(session('calc_mode') === 'deduct_tax')
                                <span class="badge bg-primary-subtle text-primary border">Mode: Total RSS - Pajak</span>
                            @else
                                <span class="badge bg-info-subtle text-info border">Mode: Target Diterima Bersih</span>
                            @endif
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border" id="js_badge_mode">Live Preview</span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Status Notifikasi Dashboard Stok -->
                    <div id="stock_status_alert">
                        @if($hasSessionResult)
                            @if(session('saved_to_dashboard'))
                                <div class="alert alert-success d-flex justify-content-between align-items-center border-0 shadow-xs mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                                        <div>
                                            <div class="fw-bold">Berhasil Masuk ke Dashboard Stok!</div>
                                            <div class="small">Stok untuk <b>KD #{{ session('kd_number') }}</b> otomatis diperbarui di database.</div>
                                        </div>
                                    </div>
                                    <a href="/stok" class="btn btn-sm btn-success fw-bold text-nowrap">
                                        <i class="bi bi-box-seam me-1"></i> Buka Stok
                                    </a>
                                </div>
                            @else
                                <div class="alert alert-secondary d-flex align-items-center border-0 shadow-xs mb-4 text-muted small">
                                    <i class="bi bi-info-circle-fill fs-5 me-2 text-secondary"></i>
                                    <div>
                                        <b>Mode Simulasi Hitung Saja:</b> Nomor KD tidak diisi, sehingga hasil kalkulasi ini <b>tidak masuk ke dashboard manapun</b>.
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-light border d-flex align-items-center mb-4 text-muted small" id="js_kd_preview_note">
                                <i class="bi bi-info-circle me-2 text-primary"></i>
                                <span>Ketik angka di formulir kiri untuk melihat simulasi kalkulasi secara instan.</span>
                            </div>
                        @endif
                    </div>

                    <!-- 4 Ringkasan Angka Utama -->
                    <div class="row g-2 mb-4 text-center">
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded border">
                                <div class="text-muted small fw-bold" id="lbl_stat_input">TOTAL INPUT</div>
                                <div class="fs-4 fw-bold text-dark mt-1" id="res_total_input">
                                    {{ $hasSessionResult ? session('total_gross') : '0' }}<span class="fs-6 text-muted fw-normal">M</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded border">
                                <div class="text-muted small fw-bold">POTONGAN PAJAK</div>
                                <div class="fs-4 fw-bold text-danger mt-1" id="res_total_tax">
                                    {{ $hasSessionResult ? session('total_tax') : '0' }}<span class="fs-6 text-muted fw-normal">M</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded border">
                                <div class="text-muted small fw-bold" id="lbl_stat_net">TOTAL BERSIH</div>
                                <div class="fs-4 fw-bold text-success mt-1" id="res_total_net">
                                    {{ $hasSessionResult ? session('total_net') : '0' }}<span class="fs-6 text-muted fw-normal">M</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded border">
                                <div class="text-muted small fw-bold">TOTAL TAGIHAN</div>
                                <div class="fs-5 fw-bold text-primary mt-1" id="res_total_price">
                                    Rp {{ $hasSessionResult ? number_format(session('total_price'), 0, ',', '.') : '0' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Rincian F, W, S, G -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>TIPE RSS</th>
                                    <th class="text-end" id="th_input_col">INPUT RSS</th>
                                    <th class="text-end">PAJAK (<span class="tax-rate-display">{{ $hasSessionResult ? session('tax') : '8' }}</span>%)</th>
                                    <th class="text-end" id="th_net_col">BERSIH DITERIMA</th>
                                    <th class="text-end">EST. HARGA (RP)</th>
                                </tr>
                            </thead>
                            <tbody id="res_tbody">
                                @php
                                    $resNames = [
                                        'food' => ['icon' => '🌽', 'name' => 'Food'],
                                        'wood' => ['icon' => '🪵', 'name' => 'Wood'],
                                        'stone' => ['icon' => '🪨', 'name' => 'Stone'],
                                        'gold' => ['icon' => '🪙', 'name' => 'Gold'],
                                    ];
                                @endphp

                                @foreach($resNames as $key => $meta)
                                    @php
                                        $row = session("results.{$key}", ['input' => 0, 'gross' => 0, 'tax' => 0, 'net' => 0, 'subtotal_price' => 0]);
                                    @endphp
                                    <tr id="tr_{{ $key }}">
                                        <td class="fw-bold">{{ $meta['icon'] }} {{ $meta['name'] }}</td>
                                        <td class="text-end" id="td_{{ $key }}_input">{{ $row['input'] > 0 ? $row['input'].'M' : '-' }}</td>
                                        <td class="text-end text-danger" id="td_{{ $key }}_tax">{{ $row['tax'] > 0 ? $row['tax'].'M' : '-' }}</td>
                                        <td class="text-end fw-bold text-success" id="td_{{ $key }}_net">{{ $row['net'] > 0 ? $row['net'].'M' : '-' }}</td>
                                        <td class="text-end" id="td_{{ $key }}_price">{{ $row['subtotal_price'] > 0 ? 'Rp '.number_format($row['subtotal_price'], 0, ',', '.') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td>TOTAL GABUNGAN</td>
                                    <td class="text-end" id="tf_total_input">{{ $hasSessionResult ? session('total_gross').'M' : '-' }}</td>
                                    <td class="text-end text-danger" id="tf_total_tax">{{ $hasSessionResult ? session('total_tax').'M' : '-' }}</td>
                                    <td class="text-end text-success" id="tf_total_net">{{ $hasSessionResult ? session('total_net').'M' : '-' }}</td>
                                    <td class="text-end text-primary" id="tf_total_price">{{ $hasSessionResult && session('total_price') > 0 ? 'Rp '.number_format(session('total_price'), 0, ',', '.') : '-' }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Tombol Cepat: Salin Format WhatsApp -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-success w-100 fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center gap-2" onclick="copyWhatsAppFormat()">
                            <i class="bi bi-whatsapp fs-5"></i>
                            <span>Salin Format Chat WhatsApp (Siap Kirim)</span>
                        </button>
                    </div>

                    <!-- Form Opsional: Catat ke Buku Kas -->
                    @if($hasSessionResult && session('total_net') > 0)
                        <div class="card bg-light border p-3">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-journal-plus me-1 text-primary"></i> Opsional: Catat Transaksi ke Buku Kas</h6>
                            <form action="/kalkulator/simpan" method="POST" class="row g-2 align-items-center">
                                @csrf
                                <input type="hidden" name="rss_type" value="Paket FWSG">
                                <input type="hidden" name="amount_requested" value="{{ session('total_net') }}">
                                <input type="hidden" name="amount_to_send" value="{{ session('total_gross') }}">
                                <input type="hidden" name="tax_percentage" value="{{ session('tax') }}">
                                <input type="hidden" name="total_price" value="{{ session('total_price') }}">

                                <div class="col-md-5">
                                    <input type="text" class="form-control form-control-sm bg-white" name="buyer_name" required placeholder="IGN Pembeli / Keterangan">
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control form-control-sm bg-white" name="kd_number" required placeholder="KD" value="{{ session('kd_number') }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-sm btn-warning w-100 fw-bold text-dark">
                                        <i class="bi bi-sd-card-fill me-1"></i> Simpan ke Kas
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Update tampilan Radio Card Mode
    function updateModeUI() {
        const isDeduct = document.getElementById('mode_deduct').checked;
        document.querySelectorAll('.mode-card').forEach(el => el.classList.remove('active'));
        if (isDeduct) {
            document.querySelector("label[for='mode_deduct']").classList.add('active');
            document.getElementById('label_rss_input').innerText = 'TOTAL RSS DIKIRIM DARI FARM (M)';
            document.getElementById('lbl_stat_input').innerText = 'TOTAL DIKIRIM';
            document.getElementById('lbl_stat_net').innerText = 'DITERIMA BERSIH';
            document.getElementById('th_input_col').innerText = 'TOTAL KIRIM';
            document.getElementById('th_net_col').innerText = 'DITERIMA BERSIH';
        } else {
            document.querySelector("label[for='mode_target']").classList.add('active');
            document.getElementById('label_rss_input').innerText = 'TARGET RSS DITERIMA BERSIH (M)';
            document.getElementById('lbl_stat_input').innerText = 'WAJIB DIKIRIM';
            document.getElementById('lbl_stat_net').innerText = 'TARGET BERSIH';
            document.getElementById('th_input_col').innerText = 'WAJIB KIRIM';
            document.getElementById('th_net_col').innerText = 'TARGET BERSIH';
        }
        calculateRealtime();
    }

    // Toggle info aksi KD
    function toggleKdActionUI() {
        const kdVal = document.getElementById('input_kd').value.trim();
        const kdNote = document.getElementById('js_kd_preview_note');
        if (kdNote) {
            if (kdVal) {
                kdNote.innerHTML = `<i class="bi bi-check-circle text-success me-2"></i> Nomor KD <b>#${kdVal}</b> terisi. Saat diklik 'Hitung', hasil bersih otomatis <b>MASUK ke Dashboard Stok KD #${kdVal}</b>.`;
                kdNote.className = "alert alert-success-subtle border border-success-subtle d-flex align-items-center mb-4 text-dark small";
            } else {
                kdNote.innerHTML = `<i class="bi bi-info-circle text-primary me-2"></i> Mode simulasi: KD kosong, hasil <b>tidak akan masuk ke dashboard manapun</b>.`;
                kdNote.className = "alert alert-light border d-flex align-items-center mb-4 text-muted small";
            }
        }
    }

    // Kalkulasi Realtime di Browser saat user mengetik
    function calculateRealtime() {
        const isDeduct = document.getElementById('mode_deduct').checked;
        const taxVal = parseFloat(document.getElementById('input_tax').value) || 0;
        const taxRate = taxVal / 100;
        const pricePerM = parseFloat(document.getElementById('input_price').value) || 0;

        document.querySelectorAll('.tax-rate-display').forEach(el => el.innerText = taxVal);

        const resources = ['food', 'wood', 'stone', 'gold'];
        let totalInput = 0;
        let totalGross = 0;
        let totalTax = 0;
        let totalNet = 0;

        resources.forEach(res => {
            const inputEl = document.getElementById('input_' + res);
            const val = parseFloat(inputEl.value) || 0;
            totalInput += val;

            let gross = 0;
            let tax = 0;
            let net = 0;

            if (val > 0) {
                if (isDeduct) {
                    gross = val;
                    tax = gross * taxRate;
                    net = gross - tax;
                } else {
                    net = val;
                    gross = taxRate >= 1 ? net : (net / (1 - taxRate));
                    tax = gross - net;
                }
            }

            totalGross += gross;
            totalTax += tax;
            totalNet += net;

            const subPrice = net * pricePerM;

            // Update row di tabel
            const tdInput = document.getElementById('td_' + res + '_input');
            const tdTax = document.getElementById('td_' + res + '_tax');
            const tdNet = document.getElementById('td_' + res + '_net');
            const tdPrice = document.getElementById('td_' + res + '_price');

            if (tdInput) tdInput.innerText = val > 0 ? (isDeduct ? gross.toFixed(2) : gross.toFixed(2)) + 'M' : '-';
            if (tdTax) tdTax.innerText = tax > 0 ? tax.toFixed(2) + 'M' : '-';
            if (tdNet) tdNet.innerText = net > 0 ? net.toFixed(2) + 'M' : '-';
            if (tdPrice) tdPrice.innerText = subPrice > 0 ? 'Rp ' + Math.round(subPrice).toLocaleString('id-ID') : '-';
        });

        const totalPrice = Math.round(totalNet * pricePerM);

        // Update 4 Ringkasan Angka
        const resInput = document.getElementById('res_total_input');
        const resTax = document.getElementById('res_total_tax');
        const resNet = document.getElementById('res_total_net');
        const resPrice = document.getElementById('res_total_price');

        if (resInput) resInput.innerHTML = (isDeduct ? totalGross.toFixed(2) : totalGross.toFixed(2)) + '<span class="fs-6 text-muted fw-normal">M</span>';
        if (resTax) resTax.innerHTML = totalTax.toFixed(2) + '<span class="fs-6 text-muted fw-normal">M</span>';
        if (resNet) resNet.innerHTML = totalNet.toFixed(2) + '<span class="fs-6 text-muted fw-normal">M</span>';
        if (resPrice) resPrice.innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');

        // Update Tabel Footer
        const tfInput = document.getElementById('tf_total_input');
        const tfTax = document.getElementById('tf_total_tax');
        const tfNet = document.getElementById('tf_total_net');
        const tfPrice = document.getElementById('tf_total_price');

        if (tfInput) tfInput.innerText = totalGross > 0 ? totalGross.toFixed(2) + 'M' : '-';
        if (tfTax) tfTax.innerText = totalTax > 0 ? totalTax.toFixed(2) + 'M' : '-';
        if (tfNet) tfNet.innerText = totalNet > 0 ? totalNet.toFixed(2) + 'M' : '-';
        if (tfPrice) tfPrice.innerText = totalPrice > 0 ? 'Rp ' + totalPrice.toLocaleString('id-ID') : '-';
    }

    // Jalankan sekali saat load
    document.addEventListener('DOMContentLoaded', function() {
        updateModeUI();
        toggleKdActionUI();
    });
</script>
<script src="/js/satset.js"></script>
</body>
</html>