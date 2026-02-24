<?php
// buat endpoint booking baru
// url : /api/booking/create_booking.php
// body json yang dikirim fe (frontend):
// nama        - nama pelanggan
// no_hp       - nomor hp pelanggan
// layanan_id  - id layanan yang dipilih
// tanggal     - tanggal booking (yyyy-mm-dd)
// jam         - jam booking (hh:mm)
// metode_bayar - cash/transfer/qris

require_once '../config/config.php';

// cuma terima request make method POST
// method lain langsung ditolak
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// ambil data json dari body request
// fe harus kirim dengan content-type: application/json
$data = json_decode(file_get_contents('php://input'), true); 

// validasi input
$required = ['nama', 'no_hp', 'layanan_id', 'tanggal', 'jam', 'metode_bayar'];
foreach ($required as $field) {
    if (empty($data[$field])) { 
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Field '$field' wajib diisi"]);
        exit; 
    }
}

// sanitasi input buat nyegah XSS dan injection
$nama         = htmlspecialchars(trim($data['nama']));
$no_hp        = htmlspecialchars($data['no_hp']);
$layanan_id   = (int) $data['layanan_id'];    
$tanggal      = $data['tanggal'];             
$jam          = $data['jam'];
$metode_bayar = $data['metode_bayar'];

// validasi H-1
// booking harus minimal 1 hari sebelum tanggal
// ga boleh booking untuk hari ini atau kemarin
$today = new DateTime();
$today->setTime(0, 0, 0);
$tgl_booking = new DateTime($tanggal);

if ($tgl_booking <= $today) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Booking harus minimal H-1 (satu hari sebelumnya)'
    ]);
    exit;
}

// cek kuota hariannya
// kalo tanggalnya belum ada di tabel kuota,
// otomatis buat entry baru untuk tanggal itu
$conn = getConnection();

$stmt = $conn->prepare(
    'SELECT id, kuota_harian, kuota_saat_ini FROM kuota WHERE tanggal = ?'
);
$stmt->bind_param('s', $tanggal); 
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // tanggal belum ada di kuota, buat baris baru
    $ins = $conn->prepare('INSERT INTO kuota (tanggal, kuota_harian, kuota_saat_ini) VALUES (?, ?, 0)'); 
    $ins->bind_param('si', $tanggal, KUOTA_HARIAN);
    $ins->execute();
    $kuota_harian   = KUOTA_HARIAN;
    $kuota_saat_ini = 0;
} else {
    $kuota          = $result->fetch_assoc();
    $kuota_harian   = $kuota['kuota_harian'];
    $kuota_saat_ini = $kuota['kuota_saat_ini'];
}

// cek apa kuota masih ada
if ($kuota_saat_ini >= $kuota_harian) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Kuota booking untuk tanggal ini sudah penuh'
    ]);
    $conn->close();
    exit;
}

// ambil harga layanan untuk insert ke tabel pembayaran
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

// transaksi database
// semua INSERT di bawah harus berhasil semua
// kalo salah satu gagal, semua di-rollback
// supaya data ngga setengah-setengah
$conn->begin_transaction();

try {
    // STEP A: Insert data pelanggan
    $s1 = $conn->prepare('INSERT INTO pelanggan (nama, no_hp) VALUES (?, ?)'); 
    $s1->bind_param('ss', $nama, $no_hp);
    $s1->execute();
    $pelanggan_id = $conn->insert_id;

    // STEP B: Insert pemesanan. status awal: 'menunggu'
    $s2 = $conn->prepare(
        'INSERT INTO pemesanan (pelanggan_id, layanan_id, tanggal, jam, status) VALUES (?, ?, ?, ?, "menunggu")'
    );
    $s2->bind_param('iiss', $pelanggan_id, $layanan_id, $tanggal, $jam);
    $s2->execute(); 
    $pemesanan_id = $conn->insert_id;

    // STEP C: Insert pembayaran. status awal: 'menunggu'
    $s3 = $conn->prepare(
        'INSERT INTO pembayaran (pemesanan_id, metode, status, jumlah) VALUES (?, ?, "menunggu", ?)'
    );
    $s3->bind_param('isd', $pemesanan_id, $metode_bayar, $harga);
    $s3->execute();

    // STEP D: Insert ke antrian
    // nomor antrian = kuota yang terisi saat ini + 1
    $nomor_antrian = $kuota_saat_ini + 1;
    $s4 = $conn->prepare(
        'INSERT INTO antrian (pemesanan_id, nomor_antrian, tanggal) VALUES (?, ?, ?)' 
    );
    $s4->bind_param('iis', $pemesanan_id, $nomor_antrian, $tanggal);
    $s4->execute();

    // STEP E: Update kuota_saat_ini +1
    // dikerjain paling akhir setelah semua INSERT sukses
    $s5 = $conn->prepare(
        'UPDATE kuota SET kuota_saat_ini = kuota_saat_ini + 1 WHERE tanggal = ?'
    );
    $s5->bind_param('s', $tanggal);
    $s5->execute();

    // semua INSERT berhasil, commit transaksi
    $conn->commit();

    http_response_code(201);
    echo json_encode([
        'status'  => 'success',
        'message' => 'Booking berhasil dibuat',
        'data'    => [
            'pemesanan_id'  => $pemesanan_id,
            'nomor_antrian' => $nomor_antrian,
            'tanggal'       => $tanggal,
            'jam'           => $jam,
        ]
    ]);

} catch (Exception $e) {
    // kalo ada yang gagal, batalkan semua perubahan
    $conn->rollback();         
    http_response_code(500);   
    echo json_encode([
        'status'  => 'error',
        'message' => 'Booking gagal: ' . $e->getMessage()
    ]);
}

$conn->close();
?>