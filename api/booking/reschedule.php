<?php
/**
 * Reschedule Booking API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk merubah jadwal booking
 * [SECURITY] Hanya pemilik booking atau admin yang dapat reschedule
 * [SECURITY] Validasi H-1 untuk tanggal baru
 * [SECURITY] Validasi kuota untuk tanggal baru
 * [SECURITY] Kuota lama dikembalikan, kuota baru dipakai
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

if (empty($data['pemesanan_id']) || empty($data['tanggal_baru']) || empty($data['jam_baru'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id, tanggal_baru, dan jam_baru wajib diisi']);
    exit;
}

// [SECURITY] Sanitasi dan validasi input
$pemesanan_id = filter_var($data['pemesanan_id'], FILTER_VALIDATE_INT);
$tanggal_baru = htmlspecialchars(trim($data['tanggal_baru']), ENT_QUOTES, 'UTF-8');
$jam_baru = htmlspecialchars(trim($data['jam_baru']), ENT_QUOTES, 'UTF-8');

if ($pemesanan_id === false || $pemesanan_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id tidak valid']);
    exit;
}

// [SECURITY] Validasi format tanggal (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_baru)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD']);
    exit;
}

// [SECURITY] Validasi format jam (HH:MM)
if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $jam_baru)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format jam tidak valid. Gunakan format HH:MM']);
    exit;
}

// ============================================================================
// H-1 VALIDATION untuk tanggal baru
// ============================================================================

$today = new DateTime();
$today->setTime(0, 0, 0);
$tgl_baru = new DateTime($tanggal_baru);

// [SECURITY] Hitung selisih hari
$interval = $today->diff($tgl_baru);
$days_diff = (int)$interval->format('%r%a');

// [SECURITY] Tanggal baru harus minimal H-1
if ($days_diff < 1) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Tanggal baru harus minimal H-1 (satu hari ke depan)'
    ]);
    exit;
}

// [SECURITY] Batasi booking maksimal 30 hari ke depan
if ($days_diff > 30) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Reschedule maksimal 30 hari ke depan'
    ]);
    exit;
}

// ============================================================================
// DATABASE OPERATIONS
// ============================================================================

$conn = getConnection();

$role = $_SESSION['user_role'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

// [SECURITY] Cek kepemilikan booking
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

// [SECURITY] Cek apakah status sudah final
if (in_array($pemesanan['status'], ['selesai', 'dibatalkan'], true)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking dengan status "' . $pemesanan['status'] . '" tidak bisa direschedule'
    ]);
    $conn->close();
    exit;
}

// [SECURITY] Cek apakah tanggal dan jam baru sama dengan yang sekarang
if ($tanggal_baru === $tanggal_lama && $jam_baru === $pemesanan['jam']) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Tanggal dan jam baru sama dengan yang sekarang'
    ]);
    $conn->close();
    exit;
}

// [SECURITY] Cek kuota untuk tanggal baru
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

// [SECURITY] Cek apakah kuota tanggal baru masih tersedia
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

// ============================================================================
// TRANSACTION
// ============================================================================

$conn->begin_transaction();

try {
    // [CORE LOGIC] Update tanggal dan jam booking
    $s1 = $conn->prepare('UPDATE pemesanan SET tanggal = ?, jam = ? WHERE id = ?');
    $s1->bind_param('ssi', $tanggal_baru, $jam_baru, $pemesanan_id);
    $s1->execute();

    // [CORE LOGIC] Update antrian dengan nomor antrian baru
    $s2 = $conn->prepare('UPDATE antrian SET tanggal = ?, nomor_antrian = ? WHERE pemesanan_id = ?');
    $s2->bind_param('sii', $tanggal_baru, $nomor_antrian_baru, $pemesanan_id);
    $s2->execute();

    // [CORE LOGIC] Kembalikan kuota tanggal lama
    $s3 = $conn->prepare('UPDATE kuota SET kuota_saat_ini = kuota_saat_ini - 1 WHERE tanggal = ? AND kuota_saat_ini > 0');
    $s3->bind_param('s', $tanggal_lama);
    $s3->execute();

    // [CORE LOGIC] Tambah kuota tanggal baru
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
    error_log("[BARBERSHOP SECURITY] Reschedule failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Reschedule gagal: Terjadi kesalahan sistem'
    ]);
}

$conn->close();
?>
