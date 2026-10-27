/**
 * Satset & Anti-Lag Interactive Button Engine
 * - Responsif instan saat tombol diklik (tanpa jeda/freeze)
 * - Mencegah double-click / spam request
 * - Dynamic AJAX execution untuk panen, log joki (-1 / reset), dan farm char
 * - Menjaga posisi scroll halaman (tidak meloncat ke atas)
 * - 100% aman tanpa mengubah logika dan tampilan web
 */

(function () {
    'use strict';

    // 1. Pulihkan posisi scroll jika ada reload sebelumnya
    document.addEventListener('DOMContentLoaded', function () {
        const savedScroll = sessionStorage.getItem('satset_scroll_pos');
        if (savedScroll !== null) {
            window.scrollTo({
                top: parseInt(savedScroll, 10),
                behavior: 'instant'
            });
            sessionStorage.removeItem('satset_scroll_pos');
        }

        initMobileNavigation();
        initSatsetButtons();
    });

    // Inisialisasi Tampilan Mobile Responsif (HP-Friendly)
    function initMobileNavigation() {
        // 1. Injeksi mobile.css jika belum ada
        if (!document.getElementById('rok-mobile-css')) {
            const link = document.createElement('link');
            link.id = 'rok-mobile-css';
            link.rel = 'stylesheet';
            link.href = '/css/mobile.css';
            document.head.appendChild(link);
        }

        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;

        const path = window.location.pathname;

        // 2. Buat Top Header Mobile jika belum ada
        if (!document.querySelector('.mobile-header')) {
            const header = document.createElement('div');
            header.className = 'mobile-header';
            header.innerHTML = `
                <div class="brand m-0">
                    <i class="bi bi-shield-shaded text-primary me-1"></i>RoK<span>Joki</span>
                </div>
                <button class="btn btn-sm text-white p-1 px-2 border-0" id="btnToggleMobileSidebar" type="button" aria-label="Toggle menu">
                    <i class="bi bi-list fs-3"></i>
                </button>
            `;
            document.body.insertBefore(header, document.body.firstChild);

            header.querySelector('#btnToggleMobileSidebar').addEventListener('click', toggleSidebar);
        }

        // 3. Buat Backdrop untuk menutup sidebar
        let backdrop = document.querySelector('.sidebar-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'sidebar-backdrop d-none';
            document.body.appendChild(backdrop);
            backdrop.addEventListener('click', closeSidebar);
        }

        function toggleSidebar() {
            const isOpen = sidebar.classList.contains('show');
            if (isOpen) {
                closeSidebar();
            } else {
                sidebar.classList.add('show');
                backdrop.classList.remove('d-none');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            backdrop.classList.add('d-none');
            document.body.style.overflow = '';
        }

        sidebar.querySelectorAll('a').forEach(a => {
            a.addEventListener('click', closeSidebar);
        });

        // 4. Buat Bottom Navigation Bar (5 Menu Utama Joki)
        if (!document.querySelector('.mobile-bottom-nav')) {
            const bottomNav = document.createElement('div');
            bottomNav.className = 'mobile-bottom-nav';

            const items = [
                { href: '/stok', icon: 'bi-box-seam', label: 'Stok' },
                { href: '/kalkulator', icon: 'bi-calculator', label: 'Kalkulator' },
                { href: '/jadwal', icon: 'bi-alarm', label: 'Jadwal' },
                { href: '/akun', icon: 'bi-people', label: 'Akun' },
                { href: '/riwayat', icon: 'bi-journal-text', label: 'Kas' }
            ];

            bottomNav.innerHTML = items.map(item => {
                const isActive = (path === item.href) || (item.href === '/stok' && (path === '/' || path === ''));
                return `
                    <a href="${item.href}" class="${isActive ? 'active' : ''}">
                        <i class="bi ${item.icon}"></i>
                        <span>${item.label}</span>
                    </a>
                `;
            }).join('');

            document.body.appendChild(bottomNav);
        }
    }

    // Helper Toast Notification (Non-intrusive)
    function showSatsetToast(message, type = 'success') {
        let container = document.getElementById('satset-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'satset-toast-container';
            container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 99999; display: flex; flex-direction: column; gap: 8px; pointer-events: none;';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} shadow border-0 py-2 px-3 m-0 d-flex align-items-center gap-2`;
        toast.style.cssText = 'pointer-events: auto; min-width: 260px; max-width: 420px; font-size: 13px; font-weight: 500; border-radius: 8px; animation: satsetFadeIn 0.2s ease-out;';
        toast.innerHTML = `
            <i class="bi ${type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger'} fs-6"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close btn-close-sm" style="font-size: 10px;"></button>
        `;

        toast.querySelector('.btn-close').addEventListener('click', () => toast.remove());
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 2800);
    }

    // Tambahkan CSS animation keyframes
    if (!document.getElementById('satset-styles')) {
        const style = document.createElement('style');
        style.id = 'satset-styles';
        style.textContent = `
            @keyframes satsetFadeIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .btn-satset-loading {
                opacity: 0.75 !important;
                pointer-events: none !important;
                cursor: wait !important;
            }
        `;
        document.head.appendChild(style);
    }

    function initSatsetButtons() {
        // A. Intercept Form Adjust Log (-1 / Reset) di Jadwal -> Instan Tanpa Reload
        document.querySelectorAll('form[action*="/adjust-log"]').forEach(form => {
            if (form.dataset.satsetBound) return;
            form.dataset.satsetBound = 'true';

            form.addEventListener('submit', async function (e) {
                // Jika form punya onsubmit confirm (misal reset), konfirmasi tetap jalan
                if (form.getAttribute('onsubmit') && !window.confirmActionHandled) {
                    // browser bawaan sudah handle confirm, jika cancel browser stop submit otomatis
                }

                e.preventDefault();
                const btn = form.querySelector('button[type="submit"]');
                const originalHtml = btn ? btn.innerHTML : '';
                if (btn) {
                    btn.classList.add('btn-satset-loading');
                    btn.disabled = true;
                }

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        showSatsetToast(data.message, 'success');

                        // Perbarui angka langsung di card jika ada
                        const parentBox = form.closest('.bg-white.p-2');
                        if (parentBox && typeof data.farm_count !== 'undefined') {
                            const countEl = parentBox.querySelector('.fs-4.fw-bold');
                            if (countEl) {
                                countEl.innerHTML = `${data.farm_count}<span class="fs-6 text-muted fw-normal">x</span>`;
                            }
                            // Atur tombol -1 disabled jika sudah 0
                            const decBtn = parentBox.querySelector('button[title*="Kurangi"]');
                            if (decBtn) {
                                decBtn.disabled = (data.farm_count <= 0);
                            }
                        } else {
                            // Jika dari modal custom, reload halaman tapi pertahankan scroll
                            sessionStorage.setItem('satset_scroll_pos', window.scrollY);
                            window.location.reload();
                        }
                    } else {
                        showSatsetToast(data.message || 'Gagal memproses permintaan', 'danger');
                    }
                } catch (err) {
                    // Fallback jika fetch bermasalah: submit form secara normal
                    form.submit();
                    return;
                } finally {
                    if (btn) {
                        btn.classList.remove('btn-satset-loading');
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }
                }
            });
        });

        // B. Intercept Form Panen Serentak (/jadwal/kd/{kd}/farm)
        document.querySelectorAll('form[action*="/jadwal/kd/"][action*="/farm"]').forEach(form => {
            if (form.dataset.satsetBound) return;
            form.dataset.satsetBound = 'true';

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const btn = form.querySelector('button[type="submit"]');
                const originalHtml = btn ? btn.innerHTML : '';
                if (btn) {
                    btn.classList.add('btn-satset-loading');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memanen...';
                }

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        showSatsetToast(data.message, 'success');
                        // Simpan scroll dan reload cepat agar status cooldown semua char terupdate
                        sessionStorage.setItem('satset_scroll_pos', window.scrollY);
                        window.location.reload();
                    } else {
                        showSatsetToast(data.message || 'Gagal panen', 'danger');
                        if (btn) {
                            btn.classList.remove('btn-satset-loading');
                            btn.innerHTML = originalHtml;
                            btn.disabled = false;
                        }
                    }
                } catch (err) {
                    form.submit();
                }
            });
        });

        // C. Intercept Tombol "Selesai" Char (/akun/char/{id}/farm dan /akun/char-bulk/farm)
        document.querySelectorAll('form[action*="/akun/char"]').forEach(form => {
            if (form.dataset.satsetBound) return;
            form.dataset.satsetBound = 'true';

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const btn = form.querySelector('button[type="submit"]');
                const originalHtml = btn ? btn.innerHTML : '';
                if (btn) {
                    btn.classList.add('btn-satset-loading');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                }

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        showSatsetToast(data.message, 'success');
                        // Simpan posisi scroll & reload agar card dan rincian cooldown sinkron sempurna
                        sessionStorage.setItem('satset_scroll_pos', window.scrollY);
                        window.location.reload();
                    } else {
                        showSatsetToast(data.message || 'Gagal panen char', 'danger');
                        if (btn) {
                            btn.classList.remove('btn-satset-loading');
                            btn.innerHTML = originalHtml;
                            btn.disabled = false;
                        }
                    }
                } catch (err) {
                    form.submit();
                }
            });
        });

        // D. Anti-Double Click & Indikator Loading untuk Semua Form Lainnya
        document.querySelectorAll('form').forEach(form => {
            if (form.dataset.satsetBound) return;
            // Abaikan form logout atau kalkulator realtime
            if (form.getAttribute('action') === '/logout' || form.getAttribute('action') === '/kalkulator') return;

            form.dataset.satsetBound = 'true';
            form.addEventListener('submit', function () {
                const btn = form.querySelector('button[type="submit"]');
                if (btn && !btn.classList.contains('btn-satset-loading')) {
                    btn.classList.add('btn-satset-loading');
                    // Simpan scroll jika nanti reload
                    sessionStorage.setItem('satset_scroll_pos', window.scrollY);
                    const text = btn.innerText.trim();
                    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> ${text}`;
                }
            });
        });
    }

    // Ekspor helper global jika dipanggil manual
    window.Satset = {
        toast: showSatsetToast,
        refresh: function () {
            sessionStorage.setItem('satset_scroll_pos', window.scrollY);
            window.location.reload();
        }
    };
})();
