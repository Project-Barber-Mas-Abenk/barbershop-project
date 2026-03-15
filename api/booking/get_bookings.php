<?php
/**
 * Get Bookings API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk mengambil daftar booking
 * [SECURITY] Role-based access control (admin dapat lihat semua, user hanya miliknya)
 * [SECURITY] Semua query menggunakan prepared statements
 * 
 * Query parameter:
 * - tanggal: filter berdasarkan tanggal (YYYY-MM-DD)
 * - status: filter berdasarkan status booking
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// AUTHENTICATION CHECK
// ============================================================================

// [SECURITY] Cek apakah user sudah login
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$conn = getConnection();

$role = $_SESSION['user_role'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

// ============================================================================
// QUERY BUILDING
// ============================================================================

$sql = '
SELECT
    p.id AS id,
    pl.nama AS nama_pelanggan,
    pl.no_hp AS no_hp,
    l.nama AS nama_layanan,
    COALESCE(b.nama, "Belum Ditentukan") AS nama_barber,
    p.tanggal AS tanggal,
    TIME_FORMAT(p.jam, "%H:%i") AS jam,
    p.status AS status_booking,
    py.metode AS metode_bayar,
    py.status AS status_bayar,
    l.harga AS harga,
    a.nomor_antrian AS nomor_antrian,
    p.created_at AS dibuat_pada
FROM pemesanan p
JOIN pelanggan pl ON p.pelanggan_id = pl.id
JOIN layanan l ON p.layanan_id = l.id
LEFT JOIN barber b ON p.barber_id = b.id
LEFT JOIN pembayaran py ON py.pemesanan_id = p.id
LEFT JOIN antrian a ON a.pemesanan_id = p.id
WHERE 1=1
';

$params = [];
$types = '';

// [SECURITY] Role-based filtering
if ($role === 'user') {
    $sql .= ' AND pl.user_id = ?';
    $params[] = $user_id;
    $types .= 'i';
}

// [SECURITY] Filter tanggal dengan validasi
if (!empty($_GET['tanggal'])) {
    $tanggal = htmlspecialchars(trim($_GET['tanggal']), ENT_QUOTES, 'UTF-8');
    // Validasi format tanggal
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $sql .= ' AND p.tanggal = ?';
        $params[] = $tanggal;
        $types .= 's';
    }
}

// [SECURITY] Filter status dengan whitelist
if (!empty($_GET['status'])) {
    $allowed_status = ['menunggu', 'dikonfirmasi', 'selesai', 'dibatalkan'];
    $status = htmlspecialchars(trim($_GET['status']), ENT_QUOTES, 'UTF-8');
    if (in_array($status, $allowed_status, true)) {
        $sql .= ' AND p.status = ?';
        $params[] = $status;
        $types .= 's';
    }
}

$sql .= ' ORDER BY p.created_at DESC';

// ============================================================================
// EXECUTE QUERY
// ============================================================================

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

// [BACKEND TO FRONTEND] Struktur data yang dikirim ke FE:
// {
//   "status": "success",
//   "total": <jumlah_booking>,
//   "data": [
//     {
//       "id": <pemesanan_id>,
//       "nama_pelanggan": <string>,
//       "no_hp": <string>,
//       "nama_layanan": <string>,
//       "nama_barber": <string>,
//       "tanggal": <YYYY-MM-DD>,
//       "jam": <HH:MM>,
//       "status_booking": <menunggu|dikonfirmasi|selesai|dibatalkan>,
//       "metode_bayar": <cash|transfer|qris>,
//       "status_bayar": <menunggu|lunas|gagal>,
//       "harga": <decimal>,
//       "nomor_antrian": <integer>,
//       "dibuat_pada": <datetime>
//     }
//   ]
// }

echo json_encode([
    'status' => 'success',
    'total' => count($bookings),
    'data' => $bookings
]);

$conn->close();
?>
