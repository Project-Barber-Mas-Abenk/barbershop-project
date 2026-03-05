<?php
$title = "Booking - Shift Studio";

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$isAdmin = $_SESSION['user_role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../components/ui/sidebar.php'; ?>

        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <h1><?php echo $isAdmin ? 'Daftar Booking' : 'Booking Baru'; ?></h1>
                </div>
            </header>

            <?php if (!$isAdmin): ?>
            <section class="booking-form">
                <form id="bookingForm">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($_SESSION['user_nama'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor HP</label>
                        <input type="text" name="no_hp" required>
                    </div>
                    <div class="form-group">
                        <label>Layanan</label>
                        <select name="layanan_id" id="layananSelect" required>
                            <option value="">Pilih Layanan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" required>
                    </div>
                    <div class="form-group">
                        <label>Jam</label>
                        <input type="time" name="jam" required>
                    </div>
                    <div class="form-group">
                        <label>Metode Pembayaran</label>
                        <select name="metode_bayar" required>
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>
                    <button type="submit">Buat Booking</button>
                </form>
                <div id="bookingMsg"></div>
            </section>
            <?php endif; ?>

            <section class="booking-list">
                <h2>Daftar Booking</h2>
                <table id="bookingTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Layanan</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <?php if ($isAdmin): ?>
                            <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>
        </main>
    </div>

    <script src="../assets/js/api.js"></script>
    <script src="../assets/js/booking.js"></script>
    <script>
        const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
        initBookingPage(isAdmin);
    </script>
</body>
</html>