<?php
// METHOD: GET
// URL: /api/payment/het_payment.php

// query param wajib :
// ?pemesanan_id - ID pemesanan yang mau dicek pembayarannya
// endpoint ini bisa diakses pelanggan maupun admin
// karena pelanggan perlu tau status bayar mereka sendiri

require_once '../config/config.php';

// hanya menerima method POST
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
} 

// pemesanan_id wajib ada di query param
if (empty($_GET['pemesanan_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id wajib disi']);
    exit;
}

$pemesanan_id = (int) $_GET['pemesanan_id'];

$conn = getConnection();

// ambil data pembayaran sekaligus join ke pemesanan dan pelanggan
// supaya responsenya lengkap dan FE ga perlu request dua kali
$stmt = $conn->prepare('
    SELECT
        py.id            AS pembayaran_id,
        py.pemesanan_id  AS pemesanan_id,
        py.metode        AS metode_bayar,
        py.status        AS status_bayar,
        py.jumlah        AS jumlah,
        py.created_at    AS dibuat_pada,
        p.tanggal        AS tanggal_booking,
        p.jam            AS jam_booking,
        p.status         AS status_booking,
        pl.nama          AS nama_pelanggan,
        l.nama           AS nama_layanan
    FROM pembayaran py
    JOIN pemesanan  p  ON p.id  = py.pemesanan_id
    JOIN pelanggan  pl ON pl.id = p.pelanggan_id
    JOIN layanan    l  ON l.id  = p.layanan_id
    WHERE py.pemesanan_id = ?
');
$stmt->bind_param('i', $pemesanan_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
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