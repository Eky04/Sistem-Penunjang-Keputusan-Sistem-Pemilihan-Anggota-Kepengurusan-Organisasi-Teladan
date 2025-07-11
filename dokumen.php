<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Cek login
requireLogin();

// Proses form pengajuan dokumen
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_mhs = trim($_POST['nama_mhs'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');
    $judul_kegiatan = trim($_POST['judul_kegiatan'] ?? '');
    $tingkat_prestasi = trim($_POST['tingkat_prestasi'] ?? '');
    $kategori_peserta = trim($_POST['kategori_peserta'] ?? '');
    $file = $_FILES['file'] ?? null;
    if ($nama_mhs === '' || $jabatan === '' || $judul_kegiatan === '' || $tingkat_prestasi === '' || $kategori_peserta === '' || !$file || $file['error'] !== 0) {
        $error = 'Semua field dan file wajib diisi!';
    } else {
        $target_dir = 'uploads/';
        if (!is_dir($target_dir)) mkdir($target_dir);
        $filename = time() . '_' . basename($file['name']);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $stmt = $pdo->prepare('INSERT INTO dokumen (nama_mhs, jabatan, jurusan, judul_kegiatan, tingkat_prestasi, kategori_peserta, file, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$nama_mhs, $jabatan, $jurusan, $judul_kegiatan, $tingkat_prestasi, $kategori_peserta, $filename, 'Menunggu']);
            $success = 'Pengajuan dokumen berhasil!';
        } else {
            $error = 'Upload file gagal!';
        }
    }
}

// Proses update status dokumen oleh admin
if (isset($_GET['aksi']) && isset($_GET['id']) && in_array($_GET['aksi'], ['setujui','tolak'])) {
    $id = intval($_GET['id']);
    $new_status = $_GET['aksi'] === 'setujui' ? 'Disetujui' : 'Ditolak';
    $stmt = $pdo->prepare('UPDATE dokumen SET status=? WHERE id_dokumen=?');
    $stmt->execute([$new_status, $id]);
    header('Location: dokumen.php?notif=status');
    exit();
}

// Ambil daftar dokumen
$stmt = $pdo->query('SELECT * FROM dokumen ORDER BY id_dokumen DESC');
$dokumen_list = $stmt->fetchAll();

// Handler hapus dokumen
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $pdo->prepare('SELECT file FROM dokumen WHERE id_dokumen=?');
    $stmt->execute([$id]);
    $file = $stmt->fetchColumn();
    if ($file && file_exists('uploads/'.$file)) {
        unlink('uploads/'.$file);
    }
    $stmt = $pdo->prepare('DELETE FROM dokumen WHERE id_dokumen=?');
    $stmt->execute([$id]);
    header('Location: dokumen.php?notif=hapus');
    exit();
}

// Handler edit dokumen
if (isset($_POST['edit_id_dokumen'])) {
    $id = intval($_POST['edit_id_dokumen']);
    $nama = trim($_POST['edit_nama_mhs']);
    $jabatan = trim($_POST['edit_jabatan']);
    $jurusan = trim($_POST['edit_jurusan'] ?? '');
    $judul = trim($_POST['edit_judul_kegiatan']);
    $tingkat = trim($_POST['edit_tingkat_prestasi']);
    $kategori = trim($_POST['edit_kategori_peserta']);
    $stmt = $pdo->prepare('UPDATE dokumen SET nama_mhs=?, jabatan=?, jurusan=?, judul_kegiatan=?, tingkat_prestasi=?, kategori_peserta=? WHERE id_dokumen=?');
    $stmt->execute([$nama, $jabatan, $jurusan, $judul, $tingkat, $kategori, $id]);
    header('Location: dokumen.php?notif=edit');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen Pendukung - Sistem SPK SAW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.0/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* :root {
            --primary: #7B61FF;
            --secondary: #5A8DEE;
            --background: #EDEAFF;
            --accent: #4B6286;
        } */
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

        .card-header, .content-card-header, .table thead th {
            background: linear-gradient(90deg, #001a08 0%, #00FF33 100%) !important;
            color: #fff !important;
            border-radius: 22px 22px 0 0;
            border: none;
            font-weight: 700;
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


        label, .form-label, .form-control, .form-select, h2, h3, h4, h5, h6 {
            color: #000 !important;
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
                        <a class="nav-link active" href="dokumen.php">
                            <i class="fas fa-folder-open me-2"></i>
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
                        <h4 class="mb-4"><i class="fas fa-folder-open"></i> Dokumen Pendukung</h4>
                        <?php if ($success): ?>
                            <div class="alert alert-success"> <?php echo $success; ?> </div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger"> <?php echo $error; ?> </div>
                        <?php elseif (isset($_GET['notif']) && $_GET['notif']==='status'): ?>
                            <div class="alert alert-info">Status dokumen berhasil diubah.</div>
                        <?php endif; ?>
                        <div class="mb-4">
                            <form method="post" enctype="multipart/form-data" class="row g-3">
                                <h5 class="mb-3">Form Pengajuan Dokumen Pendukung</h5>
                                <div class="col-md-4">
                                    <label class="form-label">Nama Anggota Kepengurusan <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_mhs" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                                    <select name="jabatan" class="form-select" required>
                                        <option value="">Pilih...</option>
                                        <option value="Ketua">Ketua</option>
                                        <option value="Wakil Ketua">Wakil Ketua</option>
                                        <option value="Bendahara">Bendahara</option>
                                        <option value="Sekretaris">Sekretaris</option>
                                        <option value="Kepala Divisi">Kepala Divisi</option>
                                        <option value="Anggota">Anggota</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <!-- <label class="form-label">Jurusan <span class="text-danger">*</span></label> -->
                                    <!-- <input type="text" name="jurusan" class="form-control" required> -->
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Judul/Nama Kegiatan <span class="text-danger">*</span></label>
                                    <input type="text" name="judul_kegiatan" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tingkat Prestasi <span class="text-danger">*</span></label>
                                    <select name="tingkat_prestasi" class="form-select" required>
                                        <option value="">Pilih...</option>
                                        <option value="Regional">Regional</option>
                                        <option value="Nasional">Nasional</option>
                                        <option value="Internasional">Internasional</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Kategori Peserta <span class="text-danger">*</span></label>
                                    <select name="kategori_peserta" class="form-select" required>
                                        <option value="">Pilih...</option>
                                        <option value="Individu">Individu</option>
                                        <option value="Tim">Tim</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Upload File <span class="text-danger">*</span></label>
                                    <input type="file" name="file" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-upload"></i> Ajukan Dokumen</button>
                                </div>
                            </form>
                        </div>
                        <div class="card p-3">
                            <h5 class="mb-3">Daftar Dokumen Pendukung</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Anggota Kepengurusan</th>
                                            <!-- <th>NIM</th> -->
                                            <!-- <th>Jurusan</th> -->
                                             <th>Jabatan</th>   
                                            <th>Judul/Nama Kegiatan</th>
                                            <th>Tingkat Prestasi</th>
                                            <th>Kategori Peserta</th>
                                            <th>Status</th>
                                            <th>File</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($dokumen_list)): ?>
                                            <tr><td colspan="10" class="text-center text-muted">Belum ada dokumen</td></tr>
                                        <?php else: foreach ($dokumen_list as $i => $dok): ?>
                                            <tr>
                                                <td><?php echo $i+1; ?></td>
                                                <td><?php echo htmlspecialchars($dok['nama_mhs']); ?></td>
                                                <td><?php echo htmlspecialchars($dok['jabatan'] ?? '-'); ?></td>
                                                <!-- <td><?php echo htmlspecialchars($dok['jurusan']); ?></td> -->
                                                <td><?php echo htmlspecialchars($dok['judul_kegiatan']); ?></td>
                                                <td><?php echo htmlspecialchars($dok['tingkat_prestasi']); ?></td>
                                                <td><?php echo htmlspecialchars($dok['kategori_peserta']); ?></td>
                                                <td><span class="badge bg-<?php echo $dok['status']==='Disetujui'?'success':($dok['status']==='Ditolak'?'danger':'warning'); ?>"> <?php echo htmlspecialchars($dok['status']); ?> </span></td>
                                                <td>
                                                    <a href="uploads/<?php echo htmlspecialchars($dok['file']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php if ($dok['status']==='Menunggu') : ?>
                                                        <a href="?aksi=setujui&id=<?php echo $dok['id_dokumen']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Setujui dokumen ini?')"><i class="fas fa-check"></i></a>
                                                        <a href="?aksi=tolak&id=<?php echo $dok['id_dokumen']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tolak dokumen ini?')"><i class="fas fa-times"></i></a>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-info btn-sm ms-1" title="Pratinjau" onclick="previewDokumen('uploads/<?php echo htmlspecialchars($dok['file']); ?>')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-warning btn-sm ms-1" title="Edit" onclick="editDokumen(
                                                        <?php echo $dok['id_dokumen']; ?>,
                                                        '<?php echo htmlspecialchars(addslashes($dok['nama_mhs'] ?? '')); ?>',
                                                        '<?php echo htmlspecialchars(addslashes($dok['jabatan'] ?? '')); ?>',
                                                        // '<?php echo htmlspecialchars(addslashes($dok['jurusan'] ?? '')); ?>',
                                                        '<?php echo htmlspecialchars(addslashes($dok['judul_kegiatan'] ?? '')); ?>',
                                                        '<?php echo htmlspecialchars(addslashes($dok['tingkat_prestasi'] ?? '')); ?>',
                                                        '<?php echo htmlspecialchars(addslashes($dok['kategori_peserta'] ?? '')); ?>'
                                                    )">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm ms-1" title="Hapus" onclick="hapusDokumen(<?php echo $dok['id_dokumen']; ?>, '<?php echo htmlspecialchars(addslashes($dok['nama_mhs'])); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Preview Dokumen -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="previewModalLabel">Pratinjau Dokumen</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center" id="previewBody">
            <div class="text-muted">Memuat pratinjau...</div>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal Edit Dokumen -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <form method="post" id="formEditDokumen">
            <div class="modal-header">
              <h5 class="modal-title" id="editModalLabel">Edit Dokumen</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="edit_id_dokumen" id="edit_id_dokumen">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Nama Mahasiswa</label>
                  <input type="text" name="edit_nama_mhs" id="edit_nama_mhs" class="form-control" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">JABATAN</label>
                  <select name="edit_jabatan" id="edit_jabatan" class="form-select" required>
                    <option value="Ketua">Ketua</option>
                    <option value="Wakil Ketua">Wakil Ketua</option>
                    <option value="Bendahara">Bendahara</option>
                    <option value="Sekretaris">Sekretaris</option>
                    <option value="Kepala Divisi">Kepala Divisi</option>
                    <option value="Anggota">Anggota</option>
                  </select>
                  <!-- <input type="text" name="edit_npm" id="edit_npm" class="form-control" required> -->
                </div>
                <!-- <div class="col-md-4">
                  <label class="form-label">Jurusan</label>
                  <input type="text" name="edit_jurusan" id="edit_jurusan" class="form-control" required>
                </div> -->
                <div class="col-md-6">
                  <label class="form-label">Judul/Nama Kegiatan</label>
                  <input type="text" name="edit_judul_kegiatan" id="edit_judul_kegiatan" class="form-control" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Tingkat Prestasi</label>
                  <select name="edit_tingkat_prestasi" id="edit_tingkat_prestasi" class="form-select" required>
                    <option value="Regional">Regional</option>
                    <option value="Nasional">Nasional</option>
                    <option value="Internasional">Internasional</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Kategori Peserta</label>
                  <select name="edit_kategori_peserta" id="edit_kategori_peserta" class="form-select" required>
                    <option value="Individu">Individu</option>
                    <option value="Tim">Tim</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function previewDokumen(fileUrl) {
        var ext = fileUrl.split('.').pop().toLowerCase();
        var html = '';
        if(['jpg','jpeg','png','gif','bmp','webp'].includes(ext)) {
            html = '<img src="'+fileUrl+'" alt="Preview" style="max-width:100%;max-height:70vh;border-radius:12px;box-shadow:0 2px 12px rgba(76,98,134,0.10);">';
            document.getElementById('previewBody').innerHTML = html;
            var modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        } else if(ext === 'pdf') {
            window.open(fileUrl, '_blank');
        } else {
            html = '<div class="alert alert-warning">Pratinjau hanya tersedia untuk file gambar dan PDF.</div>';
            document.getElementById('previewBody').innerHTML = html;
            var modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        }
    }
    function editDokumen(id, nama, jabatan, jurusan, judul, tingkat, kategori) {
        if(document.getElementById('edit_id_dokumen')) document.getElementById('edit_id_dokumen').value = id;
        if(document.getElementById('edit_nama_mhs')) document.getElementById('edit_nama_mhs').value = nama;
        if(document.getElementById('edit_jabatan')) document.getElementById('edit_jabatan').value = jabatan;
        if(document.getElementById('edit_jurusan')) document.getElementById('edit_jurusan').value = jurusan;
        if(document.getElementById('edit_judul_kegiatan')) document.getElementById('edit_judul_kegiatan').value = judul;
        if(document.getElementById('edit_tingkat_prestasi')) document.getElementById('edit_tingkat_prestasi').value = tingkat;
        if(document.getElementById('edit_kategori_peserta')) document.getElementById('edit_kategori_peserta').value = kategori;
        var modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
    }
    function hapusDokumen(id, nama) {
        if(confirm('Hapus dokumen atas nama "'+nama+'"?')) {
            window.location.href = 'dokumen.php?hapus='+id;
        }
    }
    </script>
</body>
</html> 