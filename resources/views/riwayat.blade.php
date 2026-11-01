<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Buku Kas & Riwayat Pengiriman - RoK Joki Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/mobile.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0d6efd">
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
    <script src="https://instant.page/5.2.0" type="module" integrity="sha384-jnZyxPjiCGnKDdQRvtnynQt35ypydvQSTmbVYTu29Mtxu5VEVPvyFMDZTnQMrndN"></script>
</head>
<body>

<div class="sidebar">
    <div>
        <div class="brand"><i class="bi bi-shield-shaded"></i> RoK<span>Joki</span></div>
        <a href="/stok"><i class="bi bi-box-seam"></i> Dashboard Stok</a>
        <a href="/kalkulator"><i class="bi bi-calculator"></i> Kalkulator Pajak</a>
        <a href="/riwayat" class="active"><i class="bi bi-journal-text"></i> Buku Kas</a>
        <a href="/akun"><i class="bi bi-people"></i> Database Akun</a>
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

@php
    $totalKas = $orders->where('status', 'completed')->sum('total_price');
    $pendingOrders = $orders->where('status', 'pending');
    $completedOrders = $orders->where('status', 'completed');
    $kingdoms = $kingdoms ?? collect();
@endphp

<div class="content">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold text-dark m-0"><i class="bi bi-journal-text text-primary me-2"></i>Buku Kas & Pengiriman RSS</h3>
            <p class="text-muted small mb-0">Kelola order penjualan RSS, perhitungan pajak trading post, dan pemotongan stok otomatis</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary fw-bold shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalTambahPesanan">
                <i class="bi bi-plus-circle-fill"></i> <span>Catat Order RSS</span>
            </button>
            <a href="/kalkulator" class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                <i class="bi bi-calculator me-1"></i> Kalkulator Pajak
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

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-6">
            <div class="card bg-white border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">TOTAL PENDAPATAN (LUNAS)</div>
                        <h4 class="fw-bold text-success mb-0 mt-1">Rp {{ number_format($totalKas, 0, ',', '.') }}</h4>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card bg-white border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">PESANAN PENDING (OTW)</div>
                        <h4 class="fw-bold text-warning mb-0 mt-1">{{ $pendingOrders->count() }} <span class="fs-6 text-muted fw-normal">Order</span></h4>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="card bg-white border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">PESANAN SELESAI (SUKSES)</div>
                        <h4 class="fw-bold text-primary mb-0 mt-1">{{ $completedOrders->count() }} <span class="fs-6 text-muted fw-normal">Order</span></h4>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                        <i class="bi bi-check2-all fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Pesanan -->
    <div class="card bg-white border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <span class="fw-bold text-dark"><i class="bi bi-cash-stack me-1 text-primary"></i> Semua Transaksi & Pengiriman</span>
            <span class="badge bg-secondary-subtle text-secondary border">Total {{ $orders->count() }} Catatan</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0 text-nowrap">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-4">TANGGAL</th>
                            <th>PEMBELI</th>
                            <th>TUJUAN</th>
                            <th>PESANAN</th>
                            <th>TAGIHAN (RP)</th>
                            <th>STATUS</th>
                            <th class="text-center pe-4">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse ($orders as $order)
                        <tr>
                            <td class="ps-4 text-muted small">{{ $order->created_at->format('d M Y, H:i') }}</td>
                            <td class="fw-bold">{{ $order->buyer_name }}</td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary px-2">#{{ $order->kd_number }}</span></td>
                            <td>
                                <div><span class="fw-bold text-dark">{{ $order->amount_requested }}M</span> <span class="badge bg-light text-dark border">{{ strtoupper($order->rss_type) }}</span></div>
                                <div class="small text-danger"><i class="bi bi-arrow-right-short"></i> Kirim Kotor: <b>{{ $order->amount_to_send }}M</b> (Pajak {{ $order->tax_percentage }}%)</div>
                            </td>
                            <td class="fw-bold text-success">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td>
                                @if($order->status == 'pending')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pending (OTW)</span>
                                @else
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Selesai (Lunas)</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    @if($order->status == 'pending')
                                        <form action="/riwayat/{{ $order->id }}/selesai" method="POST" onsubmit="return confirm('Tandai selesai? Stok {{ $order->rss_type }} di KD #{{ $order->kd_number }} akan otomatis dipotong {{ $order->amount_to_send }}M.');" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-check2-all me-1"></i> Lunas & Potong</button>
                                        </form>
                                    @else
                                        <span class="badge bg-light text-secondary border me-1"><i class="bi bi-check-all"></i> Terpotong</span>
                                    @endif
                                    <form action="/riwayat/{{ $order->id }}/hapus" method="POST" onsubmit="return confirm('Hapus riwayat pesanan {{ $order->buyer_name }} ini?');" class="m-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Pesanan"><i class="bi bi-trash-fill"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 d-block mb-2"></i> Belum ada riwayat pesanan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Pesanan / Order RSS -->
<div class="modal fade" id="modalTambahPesanan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="/kalkulator/simpan" method="POST" id="formTambahOrder">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-cart-plus me-2"></i>Catat Order Pengiriman RSS</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Nama Pembeli (Buyer)</label>
                            <input type="text" name="buyer_name" class="form-control" placeholder="Contoh: Sultan RoK / Budi" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Kingdom Tujuan / Asal RSS</label>
                            <select name="kd_number" id="order_kd_number" class="form-select" required>
                                <option value="">-- Pilih Kingdom --</option>
                                @foreach($kingdoms as $k)
                                    <option value="{{ $k->kd_number }}" data-food="{{ $k->food }}" data-wood="{{ $k->wood }}" data-stone="{{ $k->stone }}" data-gold="{{ $k->gold }}">
                                        KD #{{ $k->kd_number }} (F: {{ $k->food }}M | W: {{ $k->wood }}M | S: {{ $k->stone }}M | G: {{ $k->gold }}M)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Tipe Resource (RSS)</label>
                            <select name="rss_type" id="order_rss_type" class="form-select" required>
                                <option value="Food">🌾 Food (Makanan)</option>
                                <option value="Wood">🪵 Wood (Kayu)</option>
                                <option value="Stone">🪨 Stone (Batu)</option>
                                <option value="Gold">🪙 Gold (Emas)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Jumlah Bersih Diterima Buyer (M)</label>
                            <div class="input-group">
                                <input type="number" step="any" min="0.1" name="amount_requested" id="order_amount_requested" class="form-control fw-bold" placeholder="100" required>
                                <span class="input-group-text">Juta (M)</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Pajak Trading Post (%)</label>
                            <div class="input-group mb-1">
                                <input type="number" step="0.1" min="0" max="50" name="tax_percentage" id="order_tax_percentage" class="form-control fw-bold" value="8" required>
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="d-flex gap-1 flex-wrap">
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-quick-tax" data-tax="8">8% (Maks)</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-quick-tax" data-tax="10">10%</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-quick-tax" data-tax="15">15%</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-quick-tax" data-tax="20">20%</button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Harga per 1M RSS (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price_per_m" id="order_price_per_m" class="form-control fw-bold" value="2000" step="100">
                            </div>
                        </div>

                        <!-- Hidden fields to submit calculation -->
                        <input type="hidden" name="amount_to_send" id="hidden_amount_to_send" value="0">
                        <input type="hidden" name="total_price" id="hidden_total_price" value="0">

                        <!-- Kalkulasi Real-time Card -->
                        <div class="col-12 mt-3">
                            <div class="card bg-light border p-3">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-calculator-fill text-primary me-1"></i>Ringkasan Pengiriman & Tagihan</h6>
                                <div class="row g-2">
                                    <div class="col-sm-4">
                                        <div class="text-muted small">Bersih Diterima Buyer:</div>
                                        <div class="fw-bold fs-6 text-dark" id="calc_net_rss">0 M</div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="text-muted small">Wajib Kirim (Kotor):</div>
                                        <div class="fw-bold fs-6 text-danger" id="calc_gross_rss">0 M</div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="text-muted small">Total Tagihan (Rp):</div>
                                        <div class="fw-bold fs-6 text-success" id="calc_total_bill">Rp 0</div>
                                    </div>
                                </div>
                                <div class="small text-muted mt-2 border-top pt-2">
                                    <i class="bi bi-info-circle me-1"></i> Saat order ditandai <b>Lunas</b>, stok KD tujuan akan otomatis terpotong sesuai jumlah <b>Wajib Kirim (Kotor)</b>.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-save me-1"></i> Simpan Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/satset.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const netInput = document.getElementById('order_amount_requested');
        const taxInput = document.getElementById('order_tax_percentage');
        const priceInput = document.getElementById('order_price_per_m');
        const hiddenToSend = document.getElementById('hidden_amount_to_send');
        const hiddenPrice = document.getElementById('hidden_total_price');

        const calcNet = document.getElementById('calc_net_rss');
        const calcGross = document.getElementById('calc_gross_rss');
        const calcBill = document.getElementById('calc_total_bill');

        function recalculate() {
            const net = parseFloat(netInput.value) || 0;
            const tax = parseFloat(taxInput.value) || 0;
            const pricePerM = parseFloat(priceInput.value) || 0;

            let gross = 0;
            if (tax < 100 && tax >= 0) {
                gross = net / (1 - (tax / 100));
            } else {
                gross = net;
            }
            gross = Math.round(gross * 100) / 100;

            const total = Math.round(net * pricePerM);

            hiddenToSend.value = gross;
            hiddenPrice.value = total;

            calcNet.textContent = net.toLocaleString('id-ID') + ' M';
            calcGross.textContent = gross.toLocaleString('id-ID') + ' M (Pajak ' + tax + '%)';
            calcBill.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        netInput.addEventListener('input', recalculate);
        taxInput.addEventListener('input', recalculate);
        priceInput.addEventListener('input', recalculate);

        document.querySelectorAll('.btn-quick-tax').forEach(btn => {
            btn.addEventListener('click', function () {
                taxInput.value = this.getAttribute('data-tax');
                recalculate();
            });
        });

        recalculate();
    });
</script>
</body>
</html>
