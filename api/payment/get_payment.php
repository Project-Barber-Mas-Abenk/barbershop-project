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

$role = $_SESSION['user_role'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

$sql = '
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
        pl.nama AS nama_pelanggan,
        pl.user_id AS pelanggan_user_id,
        l.nama AS nama_layanan
    FROM pembayaran py
    JOIN pemesanan p ON p.id = py.pemesanan_id
    JOIN pelanggan pl ON pl.id = p.pelanggan_id
    JOIN layanan l ON l.id = p.layanan_id
    WHERE py.pemesanan_id = ?
';

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $pemesanan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Data pembayaran tidak ditemukan']);
    $conn->close();
    exit;
}

$data = $result->fetch_assoc();

if ($role === 'user' && $data['pelanggan_user_id'] != $user_id) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    $conn->close();
    exit;
}

unset($data['pelanggan_user_id']);

echo json_encode([
    'status' => 'success',
    'data' => $data
]);

$conn->close();
?>
