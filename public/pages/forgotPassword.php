<?php
$title = "Lupa Kata Sandi - Shift Studio";
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const DUMMY_MODE = true; // ubah ke false kalau mau aktif backend asli

            const stepPhone = document.getElementById("step-phone");
            const stepOtp = document.getElementById("step-otp");
            const stepReset = document.getElementById("step-reset");

            function showStep(step) {
                document.querySelectorAll(".auth-step").forEach(el => {
                    el.classList.remove("active");
                });
                step.classList.add("active");
            }

            // ==========================
            // OTP PIN BEHAVIOR (NEW)
            // ==========================
            const otpInputs = document.querySelectorAll(".otp-input");

            otpInputs.forEach((input, index) => {

                // Hanya angka
                input.addEventListener("input", function() {
                    this.value = this.value.replace(/[^0-9]/g, "");

                    if (this.value && index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                });

                // Backspace mundur
                input.addEventListener("keydown", function(e) {
                    if (e.key === "Backspace" && !this.value && index > 0) {
                        otpInputs[index - 1].focus();
                    }
                });

                // Support paste 6 digit langsung
                input.addEventListener("paste", function(e) {
                    e.preventDefault();
                    let pasteData = e.clipboardData.getData("text").replace(/[^0-9]/g, "");

                    if (pasteData.length === otpInputs.length) {
                        otpInputs.forEach((inp, i) => {
                            inp.value = pasteData[i];
                        });
                        otpInputs[otpInputs.length - 1].focus();
                    }
                });
            });

            // STEP 1
            document.getElementById("phoneForm").addEventListener("submit", function(e) {
                if (DUMMY_MODE) {
                    e.preventDefault();
                    showStep(stepOtp);
                    return;
                }
            });

            // STEP 2
            document.getElementById("otpForm").addEventListener("submit", function(e) {
                if (DUMMY_MODE) {
                    e.preventDefault();

                    let otpCode = "";
                    otpInputs.forEach(input => {
                        otpCode += input.value;
                    });

                    if (otpCode.length < 6) {
                        alert("Kode verifikasi belum lengkap.");
                        return;
                    }

                    showStep(stepReset);
                    return;
                }
            });

            // STEP 3
            document.getElementById("resetForm").addEventListener("submit", function(e) {
                if (DUMMY_MODE) {
                    e.preventDefault();
                    window.location.href = "login.php";
                    return;
                }
            });

        });
    </script>

    <div class="auth-container">

        <div class="auth-left">
            <img src="../assets/img/logo.png" alt="Shift Studio">
        </div>

        <div class="auth-right">

            <!-- Step 1 -->
            <div id="step-phone" class="auth-step active">
                <h4>Lupa Kata Sandi?</h4>
                <h2>Jangan khawatir! Masukkan Nomor Telepon Anda di bawah ini,
                    kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
                </h2>

                <form id="phoneForm">
                    <label>Nomor Telepon</label>
                    <input type="text" name="phone" placeholder="Masukkan Nomor Telepon" required>
                    <button type="submit">Kirim</button>
                </form>
            </div>

            <!-- Step 2 (OTP PIN STYLE) -->
            <div id="step-otp" class="auth-step">
                <h4>Masukkan Kode Verifikasi</h4>
                <h2>Masukkan kode verifikasi yang telah dikirim ke nomor telepon Anda.</h2>

                <form id="otpForm">
                    <div class="otp-container">
                        <input type="text" maxlength="1" class="otp-input" inputmode="numeric" autocomplete="one-time-code">
                        <input type="text" maxlength="1" class="otp-input" inputmode="numeric">
                        <input type="text" maxlength="1" class="otp-input" inputmode="numeric">
                        <input type="text" maxlength="1" class="otp-input" inputmode="numeric">
                        <input type="text" maxlength="1" class="otp-input" inputmode="numeric">
                        <input type="text" maxlength="1" class="otp-input" inputmode="numeric">
                    </div>

                    <button type="submit">Verifikasi</button>

                    <!-- Resend OTP-->
                    <div class="resend-otp">
                        <p>Tidak menerima kode verifikasi?
                            <a href="#" id="resendOtp">Kirim Ulang Kode?</a>
                        </p>
                    </div>

                </form>
            </div>

            <!-- Step 3 -->
            <div id="step-reset" class="auth-step">
                <h4>Reset Kata Sandi</h4>
                <h2>Masukkan kata sandi baru untuk akun Anda.</h2>

                <form id="resetForm">
                    <label>Kata Sandi Baru</label>
                    <input type="password" name="new_password" placeholder="Masukkan Kata Sandi Baru" required>

                    <label>Ulangi Kata Sandi Baru</label>
                    <input type="password" name="confirm_password" placeholder="Ulangi Kata Sandi Baru" required>

                    <button type="submit">Reset Kata Sandi</button>
                </form>
            </div>

        </div>

    </div>

</body>

</html>