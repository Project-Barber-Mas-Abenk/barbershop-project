<?php
/**
 * Update Layanan API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat update layanan
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
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin yang dapat mengupdate layanan']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// ============================================================================
// INPUT VALIDATION
// ============================================================================

if (empty($data['id']) || empty($data['nama']) || !isset($data['harga'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID, nama, dan harga layanan wajib diisi']);
    exit;
}

$id = filter_var($data['id'], FILTER_VALIDATE_INT);
$nama = htmlspecialchars(trim($data['nama']), ENT_QUOTES, 'UTF-8');
$harga = filter_var($data['harga'], FILTER_VALIDATE_FLOAT);

if ($id === false || $id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID layanan tidak valid']);
    exit;
}

if (strlen($nama) < 2 || strlen($nama) > 100) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nama layanan harus 2-100 karakter']);
    exit;
}

if ($harga === false || $harga < 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Harga tidak valid']);
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

// [SECURITY] Cek nama duplikat
$check2 = $conn->prepare('SELECT id FROM layanan WHERE nama = ? AND id != ?');
$check2->bind_param('si', $nama, $id);
$check2->execute();
if ($check2->get_result()->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Nama layanan sudah digunakan']);
    $conn->close();
    exit;
}
$check2->close();

// [SECURITY] Update dengan prepared statement
$stmt = $conn->prepare('UPDATE layanan SET nama = ?, harga = ? WHERE id = ?');
$stmt->bind_param('sdi', $nama, $harga, $id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Layanan berhasil diupdate',
        'data' => [
            'id' => $id,
            'nama' => $nama,
            'harga' => $harga
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate layanan']);
}

$stmt->close();
$conn->close();
?>
