<?php
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: ../../public/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'search_users') {
    header('Content-Type: application/json');

    $q = trim($_GET['q'] ?? '');
    if (empty($q)) {
        echo json_encode([]);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, username FROM users WHERE username LIKE ? AND id != ? LIMIT 10');
    $stmt->execute(['%' . $q . '%', $user_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'invite_member') {
    $project_id = (int) ($_POST['project_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');

    // Validate inputs
    if ($project_id <= 0 || empty($username)) {
        $_SESSION['error'] = 'Data undangan tidak valid.';
        header('Location: ../../public/project_detail.php?id=' . $project_id);
        exit;
    }

    // Check current user is project owner
    $stmt = $pdo->prepare('SELECT id, title FROM projects WHERE id = ? AND owner_id = ?');
    $stmt->execute([$project_id, $user_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        $_SESSION['error'] = 'Anda tidak memiliki izin untuk mengundang anggota.';
        header('Location: ../../public/index.php');
        exit;
    }

    // Find invitee by username
    $stmt = $pdo->prepare('SELECT id, username FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $invitee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invitee) {
        $_SESSION['error'] = 'User "' . htmlspecialchars($username) . '" tidak ditemukan.';
        header('Location: ../../public/project_detail.php?id=' . $project_id);
        exit;
    }

    // Check not inviting self
    if ($invitee['id'] == $user_id) {
        $_SESSION['error'] = 'Anda tidak bisa mengundang diri sendiri.';
        header('Location: ../../public/project_detail.php?id=' . $project_id);
        exit;
    }

    // Check not already member or pending
    $stmt = $pdo->prepare('SELECT status_invite FROM project_members WHERE project_id = ? AND user_id = ?');
    $stmt->execute([$project_id, $invitee['id']]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        if ($existing['status_invite'] === 'accepted') {
            $_SESSION['error'] = 'User sudah menjadi anggota proyek ini.';
        } else {
            $_SESSION['error'] = 'Undangan sudah dikirim sebelumnya dan masih menunggu konfirmasi.';
        }
        header('Location: ../../public/project_detail.php?id=' . $project_id);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Insert into project_members with pending status
        $stmt = $pdo->prepare('INSERT INTO project_members (project_id, user_id, status_invite, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$project_id, $invitee['id'], 'pending']);

        // Create notification for invitee
        $message = $_SESSION['username'] . ' mengundang Anda ke proyek "' . $project['title'] . '".';
        $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, message, reference_id, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())');
        $stmt->execute([$invitee['id'], 'invite', $message, $project_id]);

        $pdo->commit();

        $_SESSION['success'] = 'Undangan berhasil dikirim ke ' . htmlspecialchars($invitee['username']) . '.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Gagal mengirim undangan. Silakan coba lagi.';
    }

    header('Location: ../../public/project_detail.php?id=' . $project_id);
    exit;
}

// Default
header('Location: ../../public/index.php');
exit;