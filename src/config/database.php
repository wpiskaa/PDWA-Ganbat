<?php
/**
 * ============================================================
 *  GANBAT - Sistem Manajemen Tugas
 *  File   : src/config/database.php
 *  Tugas  : Gandhi Muhammad Bagas Saputra (Database & Config)
 * ============================================================
 *
 *  File ini bertugas membuat koneksi ke MySQL menggunakan PDO.
 *  Teman-teman lain (Controller) cukup memanggil file ini lalu
 *  memakai variabel $pdo untuk menjalankan query.
 *
 *  Contoh pemakaian di Controller:
 *      require_once __DIR__ . '/../config/database.php';
 *      $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
 *      $stmt->execute([$id]);
 *      $task = $stmt->fetch();
 * ============================================================
 */
 
// --- Pengaturan koneksi (sesuaikan kalau setting MySQL kamu beda) ---
$host     = 'localhost';
$dbname   = 'ganbat';
$username = 'root';     // user default XAMPP/Laragon
$password = '';         // password default biasanya kosong
 
// --- Membuat koneksi PDO ---
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            // Lempar Exception kalau ada error (memudahkan debug)
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Hasil query otomatis berupa array asosiatif
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Pakai prepared statement asli (lebih aman dari SQL injection)
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Di tahap prototipe boleh tampilkan pesan error.
    // Saat produksi nanti sebaiknya error di-log, bukan ditampilkan.
    die('Koneksi database gagal: ' . $e->getMessage());
}
