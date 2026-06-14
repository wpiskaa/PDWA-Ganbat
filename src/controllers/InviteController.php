<?php
// src/controllers/InviteController.php
// Controller untuk menangani pengiriman undangan anggota ke dalam proyek

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi: pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../public/login.php');
    exit;
}

// ============================================================
// FUNGSI inviteMember()
// Mengirim undangan ke user lain untuk bergabung ke proyek
// ============================================================
function inviteMember(PDO $pdo): void
{
    $inviter_id = (int) $_SESSION['user_id'];               // ID ketua yang mengundang
    $project_id = (int) ($_POST['project_id'] ?? 0);        // ID proyek tujuan
    $invitee_id = (int) ($_POST['invitee_id'] ?? 0);        // ID user yang diundang

    // Validasi: project_id dan invitee_id wajib ada
    if ($project_id === 0 || $invitee_id === 0) {
        $_SESSION['error'] = "Data undangan tidak lengkap!";
        header("Location: ../../public/project_detail.php?id={$project_id}");
        exit;
    }

    // Validasi: hanya owner proyek yang boleh mengundang
    $stmtCheck = $pdo->prepare(
        "SELECT id FROM projects WHERE id = :project_id AND owner_id = :owner_id"
    );
    $stmtCheck->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmtCheck->bindParam(':owner_id',   $inviter_id, PDO::PARAM_INT);
    $stmtCheck->execute();

    if (!$stmtCheck->fetch()) {
        $_SESSION['error'] = "Kamu tidak memiliki izin untuk mengundang anggota di proyek ini!";
        header("Location: ../../public/project_detail.php?id={$project_id}");
        exit;
    }

    // Validasi: cek apakah user yang diundang sudah menjadi anggota atau sudah pending
    $stmtExist = $pdo->prepare(
        "SELECT project_id FROM project_members
         WHERE project_id = :project_id AND user_id = :user_id"
    );
    $stmtExist->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmtExist->bindParam(':user_id',    $invitee_id, PDO::PARAM_INT);
    $stmtExist->execute();

    if ($stmtExist->fetch()) {
        $_SESSION['error'] = "User ini sudah diundang atau sudah menjadi anggota proyek!";
        header("Location: ../../public/project_detail.php?id={$project_id}");
        exit;
    }

    try {
        // Mulai transaksi agar kedua query (invite + notif) berhasil atau gagal bersama
        $pdo->beginTransaction();

        // 1. Masukkan data undangan ke tabel project_members dengan status 'pending'
        $stmtInvite = $pdo->prepare(
            "INSERT INTO project_members (project_id, user_id, status_invite)
             VALUES (:project_id, :user_id, 'pending')"
        );
        $stmtInvite->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmtInvite->bindParam(':user_id',    $invitee_id, PDO::PARAM_INT);
        $stmtInvite->execute();

        // 2. Ambil nama proyek untuk isi pesan notifikasi
        $stmtProject = $pdo->prepare(
            "SELECT title FROM projects WHERE id = :project_id"
        );
        $stmtProject->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmtProject->execute();
        $project = $stmtProject->fetch(PDO::FETCH_ASSOC);
        $projectTitle = $project['title'] ?? 'sebuah proyek';

        // 3. Ambil username pengundang untuk isi pesan notifikasi
        $stmtInviter = $pdo->prepare(
            "SELECT username FROM users WHERE id = :id"
        );
        $stmtInviter->bindParam(':id', $inviter_id, PDO::PARAM_INT);
        $stmtInviter->execute();
        $inviter = $stmtInviter->fetch(PDO::FETCH_ASSOC);
        $inviterName = $inviter['username'] ?? 'Seseorang';

        // 4. Kirim notifikasi ke user yang diundang
        $message = "{$inviterName} mengundangmu untuk bergabung ke proyek \"{$projectTitle}\".";
        $stmtNotif = $pdo->prepare(
            "INSERT INTO notifications (user_id, message, is_read, created_at)
             VALUES (:user_id, :message, 0, NOW())"
        );
        $stmtNotif->bindParam(':user_id', $invitee_id, PDO::PARAM_INT);
        $stmtNotif->bindParam(':message', $message,    PDO::PARAM_STR);
        $stmtNotif->execute();

        // Commit transaksi jika semua query berhasil
        $pdo->commit();

        $_SESSION['success'] = "Undangan berhasil dikirim!";
    } catch (PDOException $e) {
        // Rollback jika ada query yang gagal
        $pdo->rollBack();
        $_SESSION['error'] = "Gagal mengirim undangan. Silakan coba lagi.";
        // error_log("inviteMember Error: " . $e->getMessage()); // aktifkan untuk debugging
    }

    // Redirect kembali ke halaman detail proyek
    header("Location: ../../public/project_detail.php?id={$project_id}");
    exit;
}

// ============================================================
// ROUTING: Cek action dari form yang dikirim
// ============================================================
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'invite_member':
            inviteMember($pdo);
            break;

        default:
            header('Location: ../../public/index.php');
            exit;
    }
}