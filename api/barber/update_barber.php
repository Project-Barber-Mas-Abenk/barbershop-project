<?php
/**
 * Update Barber API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat update barber
 * [SECURITY] Validasi input dan sanitasi
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
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin yang dapat mengupdate barber']);
    exit;
}

// [SECURITY] Validasi HTTP method
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// ============================================================================
// INPUT VALIDATION
// ============================================================================

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id']) || empty($data['nama'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID dan nama barber wajib diisi']);
    exit;
}

// [SECURITY] Sanitasi input
$id = filter_var($data['id'], FILTER_VALIDATE_INT);
$nama = htmlspecialchars(trim($data['nama']), ENT_QUOTES, 'UTF-8');

if ($id === false || $id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID barber tidak valid']);
    exit;
}

// [SECURITY] Validasi panjang nama
if (strlen($nama) < 2 || strlen($nama) > 100) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nama barber harus 2-100 karakter']);
    exit;
}

$status = !empty($data['status']) && in_array($data['status'], ['aktif', 'nonaktif']) 
    ? $data['status'] 
    : 'aktif';

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

// [SECURITY] Cek nama duplikat (kecuali untuk barber ini sendiri)
$check2 = $conn->prepare('SELECT id FROM barber WHERE nama = ? AND id != ?');
$check2->bind_param('si', $nama, $id);
$check2->execute();
if ($check2->get_result()->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Nama barber sudah digunakan oleh barber lain']);
    $conn->close();
    exit;
}
$check2->close();

// [SECURITY] Update dengan prepared statement
$stmt = $conn->prepare('UPDATE barber SET nama = ?, status = ? WHERE id = ?');
$stmt->bind_param('ssi', $nama, $status, $id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Barber berhasil diupdate',
        'data' => [
            'id' => $id,
            'nama' => $nama,
            'status' => $status
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate barber']);
}

$stmt->close();
$conn->close();
?>
