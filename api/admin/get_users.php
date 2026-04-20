<?php
/**
 * Get Users (Admin) API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat mengakses
 * [SECURITY] Endpoint untuk mengambil daftar semua user
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// AUTHENTICATION & AUTHORIZATION
// ============================================================================

if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$role = $_SESSION['user_role'] ?? '';
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin yang dapat mengakses']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$conn = getConnection();

// ============================================================================
// QUERY USERS
// ============================================================================

$sql = '
    SELECT 
        u.id, 
        u.email, 
        u.nama, 
        u.no_hp, 
        u.role, 
        u.google_id IS NOT NULL as is_google_account,
        u.created_at,
        (SELECT COUNT(*) FROM pemesanan p JOIN pelanggan pl ON p.pelanggan_id = pl.id WHERE pl.user_id = u.id) as total_booking
    FROM users u 
    ORDER BY u.created_at DESC
';

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        'id' => $row['id'],
        'email' => $row['email'],
        'nama' => $row['nama'],
        'no_hp' => $row['no_hp'],
        'role' => $row['role'],
        'is_google_account' => (bool)$row['is_google_account'],
        'created_at' => $row['created_at'],
        'total_booking' => $row['total_booking']
    ];
}

echo json_encode([
    'status' => 'success',
    'data' => $users
]);

$stmt->close();
$conn->close();
?>
