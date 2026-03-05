<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$tanggal = !empty($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

$conn = getConnection();

$stmt = $conn->prepare('
    SELECT
        a.nomor_antrian AS nomor_antrian,
        p.nama_pelanggan AS nama_pelanggan,
        l.nama AS nama_layanan,
        p.jam AS jam,
        p.status AS status_booking,
        py.status AS status_bayar
    FROM antrian a
    JOIN pemesanan p ON p.id = a.pemesanan_id
    JOIN layanan l ON l.id = p.layanan_id
    LEFT JOIN pembayaran py ON py.pemesanan_id = p.id
    WHERE a.tanggal = ?
    ORDER BY a.nomor_antrian ASC
');

$stmt->bind_param('s', $tanggal);
$stmt->execute();
$result = $stmt->get_result();

$antrian = [];
while ($row = $result->fetch_assoc()) {
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