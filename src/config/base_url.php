<?php
// ============================================================
//  GANBAT - Sistem Manajemen Tugas
//  File   : src/config/base_url.php
//  Tugas  : Gandhi Muhammad Bagas Saputra (Database & Config)
// ============================================================
//
//  Mendefinisikan konstanta BASE_URL agar redirect di seluruh
//  controller & guard selalu mengarah ke folder public/ yang benar,
//  baik di localhost maupun server hosting.
//
//  File ini di-include PERTAMA KALI oleh auth_guard.php dan
//  controller manapun yang butuh redirect.
// ============================================================

if (!defined('BASE_URL')) {
    // Deteksi otomatis: http/https + host + path menuju /public/
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Ambil path sampai folder /public/ secara dinamis
    // Contoh: jika project ada di htdocs/PDWA-Ganbat/public/index.php
    //         maka BASE_URL = http://localhost/PDWA-Ganbat/public/
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Pastikan selalu diakhiri "/"
    $base = rtrim($scriptDir, '/') . '/';

    define('BASE_URL', $scheme . '://' . $host . $base);
}