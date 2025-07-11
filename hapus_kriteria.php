<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Cek login
requireLogin();

// Ambil ID kriteria dari parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: kriteria.php");
    exit();
}

try {
    // Hapus data kriteria
    $stmt = $pdo->prepare("DELETE FROM kriteria WHERE id_kriteria = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        // Redirect dengan pesan sukses
        header("Location: kriteria.php?success=Data kriteria berhasil dihapus!");
    } else {
        // Redirect dengan pesan error
        header("Location: kriteria.php?error=Data kriteria tidak ditemukan!");
    }
} catch (PDOException $e) {
    // Redirect dengan pesan error
    header("Location: kriteria.php?error=Gagal menghapus data kriteria!");
}

exit();
?> 