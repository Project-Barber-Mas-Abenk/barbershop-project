<?php
$title = "Login - Shift Studio";
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
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
                <label>Nomor Telepon</label>
                <input type="text" name="username" placeholder="Masukkan Nomor Telepon" required>

                <label>Kata Sandi</label>
                <input type="password" name="password" placeholder="Masukkan Kata Sandi" required>

                <div class="forgot-password">
                    <a href="forgotPassword.php">Lupa Kata Sandi?</a>
                </div>

                <button type="submit">Login</button>
            </form>

            <div class="divider">Atau masuk menggunakan</div>

            <button class="google-btn">
                <svg width="18" height="18" viewBox="0 0 48 48">
                    <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.9 32.7 29.4 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3 0 5.7 1.1 7.8 2.9l5.7-5.7C34.1 6.5 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.7-.4-3.5z" />
                    <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 16 18.9 12 24 12c3 0 5.7 1.1 7.8 2.9l5.7-5.7C34.1 6.5 29.3 4 24 4c-7.7 0-14.3 4.3-17.7 10.7z" />
                    <path fill="#4CAF50" d="M24 44c5.3 0 10.1-2.1 13.7-5.5l-6.3-5.2C29.4 36 27 37 24 37c-5.3 0-9.8-3.6-11.3-8.4l-6.6 5.1C9.7 39.6 16.3 44 24 44z" />
                    <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-1 3-3.4 5.4-6.9 6.5l6.3 5.2C39.5 36.2 44 30.7 44 24c0-1.3-.1-2.7-.4-3.5z" />
                </svg>
                Lanjutkan dengan Google
            </button>

            <div class="auth-link">
                You don't have any account? <a href="register.php">Register</a>
            </div>
        </div>

    </div>

</body>

</html>