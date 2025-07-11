<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

requireLogin();

$success = '';
$error = '';

// Ambil semua kriteria dari database
$stmt = $pdo->query("SELECT * FROM kriteria ORDER BY nama_kriteria ASC");
$kriteria_list = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $jabatan = trim($_POST['jabatan']);
    $nilai_kriteria = $_POST['nilai'] ?? [];

    if (empty($nama) || empty($jabatan)) {
        $error = 'Nama dan Jabatan harus diisi!';
    } elseif (count($nilai_kriteria) !== count($kriteria_list)) {
        $error = 'Semua nilai kriteria harus diisi!';
    } else {
        // Validasi nilai kriteria (opsional: bisa tambahkan validasi range per kriteria)
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
                // Simpan data mahasiswa
                $stmt = $pdo->prepare("INSERT INTO mahasiswa (nama, jabatan) VALUES (?, ?)");
                $stmt->execute([$nama, $jabatan]);
                $id_mahasiswa = $pdo->lastInsertId();
                // Simpan nilai kriteria
                foreach ($kriteria_list as $kriteria) {
                    $idk = $kriteria['id_kriteria'];
                    $nilai = $nilai_kriteria[$idk];
                    $stmt2 = $pdo->prepare("INSERT INTO nilai (id_mahasiswa, id_kriteria, nilai) VALUES (?, ?, ?)");
                    $stmt2->execute([$id_mahasiswa, $idk, $nilai]);
                }
                $success = 'Data Anggota berhasil ditambahkan!';
                $_POST = array();
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
    <title>Tambah Anggota - SPK SAW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    :root {
      --primary: #000000;
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
        background: linear-gradient(180deg, #001a08 0%, #00FF33 100%);
        border-radius: 22px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.18);
        padding: 32px 28px;
        margin: 0 auto;
        max-width: 98%;
        border: none;
        opacity: 0.97;
    }
    .content-card h2, .content-card h3, .content-card h4, .content-card h5, .content-card h6 {
        color: #fff;
    }
    .card-header {
        background: linear-gradient(90deg, #001a08 0%, #00FF33 100%);
        color: #fff;
        border-radius: 22px 22px 0 0;
        border: none;
    }
    .form-control {
        background: rgba(255,255,255,0.95);
        color: #222;
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        box-shadow: none;
        font-weight: 500;
    }
    .form-control:focus {
        border-color: #00FF33;
        box-shadow: 0 0 0 2px #00FF3340;
    }
    .label {
        color: #fff;
        font-weight: 500;
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
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tambah Data Anggota</h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success"> <?php echo $success; ?> </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"> <?php echo $error; ?> </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>JABATAN</label>
                            <select name="jabatan" class="form-control" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <option value="Sekretaris" <?php if(isset($_POST['jabatan']) && $_POST['jabatan']==='Sekretaris') echo 'selected'; ?>>Sekretaris</option>
                                <option value="Ketua Divisi" <?php if(isset($_POST['jabatan']) && $_POST['jabatan']==='Kepala Divisi') echo 'selected'; ?>>Kepala Divisi</option>
                                <option value="Bendahara" <?php if(isset($_POST['jabatan']) && $_POST['jabatan']==='Bendahara') echo 'selected'; ?>>Bendahara</option>
                                <option value="Ketua" <?php if(isset($_POST['jabatan']) && $_POST['jabatan']==='Ketua') echo 'selected'; ?>>Ketua</option>
                                <option value="Anggota" <?php if(isset($_POST['jabatan']) && $_POST['jabatan']==='Anggota') echo 'selected'; ?>>Anggota</option>
                            </select>
                        </div>
                        <?php foreach ($kriteria_list as $kriteria): ?>
                            <div class="mb-3">
                                <label for="kriteria_<?php echo $kriteria['id_kriteria']; ?>" class="form-label">
                                    <?php echo htmlspecialchars($kriteria['nama_kriteria']); ?>
                                </label>
                                <input type="number" step="any" min="0" max="100" class="form-control"
                                       id="kriteria_<?php echo $kriteria['id_kriteria']; ?>"
                                       name="nilai[<?php echo $kriteria['id_kriteria']; ?>]"
                                       value="<?php echo isset($_POST['nilai'][$kriteria['id_kriteria']]) ? htmlspecialchars($_POST['nilai'][$kriteria['id_kriteria']]) : ''; ?>"
                                       required>
                            </div>
                        <?php endforeach; ?>
                        <div class="d-flex justify-content-between">
                            <a href="mahasiswa.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <p style="position:fixed;left:0;right:0;bottom:18px;text-align:center;color:#4B6286;opacity:0.85;z-index:9999;font-size:16px;font-weight:500;letter-spacing:0.5px;pointer-events:none;">Copyright &#169; 2025 Muhammad Rizky. All rights reserved.</p>
</div>
<style>
.sidebar .nav-link {
    text-align: center;
    padding-left: 0;
    padding-right: 0;
}
@media (min-width: 992px) {
    .sidebar .nav-link {
        text-align: left;
        padding-left: 20px;
        padding-right: 20px;
    }
}
</style>
</body>
</html> 