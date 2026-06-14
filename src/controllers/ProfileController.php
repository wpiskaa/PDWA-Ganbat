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
    case 'upload_profile_picture':
        // Validate file exists
        if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Gagal mengunggah file. Silakan coba lagi.';
            header('Location: ../../public/profile.php');
            exit;
        }

        $file = $_FILES['profile_picture'];

        // Validate file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            $_SESSION['error'] = 'Ukuran file maksimal 2MB.';
            header('Location: ../../public/profile.php');
            exit;
        }

        // Validate file extension
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            $_SESSION['error'] = 'Format file tidak didukung. Gunakan: JPG, JPEG, PNG, GIF, atau WEBP.';
            header('Location: ../../public/profile.php');
            exit;
        }

        // Generate filename
        $filename = 'avatar_' . $user_id . '_' . time() . '.' . $file_extension;
        $upload_dir = __DIR__ . '/../../public/assets/uploads/';

        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Get old profile picture path for cleanup
        $stmt = $pdo->prepare('SELECT profile_picture FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $old_picture = $stmt->fetchColumn();

        // Move uploaded file
        $destination = $upload_dir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $_SESSION['error'] = 'Gagal menyimpan file. Silakan coba lagi.';
            header('Location: ../../public/profile.php');
            exit;
        }

        // Delete old file if exists
        if (!empty($old_picture)) {
            $old_file_path = __DIR__ . '/../../public/' . $old_picture;
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }
        }

        // Update database
        $relative_path = 'assets/uploads/' . $filename;
        $stmt = $pdo->prepare('UPDATE users SET profile_picture = ? WHERE id = ?');
        $stmt->execute([$relative_path, $user_id]);

        // Update session
        $_SESSION['profile_picture'] = $relative_path;

        $_SESSION['success'] = 'Foto profil berhasil diperbarui!';
        header('Location: ../../public/profile.php');
        exit;

    case 'update_password':
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate inputs
        if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['error'] = 'Semua field password wajib diisi.';
            header('Location: ../../public/profile.php');
            exit;
        }

        // Verify old password
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $current_hash = $stmt->fetchColumn();

        if (!password_verify($old_password, $current_hash)) {
            $_SESSION['error'] = 'Password lama salah.';
            header('Location: ../../public/profile.php');
            exit;
        }

        // Validate new password length
        if (strlen($new_password) < 6) {
            $_SESSION['error'] = 'Password baru minimal 6 karakter.';
            header('Location: ../../public/profile.php');
            exit;
        }

        // Check confirm match
        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = 'Konfirmasi password baru tidak cocok.';
            header('Location: ../../public/profile.php');
            exit;
        }

        // Hash and update
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$hashed_password, $user_id]);

        $_SESSION['success'] = 'Password berhasil diperbarui!';
        header('Location: ../../public/profile.php');
        exit;

    default:
        header('Location: ../../public/profile.php');
        exit;
}
