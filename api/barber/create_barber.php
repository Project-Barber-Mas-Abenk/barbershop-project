<?php
/**
 * Create Barber API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat menambah barber
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
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin yang dapat menambah barber']);
    exit;
}

// [SECURITY] Validasi HTTP method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// ============================================================================
// INPUT VALIDATION
// ============================================================================

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['nama'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nama barber wajib diisi']);
    exit;
}

// [SECURITY] Sanitasi input
$nama = htmlspecialchars(trim($data['nama']), ENT_QUOTES, 'UTF-8');

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

// [SECURITY] Cek nama barber sudah ada
$check = $conn->prepare('SELECT id FROM barber WHERE nama = ?');
$check->bind_param('s', $nama);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Nama barber sudah terdaftar']);
    $conn->close();
    exit;
}
$check->close();

// [SECURITY] Insert dengan prepared statement
$stmt = $conn->prepare('INSERT INTO barber (nama, status) VALUES (?, ?)');
$stmt->bind_param('ss', $nama, $status);

if ($stmt->execute()) {
    $barber_id = $conn->insert_id;
    echo json_encode([
        'status' => 'success',
        'message' => 'Barber berhasil ditambahkan',
        'data' => [
            'id' => $barber_id,
            'nama' => $nama,
            'status' => $status
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan barber']);
}

$stmt->close();
$conn->close();
?>
