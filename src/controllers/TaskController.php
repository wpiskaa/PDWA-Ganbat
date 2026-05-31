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

$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create_task':
            createTask($pdo);
            break;


        default:
            header('Location: ../../public/index.php');
            exit;
    }
}