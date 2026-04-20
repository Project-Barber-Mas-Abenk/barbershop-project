<?php
/**
 * Update User (Admin) API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat mengakses
 * [SECURITY] Endpoint untuk update user oleh admin
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

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// ============================================================================
// INPUT VALIDATION
// ============================================================================

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

$nama = isset($data['nama']) ? htmlspecialchars(trim($data['nama']), ENT_QUOTES, 'UTF-8') : null;
$no_hp = isset($data['no_hp']) ? htmlspecialchars(trim($data['no_hp']), ENT_QUOTES, 'UTF-8') : null;
$role_update = isset($data['role']) && in_array($data['role'], ['user', 'admin']) ? $data['role'] : null;

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

// Build update query
$updates = [];
$params = [];
$types = '';

if ($nama !== null) {
    $updates[] = 'nama = ?';
    $params[] = $nama;
    $types .= 's';
}
if ($no_hp !== null) {
    $updates[] = 'no_hp = ?';
    $params[] = $no_hp;
    $types .= 's';
}
if ($role_update !== null) {
    $updates[] = 'role = ?';
    $params[] = $role_update;
    $types .= 's';
}

if (empty($updates)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada data yang diupdate']);
    $conn->close();
    exit;
}

$params[] = $user_id;
$types .= 'i';

$sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'User berhasil diupdate'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate user']);
}

$stmt->close();
$conn->close();
?>
