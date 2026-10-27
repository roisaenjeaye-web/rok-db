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
        initLiveCountdowns();
        initKdFilters();
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

    // 2. Real-time Ticking Countdown Timer (Otomatis update detik & unlock jika siap)
    function initLiveCountdowns() {
        const timerEls = document.querySelectorAll('[data-target-time]');
        if (timerEls.length === 0) return;

        function updateTimers() {
            const now = Math.floor(Date.now() / 1000);

            timerEls.forEach(el => {
                const targetTime = parseInt(el.getAttribute('data-target-time'), 10);
                if (!targetTime || targetTime <= 0) return;

                const diff = targetTime - now;
                if (diff <= 0) {
                    el.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-unlock-fill"></i> Semua Siap!</span>';
                    el.classList.remove('text-warning');
                    el.classList.add('text-success');

                    // Update parent card if exists
                    const cardWrapper = el.closest('.kd-card-wrapper');
                    if (cardWrapper && cardWrapper.getAttribute('data-has-ready') !== 'true') {
                        cardWrapper.setAttribute('data-has-ready', 'true');
                        const badge = cardWrapper.querySelector('.kd-status-badge');
                        if (badge) {
                            badge.innerHTML = '<span class="badge bg-success"><i class="bi bi-unlock-fill"></i> Ready</span>';
                        }
                    }
                } else {
                    const hours = Math.floor(diff / 3600);
                    const minutes = Math.floor((diff % 3600) / 60);
                    const seconds = diff % 60;

                    const hStr = hours > 0 ? `${hours}j ` : '';
                    const mStr = `${minutes}m `;
                    const sStr = `${seconds < 10 ? '0' : ''}${seconds}d`;

                    el.innerHTML = `<i class="bi bi-stopwatch text-warning me-1"></i>Tunggu <b>${hStr}${mStr}${sStr}</b>`;
                }
            });
        }

        updateTimers();
        setInterval(updateTimers, 1000);
    }

    // 3. Quick Filter & Search Bar untuk KD di Jadwal & Akun
    function initKdFilters() {
        const filterBtns = document.querySelectorAll('.btn-filter-kd');
        const searchInput = document.getElementById('inputSearchKD');
        const cards = document.querySelectorAll('.kd-card-wrapper');

        if (cards.length === 0) return;

        let activeFilter = 'all';
        let searchQuery = '';

        function applyFilters() {
            cards.forEach(card => {
                const kdNum = (card.getAttribute('data-kd') || '').toLowerCase();
                const hasReady = card.getAttribute('data-has-ready') === 'true';

                let matchFilter = true;
                if (activeFilter === 'ready') {
                    matchFilter = hasReady;
                } else if (activeFilter === 'cooldown') {
                    matchFilter = !hasReady;
                }

                let matchSearch = true;
                if (searchQuery) {
                    matchSearch = kdNum.includes(searchQuery);
                }

                if (matchFilter && matchSearch) {
                    card.classList.remove('d-none');
                } else {
                    card.classList.add('d-none');
                }
            });
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                filterBtns.forEach(b => {
                    b.classList.remove('active', 'btn-dark');
                    b.classList.add('btn-outline-secondary');
                });
                this.classList.add('active', 'btn-dark');
                this.classList.remove('btn-outline-secondary');

                activeFilter = this.getAttribute('data-filter') || 'all';
                applyFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                searchQuery = this.value.trim().toLowerCase().replace('#', '');
                applyFilters();
            });
        }
    }

    // 4. Salin Format Pesanan WhatsApp Profesional (1-Click)
    window.copyWhatsAppFormat = function () {
        const kd = document.getElementById('input_kd') ? document.getElementById('input_kd').value.trim() : '';
        const food = parseFloat(document.getElementById('input_food') ? document.getElementById('input_food').value : 0) || 0;
        const wood = parseFloat(document.getElementById('input_wood') ? document.getElementById('input_wood').value : 0) || 0;
        const stone = parseFloat(document.getElementById('input_stone') ? document.getElementById('input_stone').value : 0) || 0;
        const gold = parseFloat(document.getElementById('input_gold') ? document.getElementById('input_gold').value : 0) || 0;
        const tax = parseFloat(document.getElementById('input_tax') ? document.getElementById('input_tax').value : 0) || 8;
        const pricePerM = parseFloat(document.getElementById('input_price') ? document.getElementById('input_price').value : 0) || 0;

        const totalGross = food + wood + stone + gold;
        if (totalGross <= 0) {
            showSatsetToast('Silakan isi jumlah RSS terlebih dahulu di kalkulator!', 'danger');
            return;
        }

        const isDeduct = document.getElementById('mode_deduct') ? document.getElementById('mode_deduct').checked : true;
        let totalNet = 0;
        let taxAmount = 0;

        if (isDeduct) {
            taxAmount = totalGross * (tax / 100);
            totalNet = totalGross - taxAmount;
        } else {
            const taxFactor = (1 - (tax / 100));
            const neededGross = taxFactor > 0 ? (totalGross / taxFactor) : totalGross;
            taxAmount = neededGross - totalGross;
            totalNet = totalGross;
        }

        const totalPrice = pricePerM > 0 ? Math.round(totalNet * pricePerM) : 0;

        let rssList = [];
        if (food > 0) rssList.push(`  🌽 Food: ${food.toFixed(2)}M`);
        if (wood > 0) rssList.push(`  🪵 Wood: ${wood.toFixed(2)}M`);
        if (stone > 0) rssList.push(`  🪨 Stone: ${stone.toFixed(2)}M`);
        if (gold > 0) rssList.push(`  🪙 Gold: ${gold.toFixed(2)}M`);

        let text = `👑 *DETAIL PESANAN RSS RoK* 👑\n`;
        text += `-----------------------------------\n`;
        if (kd) text += `📍 *Kingdom*: #${kd}\n`;
        text += `📦 *Rincian RSS*:\n${rssList.join('\n')}\n`;
        text += `-----------------------------------\n`;
        text += `📊 *Total Kirim*: ${isDeduct ? totalGross.toFixed(2) : (totalGross + taxAmount).toFixed(2)}M\n`;
        text += `🛡️ *Pajak TP*: ${tax}%\n`;
        text += `✅ *Bersih Diterima*: ${totalNet.toFixed(2)}M\n`;
        if (totalPrice > 0) {
            text += `💰 *Total Biaya*: Rp ${totalPrice.toLocaleString('id-ID')}\n`;
        }
        text += `-----------------------------------\n`;
        text += `🚀 *Status*: Siap Kirim / Diproses\n`;
        text += `Terima kasih sudah order! 🙏`;

        navigator.clipboard.writeText(text).then(() => {
            showSatsetToast('✅ Format WhatsApp berhasil disalin! Tinggal tempel (paste) di chat pembeli.', 'success');
        }).catch(() => {
            // Fallback manual copy
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showSatsetToast('✅ Format WhatsApp disalin ke clipboard!', 'success');
        });
    };

    // Ekspor helper global jika dipanggil manual
    window.Satset = {
        toast: showSatsetToast,
        copyWA: window.copyWhatsAppFormat,
        refresh: function () {
            sessionStorage.setItem('satset_scroll_pos', window.scrollY);
            window.location.reload();
        }
    };
})();
