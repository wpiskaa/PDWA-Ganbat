<?php
// src/controllers/AuthController.php
// Ditulis oleh: Hafiz Kurniawan
// Deskripsi: Menangani logika autentikasi (login, register, logout)
//             menggunakan session management PHP murni dan PDO Prepared Statements.

// Muat konfigurasi koneksi database
require_once __DIR__ . '/../config/database.php';

// Mulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Menangani proses REGISTRASI pengguna baru.
 * Dipanggil ketika form register di-submit (POST).
 */
function handleRegister(PDO $pdo): void
{
    // Ambil & bersihkan input dari form register
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // --- Validasi Input ---
    if (empty($username) || empty($password) || empty($confirm)) {
        $_SESSION['error'] = 'Semua kolom wajib diisi.';
        header('Location: ../../public/register.php');
        exit;
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        $_SESSION['error'] = 'Username harus antara 3–50 karakter.';
        header('Location: ../../public/register.php');
        exit;
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password minimal 6 karakter.';
        header('Location: ../../public/register.php');
        exit;
    }

    if ($password !== $confirm) {
        $_SESSION['error'] = 'Konfirmasi password tidak cocok.';
        header('Location: ../../public/register.php');
        exit;
    }

    // --- Cek apakah username sudah dipakai ---
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);

    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Username sudah digunakan. Pilih username lain.';
        header('Location: ../../public/register.php');
        exit;
    }

    // --- Hash password sebelum disimpan (keamanan) ---
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // --- Simpan user baru ke database ---
    $insert = $pdo->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
    $insert->execute([
        ':username' => $username,
        ':password' => $hashedPassword,
    ]);

    // Registrasi berhasil — arahkan ke halaman login dengan pesan sukses
    $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
    header('Location: ../../public/login.php');
    exit;
}

/**
 * Menangani proses LOGIN pengguna.
 * Dipanggil ketika form login di-submit (POST).
 */
function handleLogin(PDO $pdo): void
{
    // Ambil input dari form login
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // --- Validasi Input ---
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username dan password wajib diisi.';
        header('Location: ../../public/login.php');
        exit;
    }

    // --- Cari user berdasarkan username (Prepared Statement) ---
    $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- Verifikasi password dengan password_verify() ---
    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['error'] = 'Username atau password salah.';
        header('Location: ../../public/login.php');
        exit;
    }

    // --- Buat session untuk pengguna yang berhasil login ---
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['logged_in'] = true;

    // Arahkan ke halaman utama dashboard
    header('Location: ../../public/index.php');
    exit;
}

/**
 * Menangani proses LOGOUT.
 * Menghancurkan seluruh data session dan mengalihkan ke halaman login.
 */
function handleLogout(): void
{
    // Hapus semua variabel session
    $_SESSION = [];

    // Hancurkan session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // Hancurkan session sepenuhnya
    session_destroy();

    // Arahkan kembali ke halaman login
    header('Location: ../../public/login.php');
    exit;
}

// ============================================================
// ROUTER — Menentukan fungsi mana yang dijalankan berdasarkan
//           parameter 'action' yang dikirim dari form.
// ============================================================
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        handleRegister($pdo);
        break;

    case 'login':
        handleLogin($pdo);
        break;

    case 'logout':
        handleLogout();
        break;

    default:
        // Jika tidak ada action yang valid, kembali ke login
        header('Location: ../../public/login.php');
        exit;
}
