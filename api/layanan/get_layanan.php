<?php
// METHOD: GET
// URL : /api/layanan/get_layanan.php

// ga butuh login karena endpint ini dipake
// oleh from booking pelanggan buat ngisi dropdown
// pilihan layanan yang tersedia di babershop

// query param optional:
// ?id=1 - kalo mau ambil satu layanan spesifik
// kalo ngga diisi, ambil semua layanan

require_once '../config/config.php';

// cuma menerima method GET
if($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$conn = getConnection();

// kalo ada query param ?id=, ambil satu layanan saja
// ini berguna kalo FE butuh detai satu layanan spesifik
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

// kalo ga ada query param id, ambil semua layanan
// diurutin berdasarkan harga dari yang termurah
$result = $conn->query('SELECT id, nama, harga FROM layanan ORDER BY harga ASC');

$layanan = [];
while ($row = $result->fetch_assoc()) {
    $layanan[] = $row;
}

// kalo tabel layanan kosong, tetep return success
// tapi dengan array data kosong supaya FE ble error
echo json_encode([
    'status' => 'success',
    'total' => count($layanan),
    'data' => $layanan
]);

$conn->close();
?>