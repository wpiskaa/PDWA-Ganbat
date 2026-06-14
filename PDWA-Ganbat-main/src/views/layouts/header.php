<?php
/**
 * ============================================================
 *  GANBAT - Sistem Manajemen Tugas
 *  File   : src/views/layouts/header.php
 *  Tugas  : Gandhi Muhammad Bagas Saputra (Core Configuration)
 * ============================================================
 *
 *  Bagian pembuka HTML yang dipakai SEMUA halaman.
 *  Cukup panggil di awal tiap halaman:
 *
 *      <?php $pageTitle = "Dashboard"; ?>
 *      <?php require_once __DIR__ . '/../src/views/layouts/header.php'; ?>
 *
 *  (Variabel $pageTitle opsional, untuk judul tab browser.)
 * ============================================================
 */
 
// Mulai session kalau belum jalan (dibutuhkan modul login - Hafiz)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — Ganbat' : 'Ganbat' ?></title>
 
    <!-- Tailwind CSS via CDN (sesuai stack proyek) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
    <!-- Isi tiap halaman akan ditulis setelah baris ini -->