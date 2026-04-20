<?php
/**
 * Update User Profile API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk update profil user
 * [SECURITY] User hanya bisa update profil sendiri
 * [SECURITY] Validasi input lengkap
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

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// ============================================================================
// INPUT VALIDATION
// ============================================================================

if (empty($data['nama'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nama wajib diisi']);
    exit;
}

$nama = htmlspecialchars(trim($data['nama']), ENT_QUOTES, 'UTF-8');

if (strlen($nama) < 2 || strlen($nama) > 100) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nama harus 2-100 karakter']);
    exit;
}

$conn = getConnection();

$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['user_role'] ?? '';

// ============================================================================
// UPDATE PROFILE
// ============================================================================

if ($role === 'admin') {
    // Update admin
    $admin_id = $_SESSION['admin_id'] ?? 0;
    $stmt = $conn->prepare('UPDATE admin SET nama = ? WHERE id = ?');
    $stmt->bind_param('si', $nama, $admin_id);
} else {
    // Update user - bisa update no_hp juga
    $no_hp = isset($data['no_hp']) ? htmlspecialchars(trim($data['no_hp']), ENT_QUOTES, 'UTF-8') : '';
    
    if (!empty($no_hp) && !preg_match('/^[0-9\-\+\s]{10,15}$/', $no_hp)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Format nomor HP tidak valid']);
        $conn->close();
        exit;
    }
    
    // Update users table
    $stmt = $conn->prepare('UPDATE users SET nama = ?, no_hp = ? WHERE id = ?');
    $stmt->bind_param('ssi', $nama, $no_hp, $user_id);
    
    // Update pelanggan table juga
    $stmt2 = $conn->prepare('
        UPDATE pelanggan SET nama = ?, no_hp = ? WHERE user_id = ?
    ');
    $stmt2->bind_param('ssi', $nama, $no_hp, $user_id);
    $stmt2->execute();
    $stmt2->close();
    
    // Update session
    $_SESSION['user_nama'] = $nama;
}

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Profil berhasil diupdate',
        'data' => [
            'nama' => $nama
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate profil']);
}

$stmt->close();
$conn->close();
?>
