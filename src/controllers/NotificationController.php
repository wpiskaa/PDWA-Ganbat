<?php
/**
 * ============================================================
 *  GANBAT - Sistem Manajemen Tugas
 *  File   : src/controllers/NotificationController.php
 * ============================================================
 */

require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    header('Location: ../../public/login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ──── GET: Ambil notifikasi (JSON untuk AJAX) ────
    case 'get_notifications':
        header('Content-Type: application/json');
        try {
            $stmt = $pdo->prepare("
                SELECT id, type, message, reference_id, is_read, created_at
                FROM notifications
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 20
            ");
            $stmt->execute([$user_id]);
            $notifications = $stmt->fetchAll();

            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmtCount->execute([$user_id]);
            $unreadCount = (int) $stmtCount->fetchColumn();

            echo json_encode([
                'success'       => true,
                'notifications' => $notifications,
                'unread_count'  => $unreadCount,
            ]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;

    // ──── GET: Tandai semua sudah dibaca ────
    case 'mark_all_read':
        header('Content-Type: application/json');
        try {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$user_id]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;

    // ──── POST: Terima undangan proyek ────
    case 'accept_invite':
        $notifId = (int) ($_POST['notification_id'] ?? 0);

        try {
            // Ambil notifikasi
            $stmtNotif = $pdo->prepare("SELECT * FROM notifications WHERE id = ? AND user_id = ? AND type = 'invite' AND is_read = 0");
            $stmtNotif->execute([$notifId, $user_id]);
            $notif = $stmtNotif->fetch();

            if (!$notif) {
                $_SESSION['error'] = 'Undangan tidak ditemukan.';
                header('Location: ../../public/index.php');
                exit;
            }

            $project_id = (int) $notif['reference_id'];

            $pdo->beginTransaction();

            // Update status member menjadi accepted
            $stmtAccept = $pdo->prepare("
                UPDATE project_members SET status_invite = 'accepted'
                WHERE project_id = ? AND user_id = ? AND status_invite = 'pending'
            ");
            $stmtAccept->execute([$project_id, $user_id]);

            // Tandai notifikasi sebagai dibaca
            $stmtRead = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
            $stmtRead->execute([$notifId]);

            $pdo->commit();

            $_SESSION['success'] = 'Anda berhasil bergabung ke proyek.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Accept invite error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal menerima undangan.';
        }

        header('Location: ../../public/index.php');
        exit;

    // ──── POST: Tolak undangan proyek ────
    case 'decline_invite':
        $notifId = (int) ($_POST['notification_id'] ?? 0);

        try {
            $stmtNotif = $pdo->prepare("SELECT * FROM notifications WHERE id = ? AND user_id = ? AND type = 'invite' AND is_read = 0");
            $stmtNotif->execute([$notifId, $user_id]);
            $notif = $stmtNotif->fetch();

            if (!$notif) {
                $_SESSION['error'] = 'Undangan tidak ditemukan.';
                header('Location: ../../public/index.php');
                exit;
            }

            $project_id = (int) $notif['reference_id'];

            $pdo->beginTransaction();

            // Hapus dari project_members
            $stmtDel = $pdo->prepare("DELETE FROM project_members WHERE project_id = ? AND user_id = ? AND status_invite = 'pending'");
            $stmtDel->execute([$project_id, $user_id]);

            // Tandai notifikasi sebagai dibaca
            $stmtRead = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
            $stmtRead->execute([$notifId]);

            $pdo->commit();

            $_SESSION['success'] = 'Undangan ditolak.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Decline invite error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal menolak undangan.';
        }

        header('Location: ../../public/index.php');
        exit;

    default:
        header('Location: ../../public/index.php');
        exit;
}