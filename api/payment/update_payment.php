<?php
/**
 * Update Payment API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat mengubah status pembayaran
 * [SECURITY] Validasi status pembayaran yang diizinkan
 * [SECURITY] Update status booking terkait saat pembayaran berubah
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
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin yang dapat mengubah status pembayaran']);
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
$status_valid = ['lunas', 'gagal'];
if (!in_array($status_baru, $status_valid, true)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Status pembayaran tidak valid. Pilih: lunas, gagal'
    ]);
    exit;
}

// ============================================================================
// DATABASE OPERATIONS
// ============================================================================

$conn = getConnection();

// [SECURITY] Cek data pembayaran dengan prepared statement
$cek = $conn->prepare('SELECT id, status FROM pembayaran WHERE pemesanan_id = ?');
$cek->bind_param('i', $pemesanan_id);
$cek->execute();
$res_cek = $cek->get_result();

if ($res_cek->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Data pembayaran tidak ditemukan']);
    $conn->close();
    exit;
}

$pembayaran = $res_cek->fetch_assoc();

// [SECURITY] Cek apakah sudah lunas (tidak bisa diubah lagi)
if ($pembayaran['status'] === 'lunas') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Pembayaran sudah berstatus lunas, tidak bisa diubah lagi'
    ]);
    $conn->close();
    exit;
}

// ============================================================================
// TRANSACTION
// ============================================================================

$conn->begin_transaction();

try {
    // [CORE LOGIC] Update status pembayaran
    $stmt = $conn->prepare('UPDATE pembayaran SET status = ? WHERE pemesanan_id = ?');
    $stmt->bind_param('si', $status_baru, $pemesanan_id);
    $stmt->execute();

    if ($status_baru === 'lunas') {
        // [CORE LOGIC] Jika lunas, konfirmasi booking
        $update_booking = $conn->prepare('UPDATE pemesanan SET status = "dikonfirmasi" WHERE id = ? AND status = "menunggu"');
        $update_booking->bind_param('i', $pemesanan_id);
        $update_booking->execute();
        
    } else if ($status_baru === 'gagal') {
        // [CORE LOGIC] Jika pembayaran gagal, batalkan pesanan dan kembalikan kuota
        $stmt_b = $conn->prepare('SELECT tanggal FROM pemesanan WHERE id = ?');
        $stmt_b->bind_param('i', $pemesanan_id);
        $stmt_b->execute();
        $pesanan = $stmt_b->get_result()->fetch_assoc();

        if ($pesanan) {
            // Update status booking menjadi dibatalkan
            $stmt_upd = $conn->prepare('UPDATE pemesanan SET status = "dibatalkan" WHERE id = ?');
            $stmt_upd->bind_param('i', $pemesanan_id);
            $stmt_upd->execute();

            // Kembalikan kuota
            $stmt_k = $conn->prepare('UPDATE kuota SET kuota_saat_ini = kuota_saat_ini - 1 WHERE tanggal = ? AND kuota_saat_ini > 0');
            $stmt_k->bind_param('s', $pesanan['tanggal']);
            $stmt_k->execute();
        }
    }

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Status pembayaran berhasil diupdate',
        'data' => [
            'pemesanan_id' => $pemesanan_id,
            'status_lama' => $pembayaran['status'],
            'status_baru' => $status_baru
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("[BARBERSHOP SECURITY] Update payment failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate status pembayaran: Terjadi kesalahan sistem']);
}

$conn->close();
?>
