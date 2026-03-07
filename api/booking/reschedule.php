<?php
require_once '../config/config.php';

session_start();

if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['pemesanan_id']) || empty($data['tanggal_baru']) || empty($data['jam_baru'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id, tanggal_baru, dan jam_baru wajib diisi']);
    exit;
}

$pemesanan_id = (int) $data['pemesanan_id'];
$tanggal_baru = $data['tanggal_baru'];
$jam_baru = $data['jam_baru'];

$today = new DateTime();
$today->setTime(0, 0, 0);
$tgl_baru = new DateTime($tanggal_baru);

if ($tgl_baru <= $today) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Tanggal baru harus minimal H-1 (satu hari ke depan)'
    ]);
    exit;
}

$conn = getConnection();

$role = $_SESSION['user_role'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

$cek_sql = 'SELECT p.id, p.tanggal, p.jam, p.status FROM pemesanan p JOIN pelanggan pl ON p.pelanggan_id = pl.id WHERE p.id = ?';
$cek_params = [$pemesanan_id];
$cek_types = 'i';

if ($role === 'user') {
    $cek_sql .= ' AND pl.user_id = ?';
    $cek_params[] = $user_id;
    $cek_types .= 'i';
}

$cek = $conn->prepare($cek_sql);
$cek->bind_param($cek_types, ...$cek_params);
$cek->execute();
$res_cek = $cek->get_result();

if ($res_cek->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Pemesanan tidak ditemukan atau tidak memiliki akses']);
    $conn->close();
    exit;
}

$pemesanan = $res_cek->fetch_assoc();
$tanggal_lama = $pemesanan['tanggal'];

if (in_array($pemesanan['status'], ['selesai', 'dibatalkan'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking dengan status "' . $pemesanan['status'] . '" tidak bisa direschedule'
    ]);
    $conn->close();
    exit;
}

if ($tanggal_baru === $tanggal_lama && $jam_baru === $pemesanan['jam']) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Tanggal dan jam baru sama dengan yang sekarang'
    ]);
    $conn->close();
    exit;
}

$stmt_kuota = $conn->prepare('SELECT kuota_harian, kuota_saat_ini FROM kuota WHERE tanggal = ?');
$stmt_kuota->bind_param('s', $tanggal_baru);
$stmt_kuota->execute();
$res_kuota = $stmt_kuota->get_result();

if ($res_kuota->num_rows === 0) {
    $ins_kuota = $conn->prepare('INSERT INTO kuota (tanggal, kuota_harian, kuota_saat_ini) VALUES (?, ?, 0)');
    $ins_kuota->bind_param('si', $tanggal_baru, KUOTA_HARIAN);
    $ins_kuota->execute();
    $kuota_baru_harian = KUOTA_HARIAN;
    $kuota_baru_saat_ini = 0;
} else {
    $kuota_baru = $res_kuota->fetch_assoc();
    $kuota_baru_harian = $kuota_baru['kuota_harian'];
    $kuota_baru_saat_ini = $kuota_baru['kuota_saat_ini'];
}

if ($kuota_baru_saat_ini >= $kuota_baru_harian) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Kuota untuk tanggal baru sudah penuh'
    ]);
    $conn->close();
    exit;
}

$nomor_antrian_baru = $kuota_baru_saat_ini + 1;

$conn->begin_transaction();

try {
    $s1 = $conn->prepare('UPDATE pemesanan SET tanggal = ?, jam = ? WHERE id = ?');
    $s1->bind_param('ssi', $tanggal_baru, $jam_baru, $pemesanan_id);
    $s1->execute();

    $s2 = $conn->prepare('UPDATE antrian SET tanggal = ?, nomor_antrian = ? WHERE pemesanan_id = ?');
    $s2->bind_param('sii', $tanggal_baru, $nomor_antrian_baru, $pemesanan_id);
    $s2->execute();

    $s3 = $conn->prepare('UPDATE kuota SET kuota_saat_ini = kuota_saat_ini - 1 WHERE tanggal = ? AND kuota_saat_ini > 0');
    $s3->bind_param('s', $tanggal_lama);
    $s3->execute();

    $s4 = $conn->prepare('UPDATE kuota SET kuota_saat_ini = kuota_saat_ini + 1 WHERE tanggal = ?');
    $s4->bind_param('s', $tanggal_baru);
    $s4->execute();

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Jadwal booking berhasil diubah',
        'data' => [
            'pemesanan_id' => $pemesanan_id,
            'tanggal_lama' => $tanggal_lama,
            'jam_lama' => $pemesanan['jam'],
            'tanggal_baru' => $tanggal_baru,
            'jam_baru' => $jam_baru,
            'nomor_antrian_baru' => $nomor_antrian_baru
        ]
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Reschedule gagal: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
