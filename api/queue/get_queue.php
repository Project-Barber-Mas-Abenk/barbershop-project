<?php
require_once '../config/config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$tanggal = !empty($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

$conn = getConnection();

$role = $_SESSION['user_role'] ?? 'guest';
$user_id = $_SESSION['user_id'] ?? 0;

$sql = '
    SELECT
        a.nomor_antrian AS nomor_antrian,
        pl.nama AS nama_pelanggan,
        pl.user_id AS pelanggan_user_id,
        l.nama AS nama_layanan,
        p.jam AS jam,
        p.status AS status_booking,
        py.status AS status_bayar
    FROM antrian a
    JOIN pemesanan p ON p.id = a.pemesanan_id
    JOIN pelanggan pl ON pl.id = p.pelanggan_id
    JOIN layanan l ON l.id = p.layanan_id
    LEFT JOIN pembayaran py ON py.pemesanan_id = p.id
    WHERE a.tanggal = ?
';

if ($role === 'user') {
    $sql .= ' AND pl.user_id = ?';
}

$sql .= ' ORDER BY a.nomor_antrian ASC';

$stmt = $conn->prepare($sql);

if ($role === 'user') {
    $stmt->bind_param('si', $tanggal, $user_id);
} else {
    $stmt->bind_param('s', $tanggal);
}

$stmt->execute();
$result = $stmt->get_result();

$antrian = [];
while ($row = $result->fetch_assoc()) {
    unset($row['pelanggan_user_id']);
    $antrian[] = $row;
}

$stmt_kuota = $conn->prepare('SELECT kuota_harian, kuota_saat_ini FROM kuota WHERE tanggal = ?');
$stmt_kuota->bind_param('s', $tanggal);
$stmt_kuota->execute();
$res_kuota = $stmt_kuota->get_result();

if ($res_kuota->num_rows > 0) {
    $kuota = $res_kuota->fetch_assoc();
} else {
    $kuota = [
        'kuota_harian' => KUOTA_HARIAN,
        'kuota_saat_ini' => 0
    ];
}

echo json_encode([
    'status' => 'success',
    'tanggal' => $tanggal,
    'kuota' => $kuota,
    'total' => count($antrian),
    'data' => $antrian
]);

$conn->close();
?>
