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
    case 'create_subtask':
        $project_id = (int) ($_POST['project_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $assigned_to = !empty($_POST['assigned_to']) ? (int) $_POST['assigned_to'] : null;
        $priority = $_POST['priority'] ?? 'medium';
        $deadline_date = !empty($_POST['deadline_date']) ? $_POST['deadline_date'] : null;

        // Validate required fields
        if ($project_id <= 0 || empty($title)) {
            $_SESSION['error'] = 'Project ID dan judul tugas wajib diisi.';
            header('Location: ../../public/project_detail.php?id=' . $project_id);
            exit;
        }

        // Validate priority
        if (!in_array($priority, ['low', 'medium', 'high'])) {
            $priority = 'medium';
        }

        // Check current user is project owner
        $stmt = $pdo->prepare('SELECT id, title AS project_title FROM projects WHERE id = ? AND owner_id = ?');
        $stmt->execute([$project_id, $user_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$project) {
            $_SESSION['error'] = 'Anda tidak memiliki izin untuk menambah tugas di proyek ini.';
            header('Location: ../../public/index.php');
            exit;
        }

        // Validate assigned_to is accepted member of project
        if ($assigned_to !== null) {
            $stmt = $pdo->prepare('SELECT user_id FROM project_members WHERE project_id = ? AND user_id = ? AND status_invite = ?');
            $stmt->execute([$project_id, $assigned_to, 'accepted']);
            if (!$stmt->fetch()) {
                $_SESSION['error'] = 'User yang ditugaskan bukan anggota proyek ini.';
                header('Location: ../../public/project_detail.php?id=' . $project_id);
                exit;
            }
        }

        // Insert subtask
        $stmt = $pdo->prepare('INSERT INTO subtasks (project_id, title, description, assigned_to, status, priority, deadline_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$project_id, $title, $description, $assigned_to, 'todo', $priority, $deadline_date]);

        // Create notification for assignee
        if ($assigned_to !== null && $assigned_to !== $user_id) {
            $message = $_SESSION['username'] . ' menugaskan Anda pada tugas "' . $title . '" di proyek "' . $project['project_title'] . '".';
            $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, message, reference_id, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())');
            $stmt->execute([$assigned_to, 'task_assigned', $message, $project_id]);
        }

        $_SESSION['success'] = 'Tugas berhasil ditambahkan!';
        header('Location: ../../public/project_detail.php?id=' . $project_id);
        exit;

    case 'update_subtask_status':
        $subtask_id = (int) ($_POST['subtask_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        // Validate status
        if (!in_array($status, ['todo', 'ongoing', 'done'])) {
            $_SESSION['error'] = 'Status tidak valid.';
            header('Location: ../../public/index.php');
            exit;
        }

        // Fetch subtask with project info
        $stmt = $pdo->prepare('SELECT s.id, s.title, s.project_id, s.assigned_to, p.owner_id, p.title as project_title FROM subtasks s JOIN projects p ON s.project_id = p.id WHERE s.id = ?');
        $stmt->execute([$subtask_id]);
        $subtask = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$subtask) {
            $_SESSION['error'] = 'Tugas tidak ditemukan.';
            header('Location: ../../public/index.php');
            exit;
        }

        // Check user is either assignee or project owner
        if ($subtask['assigned_to'] != $user_id && $subtask['owner_id'] != $user_id) {
            $_SESSION['error'] = 'Anda tidak memiliki izin untuk mengubah status tugas ini.';
            header('Location: ../../public/project_detail.php?id=' . $subtask['project_id']);
            exit;
        }

        // Update status
        $stmt = $pdo->prepare('UPDATE subtasks SET status = ? WHERE id = ?');
        $stmt->execute([$status, $subtask_id]);

        // Create Activity Log
        $action_text = '';
        if ($status === 'todo') {
            $action_text = 'memindahkan ke To-Do';
        } elseif ($status === 'ongoing') {
            $action_text = 'sedang mengerjakan';
        } elseif ($status === 'done') {
            $action_text = 'telah menyelesaikan';
        }

        $log_message = sprintf('[%s] - %s %s %s', 
            $subtask['project_title'], 
            $_SESSION['username'], 
            $action_text, 
            $subtask['title']
        );
        $stmtLog = $pdo->prepare('INSERT INTO activity_logs (project_id, user_id, log_text, created_at) VALUES (?, ?, ?, NOW())');
        $stmtLog->execute([$subtask['project_id'], $user_id, $log_message]);

        $_SESSION['success'] = 'Status tugas berhasil diperbarui.';
        header('Location: ../../public/project_detail.php?id=' . $subtask['project_id']);
        exit;

    case 'delete_subtask':
        $subtask_id = (int) ($_POST['subtask_id'] ?? 0);

        // Fetch subtask
        $stmt = $pdo->prepare('SELECT s.id, s.project_id, p.owner_id FROM subtasks s JOIN projects p ON s.project_id = p.id WHERE s.id = ?');
        $stmt->execute([$subtask_id]);
        $subtask = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$subtask) {
            $_SESSION['error'] = 'Tugas tidak ditemukan.';
            header('Location: ../../public/index.php');
            exit;
        }

        // Check current user is project owner
        if ($subtask['owner_id'] != $user_id) {
            $_SESSION['error'] = 'Hanya pemilik proyek yang dapat menghapus tugas.';
            header('Location: ../../public/project_detail.php?id=' . $subtask['project_id']);
            exit;
        }

        // Delete subtask
        $stmt = $pdo->prepare('DELETE FROM subtasks WHERE id = ?');
        $stmt->execute([$subtask_id]);

        $_SESSION['success'] = 'Tugas berhasil dihapus.';
        header('Location: ../../public/project_detail.php?id=' . $subtask['project_id']);
        exit;

    default:
        header('Location: ../../public/index.php');
        exit;
}
