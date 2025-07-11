<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Cek login
requireLogin();

$success = '';
$error = '';

// Ambil data mahasiswa dan kriteria
$stmt = $pdo->query("SELECT * FROM mahasiswa ORDER BY nama ASC");
$mahasiswa_list = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM kriteria ORDER BY nama_kriteria ASC");
$kriteria_list = $stmt->fetchAll();

// Proses perhitungan SAW
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hitung'])) {
    if (empty($mahasiswa_list)) {
        $error = 'Tidak ada data mahasiswa!';
    } elseif (empty($kriteria_list)) {
        $error = 'Tidak ada data kriteria!';
    } else {
        try {
            // Hapus hasil perhitungan sebelumnya
            $pdo->exec("DELETE FROM hasil_perhitungan");
            
            // Ambil semua nilai dari tabel nilai
            $nilai_stmt = $pdo->prepare("SELECT id_kriteria, nilai FROM nilai WHERE id_mahasiswa = ?");
            
            // Ambil nilai maksimum dan minimum untuk setiap kriteria
            $max_values = [];
            $min_values = [];
            
            // Hitung max dan min untuk setiap kriteria
            foreach ($kriteria_list as $kriteria) {
                $idk = $kriteria['id_kriteria'];
                $stmt = $pdo->prepare("SELECT MAX(nilai) as max_val, MIN(nilai) as min_val FROM nilai WHERE id_kriteria = ?");
                $stmt->execute([$idk]);
                $result = $stmt->fetch();
                $max_values[$idk] = $result['max_val'];
                $min_values[$idk] = $result['min_val'];
            }
            
            $hasil_perhitungan = [];
            
            // Hitung nilai preferensi untuk setiap mahasiswa
            foreach ($mahasiswa_list as $mahasiswa) {
                $nilai_stmt->execute([$mahasiswa['id_mahasiswa']]);
                $nilai_mahasiswa = $nilai_stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [id_kriteria => nilai]
                $nilai_preferensi = 0;
                
                foreach ($kriteria_list as $kriteria) {
                    $idk = $kriteria['id_kriteria'];
                    if (!isset($nilai_mahasiswa[$idk])) continue;
                    $nilai_kriteria = floatval($nilai_mahasiswa[$idk]);
                    
                    // Cek apakah max/min value tersedia dan tidak sama
                    if (!isset($max_values[$idk]) || !isset($min_values[$idk])) {
                        continue;
                    }
                    $max_val = floatval($max_values[$idk]);
                    $min_val = floatval($min_values[$idk]);
                    if ($max_val == $min_val) {
                        $normalisasi = 1; // atau 0, tergantung kebutuhan, di sini diasumsikan 1
                    } else {
                        if ($kriteria['jenis'] == 'benefit') {
                            $normalisasi = ($nilai_kriteria - $min_val) / ($max_val - $min_val);
                        } else {
                            $normalisasi = ($max_val - $nilai_kriteria) / ($max_val - $min_val);
                        }
                    }
                    
                    // Kalikan dengan bobot
                    $nilai_preferensi += $normalisasi * $kriteria['bobot'];
                }
                
                $hasil_perhitungan[] = [
                    'id_mahasiswa' => $mahasiswa['id_mahasiswa'],
                    'nama' => $mahasiswa['nama'],
                    'jabatan' => $mahasiswa['jabatan'],
                    'nilai_preferensi' => $nilai_preferensi
                ];
            }
            
            // Urutkan berdasarkan nilai preferensi (descending)
            usort($hasil_perhitungan, function($a, $b) {
                return $b['nilai_preferensi'] <=> $a['nilai_preferensi'];
            });
            
            // Simpan hasil ke database
            foreach ($hasil_perhitungan as $index => $hasil) {
                $ranking = $index + 1;
                $stmt = $pdo->prepare("
                    INSERT INTO hasil_perhitungan (id_mahasiswa, nilai_preferensi, ranking) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$hasil['id_mahasiswa'], $hasil['nilai_preferensi'], $ranking]);
            }
            
            $success = 'Perhitungan SAW berhasil dilakukan!';
            
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Ambil hasil perhitungan terbaru
$stmt = $pdo->query("
    SELECT h.*, m.nama, m.jabatan 
    FROM hasil_perhitungan h 
    JOIN mahasiswa m ON h.id_mahasiswa = m.id_mahasiswa 
    ORDER BY h.ranking ASC
");
$hasil_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hitung SAW - Sistem SPK SAW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.0/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body, .main-content {
            background: linear-gradient(180deg, #001a08 0%, #00FF33 100%) !important;
            color: #fff;
        }
        .sidebar {
            background: linear-gradient(180deg, #000000 0%, #00FF33 100%) !important;
            min-height: 100vh;
            color: #fff;
        }
        .card, .content-card {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
            border: none;
        }
        .card-header, .content-card-header, .table thead th {
            background: linear-gradient(90deg, #001a08 0%, #00FF33 100%) !important;
            color: #fff !important;
            border-radius: 22px 22px 0 0;
            border: none;
            font-weight: 700;
        }
        .sidebar .nav-link {
            color: #fff;
            border-radius: 12px;
            margin: 6px 8px;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
        }
        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .sidebar hr {
            border-color: rgba(255,255,255,0.2) !important;
            opacity: 1;
        }
        .main-content {
            padding: 40px 0;
        }
        .content-card {
            padding: 32px 28px;
            margin: 0 auto;
            max-width: 98%;
        }
        @media (min-width: 992px) {
            .content-card {
                max-width: 95%;
            }
        }
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: flex-start;
        }
        .sidebar .nav-link i {
            min-width: 22px;
                text-align: left;
        }
        .btn.btn-primary {
            background: linear-gradient(90deg, #000 0%, #00FF33 100%) !important;
            border: none !important;
            color: #fff !important;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0,255,51,0.12);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .btn.btn-primary:hover, .btn.btn-primary:focus {
            background: linear-gradient(90deg, #222 0%, #00FF66 100%) !important;
            color: #fff !important;
            box-shadow: 0 4px 16px rgba(0,255,51,0.22);
        }
        .btn.btn-info {
            background: linear-gradient(90deg, #000 0%, #00FF33 100%) !important;
            border: none !important;
            color: #fff !important;
            font-weight: 600;
        }
        .btn.btn-info:hover, .btn.btn-info:focus {
            background: linear-gradient(90deg, #222 0%, #00FF66 100%) !important;
            color: #fff !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-4 text-center">
                        <h5 class="text-white mb-0">
                            <i class="fas fa-graduation-cap"></i> SPK SAW
                        </h5>
                        <small class="text-white-50">Anggota Kepengurusan Organisasi Teladan</small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            <span class="d-none d-lg-inline">Dashboard</span>
                        </a>
                        <a class="nav-link" href="mahasiswa.php">
                            <i class="fas fa-users me-2"></i>
                            <span class="d-none d-lg-inline">Data Anggota Kepengurusan</span>
                        </a>
                        <a class="nav-link" href="dokumen.php">
                            <i class="fas fa-file-alt me-2"></i>
                            <span class="d-none d-lg-inline">Dokumen Pendukung</span>
                        </a>
                        <a class="nav-link" href="kriteria.php">
                            <i class="fas fa-list-check me-2"></i>
                            <span class="d-none d-lg-inline">Kriteria</span>
                        </a>
                        <a class="nav-link active" href="hitung_saw.php">
                            <i class="fas fa-calculator me-2"></i>
                            <span class="d-none d-lg-inline">Hitung SAW</span>
                        </a>
                        <a class="nav-link" href="hasil.php">
                            <i class="fas fa-chart-bar me-2"></i>
                            <span class="d-none d-lg-inline">Hasil Ranking</span>
                        </a>
                        <hr class="text-white-50 mx-3">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            <span class="d-none d-lg-inline">Logout</span>
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-0">
                <div class="main-content">
                    <div class="content-card">
                    <!-- Navbar -->
                    <nav class="navbar navbar-expand-lg">
                        <div class="container-fluid">
                            <h4 class="mb-0">Hitung SAW</h4>
                            <div class="navbar-nav ms-auto">
                                <a href="hasil.php" class="btn btn-info">
                                    <i class="fas fa-chart-bar"></i> Lihat Hasil
                                </a>
                            </div>
                        </div>
                    </nav>
                    
                    <div class="p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Tombol Hitung SAW -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-calculator"></i> Perhitungan SAW
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Data Mahasiswa: <?php echo count($mahasiswa_list); ?> orang</h6>
                                        <h6>Data Kriteria: <?php echo count($kriteria_list); ?> kriteria</h6>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <form method="POST" style="display: inline;">
                                            <button type="submit" name="hitung" class="btn btn-primary btn-lg">
                                                <i class="fas fa-calculator"></i> Hitung SAW
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hasil Perhitungan -->
                        <?php if (!empty($hasil_list)): ?>
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-trophy"></i> Hasil Perhitungan SAW
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="hasilTable" class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Ranking</th>
                                                    <th>Nama</th>
                                                    <th>Jabatan</th>
                                                    <th>Nilai Preferensi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($hasil_list as $hasil): ?>
                                                    <tr>
                                                        <td>
                                                            <?php if ($hasil['ranking'] == 1): ?>
                                                                <span class="badge bg-warning text-dark">
                                                                    <i class="fas fa-crown"></i> 1
                                                                </span>
                                                            <?php elseif ($hasil['ranking'] == 2): ?>
                                                                <span class="badge bg-secondary">2</span>
                                                            <?php elseif ($hasil['ranking'] == 3): ?>
                                                                <span class="badge bg-secondary">3</span>
                                                            <?php elseif ($hasil['ranking'] == 4): ?>
                                                                <span class="badge bg-secondary">4</span>
                                                            <?php elseif ($hasil['ranking'] == 5): ?>
                                                                <span class="badge bg-secondary">5</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-light text-dark"><?php echo $hasil['ranking']; ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($hasil['nama'] ?? ''); ?></td>
                                                        <td><?php echo htmlspecialchars($hasil['jabatan'] ?? '-'); ?></td>
                                                        <td>
                                                            <span class="badge bg-primary">
                                                                <?php echo number_format($hasil['nilai_preferensi'], 6); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card">
                                <div class="card-body text-center py-4">
                                    <i class="fas fa-calculator fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Belum ada hasil perhitungan</h5>
                                    <p class="text-muted">Klik tombol "Hitung SAW" untuk memulai perhitungan</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <p style="position:fixed;left:0;right:0;bottom:18px;text-align:center;color:#4B6286;opacity:0.85;z-index:9999;font-size:16px;font-weight:500;letter-spacing:0.5px;pointer-events:none;">Copyright &#169; 2025 Muhammad Rizky. All rights reserved.</p>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.0/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.0/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#hasilTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.0/i18n/id.json'
                },
                responsive: true,
                order: [[0, 'asc']]
            });
        });
    </script>
</body>
</html> 