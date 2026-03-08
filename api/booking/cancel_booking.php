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

if (empty($data['pemesanan_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id wajib diisi']);
    exit;
}

$pemesanan_id = (int) $data['pemesanan_id'];
$alasan = !empty($data['alasan']) ? htmlspecialchars(trim($data['alasan'])) : '';

$conn = getConnection();

$role = $_SESSION['user_role'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

$cek_sql = 'SELECT p.id, p.tanggal, p.status FROM pemesanan p JOIN pelanggan pl ON p.pelanggan_id = pl.id WHERE p.id = ?';
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

if (in_array($pemesanan['status'], ['selesai', 'dibatalkan'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking sudah ' . $pemesanan['status'] . ', tidak bisa dibatalkan'
    ]);
    $conn->close();
    exit;
}

$tanggal = $pemesanan['tanggal'];

$conn->begin_transaction();

try {
    $stmt = $conn->prepare('UPDATE pemesanan SET status = "dibatalkan" WHERE id = ?');
    $stmt->bind_param('i', $pemesanan_id);
    $stmt->execute();

    $stmt2 = $conn->prepare('UPDATE pembayaran SET status = "gagal" WHERE pemesanan_id = ?');
    $stmt2->bind_param('i', $pemesanan_id);
    $stmt2->execute();

    $stmt3 = $conn->prepare('UPDATE kuota SET kuota_saat_ini = kuota_saat_ini - 1 WHERE tanggal = ? AND kuota_saat_ini > 0');
    $stmt3->bind_param('s', $tanggal);
    $stmt3->execute();

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Booking berhasil dibatalkan',
        'data' => [
            'pemesanan_id' => $pemesanan_id,
            'status' => 'dibatalkan'
        ]
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal membatalkan booking: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
