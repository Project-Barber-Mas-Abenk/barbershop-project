<?php
/**
 * Create Booking API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint untuk membuat booking baru
 * [SECURITY] Validasi H-1 (booking minimal 1 hari sebelumnya)
 * [SECURITY] Validasi kuota harian
 * [SECURITY] Transaksi database untuk integritas data
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// METHOD VALIDATION
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// ============================================================================
// INPUT PARSING
// ============================================================================

$data = json_decode(file_get_contents('php://input'), true);

// ============================================================================
// INPUT VALIDATION
// ============================================================================

$required = ['layanan_id', 'tanggal', 'jam', 'metode_bayar'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Field '$field' wajib diisi"]);
        exit;
    }
}

// [SECURITY] Sanitasi dan validasi input
$layanan_id = filter_var($data['layanan_id'], FILTER_VALIDATE_INT);
$tanggal = htmlspecialchars(trim($data['tanggal']), ENT_QUOTES, 'UTF-8');
$jam = htmlspecialchars(trim($data['jam']), ENT_QUOTES, 'UTF-8');
$metode_bayar = htmlspecialchars(trim($data['metode_bayar']), ENT_QUOTES, 'UTF-8');

// [SECURITY] Validasi layanan_id
if ($layanan_id === false || $layanan_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'layanan_id tidak valid']);
    exit;
}

// [SECURITY] Validasi format tanggal (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD']);
    exit;
}

// [SECURITY] Validasi format jam (HH:MM)
if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $jam)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format jam tidak valid. Gunakan format HH:MM']);
    exit;
}

// [SECURITY] Validasi metode pembayaran
$metode_valid = ['cash', 'transfer', 'qris'];
if (!in_array($metode_bayar, $metode_valid, true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Metode pembayaran tidak valid. Pilih: cash, transfer, qris']);
    exit;
}

// ============================================================================
// H-1 VALIDATION (Booking minimal 1 hari sebelumnya)
// ============================================================================

$today = new DateTime();
$today->setTime(0, 0, 0);
$tgl_booking = new DateTime($tanggal);

// [SECURITY] Hitung selisih hari
$interval = $today->diff($tgl_booking);
$days_diff = (int)$interval->format('%r%a');

// [SECURITY] Booking harus minimal H-1 (satu hari sebelumnya)
// Artinya: tanggal booking harus > hari ini
if ($days_diff < 1) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking harus minimal H-1 (satu hari sebelumnya). Silakan pilih tanggal mulai besok.'
    ]);
    exit;
}

// [SECURITY] Batasi booking maksimal 30 hari ke depan
if ($days_diff > 30) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking maksimal 30 hari ke depan'
    ]);
    exit;
}

// ============================================================================
// DATABASE OPERATIONS
// ============================================================================

$conn = getConnection();

// [SECURITY] Ambil data user jika sudah login
$nama = '';
$no_hp = '';
$user_id = null;

if (!empty($_SESSION['logged_in']) && $_SESSION['user_role'] === 'user') {
    $user_id = $_SESSION['user_id'];
    $stmt_user = $conn->prepare('SELECT nama, no_hp FROM users WHERE id = ?');
    $stmt_user->bind_param('i', $user_id);
    $stmt_user->execute();
    $user_data = $stmt_user->get_result()->fetch_assoc();
    if ($user_data) {
        $nama = $user_data['nama'];
        $no_hp = $user_data['no_hp'];
    }
}

// [SECURITY] Validasi nama dan no_hp untuk guest atau user yang belum lengkap data
if (empty($nama) || empty($no_hp)) {
    if (empty($data['nama']) || empty($data['no_hp'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Nama dan no_hp wajib diisi']);
        $conn->close();
        exit;
    }
    $nama = htmlspecialchars(trim($data['nama']), ENT_QUOTES, 'UTF-8');
    $no_hp = htmlspecialchars(trim($data['no_hp']), ENT_QUOTES, 'UTF-8');
    
    // [SECURITY] Validasi format no_hp (minimal 10 digit, maksimal 15 digit)
    if (!preg_match('/^[0-9\-\+\s]{10,15}$/', $no_hp)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Format nomor HP tidak valid']);
        $conn->close();
        exit;
    }
}

// [SECURITY] Cek kuota dengan prepared statement
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

// [SECURITY] Cek apakah kuota masih tersedia
if ($kuota_saat_ini >= $kuota_harian) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Kuota booking untuk tanggal ini sudah penuh'
    ]);
    $conn->close();
    exit;
}

// [SECURITY] Ambil harga layanan
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

// [SECURITY] Default status
$status_booking = 'menunggu';
$status_bayar = 'menunggu';

// [SECURITY] Admin dapat set status khusus
if (!empty($_SESSION['logged_in']) && $_SESSION['user_role'] === 'admin') {
    if (!empty($data['status_booking'])) {
        $allowed_status = ['menunggu', 'dikonfirmasi', 'selesai', 'dibatalkan'];
        $input_status = htmlspecialchars(trim($data['status_booking']), ENT_QUOTES, 'UTF-8');
        if (in_array($input_status, $allowed_status, true)) {
            $status_booking = $input_status;
        }
    }
    if (!empty($data['status_bayar'])) {
        $allowed_payment = ['menunggu', 'lunas', 'gagal'];
        $input_payment = htmlspecialchars(trim($data['status_bayar']), ENT_QUOTES, 'UTF-8');
        if (in_array($input_payment, $allowed_payment, true)) {
            $status_bayar = $input_payment;
        }
    }
}

// ============================================================================
// TRANSACTION - Core Booking Logic
// ============================================================================

$conn->begin_transaction();

try {
    // [CORE BOOKING LOGIC #1] Insert pelanggan
    $s1 = $conn->prepare('INSERT INTO pelanggan (user_id, nama, no_hp) VALUES (?, ?, ?)');
    $s1->bind_param('iss', $user_id, $nama, $no_hp);
    $s1->execute();
    $pelanggan_id = $conn->insert_id;

    // [CORE BOOKING LOGIC #2] Insert pemesanan
    $s2 = $conn->prepare('INSERT INTO pemesanan (pelanggan_id, layanan_id, tanggal, jam, status) VALUES (?, ?, ?, ?, ?)');
    $s2->bind_param('iisss', $pelanggan_id, $layanan_id, $tanggal, $jam, $status_booking);
    $s2->execute();
    $pemesanan_id = $conn->insert_id;

    // [CORE BOOKING LOGIC #3] Insert pembayaran (status: menunggu)
    $s3 = $conn->prepare('INSERT INTO pembayaran (pemesanan_id, metode, status, jumlah) VALUES (?, ?, ?, ?)');
    $s3->bind_param('issd', $pemesanan_id, $metode_bayar, $status_bayar, $harga);
    $s3->execute();

    // [CORE BOOKING LOGIC #4] Insert antrian
    $nomor_antrian = $kuota_saat_ini + 1;
    $s4 = $conn->prepare('INSERT INTO antrian (pemesanan_id, nomor_antrian, tanggal) VALUES (?, ?, ?)');
    $s4->bind_param('iis', $pemesanan_id, $nomor_antrian, $tanggal);
    $s4->execute();

    // [CORE BOOKING LOGIC #5] Update kuota_saat_ini + 1
    $s5 = $conn->prepare('UPDATE kuota SET kuota_saat_ini = kuota_saat_ini + 1 WHERE tanggal = ?');
    $s5->bind_param('s', $tanggal);
    $s5->execute();

    $conn->commit();

    http_response_code(201);
    echo json_encode([
        'status' => 'success',
        'message' => 'Booking berhasil dibuat',
        'data' => [
            'pemesanan_id' => $pemesanan_id,
            'nomor_antrian' => $nomor_antrian,
            'tanggal' => $tanggal,
            'jam' => $jam,
            'status_booking' => $status_booking,
            'status_bayar' => $status_bayar
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("[BARBERSHOP SECURITY] Create booking failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking gagal: Terjadi kesalahan sistem'
    ]);
}

$conn->close();
?>
