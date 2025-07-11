<?php
session_start();

// Fungsi untuk cek apakah admin sudah login
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Fungsi untuk redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Fungsi untuk logout
function logout() {
    session_destroy();
    header("Location: login.php");
    exit();
}
?> 