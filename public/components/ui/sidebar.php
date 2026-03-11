<?php
$currentPage = $_GET['page'] ?? 'dashboard';
?>

<div class="sidebar">
    <img src="../assets/img/logo.png" alt="Shift Studio Logo">

    <nav>
        <div>
            <p>Have a nice day!</p>
            <h3>Mufadhol Abenk</h3>
        </div>
        <ul>
            <li class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <a href="dashboard.php?page=dashboard">Dashboard</a>
            </li>
            <li class="<?= $currentPage === 'booking' ? 'active' : '' ?>">
                <a href="dashboard.php?page=booking">Daftar Booking</a>
            </li>
            <li class="<?= $currentPage === 'layanan' ? 'active' : '' ?>">
                <a href="dashboard.php?page=layanan">Layanan</a>
            </li>
            <li class="<?= $currentPage === 'barber' ? 'active' : '' ?>">
                <a href="dashboard.php?page=barber">Barber/Staff</a>
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