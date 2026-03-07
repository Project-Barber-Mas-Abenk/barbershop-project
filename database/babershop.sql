CREATE DATABASE IF NOT EXISTS barbershop_db
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE barbershop_db;

DROP TABLE IF EXISTS antrian;
DROP TABLE IF EXISTS pembayaran;
DROP TABLE IF EXISTS pemesanan;
DROP TABLE IF EXISTS kuota;
DROP TABLE IF EXISTS pelanggan;
DROP TABLE IF EXISTS layanan;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admin;

CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255),
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20),
    google_id VARCHAR(100),
    role ENUM('admin','user') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE layanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL
);

CREATE TABLE kuota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL UNIQUE,
    kuota_harian INT NOT NULL DEFAULT 4,
    kuota_saat_ini INT NOT NULL DEFAULT 0
);

CREATE TABLE pemesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelanggan_id INT NOT NULL,
    layanan_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam TIME NOT NULL,
    status ENUM('menunggu','dikonfirmasi','selesai','dibatalkan') 
        NOT NULL DEFAULT 'menunggu',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id),
    FOREIGN KEY (layanan_id) REFERENCES layanan(id)
);

CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pemesanan_id INT NOT NULL UNIQUE,
    metode ENUM('cash','transfer','qris') NOT NULL,
    status ENUM('menunggu','lunas','gagal') NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pemesanan_id) REFERENCES pemesanan(id)
);

CREATE TABLE antrian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pemesanan_id INT NOT NULL UNIQUE,
    nomor_antrian INT NOT NULL,
    tanggal DATE NOT NULL,
    FOREIGN KEY (pemesanan_id) REFERENCES pemesanan(id)
);

INSERT INTO layanan (nama, harga) VALUES
('Potong Rambut', 30000),
('Cukur Jenggot/Kumis', 10000),
('Cuci Rambut', 10000),
('Creambath', 40000),
('Warnain Rambut', 300000),
('Highlight Rambut', 300000),
('Booking Potong Rambut', 50000);

INSERT INTO admin (username, password, nama) VALUES
('admin', '$2y$10$f5x8RovfMH91lpMAAs1/GuECkZemJJel7woxKvGIUIhvAJMocVqfC', 'Mas Abenk');
