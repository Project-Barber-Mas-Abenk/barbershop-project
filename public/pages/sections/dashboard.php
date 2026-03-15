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
    <button class="action-btn">Admin Konfirmasi</button>
    <button class="action-btn">Input Ke Dashboard</button>
    <button class="action-btn">Booking Hari Ini</button>
    <button class="action-btn">Redirect Ke WA Admin</button>
    <button class="action-btn action-btn--primary">+ Tambah Booking</button>
</div>

<!-- STATS SECTION -->
<section class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-number" id="statBookingHariIni">0</div>
        <div class="stat-label">Booking Hari Ini</div>
        <div class="stat-sub">Dicatat oleh Admin</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">📆</div>
        <div class="stat-number" id="statBookingBesok">0</div>
        <div class="stat-label">Booking Besok</div>
        <div class="stat-sub">Sudah Dikonfirmasi oleh Admin</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-number" id="statPelangganHariIni">0</div>
        <div class="stat-label">Pelanggan Hari Ini</div>
        <div class="stat-sub">Booking Tercatat</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-number" id="statPelangganMinggu">0</div>
        <div class="stat-label">Pelanggan Minggu Ini</div>
        <div class="stat-sub" id="statMingguVs">— vs Minggu Lalu</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-number" id="statPelangganBulan">0</div>
        <div class="stat-label">Pelanggan Bulan Ini</div>
        <div class="stat-sub" id="statBulanVs">— vs Bulan Lalu</div>
    </div>

    <div class="stat-card stat-card--alert">
        <div class="stat-icon">💬</div>
        <div class="stat-number" id="statBelumDicatat">0</div>
        <div class="stat-label">Belum Dicatat</div>
        <div class="stat-sub stat-sub--alert">Perlu Input dari WA</div>
    </div>

    <?php if ($role === 'admin'): ?>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
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

        <div class="booking-list" id="bookingList">
            <div class="empty-state">Memuat data...</div>
        </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="right-column">

        <!-- JADWAL BESOK -->
        <div class="panel panel--besok">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Jadwal Besok</h3>
                    <p class="panel-subtitle" id="besokTanggal">-</p>
                </div>
                <div class="antrian-badge" id="besokAntrian">0 Antrian</div>
            </div>

            <div class="booking-list" id="besokList">
                <div class="empty-state">Tidak ada jadwal</div>
            </div>
        </div>

        <!-- BEBAN BARBER -->
        <div class="panel panel--barber">
            <div class="panel-header">
                <h3 class="panel-title">Beban Barber Hari Ini</h3>
                <span class="maks-label">Maks 4 booking</span>
            </div>

            <div id="barberLoadList">
                <div class="empty-state">Memuat data...</div>
            </div>
        </div>

    </div>
</div>


<script>
    if (typeof formatRupiah === 'undefined') {
        function formatRupiah(angka) {
            return 'Rp ' + Number(angka).toLocaleString('id-ID');
        }
    }

    /* ============================================================
       CONFIG
    ============================================================ */
    const userRole = '<?php echo $role; ?>';
    const DUMMY_MODE = true; // Set false saat BE sudah siap

    /* ============================================================
       DUMMY DATA — kontrak struktur data FE ↔ BE
       BE wajib mengembalikan array dengan field yang sama persis.

       Field per objek:
         id             : number   — ID unik booking
         nama_pelanggan : string   — Nama pelanggan
         nama_layanan   : string   — Nama layanan yang dipesan
         nama_barber    : string   — Nama barber yang ditugaskan
         tanggal        : string   — Format YYYY-MM-DD
         jam            : string   — Format HH:MM-HH:MM
         status_booking : string   — "menunggu" | "proses" | "selesai" | "dikonfirmasi" | "dibatalkan"
         status_bayar   : string   — "lunas" | "belum_lunas" | "pending"
         harga          : number   — Nominal harga layanan
    ============================================================ */
    function getDummyBookings() {
        const today = new Date().toISOString().slice(0, 10);
        const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10);

        return [
            /* ---- HARI INI ---- */
            {
                id: 1,
                nama_pelanggan: 'Reza Firmansyah',
                nama_layanan: 'Haircut + Cuci',
                nama_barber: 'Abeng',
                tanggal: today,
                jam: '08:00-09:00',
                status_booking: 'selesai',
                status_bayar: 'lunas',
                harga: 45000
            },
            {
                id: 2,
                nama_pelanggan: 'Dimas Pratama',
                nama_layanan: 'Haircut Booking',
                nama_barber: 'Abeng',
                tanggal: today,
                jam: '08:00-09:00',
                status_booking: 'selesai',
                status_bayar: 'lunas',
                harga: 35000
            },
            {
                id: 3,
                nama_pelanggan: 'Yoga Aditya',
                nama_layanan: 'Cukur + Beard',
                nama_barber: 'Abeng',
                tanggal: today,
                jam: '09:00-10:00',
                status_booking: 'proses',
                status_bayar: 'belum_lunas',
                harga: 55000
            },
            {
                id: 4,
                nama_pelanggan: 'Fajar Nugroho',
                nama_layanan: 'Haircut Booking',
                nama_barber: 'Rizal',
                tanggal: today,
                jam: '09:00-10:00',
                status_booking: 'menunggu',
                status_bayar: 'belum_lunas',
                harga: 35000
            },
            {
                id: 5,
                nama_pelanggan: 'Budi Santoso',
                nama_layanan: 'Haircut + Coloring',
                nama_barber: 'Rizal',
                tanggal: today,
                jam: '10:00-11:00',
                status_booking: 'menunggu',
                status_bayar: 'belum_lunas',
                harga: 120000
            },
            {
                id: 9,
                nama_pelanggan: 'Gilang Saputra',
                nama_layanan: 'Haircut + Cuci',
                nama_barber: 'Rizal',
                tanggal: today,
                jam: '11:00-12:00',
                status_booking: 'selesai',
                status_bayar: 'lunas',
                harga: 45000
            },
            {
                id: 10,
                nama_pelanggan: 'Arif Hidayat',
                nama_layanan: 'Haircut Booking',
                nama_barber: 'Abeng',
                tanggal: today,
                jam: '13:00-14:00',
                status_booking: 'dibatalkan',
                status_bayar: 'belum_lunas',
                harga: 35000
            },
            /* ---- BESOK ---- */
            {
                id: 6,
                nama_pelanggan: 'Andi Wijaya',
                nama_layanan: 'Haircut Booking',
                nama_barber: 'Abeng',
                tanggal: tomorrow,
                jam: '08:00-09:00',
                status_booking: 'dikonfirmasi',
                status_bayar: 'belum_lunas',
                harga: 35000
            },
            {
                id: 7,
                nama_pelanggan: 'Hendra Kusuma',
                nama_layanan: 'Cukur + Beard',
                nama_barber: 'Abeng',
                tanggal: tomorrow,
                jam: '09:00-10:00',
                status_booking: 'dikonfirmasi',
                status_bayar: 'belum_lunas',
                harga: 55000
            },
            {
                id: 8,
                nama_pelanggan: 'Surya Mahendra',
                nama_layanan: 'Haircut Booking',
                nama_barber: 'Rizal',
                tanggal: tomorrow,
                jam: '10:00-11:00',
                status_booking: 'menunggu',
                status_bayar: 'belum_lunas',
                harga: 35000
            },
        ];
    }

    document.addEventListener('DOMContentLoaded', function() {
        initDashboard();
    });

    function initDashboard() {
        setDateLabels();
        loadDashboardData();
    }

    /* ============================================================
       DATE LABELS
    ============================================================ */
    function setDateLabels() {
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const now = new Date();

        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);

        document.getElementById('jadwalTanggal').textContent =
            `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;

        document.getElementById('besokTanggal').textContent =
            `${days[tomorrow.getDay()]}, ${tomorrow.getDate()} ${months[tomorrow.getMonth()]} ${tomorrow.getFullYear()}` +
            ' — Sudah dikonfirmasi via WA';
    }

    /* ============================================================
       LOAD DATA
       DUMMY_MODE=true  → getDummyBookings() langsung, tanpa fetch
       DUMMY_MODE=false → getBookings() dari BE
    ============================================================ */
    function loadDashboardData() {

        /* --- DUMMY --- */
        if (DUMMY_MODE) {
            const bookings = getDummyBookings();
            updateStats(bookings);
            renderJadwalHariIni(bookings);
            renderJadwalBesok(bookings);
            renderBebanBarber(bookings);
            return;
        }

        /* --- REAL (kalau BE ready, ubah DUMMY_MODE (line 151) ke false) --- */
        getBookings()
            .then(function(response) {
                if (response.status === 'success') {
                    const bookings = response.data || [];
                    updateStats(bookings);
                    renderJadwalHariIni(bookings);
                    renderJadwalBesok(bookings);
                    renderBebanBarber(bookings);
                } else {
                    document.getElementById('bookingList').innerHTML =
                        '<div class="empty-state">Gagal memuat data</div>';
                }
            })
            .catch(function(err) {
                document.getElementById('bookingList').innerHTML =
                    '<div class="empty-state">Error: ' + err.message + '</div>';
            });
    }

    /* ============================================================
       UPDATE STATS
       TODO BE: sediakan field total_minggu_ini, total_minggu_lalu,
                total_bulan_ini, total_bulan_lalu di response
    ============================================================ */
    function updateStats(bookings) {
        const today = new Date().toISOString().slice(0, 10);
        const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10);

        const todayB = bookings.filter(function(b) {
            return b.tanggal === today;
        });
        const tomorrowB = bookings.filter(function(b) {
            return b.tanggal === tomorrow;
        });
        const belum = bookings.filter(function(b) {
            return !b.nama_pelanggan;
        }).length;

        // TODO BE: ganti bookings.length dengan data minggu/bulan yang benar
        document.getElementById('statBookingHariIni').textContent = todayB.length;
        document.getElementById('statBookingBesok').textContent = tomorrowB.length;
        document.getElementById('statPelangganHariIni').textContent = todayB.length;
        document.getElementById('statPelangganMinggu').textContent = bookings.length;
        document.getElementById('statPelangganBulan').textContent = bookings.length;
        document.getElementById('statBelumDicatat').textContent = belum;

        if (DUMMY_MODE) {
            document.getElementById('statMingguVs').textContent = '+12% vs Minggu Lalu';
            document.getElementById('statBulanVs').textContent = '+54% vs Bulan Lalu';
        }

        document.getElementById('jadwalAntrian').textContent = todayB.length + ' Antrian';
        document.getElementById('besokAntrian').textContent = tomorrowB.length + ' Antrian';

        if (userRole === 'admin') {
            const income = bookings
                .filter(function(b) {
                    return b.status_bayar === 'lunas';
                })
                .reduce(function(sum, b) {
                    return sum + parseFloat(b.harga || 0);
                }, 0);
            document.getElementById('totalIncome').textContent = formatRupiah(income);
        }
    }

    /* ============================================================
       RENDER JADWAL HARI INI
    ============================================================ */
    function renderJadwalHariIni(bookings) {
        const today = new Date().toISOString().slice(0, 10);
        const filtered = bookings.filter(function(b) {
            return b.tanggal === today;
        });
        const container = document.getElementById('bookingList');

        if (!container) return;

        if (filtered.length === 0) {
            container.innerHTML = '<div class="empty-state">Tidak ada jadwal hari ini</div>';
            return;
        }

        container.innerHTML = filtered.map(buildBookingItem).join('');
    }

    /* ============================================================
       RENDER JADWAL BESOK
    ============================================================ */
    function renderJadwalBesok(bookings) {
        const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10);
        const filtered = bookings.filter(function(b) {
            return b.tanggal === tomorrow;
        });
        const container = document.getElementById('besokList');

        if (!container) return;

        if (filtered.length === 0) {
            container.innerHTML = '<div class="empty-state">Tidak ada jadwal besok</div>';
            return;
        }

        container.innerHTML = filtered.map(buildBookingItem).join('');
    }

    /* ============================================================
       BUILD BOOKING ITEM HTML
    ============================================================ */
    function buildBookingItem(b) {
        var statusClass = 'status--pending';
        var statusText = b.status_booking || 'menunggu';

        if (b.status_booking === 'dikonfirmasi' || b.status_booking === 'selesai') {
            statusClass = 'status--selesai';
        } else if (b.status_booking === 'proses') {
            statusClass = 'status--proses';
        } else if (b.status_booking === 'dibatalkan') {
            statusClass = 'status--batal';
        }

        var icons = {
            'status--selesai': '&#10003;',
            'status--proses': '&#9679;',
            'status--batal': '&#10005;',
            'status--pending': '&#9203;'
        };
        var icon = icons[statusClass] || '&#9203;';
        var initials = (b.nama_pelanggan || '?').substring(0, 2).toUpperCase();

        return '<div class="booking-item">' +
            '<div class="booking-time">' + (b.jam || '00:00') + '</div>' +
            '<div class="booking-avatar">' + initials + '</div>' +
            '<div class="booking-info">' +
            '<div class="booking-name">' + (b.nama_pelanggan || '-') + '</div>' +
            '<div class="booking-detail">' + (b.nama_layanan || '-') + '</div>' +
            '<div class="booking-barber">Barber : ' + (b.nama_barber || '-') + '</div>' +
            '</div>' +
            '<div class="booking-status ' + statusClass + '">' + icon + ' ' + statusText + '</div>' +
            '</div>';
    }

    /* ============================================================
       RENDER BEBAN BARBER
    ============================================================ */
    function renderBebanBarber(bookings) {
        const today = new Date().toISOString().slice(0, 10);
        const todayB = bookings.filter(function(b) {
            return b.tanggal === today;
        });
        const container = document.getElementById('barberLoadList');

        if (!container) return;

        var barberMap = {};
        todayB.forEach(function(b) {
            var name = b.nama_barber || 'Unknown';
            barberMap[name] = (barberMap[name] || 0) + 1;
        });

        var entries = Object.entries(barberMap);

        if (entries.length === 0) {
            container.innerHTML = '<div class="empty-state">Tidak ada data barber</div>';
            return;
        }

        var maxLoad = 4;
        container.innerHTML = entries.map(function(entry) {
            var name = entry[0];
            var count = entry[1];
            var pct = Math.min((count / maxLoad) * 100, 100);
            return '<div class="barber-load-item">' +
                '<div class="barber-load-header">' +
                '<span class="barber-load-name">' + name + '</span>' +
                '<span class="barber-load-count">' + count + '/' + maxLoad + '</span>' +
                '</div>' +
                '<div class="barber-load-bar-track">' +
                '<div class="barber-load-bar-fill" style="width:' + pct + '%"></div>' +
                '</div>' +
                '</div>';
        }).join('');
    }
</script>