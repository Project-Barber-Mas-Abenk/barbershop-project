<?php
$title = "Register - Shift Studio";

ob_start();
?>

<h2>Selamat Datang di Shift Studio</h2>
<h1>Bergabunglah bersama kami dan rasakan kenyamanan di setiap pencukuran.</h1>

<form action="../api/auth/register.php" method="POST">

<?php
$label = "Nama Lengkap";
$type = "text";
$name = "nama";
$placeholder = "Masukkan nama lengkap Anda";
include "components/input.php";
?>

<?php
$label = "Nomor Telepon";
$type = "text";
$name = "phone";
$placeholder = "Masukkan nomor telepon Anda";
include "components/input.php";
?>

<?php
$label = "Kata Sandi";
$type = "password";
$name = "password";
$placeholder = "Masukkan kata sandi Anda";
include "components/input.php";
?>

<?php
$text = "Konfirmasi Kata Sandi";
$type = "password";
$name = "confirm_password";
$placeholder = "Masukkan ulang kata sandi Anda";
include "components/input.php";
?>

<?php
$text = "Buat Akun";
$type = "submit";
include "components/button.php";
?>

</form>
<hr>
<?php include "components/oauth-button.php"; ?>

<p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>

<?php
$content = ob_get_clean();
include "components/auth-layout.php";
?>

<?php
ob_end_clean();
?>