-- Database untuk Sistem Pendukung Keputusan SAW
-- Mahasiswa Berprestasi FTI Uniska MAB

-- Reset database (opsional, hapus jika tidak ingin menghapus data lama)
DROP DATABASE IF EXISTS spk_mawapres_fti_uniska_mab;
CREATE DATABASE IF NOT EXISTS spk_mawapres_fti_uniska_mab;
USE spk_mawapres_fti_uniska_mab;

-- Tabel admin
DROP TABLE IF EXISTS admin;
CREATE TABLE admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel mahasiswa
DROP TABLE IF EXISTS mahasiswa;
CREATE TABLE mahasiswa (
    id_mahasiswa INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nim VARCHAR(20) NOT NULL UNIQUE,
    ipk DECIMAL(3,2) NOT NULL,
    bahasa_program INT NOT NULL,
    kti INT NOT NULL,
    toefl INT NOT NULL,
    organisasi INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel kriteria
DROP TABLE IF EXISTS kriteria;
CREATE TABLE kriteria (
    id_kriteria INT AUTO_INCREMENT PRIMARY KEY,
    nama_kriteria VARCHAR(100) NOT NULL,
    bobot DECIMAL(3,2) NOT NULL,
    jenis ENUM('benefit', 'cost') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel hasil perhitungan
DROP TABLE IF EXISTS hasil_perhitungan;
CREATE TABLE hasil_perhitungan (
    id_hasil INT AUTO_INCREMENT PRIMARY KEY,
    id_mahasiswa INT NOT NULL,
    nilai_preferensi DECIMAL(10,6) NOT NULL,
    ranking INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(id_mahasiswa) ON DELETE CASCADE
);

-- Tabel dokumen pendukung
DROP TABLE IF EXISTS dokumen;
CREATE TABLE dokumen (
    id_dokumen INT AUTO_INCREMENT PRIMARY KEY,
    nama_mhs VARCHAR(100) NOT NULL,
    npm VARCHAR(30) NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    judul_kegiatan VARCHAR(200) NOT NULL,
    tingkat_prestasi ENUM('Regional','Nasional','Internasional') NOT NULL,
    kategori_peserta ENUM('Individu','Tim') NOT NULL,
    file VARCHAR(255) NOT NULL,
    status ENUM('Menunggu','Disetujui','Ditolak') DEFAULT 'Menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert data admin default
INSERT INTO admin (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

-- Insert data kriteria default
INSERT INTO kriteria (nama_kriteria, bobot, jenis) VALUES 
('IPK', 0.25, 'benefit'),
('Kemampuan Bahasa Pemrograman', 0.20, 'benefit'),
('Karya Tulis Ilmiah', 0.20, 'benefit'),
('TOEFL', 0.15, 'benefit'),
('Pengalaman Organisasi', 0.20, 'benefit');

-- Insert sample data mahasiswa
INSERT INTO mahasiswa (nama, nim, ipk, bahasa_program, kti, toefl, organisasi) VALUES 
('Ahmad Fauzi', '2021001', 3.85, 85, 90, 550, 8),
('Siti Nurhaliza', '2021002', 3.90, 90, 85, 580, 7),
('Muhammad Rizki', '2021003', 3.75, 80, 88, 520, 9),
('Dewi Sartika', '2021004', 3.95, 92, 92, 600, 6),
('Budi Santoso', '2021005', 3.70, 78, 82, 500, 10); 