<?php
$title = "Lupa Kata Sandi - Shift Studio";
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="../assets/css/component.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const DUMMY_MODE = true;

            const stepPhone = document.getElementById("step-phone");
            const stepOtp = document.getElementById("step-otp");
            const stepReset = document.getElementById("step-reset");

            function showStep(step) {
                document.querySelectorAll(".auth-step").forEach(el => {
                    el.classList.remove("active");
                });
                step.classList.add("active");
            }

            // OTP PIN BEHAVIOR
            const otpInputs = document.querySelectorAll(".otp-input");

            otpInputs.forEach((input, index) => {

                input.addEventListener("input", function() {
                    this.value = this.value.replace(/[^0-9]/g, "");
                    if (this.value && index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                });

                input.addEventListener("keydown", function(e) {
                    if (e.key === "Backspace" && !this.value && index > 0) {
                        otpInputs[index - 1].focus();
                    }
                });

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

                    showModal({
                        type: "success",
                        titleText: "Kode Dikirim",
                        messageText: "Kode verifikasi berhasil dikirim ke nomor Anda.",
                        buttonText: "Lanjut",
                        onConfirm: function() {
                            showStep(stepOtp);
                        }
                    });

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
                        showModal({
                            type: "error",
                            titleText: "Kode Tidak Lengkap",
                            messageText: "Silakan masukkan 6 digit kode verifikasi terlebih dahulu.",
                            buttonText: "Mengerti"
                        });
                        return;
                    }

                    showModal({
                        type: "success",
                        titleText: "Verifikasi Berhasil",
                        messageText: "Kode verifikasi valid.",
                        buttonText: "Lanjut",
                        onConfirm: function() {
                            showStep(stepReset);
                        }
                    });

                    return;
                }
            });

            // STEP 3
            document.getElementById("resetForm").addEventListener("submit", function(e) {
                if (DUMMY_MODE) {
                    e.preventDefault();

                    showModal({
                        type: "success",
                        titleText: "Berhasil",
                        messageText: "Kata sandi berhasil diperbarui.",
                        buttonText: "Login Sekarang",
                        onConfirm: function() {
                            window.location.href = "login.php";
                        }
                    });

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

            <!-- Step 2 -->
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

    <?php include '../components/ui/modal.php'; ?>

</body>

</html>