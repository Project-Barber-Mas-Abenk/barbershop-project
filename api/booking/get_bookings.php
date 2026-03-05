<?php
require_once '../config/config.php';
session_start();

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$conn = getConnection();

$sql = '
SELECT
    p.id AS pemesanan_id,
    p.nama_pelanggan,
    p.no_hp,
    l.nama AS nama_layanan,
    l.harga AS harga,
    p.tanggal AS tanggal,
    p.jam AS jam,
    p.status AS status_booking,
    py.metode AS metode_bayar,
    py.status AS status_bayar,
    a.nomor_antrian AS nomor_antrian,
    p.created_at AS dibuat_pada
FROM pemesanan p
JOIN layanan l ON p.layanan_id = l.id
LEFT JOIN pembayaran py ON py.pemesanan_id = p.id
LEFT JOIN antrian a ON a.pemesanan_id = p.id
WHERE 1=1
';

$params = [];
$types = '';

if (!empty($_GET['tanggal'])) {
    $sql .= ' AND p.tanggal = ?';
    $params[] = $_GET['tanggal'];
    $types .= 's';
}

if (!empty($_GET['status'])) {
    $sql .= ' AND p.status = ?';
    $params[] = $_GET['status'];
    $types .= 's';
}

if ($_SESSION['user_role'] === 'user' && !empty($_SESSION['user_id'])) {
    $sql .= ' AND p.user_id = ?';
    $params[] = $_SESSION['user_id'];
    $types .= 'i';
}

$sql .= ' ORDER BY p.created_at DESC';

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

echo json_encode([
    'status' => 'success',
    'total' => count($bookings),
    'data' => $bookings
]);

$conn->close();
?>