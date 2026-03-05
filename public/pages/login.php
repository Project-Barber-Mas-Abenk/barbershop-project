<?php
$title = "Login - Shift Studio";

session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-left">
            <img src="../assets/img/logo.png" alt="Shift Studio">
        </div>

        <div class="auth-right">
            <h4>Selamat Datang Kembali di Shift Studio</h4>
            <h2>Masuk ke akun Anda untuk melanjutkan pemesanan dan menikmati layanan kami.</h2>

            <form id="loginForm">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan Username" required>

                <label>Kata Sandi</label>
                <input type="password" name="password" placeholder="Masukkan Kata Sandi" required>

                <div class="forgot-password">
                    <a href="forgotPassword.php">Lupa Kata Sandi?</a>
                </div>

                <button type="submit">Login</button>
            </form>

            <div id="errorMsg" style="color: red; margin-top: 10px; display: none;"></div>

            <div class="auth-link">
                Belum punya akun? <a href="register.php">Daftar</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/api.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = {
                username: formData.get('username'),
                password: formData.get('password')
            };

            try {
                const response = await fetch('../../api/auth/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    window.location.href = 'dashboard.php';
                } else {
                    document.getElementById('errorMsg').textContent = result.message;
                    document.getElementById('errorMsg').style.display = 'block';
                }
            } catch (error) {
                document.getElementById('errorMsg').textContent = 'Terjadi kesalahan. Coba lagi.';
                document.getElementById('errorMsg').style.display = 'block';
            }
        });
    </script>
</body>
</html>