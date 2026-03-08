<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['user_role'] ?? 'user';
$nama = $_SESSION['user_nama'] ?? $_SESSION['admin_nama'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Shift Studio</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../components/ui/sidebar.php'; ?>

        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <h1>Dashboard</h1>
                    <p id="currentTime">-</p>
                </div>

                <div class="header-right">
                    <span class="status-online">● Online</span>
                    <span style="margin-right: 15px;"><?php echo htmlspecialchars($nama); ?> (<?php echo $role; ?>)</span>
                </div>
            </header>

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

                <?php if ($role === 'admin'): ?>
                <div class="card">
                    <p>Total Pendapatan</p>
                    <h2 id="totalIncome">Rp 0</h2>
                    <span class="card-info">Dari transaksi lunas</span>
                </div>
                <?php endif; ?>
            </section>

            <section class="dashboard-grid">
                <div class="panel-notif" style="flex: 1;">
                    <h3>Daftar Booking</h3>
                    <div id="bookingList" style="max-height: 400px; overflow-y: auto;">
                        <p>Memuat data...</p>
                    </div>
                </div>
            </section>

            </main>
    </div>

    <script src="../assets/js/auth.js"></script>
    <script>
        const userRole = '<?php echo $role; ?>';
        
        function updateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            document.getElementById('currentTime').textContent = now.toLocaleDateString('id-ID', options) + ' WIB';
        }
        
        updateTime();
        setInterval(updateTime, 60000);
        
        async function loadDashboardData() {
            try {
                const response = await getBookings();
                
                if (response.status === 'success') {
                    const bookings = response.data || [];
                    updateStats(bookings);
                    renderBookingList(bookings);
                } else {
                    document.getElementById('bookingList').innerHTML = '<p>Gagal memuat data</p>';
                }
            } catch (err) {
                document.getElementById('bookingList').innerHTML = '<p>Error: ' + err.message + '</p>';
            }
        }
        
        function updateStats(bookings) {
            const total = bookings.length;
            const pending = bookings.filter(b => b.status_booking === 'menunggu').length;
            const confirmed = bookings.filter(b => b.status_booking === 'dikonfirmasi').length;
            const cancelled = bookings.filter(b => b.status_booking === 'dibatalkan').length;
            const selesai = bookings.filter(b => b.status_booking === 'selesai').length;
            
            document.getElementById('totalBooking').textContent = total;
            document.getElementById('pendingBooking').textContent = pending;
            document.getElementById('confirmedBooking').textContent = confirmed + selesai;
            document.getElementById('cancelledBooking').textContent = cancelled;
            
            if (userRole === 'admin') {
                const income = bookings
                    .filter(b => b.status_bayar === 'lunas')
                    .reduce((sum, b) => sum + parseFloat(b.harga || 0), 0);
                document.getElementById('totalIncome').textContent = formatRupiah(income);
            }
        }
        
        function renderBookingList(bookings) {
            const container = document.getElementById('bookingList');
            
            if (bookings.length === 0) {
                container.innerHTML = '<p>Tidak ada booking</p>';
                return;
            }
            
            let html = '<table style="width: 100%; border-collapse: collapse;">';
            html += '<thead><tr style="border-bottom: 1px solid #444;">';
            html += '<th style="text-align: left; padding: 10px;">Nama</th>';
            html += '<th style="text-align: left; padding: 10px;">Layanan</th>';
            html += '<th style="text-align: left; padding: 10px;">Tanggal</th>';
            html += '<th style="text-align: left; padding: 10px;">Status</th>';
            if (userRole === 'admin') {
                html += '<th style="text-align: left; padding: 10px;">Aksi</th>';
            }
            html += '</tr></thead><tbody>';
            
            bookings.forEach(b => {
                const statusClass = b.status_booking === 'menunggu' ? 'orange' : 
                                   b.status_booking === 'dikonfirmasi' ? 'green' :
                                   b.status_booking === 'selesai' ? 'blue' : 'red';
                
                html += '<tr style="border-bottom: 1px solid #333;">';
                html += `<td style="padding: 10px;">${b.nama_pelanggan}</td>`;
                html += `<td style="padding: 10px;">${b.nama_layanan}</td>`;
                html += `<td style="padding: 10px;">${formatTanggal(b.tanggal)} ${b.jam}</td>`;
                html += `<td style="padding: 10px;"><span style="color: ${statusClass}; text-transform: capitalize;">${b.status_booking}</span></td>`;
                
                if (userRole === 'admin') {
                    html += `<td style="padding: 10px;">`;
                    if (b.status_booking === 'menunggu') {
                        html += `<button onclick="updateStatus(${b.pemesanan_id}, 'dikonfirmasi')" style="margin-right: 5px; padding: 5px 10px; background: #27ae60; color: white; border: none; border-radius: 3px; cursor: pointer;">Konfirm</button>`;
                        html += `<button onclick="updateStatus(${b.pemesanan_id}, 'dibatalkan')" style="padding: 5px 10px; background: #c0392b; color: white; border: none; border-radius: 3px; cursor: pointer;">Batal</button>`;
                    }
                    html += `</td>`;
                }
                
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
        }
        
        async function updateStatus(id, status) {
            if (!confirm(`Yakin ingin mengubah status menjadi ${status}?`)) return;
            
            try {
                const response = await updateBookingStatus(id, status);
                if (response.status === 'success') {
                    loadDashboardData();
                } else {
                    alert(response.message || 'Gagal update status');
                }
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }
        
        async function handleLogout() {
            if (!confirm('Yakin ingin logout?')) return;
            
            try {
                const response = await logout();
                if (response.status === 'success') {
                    window.location.href = 'login.php';
                }
            } catch (err) {
                window.location.href = 'login.php';
            }
        }
        
        loadDashboardData();
    </script>
</body>
</html>
