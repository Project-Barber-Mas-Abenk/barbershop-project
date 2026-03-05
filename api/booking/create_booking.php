<?php
require_once '../config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$required = ['nama', 'no_hp', 'layanan_id', 'tanggal', 'jam', 'metode_bayar'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Field '$field' wajib diisi"]);
        exit;
    }
}

$nama = htmlspecialchars(trim($data['nama']));
$no_hp = htmlspecialchars($data['no_hp']);
$layanan_id = (int) $data['layanan_id'];
$tanggal = $data['tanggal'];
$jam = $data['jam'];
$metode_bayar = $data['metode_bayar'];
$user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$today = new DateTime();
$today->setTime(0, 0, 0);
$tgl_booking = new DateTime($tanggal);

if ($tgl_booking <= $today) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking harus minimal H-1 (satu hari sebelumnya)'
    ]);
    exit;
}

$conn = getConnection();

$stmt = $conn->prepare('SELECT id, kuota_harian, kuota_saat_ini FROM kuota WHERE tanggal = ?');
$stmt->bind_param('s', $tanggal);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $ins = $conn->prepare('INSERT INTO kuota (tanggal, kuota_harian, kuota_saat_ini) VALUES (?, ?, 0)');
    $ins->bind_param('si', $tanggal, KUOTA_HARIAN);
    $ins->execute();
    $kuota_harian = KUOTA_HARIAN;
    $kuota_saat_ini = 0;
} else {
    $kuota = $result->fetch_assoc();
    $kuota_harian = $kuota['kuota_harian'];
    $kuota_saat_ini = $kuota['kuota_saat_ini'];
}

if ($kuota_saat_ini >= $kuota_harian) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Kuota booking untuk tanggal ini sudah penuh'
    ]);
    $conn->close();
    exit;
}

$stmt_l = $conn->prepare('SELECT harga FROM layanan WHERE id = ?');
$stmt_l->bind_param('i', $layanan_id);
$stmt_l->execute();
$res_l = $stmt_l->get_result();

if ($res_l->num_rows === 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Layanan tidak ditemukan']);
    $conn->close();
    exit;
}
$harga = $res_l->fetch_assoc()['harga'];

$conn->begin_transaction();

try {
    $s1 = $conn->prepare('INSERT INTO pemesanan (user_id, nama_pelanggan, no_hp, layanan_id, tanggal, jam, status) VALUES (?, ?, ?, ?, ?, ?, "menunggu")');
    $s1->bind_param('ississ', $user_id, $nama, $no_hp, $layanan_id, $tanggal, $jam);
    $s1->execute();
    $pemesanan_id = $conn->insert_id;

    $s2 = $conn->prepare('INSERT INTO pembayaran (pemesanan_id, metode, status, jumlah) VALUES (?, ?, "menunggu", ?)');
    $s2->bind_param('isd', $pemesanan_id, $metode_bayar, $harga);
    $s2->execute();

    $nomor_antrian = $kuota_saat_ini + 1;
    $s3 = $conn->prepare('INSERT INTO antrian (pemesanan_id, nomor_antrian, tanggal) VALUES (?, ?, ?)');
    $s3->bind_param('iis', $pemesanan_id, $nomor_antrian, $tanggal);
    $s3->execute();

    $s4 = $conn->prepare('UPDATE kuota SET kuota_saat_ini = kuota_saat_ini + 1 WHERE tanggal = ?');
    $s4->bind_param('s', $tanggal);
    $s4->execute();

    $conn->commit();

    http_response_code(201);
    echo json_encode([
        'status' => 'success',
        'message' => 'Booking berhasil dibuat',
        'data' => [
            'pemesanan_id' => $pemesanan_id,
            'nomor_antrian' => $nomor_antrian,
            'tanggal' => $tanggal,
            'jam' => $jam
        ]
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking gagal: ' . $e->getMessage()
    ]);
}

$conn->close();
?>