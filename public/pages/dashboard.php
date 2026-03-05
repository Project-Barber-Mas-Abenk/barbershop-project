<?php
$title = "Dashboard - Shift Studio";

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$isAdmin = $_SESSION['user_role'] === 'admin';
$userName = $_SESSION['user_nama'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../components/ui/sidebar.php'; ?>

        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <h1>Dashboard</h1>
                    <p id="currentDate"></p>
                </div>
                <div class="header-right">
                    <span class="status-online">● Online</span>
                    <span><?php echo htmlspecialchars($userName); ?> (<?php echo $isAdmin ? 'Admin' : 'User'; ?>)</span>
                </div>
            </header>

            <section class="summary">
                <div class="card">
                    <p>Total Booking</p>
                    <h2 id="totalBooking">0</h2>
                </div>
                <div class="card">
                    <p>Booking Pending</p>
                    <h2 id="pendingBooking">0</h2>
                </div>
                <div class="card">
                    <p>Confirmed</p>
                    <h2 id="confirmedBooking">0</h2>
                </div>
                <?php if ($isAdmin): ?>
                <div class="card">
                    <p>Dibatalkan</p>
                    <h2 id="cancelledBooking">0</h2>
                </div>
                <div class="card">
                    <p>Total Pendapatan</p>
                    <h2 id="totalRevenue">Rp 0</h2>
                </div>
                <?php endif; ?>
            </section>

            <section class="dashboard-grid">
                <div class="panel-notif">
                    <h3>Booking Terbaru</h3>
                    <ul class="notif-list" id="recentBookings">
                        <li>Memuat data...</li>
                    </ul>
                </div>

                <div class="panel">
                    <h3>Aksi Langsung</h3>
                    <div class="quick-actions">
                        <button onclick="location.href='booking.php'"><?php echo $isAdmin ? 'Daftar Booking' : 'Booking Baru'; ?></button>
                        <?php if ($isAdmin): ?>
                        <button onclick="location.href='payment.php'">Manajemen Payment</button>
                        <button onclick="location.href='users.php'">Manajemen User</button>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="../assets/js/api.js"></script>
    <script src="../assets/js/dashboard.js"></script>
    <script>
        document.getElementById('currentDate').textContent = new Date().toLocaleDateString('id-ID', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });

        loadDashboardData(<?php echo $isAdmin ? 'true' : 'false'; ?>);
    </script>
</body>
</html>