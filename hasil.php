<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Cek login
requireLogin();

// Ambil hasil perhitungan
$stmt = $pdo->query("
    SELECT h.*, m.nama, m.jabatan 
    FROM hasil_perhitungan h 
    JOIN mahasiswa m ON h.id_mahasiswa = m.id_mahasiswa 
    ORDER BY h.ranking ASC
");
$hasil_list = $stmt->fetchAll();

// Ambil data kriteria
$stmt = $pdo->query("SELECT * FROM kriteria ORDER BY nama_kriteria ASC");
$kriteria_list = $stmt->fetchAll();

// Prepare statement untuk ambil nilai per mahasiswa
$nilai_stmt = $pdo->prepare("SELECT id_kriteria, nilai FROM nilai WHERE id_mahasiswa = ?");

// Sebelum penggunaan $nilai_kriteria, tambahkan inisialisasi jika belum ada
if (!isset($nilai_kriteria)) {
    $nilai_kriteria = [];
}

// Proses ekspor Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // Set header untuk download Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="hasil_ranking_saw.xls"');
    
    // Hitung jumlah kolom untuk colspan
    $total_columns = 3 + count($kriteria_list) + 1; // Ranking + Nama + Jabatan + Kriteria + Nilai Preferensi
    
    echo '<table border="1">';
    echo '<tr><th colspan="' . $total_columns . '" style="text-align:center;font-weight:bold;font-size:16px;">HASIL RANKING ANGGOTA KEPENGURUSAN ORGANISASI TELADAN</th></tr>';
    echo '<tr><th colspan="' . $total_columns . '" style="text-align:center;">PERHIMPUNAN MAHASISWA INFORMATIKA DAN KOMPUTER NASIONAL WILAYAH XII KALIMANTAN</th></tr>';
    echo '<tr><th colspan="' . $total_columns . '" style="text-align:center;">Metode SAW (Simple Additive Weighting)</th></tr>';
    echo '<tr><th colspan="' . $total_columns . '"></th></tr>';
    
    // Header tabel
    echo '<tr>';
    echo '<th style="background-color:#f0f0f0;font-weight:bold;">Ranking</th>';
    echo '<th style="background-color:#f0f0f0;font-weight:bold;">Nama</th>';
    echo '<th style="background-color:#f0f0f0;font-weight:bold;">Jabatan</th>';
    
    // Header kriteria dinamis
    foreach ($kriteria_list as $kriteria) {
        echo '<th style="background-color:#f0f0f0;font-weight:bold;">' . strtoupper($kriteria['nama_kriteria']) . '</th>';
    }
    
    echo '<th style="background-color:#f0f0f0;font-weight:bold;">Nilai Preferensi</th>';
    echo '</tr>';
    
    // Data rows
    foreach ($hasil_list as $hasil) {
        // Ambil nilai kriteria untuk mahasiswa ini
        $nilai_stmt->execute([$hasil['id_mahasiswa']]);
        $nilai_mahasiswa = $nilai_stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [id_kriteria => nilai]
        
        echo '<tr>';
        echo '<td>' . $hasil['ranking'] . '</td>';
        echo '<td>' . $hasil['nama'] . '</td>';
        echo '<td>' . $hasil['jabatan'] . '</td>';
        
        // Nilai kriteria dinamis
        foreach ($kriteria_list as $kriteria) {
            $nilai = $nilai_mahasiswa[$kriteria['id_kriteria']] ?? '-';
            echo '<td>' . $nilai . '</td>';
        }
        
        echo '<td>' . number_format($hasil['nilai_preferensi'], 6) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Ranking - Sistem SPK SAW</title>
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
        .ranking-1 {
            background: linear-gradient(45deg, #ffd700, #ffed4e) !important;
            color: #000 !important;
        }
        .ranking-2 {
            background: linear-gradient(45deg, #c0c0c0, #e0e0e0) !important;
        }
        .ranking-3 {
            background: linear-gradient(45deg, #cd7f32, #daa520) !important;
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
        }
        .btn.btn-primary:hover, .btn.btn-primary:focus {
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
                        <a class="nav-link" href="hitung_saw.php">
                            <i class="fas fa-calculator me-2"></i>
                            <span class="d-none d-lg-inline">Hitung SAW</span>
                        </a>
                        <a class="nav-link active" href="hasil.php">
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
                            <h4 class="mb-0">Hasil Ranking</h4>
                            <div class="navbar-nav ms-auto">
                                <?php if (!empty($hasil_list)): ?>
                                    <a href="?export=excel" class="btn btn-success me-2">
                                        <i class="fas fa-file-excel"></i> Export Excel
                                    </a>
                                    <a href="cetak.php" class="btn btn-danger me-2">
                                        <i class="fas fa-file-pdf"></i> Export PDF
                                    </a>
                                <?php endif; ?>
                                <a href="hitung_saw.php" class="btn btn-primary">
                                    <i class="fas fa-calculator"></i> Hitung SAW
                                </a>
                            </div>
                        </div>
                    </nav>
                    
                    <div class="p-4">
                        <?php if (empty($hasil_list)): ?>
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-chart-bar fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">Belum ada hasil perhitungan</h4>
                                    <p class="text-muted">Silakan lakukan perhitungan SAW terlebih dahulu</p>
                                    <a href="hitung_saw.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-calculator"></i> Hitung SAW
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Header Hasil -->
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                            <i class="fas fa-trophy"></i> HASIL RANKING ANGGOTA KEPENGURUSAN PERMIKOMNAS WILAYAH XII KALIMANTAN
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <h6 class="text-muted">Total Anggota Kepengurusan</h6>
                                            <h3 class="text-primary"><?php echo count($hasil_list); ?></h3>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="text-muted">Anggota Kepengurusan Teladan</h6>
                                            <h3 class="text-success"><?php echo $hasil_list[0]['nama']; ?></h3>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="text-muted">Nilai Tertinggi</h6>
                                            <h3 class="text-warning"><?php echo number_format($hasil_list[0]['nilai_preferensi'], 6); ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tabel Hasil -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-list-ol"></i> Daftar Ranking Lengkap
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
                                                    <?php foreach ($kriteria_list as $kriteria): ?>
                                                        <th><?php echo htmlspecialchars($kriteria['nama_kriteria']); ?></th>
                                                    <?php endforeach; ?>
                                                    <th>Nilai Preferensi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($hasil_list as $hasil): ?>
                                                    <tr class="<?php echo 'ranking-' . $hasil['ranking']; ?>">
                                                        <td>
                                                            <?php if ($hasil['ranking'] == 1): ?>
                                                                <span class="badge bg-warning text-dark"><i class="fas fa-crown"></i> JUARA 1</span>
                                                            <?php elseif ($hasil['ranking'] == 2): ?>
                                                                <span class="badge bg-secondary">JUARA 2</span>
                                                            <?php elseif ($hasil['ranking'] == 3): ?>
                                                                <span class="badge bg-secondary">JUARA 3</span>
                                                            <?php elseif ($hasil['ranking'] == 4): ?>
                                                                <span class="badge bg-secondary">JUARA 4</span>
                                                            <?php elseif ($hasil['ranking'] == 5): ?>
                                                                <span class="badge bg-secondary">JUARA 5</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-light text-dark"><?php echo $hasil['ranking']; ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><strong><?php echo htmlspecialchars($hasil['nama'] ?? ''); ?></strong></td>
                                                        <td><?php echo htmlspecialchars($hasil['jabatan'] ?? '-'); ?></td>
                                                        <?php
                                                        // Ambil nilai kriteria untuk mahasiswa ini
                                                        $nilai_stmt->execute([$hasil['id_mahasiswa']]);
                                                        $nilai_mahasiswa = $nilai_stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [id_kriteria => nilai]
                                                        foreach ($kriteria_list as $kriteria) {
                                                            $nilai = $nilai_mahasiswa[$kriteria['id_kriteria']] ?? '-';
                                                            echo '<td><span class="badge bg-success">' . htmlspecialchars($nilai) . '</span></td>';
                                                        }
                                                        ?>
                                                        <td>
                                                            <span class="badge bg-dark fs-6">
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
                order: [[0, 'asc']],
                pageLength: 25
            });
        });
    </script>
</div>
</html> 
