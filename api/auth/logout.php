<?php
/**
 * Logout API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint logout dengan proper session cleanup
 * [SECURITY] Cookie dihapus untuk mencegah session fixation
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// METHOD VALIDATION
// ============================================================================
/**
 * [SECURITY] Validasi method request
 * Hanya method POST yang diizinkan
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// ============================================================================
// SESSION CLEANUP
// ============================================================================
/**
 * [SECURITY] Cleanup session data
 * Hapus semua data session
 */
if (empty($_SESSION['logged_in'])) {
    echo json_encode(['status' => 'success', 'message' => 'Sudah tidak ada sesi aktif']);
    exit;
}

// [SECURITY] Clear semua session data
$_SESSION = [];

// [SECURITY] Hapus session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// [SECURITY] Destroy session
session_destroy();

echo json_encode([
    'status' => 'success',
    'message' => 'Logout berhasil'
]);
?>
