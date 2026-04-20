<?php
/**
 * Get Barbers API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk mengambil daftar barber
 * [SECURITY] Semua query menggunakan prepared statements
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// METHOD VALIDATION
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$conn = getConnection();

// ============================================================================
// QUERY DATA
// ============================================================================

// [SECURITY] Query dengan prepared statement
$stmt = $conn->prepare('SELECT id, nama, status, created_at FROM barber ORDER BY nama ASC');
$stmt->execute();
$result = $stmt->get_result();

$barbers = [];
while ($row = $result->fetch_assoc()) {
    $barbers[] = [
        'id' => $row['id'],
        'nama' => $row['nama'],
        'status' => $row['status'],
        'created_at' => $row['created_at']
    ];
}

$stmt->close();
$conn->close();

echo json_encode([
    'status' => 'success',
    'data' => $barbers
]);
?>
