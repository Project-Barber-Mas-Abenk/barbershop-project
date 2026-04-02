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
    <title>Lupa Kata Sandi - Shift Studio</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="../assets/css/component.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-left">
            <img src="../assets/img/logo.png" alt="Shift Studio">
        </div>

        <div class="auth-right">
            <div id="step-phone" class="auth-step active">
                <h4>Lupa Kata Sandi?</h4>
                <h2>Jangan khawatir! Masukkan email Anda di bawah ini, kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.</h2>

                <form id="phoneForm">
                    <label>Email</label>
                    <input type="email" name="email" id="email" placeholder="Masukkan Email" required>
                    <div id="errorMsg" style="color: #e74c3c; margin-bottom: 10px; font-size: 14px;"></div>
                    <button type="submit" id="submitBtn">Kirim</button>
                </form>

                <div class="auth-link" style="margin-top: 20px;">
                    <a href="login.php" style="text-align: end;">Kembali ke Login</a>
                </div>
            </div>

            <div id="step-otp" class="auth-step" style="display: none;">
                <h4>Masukkan Kode Verifikasi</h4>
                <h2>Masukkan kode verifikasi yang telah dikirim ke email Anda.</h2>

                <form id="otpForm">
                    <div class="otp-container">
                        <input type="text" maxlength="1" class="otp-input" inputmode="numeric" autocomplete="one-time-code">
                        <input type="text" maxlength="1" class="otp-input" inputmode="numeric">
                        <input type="text" maxlength="1" class="otp-input" inputmode="numeric">
                        <input type="text" maxlength="1" class="otp-input" inputmode="numeric">
                        <input type="text" maxlength="1" class="otp-input" inputmode="numeric">
                        <input type="text" maxlength="1" class="otp-input" inputmode="numeric">
                    </div>

                    <div id="otpError" style="color: #e74c3c; margin-bottom: 10px; font-size: 14px;"></div>
                    <button type="submit" id="otpBtn">Verifikasi</button>

                    <div class="resend-otp">
                        <p>Tidak menerima kode?
                            <a href="#" id="resendOtp">Kirim Ulang</a>
                        </p>
                    </div>
                </form>
            </div>

            <div id="step-reset" class="auth-step" style="display: none;">
                <h4>Reset Kata Sandi</h4>
                <h2>Masukkan kata sandi baru untuk akun Anda.</h2>

                <form id="resetForm">
                    <label>Kata Sandi Baru</label>
                    <input type="password" name="new_password" id="new_password" placeholder="Masukkan Kata Sandi Baru" required>

                    <label>Ulangi Kata Sandi Baru</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Ulangi Kata Sandi Baru" required>

                    <div id="resetError" style="color: #e74c3c; margin-bottom: 10px; font-size: 14px;"></div>
                    <button type="submit" id="resetBtn">Reset Kata Sandi</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const stepPhone = document.getElementById("step-phone");
            const stepOtp = document.getElementById("step-otp");
            const stepReset = document.getElementById("step-reset");

            function showStep(step) {
                stepPhone.style.display = 'none';
                stepOtp.style.display = 'none';
                stepReset.style.display = 'none';
                step.style.display = 'block';
            }

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

            document.getElementById("phoneForm").addEventListener("submit", async function(e) {
                e.preventDefault();
                const email = document.getElementById("email").value.trim();
                const errorMsg = document.getElementById("errorMsg");
                const submitBtn = document.getElementById("submitBtn");

                errorMsg.textContent = '';
                submitBtn.disabled = true;
                submitBtn.textContent = 'Mengirim...';

                try {
                    const response = await fetch('../../api/auth/forgot_password.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email })
                    });
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        alert(data.message);
                        showStep(stepOtp);
                    } else {
                        errorMsg.textContent = data.message || 'Gagal mengirim email';
                    }
                } catch (err) {
                    errorMsg.textContent = 'Terjadi kesalahan. Coba lagi.';
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Kirim';
                }
            });

            document.getElementById("otpForm").addEventListener("submit", function(e) {
                e.preventDefault();
                let otpCode = "";
                otpInputs.forEach(input => {
                    otpCode += input.value;
                });

                const otpError = document.getElementById("otpError");

                if (otpCode.length < 6) {
                    otpError.textContent = "Masukkan 6 digit kode verifikasi";
                    return;
                }

                otpError.textContent = '';
                showStep(stepReset);
            });

            document.getElementById("resetForm").addEventListener("submit", async function(e) {
                e.preventDefault();
                const email = document.getElementById("email").value.trim();
                let otpCode = "";
                otpInputs.forEach(input => {
                    otpCode += input.value;
                });
                
                const newPass = document.getElementById("new_password").value;
                const confirmPass = document.getElementById("confirm_password").value;
                const resetError = document.getElementById("resetError");
                const resetBtn = document.getElementById("resetBtn");

                if (newPass.length < 6) {
                    resetError.textContent = "Password minimal 6 karakter";
                    return;
                }

                if (newPass !== confirmPass) {
                    resetError.textContent = "Konfirmasi password tidak cocok";
                    return;
                }

                resetBtn.disabled = true;
                resetBtn.textContent = 'Memproses...';

                try {
                    const response = await fetch('../../api/auth/reset_password.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email, otp: otpCode, new_password: newPass })
                    });
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        alert(data.message);
                        window.location.href = "login.php";
                    } else {
                        resetError.textContent = data.message || 'Gagal mengatur ulang kata sandi';
                    }
                } catch (err) {
                    resetError.textContent = 'Terjadi kesalahan. Coba lagi.';
                } finally {
                    resetBtn.disabled = false;
                    resetBtn.textContent = 'Reset Kata Sandi';
                }
            });

            document.getElementById("resendOtp").addEventListener("click", function(e) {
                e.preventDefault();
                alert('Kode verifikasi telah dikirim ulang.');
            });
        });
    </script>
</body>
</html>
