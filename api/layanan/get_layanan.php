<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$conn = getConnection();

if (!empty($_GET['id'])) {
    $id = (int) $_GET['id'];

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

    echo json_encode([
        'status' => 'success',
        'data' => $layanan
    ]);
    $conn->close();
    exit;
}

$result = $conn->query('SELECT id, nama, harga FROM layanan ORDER BY harga ASC');

$layanan = [];
while ($row = $result->fetch_assoc()) {
    $layanan[] = $row;
}

echo json_encode([
    'status' => 'success',
    'total' => count($layanan),
    'data' => $layanan
]);

$conn->close();
?>