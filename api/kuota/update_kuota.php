<?php
/**
 * Update Kuota API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat update kuota
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
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin yang dapat mengupdate kuota']);
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

if (empty($data['tanggal']) || empty($data['kuota_harian'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Tanggal dan kuota_harian wajib diisi']);
    exit;
}

$tanggal = htmlspecialchars(trim($data['tanggal']), ENT_QUOTES, 'UTF-8');
$kuota_harian = filter_var($data['kuota_harian'], FILTER_VALIDATE_INT);

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format tanggal tidak valid']);
    exit;
}

if ($kuota_harian === false || $kuota_harian < 1 || $kuota_harian > 100) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Kuota harian harus 1-100']);
    exit;
}

$conn = getConnection();

// [SECURITY] Cek apakah kuota sudah ada
$check = $conn->prepare('SELECT id, kuota_saat_ini FROM kuota WHERE tanggal = ?');
$check->bind_param('s', $tanggal);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Update existing kuota
    $existing = $result->fetch_assoc();
    
    // Validasi: kuota baru tidak boleh kurang dari yang sudah terpakai
    if ($kuota_harian < $existing['kuota_saat_ini']) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Kuota baru tidak boleh kurang dari yang sudah terpakai (' . $existing['kuota_saat_ini'] . ')'
        ]);
        $conn->close();
        exit;
    }
    
    $stmt = $conn->prepare('UPDATE kuota SET kuota_harian = ? WHERE tanggal = ?');
    $stmt->bind_param('is', $kuota_harian, $tanggal);
} else {
    // Insert new kuota
    $stmt = $conn->prepare('INSERT INTO kuota (tanggal, kuota_harian, kuota_saat_ini) VALUES (?, ?, 0)');
    $stmt->bind_param('si', $tanggal, $kuota_harian);
}

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Kuota berhasil diupdate',
        'data' => [
            'tanggal' => $tanggal,
            'kuota_harian' => $kuota_harian
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate kuota']);
}

$stmt->close();
$conn->close();
?>
