<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['user_role'] ?? 'user';
$nama = $_SESSION['user_nama'] ?? $_SESSION['admin_nama'] ?? 'User';

$page = $_GET['page'] ?? 'dashboard';

$allowedPages = [
    'dashboard',
    'booking',
    'layanan',
    'barber',
    'settings'
];

if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}
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

            <?php
            include "../pages/sections/$page.php";
            ?>

        </main>

    </div>

    <script src="../assets/js/auth.js"></script>

    <script>
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

            const el = document.getElementById('currentTime');

            if (el) {
                el.textContent =
                    now.toLocaleDateString('id-ID', options) + ' WIB';
            }

        }

        updateTime();
        setInterval(updateTime, 60000);

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
    </script>

</body>

</html>