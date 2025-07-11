<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Cek login
requireLogin();

// Ambil statistik
$stmt = $pdo->query("SELECT COUNT(*) as total_mahasiswa FROM mahasiswa");
$total_mahasiswa = $stmt->fetch()['total_mahasiswa'];

$stmt = $pdo->query("SELECT COUNT(*) as total_kriteria FROM kriteria");
$total_kriteria = $stmt->fetch()['total_kriteria'];

$stmt = $pdo->query("SELECT COUNT(*) as total_hasil FROM hasil_perhitungan");
$total_hasil = $stmt->fetch()['total_hasil'];

// Ambil top 5 mahasiswa terbaik
$stmt = $pdo->query("
    SELECT m.nama, m.nim, h.nilai_preferensi, h.ranking 
    FROM hasil_perhitungan h 
    JOIN mahasiswa m ON h.id_mahasiswa = m.id_mahasiswa 
    ORDER BY h.ranking ASC 
    LIMIT 5
");
$top_mahasiswa = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem SPK SAW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.0/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary:#000000;
            --secondary: #00FF33;
            --background: #EDEAFF;
            --accent: #4B6286;
        }
        body, .main-content {
            background: linear-gradient(180deg, #001a08 0%, #00FF33 100%) !important;
            color: #fff;
        }
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            color: #fff;
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
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .btn-primary:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        .card, .content-card {
            background: rgba(255,255,255,0.97);
            border-radius: 22px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
            border: none;
        }
        .navbar, .main-content {
            background: transparent !important;
        }
        hr {
            border-color: var(--primary) !important;
            opacity: 0.2;
        }
        .table thead th {
            background: var(--secondary);
            color: var(--accent);
        }
        .table-striped > tbody > tr:nth-of-type(odd) {
            background: #f6fbfa;
        }
        .main-content {
            min-height: 100vh;
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
        h4.mb-0, .dashboard-title, h1.dashboard-title, h2.dashboard-title {
            color: #000 !important;
        }
        .card-stats {
            background: linear-gradient(90deg, #001a08 0%, #00FF33 100%) !important;
            color: #fff !important;
            border-radius: 24px !important;
            box-shadow: 0 4px 24px rgba(0,255,51,0.18);
            border: none;
        }
        .card-stats .fa-2x {
            color: #fff !important;
        }
        .btn.btn-info {
            background: linear-gradient(90deg, #000 0%, #00FF33 100%) !important;
            border: none !important;
            color: #fff !important;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0,255,51,0.12);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .btn.btn-info:hover, .btn.btn-info:focus {
            background: linear-gradient(90deg, #222 0%, #00FF66 100%) !important;
            color: #fff !important;
            box-shadow: 0 4px 16px rgba(0,255,51,0.22);
        }
        .btn.btn-warning {
            background: linear-gradient(90deg, #00FF33 0%, #00CC00 100%) !important;
            border: none !important;
            color: #fff !important;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0,255,51,0.12);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .btn.btn-warning:hover, .btn.btn-warning:focus {
            background: linear-gradient(90deg, #00FF66 0%, #00CC33 100%) !important;
            color: #fff !important;
            box-shadow: 0 4px 16px rgba(0,255,51,0.22);
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
                        <a class="nav-link active" href="index.php">
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
                            <h4 class="mb-0">Dashboard</h4>
                            <div class="navbar-nav ms-auto">
                                <span class="navbar-text">
                                    <i class="fas fa-user me-1"></i>
                                    Selamat datang, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                                </span>
                            </div>
                        </div>
                    </nav>
                    
                    <div class="p-4">
                        <!-- Statistik Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="card card-stats bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h3 class="mb-0"><?php echo $total_mahasiswa; ?></h3>
                                                <p class="mb-0">Total Anggota Kepengurusan</p>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-users fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="card card-stats bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h3 class="mb-0"><?php echo $total_kriteria; ?></h3>
                                                <p class="mb-0">Total Kriteria</p>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-list-check fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="card card-stats bg-info text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h3 class="mb-0"><?php echo $total_hasil; ?></h3>
                                                <p class="mb-0">Hasil Perhitungan</p>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-chart-bar fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Top 5 Mahasiswa -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-trophy text-warning"></i> Top 5 Anggota Kepengurusan Teladan
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($top_Anggota)): ?>
                                            <div class="text-center py-4">
                                                <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                                                <p class="text-muted">Belum ada hasil perhitungan SAW</p>
                                                <a href="hitung_saw.php" class="btn btn-primary">
                                                    <i class="fas fa-calculator"></i> Hitung SAW
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Ranking</th>
                                                            <th>Nama</th>
                                                            <th>Jabatan</th>
                                                            <th>Nilai Preferensi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($top_Anggota as $index => $Anggota): ?>
                                                            <tr>
                                                                <td>
                                                                    <?php if ($Anggota['ranking'] == 1): ?>
                                                                        <span class="badge bg-warning text-dark">
                                                                            <i class="fas fa-crown"></i> 1
                                                                        </span>
                                                                    <?php elseif ($Anggota['ranking'] == 2): ?>
                                                                        <span class="badge bg-secondary">2</span>
                                                                    <?php elseif ($Anggota['ranking'] == 3): ?>
                                                                        <span class="badge bg-warning">3</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-light text-dark"><?php echo $mahasiswa['ranking']; ?></span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($Anggota['nama']); ?></td>
                                                                <td><?php echo htmlspecialchars($Anggota['jabatan']); ?></td>
                                                                <td>
                                                                    <span class="badge bg-primary">
                                                                        <?php echo number_format($Anggota['nilai_preferensi'], 4); ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-bolt"></i> Quick Actions
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <a href="tambah_mahasiswa.php" class="btn btn-primary w-100">
                                                    <i class="fas fa-plus"></i> Tambah Anggota
                                                </a>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <a href="tambah_kriteria.php" class="btn btn-success w-100">
                                                    <i class="fas fa-plus"></i> Tambah Kriteria
                                                </a>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <a href="hitung_saw.php" class="btn btn-info w-100">
                                                    <i class="fas fa-calculator"></i> Hitung SAW
                                                </a>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <a href="hasil.php" class="btn btn-warning w-100">
                                                    <i class="fas fa-chart-bar"></i> Lihat Hasil
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
</body>
</html> 