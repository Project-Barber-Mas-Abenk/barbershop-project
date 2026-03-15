<?php
/**
 * Get Layanan API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint publik untuk mengambil daftar layanan
 * [SECURITY] Tidak memerlukan autentikasi (public endpoint)
 * [SECURITY] Input validation untuk ID parameter
 */

require_once '../config/config.php';

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
// SINGLE LAYANAN (by ID)
// ============================================================================

if (!empty($_GET['id'])) {
    // [SECURITY] Validasi ID
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    
    if ($id === false || $id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID layanan tidak valid']);
        $conn->close();
        exit;
    }

    $stmt = $conn->prepare('SELECT id, nama, harga FROM layanan WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Layanan tidak ditemukan']);
        $conn->close();
        exit;
    }

    $layanan = $result->fetch_assoc();

    // [BACKEND TO FRONTEND] Struktur single layanan:
    // {
    //   "status": "success",
    //   "data": {
    //     "id": <integer>,
    //     "nama": <string>,
    //     "harga": <decimal>
    //   }
    // }

    echo json_encode([
        'status' => 'success',
        'data' => $layanan
    ]);
    $conn->close();
    exit;
}

// ============================================================================
// ALL LAYANAN
// ============================================================================

$result = $conn->query('SELECT id, nama, harga FROM layanan ORDER BY harga ASC');

$layanan = [];
while ($row = $result->fetch_assoc()) {
    $layanan[] = $row;
}

// [BACKEND TO FRONTEND] Struktur semua layanan:
// {
//   "status": "success",
//   "total": <jumlah_layanan>,
//   "data": [
//     {
//       "id": <integer>,
//       "nama": <string>,
//       "harga": <decimal>
//     }
//   ]
// }

echo json_encode([
    'status' => 'success',
    'total' => count($layanan),
    'data' => $layanan
]);

$conn->close();
?>
