<?php
require_once '../config/config.php';

session_start();

if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$role = $_SESSION['user_role'] ?? '';
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin yang dapat mengubah status pembayaran']);
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

$status_valid = ['lunas', 'gagal'];

if (!in_array($status_baru, $status_valid)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Status pembayaran tidak valid. Pilih: lunas, gagal'
    ]);
    exit;
}

$conn = getConnection();

$cek = $conn->prepare('SELECT id, status FROM pembayaran WHERE pemesanan_id = ?');
$cek->bind_param('i', $pemesanan_id);
$cek->execute();
$res_cek = $cek->get_result();

if ($res_cek->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Data pembayaran tidak ditemukan']);
    $conn->close();
    exit;
}

$pembayaran = $res_cek->fetch_assoc();

if ($pembayaran['status'] === 'lunas') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Pembayaran sudah berstatus lunas, tidak bisa diubah lagi'
    ]);
    $conn->close();
    exit;
}

$stmt = $conn->prepare('UPDATE pembayaran SET status = ? WHERE pemesanan_id = ?');
$stmt->bind_param('si', $status_baru, $pemesanan_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate status pembayaran']);
    $conn->close();
    exit;
}

if ($status_baru === 'lunas') {
    $update_booking = $conn->prepare('UPDATE pemesanan SET status = "dikonfirmasi" WHERE id = ? AND status = "menunggu"');
    $update_booking->bind_param('i', $pemesanan_id);
    $update_booking->execute();
}

echo json_encode([
    'status' => 'success',
    'message' => 'Status pembayaran berhasil diupdate',
    'data' => [
        'pemesanan_id' => $pemesanan_id,
        'status_lama' => $pembayaran['status'],
        'status_baru' => $status_baru
    ]
]);

$conn->close();
?>
