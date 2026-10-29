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
        initWhatsAppReminders();
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
        // A. Intercept Form Adjust Log (-1 / +1 / Reset) di Jadwal -> Instan Tanpa Reload
        document.querySelectorAll('form[action*="/adjust-log"]').forEach(form => {
            if (form.dataset.satsetBound) return;
            form.dataset.satsetBound = 'true';

            form.addEventListener('submit', async function (e) {
                // Konfirmasi aman via data-confirm
                const confirmMsg = form.getAttribute('data-confirm');
                if (confirmMsg && !window.confirm(confirmMsg)) {
                    e.preventDefault();
                    return;
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
                    const targetUrl = form.getAttribute('action') || (typeof form.action === 'string' ? form.action : form.attributes.action.value);
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
                        showSatsetToast(data.message, 'success');

                        // Perbarui angka langsung di card jika ada
                        const parentBox = form.closest('.bg-white.p-2');
                        if (parentBox && typeof data.farm_count !== 'undefined') {
                            const countEl = parentBox.querySelector('.fs-4.fw-bold');
                            if (countEl) {
                                countEl.innerHTML = `${data.farm_count}<span class="fs-6 text-muted fw-normal">x</span>`;
                            }
                            // Atur tombol -1 disabled jika sudah 0, dan hidupkan jika > 0
                            const decBtn = parentBox.querySelector('button.btn-decrement, button[title*="Kurangi"]');
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
                    HTMLFormElement.prototype.submit.call(form);
                    return;
                } finally {
                    if (btn) {
                        btn.classList.remove('btn-satset-loading');
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
                    const targetUrl = form.getAttribute('action') || (typeof form.action === 'string' ? form.action : form.attributes.action.value);
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
                    HTMLFormElement.prototype.submit.call(form);
                }
            });
        });

        // B2. Intercept Form Atur Jeda Macul (/jadwal/kd/{kd}/set-cooldown)
        document.querySelectorAll('form[action*="/set-cooldown"]').forEach(form => {
            if (form.dataset.satsetBound) return;
            form.dataset.satsetBound = 'true';

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const btn = form.querySelector('button[type="submit"]');
                const originalHtml = btn ? btn.innerHTML : '';
                if (btn) {
                    btn.classList.add('btn-satset-loading');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
                }

                try {
                    const formData = new FormData(form);
                    const targetUrl = form.getAttribute('action') || (typeof form.action === 'string' ? form.action : form.attributes.action.value);
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
                        showSatsetToast(data.message, 'success');
                        sessionStorage.setItem('satset_scroll_pos', window.scrollY);
                        window.location.reload();
                    } else {
                        showSatsetToast(data.message || 'Gagal menyimpan jeda', 'danger');
                        if (btn) {
                            btn.classList.remove('btn-satset-loading');
                            btn.innerHTML = originalHtml;
                            btn.disabled = false;
                        }
                    }
                } catch (err) {
                    HTMLFormElement.prototype.submit.call(form);
                }
            });
        });

        // C. Intercept Tombol "Selesai" Char (/akun/char/{id}/farm dan /akun/char-bulk/farm)
        document.querySelectorAll('form[action*="/akun/char/"][action*="/farm"], form[action*="/akun/char-bulk/farm"]').forEach(form => {
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
                    const targetUrl = form.getAttribute('action') || (typeof form.action === 'string' ? form.action : form.attributes.action.value);
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
                    HTMLFormElement.prototype.submit.call(form);
                }
            });
        });

        // D. Anti-Double Click & Indikator Loading untuk Semua Form Lainnya
        document.querySelectorAll('form').forEach(form => {
            if (form.dataset.satsetBound) return;
            // Abaikan form logout atau kalkulator realtime
            const action = form.getAttribute('action') || '';
            if (action === '/logout' || action.includes('/kalkulator')) return;

            form.dataset.satsetBound = 'true';
            form.addEventListener('submit', function () {
                // Jangan disable jika validasi form HTML5 belum lengkap
                if (form.checkValidity && !form.checkValidity()) {
                    return;
                }

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
        const timerEls = document.querySelectorAll('span[data-target-time]');
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

                    // Alert toast jika timer baru saja selesai di browser yang aktif
                    if (el.dataset.wasCountdown === 'true' && !el.dataset.hasAlerted) {
                        el.dataset.hasAlerted = 'true';
                        const kd = cardWrapper ? cardWrapper.getAttribute('data-kd') : '';
                        if (kd) {
                            showSatsetToast(`🔔 <b>KD #${kd} SUDAH SIAP PANEN!</b> <button type="button" class="btn btn-xs btn-success rounded-pill ms-2 text-white fw-bold px-2" onclick="window.Satset.sendSingleKdWA('${kd}')"><i class="bi bi-whatsapp"></i> WA</button>`, 'success');
                            if (window.SatsetTriggerHeartbeat) {
                                window.SatsetTriggerHeartbeat();
                            }
                        }
                    }
                } else {
                    el.dataset.wasCountdown = 'true';
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
        const clearSearchBtn = document.getElementById('btnClearSearchKD');
        const cards = document.querySelectorAll('.kd-card-wrapper');
        const emptyState = document.getElementById('kd-filter-empty');
        const emptyIcon = document.getElementById('empty-state-icon');
        const emptyTitle = document.getElementById('empty-state-title');
        const emptyDesc = document.getElementById('empty-state-desc');
        const resetFilterBtn = document.getElementById('btnResetFilterToAll');

        if (cards.length === 0) return;

        let activeFilter = 'all';

        // Bersihkan otomatis jika browser mengautofill email ke input cari KD
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

            // Tampilkan tombol silang jika ada teks pencarian
            if (clearSearchBtn) {
                if (currentSearch.length > 0) {
                    clearSearchBtn.classList.remove('d-none');
                } else {
                    clearSearchBtn.classList.add('d-none');
                }
            }

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
                if (currentSearch) {
                    matchSearch = kdNum.includes(currentSearch);
                }

                if (matchFilter && matchSearch) {
                    card.classList.remove('d-none');
                    visibleCount++;
                } else {
                    card.classList.add('d-none');
                }
            });

            // Handle Banner Kosong (Empty State) jika tidak ada kartu yang cocok
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

        window.Satset = window.Satset || {};
        window.Satset.setFilter = function (filter) {
            activeFilter = filter;
            updateFilterButtonStyles();
            applyFilters();
        };

        window.Satset.clearSearch = function () {
            if (searchInput) {
                searchInput.value = '';
            }
            if (clearSearchBtn) {
                clearSearchBtn.classList.add('d-none');
            }
            applyFilters();
        };

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                activeFilter = this.getAttribute('data-filter') || 'all';
                updateFilterButtonStyles();
                applyFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }

        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', window.Satset.clearSearch);
        }

        if (resetFilterBtn) {
            resetFilterBtn.addEventListener('click', function () {
                window.Satset.clearSearch();
                window.Satset.setFilter('all');
            });
        }

        // Terapkan filter awal
        updateFilterButtonStyles();
        applyFilters();
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

    // 5. Fitur Pengingat WhatsApp Direct (Satset)
    function sendDirectWhatsApp(message, targetPhone = null) {
        let phone = targetPhone !== null ? targetPhone : (localStorage.getItem('rok_wa_phone') || '');
        let cleanPhone = phone.replace(/[^0-9]/g, '');
        if (cleanPhone.startsWith('08')) {
            cleanPhone = '628' + cleanPhone.substring(2);
        }

        const waUrl = cleanPhone
            ? `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(message)}`
            : `https://api.whatsapp.com/send?text=${encodeURIComponent(message)}`;

        window.open(waUrl, '_blank');
    }

    function getDashboardPublicUrl() {
        if (window.location.origin.includes('localhost') || window.location.origin.includes('127.0.0.1')) {
            return 'https://rokjoki.vercel.app/jadwal';
        }
        return window.location.origin + '/jadwal';
    }

    function formatSingleKdWaMessage(kd, readyCount, totalChars, targetTime, cooldownHours, workerName) {
        const nowSec = Math.floor(Date.now() / 1000);
        const isReady = (!targetTime || targetTime <= nowSec || readyCount > 0);
        const currentUrl = getDashboardPublicUrl();
        const cdNum = parseFloat(cooldownHours) || 4;
        const cdStr = Number.isInteger(cdNum) ? cdNum : cdNum.toFixed(1);

        let text = '';
        if (isReady) {
            text += `🚨 *PANGGILAN PANEN FARM RoK* 🚨\n`;
            text += `━━━━━━━━━━━━━━━━━\n`;
            text += `🏰 *Kingdom:* KD #${kd}\n`;
            text += `✅ *Status:* SUDAH SIAP PANEN!\n`;
            text += `👥 *Total Char:* ${readyCount || totalChars} Char Siap\n`;
            text += `⏱️ *Siklus:* Tiap ${cdStr} Jam\n`;
            text += `@all\n`;
            text += `━━━━━━━━━━━━━━━━━\n`;
            text += `🔗 *Buka Dashboard:* ${currentUrl}\n`;
            text += `_Pesan otomatis dari Bot RoK Joki. Yuk gas macul sekarang!_ 🌾🚜`;
        } else {
            const diff = targetTime - nowSec;
            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const sisaStr = (hours > 0 ? `${hours} Jam ` : '') + `${minutes} Menit`;

            const targetDate = new Date(targetTime * 1000);
            const jamSelesai = targetDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';

            text += `🔔 *PENGINGAT JADWAL FARM RoK* 🔔\n`;
            text += `━━━━━━━━━━━━━━━━━\n`;
            text += `🏰 *Kingdom:* KD #${kd}\n`;
            text += `⏳ *Status:* Sedang Masa Jeda (Cooldown)\n`;
            text += `⏱️ *Sisa Waktu:* ${sisaStr} lagi\n`;
            text += `🎯 *Estimasi Siap:* pk ${jamSelesai}\n`;
            text += `👥 *Target Char:* ${totalChars} Char\n`;
            text += `⏱️ *Siklus:* Tiap ${cdStr} Jam\n`;
            text += `@all\n`;
            text += `━━━━━━━━━━━━━━━━━\n`;
            text += `🔗 *Buka Dashboard:* ${currentUrl}\n`;
            text += `_Pesan dari Bot RoK Joki. Set alarm jam ${jamSelesai}!_ ⏰`;
        }
        return text;
    }

    function formatAllKdWaMessage() {
        const cards = document.querySelectorAll('.kd-card-wrapper');
        const nowSec = Math.floor(Date.now() / 1000);
        const nowStr = new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB';
        const currentUrl = getDashboardPublicUrl();
        
        let readyList = [];
        let cooldownList = [];

        cards.forEach(card => {
            const kd = card.getAttribute('data-kd');
            const btnWa = card.querySelector('.btn-wa-single-kd');
            let ready = 0;
            let total = 0;
            let targetTime = 0;

            if (btnWa) {
                ready = parseInt(btnWa.getAttribute('data-ready'), 10) || 0;
                total = parseInt(btnWa.getAttribute('data-total'), 10) || 0;
                targetTime = parseInt(btnWa.getAttribute('data-target-time'), 10) || 0;
            }

            const isReady = (!targetTime || targetTime <= nowSec || ready > 0);
            if (isReady) {
                readyList.push(`• *KD #${kd}*: ${ready || total} Char Siap Panen!`);
            } else {
                const diff = targetTime - nowSec;
                const hours = Math.floor(diff / 3600);
                const minutes = Math.floor((diff % 3600) / 60);
                const sisaStr = (hours > 0 ? `${hours}j ` : '') + `${minutes}m`;
                const targetDate = new Date(targetTime * 1000);
                const jamSelesai = targetDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                cooldownList.push(`• *KD #${kd}*: Tunggu ${sisaStr} (pk ${jamSelesai})`);
            }
        });

        let text = `📋 *REKAP PENGINGAT JADWAL FARM RoK* 📋\n`;
        text += `🗓️ *Waktu:* ${nowStr}\n`;
        text += `━━━━━━━━━━━━━━━━━\n\n`;

        if (readyList.length > 0) {
            text += `🟢 *SIAP PANEN (READY - ${readyList.length} KD):*\n`;
            text += readyList.join('\n') + `\n\n`;
        } else {
            text += `🟢 *SIAP PANEN:* (Semua KD sedang masa jeda)\n\n`;
        }

        if (cooldownList.length > 0) {
            text += `⏳ *SEDANG JEDA (COOLDOWN - ${cooldownList.length} KD):*\n`;
            text += cooldownList.join('\n') + `\n\n`;
        }

        text += `@all\n`;
        text += `━━━━━━━━━━━━━━━━━\n`;
        text += `🔗 *Buka Dashboard:* ${currentUrl}\n`;
        text += `_Update langsung dari RoK Joki Admin Satset!_ 🌾🚜`;

        return text;
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
               document.querySelector('input[name="_token"]')?.value || '';
    }

    function initWhatsAppReminders() {
        // A. Handle tombol Manual WA per KD (buka chat WA langsung)
        document.querySelectorAll('.btn-wa-single-kd').forEach(btn => {
            if (btn.dataset.waBound) return;
            btn.dataset.waBound = 'true';

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const kd = btn.getAttribute('data-kd');
                const ready = parseInt(btn.getAttribute('data-ready'), 10) || 0;
                const total = parseInt(btn.getAttribute('data-total'), 10) || 0;
                const targetTime = parseInt(btn.getAttribute('data-target-time'), 10) || 0;
                const cooldownHours = btn.getAttribute('data-cooldown-hours') || '4.0';
                const worker = btn.getAttribute('data-worker') || 'Rois';

                const msg = formatSingleKdWaMessage(kd, ready, total, targetTime, cooldownHours, worker);
                sendDirectWhatsApp(msg);
                showSatsetToast(`✅ Membuka WhatsApp Pengingat KD #${kd}...`, 'success');
            });
        });

        // B. Handle tombol 1-Click Bot WA per KD (Kirim via Bot Fonnte tanpa buka WA)
        document.querySelectorAll('.btn-bot-notify-kd').forEach(btn => {
            if (btn.dataset.botBound) return;
            btn.dataset.botBound = 'true';

            btn.addEventListener('click', async function (e) {
                e.preventDefault();
                const kd = btn.getAttribute('data-kd');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 12px; height: 12px;"></span>';
                btn.disabled = true;

                try {
                    const res = await fetch(`/bot/notify-kd/${kd}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        showSatsetToast(`🤖 <b>Sukses!</b> Pengingat KD #${kd} otomatis terkirim via Bot WA!`, 'success');
                    } else {
                        showSatsetToast(data.message || 'Gagal mengirim via Bot WA', 'danger');
                        // Jika token belum diset, buka modal setting
                        if (data.message && data.message.includes('Token')) {
                            const modalEl = document.getElementById('modalSettingWA');
                            if (modalEl && window.bootstrap) new bootstrap.Modal(modalEl).show();
                        }
                    }
                } catch (err) {
                    showSatsetToast('Gagal menghubungi server bot', 'danger');
                } finally {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            });
        });

        // C. Handle tombol Rekap Manual WA di Header
        const btnShareAll = document.getElementById('btnShareAllWA');
        if (btnShareAll && !btnShareAll.dataset.waBound) {
            btnShareAll.dataset.waBound = 'true';
            btnShareAll.addEventListener('click', function () {
                const msg = formatAllKdWaMessage();
                sendDirectWhatsApp(msg);
                showSatsetToast('✅ Membuka WhatsApp Rekap Semua KD...', 'success');
            });
        }

        // D. Handle tombol Rekap Bot WA Otomatis di Header
        const btnBotShareAll = document.getElementById('btnBotShareAll');
        if (btnBotShareAll && !btnBotShareAll.dataset.botBound) {
            btnBotShareAll.dataset.botBound = 'true';
            btnBotShareAll.addEventListener('click', async function () {
                const originalHtml = btnBotShareAll.innerHTML;
                btnBotShareAll.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';
                btnBotShareAll.disabled = true;

                try {
                    const res = await fetch('/bot/notify-all', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        showSatsetToast('🤖 <b>Sukses!</b> Rekap semua KD berhasil dikirim otomatis oleh Bot WA Fonnte!', 'success');
                    } else {
                        showSatsetToast(data.message || 'Gagal mengirim rekap bot', 'danger');
                    }
                } catch (err) {
                    showSatsetToast('Gagal menghubungi server bot', 'danger');
                } finally {
                    btnBotShareAll.innerHTML = originalHtml;
                    btnBotShareAll.disabled = false;
                }
            });
        }

        // E. Handle Modal Pengaturan Bot WhatsApp (Fonnte API)
        const modalEl = document.getElementById('modalSettingWA');
        if (modalEl) {
            const inputToken = document.getElementById('inputFonnteToken');
            const inputPhone = document.getElementById('inputSettingWAPhone');
            const checkAuto = document.getElementById('checkAutoNotify');
            const badgeStatus = document.getElementById('badgeDeviceStatus');
            const btnToggleEye = document.getElementById('btnToggleTokenVisibility');
            const btnSave = document.getElementById('btnSaveBotSettings');
            const btnTestSend = document.getElementById('btnTestBotSend');
            const btnTestConn = document.getElementById('btnTestDeviceConn');
            const inputWebhook = document.getElementById('inputWebhookUrl');
            const btnCopyWebhook = document.getElementById('btnCopyWebhookUrl');

            if (btnCopyWebhook && inputWebhook) {
                btnCopyWebhook.addEventListener('click', function () {
                    const urlVal = inputWebhook.value;
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(urlVal).then(() => {
                            showSatsetToast('📋 <b>URL Webhook disalin!</b> Tempel di dashboard Fonnte menu Webhook.', 'success');
                        }).catch(() => {
                            inputWebhook.select();
                            document.execCommand('copy');
                            showSatsetToast('📋 <b>URL Webhook disalin!</b>', 'success');
                        });
                    } else {
                        inputWebhook.select();
                        document.execCommand('copy');
                        showSatsetToast('📋 <b>URL Webhook disalin!</b>', 'success');
                    }
                });
            }

            async function loadBotSettings() {
                try {
                    const res = await fetch('/bot/settings', {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (inputToken) inputToken.value = data.fonnte_token || '';
                        if (inputPhone) inputPhone.value = data.target_phone || '';
                        if (checkAuto) checkAuto.checked = !!data.is_auto_notify;
                        if (inputWebhook && data.webhook_url) inputWebhook.value = data.webhook_url;

                        if (data.target_phone) {
                            localStorage.setItem('rok_wa_phone', data.target_phone);
                        }

                        if (data.fonnte_token) {
                            checkDeviceStatusDirect(data.fonnte_token);
                        }
                    }
                } catch (e) {
                    console.log('Load bot settings error', e);
                }
            }

            async function checkDeviceStatusDirect(tokenVal) {
                if (!badgeStatus) return;
                badgeStatus.className = 'badge bg-warning text-dark';
                badgeStatus.textContent = 'Mengecek...';

                try {
                    const res = await fetch('/bot/test-connection', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ fonnte_token: tokenVal })
                    });
                    const data = await res.json();
                    if (data.success) {
                        badgeStatus.className = 'badge bg-success';
                        badgeStatus.innerHTML = `<i class="bi bi-check-circle-fill"></i> Terhubung (${data.name || data.device || 'WA'})`;
                    } else {
                        badgeStatus.className = 'badge bg-danger';
                        badgeStatus.textContent = 'Belum Terhubung';
                    }
                } catch (e) {
                    badgeStatus.className = 'badge bg-secondary';
                    badgeStatus.textContent = 'Offline';
                }
            }

            modalEl.addEventListener('show.bs.modal', loadBotSettings);

            if (btnToggleEye && inputToken) {
                btnToggleEye.addEventListener('click', function () {
                    const isPass = inputToken.type === 'password';
                    inputToken.type = isPass ? 'text' : 'password';
                    btnToggleEye.innerHTML = isPass ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
                });
            }

            if (btnSave) {
                btnSave.addEventListener('click', async function () {
                    const tokenVal = inputToken ? inputToken.value.trim() : '';
                    const phoneVal = inputPhone ? inputPhone.value.trim() : '';
                    const isAutoVal = checkAuto ? checkAuto.checked : true;

                    btnSave.disabled = true;
                    btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

                    try {
                        const res = await fetch('/bot/settings', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                fonnte_token: tokenVal,
                                target_phone: phoneVal,
                                is_auto_notify: isAutoVal
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (phoneVal) localStorage.setItem('rok_wa_phone', phoneVal);
                            showSatsetToast('✅ Pengaturan Bot WhatsApp Fonnte berhasil disimpan!', 'success');
                            if (tokenVal) checkDeviceStatusDirect(tokenVal);
                        } else {
                            showSatsetToast(data.message || 'Gagal menyimpan pengaturan bot', 'danger');
                        }
                    } catch (e) {
                        showSatsetToast('Gagal menghubungi server', 'danger');
                    } finally {
                        btnSave.disabled = false;
                        btnSave.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Pengaturan Bot';
                    }
                });
            }

            if (btnTestConn) {
                btnTestConn.addEventListener('click', function () {
                    const tokenVal = inputToken ? inputToken.value.trim() : '';
                    if (!tokenVal) {
                        showSatsetToast('Silakan isi Token Fonnte terlebih dahulu!', 'danger');
                        return;
                    }
                    checkDeviceStatusDirect(tokenVal);
                });
            }

            const btnFetchGroups = document.getElementById('btnFetchWAGroups');
            const containerGroupList = document.getElementById('containerGroupList');
            const groupListItems = document.getElementById('groupListItems');
            const btnCloseGroupList = document.getElementById('btnCloseGroupList');

            if (btnCloseGroupList && containerGroupList) {
                btnCloseGroupList.addEventListener('click', () => {
                    containerGroupList.classList.add('d-none');
                });
            }

            if (btnFetchGroups) {
                btnFetchGroups.addEventListener('click', async function () {
                    const tokenVal = inputToken ? inputToken.value.trim() : '';
                    if (!tokenVal) {
                        showSatsetToast('Silakan isi Token Fonnte terlebih dahulu!', 'danger');
                        return;
                    }

                    const origHtml = btnFetchGroups.innerHTML;
                    btnFetchGroups.disabled = true;
                    btnFetchGroups.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 10px; height: 10px;"></span> Memuat...';

                    try {
                        const res = await fetch('/bot/get-groups', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ fonnte_token: tokenVal })
                        });
                        const data = await res.json();

                        if (res.ok && data.success && data.groups && data.groups.length > 0) {
                            if (groupListItems) {
                                groupListItems.innerHTML = '';
                                data.groups.forEach(g => {
                                    const row = document.createElement('div');
                                    row.className = 'd-flex justify-content-between align-items-center p-1 bg-white border rounded';
                                    row.style.cursor = 'pointer';
                                    row.innerHTML = `
                                        <div class="overflow-hidden me-2" style="max-width: 75%;">
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 11px;">${g.name || 'Grup Tanpa Nama'}</div>
                                            <div class="text-muted" style="font-size: 9px; font-family: monospace;">${g.id}</div>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-outline-success py-0 px-2 fw-bold" style="font-size: 10px;">Pilih</button>
                                    `;
                                    row.addEventListener('click', () => {
                                        if (inputPhone) {
                                            inputPhone.value = g.id;
                                            showSatsetToast(`✅ ID Grup "${g.name}" terpilih! Klik "Simpan Pengaturan Bot".`, 'success');
                                            if (containerGroupList) containerGroupList.classList.add('d-none');
                                        }
                                    });
                                    groupListItems.appendChild(row);
                                });
                            }
                            if (containerGroupList) containerGroupList.classList.remove('d-none');
                            showSatsetToast(`✅ Ditemukan ${data.groups.length} grup WhatsApp! Silakan klik untuk memilih.`, 'success');
                        } else {
                            showSatsetToast(data.message || 'Tidak ada grup ditemukan atau belum tersinkron di Fonnte.', 'warning');
                        }
                    } catch (e) {
                        showSatsetToast('Gagal mengambil daftar grup dari server', 'danger');
                    } finally {
                        btnFetchGroups.disabled = false;
                        btnFetchGroups.innerHTML = origHtml;
                    }
                });
            }


            if (btnTestSend) {
                btnTestSend.addEventListener('click', async function () {
                    const tokenVal = inputToken ? inputToken.value.trim() : '';
                    const phoneVal = inputPhone ? inputPhone.value.trim() : '';

                    if (!phoneVal) {
                        showSatsetToast('Silakan isi Nomor WhatsApp Tujuan terlebih dahulu!', 'danger');
                        return;
                    }

                    btnTestSend.disabled = true;
                    btnTestSend.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';

                    try {
                        const res = await fetch('/bot/test-send', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                fonnte_token: tokenVal,
                                target_phone: phoneVal
                            })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            showSatsetToast('🤖 <b>Berhasil!</b> Pesan tes telah terkirim ke WhatsApp tujuan via Bot Fonnte!', 'success');
                        } else {
                            showSatsetToast(data.message || 'Gagal mengirim pesan tes via Fonnte', 'danger');
                        }
                    } catch (e) {
                        showSatsetToast('Gagal mengirim pesan tes', 'danger');
                    } finally {
                        btnTestSend.disabled = false;
                        btnTestSend.innerHTML = '<i class="bi bi-send-check me-1"></i> Tes Kirim Pesan via Bot';
                    }
                });
            }
        }

        // F. Auto-Notify Heartbeat (Otomatis kirim saat tab terbuka & KD selesai jeda tanpa klik apapun)
        // - Interval 60 detik (server guard 2 menit cegah duplikat dari multiple tab)
        // - Pause otomatis saat tab tersembunyi (hemat kuota Fonnte)
        // - Langsung cek ulang saat tab aktif kembali setelah >= 8 menit tidak aktif
        async function runAutoNotifyHeartbeat() {
            // Jangan kirim saat tab sedang tersembunyi/minimize
            if (document.visibilityState === 'hidden') return;

            try {
                const res = await fetch('/bot/check-auto-notify', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success && data.notified_count > 0) {
                    data.notified_kds.forEach(item => {
                        if (item.type === 'spam_10m_unreplied') {
                            showSatsetToast(`⚠️ <b>[Bot WA Fonnte]</b> Spam 10 Menit #${item.reminder_count} terkirim untuk KD #${item.kd} (${item.ready_count} char) karena belum dibalas!`, 'warning');
                        } else if (item.type === 'spam_5m_overdue') {
                            showSatsetToast(`🔥 <b>[Bot WA Fonnte]</b> Spam Overdue 5 Menit #${item.reminder_count} terkirim untuk KD #${item.kd} (> 30m belum diceklis)!`, 'danger');
                        } else {
                            showSatsetToast(`🤖 <b>[Bot WA Fonnte]</b> Pengingat KD #${item.kd} (${item.ready_count} char siap) otomatis terkirim ke WhatsApp!`, 'success');
                        }
                    });
                }
            } catch (e) {
                // Background check, abaikan jika offline
            }
        }

        // Listener Tombol "Saya OTW / Tandai Sudah Dibalas" pada kartu KD
        document.addEventListener('click', async function (e) {
            const btn = e.target.closest('.btn-ack-kd');
            if (!btn) return;
            const kd = btn.getAttribute('data-kd');
            if (!kd) return;

            btn.disabled = true;
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 10px; height: 10px;"></span>';

            try {
                const res = await fetch(`/bot/acknowledge-kd/${kd}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    showSatsetToast(`👍 <b>[KD #${kd}]</b> Berhasil ditandai sedang dikerjakan! Waktu 30 menit dimulai.`, 'success');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showSatsetToast(data.message || 'Gagal menandai KD', 'danger');
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
            } catch (err) {
                showSatsetToast('Gagal memproses tanda OTW', 'danger');
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        });

        // Jalankan pengecekan heartbeat:
        // - Pertama: 5 detik setelah halaman terbuka
        // - Selanjutnya: tiap 60 detik (server guard 2 menit cegah duplikat)
        setTimeout(runAutoNotifyHeartbeat, 5000);
        setInterval(runAutoNotifyHeartbeat, 60000);

        // Cek ulang segera saat tab aktif kembali setelah >= 8 menit tersembunyi
        let tabHiddenAt = null;
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') {
                tabHiddenAt = Date.now();
            } else if (document.visibilityState === 'visible') {
                const hiddenMs = tabHiddenAt ? (Date.now() - tabHiddenAt) : 0;
                if (hiddenMs >= 8 * 60 * 1000) {
                    runAutoNotifyHeartbeat();
                }
                tabHiddenAt = null;
            }
        });

        // Ekspor fungsi trigger heartbeat langsung saat timer 00:00
        window.SatsetTriggerHeartbeat = runAutoNotifyHeartbeat;
    }

    // Ekspor helper global jika dipanggil manual
    window.Satset = {
        toast: showSatsetToast,
        copyWA: window.copyWhatsAppFormat,
        sendWA: sendDirectWhatsApp,
        sendSingleKdWA: function (kd) {
            const card = document.querySelector(`.kd-card-wrapper[data-kd="${kd}"]`);
            if (!card) return;
            const btn = card.querySelector('.btn-wa-single-kd');
            if (btn) btn.click();
        },
        sendSingleKdBot: function (kd) {
            const card = document.querySelector(`.kd-card-wrapper[data-kd="${kd}"]`);
            if (!card) return;
            const btn = card.querySelector('.btn-bot-notify-kd');
            if (btn) btn.click();
        },
        sendAllWA: function () {
            const btn = document.getElementById('btnShareAllWA');
            if (btn) btn.click();
        },
        sendAllBot: function () {
            const btn = document.getElementById('btnBotShareAll');
            if (btn) btn.click();
        },
        refresh: function () {
            sessionStorage.setItem('satset_scroll_pos', window.scrollY);
            window.location.reload();
        }
    };
})();
