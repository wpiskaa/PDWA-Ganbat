<?php
/**
 * ============================================================
 *  GANBAT - Sistem Manajemen Tugas
 *  File   : src/config/database.php
 *  Tugas  : Gandhi Muhammad Bagas Saputra (Database & Config)
 * ============================================================
 */

$host     = 'localhost';
$dbname   = 'ganbat';
$username = 'root';
$password = '';  // <-- Sesuaikan dengan password MySQL lokal kamu

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}
