<!-- src/views/components/board_column.php -->

<?php
/**
 * Required Variables:
 * $pdo
 * $currentUserId
 * $status // todo | ongoing | done
 * $title
 */

$stmt = $pdo->prepare("
    SELECT
        s.id,
        s.project_id,
        s.title,
        s.assigned_to,
        s.status,
        s.deadline_date
    FROM subtasks s
    WHERE s.assigned_to = :user_id
      AND s.status = :status
    ORDER BY s.deadline_date ASC
");

$stmt->execute([
    ':user_id' => $currentUserId,
    ':status'  => $status
]);

$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex-1 min-w-[320px] bg-gray-100 rounded-xl p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">
            <?= htmlspecialchars($title) ?>
        </h3>

        <span class="px-3 py-1 text-sm font-medium bg-white rounded-full text-gray-700">
            <?= count($tasks) ?>
        </span>
    </div>

    <div class="space-y-3">
        <?php if (!empty($tasks)): ?>
            <?php foreach ($tasks as $task): ?>
                <?php include __DIR__ . '/task_card.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-white rounded-lg p-4 text-center text-sm text-gray-500 border border-dashed border-gray-300">
                No task available
            </div>
        <?php endif; ?>
    </div>
</div>