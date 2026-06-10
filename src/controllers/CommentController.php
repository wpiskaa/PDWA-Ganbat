<?php
require_once __DIR__ . '/../../src/config/database.php';

session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../public/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id     = $_POST['task_id'] ?? null;
    $comment_text = trim($_POST['comment_text'] ?? '');
    $user_id     = $_SESSION['user_id'];

    // Validasi input tidak kosong
    if ($task_id && $comment_text !== '') {
        $stmt = $pdo->prepare(
            "INSERT INTO comments (task_id, user_id, comment_text, created_at)
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$task_id, $user_id, $comment_text]);
    }

    // Redirect kembali ke halaman utama
    header('Location: ../../public/index.php');
    exit;
}

// Fungsi untuk mengambil semua komentar milik sebuah task
function getCommentsByTask(PDO $pdo, int $task_id): array {
    $stmt = $pdo->prepare(
        "SELECT c.comment_text, c.created_at, u.username
         FROM comments c
         JOIN users u ON c.user_id = u.id
         WHERE c.task_id = ?
         ORDER BY c.created_at ASC"
    );
    $stmt->execute([$task_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}