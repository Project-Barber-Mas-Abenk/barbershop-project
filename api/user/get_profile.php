<?php
/**
 * Get User Profile API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk mengambil profil user
 * [SECURITY] User hanya bisa lihat profil sendiri
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$conn = getConnection();

$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['user_role'] ?? '';

// ============================================================================
// QUERY PROFILE
// ============================================================================

if ($role === 'admin') {
    // Admin profile
    $stmt = $conn->prepare('SELECT id, nama, username, created_at FROM admin WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['admin_id']);
} else {
    // User profile
    $stmt = $conn->prepare('
        SELECT u.id, u.email, u.nama, u.no_hp, u.google_id, u.created_at,
               (SELECT COUNT(*) FROM pemesanan p JOIN pelanggan pl ON p.pelanggan_id = pl.id WHERE pl.user_id = u.id) as total_booking
        FROM users u 
        WHERE u.id = ?
    ');
    $stmt->bind_param('i', $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Profil tidak ditemukan']);
    $conn->close();
    exit;
}

$profile = $result->fetch_assoc();

// [SECURITY] Hapus sensitive data
if (isset($profile['google_id'])) {
    unset($profile['google_id']);
}

echo json_encode([
    'status' => 'success',
    'data' => $profile
]);

$stmt->close();
$conn->close();
?>
