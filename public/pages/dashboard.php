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

            <!-- HEADER -->
            <header class="header">

                <div class="header-left">
                    <h1>Dashboard</h1>
                    <p>Sabtu, 28 Februari 2026 00:09 WIB</p>
                </div>

                <div class="header-right">
                    <span class="status-online">● Online</span>
                    <button class="icon-btn"></button>
                    <button class="icon-btn"></button>
                </div>

            </header>

            <!-- SUMMARY -->
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
                    <h2>19</h2>
                    <span class="card-info">Transaksi lunas</span>
                </div>

            </section>


            <!-- GRID -->
            <section class="dashboard-grid">

                <!-- NOTIFIKASI -->
                <div class="panel-notif">

                    <div class="top-panel">

                        <div class="top-left">
                            <h3>Notifikasi Terbaru</h3>
                        </div>

                        <div class="top-right">
                            <button class="btn-secondary">Tandai Semua Sudah Dibaca</button>
                            <button class="btn-secondary">Semua Notifikasi →</button>
                        </div>

                    </div>

                    <ul class="notif-list">

                        <li>
                            <div>
                                <strong>Ada 3 Booking Baru</strong>
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
                                <strong>Permintaan Reschedule</strong>
                                <p>28 menit lalu</p>
                            </div>
                            <button class="btn-primary">Review</button>
                        </li>

                        <li>
                            <div>
                                <strong>Booking 005 Bayar Cash</strong>
                                <p>5 jam lalu</p>
                            </div>
                            <button class="btn-primary">Detail</button>
                        </li>

                        <li>
                            <div>
                                <strong>Booking 003 Dibatalkan</strong>
                                <p>8 jam lalu</p>
                            </div>
                            <button class="btn-primary">Detail</button>
                        </li>

                    </ul>

                </div>


                <!-- RIGHT -->
                <div class="grid-right">

                    <!-- BOOKING 7 HARI -->
                    <div class="panel-box">

                        <div class="panel-header">
                            <h2>BOOKING 7 HARI TERAKHIR</h2>
                        </div>

                        <div class="panel-body">

                            <h4>JUMLAH BOOKING PER-HARI</h4>

                            <div class="chart-bars">

                                <div class="bar-item">
                                    <div class="bar" style="height:60px"></div>
                                    <span>Sen</span>
                                </div>

                                <div class="bar-item">
                                    <div class="bar" style="height:80px"></div>
                                    <span>Sel</span>
                                </div>

                                <div class="bar-item">
                                    <div class="bar" style="height:40px"></div>
                                    <span>Rab</span>
                                </div>

                                <div class="bar-item">
                                    <div class="bar" style="height:60px"></div>
                                    <span>Kam</span>
                                </div>

                                <div class="bar-item">
                                    <div class="bar" style="height:80px"></div>
                                    <span>Jum</span>
                                </div>

                                <div class="bar-item">
                                    <div class="bar" style="height:90px"></div>
                                    <span>Sab</span>
                                </div>

                                <div class="bar-item active">
                                    <div class="bar" style="height:110px"></div>
                                    <span>Min</span>
                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- JADWAL HARI INI -->
                    <div class="panel-box">

                        <div class="panel-header between">
                            <h2>JADWAL HARI INI</h2>
                            <button class="btn-primary">Semua →</button>
                        </div>

                        <div class="panel-body jadwal-list">

                            <div class="jadwal-row">

                                <span class="time">08.00–09.00</span>

                                <div class="jadwal-info">

                                    <div class="line green"></div>

                                    <div>
                                        <strong>Jajat</strong>
                                        <p>Shift Studio Barbershop – Perumnas</p>
                                    </div>

                                </div>

                                <span class="status lunas">Lunas</span>

                            </div>

                            <div class="jadwal-row">

                                <span class="time">09.00–10.00</span>

                                <div class="jadwal-info">

                                    <div class="line yellow"></div>

                                    <div>
                                        <strong>Jajat</strong>
                                        <p>Shift Studio Barbershop – Perumnas</p>
                                    </div>

                                </div>

                                <span class="status pending">Pending</span>

                            </div>

                        </div>

                    </div>

                </div>

            </section>


            <!-- BOOKING TERBARU -->
            <section class="booking-terbaru">

                <div class="panel-box">

                    <div class="panel-header between">
                        <h2>BOOKING TERBARU</h2>
                        <button class="btn-primary">Semua Booking →</button>
                    </div>

                    <div class="panel-body">

                        <table class="booking-table">

                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>User</th>
                                    <th>Layanan</th>
                                    <th>Jam</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>

                                <tr>
                                    <td>001</td>
                                    <td>Jajat</td>
                                    <td>Haircut</td>
                                    <td>08.00–09.00</td>
                                    <td class="confirmed">Confirmed</td>
                                </tr>

                                <tr>
                                    <td>002</td>
                                    <td>Jupri</td>
                                    <td>Haircut</td>
                                    <td>09.00–10.00</td>
                                    <td class="pending">Pending</td>
                                </tr>

                                <tr>
                                    <td>003</td>
                                    <td>Pranaja</td>
                                    <td>Haircut</td>
                                    <td>11.00–12.00</td>
                                    <td class="confirmed">Confirmed</td>
                                </tr>

                                <tr>
                                    <td>004</td>
                                    <td>Elgiza</td>
                                    <td>Haircut</td>
                                    <td>08.00–09.00</td>
                                    <td class="cancel">Dibatalkan</td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </section>


        </main>

    </div>

</body>

</html>