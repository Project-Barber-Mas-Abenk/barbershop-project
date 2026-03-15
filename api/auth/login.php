<?php
/**
 * Login API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint autentikasi dengan rate limiting
 * [SECURITY] Menggunakan password_hash dan password_verify
 * [SECURITY] Session regeneration untuk mencegah session fixation
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// RATE LIMITING
// ============================================================================

// [SECURITY] Implementasi rate limiting untuk mencegah brute force
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_key = 'login_attempts_' . $ip;

if (!isset($_SESSION[$rate_key])) {
    $_SESSION[$rate_key] = ['count' => 0, 'last_attempt' => time()];
}

// Reset setelah 15 menit
if (time() - $_SESSION[$rate_key]['last_attempt'] > 900) {
    $_SESSION[$rate_key] = ['count' => 0, 'last_attempt' => time()];
}

// [SECURITY] Blokir jika terlalu banyak percobaan
if ($_SESSION[$rate_key]['count'] >= 5) {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.']);
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

if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email dan password wajib diisi']);
    exit;
}

// [SECURITY] Sanitasi input
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$password = $data['password']; // Jangan dimodifikasi untuk password

// ============================================================================
// AUTHENTICATION - ADMIN (by username)
// ============================================================================

$conn = getConnection();

// [SECURITY] Cek admin dengan prepared statement (admin pakai username, bukan email)
$stmt = $conn->prepare('SELECT id, nama, password FROM admin WHERE username = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    
    // [SECURITY] Verifikasi password dengan password_verify
    if (password_verify($password, $admin['password'])) {
        // Reset rate limit saat berhasil login
        unset($_SESSION[$rate_key]);
        
        // [SECURITY] Regenerate session ID untuk mencegah session fixation
        session_regenerate_id(true);
        
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nama'] = $admin['nama'];
        $_SESSION['user_role'] = 'admin';
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        echo json_encode([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'nama' => $admin['nama'],
                'role' => 'admin'
            ]
        ]);
        $conn->close();
        exit;
    } else {
        // [SECURITY] Increment rate limit saat gagal
        $_SESSION[$rate_key]['count']++;
        $_SESSION[$rate_key]['last_attempt'] = time();
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Username atau password salah']);
        $conn->close();
        exit;
    }
}

// ============================================================================
// AUTHENTICATION - USER (by email)
// ============================================================================

// [SECURITY] Cek user dengan prepared statement (user pakai email)
$stmt2 = $conn->prepare('SELECT id, nama, email, password, role FROM users WHERE email = ?');
$stmt2->bind_param('s', $email);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($result2->num_rows === 0) {
    // [SECURITY] Increment rate limit saat gagal
    $_SESSION[$rate_key]['count']++;
    $_SESSION[$rate_key]['last_attempt'] = time();
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Email atau password salah']);
    $conn->close();
    exit;
}

$user = $result2->fetch_assoc();

// [SECURITY] Verifikasi password
if (!password_verify($password, $user['password'])) {
    // [SECURITY] Increment rate limit saat gagal
    $_SESSION[$rate_key]['count']++;
    $_SESSION[$rate_key]['last_attempt'] = time();
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Email atau password salah']);
    $conn->close();
    exit;
}

// [SECURITY] Regenerate session ID untuk mencegah session fixation
session_regenerate_id(true);

// Reset rate limit saat berhasil login
unset($_SESSION[$rate_key]);

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_nama'] = $user['nama'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['logged_in'] = true;
$_SESSION['login_time'] = time();

echo json_encode([
    'status' => 'success',
    'message' => 'Login berhasil',
    'data' => [
        'user_id' => $user['id'],
        'nama' => $user['nama'],
        'email' => $user['email'],
        'role' => $user['role']
    ]
]);

$conn->close();
?>
