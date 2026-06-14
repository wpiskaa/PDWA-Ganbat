<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../public/login.php');
    exit;
}

function createTask(PDO $pdo): void
{
    $title       = htmlspecialchars(trim($_POST['title'] ?? ''), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');
    $priority    = htmlspecialchars(trim($_POST['priority'] ?? 'medium'), ENT_QUOTES, 'UTF-8');
    $deadline    = $_POST['deadline_date'] ?? null;

    if (empty($title)) {
        $_SESSION['error'] = "Judul tugas tidak boleh kosong!";
        header('Location: ../../public/index.php');
        exit;
    }

    $allowedPriorities = ['low', 'medium', 'high'];
    if (!in_array($priority, $allowedPriorities)) {
        $priority = 'medium';
    }

    if (!empty($deadline)) {
        $dateCheck = DateTime::createFromFormat('Y-m-d', $deadline);
        if (!$dateCheck) {
            $_SESSION['error'] = "Format tanggal deadline tidak valid!";
            header('Location: ../../public/index.php');
            exit;
        }
    } else {
        $deadline = null;
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO tasks (title, description, priority, status, deadline_date)
             VALUES (:title, :description, :priority, 'todo', :deadline_date)"
        );

        $stmt->bindParam(':title',         $title,       PDO::PARAM_STR);
        $stmt->bindParam(':description',   $description, PDO::PARAM_STR);
        $stmt->bindParam(':priority',      $priority,    PDO::PARAM_STR);
        $stmt->bindParam(':deadline_date', $deadline,    PDO::PARAM_STR);

        $stmt->execute();

        $_SESSION['success'] = "Tugas berhasil dibuat!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Gagal membuat tugas. Silakan coba lagi.";
    }

    header('Location: ../../public/index.php');
    exit;
}

function updateTaskStatus(PDO $pdo): void
{
    $task_id    = isset($_POST['task_id']) ? (int) $_POST['task_id'] : 0;
    $new_status = trim($_POST['status'] ?? '');

    // Whitelist status yang diizinkan
    $allowed = ['todo', 'doing', 'done'];

    if ($task_id <= 0 || !in_array($new_status, $allowed, true)) {
        $_SESSION['error'] = "Status tidak valid.";
        header('Location: ../../public/index.php');
        exit;
    }

    try {
        // Prepared statement — aman dari SQL Injection
        $stmt = $pdo->prepare("UPDATE tasks SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
        $stmt->bindParam(':id',     $task_id,    PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['success'] = "Status tugas berhasil diperbarui!";
    } catch (PDOException $e) {
        error_log('[TaskController] updateTaskStatus error: ' . $e->getMessage());
        $_SESSION['error'] = "Gagal memperbarui status. Silakan coba lagi.";
    }

    header('Location: ../../public/index.php');
    exit;
}

function createSubtask(PDO $pdo): void
{
    $project_id   = isset($_POST['project_id']) ? (int) $_POST['project_id'] : 0;
    $title        = htmlspecialchars(trim($_POST['title'] ?? ''), ENT_QUOTES, 'UTF-8');
    $assigned_to  = isset($_POST['assigned_to']) ? (int) $_POST['assigned_to'] : 0;
    $status       = htmlspecialchars(trim($_POST['status'] ?? 'todo'), ENT_QUOTES, 'UTF-8');
    $deadline     = $_POST['deadline_date'] ?? null;

    if ($project_id <= 0 || empty($title) || $assigned_to <= 0) {

        $_SESSION['error'] = "Data subtask tidak valid.";

        header('Location: ../../public/index.php');
        exit;
    }

    $allowedStatus = ['todo', 'ongoing', 'done'];

    if (!in_array($status, $allowedStatus, true)) {
        $status = 'todo';
    }

    try {

        $memberStmt = $pdo->prepare(
            "SELECT *
             FROM project_members
             WHERE project_id = :project_id
             AND user_id = :user_id
             AND status_invite = 'accepted'"
        );

        $memberStmt->execute([
            ':project_id' => $project_id,
            ':user_id'    => $assigned_to
        ]);

        $member = $memberStmt->fetch(PDO::FETCH_ASSOC);

        if (!$member) {

            $_SESSION['error'] = "User bukan member project.";

            header('Location: ../../public/index.php');
            exit;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO subtasks (
                project_id,
                title,
                assigned_to,
                status,
                deadline_date
            ) VALUES (
                :project_id,
                :title,
                :assigned_to,
                :status,
                :deadline_date
            )"
        );

        $stmt->execute([
            ':project_id'    => $project_id,
            ':title'         => $title,
            ':assigned_to'   => $assigned_to,
            ':status'        => $status,
            ':deadline_date' => $deadline
        ]);

        $_SESSION['success'] = "Subtask berhasil dibuat!";

    } catch (PDOException $e) {

        $_SESSION['error'] = "Gagal membuat subtask.";
    }

    header('Location: ../../public/index.php');
    exit;
}

function assignMemberToTask(PDO $pdo): void
{
    $task_id = isset($_POST['task_id']) ? (int) $_POST['task_id'] : 0;
    $user_ids = $_POST['user_ids'] ?? [];

    if ($task_id <= 0) {
        $_SESSION['error'] = "Task tidak valid.";
        header('Location: ../../public/index.php');
        exit;
    }

    try {

        $pdo->beginTransaction();

        $deleteStmt = $pdo->prepare(
            "DELETE FROM task_assignees WHERE task_id = :task_id"
        );

        $deleteStmt->bindValue(':task_id', $task_id, PDO::PARAM_INT);
        $deleteStmt->execute();

        if (!empty($user_ids)) {

            $insertStmt = $pdo->prepare(
                "INSERT INTO task_assignees (task_id, user_id)
                 VALUES (:task_id, :user_id)"
            );

            foreach ($user_ids as $user_id) {

                $insertStmt->execute([
                    ':task_id' => $task_id,
                    ':user_id' => (int)$user_id
                ]);
            }
        }

        $pdo->commit();

        $_SESSION['success'] = "Member berhasil di-assign.";

    } catch (PDOException $e) {

        $pdo->rollBack();

        $_SESSION['error'] = "Gagal assign member.";
    }

    header('Location: ../../public/index.php');
    exit;
}

// $pdo is already initialized in src/config/database.php and available in the global scope

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create_task':
            createTask($pdo);
            break;

        case 'update_status':
            updateTaskStatus($pdo);
            break;
        
        case 'create_subtask':
            createSubtask($pdo);
            break;

        case 'assign_member':
            assignMemberToTask($pdo);
            break;

        default:
            header('Location: ../../public/index.php');
            exit;
    }
}

