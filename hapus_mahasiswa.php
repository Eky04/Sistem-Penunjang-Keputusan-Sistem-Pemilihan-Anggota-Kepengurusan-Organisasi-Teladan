<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Cek login
requireLogin();

// Ambil ID mahasiswa dari parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: mahasiswa.php");
    exit();
}

try {
    // Hapus data mahasiswa
    $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id_mahasiswa = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        // Redirect dengan pesan sukses
        header("Location: mahasiswa.php?success=Data mahasiswa berhasil dihapus!");
    } else {
        // Redirect dengan pesan error
        header("Location: mahasiswa.php?error=Data mahasiswa tidak ditemukan!");
    }
} catch (PDOException $e) {
    // Redirect dengan pesan error
    header("Location: mahasiswa.php?error=Gagal menghapus data mahasiswa!");
}

exit();
?> 