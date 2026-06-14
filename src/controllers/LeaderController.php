<?php

require_once __DIR__ . '/../config/database.php';

class LeaderController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getProjectById(int $projectId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM projects
            WHERE id = :project_id
        ");

        $stmt->execute([
            ':project_id' => $projectId
        ]);

        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        return $project ?: null;
    }

    public function getProjectMembers(int $projectId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                u.id,
                u.username,
                u.profile_picture
            FROM project_members pm
            INNER JOIN users u
                ON pm.user_id = u.id
            WHERE pm.project_id = :project_id
            AND pm.status_invite = 'accepted'
            ORDER BY u.username ASC
        ");

        $stmt->execute([
            ':project_id' => $projectId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectSubtasks(int $projectId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                s.id,
                s.title,
                s.status,
                s.deadline_date,
                u.username AS assigned_username,
                u.id AS assigned_user_id
            FROM subtasks s
            LEFT JOIN users u
                ON s.assigned_to = u.id
            WHERE s.project_id = :project_id
            ORDER BY s.id DESC
        ");

        $stmt->execute([
            ':project_id' => $projectId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reassignSubtask(int $subtaskId, int $newUserId, int $projectId): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE subtasks
            SET assigned_to = :assigned_to
            WHERE id = :subtask_id
            AND project_id = :project_id
        ");

        return $stmt->execute([
            ':assigned_to' => $newUserId,
            ':subtask_id' => $subtaskId,
            ':project_id' => $projectId
        ]);
    }

    public function kickMember(int $projectId, int $userId): bool
    {
        try {

            $this->pdo->beginTransaction();

            $removeMember = $this->pdo->prepare("
                DELETE FROM project_members
                WHERE project_id = :project_id
                AND user_id = :user_id
            ");

            $removeMember->execute([
                ':project_id' => $projectId,
                ':user_id' => $userId
            ]);

            $clearSubtasks = $this->pdo->prepare("
                UPDATE subtasks
                SET assigned_to = NULL
                WHERE project_id = :project_id
                AND assigned_to = :user_id
            ");

            $clearSubtasks->execute([
                ':project_id' => $projectId,
                ':user_id' => $userId
            ]);

            $this->pdo->commit();

            return true;

        } catch (PDOException $e) {

            $this->pdo->rollBack();

            return false;
        }
    }
}