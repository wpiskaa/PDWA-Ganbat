<?php

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    header('Location: ../../public/login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_profile_picture') {
        if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['error'] = 'Silakan pilih file foto terlebih dahulu.';
            header('Location: ../../public/profile.php');
            exit;
        }

        $file = $_FILES['profile_picture'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];

        if ($fileError !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Terjadi kesalahan saat mengunggah file.';
            header('Location: ../../public/profile.php');
            exit;
        }

        if ($fileSize > 2 * 1024 * 1024) {
            $_SESSION['error'] = 'Ukuran file terlalu besar. Maksimal 2 MB.';
            header('Location: ../../public/profile.php');
            exit;
        }

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExt, $allowedExtensions)) {
            $_SESSION['error'] = 'Format file tidak valid. Hanya JPG, JPEG, PNG, dan GIF yang diperbolehkan.';
            header('Location: ../../public/profile.php');
            exit;
        }

        $uploadDir = __DIR__ . '/../../public/assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFileName = 'avatar_' . $user_id . '_' . time() . '.' . $fileExt;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpName, $destPath)) {
            $dbPath = 'assets/uploads/' . $newFileName;

            try {
                $selectStmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = :id");
                $selectStmt->execute([':id' => $user_id]);
                $oldPic = $selectStmt->fetchColumn();

                if ($oldPic && file_exists(__DIR__ . '/../../public/' . $oldPic)) {
                    unlink(__DIR__ . '/../../public/' . $oldPic);
                }

                $updateStmt = $pdo->prepare("UPDATE users SET profile_picture = :profile_picture WHERE id = :id");
                $updateStmt->execute([
                    ':profile_picture' => $dbPath,
                    ':id' => $user_id
                ]);

                $_SESSION['profile_picture'] = $dbPath;

                $_SESSION['success'] = 'Foto profil berhasil diperbarui.';
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Gagal menyimpan data ke database: ' . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = 'Gagal memindahkan file ke direktori tujuan.';
        }

        header('Location: ../../public/profile.php');
        exit;
    }
}

header('Location: ../../public/profile.php');
exit;
