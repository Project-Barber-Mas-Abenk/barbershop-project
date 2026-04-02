<?php
/**
 * Register API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint registrasi user baru
 * [SECURITY] Password di-hash dengan bcrypt
 * [SECURITY] Validasi input dan sanitasi
 * [SECURITY] Auto-login setelah registrasi
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// RATE LIMITING
// ============================================================================

// [SECURITY] Rate limiting untuk mencegah spam registrasi
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_key = 'register_attempts_' . $ip;

if (!isset($_SESSION[$rate_key])) {
    $_SESSION[$rate_key] = ['count' => 0, 'last_attempt' => time()];
}

if (time() - $_SESSION[$rate_key]['last_attempt'] > 3600) {
    $_SESSION[$rate_key] = ['count' => 0, 'last_attempt' => time()];
}

if ($_SESSION[$rate_key]['count'] >= 10) {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Terlalu banyak percobaan registrasi. Silakan coba lagi nanti.']);
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

if (empty($data['nama']) || empty($data['email']) || empty($data['password']) || empty($data['no_hp'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nama, email, password, dan no_hp wajib diisi']);
    exit;
}

// [SECURITY] Sanitasi input
$nama = htmlspecialchars(trim($data['nama']), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$password = $data['password'];
$no_hp = htmlspecialchars(trim($data['no_hp']), ENT_QUOTES, 'UTF-8');

// [SECURITY] Validasi format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION[$rate_key]['count']++;
    $_SESSION[$rate_key]['last_attempt'] = time();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid']);
    exit;
}

// [SECURITY] Validasi panjang password
if (strlen($password) < 6) {
    $_SESSION[$rate_key]['count']++;
    $_SESSION[$rate_key]['last_attempt'] = time();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password minimal 6 karakter']);
    exit;
}

// [SECURITY] Validasi format no_hp
if (!preg_match('/^[0-9\-\+\s]{10,15}$/', $no_hp)) {
    $_SESSION[$rate_key]['count']++;
    $_SESSION[$rate_key]['last_attempt'] = time();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format nomor HP tidak valid']);
    exit;
}

// ============================================================================
// DATABASE OPERATIONS
// ============================================================================

$conn = getConnection();

// [SECURITY] Cek email sudah terdaftar
$check = $conn->prepare('SELECT id FROM users WHERE email = ?');
$check->bind_param('s', $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    $_SESSION[$rate_key]['count']++;
    $_SESSION[$rate_key]['last_attempt'] = time();
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Email sudah terdaftar']);
    $conn->close();
    exit;
}

// [SECURITY] Hash password dengan bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT);

// ============================================================================
// TRANSACTION
// ============================================================================

$conn->begin_transaction();

try {
    // [CORE LOGIC] Insert user
    $stmt = $conn->prepare('INSERT INTO users (email, password, nama, no_hp, role) VALUES (?, ?, ?, ?, "user")');
    $stmt->bind_param('ssss', $email, $hash, $nama, $no_hp);
    $stmt->execute();
    $user_id = $conn->insert_id;

    // [CORE LOGIC] Insert pelanggan
    $stmt2 = $conn->prepare('INSERT INTO pelanggan (user_id, nama, no_hp) VALUES (?, ?, ?)');
    $stmt2->bind_param('iss', $user_id, $nama, $no_hp);
    $stmt2->execute();

    $conn->commit();

    // Reset rate limit saat berhasil
    unset($_SESSION[$rate_key]);

    // [SECURITY] Auto-login setelah registrasi
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_nama'] = $nama;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = 'user';
    $_SESSION['logged_in'] = true;

    echo json_encode([
        'status' => 'success',
        'message' => 'Registrasi berhasil',
        'data' => [
            'user_id' => $user_id,
            'nama' => $nama,
            'email' => $email,
            'role' => 'user'
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("[BARBERSHOP SECURITY] Registration failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Registrasi gagal: Terjadi kesalahan sistem']);
}

$conn->close();
?>
