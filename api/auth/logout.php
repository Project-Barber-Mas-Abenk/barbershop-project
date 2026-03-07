<?php
require_once '../config/config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

if (empty($_SESSION['logged_in'])) {
    echo json_encode(['status' => 'success', 'message' => 'Sudah tidak ada sesi aktif']);
    exit;
}

$_SESSION = [];

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

session_destroy();

echo json_encode([
    'status' => 'success',
    'message' => 'Logout berhasil'
]);
?>
