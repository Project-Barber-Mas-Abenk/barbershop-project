<?php
$title = "Register - Shift Studio";

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
            <h4>Selamat Datang di Shift Studio</h4>
            <h2>Bergabunglah bersama kami dan rasakan kenyamanan di setiap pencukuran.</h2>

            <form id="registerForm">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Masukkan Nama Lengkap" required>

                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan Username" required>

                <label>Nomor Telepon</label>
                <input type="text" name="no_hp" placeholder="Masukkan Nomor Telepon" required>

                <label>Kata Sandi</label>
                <input type="password" name="password" placeholder="Masukkan Kata Sandi" required>

                <label>Konfirmasi Kata Sandi</label>
                <input type="password" name="confirm_password" placeholder="Masukkan Ulang Kata Sandi" required>

                <button type="submit">Buat Akun</button>
            </form>

            <div id="errorMsg" style="color: red; margin-top: 10px; display: none;"></div>
            <div id="successMsg" style="color: green; margin-top: 10px; display: none;"></div>

            <div class="auth-link">
                Sudah memiliki akun? <a href="login.php">Login</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/api.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            if (formData.get('password') !== formData.get('confirm_password')) {
                document.getElementById('errorMsg').textContent = 'Password tidak cocok';
                document.getElementById('errorMsg').style.display = 'block';
                return;
            }

            const data = {
                nama: formData.get('nama'),
                username: formData.get('username'),
                no_hp: formData.get('no_hp'),
                password: formData.get('password')
            };

            try {
                const response = await fetch('../../api/auth/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    document.getElementById('successMsg').textContent = 'Registrasi berhasil! Redirecting...';
                    document.getElementById('successMsg').style.display = 'block';
                    document.getElementById('errorMsg').style.display = 'none';
                    setTimeout(() => window.location.href = 'login.php', 1500);
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