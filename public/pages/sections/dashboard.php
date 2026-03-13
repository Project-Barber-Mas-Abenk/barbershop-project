<!-- =========================================================
HEADER DASHBOARD
Data yang ditampilkan:
- Nama user
- Role user
- Current time
========================================================= -->

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

<!-- =========================================================
SUMMARY CARD
Data ini HARUS di-fetch dari API booking

API yang dibutuhkan:
GET /bookings

Data yang dipakai:
- total booking
- booking status menunggu
- booking status dikonfirmasi
- booking status dibatalkan
- booking selesai

Jika admin:
- total pendapatan (status_bayar = lunas)
========================================================= -->

<section class="summary">

    <div class="card">
        <p>Total Booking</p>
        <h2 id="totalBooking">0</h2>
        <span class="card-info">Semua booking</span>
    </div>

    <div class="card">
        <p>Booking Pending</p>
        <h2 id="pendingBooking">0</h2>
        <span class="card-info">Menunggu Konfirmasi</span>
    </div>

    <div class="card">
        <p>Confirmed</p>
        <h2 id="confirmedBooking">0</h2>
        <span class="card-info">Booking dikonfirmasi</span>
    </div>

    <div class="card">
        <p>Dibatalkan</p>
        <h2 id="cancelledBooking">0</h2>
        <span class="card-info">Booking dibatalkan</span>
    </div>

    <!-- Hanya muncul jika role = admin -->
    <?php if ($role === 'admin'): ?>

        <div class="card">
            <p>Total Pendapatan</p>
            <h2 id="totalIncome">Rp 0</h2>
            <span class="card-info">Dari transaksi lunas</span>
        </div>

    <?php endif; ?>

</section>



<!-- =========================================================
BOOKING TERBARU PANEL

Data yang harus di-fetch:
GET /bookings

Field yang digunakan:
- nama_pelanggan
- nama_layanan
- tanggal
- jam
- status_booking

Limit:
ambil 5 - 10 booking terbaru saja
========================================================= -->

<section class="dashboard-grid">

    <!-- PANEL BOOKING -->
    <div class="panel-notif">

        <h3>Booking Terbaru</h3>

        <table class="booking-table">

            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Layanan</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody id="bookingList">

                <tr>
                    <td colspan="4">Memuat data...</td>
                </tr>

            </tbody>

        </table>

    </div>



    <!-- RIGHT PANEL -->
    <div class="grid-right">


        <!-- PANEL BARBER -->
        <div class="panel">

            <h3>Barber Tersedia</h3>

            <ul class="notif-list" id="barberList">

                <li>
                    <div>
                        <strong>Abenk</strong>
                        <p>3 booking hari ini</p>
                    </div>

                    <span class="status confirmed">Available</span>
                </li>

                <li>
                    <div>
                        <strong>Iki</strong>
                        <p>5 booking hari ini</p>
                    </div>

                    <span class="status pending">Busy</span>
                </li>

                <li>
                    <div>
                        <strong>Rudi</strong>
                        <p>Tidak ada jadwal</p>
                    </div>

                    <span class="status cancel">Off</span>
                </li>

            </ul>

        </div>


        <!-- PANEL SERVICES -->
        <div class="panel">

            <h3>Layanan Populer</h3>

            <div class="services-grid">

                <div class="service-card">
                    <h4>Haircut</h4>
                    <p>150x booking</p>
                </div>

                <div class="service-card">
                    <h4>Hairwash</h4>
                    <p>120x booking</p>
                </div>

                <div class="service-card">
                    <h4>Beard Trim</h4>
                    <p>90x booking</p>
                </div>

                <div class="service-card">
                    <h4>Coloring</h4>
                    <p>60x booking</p>
                </div>

                <div class="service-card">
                    <h4>Creambath</h4>
                    <p>40x booking</p>
                </div>

            </div>

        </div>

    </div>

</section>



<script>
    const userRole = '<?php echo $role; ?>';

    /* =========================================================
    INIT DASHBOARD
    Load semua data dashboard dari backend
    ========================================================= */

    loadDashboardData();

    /* =========================================================
    FETCH BOOKING DATA
    Digunakan untuk:

    1. Summary Card
    2. Booking terbaru table
    ========================================================= */

    async function loadDashboardData() {

        try {

            const response = await getBookings();

            if (response.status === 'success') {

                const bookings = response.data || [];

                updateStats(bookings);
                renderBookingList(bookings);

            } else {

                document.getElementById('bookingList').innerHTML =
                    `<tr><td colspan="4">Gagal memuat data</td></tr>`;

            }

        } catch (err) {

            document.getElementById('bookingList').innerHTML =
                `<tr><td colspan="4">Error: ${err.message}</td></tr>`;

        }

    }

    /* =========================================================
    UPDATE SUMMARY CARD

    Data dihitung dari array bookings
    ========================================================= */

    function updateStats(bookings) {

        const total = bookings.length;

        const pending =
            bookings.filter(b => b.status_booking === 'menunggu').length;

        const confirmed =
            bookings.filter(b => b.status_booking === 'dikonfirmasi').length;

        const cancelled =
            bookings.filter(b => b.status_booking === 'dibatalkan').length;

        const selesai =
            bookings.filter(b => b.status_booking === 'selesai').length;


        document.getElementById('totalBooking').textContent = total;
        document.getElementById('pendingBooking').textContent = pending;
        document.getElementById('confirmedBooking').textContent = confirmed + selesai;
        document.getElementById('cancelledBooking').textContent = cancelled;

        /* HITUNG PENDAPATAN (admin only) */

        if (userRole === 'admin') {

            const income = bookings
                .filter(b => b.status_bayar === 'lunas')
                .reduce((sum, b) => sum + parseFloat(b.harga || 0), 0);

            document.getElementById('totalIncome').textContent =
                formatRupiah(income);

        }

    }

    /* =========================================================
    RENDER BOOKING TERBARU

    Menampilkan max 10 booking terbaru
    ========================================================= */

    function renderBookingList(bookings) {

        const container = document.getElementById('bookingList');

        if (bookings.length === 0) {

            container.innerHTML =
                `<tr><td colspan="4">Tidak ada booking</td></tr>`;

            return;

        }

        let html = "";

        bookings.slice(0, 10).forEach(b => {

            let statusClass = "pending";

            if (b.status_booking === "dikonfirmasi" || b.status_booking === "selesai") {
                statusClass = "confirmed";
            }

            if (b.status_booking === "dibatalkan") {
                statusClass = "cancel";
            }

            html += `
<tr>
<td>${b.nama_pelanggan}</td>
<td>${b.nama_layanan}</td>
<td>${formatTanggal(b.tanggal)} ${b.jam}</td>
<td class="${statusClass}">
${b.status_booking}
</td>
</tr>
`;

        });

        container.innerHTML = html;

    }
</script>