<?php
/**
 * Create Layanan API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat menambah layanan
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
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin yang dapat menambah layanan']);
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

if (empty($data['nama']) || !isset($data['harga'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nama dan harga layanan wajib diisi']);
    exit;
}

$nama = htmlspecialchars(trim($data['nama']), ENT_QUOTES, 'UTF-8');
$harga = filter_var($data['harga'], FILTER_VALIDATE_FLOAT);

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

// [SECURITY] Cek nama layanan sudah ada
$check = $conn->prepare('SELECT id FROM layanan WHERE nama = ?');
$check->bind_param('s', $nama);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Nama layanan sudah terdaftar']);
    $conn->close();
    exit;
}
$check->close();

// [SECURITY] Insert dengan prepared statement
$stmt = $conn->prepare('INSERT INTO layanan (nama, harga) VALUES (?, ?)');
$stmt->bind_param('sd', $nama, $harga);

if ($stmt->execute()) {
    $layanan_id = $conn->insert_id;
    echo json_encode([
        'status' => 'success',
        'message' => 'Layanan berhasil ditambahkan',
        'data' => [
            'id' => $layanan_id,
            'nama' => $nama,
            'harga' => $harga
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan layanan']);
}

$stmt->close();
$conn->close();
?>
