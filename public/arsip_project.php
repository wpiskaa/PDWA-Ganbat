<?php
session_start();
require_once __DIR__ . '/../src/config/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$flash_error = $_SESSION['error'] ?? '';
$flash_success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

$stmt = $pdo->prepare("
    SELECT p.*, 
        u.username AS owner_username,
        (SELECT COUNT(*) FROM subtasks WHERE project_id = p.id) AS total_subtasks,
        (SELECT COUNT(*) FROM subtasks WHERE project_id = p.id AND status = 'done') AS done_subtasks,
        (SELECT MIN(deadline_date) FROM subtasks WHERE project_id = p.id AND assigned_to = :uid3 AND status != 'done') AS my_next_deadline
    FROM projects p
    JOIN users u ON p.owner_id = u.id
    JOIN project_members pm ON p.id = pm.project_id 
    WHERE pm.user_id = :uid2 
      AND pm.status_invite = 'accepted'
      AND pm.is_archived = 1
    ORDER BY p.created_at DESC
");
$stmt->execute(['uid2' => $user_id, 'uid3' => $user_id]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip Project - Ganbat!</title>
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
        
        <!-- Flash Messages -->
        <?php if ($flash_error): ?>
            <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 mb-6 text-sm">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <span><?= htmlspecialchars($flash_error) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($flash_success): ?>
            <div class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 mb-6 text-sm">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span><?= htmlspecialchars($flash_success) ?></span>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <a href="my_project.php" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors duration-200 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>
                <h1 class="text-3xl font-bold tracking-tight text-white mb-1">Arsip Project</h1>
                <p class="text-slate-400 text-sm">Project yang Anda arsipkan secara pribadi.</p>
            </div>
        </div>

        <!-- Project Grid -->
        <?php if (empty($projects)): ?>
            <div class="flex flex-col items-center justify-center py-20 text-center border border-dashed border-slate-700/60 rounded-2xl bg-dark-900/30">
                <div class="w-16 h-16 bg-dark-800 rounded-full flex items-center justify-center text-slate-500 mb-4 border border-slate-700">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Arsip kosong</h3>
                <p class="text-slate-400 text-sm max-w-sm">Anda belum mengarsipkan project apapun.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 opacity-70 hover:opacity-100 transition-opacity">
                <?php foreach ($projects as $project): ?>
                    <?php
                        $total = (int)$project['total_subtasks'];
                        $done = (int)$project['done_subtasks'];
                        $progress = $total > 0 ? round(($done / $total) * 100) : 0;
                        $is_owner = ((int)$project['owner_id'] === (int)$user_id);
                    ?>
                    <div class="bg-dark-800/60 backdrop-blur-sm border border-slate-700/50 rounded-2xl p-5 cursor-pointer flex flex-col" onclick="window.location.href='project_detail.php?id=<?= $project['id'] ?>'">
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="text-lg font-bold text-slate-400 line-clamp-1 flex-1 pr-3"><?= htmlspecialchars($project['title']) ?></h3>
                            <?php if ($is_owner): ?>
                                <span class="px-2.5 py-1 text-[10px] font-bold tracking-wider rounded-lg bg-primary-500/10 text-primary-400 border border-primary-500/20 uppercase">Ketua</span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 text-[10px] font-bold tracking-wider rounded-lg bg-slate-700/50 text-slate-300 border border-slate-600 uppercase">Anggota</span>
                            <?php endif; ?>
                        </div>

                        <p class="text-sm text-slate-500 line-clamp-2 mb-6 flex-1">
                            <?= htmlspecialchars($project['description'] ?? 'Tidak ada deskripsi') ?>
                        </p>

                        <!-- Progress Bar -->
                        <div class="mb-5">
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-xs font-medium text-slate-400">Progress</span>
                                <span class="text-xs font-semibold text-slate-400"><?= $done ?>/<?= $total ?> subtask</span>
                            </div>
                            <div class="h-2 w-full bg-dark-900 rounded-full overflow-hidden border border-slate-700/50">
                                <div class="h-full rounded-full transition-all duration-500 bg-slate-500" style="width: <?= $progress ?>%"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-700/50">
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                                <div class="w-6 h-6 rounded-full bg-slate-700 flex items-center justify-center text-white">
                                    <?= strtoupper(substr($project['owner_username'], 0, 1)) ?>
                                </div>
                                <span><?= htmlspecialchars($project['owner_username']) ?></span>
                            </div>

                            <form method="POST" action="../src/controllers/ProjectController.php" onclick="event.stopPropagation();">
                                <input type="hidden" name="action" value="unarchive">
                                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                <button type="submit" class="text-xs flex items-center gap-1.5 text-primary-400 hover:text-primary-300 font-semibold px-2.5 py-1.5 rounded-lg bg-primary-500/10 hover:bg-primary-500/20 transition-colors">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8v13H3V8M1 3h22v5H1z"/><path d="M10 12h4"/></svg>
                                    Keluarkan
                                </button>
                            </form>
                        </div>
                    </div>
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
