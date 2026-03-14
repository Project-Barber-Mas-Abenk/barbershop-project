<header class="header">

    <div class="header-left">
        <h1>Dashboard</h1>
        <p id="currentTime">-</p>
    </div>

    <div class="header-right">
        <span class="status-online">● Online</span>

        <!-- Nama dan role user dari SESSION -->
        <span style="margin-right:15px;">
            <?php echo htmlspecialchars($nama); ?> (<?php echo $role; ?>)
        </span>
    </div>

</header>

<!-- ACTION BAR -->
<div class="action-bar">
    <button class="action-btn">
        Admin Konfirmasi
    </button>
    <button class="action-btn">
        Input Ke Dashboard
    </button>
    <button class="action-btn">
        Booking Hari Ini
    </button>
    <button class="action-btn">
        Redirect Ke WA Admin
    </button>
    <button class="action-btn action-btn--primary">
        + Tambah Booking
    </button>
</div>

<!-- STATS SECTION -->
<section class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon"></div>
        <div class="stat-number" id="statBookingHariIni">0</div>
        <div class="stat-label">Booking Hari Ini</div>
        <div class="stat-sub">Dicatat oleh Admin</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon"></div>
        <div class="stat-number" id="statBookingBesok">0</div>
        <div class="stat-label">Booking Besok</div>
        <div class="stat-sub">Sudah Dikonfirmasi oleh Admin</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon"></div>
        <div class="stat-number" id="statPelangganHariIni">0</div>
        <div class="stat-label">Pelanggan Hari Ini</div>
        <div class="stat-sub">Booking Tercatat</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon"></div>
        <div class="stat-number" id="statPelangganMinggu">0</div>
        <div class="stat-label">Pelanggan Minggu Ini</div>
        <div class="stat-sub" id="statMingguLalu">- vs Minggu Lalu</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon"></div>
        <div class="stat-number" id="statPelangganBulanan">0</div>
        <div class="stat-label">Pelanggan Bulan Ini</div>
        <div class="stat-sub" id="statBulanLalu">- vs Bulan Lalu</div>
    </div>

    <div class="stat-card stat-card--alert">
        <div class="stat-icon"></div>
        <div class="stat-number" id="statBelumDicatat">0</div>
        <div class="stat-label">Belum Dicatat</div>
        <div class="stat-sub stat-sub--alert">Perlu Input dari WA</div>
    </div>

    <?php if ($role === 'admin'): ?>
        <div class="stat-card">
            <div class="stat-icon"></div>
            <div class="stat-number" id="totalIncome">0</div>
            <div class="stat-label">Pendapatan Total</div>
            <div class="stat-sub">Dari Semua Transaksi</div>
        </div>
    <?php endif; ?>

</section>

<!-- MAIN DASHBOARD -->
<div class="dashboard-main-grid">
    <!-- LEFT: Jadwal Hari Ini -->
    <div class="panel panel--jadwal">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Jadwal Hari Ini</h3>
                <p class="panel-subtitle" id="jadwalTanggal">-</p>
                <p class="panel-note">Dicatat oleh Admin setelah Konfirmasi dari WA</p>
            </div>
            <div class="panel-header-right">
                <div class="antrian-badge" id="jadwalAntrian">0 Antrian</div>
                <button class="btn-catat">+ Catat</button>
            </div>
        </div>

        <div class="booking-list" id="bokingList">
            <tr>
                <td colspan="4">Memuat data...</td>
            </tr>
        </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="right-column">

        <!-- JADWAL BESOK -->
        <div class="panel panel--besok">
            <div class="panel-header">
                <div>
                    <h3 class="panel-tittle">Jadwal Besok</h3>
                    <p class="panel-subtitle" id="besokTanggal">-</p>
                </div>
                <div class="antrian-badge" id="besokAntrian">0 Antrian</div>
            </div>

            <div class="booking-list" id="besokList">
                <div class="empty-state">Tidak ada jadwal</div>
            </div>
        </div>

        <!-- BARBER -->
        <div class="panel panel--barber">
            <div class="panel-header">
                <h3 class="panel-title">Barber Hari Ini</h3>
                <span class="maks-label">Maks 4 booking</span>
            </div>

            <div id="barberLoadList">
                <div class="empty-state">Memuat data...</div>
            </div>
        </div>

    </div>
</div>

<script>
    const userRole = '<?php echo $role; ?>';

    // ========================================================
    // INIT
    // ========================================================
    initDashboard();

    function initDashboard() {
        setDateLabels();
        loadDashboardData();
    }

    // SET DATE LABELS
    function setDateLabels() {
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const now = new Date();

        const dayName = days[now.getDay()];
        const dayNum = now.getDate();
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();

        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);

        document.getElementById('currentDate').textContent =
            `${dayName}, ${dayNum} ${monthName} ${year}`;

        document.getElementById('jadwalTanggal').textContent =
            `${dayName}, ${dayNum} ${monthName} ${year}`;

        document.getElementById('besokTanggal').textContent =
            `${days[tomorrow.getDay()]}, ${tomorrow.getDate()} ${months[tomorrow.getMonth()]} ${tomorrow.getFullYear()}` +
            ' · Sudah dikonfirmasi via WA';
    }

    // LOAD DASHBOARD DATA
    async function loadDashboardData() {
        try {
            const response = await getBookings();

            if (response.status === 'success') {
                const bookings = response.data || [];
                updateStats(bookings);
                renderJadwalHariIni(bookings);
                renderJadwalBesok(bookings);
                renderBebanBarber(bookings);
            } else {
                document.getElementById('bookingList').innerHTML =
                    `<div class="empty-state">Gagal memuat data</div>`;
            }
        } catch (err) {
            document.getElementById('bookingList').innerHTML =
                `<div class="empty-state">Error: ${err.message}</div>`;
        }
    }

    // UPDATE STATISTICS
    function updateStats(bookings) {
        const today = new Date().toISOString().slice(0, 10);
        const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10);

        const todayBookings = bookings.filter(b => b.tanggal === today);
        const tomorrowBookings = bookings.filter(b => b.tanggal === tomorrow);

        document.getElementById('statBookingHariIni').textContent = todayBookings.length;
        document.getElementById('statBookingBesok').textContent = tomorrowBookings.length;
        document.getElementById('statPelangganHariIni').textContent = todayBookings.length;

        // Weekly / monthly (semua booking sebagai placeholder)
        document.getElementById('statPelangganMinggu').textContent = bookings.length;
        document.getElementById('statPelangganBulan').textContent = bookings.length;
        document.getElementById('statBelumDicatat').textContent =
            bookings.filter(b => !b.nama_pelanggan).length;

        document.getElementById('jadwalAntrian').textContent = todayBookings.length + ' Antrian';
        document.getElementById('besokAntrian').textContent = tomorrowBookings.length + ' Antrian';

        if (userRole === 'admin') {
            const income = bookings
                .filter(b => b.status_bayar === 'lunas')
                .reduce((sum, b) => sum + parseFloat(b.harga || 0), 0);
            document.getElementById('totalIncome').textContent = formatRupiah(income);
        }
    }

    // RENDER JADWAL HARI INI
    function renderJadwalHariIni(bookings) {
        const today = new Date().toISOString().slice(0, 10);
        const filtered = bookings.filter(b => b.tanggal === today);
        const container = document.getElementById('bookingList');

        if (filtered.length === 0) {
            container.innerHTML = `<div class="empty-state">Tidak ada jadwal hari ini</div>`;
            return;
        }

        container.innerHTML = filtered.map(b => buildBookingItem(b)).join('');
    }

    // RENDER JADWAL BESOK
    function renderJadwalBesok(bookings) {
        const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10);
        const filtered = bookings.filter(b => b.tanggal === tomorrow);
        const container = document.getElementById('besokList');

        if (filtered.length === 0) {
            container.innerHTML = `<div class="empty-state">Tidak ada jadwal besok</div>`;
            return;
        }

        container.innerHTML = filtered.map(b => buildBookingItem(b)).join('');
    }

    // BUILD BOOKING ITEM HTML
    function buildBookingItem(b) {
        let statusClass = 'status--pending';
        let statusText = b.status_booking || 'menunggu';

        if (b.status_booking === 'dikonfirmasi' || b.status_booking === 'selesai') {
            statusClass = 'status--selesai';
        } else if (b.status_booking === 'proses') {
            statusClass = 'status--proses';
        } else if (b.status_booking === 'dibatalkan') {
            statusClass = 'status--batal';
        }

        const initials = (b.nama_pelanggan || '?').substring(0, 2).toUpperCase();

        return `
        <div class="booking-item">
            <div class="booking-time">${b.jam || '00:00'}</div>
            <div class="booking-avatar">${initials}</div>
            <div class="booking-info">
                <div class="booking-name">${b.nama_pelanggan || '-'}</div>
                <div class="booking-detail">${b.nama_layanan || '-'}</div>
                <div class="booking-barber">Barber : ${b.nama_barber || '-'}</div>
            </div>
            <div class="booking-status ${statusClass}">
                ${statusClass === 'status--selesai' ? '✓ ' : statusClass === 'status--proses' ? '● ' : '⏳ '}${statusText}
            </div>
        </div>`;
    }

    // RENDER BEBAN BARBER
    function renderBebanBarber(bookings) {
        const today = new Date().toISOString().slice(0, 10);
        const todayB = bookings.filter(b => b.tanggal === today);
        const container = document.getElementById('barberLoadList');

        // Group by barber
        const barberMap = {};
        todayB.forEach(b => {
            const name = b.nama_barber || 'Unknown';
            barberMap[name] = (barberMap[name] || 0) + 1;
        });

        const entries = Object.entries(barberMap);

        if (entries.length === 0) {
            container.innerHTML = `<div class="empty-state">Tidak ada data barber</div>`;
            return;
        }

        const maxLoad = 4;
        container.innerHTML = entries.map(([name, count]) => {
            const pct = Math.min((count / maxLoad) * 100, 100);
            return `
            <div class="barber-load-item">
                <div class="barber-load-header">
                    <span class="barber-load-name">${name}</span>
                    <span class="barber-load-count">${count}/${maxLoad}</span>
                </div>
                <div class="barber-load-bar-track">
                    <div class="barber-load-bar-fill" style="width:${pct}%"></div>
                </div>
            </div>`;
        }).join('');
    }
</script>