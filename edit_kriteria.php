<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Cek login
requireLogin();

$success = '';
$error = '';
$kriteria = null;

// Ambil ID kriteria dari parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: kriteria.php");
    exit();
}

// Ambil data kriteria
$stmt = $pdo->prepare("SELECT * FROM kriteria WHERE id_kriteria = ?");
$stmt->execute([$id]);
$kriteria = $stmt->fetch();

if (!$kriteria) {
    header("Location: kriteria.php");
    exit();
}

// Proses form update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kriteria = trim($_POST['nama_kriteria']);
    $bobot = floatval($_POST['bobot']);
    $jenis = $_POST['jenis'];
    
    // Validasi
    if (empty($nama_kriteria)) {
        $error = 'Nama kriteria harus diisi!';
    } elseif ($bobot <= 0 || $bobot > 1) {
        $error = 'Bobot harus antara 0-1!';
    } elseif (!in_array($jenis, ['benefit', 'cost'])) {
        $error = 'Jenis kriteria tidak valid!';
    } else {
        try {
            // Update data kriteria
            $stmt = $pdo->prepare("
                UPDATE kriteria 
                SET nama_kriteria = ?, bobot = ?, jenis = ?
                WHERE id_kriteria = ?
            ");
            $stmt->execute([$nama_kriteria, $bobot, $jenis, $id]);
            
            $success = 'Data kriteria berhasil diperbarui!';
            
            // Update data kriteria untuk ditampilkan
            $kriteria = [
                'nama_kriteria' => $nama_kriteria,
                'bobot' => $bobot,
                'jenis' => $jenis
            ];
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kriteria - Sistem SPK SAW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            color: #00FF33;
        }
        .btn-primary:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        .card {
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(76, 98, 134, 0.07);
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
            background: linear-gradient(90deg, #001a08 0%, #00FF33 100%) !important;
            color: #fff !important;
            font-weight: 700;
            border-top-left-radius: 18px;
            border-top-right-radius: 18px;
        }
        .table-striped > tbody > tr:nth-of-type(odd) {
            background: #f6fbfa;
        }
        .main-content {
            padding: 40px 0;
        }
        .content-card {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 4px 24px rgba(76, 98, 134, 0.10);
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
        .footer-copyright, .copyright, footer, .footer {
            color: #fff !important;
            font-weight: 500;
            text-align: center;
        }
        .card-header, .content-card-header {
            background: linear-gradient(90deg, #001a08 0%, #00FF33 100%);
            color: #fff;
            border-radius: 22px 22px 0 0;
            border: none;
        }
        .btn.btn-success {
            background: linear-gradient(90deg, #000 0%, #00FF33 100%) !important;
            border: none !important;
            color: #fff !important;
            font-weight: 600;
        }
        .btn.btn-success:hover, .btn.btn-success:focus {
            background: linear-gradient(90deg, #222 0%, #00FF66 100%) !important;
            color: #fff !important;
        }
        .btn.btn-warning {
            background: linear-gradient(90deg, #000 0%, #00FF33 100%) !important;
            color: #fff !important;
            border: none !important;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0,255,51,0.12);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .btn.btn-warning:hover, .btn.btn-warning:focus {
            background: linear-gradient(90deg, #222 0%, #00FF66 100%) !important;
            color: #fff !important;
            box-shadow: 0 4px 16px rgba(0,255,51,0.22);
        }
        .btn.btn-secondary {
            background: linear-gradient(90deg, #000 0%, #00FF33 100%) !important;
            color: #fff !important;
            border: none !important;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0,255,51,0.12);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .btn.btn-secondary:hover, .btn.btn-secondary:focus {
            background: linear-gradient(90deg, #222 0%, #00FF66 100%) !important;
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
                        <small class="text-white-50">Mahasiswa Berprestasi</small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            <span class="d-none d-lg-inline">Dashboard</span>
                        </a>
                        <a class="nav-link" href="mahasiswa.php">
                            <i class="fas fa-users me-2"></i>
                            <span class="d-none d-lg-inline">Data Mahasiswa</span>
                        </a>
                        <a class="nav-link active" href="kriteria.php">
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
                        <a class="nav-link" href="dokumen.php">
                            <i class="fas fa-file-alt me-2"></i>
                            <span class="d-none d-lg-inline">Dokumen Pendukung</span>
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
                            <h4 class="mb-0">Edit Kriteria</h4>
                            <div class="navbar-nav ms-auto">
                                <a href="kriteria.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </nav>
                    
                    <div class="p-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-edit"></i> Edit Data Kriteria
                                </h5>
                            </div>
                            <div class="card-body">
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
                                
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nama_kriteria" class="form-label">Nama Kriteria <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nama_kriteria" name="nama_kriteria" 
                                                   value="<?php echo htmlspecialchars($kriteria['nama_kriteria']); ?>" 
                                                   required>
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="bobot" class="form-label">Bobot (0-1) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="bobot" name="bobot" 
                                                   step="0.01" min="0" max="1" 
                                                   value="<?php echo $kriteria['bobot']; ?>" 
                                                   required>
                                            <div class="form-text">Contoh: 0.25</div>
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="jenis" class="form-label">Jenis <span class="text-danger">*</span></label>
                                            <select class="form-select" id="jenis" name="jenis" required>
                                                <option value="">Pilih Jenis</option>
                                                <option value="benefit" <?php echo ($kriteria['jenis'] == 'benefit') ? 'selected' : ''; ?>>Benefit</option>
                                                <option value="cost" <?php echo ($kriteria['jenis'] == 'cost') ? 'selected' : ''; ?>>Cost</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="kriteria.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Batal
                                        </a>
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-save"></i> Update
                                        </button>
                                    </div>
                                </form>
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
</body>
</html> 