<?php
/**
 * Get Kuota API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk mengambil data kuota
 */

require_once '../config/config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// [SECURITY] Validasi tanggal
$tanggal = isset($_GET['tanggal']) ? htmlspecialchars(trim($_GET['tanggal']), ENT_QUOTES, 'UTF-8') : date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format tanggal tidak valid']);
    exit;
}

$conn = getConnection();

// [SECURITY] Query dengan prepared statement
$stmt = $conn->prepare('SELECT * FROM kuota WHERE tanggal = ?');
$stmt->bind_param('s', $tanggal);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Return default kuota jika belum ada
    echo json_encode([
        'status' => 'success',
        'data' => [
            'tanggal' => $tanggal,
            'kuota_harian' => KUOTA_HARIAN,
            'kuota_saat_ini' => 0,
            'sisa_kuota' => KUOTA_HARIAN,
            'tersedia' => true
        ]
    ]);
} else {
    $kuota = $result->fetch_assoc();
    $sisa = $kuota['kuota_harian'] - $kuota['kuota_saat_ini'];
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'id' => $kuota['id'],
            'tanggal' => $kuota['tanggal'],
            'kuota_harian' => $kuota['kuota_harian'],
            'kuota_saat_ini' => $kuota['kuota_saat_ini'],
            'sisa_kuota' => $sisa,
            'tersedia' => $sisa > 0
        ]
    ]);
}

$stmt->close();
$conn->close();
?>
