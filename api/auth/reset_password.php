<?php
/**
 * Reset Password API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk reset password dengan OTP
 * [SECURITY] Validasi OTP dengan expiry time (5 menit)
 * [SECURITY] Password di-hash dengan bcrypt
 * [SECURITY] Hapus data reset setelah berhasil
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// RATE LIMITING
// ============================================================================

// [SECURITY] Rate limiting untuk mencegah brute force OTP
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_key = 'reset_attempts_' . $ip;

if (!isset($_SESSION[$rate_key])) {
    $_SESSION[$rate_key] = ['count' => 0, 'last_attempt' => time()];
}

if (time() - $_SESSION[$rate_key]['last_attempt'] > 900) {
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

if (empty($data['email']) || empty($data['otp']) || empty($data['new_password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email, OTP, dan password baru wajib diisi']);
    exit;
}

// [SECURITY] Sanitasi input
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$otp = trim($data['otp']);
$new_password = $data['new_password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION[$rate_key]['count']++;
    $_SESSION[$rate_key]['last_attempt'] = time();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid']);
    exit;
}

// [SECURITY] Validasi OTP dari session
if (empty($_SESSION['reset_email']) || $_SESSION['reset_email'] !== $email || 
    empty($_SESSION['reset_otp']) || $_SESSION['reset_otp'] !== $otp) {
    $_SESSION[$rate_key]['count']++;
    $_SESSION[$rate_key]['last_attempt'] = time();
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Email atau kode OTP tidak valid']);
    exit;
}

// [SECURITY] Cek waktu OTP kadaluarsa (5 menit)
if (time() - $_SESSION['reset_otp_time'] > 300) {
    // Hapus data reset yang kadaluarsa
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_otp']);
    unset($_SESSION['reset_otp_time']);
    
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Kode OTP sudah kadaluarsa']);
    exit;
}

// [SECURITY] Validasi panjang password baru
if (strlen($new_password) < 6) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password minimal 6 karakter']);
    exit;
}

// ============================================================================
// DATABASE OPERATIONS
// ============================================================================

$conn = getConnection();

// [SECURITY] Hash password baru
$hash = password_hash($new_password, PASSWORD_BCRYPT);

$stmt = $conn->prepare('UPDATE users SET password = ? WHERE email = ?');
$stmt->bind_param('ss', $hash, $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // [SECURITY] Hapus data reset di session
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_otp']);
    unset($_SESSION['reset_otp_time']);
    unset($_SESSION[$rate_key]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Kata sandi berhasil diatur ulang. Silakan login.'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengatur ulang kata sandi']);
}

$conn->close();
?>
