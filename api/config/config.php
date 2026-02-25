<?php
// koneksi Database & Setting Global

// konstanta koneksi database
// sesuaikan dengan environment (lokal / server)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'barbershop_db');

// kapasitas maksimal booking per hari
// kalo mau ganti kuota, cukup ubah angka di sini
define('KUOTA_HARIAN', 4);

// fungsi buat buka koneksi ke database
// panggil di awal setiap endpoint yang butuh DB
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // kalo koneksi gagal, stop dan kirim error JSON
    // jangan dibiarin diem-diem gagal
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode([
            'status'  => 'error',
            'message' => 'Koneksi database gagal: ' . $conn->connect_error
        ]));
    }

    // set charset UTF-8 supaya nama dengan karakter
    // khusus tersimpan dengan bener di database
    $conn->set_charset('utf8mb4');

    return $conn;
}

// header ini dipasang di sini supaya ga perlu
// ditulis ulang di setiap file endpoint
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type'); 
?>