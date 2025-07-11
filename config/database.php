<?php
// Konfigurasi Database
$host = 'localhost';
$db   = 'spk_mawapres_fti_uniska_mab';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Perbaiki koneksi mysqli sesuai konfigurasi di atas
$conn = mysqli_connect($host, $user, $pass, $db);

// Jika Anda belum memiliki tabel 'nilai', jalankan query berikut di phpMyAdmin atau Adminer:
//
// CREATE TABLE nilai (
//   id_nilai INT AUTO_INCREMENT PRIMARY KEY,
//   id_mahasiswa INT NOT NULL,
//   id_kriteria INT NOT NULL,
//   nilai FLOAT NOT NULL,
//   FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(id_mahasiswa) ON DELETE CASCADE,
//   FOREIGN KEY (id_kriteria) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE
// );
//
// Pastikan tabel ini ada agar fitur tambah/edit anggota berjalan normal.
?> 