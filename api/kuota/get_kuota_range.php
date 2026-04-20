<?php
/**
 * Get Kuota Range API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk mengambil data kuota dalam range tanggal
 */

require_once '../config/config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// [SECURITY] Validasi input
$start_date = isset($_GET['start']) ? htmlspecialchars(trim($_GET['start']), ENT_QUOTES, 'UTF-8') : date('Y-m-d');
$end_date = isset($_GET['end']) ? htmlspecialchars(trim($_GET['end']), ENT_QUOTES, 'UTF-8') : date('Y-m-d', strtotime('+30 days'));

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format tanggal tidak valid']);
    exit;
}

$conn = getConnection();

// [SECURITY] Query dengan prepared statement
$stmt = $conn->prepare('
    SELECT * FROM kuota 
    WHERE tanggal BETWEEN ? AND ? 
    ORDER BY tanggal ASC
');
$stmt->bind_param('ss', $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$kuota_list = [];
while ($row = $result->fetch_assoc()) {
    $sisa = $row['kuota_harian'] - $row['kuota_saat_ini'];
    $kuota_list[] = [
        'id' => $row['id'],
        'tanggal' => $row['tanggal'],
        'kuota_harian' => $row['kuota_harian'],
        'kuota_saat_ini' => $row['kuota_saat_ini'],
        'sisa_kuota' => $sisa,
        'tersedia' => $sisa > 0
    ];
}

echo json_encode([
    'status' => 'success',
    'data' => $kuota_list
]);

$stmt->close();
$conn->close();
?>
