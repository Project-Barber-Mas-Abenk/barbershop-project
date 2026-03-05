CREATE DATABASE IF NOT EXISTS barbershop_db
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE barbershop_db;

-- tabel users (admin & customer)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20),
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- tabel layanan
CREATE TABLE layanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL
);

-- tabel kuota booking per hari
CREATE TABLE kuota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL UNIQUE,
    kuota_harian INT NOT NULL DEFAULT 4,
    kuota_saat_ini INT NOT NULL DEFAULT 0
);

-- tabel pemesanan
CREATE TABLE pemesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    nama_pelanggan VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    layanan_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam TIME NOT NULL,
    status ENUM('menunggu','dikonfirmasi','selesai','dibatalkan') 
        NOT NULL DEFAULT 'menunggu',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (layanan_id) REFERENCES layanan(id)
);

-- tabel pembayaran
CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pemesanan_id INT NOT NULL UNIQUE,
    metode ENUM('cash','transfer','qris') NOT NULL,
    status ENUM('menunggu','lunas','gagal') NOT NULL DEFAULT 'menunggu',
    jumlah DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pemesanan_id) REFERENCES pemesanan(id)
);

-- tabel antrian
CREATE TABLE antrian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pemesanan_id INT NOT NULL UNIQUE,
    nomor_antrian INT NOT NULL,
    tanggal DATE NOT NULL,
    FOREIGN KEY (pemesanan_id) REFERENCES pemesanan(id)
);

-- data layanan
INSERT INTO layanan (nama, harga) VALUES
('Potong Rambut', 30000),
('Cukur Jenggot/Kumis', 10000),
('Cuci Rambut', 10000),
('Creambath', 40000),
('Warnain Rambut', 300000),
('Highlight Rambut', 300000),
('Booking Potong Rambut', 50000);

-- akun admin default
INSERT INTO users (username, password, nama, role) VALUES
('admin', '$2y$10$f5x8RovfMH91lpMAAs1/GuECkZemJJel7woxKvGIUIhvAJMocVqfC', 'Administrator', 'admin');
