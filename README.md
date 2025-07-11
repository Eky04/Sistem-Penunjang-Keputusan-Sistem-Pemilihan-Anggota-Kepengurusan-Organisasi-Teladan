# Sistem Pendukung Keputusan SAW - Mahasiswa Berprestasi FTI Uniska MAB

Sistem pendukung keputusan berbasis web untuk pemilihan mahasiswa berprestasi menggunakan metode SAW (Simple Additive Weighting).

## 🎯 Deskripsi

Sistem ini dirancang untuk membantu Fakultas Teknologi Informasi Uniska MAB dalam memilih mahasiswa berprestasi berdasarkan kriteria yang telah ditentukan. Metode yang digunakan adalah SAW (Simple Additive Weighting) yang merupakan salah satu metode dalam Multi-Criteria Decision Making (MCDM).

## ✨ Fitur Utama

- **Dashboard Admin**: Tampilan statistik dan ringkasan data
- **Manajemen Mahasiswa**: CRUD data mahasiswa dengan kriteria lengkap
- **Manajemen Kriteria**: Pengaturan bobot dan jenis kriteria
- **Perhitungan SAW**: Otomatis menghitung ranking berdasarkan metode SAW
- **Hasil Ranking**: Tampilan hasil perhitungan dengan ranking
- **Export Excel**: Ekspor hasil ke format Excel (.xls)
- **Sistem Login**: Autentikasi admin dengan session
- **Responsive Design**: Tampilan yang responsif dengan Bootstrap 5

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP Native
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **JavaScript**: jQuery, DataTables
- **Icons**: Font Awesome 6

## 📋 Kriteria Penilaian

1. **IPK** (Bobot: 0.25) - Benefit
2. **Kemampuan Bahasa Pemrograman** (Bobot: 0.20) - Benefit
3. **Karya Tulis Ilmiah** (Bobot: 0.20) - Benefit
4. **TOEFL** (Bobot: 0.15) - Benefit
5. **Pengalaman Organisasi** (Bobot: 0.20) - Benefit

## 🚀 Instalasi

### Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx)
- XAMPP/WAMP/LAMP

### Langkah Instalasi

1. **Clone atau Download Proyek**
   ```bash
   git clone [repository-url]
   cd tes-spk-ahp
   ```

2. **Setup Database**
   - Buka phpMyAdmin
   - Buat database baru dengan nama `spk_mawapres_fti_uniska_mab`
   - Import file `database.sql`

3. **Konfigurasi Database**
   - Edit file `config/database.php`
   - Sesuaikan host, username, password, dan nama database

4. **Upload ke Web Server**
   - Upload semua file ke folder web server
   - Pastikan folder memiliki permission yang tepat

5. **Akses Sistem**
   - Buka browser dan akses `http://localhost/tes-spk-ahp`
   - Login dengan kredensial default:
     - Username: `admin`
     - Password: `password`

## 📁 Struktur File

```
tes-spk-ahp/
├── config/
│   └── database.php          # Konfigurasi database
├── includes/
│   └── auth.php              # Fungsi autentikasi
├── database.sql              # File SQL database
├── index.php                 # Dashboard utama
├── login.php                 # Halaman login
├── logout.php                # Logout
├── mahasiswa.php             # Data mahasiswa
├── tambah_mahasiswa.php      # Tambah mahasiswa
├── edit_mahasiswa.php        # Edit mahasiswa
├── hapus_mahasiswa.php       # Hapus mahasiswa
├── kriteria.php              # Data kriteria
├── tambah_kriteria.php       # Tambah kriteria
├── hitung_saw.php            # Perhitungan SAW
├── hasil.php                 # Hasil ranking
└── README.md                 # Dokumentasi
```

## 🔐 Login Default

- **Username**: admin
- **Password**: password

**⚠️ Penting**: Ganti password default setelah instalasi!

## 📊 Alur Sistem

1. **Admin Login** ke sistem
2. **Input Data Kriteria** dan bobot
3. **Input Data Mahasiswa** dengan nilai kriteria
4. **Sistem Menghitung SAW** secara otomatis:
   - Normalisasi nilai (benefit/cost)
   - Perhitungan bobot
   - Menghitung nilai preferensi (Vi)
5. **Menampilkan Hasil Ranking**
6. **Export ke Excel** (opsional)

## 🧮 Metode SAW

### Langkah Perhitungan:

1. **Normalisasi Matriks Keputusan**
   - Benefit: `rij = xij / max(xij)`
   - Cost: `rij = min(xij) / xij`

2. **Perhitungan Nilai Preferensi**
   - `Vi = Σ(wj × rij)`
   - Dimana: wj = bobot kriteria, rij = nilai normalisasi

3. **Ranking**
   - Urutkan berdasarkan nilai Vi (descending)

## 🎨 Tampilan

- **Dashboard**: Statistik dan quick actions
- **Sidebar Navigation**: Menu navigasi yang mudah
- **DataTables**: Tabel dinamis dengan fitur search dan pagination
- **Bootstrap 5**: UI modern dan responsif
- **Font Awesome**: Icons yang menarik

## 🔧 Konfigurasi

### Database
Edit file `config/database.php`:
```php
$host = 'localhost';
$dbname = 'spk_mawapres_fti_uniska_mab';
$username = 'root';
$password = '';
```

### Kriteria
Kriteria dapat diubah melalui menu "Kriteria" di sistem. Pastikan total bobot = 1.0

## 📱 Responsive Design

Sistem didesain responsif untuk:
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (< 768px)

## 🚀 Deployment

### XAMPP (Local)
1. Copy folder ke `htdocs/`
2. Start Apache dan MySQL
3. Import database
4. Akses via `http://localhost/tes-spk-ahp`

### cPanel (Hosting)
1. Upload file via File Manager
2. Import database via phpMyAdmin
3. Edit konfigurasi database
4. Akses via domain

## 🐛 Troubleshooting

### Error Koneksi Database
- Periksa konfigurasi di `config/database.php`
- Pastikan MySQL berjalan
- Cek username dan password

### Error Session
- Pastikan folder memiliki permission write
- Cek konfigurasi PHP session

### Error Upload
- Periksa permission folder
- Cek max_upload_size di php.ini

## 📞 Support

Untuk bantuan dan dukungan teknis, silakan hubungi:
- Email: [email]
- WhatsApp: [nomor]

## 📄 Lisensi

Proyek ini dibuat untuk keperluan akademik Fakultas Teknologi Informasi Uniska MAB.

## 🔄 Versi

- **v1.0.0**: Release awal dengan fitur dasar SAW
- **v1.1.0**: Penambahan fitur export Excel
- **v1.2.0**: Perbaikan UI/UX dan responsivitas

---

**© 2024 FTI Uniska MAB - Sistem Pendukung Keputusan SAW** 