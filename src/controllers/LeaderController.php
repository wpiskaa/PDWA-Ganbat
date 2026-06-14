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
    case 'reassign_subtask':
        $subtask_id = (int) ($_POST['subtask_id'] ?? 0);
        $new_user_id = (int) ($_POST['new_user_id'] ?? 0);
        $project_id = (int) ($_POST['project_id'] ?? 0);

        if ($subtask_id <= 0 || $new_user_id <= 0 || $project_id <= 0) {
            $_SESSION['error'] = 'Data tidak valid.';
            header('Location: ../../public/index.php');
            exit;
        }

        // Check current user is project owner
        $stmt = $pdo->prepare('SELECT id, title FROM projects WHERE id = ? AND owner_id = ?');
        $stmt->execute([$project_id, $user_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$project) {
            $_SESSION['error'] = 'Anda tidak memiliki izin untuk mengubah penugasan.';
            header('Location: ../../public/index.php');
            exit;
        }

        // Check new_user_id is accepted member
        $stmt = $pdo->prepare('SELECT user_id FROM project_members WHERE project_id = ? AND user_id = ? AND status_invite = ?');
        $stmt->execute([$project_id, $new_user_id, 'accepted']);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = 'User tujuan bukan anggota aktif proyek ini.';
            header('Location: ../../public/project_detail.php?id=' . $project_id);
            exit;
        }

        // Update subtask assignment
        $stmt = $pdo->prepare('UPDATE subtasks SET assigned_to = ? WHERE id = ? AND project_id = ?');
        $stmt->execute([$new_user_id, $subtask_id, $project_id]);

        // Get subtask title for notification
        $stmt = $pdo->prepare('SELECT title FROM subtasks WHERE id = ?');
        $stmt->execute([$subtask_id]);
        $subtask = $stmt->fetch(PDO::FETCH_ASSOC);

        // Create notification for new assignee
        if ($new_user_id != $user_id) {
            $message = $_SESSION['username'] . ' menugaskan ulang Anda pada tugas "' . $subtask['title'] . '" di proyek "' . $project['title'] . '".';
            $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, message, reference_id, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())');
            $stmt->execute([$new_user_id, 'task_reassigned', $message, $project_id]);
        }

        $_SESSION['success'] = 'Tugas berhasil ditugaskan ulang.';
        header('Location: ../../public/project_detail.php?id=' . $project_id);
        exit;

    case 'kick_member':
        $project_id = (int) ($_POST['project_id'] ?? 0);
        $kick_user_id = (int) ($_POST['user_id'] ?? 0);

        if ($project_id <= 0 || $kick_user_id <= 0) {
            $_SESSION['error'] = 'Data tidak valid.';
            header('Location: ../../public/index.php');
            exit;
        }

        // Check current user is project owner
        $stmt = $pdo->prepare('SELECT id, title FROM projects WHERE id = ? AND owner_id = ?');
        $stmt->execute([$project_id, $user_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$project) {
            $_SESSION['error'] = 'Anda tidak memiliki izin untuk mengeluarkan anggota.';
            header('Location: ../../public/index.php');
            exit;
        }

        // Can't kick self (owner)
        if ($kick_user_id == $user_id) {
            $_SESSION['error'] = 'Anda tidak bisa mengeluarkan diri sendiri dari proyek.';
            header('Location: ../../public/project_detail.php?id=' . $project_id);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Delete from project_members
            $stmt = $pdo->prepare('DELETE FROM project_members WHERE project_id = ? AND user_id = ?');
            $stmt->execute([$project_id, $kick_user_id]);

            // Unassign subtasks belonging to kicked member
            $stmt = $pdo->prepare('UPDATE subtasks SET assigned_to = NULL WHERE project_id = ? AND assigned_to = ?');
            $stmt->execute([$project_id, $kick_user_id]);

            $pdo->commit();

            $_SESSION['success'] = 'Anggota berhasil dikeluarkan dari proyek.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Gagal mengeluarkan anggota. Silakan coba lagi.';
        }

        header('Location: ../../public/project_detail.php?id=' . $project_id);
        exit;

    default:
        header('Location: ../../public/index.php');
        exit;
}