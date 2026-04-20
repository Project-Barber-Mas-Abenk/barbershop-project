<?php
/**
 * Delete Layanan API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat menghapus layanan
 * [SECURITY] Cek apakah layanan masih digunakan dalam booking
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
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin yang dapat menghapus layanan']);
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
    echo json_encode(['status' => 'error', 'message' => 'ID layanan wajib diisi']);
    exit;
}

$id = filter_var($data['id'], FILTER_VALIDATE_INT);
if ($id === false || $id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID layanan tidak valid']);
    exit;
}

$conn = getConnection();

// [SECURITY] Cek layanan exists
$check = $conn->prepare('SELECT id FROM layanan WHERE id = ?');
$check->bind_param('i', $id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Layanan tidak ditemukan']);
    $conn->close();
    exit;
}
$check->close();

// [SECURITY] Cek apakah layanan masih digunakan
$check_booking = $conn->prepare('SELECT COUNT(*) as total FROM pemesanan WHERE layanan_id = ?');
$check_booking->bind_param('i', $id);
$check_booking->execute();
$result = $check_booking->get_result()->fetch_assoc();
if ($result['total'] > 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Layanan tidak dapat dihapus karena masih digunakan dalam booking'
    ]);
    $conn->close();
    exit;
}
$check_booking->close();

// [SECURITY] Delete dengan prepared statement
$stmt = $conn->prepare('DELETE FROM layanan WHERE id = ?');
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Layanan berhasil dihapus'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus layanan']);
}

$stmt->close();
$conn->close();
?>
