<?php
// METHOD: POST
// UR: /api/booking/reschedule.php

// Body JSON:
// pemesanan_id - ID pemesanan yang mau direschedule
// tanggal - tanggal baru (YYYY-MM-DD)
// jam_baru - jam baru (HH:MM)
// proteksi: hanya admin yang sudah login
// Alur kerja file ini:
// 1. validasi input dan cek booking ada
// 2. pastikan booking masih boleh direschedule
// 3. cek kuota di tanggal baru
// 4. kurangi kouta di tanggal lama
// 5. update tanggal dan jam di tabel pemesanan
// 6. update nomor antrian di tabel antrian
// 7. tambah kuota di tanggal baru
// semua dalam satu transaksi supaya ga setengah-setengah

require_once '../config/config.php';

session_start();

// cek admin udah login
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

// hanya menerima method PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// ambil data JSON dari body request
$data = json_decode(file_get_contents('php://input'), true);

// validasi semua field wajib ada
if (empty($data['pemesanan_id']) || empty($data['tanggal_baru']) || empty($data['jam_baru'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'pemesanan_id, tanggal_baru, dan jam_baru wajib diisi']);
    exit;
}

$pemesanan_id = (int) $data['pemesanan_id'];
$tanggal_baru = $data['tanggal_baru'];
$jam_baru     = $data['jam_baru'];

// validasi H-1: tanggal baru juga harus minimal besok
$today       = new DateTime();
$today->setTime(0, 0, 0);
$tgl_baru    = new DateTime($tanggal_baru);

if ($tgl_baru <= $today) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Tanggal baru harus minimal H-1 (satu hari ke depan)'
    ]);
    exit;
}

$conn = getConnection();

// ambil data booking lama beserta tanggal lainnya
// sata butuh tanggal lama buat mengurangi kuota di tanggal itu
$cek = $conn->prepare('
    SELECT id, tanggal, jam, status FROM pemesanan WHERE id = ?
');
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
$tanggal_lama = $pemesanan['tanggal'];

// booking yang sudah selesai atau dibatalin ga bisa direschedule
if (in_array($pemesanan['status'], ['selesai', 'dibatalkan'])) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Booking dengan status "' . $pemesanan['status'] . '" tidak bisa direschedule'
    ]);
    $conn->close();
    exit;
}

// kalo tanggal baru sama dengan tanggal lama, ga perlu reschedule
if ($tanggal_baru === $tanggal_lama && $jam_baru === $pemesanan['jam']) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Tanggal dan jam baru sama dengan yang sekarang'
    ]);
    $conn->close();
    exit;
}

// cek kouta di tanggal baru
$stmt_kuota = $conn->prepare(
    'SELECT kuota_harian, kuota_saat_ini FROM kuota WHERE tanggal = ?'
);
$stmt_kuota->bind_param('s', $tanggal_baru);
$stmt_kuota->execute();
$res_kuota = $stmt_kuota->get_result();

if ($res_kuota->num_rows === 0) {
    // tanggal baru belum ada di tabel kuota, berarti masih kosong
    // buat entry baru untuk tanggal baru tersebut
    $ins_kuota = $conn->prepare(
        'INSERT INTO kuota (tanggal, kuota_harian, kuota_saat_ini) VALUES (?, ?, 0)'
    );
    $ins_kuota->bind_param('si', $tanggal_baru, KUOTA_HARIAN);
    $ins_kuota->execute();
    $kuota_baru_harian   = KUOTA_HARIAN;
    $kuota_baru_saat_ini = 0;
} else {
    $kuota_baru          = $res_kuota->fetch_assoc();
    $kuota_baru_harian   = $kuota_baru['kuota_harian'];
    $kuota_baru_saat_ini = $kuota_baru['kuota_saat_ini'];
}

// cek apakah kuota di tanggal baru masih tersedia
if ($kuota_baru_saat_ini >= $kuota_baru_harian) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Kuota untuk tanggal baru sudah penuh'
    ]);
    $conn->close();
    exit;
}

// hitung nomor antrian di tanggal baru
// nomor antrian = jumlah booking yang sudah ada di tanggal baru + 1
$nomor_antrian_baru = $kuota_baru_saat_ini + 1;

// mulai transaksi karena ada banyak tabel yang harus diupdate sekaligus
$conn->begin_transaction();

try {
    // STEP 1: Update tanggal dan jam di tabel pemesanan
    $s1 = $conn->prepare('UPDATE pemesanan SET tanggal = ?, jam = ? WHERE id = ?');
    $s1->bind_param('ssi', $tanggal_baru, $jam_baru, $pemesanan_id);
    $s1->execute();

    // STEP 2: Update nomor antrian dan tanggal di tabel antrian
    $s2 = $conn->prepare(
        'UPDATE antrian SET tanggal = ?, nomor_antrian = ? WHERE pemesanan_id = ?'
    );
    $s2->bind_param('sii', $tanggal_baru, $nomor_antrian_baru, $pemesanan_id);
    $s2->execute();

    // STEP 3: Kurangi kuota di tanggal lama
    // karena slot di tanggal lama sudah dikosongkan kembali
    $s3 = $conn->prepare(
        'UPDATE kuota SET kuota_saat_ini = kuota_saat_ini - 1 WHERE tanggal = ? AND kuota_saat_ini > 0'
    );
    $s3->bind_param('s', $tanggal_lama);
    $s3->execute();

    // STEP 4: Tambah kuota di tanggal baru
    // karena slot di tanggal baru sekarang terisi
    $s4 = $conn->prepare(
        'UPDATE kuota SET kuota_saat_ini = kuota_saat_ini + 1 WHERE tanggal = ?'
    );
    $s4->bind_param('s', $tanggal_baru);
    $s4->execute();

    // semua berhasil, commit transaksi
    $conn->commit();

    echo json_encode([
        'status'  => 'success',
        'message' => 'Jadwal booking berhasil diubah',
        'data'    => [
            'pemesanan_id'       => $pemesanan_id,
            'tanggal_lama'       => $tanggal_lama,
            'jam_lama'           => $pemesanan['jam'],
            'tanggal_baru'       => $tanggal_baru,
            'jam_baru'           => $jam_baru,
            'nomor_antrian_baru' => $nomor_antrian_baru
        ]
    ]);

} catch (Exception $e) {
    // kalau ada yang gagal, batalkan semua perubahan
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Reschedule gagal: ' . $e->getMessage()
    ]);
}

$conn->close();
?>