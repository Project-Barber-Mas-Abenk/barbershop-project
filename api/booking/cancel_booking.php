<?php
/**
 * Cancel Booking API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk membatalkan booking
 * [SECURITY] Hanya pemilik booking atau admin yang dapat membatalkan
 * [SECURITY] Booking yang sudah selesai/dibatalkan tidak dapat dibatalkan lagi
 * [SECURITY] Kuota dikembalikan saat pembatalan
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

// [SECURITY] Validasi HTTP method
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// ============================================================================
// INPUT VALIDATION
// ============================================================================

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['pemesanan_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id wajib diisi']);
    exit;
}

// [SECURITY] Sanitasi input
$pemesanan_id = filter_var($data['pemesanan_id'], FILTER_VALIDATE_INT);
$alasan = !empty($data['alasan']) ? htmlspecialchars(trim($data['alasan']), ENT_QUOTES, 'UTF-8') : '';

if ($pemesanan_id === false || $pemesanan_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id tidak valid']);
    exit;
}

$conn = getConnection();

$role = $_SESSION['user_role'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

// ============================================================================
// AUTHORIZATION CHECK
// ============================================================================

// [SECURITY] Cek kepemilikan booking dengan prepared statement
$cek_sql = 'SELECT p.id, p.tanggal, p.status FROM pemesanan p JOIN pelanggan pl ON p.pelanggan_id = pl.id WHERE p.id = ?';
$cek_params = [$pemesanan_id];
$cek_types = 'i';

// [SECURITY] Jika user biasa, hanya bisa lihat booking sendiri
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

// [SECURITY] Cek apakah status sudah final
if (in_array($pemesanan['status'], ['selesai', 'dibatalkan'], true)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking sudah ' . $pemesanan['status'] . ', tidak bisa dibatalkan'
    ]);
    $conn->close();
    exit;
}

$tanggal = $pemesanan['tanggal'];

// ============================================================================
// TRANSACTION
// ============================================================================

$conn->begin_transaction();

try {
    // [CORE LOGIC] Update status booking menjadi dibatalkan
    $stmt = $conn->prepare('UPDATE pemesanan SET status = "dibatalkan" WHERE id = ?');
    $stmt->bind_param('i', $pemesanan_id);
    $stmt->execute();

    // [CORE LOGIC] Update status pembayaran menjadi gagal
    $stmt2 = $conn->prepare('UPDATE pembayaran SET status = "gagal" WHERE pemesanan_id = ?');
    $stmt2->bind_param('i', $pemesanan_id);
    $stmt2->execute();

    // [CORE LOGIC] Kembalikan kuota (kurangi kuota_saat_ini)
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
    error_log("[BARBERSHOP SECURITY] Cancel booking failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal membatalkan booking: Terjadi kesalahan sistem'
    ]);
}

$conn->close();
?>
