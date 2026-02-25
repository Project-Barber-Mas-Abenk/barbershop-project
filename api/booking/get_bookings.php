<?php
// METHOD : GET
// URL : api/booking/get_bookings.php
// proteksi: cuma admin yang udah login

// query param opsional:
// ?tanggal=2026-02-25  - filter by tanggal
// ?status=menunggu     - filter by status booking

require_once '../config/config.php';

session_start();

// cek admin dah login belum
// kalo belum ada session, tolak dengan 401
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$conn = getConnection();

// query utama pakai JOIN ke beberapa tabel sekaligus
// pelanggan  - untuk nama & no HP
// layanan    - untuk nama service & harga
// pembayaran - untuk status & metode bayar
// antrian    - untuk nomor antrian hari itu
$sql = '
SELECT
    p.id            AS pemesanan_id,
    pl.nama         AS nama_pelanggan,
    pl.no_hp        AS no_hp,
    l.nama          AS nama_layanan,
    l.harga         AS harga,
    p.tanggal       AS tanggal,
    p.jam           AS jam,
    p.status        AS status_booking,
    py.metode       AS metode_bayar,
    py.status       AS status_bayar,
    a.nomor_antrian AS nomor_antrian,
    p.created_at    AS dibuat_pada
FROM pemesanan p
JOIN pelanggan       pl ON p.pelanggan_id = pl.id
JOIN layanan          l ON p.layanan_id   = l.id
LEFT JOIN pembayaran py ON py.pemesanan_id = p.id
LEFT JOIN antrian     a ON a.pemesanan_id  = p.id
WHERE 1=1
'; 

// siapin array buat nampung parameter filter dinamis
$params = [];
$types  = '';

// filter by tanggal jika query param ada
if (!empty($_GET['tanggal'])) {
    $sql     .= ' AND p.tanggal = ?';
    $params[] = $_GET['tanggal'];
    $types   .= 's';
}

// filter by status booking jika query param ada
if (!empty($_GET['status'])) {
    $sql     .= ' AND p.status = ?'; 
    $params[] = $_GET['status'];
    $types   .= 's';
}

// urut dari booking terbaru
$sql .= ' ORDER BY p.created_at DESC';

$stmt = $conn->prepare($sql);

// bind parameter hanya kalo ada filter yang aktif
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// kumpulkan semua baris ke dalam array php
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

echo json_encode([
    'status' => 'success',
    'total'  => count($bookings),
    'data'   => $bookings
]);

$conn->close(); 
?>