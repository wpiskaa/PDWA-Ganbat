<?php
// ============================================================
//  GANBAT - Sistem Manajemen Tugas
//  File   : src/config/auth_guard.php
//  Tugas  : Gandhi Muhammad Bagas Saputra (Auth Fix)
// ============================================================
//
//  Guard ini WAJIB di-include di bagian PALING ATAS setiap
//  halaman yang membutuhkan login (seluruh halaman kecuali
//  login.php dan register.php).
//
//  Cara pakai (satu baris di atas file teman-teman):
//      require_once __DIR__ . '/../src/config/auth_guard.php';
//
//  Jika user belum login, otomatis diarahkan ke login.php.
// ============================================================

// Mulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    // Simpan halaman yang dituju agar bisa redirect balik setelah login (opsional)
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';

    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// ---- Variabel siap pakai setelah guard lolos ----
// Teman-teman bisa langsung pakai $auth_user di view mereka
// tanpa perlu query ulang untuk data dasar.
$auth_user = [
    'id'       => (int) $_SESSION['user_id'],
    'username' => $_SESSION['username'] ?? 'User',
];