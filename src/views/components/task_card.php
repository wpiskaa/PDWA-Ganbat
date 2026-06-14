<!-- src/views/components/task_card.php -->

<?php
/**
 * Required Variables:
 * $task
 * $currentUserId
 */

$canUpdate = ((int)$task['assigned_to'] === (int)$currentUserId);

$nextStatus = null;

if ($task['status'] === 'todo') {
    $nextStatus = 'ongoing';
} elseif ($task['status'] === 'ongoing') {
    $nextStatus = 'done';
}
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <div class="mb-3">
        <h4 class="font-semibold text-gray-800 break-words">
            <?= htmlspecialchars($task['title']) ?>
        </h4>
    </div>

    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
        <span>
            Deadline:
            <?= !empty($task['deadline_date'])
                ? htmlspecialchars($task['deadline_date'])
                : '-' ?>
        </span>

        <span class="px-2 py-1 rounded-full text-xs font-medium
            <?php if ($task['status'] === 'todo'): ?>
                bg-gray-200 text-gray-700
            <?php elseif ($task['status'] === 'ongoing'): ?>
                bg-yellow-100 text-yellow-700
            <?php else: ?>
                bg-green-100 text-green-700
            <?php endif; ?>">
            <?= ucfirst(htmlspecialchars($task['status'])) ?>
        </span>
    </div>

    <?php if ($canUpdate && $nextStatus !== null): ?>
        <form action="/src/controllers/update_subtask_status.php" method="POST">
            <input
                type="hidden"
                name="subtask_id"
                value="<?= (int)$task['id'] ?>"
            >

            <input
                type="hidden"
                name="status"
                value="<?= htmlspecialchars($nextStatus) ?>"
            >

            <button
                type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 rounded-lg transition"
            >
                Move to <?= ucfirst($nextStatus) ?>
            </button>
        </form>
    <?php endif; ?>
</div>