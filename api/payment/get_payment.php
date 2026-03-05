<?php
require_once '../config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

if (empty($_GET['pemesanan_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id wajib diisi']);
    exit;
}

$pemesanan_id = (int) $_GET['pemesanan_id'];

$conn = getConnection();

$stmt = $conn->prepare('
    SELECT
        py.id AS pembayaran_id,
        py.pemesanan_id AS pemesanan_id,
        py.metode AS metode_bayar,
        py.status AS status_bayar,
        py.jumlah AS jumlah,
        py.created_at AS dibuat_pada,
        p.tanggal AS tanggal_booking,
        p.jam AS jam_booking,
        p.status AS status_booking,
        p.nama_pelanggan,
        l.nama AS nama_layanan
    FROM pembayaran py
    JOIN pemesanan p ON p.id = py.pemesanan_id
    JOIN layanan l ON l.id = p.layanan_id
    WHERE py.pemesanan_id = ?
');
$stmt->bind_param('i', $pemesanan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Data pembayaran tidak ditemukan']);
    exit;
}

$data = $result->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'data' => $data
]);

$conn->close();
?>