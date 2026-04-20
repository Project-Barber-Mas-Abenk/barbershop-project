<?php
/**
 * Delete Barber API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat menghapus barber
 * [SECURITY] Cek apakah barber masih memiliki booking aktif
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// AUTHENTICATION & AUTHORIZATION
// ============================================================================

// [SECURITY] Cek autentikasi
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

// [SECURITY] Cek authorization - hanya admin
$role = $_SESSION['user_role'] ?? '';
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin yang dapat menghapus barber']);
    exit;
}

// [SECURITY] Validasi HTTP method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// ============================================================================
// INPUT VALIDATION
// ============================================================================

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID barber wajib diisi']);
    exit;
}

// [SECURITY] Sanitasi input
$id = filter_var($data['id'], FILTER_VALIDATE_INT);

if ($id === false || $id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID barber tidak valid']);
    exit;
}

$conn = getConnection();

// [SECURITY] Cek barber exists
$check = $conn->prepare('SELECT id FROM barber WHERE id = ?');
$check->bind_param('i', $id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Barber tidak ditemukan']);
    $conn->close();
    exit;
}
$check->close();

// [SECURITY] Cek apakah barber masih memiliki booking aktif
$check_booking = $conn->prepare('
    SELECT COUNT(*) as total FROM pemesanan 
    WHERE barber_id = ? AND status IN ("menunggu", "dikonfirmasi")
');
$check_booking->bind_param('i', $id);
$check_booking->execute();
$result = $check_booking->get_result()->fetch_assoc();
if ($result['total'] > 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Barber tidak dapat dihapus karena masih memiliki booking aktif'
    ]);
    $conn->close();
    exit;
}
$check_booking->close();

// [SECURITY] Delete dengan prepared statement
$stmt = $conn->prepare('DELETE FROM barber WHERE id = ?');
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Barber berhasil dihapus'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus barber']);
}

$stmt->close();
$conn->close();
?>
