<?php
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
    <title>Register - Shift Studio</title>
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
                <input type="text" name="nama" id="nama" placeholder="Masukkan Nama Lengkap" required>

                <label>Email</label>
                <input type="email" name="email" id="email" placeholder="Masukkan Email" required>

                <label>Nomor Telepon</label>
                <input type="text" name="no_hp" id="no_hp" placeholder="Masukkan Nomor Telepon" required>

                <label>Kata Sandi</label>
                <input type="password" name="password" id="password" placeholder="Masukkan Kata Sandi" required>

                <label>Konfirmasi Kata Sandi</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Masukkan Ulang Kata Sandi" required>

                <div id="errorMsg" style="color: #e74c3c; margin-bottom: 10px; font-size: 14px;"></div>

                <button type="submit" id="submitBtn">Buat Akun</button>
            </form>

            <div class="divider">Atau masuk menggunakan</div>

            <button class="google-btn" id="googleBtn">
                <svg width="18" height="18" viewBox="0 0 48 48">
                    <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.9 32.7 29.4 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3 0 5.7 1.1 7.8 2.9l5.7-5.7C34.1 6.5 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.7-.4-3.5z" />
                    <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 16 18.9 12 24 12c3 0 5.7 1.1 7.8 2.9l5.7-5.7C34.1 6.5 29.3 4 24 4c-7.7 0-14.3 4.3-17.7 10.7z" />
                    <path fill="#4CAF50" d="M24 44c5.3 0 10.1-2.1 13.7-5.5l-6.3-5.2C29.4 36 27 37 24 37c-5.3 0-9.8-3.6-11.3-8.4l-6.6 5.1C9.7 39.6 16.3 44 24 44z" />
                    <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-1 3-3.4 5.4-6.9 6.5l6.3 5.2C39.5 36.2 44 30.7 44 24c0-1.3-.1-2.7-.4-3.5z" />
                </svg>
                Lanjutkan dengan Google
            </button>

            <div class="auth-link">
                Sudah memiliki akun? <a href="login.php">Login</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const errorMsg = document.getElementById('errorMsg');
            const submitBtn = document.getElementById('submitBtn');
            
            const nama = document.getElementById('nama').value.trim();
            const email = document.getElementById('email').value.trim();
            const no_hp = document.getElementById('no_hp').value.trim();
            const password = document.getElementById('password').value;
            const confirm_password = document.getElementById('confirm_password').value;
            
            errorMsg.textContent = '';
            
            if (password !== confirm_password) {
                errorMsg.textContent = 'Konfirmasi password tidak cocok!';
                return;
            }
            
            if (password.length < 6) {
                errorMsg.textContent = 'Password minimal 6 karakter!';
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses...';
            
            try {
                const response = await fetch('../../api/auth/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nama, email, no_hp, password })
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    window.location.href = 'dashboard.php';
                } else {
                    errorMsg.textContent = data.message || 'Registrasi gagal';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Buat Akun';
                }
            } catch (err) {
                errorMsg.textContent = 'Terjadi kesalahan. Coba lagi.';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Buat Akun';
            }
        });
        
        document.getElementById('googleBtn').addEventListener('click', function() {
            alert('Google OAuth memerlukan konfigurasi Google Client ID. Hubungi admin untuk mengaktifkan fitur ini.');
        });
    </script>
</body>
</html>
