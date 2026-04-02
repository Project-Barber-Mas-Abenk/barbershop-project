<?php
/**
 * Get Payment API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk mengambil detail pembayaran
 * [SECURITY] User hanya bisa melihat pembayaran miliknya sendiri
 * [SECURITY] Admin dapat melihat semua pembayaran
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

if (empty($_GET['pemesanan_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id wajib diisi']);
    exit;
}

// [SECURITY] Sanitasi input
$pemesanan_id = filter_var($_GET['pemesanan_id'], FILTER_VALIDATE_INT);

if ($pemesanan_id === false || $pemesanan_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id tidak valid']);
    exit;
}

$conn = getConnection();

$role = $_SESSION['user_role'] ?? 'guest';
$user_id = $_SESSION['user_id'] ?? 0;

// ============================================================================
// QUERY DATA
// ============================================================================

$sql = '
    SELECT
        py.id AS pembayaran_id,
        py.pemesanan_id AS pemesanan_id,
        py.metode AS metode_bayar,
        py.status AS status_bayar,
        py.jumlah AS jumlah,
        py.created_at AS dibuat_pada,
        p.tanggal AS tanggal_booking,
        p.jam AS jam_booking,
        p.status AS status_booking,
        pl.nama AS nama_pelanggan,
        pl.user_id AS pelanggan_user_id,
        l.nama AS nama_layanan
    FROM pembayaran py
    JOIN pemesanan p ON p.id = py.pemesanan_id
    JOIN pelanggan pl ON pl.id = p.pelanggan_id
    JOIN layanan l ON l.id = p.layanan_id
    WHERE py.pemesanan_id = ?
';

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $pemesanan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Data pembayaran tidak ditemukan']);
    $conn->close();
    exit;
}

$data = $result->fetch_assoc();

// [SECURITY] Authorization check - user hanya bisa lihat miliknya sendiri
if ($role === 'user' && $data['pelanggan_user_id'] != $user_id) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    $conn->close();
    exit;
}

// [SECURITY] Hapus sensitive data sebelum dikirim ke frontend
unset($data['pelanggan_user_id']);

// [BACKEND TO FRONTEND] Struktur data pembayaran:
// {
//   "status": "success",
//   "data": {
//     "pembayaran_id": <integer>,
//     "pemesanan_id": <integer>,
//     "metode_bayar": <cash|transfer|qris>,
//     "status_bayar": <menunggu|lunas|gagal>,
//     "jumlah": <decimal>,
//     "dibuat_pada": <datetime>,
//     "tanggal_booking": <YYYY-MM-DD>,
//     "jam_booking": <HH:MM:SS>,
//     "status_booking": <string>,
//     "nama_pelanggan": <string>,
//     "nama_layanan": <string>
//   }
// }

echo json_encode([
    'status' => 'success',
    'data' => $data
]);

$conn->close();
?>
