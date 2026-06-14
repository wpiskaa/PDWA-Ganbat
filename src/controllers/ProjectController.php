<?php
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: ../../public/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create_project':
        $title = trim($_POST['title'] ?? '');
        $description = $_POST['description'] ?? '';
        $global_deadline = !empty($_POST['global_deadline']) ? $_POST['global_deadline'] : null;

        if (empty($title)) {
            $_SESSION['error'] = 'Judul project tidak boleh kosong.';
            header('Location: ../../public/index.php');
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('INSERT INTO projects (title, description, global_deadline, owner_id, created_at) VALUES (?, ?, ?, ?, NOW())');
            $stmt->execute([$title, $description, $global_deadline, $_SESSION['user_id']]);
            $project_id = $pdo->lastInsertId();

            // Insert owner as accepted member
            $stmt = $pdo->prepare('INSERT INTO project_members (project_id, user_id, status_invite, created_at) VALUES (?, ?, ?, NOW())');
            $stmt->execute([$project_id, $user_id, 'accepted']);

            $pdo->commit();

            $_SESSION['success'] = 'Proyek berhasil dibuat!';
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Gagal membuat proyek. Silakan coba lagi.';
        }

        header('Location: ../../public/index.php');
        exit;

    case 'archive':
        $project_id = (int) ($_POST['project_id'] ?? 0);

        if ($project_id <= 0) {
            $_SESSION['error'] = 'ID proyek tidak valid.';
            header('Location: ../../public/my_project.php');
            exit;
        }

        // Check if user is member
        $stmt = $pdo->prepare('SELECT user_id FROM project_members WHERE project_id = ? AND user_id = ?');
        $stmt->execute([$project_id, $user_id]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = 'Anda tidak memiliki izin untuk mengarsipkan proyek ini.';
            header('Location: ../../public/my_project.php');
            exit;
        }

        // Archive project for current user
        $stmt = $pdo->prepare('UPDATE project_members SET is_archived = 1 WHERE project_id = ? AND user_id = ?');
        $stmt->execute([$project_id, $user_id]);

        $_SESSION['success'] = 'Proyek berhasil diarsipkan (hanya untuk Anda).';
        header('Location: ../../public/my_project.php');
        exit;

    case 'unarchive':
        $project_id = (int) ($_POST['project_id'] ?? 0);

        if ($project_id <= 0) {
            header('Location: ../../public/arsip_project.php');
            exit;
        }

        // Unarchive project for current user
        $stmt = $pdo->prepare('UPDATE project_members SET is_archived = 0 WHERE project_id = ? AND user_id = ?');
        $stmt->execute([$project_id, $user_id]);

        $_SESSION['success'] = 'Proyek berhasil dikembalikan ke daftar aktif.';
        header('Location: ../../public/arsip_project.php');
        exit;

    default:
        header('Location: ../../public/index.php');
        exit;
}