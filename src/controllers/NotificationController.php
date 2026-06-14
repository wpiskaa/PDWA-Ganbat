<?php
require_once __DIR__ . '/../config/database.php';

class NotificationController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getDBConnection();
    }

    /**
     * Ambil semua notifikasi milik user yang sedang login.
     */
    public function getNotifications(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, message, is_read, created_at
             FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT 20"
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hitung notifikasi yang belum dibaca.
     */
    public function countUnread(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM notifications
             WHERE user_id = :user_id AND is_read = 0"
        );
        $stmt->execute([':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Tandai semua notifikasi user sebagai sudah dibaca.
     */
    public function markAllRead(int $userId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE notifications SET is_read = 1
             WHERE user_id = :user_id AND is_read = 0"
        );
        $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Terima undangan proyek:
     *  – ubah status_invite menjadi 'accepted'
     *  – tandai notifikasi terkait sebagai sudah dibaca
     */
    public function acceptInvite(int $projectId, int $userId): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "UPDATE project_members
                 SET status_invite = 'accepted'
                 WHERE project_id = :project_id
                   AND user_id    = :user_id
                   AND status_invite = 'pending'"
            );
            $stmt->execute([
                ':project_id' => $projectId,
                ':user_id'    => $userId,
            ]);

            // Tandai notifikasi undangan ini sebagai sudah dibaca
            $this->markInviteNotificationRead($projectId, $userId);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Tolak undangan proyek:
     *  – hapus baris dari project_members
     *  – tandai notifikasi terkait sebagai sudah dibaca
     */
    public function rejectInvite(int $projectId, int $userId): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "DELETE FROM project_members
                 WHERE project_id = :project_id
                   AND user_id    = :user_id
                   AND status_invite = 'pending'"
            );
            $stmt->execute([
                ':project_id' => $projectId,
                ':user_id'    => $userId,
            ]);

            $this->markInviteNotificationRead($projectId, $userId);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Tandai notifikasi undangan proyek sebagai sudah dibaca
     * berdasarkan project_id yang disematkan di pesan notifikasi.
     * Konvensi pesan: mengandung substring "project_id:{$projectId}"
     */
    private function markInviteNotificationRead(int $projectId, int $userId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE notifications
             SET is_read = 1
             WHERE user_id = :user_id
               AND message LIKE :pattern
               AND is_read = 0"
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':pattern' => "%project_id:{$projectId}%",
        ]);
    }
}

// ─── Entry point: hanya dieksekusi jika dipanggil via POST ───────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $userId = (int) $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';

    $controller = new NotificationController();
    $response   = ['success' => false, 'message' => 'Aksi tidak dikenali.'];

    switch ($action) {

        case 'get_notifications':
            $notifications = $controller->getNotifications($userId);
            $unread        = $controller->countUnread($userId);
            $response      = [
                'success'       => true,
                'notifications' => $notifications,
                'unread_count'  => $unread,
            ];
            break;

        case 'mark_all_read':
            $controller->markAllRead($userId);
            $response = ['success' => true];
            break;

        case 'accept_invite':
            $projectId = (int) ($_POST['project_id'] ?? 0);
            if ($projectId > 0) {
                $ok       = $controller->acceptInvite($projectId, $userId);
                $response = [
                    'success' => $ok,
                    'message' => $ok ? 'Undangan diterima.' : 'Gagal menerima undangan.',
                ];
            } else {
                $response = ['success' => false, 'message' => 'project_id tidak valid.'];
            }
            break;

        case 'reject_invite':
            $projectId = (int) ($_POST['project_id'] ?? 0);
            if ($projectId > 0) {
                $ok       = $controller->rejectInvite($projectId, $userId);
                $response = [
                    'success' => $ok,
                    'message' => $ok ? 'Undangan ditolak.' : 'Gagal menolak undangan.',
                ];
            } else {
                $response = ['success' => false, 'message' => 'project_id tidak valid.'];
            }
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}