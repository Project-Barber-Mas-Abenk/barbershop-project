<?php
// METHOD: POST
// URL : /api/auth/logout.php
// ga butuh body JSON
// cukup kirim request POST ke endpoint ini
// dan session admin akan hapus

require_once '../config/config.php';

// session harus dimulai dulu sebelum bisa dihapus
session_start();

// hanya menerima method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// cek apakah admin memang ada session yang aktif
// kalo sudah gaada session, gaperlu logout lagi
if (empty($_SESSION['logged_in'])) {
    echo json_encode(['status' => 'success', 'message' => 'Sudah tidak ada sesi aktif']);
    exit;
}
// hapus semua isi session
// ini ngapus semua data yang terpimpen di $_SESSION
$_SESSION = [];

// hapus cookie session di browser pengguna
// kalo ini tidak dilakukan, cookie masih ada di browser
// walau data session di server udah diapus
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
// ancurin session di server
// ini yang bener bener ngapus file session di server
session_destroy();

echo json_encode([
    'status'  => 'success',
    'message' => 'Logout berhasil'
]);
?>