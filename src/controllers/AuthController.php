<?php
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate username length
        if (strlen($username) < 3 || strlen($username) > 50) {
            $_SESSION['error'] = 'Username harus antara 3-50 karakter.';
            header('Location: ../../public/register.php');
            exit;
        }

        // Validate password length
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password minimal 6 karakter.';
            header('Location: ../../public/register.php');
            exit;
        }

        // Validate confirm password
        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Konfirmasi password tidak cocok.';
            header('Location: ../../public/register.php');
            exit;
        }

        // Check unique username
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Username sudah digunakan.';
            header('Location: ../../public/register.php');
            exit;
        }

        // Hash password and insert user
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password, created_at) VALUES (?, ?, NOW())');
        $stmt->execute([$username, $hashed_password]);

        $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
        header('Location: ../../public/login.php');
        exit;

    case 'login':
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate input
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Username dan password wajib diisi.';
            header('Location: ../../public/login.php');
            exit;
        }

        // Find user by username
        $stmt = $pdo->prepare('SELECT id, username, password, profile_picture FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Username atau password salah.';
            header('Location: ../../public/login.php');
            exit;
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['logged_in'] = true;
        $_SESSION['profile_picture'] = $user['profile_picture'];

        $_SESSION['success'] = 'Login berhasil! Selamat datang, ' . htmlspecialchars($user['username']) . '.';
        header('Location: ../../public/index.php');
        exit;

    case 'logout':
        session_unset();
        session_destroy();
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['success'] = 'Anda telah berhasil logout.';
        header('Location: ../../public/login.php');
        exit;

    default:
        header('Location: ../../public/login.php');
        exit;
}
