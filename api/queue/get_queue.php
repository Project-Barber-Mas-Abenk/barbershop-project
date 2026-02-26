<?php
// ambil data antrian

// metod : GET
// URL : /api/queue/get_queue.pho

// query param optional:
// ?tanggal=2026-02-25 - ambil antrian tanggal tertentu
// kalau ngga diisi, default ke hari ini

// endpoint ini bisa diakses tanpa login
// karena antrian bisa ditampilin 

require_once '../config/config.php';

// hanya menerimma method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// kalo tanggal ga diisi, pakai tanggal hari ini
// format: YYYY-MM-DD
$tanggal = !empty($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

$conn = getConnection();
// ambil semua antrian untuk tanggal yang diminta
// join ke pemesanan dan pelanggan untuk dapat nama dan jam
// urutin berdasarkan nomor_antrian dari terkecil

$stmt = $conn->prepare('
    SELECT
        a.nomor_antrian  AS nomor_antrian,
        pl.nama          AS nama_pelanggan,
        l.nama           AS nama_layanan,
        p.jam            AS jam,
        p.status         AS status_booking,
        py.status        AS status_bayar
    FROM antrian a
    JOIN pemesanan  p  ON p.id  = a.pemesanan_id
    JOIN pelanggan  pl ON pl.id = p.pelanggan_id
    JOIN layanan    l  ON l.id  = p.layanan_id
    LEFT JOIN pembayaran py ON py.pemesanan_id = p.id
    WHERE a.tanggal = ?
    ORDER BY a.nomor_antrian ASC
');

$stmt->bind_param('s', $tanggal);
$stmt->execute();
$result = $stmt->get_result();

// kumpulkan semua data antrian dalam array
$antrian = [];
while ($row = $result->fetch_assoc()) {
    $antrian[] = $row;
}

// ambil juga info kouta buat tanggal ini
// berguna buat ditampilin di dashboard : "15 dari 20 slot terisi"
$stmt_kuota = $conn->prepare(
    'SELECT kuota_harian, kuota_saat_ini FROM kuota WHERE tanggal = ?'
);
$stmt_kuota->bind_param('s', $tanggal);
$stmt_kuota->execute();
$res_kuota = $stmt_kuota->get_result();

// kalo tanggal belum ada di tabel kouta, berarti belum ada booking sama sekali
if ($res_kuota->num_rows > 0) {
    $kuota = $res_kuota->fetch_assoc();
} else {
    $kuota = [
        'kuota_harian'   => KUOTA_HARIAN,
        'kuota_saat_ini' => 0
    ];
}

echo json_encode([
    'status'  => 'success',
    'tanggal' => $tanggal,
    'kuota'   => $kuota,
    'total'   => count($antrian),
    'data'    => $antrian
]);

$conn->close();

?>