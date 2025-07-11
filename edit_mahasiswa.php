<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Cek login
requireLogin();

$success = '';
$error = '';
$mahasiswa = null;

// Ambil ID mahasiswa dari parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: mahasiswa.php");
    exit();
}

// Ambil data mahasiswa
$stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = ?");
$stmt->execute([$id]);
$mahasiswa = $stmt->fetch();

if (!$mahasiswa) {
    header("Location: mahasiswa.php");
    exit();
}

// Ambil semua kriteria dari database
$stmt = $pdo->query("SELECT * FROM kriteria ORDER BY nama_kriteria ASC");
$kriteria_list = $stmt->fetchAll();

// Ambil nilai kriteria untuk anggota ini
$nilai_stmt = $pdo->prepare("SELECT id_kriteria, nilai FROM nilai WHERE id_mahasiswa = ?");
$nilai_stmt->execute([$id]);
$nilai_mahasiswa = $nilai_stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [id_kriteria => nilai]

// Proses form update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nama = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $nilai_kriteria = $_POST['nilai'] ?? [];
    
    // Validasi
    if (empty($nama) || empty($jabatan)) {
        $error = 'Nama dan Jabatan harus diisi!';
    } elseif (count($nilai_kriteria) !== count($kriteria_list)) {
        $error = 'Semua nilai kriteria harus diisi!';
    } else {
        $valid = true;
        foreach ($kriteria_list as $kriteria) {
            $idk = $kriteria['id_kriteria'];
            if (!isset($nilai_kriteria[$idk]) || $nilai_kriteria[$idk] === '') {
                $valid = false;
                break;
            }
        }
        if (!$valid) {
            $error = 'Semua nilai kriteria harus diisi!';
    } else {
        try {
                // Update data mahasiswa
                $stmt = $pdo->prepare("UPDATE mahasiswa SET nama = ?, jabatan = ? WHERE id_mahasiswa = ?");
                $stmt->execute([$nama, $jabatan, $id]);
                // Hapus nilai lama
                $stmt = $pdo->prepare("DELETE FROM nilai WHERE id_mahasiswa = ?");
                $stmt->execute([$id]);
                // Simpan nilai baru
                foreach ($kriteria_list as $kriteria) {
                    $idk = $kriteria['id_kriteria'];
                    $nilai = $nilai_kriteria[$idk];
                    $stmt2 = $pdo->prepare("INSERT INTO nilai (id_mahasiswa, id_kriteria, nilai) VALUES (?, ?, ?)");
                    $stmt2->execute([$id, $idk, $nilai]);
                }
                $success = 'Data anggota berhasil diperbarui!';
                $mahasiswa['nama'] = $nama;
                $mahasiswa['jabatan'] = $jabatan;
                $nilai_mahasiswa = $nilai_kriteria;
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mahasiswa - Sistem SPK SAW</title>
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
            /* margin: 6px 8px;
            font-weight: 500;
            transition: background 0.2s, color 0.2s; */
        }
        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .sidebar hr {
            border-color: rgba(255,255,255,0.2) !important;
            opacity: 1;
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
            /* background: #00FF33 !important;
            min-height: 100vh; */
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
                        <a class="nav-link active" href="mahasiswa.php">
                            <i class="fas fa-users me-2"></i>
                            <span class="d-none d-lg-inline">Data Mahasiswa</span>
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
                            <h4 class="mb-0">Edit Mahasiswa</h4>
                            <div class="navbar-nav ms-auto">
                                <a href="mahasiswa.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </nav>
                    
                    <div class="p-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-edit"></i> Edit Data Anggota Kepengurusan
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
                                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nama" name="nama" 
                                                   value="<?php echo htmlspecialchars($mahasiswa['nama']); ?>" 
                                                   required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="jabatan" class="form-label">Jabatan <span class="text-danger">*</span></label>
                                            <select class="form-control" id="jabatan" name="jabatan" required>
                                                <option value="">-- Pilih Jabatan --</option>
                                                <option value="Sekretaris" <?php if(($mahasiswa['jabatan'] ?? '') === 'Sekretaris') echo 'selected'; ?>>Sekretaris</option>
                                                <option value="Kepala Divisi" <?php if(($mahasiswa['jabatan'] ?? '') === 'Kepala Divisi') echo 'selected'; ?>>Kepala Divisi</option>
                                                <option value="Bendahara" <?php if(($mahasiswa['jabatan'] ?? '') === 'Bendahara') echo 'selected'; ?>>Bendahara</option>
                                                <option value="Ketua" <?php if(($mahasiswa['jabatan'] ?? '') === 'Ketua') echo 'selected'; ?>>Ketua</option>
                                            </select>
                                        </div>
                                    </div>
                                    <?php foreach ($kriteria_list as $kriteria): ?>
                                        <div class="mb-3">
                                            <label for="kriteria_<?php echo $kriteria['id_kriteria']; ?>" class="form-label">
                                                <?php echo htmlspecialchars($kriteria['nama_kriteria']); ?>
                                            </label>
                                            <input type="number" step="any" min="0" max="100" class="form-control"
                                                   id="kriteria_<?php echo $kriteria['id_kriteria']; ?>"
                                                   name="nilai[<?php echo $kriteria['id_kriteria']; ?>]"
                                                   value="<?php echo isset($nilai_mahasiswa[$kriteria['id_kriteria']]) ? htmlspecialchars($nilai_mahasiswa[$kriteria['id_kriteria']]) : ''; ?>"
                                                   required>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="mahasiswa.php" class="btn btn-secondary">
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