<?php
session_start();
require_once __DIR__ . '/../src/config/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get all subtasks assigned to the user
$stmt = $pdo->prepare("
    SELECT s.*, p.title as project_title, p.id as project_id
    FROM subtasks s
    JOIN projects p ON s.project_id = p.id
    WHERE s.assigned_to = :user_id AND s.status != 'done'
    ORDER BY 
        CASE 
            WHEN s.deadline_date IS NULL THEN 1
            ELSE 0 
        END,
        s.deadline_date ASC
");
$stmt->execute(['user_id' => $user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Task - Ganbat!</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-dark-950 text-white font-sans min-h-screen">

    <!-- Background Glow Decorations -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-primary-600 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute top-1/2 -right-40 w-96 h-96 bg-blue-500 rounded-full opacity-10 blur-3xl"></div>
    </div>

    <?php include __DIR__ . '/../src/views/components/navbar.php'; ?>

    <main class="relative px-4 py-8 md:px-8 max-w-screen-xl mx-auto animate-fade-in">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-white mb-1">My Task</h1>
            <p class="text-slate-400 text-sm">Daftar semua tugas yang di-assign ke Anda</p>
        </div>

        <?php if (empty($tasks)): ?>
            <div class="flex flex-col items-center justify-center py-20 text-center border border-dashed border-slate-700/60 rounded-2xl bg-dark-900/30">
                <div class="w-16 h-16 bg-dark-800 rounded-full flex items-center justify-center text-slate-500 mb-4 border border-slate-700">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Hore! Tidak ada tugas</h3>
                <p class="text-slate-400 text-sm max-w-sm">Anda telah menyelesaikan semua tugas yang diberikan.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($tasks as $task): ?>
                    <a href="project_detail.php?id=<?= $task['project_id'] ?>" class="block bg-dark-800/60 backdrop-blur-sm border border-slate-700/50 rounded-2xl p-5 hover:border-primary-500/50 hover:shadow-xl hover:-translate-y-1 transition-all">
                        <div class="flex justify-between items-start mb-3">
                            <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-dark-900 border border-slate-700 text-slate-300">
                                <?= htmlspecialchars($task['project_title']) ?>
                            </span>
                            <?php 
                                $badgeColor = 'bg-slate-700 text-slate-300';
                                if ($task['priority'] === 'high') $badgeColor = 'bg-red-500/20 text-red-400 border border-red-500/30';
                                if ($task['priority'] === 'medium') $badgeColor = 'bg-amber-500/20 text-amber-400 border border-amber-500/30';
                                if ($task['priority'] === 'low') $badgeColor = 'bg-green-500/20 text-green-400 border border-green-500/30';
                            ?>
                            <span class="text-[10px] uppercase font-bold px-2 py-1 rounded-md <?= $badgeColor ?>">
                                <?= htmlspecialchars($task['priority']) ?>
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2"><?= htmlspecialchars($task['title']) ?></h3>
                        <p class="text-sm text-slate-400 line-clamp-2 mb-4"><?= htmlspecialchars($task['description']) ?></p>
                        
                        <?php if ($task['deadline_date']): ?>
                        <div class="mt-auto border-t border-slate-700/50 pt-3">
                            <span class="text-xs text-slate-500 font-medium uppercase tracking-wider block mb-1">Deadline:</span>
                            <div class="flex items-center gap-1.5" data-deadline="<?= $task['deadline_date'] ?>">
                                <span class="text-sm font-bold countdown-text tracking-tight"></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <footer class="text-center text-xs text-slate-500 py-6 mt-8">
        &copy; <?= date('Y') ?> Ganbat! &mdash; All rights reserved.
    </footer>
    <script src="js/countdown.js"></script>
</body>
</html>
