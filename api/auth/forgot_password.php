<?php
/**
 * Forgot Password API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk request reset password
 * [SECURITY] OTP random 6 digit dikirim via email SMTP
 * [SECURITY] OTP expiry 5 menit
 * [SECURITY] Tidak memberi tahu jika email tidak terdaftar
 * 
 * [SETUP] Konfigurasi SMTP di bagian SMTP CONFIGURATION di bawah
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// SMTP CONFIGURATION - Konfigurasi Email Server
// ============================================================================

/**
 * [PRODUCTION SETUP] Konfigurasi SMTP untuk mengirim email OTP
 * 
 * Pilihan SMTP Provider:
 * 1. Gmail SMTP (untuk testing/development):
 *    - Host: smtp.gmail.com
 *    - Port: 587 (TLS) atau 465 (SSL)
 *    - Username: email@gmail.com
 *    - Password: App Password (bukan password biasa!)
 *    
 * 2. SendGrid SMTP:
 *    - Host: smtp.sendgrid.net
 *    - Port: 587
 *    - Username: apikey
 *    - Password: (SendGrid API Key)
 *    
 * 3. Mailtrap (untuk testing):
 *    - Host: smtp.mailtrap.io
 *    - Port: 587
 *    - Username: (dari Mailtrap dashboard)
 *    - Password: (dari Mailtrap dashboard)
 */

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'soangngacengan@gmail.com');
define('SMTP_PASSWORD', 'pmnl kmwd cgev mdbg');  // HARUS diganti dengan GMAIL APP PASSWORD
define('SMTP_FROM_EMAIL', 'soangngacengan@gmail.com');
define('SMTP_FROM_NAME', 'Shift Studio Barbershop');
define('SMTP_SECURE', 'tls');

// ============================================================================
// MOCK MODE - UNTUK TESTING TANPA SMTP
// ============================================================================
// [IMPORTANT] Set ke TRUE untuk testing tanpa kirim email real
// OTP akan tetap disimpan di session dan bisa dilihat di response
// Set ke FALSE untuk production dengan SMTP real
define('MOCK_EMAIL_MODE', false);

if (MOCK_EMAIL_MODE) {
    error_log("[BARBERSHOP WARNING] MOCK_EMAIL_MODE aktif - OTP tidak dikirim ke email real");
}

// ============================================================================
// RATE LIMITING
// ============================================================================

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_key = 'forgot_password_' . $ip;

if (!isset($_SESSION[$rate_key])) {
    $_SESSION[$rate_key] = ['count' => 0, 'last_attempt' => time()];
}

if (time() - $_SESSION[$rate_key]['last_attempt'] > 3600) {
    $_SESSION[$rate_key] = ['count' => 0, 'last_attempt' => time()];
}

if ($_SESSION[$rate_key]['count'] >= 5) {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Terlalu banyak percobaan. Silakan coba lagi nanti.']);
    exit;
}

// ============================================================================
// METHOD VALIDATION
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// ============================================================================
// INPUT VALIDATION
// ============================================================================

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['email'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email wajib diisi']);
    exit;
}

$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION[$rate_key]['count']++;
    $_SESSION[$rate_key]['last_attempt'] = time();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid']);
    exit;
}

$conn = getConnection();

// [SECURITY] Cek apakah email terdaftar
$stmt = $conn->prepare('SELECT id, nama FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $_SESSION[$rate_key]['count']++;
    $_SESSION[$rate_key]['last_attempt'] = time();
    echo json_encode([
        'status' => 'success',
        'message' => 'Jika email terdaftar, kode OTP akan dikirimkan'
    ]);
    $conn->close();
    exit;
}

$user = $res->fetch_assoc();
$nama_user = $user['nama'];

// ============================================================================
// GENERATE RANDOM OTP
// ============================================================================

$otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
$_SESSION['reset_email'] = $email;
$_SESSION['reset_otp'] = $otp;
$_SESSION['reset_otp_time'] = time();

error_log("[BARBERSHOP SECURITY] OTP generated and sent to: " . $email);

// ============================================================================
// SEND EMAIL VIA SMTP ATAU MOCK MODE
// ============================================================================

if (MOCK_EMAIL_MODE) {
    // [MOCK MODE] Tidak kirim email real, OTP tetap disimpan di session
    echo json_encode([
        'status' => 'success',
        'message' => '[MOCK MODE] Kode OTP: ' . $otp . ' (Email tidak dikirim - untuk testing)',
        'mock_otp' => $otp
    ]);
} else {
    // [PRODUCTION] Kirim email via SMTP
    $email_sent = sendOTPEmail($email, $nama_user, $otp, $error_message);

    if ($email_sent) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Kode OTP berhasil dikirim ke email Anda. Periksa inbox/spam.'
        ]);
    } else {
        // Return error message untuk debugging
        error_log("[BARBERSHOP ERROR] Failed to send OTP email to: " . $email . " Error: " . $error_message);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengirim email: ' . $error_message
        ]);
    }
}

$conn->close();

// ============================================================================
// EMAIL SENDING FUNCTIONS
// ============================================================================

function sendOTPEmail($to_email, $nama_user, $otp, &$error_message = '') {
    $subject = 'Kode OTP Reset Password - Shift Studio';
    
    $body = buildEmailTemplate($nama_user, $otp);
    $alt_body = "Halo {$nama_user},\n\nKode OTP Anda: {$otp}\n\nKode ini berlaku selama 5 menit.\n\nShift Studio Barbershop";
    
    // Coba kirim dengan PHPMailer
    if (sendWithPHPMailer($to_email, $nama_user, $subject, $body, $alt_body, $error_message)) {
        return true;
    }
    
    // Fallback ke PHP mail()
    return sendWithMailFunction($to_email, $subject, $body, $alt_body);
}

function buildEmailTemplate($nama_user, $otp) {
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a1a2e; color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 30px; }
        .otp-code { font-size: 32px; font-weight: bold; color: #1a1a2e; 
                    background: #fff; padding: 15px 30px; display: inline-block; 
                    border-radius: 8px; border: 2px solid #1a1a2e; letter-spacing: 5px; }
        .warning { color: #e74c3c; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Shift Studio Barbershop</h2>
        </div>
        <div class="content">
            <p>Halo <strong>{$nama_user}</strong>,</p>
            <p>Anda telah meminta reset password. Gunakan kode OTP berikut:</p>
            <p style="text-align: center; margin: 30px 0;">
                <span class="otp-code">{$otp}</span>
            </p>
            <p>Kode OTP ini berlaku selama <strong>5 menit</strong>.</p>
            <p class="warning">Jangan bagikan kode ini kepada siapapun.</p>
            <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
            <div class="footer">
                <p>Shift Studio Barbershop &copy; 2024</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
}

function sendWithPHPMailer($to_email, $nama_user, $subject, $body, $alt_body, &$error_message = '') {
    $phpmailer_file = __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    
    if (!file_exists($phpmailer_file)) {
        $error_message = 'PHPMailer library tidak ditemukan. Jalankan: composer install';
        return false;
    }
    
    try {
        require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
        require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        
        // [DEBUG] Enable SMTP debugging untuk troubleshooting
        // Ganti ke 2 untuk verbose output, 0 untuk production
        $mail->SMTPDebug  = 0; 
        $mail->Debugoutput = 'error_log';
        
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $nama_user);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $alt_body;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log("[BARBERSHOP ERROR] PHPMailer Error: " . $e->getMessage());
        return false;
    }
}

function sendWithMailFunction($to_email, $subject, $body, $alt_body) {
    $boundary = md5(time());
    
    $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
    
    $message = "--{$boundary}\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $message .= $alt_body . "\r\n\r\n";
    $message .= "--{$boundary}\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $message .= $body . "\r\n\r\n";
    $message .= "--{$boundary}--";
    
    return mail($to_email, $subject, $message, $headers);
}
?>
