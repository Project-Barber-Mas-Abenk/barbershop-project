<?php
/**
 * Delete User (Admin) API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat mengakses
 * [SECURITY] Cek apakah user masih punya booking aktif
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

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID user wajib diisi']);
    exit;
}

$user_id = filter_var($data['id'], FILTER_VALIDATE_INT);
if ($user_id === false || $user_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID user tidak valid']);
    exit;
}

// Prevent admin delete themselves
if ($user_id == $_SESSION['user_id'] && $role === 'admin') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Anda tidak dapat menghapus akun sendiri']);
    exit;
}

$conn = getConnection();

// [SECURITY] Cek user exists
$check = $conn->prepare('SELECT id FROM users WHERE id = ?');
$check->bind_param('i', $user_id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
    $conn->close();
    exit;
}
$check->close();

// [SECURITY] Cek apakah user punya booking aktif
$check_booking = $conn->prepare('
    SELECT COUNT(*) as total FROM pemesanan p 
    JOIN pelanggan pl ON p.pelanggan_id = pl.id 
    WHERE pl.user_id = ? AND p.status IN ("menunggu", "dikonfirmasi")
');
$check_booking->bind_param('i', $user_id);
$check_booking->execute();
$result = $check_booking->get_result()->fetch_assoc();
if ($result['total'] > 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error', 
        'message' => 'User tidak dapat dihapus karena masih memiliki booking aktif'
    ]);
    $conn->close();
    exit;
}
$check_booking->close();

// [SECURITY] Transaction untuk menghapus user dan pelanggan terkait
$conn->begin_transaction();

try {
    // Hapus pelanggan terlebih dahulu (foreign key constraint)
    $stmt1 = $conn->prepare('DELETE FROM pelanggan WHERE user_id = ?');
    $stmt1->bind_param('i', $user_id);
    $stmt1->execute();
    $stmt1->close();
    
    // Hapus user
    $stmt2 = $conn->prepare('DELETE FROM users WHERE id = ?');
    $stmt2->bind_param('i', $user_id);
    $stmt2->execute();
    $stmt2->close();
    
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'User berhasil dihapus'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus user']);
}

$conn->close();
?>
