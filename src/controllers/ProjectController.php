<?php
/**
 * Ganbat V2 Task Management System
 * Controller: Project Controller
 * - Archive Module — Author: Rafie Rasydan Wahyudi
 * - Create Project Module — Author: Bima Baraja
 */

session_start();
require_once '../config/database.php'; // Sesuaikan dengan file koneksi database kalian

// Cek apakah user sudah login (Mencegah Bypass)
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../public/login.php');
    exit();
}

$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    switch ($action) {

        // ============================================================
        // ARCHIVE PROJECT — by Rafie Rasydan Wahyudi
        // ============================================================
        case 'archive':
            archiveProject($pdo);
            break;

        // ============================================================
        // CREATE PROJECT — by Bima Baraja
        // ============================================================
        case 'create_project':
            createProject($pdo);
            break;

        default:
            header('Location: ../../public/index.php');
            exit();
    }

} else {
    // Jika diakses langsung via URL (GET Request), tendang balik ke dashboard
    header('Location: ../../public/index.php');
    exit();
}

/**
 * Mengubah is_archived = 1 pada project, hanya jika user adalah owner.
 */
function archiveProject(PDO $pdo): void
{
    $project_id = $_POST['project_id'];
    $user_id    = $_SESSION['user_id'];

    try {
        $query = "UPDATE projects SET is_archived = 1 WHERE id = :project_id AND owner_id = :owner_id";
        $stmt  = $pdo->prepare($query);
        $stmt->execute([
            ':project_id' => $project_id,
            ':owner_id'   => $user_id
        ]);

        if ($stmt->rowCount() > 0) {
            header('Location: ../../public/index.php?status=archived_success');
            exit();
        } else {
            header('Location: ../../public/index.php?error=unauthorized_archive');
            exit();
        }

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}

/**
 * Membuat project baru. Owner otomatis tercatat sebagai member
 * dengan status_invite 'accepted' di project_members.
 */
function createProject(PDO $pdo): void
{
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $owner_id    = $_SESSION['user_id'];

    if ($title === '') {
        $_SESSION['error'] = 'Judul project tidak boleh kosong.';
        header('Location: ../../public/index.php');
        exit();
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "INSERT INTO projects (title, description, owner_id, is_archived, created_at)
             VALUES (:title, :description, :owner_id, 0, NOW())"
        );
        $stmt->execute([
            ':title'       => $title,
            ':description' => $description,
            ':owner_id'    => $owner_id,
        ]);

        $project_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            "INSERT INTO project_members (project_id, user_id, status_invite)
             VALUES (:project_id, :user_id, 'accepted')"
        );
        $stmt->execute([
            ':project_id' => $project_id,
            ':user_id'    => $owner_id,
        ]);

        $pdo->commit();

        $_SESSION['success'] = 'Project "' . $title . '" berhasil dibuat.';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Gagal membuat project: ' . $e->getMessage();
    }

    header('Location: ../../public/index.php');
    exit();
}