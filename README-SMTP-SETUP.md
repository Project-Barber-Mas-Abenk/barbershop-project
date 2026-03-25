# SMTP Email Setup Guide - Forgot Password
## Barbershop Project - Backend

File ini berisi instruksi untuk mengkonfigurasi SMTP email agar fitur **Forgot Password** dapat mengirim OTP ke email user.

---

## Persiapan

### 1. Install PHPMailer (WAJIB)

Jalankan command berikut di terminal/command prompt:

```bash
cd c:\xampp\htdocs\Web_BarberShop
composer install
```

Jika Anda belum menginstall Composer:
1. Download dari https://getcomposer.org/download/
2. Install seperti aplikasi biasa
3. Restart terminal/command prompt

---

##  Konfigurasi SMTP

Edit file: `api/auth/forgot_password.php`

Ubah konstanta di bagian **SMTP CONFIGURATION** (lines 45-51):

### Opsi 1: Gmail SMTP (Recommended untuk Development)

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'emailanda@gmail.com');           // Ganti dengan email Gmail Anda
define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx');         // App Password (lihat cara buat di bawah)
define('SMTP_FROM_EMAIL', 'noreply@shiftstudio.com');
define('SMTP_FROM_NAME', 'Shift Studio Barbershop');
define('SMTP_SECURE', 'tls');
```

**Cara mendapatkan Gmail App Password:**
1. Buka https://myaccount.google.com/
2. Security → 2-Step Verification (harus aktif!)
3. Search "App passwords" di search bar
4. Select app: "Mail"
5. Select device: "Other (Custom name)" → nama "Barbershop App"
6. Copy App Password yang muncul (format: xxxx xxxx xxxx xxxx)
7. Paste ke `SMTP_PASSWORD`

 **Jangan gunakan password Gmail biasa!** Harus pakai App Password.

---

### Opsi 2: SendGrid SMTP (Recommended untuk Production)

```php
define('SMTP_HOST', 'smtp.sendgrid.net');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'apikey');
define('SMTP_PASSWORD', 'SG.xxxxxxxxxxxxxxxxxxx');    // SendGrid API Key
define('SMTP_FROM_EMAIL', 'noreply@shiftstudio.com');
define('SMTP_FROM_NAME', 'Shift Studio Barbershop');
define('SMTP_SECURE', 'tls');
```

---

### Opsi 3: Mailtrap (Recommended untuk Testing)

```php
define('SMTP_HOST', 'smtp.mailtrap.io');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'xxxxxxxxxxxxx');    // Dari Mailtrap dashboard
define('SMTP_PASSWORD', 'xxxxxxxxxxxxx');    // Dari Mailtrap dashboard
define('SMTP_FROM_EMAIL', 'noreply@shiftstudio.com');
define('SMTP_FROM_NAME', 'Shift Studio Barbershop');
define('SMTP_SECURE', 'tls');
```

Mailtrap adalah fake SMTP server untuk testing. Email tidak benar-benar dikirim ke user, tapi ditangkap di dashboard Mailtrap.

1. Buat akun di https://mailtrap.io/
2. Go to Inbox → SMTP Settings
3. Copy username & password

---

##  Testing

Setelah konfigurasi selesai:

1. **Register user baru** atau gunakan user yang sudah ada
2. Buka halaman Login → Klik "Lupa Kata Sandi?"
3. Masukkan email terdaftar
4. Klik "Kirim OTP"
5. Cek inbox email (atau Mailtrap inbox jika pakai Mailtrap)
6. Email akan berisi OTP 6 digit random
7. Masukkan OTP di form reset password

---

##  Struktur Email OTP

Email yang dikirim berisi:
- Subject: "Kode OTP Reset Password - Shift Studio"
- Template HTML dengan styling
- OTP 6 digit yang jelas terlihat
- Peringatan expiry 5 menit
- Security warning

---

##  Security Features

- OTP random 6 digit (bukan hardcoded)
- OTP expiry 5 menit
- Rate limiting: max 5 request per jam per IP
- Tidak expose OTP di log
- Email template dengan branding

---

##  Troubleshooting

### Error: "Failed to send OTP email"

**Cek:**
1. PHPMailer sudah diinstall? (`composer install`)
2. SMTP credentials benar?
3. Firewall tidak blok port 587?
4. Untuk Gmail: 2-Step Verification sudah aktif?

### Error: "Could not connect to SMTP host"

**Solusi:**
- Cek koneksi internet
- Cek XAMPP → Apache → Config → PHP (php.ini)
- Pastikan extension `openssl` aktif (hilangkan ; di depan `extension=openssl`)

### Error: "SMTP Error: Could not authenticate"

**Solusi:**
- Gmail: Pastikan pakai App Password, bukan password biasa
- Cek username dan password benar
- Untuk Gmail: Less Secure App Access harus OFF, pakai App Password

---

##  File Structure

```
Web_BarberShop/
├── composer.json           # (sudah dibuat)
├── vendor/                 # (akan dibuat oleh composer)
│   └── phpmailer/
│       └── phpmailer/
│           └── src/
│               ├── PHPMailer.php
│               ├── SMTP.php
│               └── Exception.php
└── api/
    └── auth/
        └── forgot_password.php    # (sudah diupdate dengan SMTP)
```

---

##  Production Checklist

- [ ] Ganti ke production SMTP (SendGrid/AWS SES/Outlook)
- [ ] Update `SMTP_FROM_EMAIL` dengan domain yang valid
- [ ] Setup SPF dan DKIM records di DNS domain
- [ ] Test email deliverability
- [ ] Monitor email logs

---

**Butuh bantuan?** Cek log error di file: `C:\xampp\apache\logs\error.log`
