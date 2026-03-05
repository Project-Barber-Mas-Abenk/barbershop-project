<?php
require_once '../config/config.php';
session_start();

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin yang dapat mengubah status']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['pemesanan_id']) || empty($data['status'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id dan status wajib diisi']);
    exit;
}

$pemesanan_id = (int) $data['pemesanan_id'];
$status_baru = $data['status'];

$status_valid = ['dikonfirmasi', 'selesai', 'dibatalkan'];

if (!in_array($status_baru, $status_valid)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Status tidak valid. Pilih: dikonfirmasi, selesai, dibatalkan'
    ]);
    exit;
}

$conn = getConnection();

$cek = $conn->prepare('SELECT id, status FROM pemesanan WHERE id = ?');
$cek->bind_param('i', $pemesanan_id);
$cek->execute();
$res_cek = $cek->get_result();

if ($res_cek->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Pemesanan tidak ditemukan']);
    $conn->close();
    exit;
}

$pemesanan = $res_cek->fetch_assoc();

if (in_array($pemesanan['status'], ['selesai', 'dibatalkan'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking dengan status "' . $pemesanan['status'] . '" tidak bisa diubah lagi'
    ]);
    $conn->close();
    exit;
}

$stmt = $conn->prepare('UPDATE pemesanan SET status = ? WHERE id = ?');
$stmt->bind_param('si', $status_baru, $pemesanan_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate status']);
    $conn->close();
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => 'Status booking berhasil diupdate',
    'data' => [
        'pemesanan_id' => $pemesanan_id,
        'status_lama' => $pemesanan['status'],
        'status_baru' => $status_baru
    ]
]);

$conn->close();
?>