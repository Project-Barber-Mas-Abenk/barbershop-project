<?php
/**
 * Update Booking Status API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat mengubah status booking
 * [SECURITY] Semua query menggunakan prepared statements
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// AUTHENTICATION & AUTHORIZATION
// ============================================================================

// [SECURITY] Cek autentikasi
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

// [SECURITY] Cek authorization - hanya admin
$role = $_SESSION['user_role'] ?? '';
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin yang dapat mengubah status']);
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

// [SECURITY] Validasi input wajib
if (empty($data['pemesanan_id']) || empty($data['status'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id dan status wajib diisi']);
    exit;
}

// [SECURITY] Sanitasi input
$pemesanan_id = filter_var($data['pemesanan_id'], FILTER_VALIDATE_INT);
$status_baru = htmlspecialchars(trim($data['status']), ENT_QUOTES, 'UTF-8');

if ($pemesanan_id === false || $pemesanan_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id tidak valid']);
    exit;
}

// [SECURITY] Validasi status yang diizinkan
$status_valid = ['dikonfirmasi', 'selesai', 'dibatalkan'];
if (!in_array($status_baru, $status_valid, true)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Status tidak valid. Pilih: dikonfirmasi, selesai, dibatalkan'
    ]);
    exit;
}

// ============================================================================
// DATABASE OPERATIONS
// ============================================================================

$conn = getConnection();

// [SECURITY] Fetch pemesanan dengan prepared statement - termasuk tanggal untuk update kuota
$cek = $conn->prepare('SELECT id, status, tanggal FROM pemesanan WHERE id = ?');
$cek->bind_param('i', $pemesanan_id);
$cek->execute();
$res_cek = $cek->get_result();

if ($res_cek->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Pemesanan tidak ditemukan']);
    $conn->close();
    exit;
}

$pemesanan = $res_cek->fetch_assoc();

// [SECURITY] Cek apakah status sudah final
if (in_array($pemesanan['status'], ['selesai', 'dibatalkan'], true)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking dengan status "' . $pemesanan['status'] . '" tidak bisa diubah lagi'
    ]);
    $conn->close();
    exit;
}

// Simpan tanggal untuk update kuota nanti
$tanggal_pemesanan = $pemesanan['tanggal'];

// ============================================================================
// TRANSACTION
// ============================================================================

$conn->begin_transaction();

try {
    // [SECURITY] Update status dengan prepared statement
    $stmt = $conn->prepare('UPDATE pemesanan SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status_baru, $pemesanan_id);
    $stmt->execute();

    if ($status_baru === 'dibatalkan') {
        // [SECURITY] Update kuota jika dibatalkan - prepared statement
        $stmt_k = $conn->prepare('UPDATE kuota SET kuota_saat_ini = kuota_saat_ini - 1 WHERE tanggal = ? AND kuota_saat_ini > 0');
        $stmt_k->bind_param('s', $tanggal_pemesanan);
        $stmt_k->execute();

        // [SECURITY] Update status pembayaran jika ada - prepared statement
        $stmt_p = $conn->prepare('UPDATE pembayaran SET status = "gagal" WHERE pemesanan_id = ?');
        $stmt_p->bind_param('i', $pemesanan_id);
        $stmt_p->execute();
        
    } else if ($status_baru === 'selesai') {
        // [SECURITY] Jika selesai, pastikan pembayaran lunas - prepared statement
        $stmt_p = $conn->prepare('UPDATE pembayaran SET status = "lunas" WHERE pemesanan_id = ? AND status = "menunggu"');
        $stmt_p->bind_param('i', $pemesanan_id);
        $stmt_p->execute();
    }

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Status booking berhasil diupdate',
        'data' => [
            'pemesanan_id' => $pemesanan_id,
            'status_lama' => $pemesanan['status'],
            'status_baru' => $status_baru
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("[BARBERSHOP SECURITY] Update status failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate status: Terjadi kesalahan sistem']);
}

$conn->close();
?>
