<?php
// [FIX] Cek session sudah aktif sebelum panggil session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = $_GET['page'] ?? 'dashboard';

// [BACKEND] Ambil nama dari session berdasarkan role
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $userName = $_SESSION['admin_nama'] ?? 'Admin';
} else {
    $userName = $_SESSION['user_nama'] ?? 'User';
}
?>

<div class="sidebar">
    <img src="../assets/img/logo.png" alt="Shift Studio Logo">

    <nav>
        <div>
            <p>Have a nice day!</p>
            <h3><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></h3>
        </div>
        <ul>
            <li class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <a href="dashboard.php?page=dashboard">Dashboard</a>
            </li>
            <li class="<?= $currentPage === 'booking' ? 'active' : '' ?>">
                <a href="dashboard.php?page=booking">Daftar Booking</a>
            </li>
            <li class="<?= $currentPage === 'layanan' ? 'active' : '' ?>">
                <a href="dashboard.php?page=layanan">Layanan & Info Barber</a>
            </li>
            <li class="<?= $currentPage === 'settings' ? 'active' : '' ?>">
                <a href="dashboard.php?page=settings">Settings</a>
            </li>
        </ul>
    </nav>

    <div class="logout-btn-container">
        <button onclick="handleLogout()"
            class="logout-btn">Logout</button>
    </div>

</div>