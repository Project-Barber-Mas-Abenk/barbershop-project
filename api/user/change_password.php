<?php
/**
 * Change Password API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk mengubah password
 * [SECURITY] Memerlukan password lama untuk verifikasi
 * [SECURITY] Password di-hash dengan bcrypt
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// AUTHENTICATION CHECK
// ============================================================================

if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// ============================================================================
// INPUT VALIDATION
// ============================================================================

if (empty($data['old_password']) || empty($data['new_password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password lama dan password baru wajib diisi']);
    exit;
}

$old_password = $data['old_password'];
$new_password = $data['new_password'];

// [SECURITY] Validasi panjang password baru
if (strlen($new_password) < 6) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password baru minimal 6 karakter']);
    exit;
}

$conn = getConnection();

$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['user_role'] ?? '';

// ============================================================================
// VERIFY OLD PASSWORD
// ============================================================================

if ($role === 'admin') {
    $admin_id = $_SESSION['admin_id'] ?? 0;
    $stmt = $conn->prepare('SELECT password FROM admin WHERE id = ?');
    $stmt->bind_param('i', $admin_id);
} else {
    $stmt = $conn->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->bind_param('i', $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// [SECURITY] Verifikasi password lama
if (!password_verify($old_password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Password lama tidak benar']);
    $conn->close();
    exit;
}

// ============================================================================
// UPDATE PASSWORD
// ============================================================================

$hash = password_hash($new_password, PASSWORD_BCRYPT);

if ($role === 'admin') {
    $stmt = $conn->prepare('UPDATE admin SET password = ? WHERE id = ?');
    $stmt->bind_param('si', $hash, $admin_id);
} else {
    $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmt->bind_param('si', $hash, $user_id);
}

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Password berhasil diubah'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengubah password']);
}

$stmt->close();
$conn->close();
?>
