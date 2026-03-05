<?php
$title = "Dashboard - Shift Studio";
?>

<!DOCTYPE html>
<html lang="en">

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

            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1>Dashboard</h1>
                    <p>Sabtu, 28 Februari 2026 00:09 WIB</p>
                </div>

                <div class="header-right">
                    <span class="status-online">● Online</span>
                    <button class="icon-btn"></i></button>
                    <button class="icon-btn"></button>
                </div>
            </header>

            <!-- SUMMARY CARD -->
            <section class="summary">
                <div class="card">
                    <p>Total Booking</p>
                    <h2>24</h2>
                    <span class="card-info">+4 dari kemarin</span>
                </div>

                <div class="card">
                    <p>Booking Pending</p>
                    <h2>5</h2>
                    <span class="card-info">Menunggu Konfirmasi</span>
                </div>

                <div class="card">
                    <p>Confirmed & Lunas</p>
                    <h2>12</h2>
                    <span class="card-info">Booking aktif hari ini</span>
                </div>

                <div class="card">
                    <p>Dibatalkan</p>
                    <h2>3</h2>
                    <span class="card-info">Dari total booking</span>
                </div>

                <div class="card">
                    <p>Total Pendapatan</p>
                    <h2>19 Lapangan Kerja</h2>
                    <span class="card-info">Dari total transaksi lunas</span>
                </div>
            </section>

            <!-- CONTENT GRID -->
            <section class="dashboard-grid">

                <div class="panel-notif">
                    <div class="top-panel">
                        <div class="top-left">
                            <h3>Notifikasi Terbaru</h3>
                        </div>
                        <div class="top-right">
                            <button class="btn-secondary">Tandai Semua Sudah Dibaca</button>
                            <button class="btn-secondary">Lihat Semua</button>
                        </div>
                    </div>

                    <ul class="notif-list">
                        <li>
                            <div>
                                <strong>3 Booking Baru</strong>
                                <p>5 menit lalu</p>
                            </div>
                            <button class="btn-primary">Lihat</button>
                        </li>
                        <li>
                            <div>
                                <strong>Bukti Transfer Perlu Verifikasi</strong>
                                <p>2 menit lalu</p>
                            </div>
                            <button class="btn-primary">Verifikasi</button>
                        </li>
                        <li>
                            <div>
                                <strong>3 Booking Baru</strong>
                                <p>5 menit lalu</p>
                            </div>
                            <button class="btn-primary">Lihat</button>
                        </li>

                        <li>
                            <div>
                                <strong>Bukti Transfer Perlu Verifikasi</strong>
                                <p>2 menit lalu</p>
                            </div>
                            <button class="btn-primary">Verifikasi</button>
                        </li>
                    </ul>
                </div>

                <div class="grid-right">
                    <div class="panel-booking">
                        <h2>Booking 7 Hari Terakhir</h2>
                        <h4>Jumlah Booking Per Hari</h4>
                        <div class="chart-placeholder">
                            <!-- Placeholder untuk grafik -->
                            <p>Grafik akan ditampilkan di sini</p>
                        </div>
                    </div>

                    <div class="panel-jadwal">
                        <h2>Jadwal Booking Hari Ini</h2>
                        <h4>Daftar Booking Aktif</h4>
                        <div class="jadwal-placeholder">
                            <div class="jadwal-item">
                                <div>
                                    <strong>John Doe</strong>
                                    <p>10:00 - 11:00 WIB</p>
                                </div>
                                <span class="status-confirmed">Confirmed</span>
                            </div>

                            <div class="jadwal-item">
                                <div>
                                    <strong>Jane Smith</strong>
                                    <p>11:30 - 12:30 WIB</p>
                                </div>
                                <span class="status-pending">Pending</span>
                            </div>

                            <div class="jadwal-item">
                                <div>
                                    <strong>Michael Brown</strong>
                                    <p>13:00 - 14:00 WIB</p>
                                </div>
                                <span class="status-confirmed">Confirmed</span>
                            </div>

                            <!-- Tambahkan jadwal lainnya sesuai kebutuhan -->
                        </div>
                    </div>
                </div>

            </section>

        </main>

    </div>

</body>

</html>