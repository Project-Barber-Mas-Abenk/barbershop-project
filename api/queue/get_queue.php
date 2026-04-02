<?php
/**
 * Get Queue API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk mengambil daftar antrian
 * [SECURITY] Role-based access control
 * [SECURITY] User hanya bisa melihat antrian miliknya sendiri
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// METHOD VALIDATION
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// ============================================================================
// INPUT VALIDATION
// ============================================================================

// [SECURITY] Validasi format tanggal
$tanggal_input = !empty($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$tanggal = htmlspecialchars(trim($tanggal_input), ENT_QUOTES, 'UTF-8');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    $tanggal = date('Y-m-d'); // Default ke hari ini jika format invalid
}

$conn = getConnection();

$role = $_SESSION['user_role'] ?? 'guest';
$user_id = $_SESSION['user_id'] ?? 0;

// ============================================================================
// QUERY BUILDING
// ============================================================================

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

// [SECURITY] Role-based filtering
if ($role === 'user') {
    $sql .= ' AND pl.user_id = ?';
}

$sql .= ' ORDER BY a.nomor_antrian ASC';

// ============================================================================
// EXECUTE QUERY
// ============================================================================

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
    // [SECURITY] Hapus sensitive data sebelum dikirim ke frontend
    unset($row['pelanggan_user_id']);
    $antrian[] = $row;
}

// [SECURITY] Ambil info kuota
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

// [BACKEND TO FRONTEND] Struktur data antrian:
// {
//   "status": "success",
//   "tanggal": <YYYY-MM-DD>,
//   "kuota": {
//     "kuota_harian": <integer>,
//     "kuota_saat_ini": <integer>
//   },
//   "total": <jumlah_antrian>,
//   "data": [
//     {
//       "nomor_antrian": <integer>,
//       "nama_pelanggan": <string>,
//       "nama_layanan": <string>,
//       "jam": <HH:MM:SS>,
//       "status_booking": <menunggu|dikonfirmasi|selesai|dibatalkan>,
//       "status_bayar": <menunggu|lunas|gagal>
//     }
//   ]
// }

echo json_encode([
    'status' => 'success',
    'tanggal' => $tanggal,
    'kuota' => $kuota,
    'total' => count($antrian),
    'data' => $antrian
]);

$conn->close();
?>
