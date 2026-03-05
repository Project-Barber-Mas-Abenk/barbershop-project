<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$userName = $_SESSION['user_nama'] ?? 'User';
?>
<div class="sidebar">
    <img src="../assets/img/logo.png" alt="">
    <nav>
        <div>
            <p>Have a nice day!</p>
            <h3><?php echo htmlspecialchars($userName); ?></h3>
        </div>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="booking.php"><?php echo $isAdmin ? 'Daftar Booking' : 'Booking Baru'; ?></a></li>
            <?php if ($isAdmin): ?>
            <li><a href="payment.php">Manajemen Payment</a></li>
            <li><a href="users.php">Manajemen User</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="logout">
        <a href="#" onclick="logout(); return false;">Logout</a>
    </div>
</div>

<script>
async function logout() {
    try {
        const response = await fetch('../../api/auth/logout.php', { method: 'POST' });
        const result = await response.json();
        if (result.status === 'success') {
            window.location.href = 'login.php';
        }
    } catch (error) {
        console.error('Logout failed:', error);
    }
}
</script>