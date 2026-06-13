<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/controllers/LeaderController.php';

$leaderController = new LeaderController($pdo);

$projectId = isset($_GET['project_id'])
    ? (int) $_GET['project_id']
    : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'reassign') {

        $leaderController->reassignSubtask(
            (int) $_POST['subtask_id'],
            (int) $_POST['new_user_id'],
            $projectId
        );

        header("Location: project_detail.php?project_id={$projectId}");
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'kick') {

        $leaderController->kickMember(
            $projectId,
            (int) $_POST['user_id']
        );

        header("Location: project_detail.php?project_id={$projectId}");
        exit;
    }
}

$project = $leaderController->getProjectById($projectId);
$members = $leaderController->getProjectMembers($projectId);
$subtasks = $leaderController->getProjectSubtasks($projectId);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leader Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <meta http-equiv="refresh" content="15">
</head>

<body class="bg-slate-100 min-h-screen">

<div class="max-w-7xl mx-auto p-6">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-800">
            Leader Dashboard
        </h1>

        <p class="text-slate-500 mt-2">
            <?= htmlspecialchars($project['title'] ?? 'Project') ?>
        </p>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2">

            <div class="bg-white rounded-xl shadow">

                <div class="p-5 border-b">
                    <h2 class="font-bold text-lg">
                        Monitoring Subtask
                    </h2>
                </div>

                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-slate-50">
                        <tr>
                            <th class="text-left p-4">Subtask</th>
                            <th class="text-left p-4">PIC</th>
                            <th class="text-left p-4">Status</th>
                            <th class="text-left p-4">Deadline</th>
                            <th class="text-left p-4">Re-Assign</th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($subtasks as $subtask): ?>

                            <tr class="border-t">

                                <td class="p-4">
                                    <?= htmlspecialchars($subtask['title']) ?>
                                </td>

                                <td class="p-4">
                                    <?= htmlspecialchars($subtask['assigned_username'] ?? '-') ?>
                                </td>

                                <td class="p-4">

                                    <?php if ($subtask['status'] === 'todo'): ?>

                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs">
                                            TODO
                                        </span>

                                    <?php elseif ($subtask['status'] === 'ongoing'): ?>

                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">
                                            ONGOING
                                        </span>

                                    <?php else: ?>

                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs">
                                            DONE
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td class="p-4">
                                    <?= htmlspecialchars($subtask['deadline_date']) ?>
                                </td>

                                <td class="p-4">

                                    <form method="POST" class="flex gap-2">

                                        <input type="hidden" name="action" value="reassign">
                                        <input type="hidden" name="subtask_id" value="<?= $subtask['id'] ?>">

                                        <select
                                            name="new_user_id"
                                            class="border rounded-lg px-3 py-2 text-sm"
                                        >

                                            <?php foreach ($members as $member): ?>

                                                <option value="<?= $member['id'] ?>">
                                                    <?= htmlspecialchars($member['username']) ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                        <button
                                            type="submit"
                                            class="bg-blue-600 text-white px-3 py-2 rounded-lg text-sm"
                                        >
                                            Re-Assign
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <div>

            <div class="bg-white rounded-xl shadow">

                <div class="p-5 border-b">
                    <h2 class="font-bold text-lg">
                        Project Members
                    </h2>
                </div>

                <div class="p-4 space-y-3">

                    <?php foreach ($members as $member): ?>

                        <div class="flex items-center justify-between border rounded-lg p-3">

                            <div class="flex items-center gap-3">

                                <div class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden">

                                    <?php if (!empty($member['profile_picture'])): ?>

                                        <img
                                            src="<?= htmlspecialchars($member['profile_picture']) ?>"
                                            class="w-full h-full object-cover"
                                        >

                                    <?php endif; ?>

                                </div>

                                <span class="font-medium">
                                    <?= htmlspecialchars($member['username']) ?>
                                </span>

                            </div>

                            <form method="POST">

                                <input type="hidden" name="action" value="kick">
                                <input type="hidden" name="user_id" value="<?= $member['id'] ?>">

                                <button
                                    type="submit"
                                    onclick="return confirm('Kick member ini?')"
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm"
                                >
                                    Kick
                                </button>

                            </form>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>