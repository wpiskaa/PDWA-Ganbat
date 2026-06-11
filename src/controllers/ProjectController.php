<?php
/**
 * Ganbat V2 Task Management System
 * Controller: Project Controller (Archive Module)
 * Author: Rafie Rasydan Wahyudi
 */

session_start();
require_once '../config/database.php'; // Sesuaikan dengan file koneksi database kalian

// Cek apakah user sudah login (Mencegah Bypass)
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../public/login.php');
    exit();
}

// Cek apakah request yang masuk adalah POST dan actionnya adalah 'archive'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'archive') {
    
    // Ambil data dari form
    $project_id = $_POST['project_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // PERLINDUNGAN: Pastikan hanya owner_id yang bisa meng-archive project ini
        $query = "UPDATE projects SET is_archived = 1 WHERE id = :project_id AND owner_id = :owner_id";
        
        // Asumsi koneksi PDO disimpan di variabel $pdo (Sesuaikan dengan config tim kalian)
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':project_id' => $project_id,
            ':owner_id' => $user_id
        ]);

        // Cek apakah ada baris yang berubah (jika 0, mungkin dia bukan owner atau project tidak ada)
        if ($stmt->rowCount() > 0) {
            // Sukses Archive
            header('Location: ../../public/index.php?status=archived_success');
            exit();
        } else {
            // Gagal Archive (Bukan owner)
            header('Location: ../../public/index.php?error=unauthorized_archive');
            exit();
        }

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
} else {
    // Jika diakses langsung via URL (GET Request), tendang balik ke dashboard
    header('Location: ../../public/index.php');
    exit();
}