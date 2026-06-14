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
      AND pm.is_archived = 0
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
    <title>Project Saya - Ganbat!</title>
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
                <h1 class="text-3xl font-bold tracking-tight text-white mb-1">Project Saya</h1>
                <p class="text-slate-400 text-sm">Kelola semua project dan kolaborasi tim kamu</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="arsip_project.php" class="flex items-center gap-2 bg-dark-800 hover:bg-dark-700 text-slate-300 font-medium px-4 py-2.5 rounded-xl border border-slate-700 transition-all">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                    Arsip
                </a>
                <button onclick="document.getElementById('modalCreateProject').classList.remove('hidden')" class="flex items-center gap-2 bg-primary-600 hover:bg-primary-500 text-white font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-primary-600/30 transition-all">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Buat Project
                </button>
            </div>
        </div>

        <!-- Project Grid -->
        <?php if (empty($projects)): ?>
            <div class="flex flex-col items-center justify-center py-20 text-center border border-dashed border-slate-700/60 rounded-2xl bg-dark-900/30">
                <div class="w-16 h-16 bg-dark-800 rounded-full flex items-center justify-center text-slate-500 mb-4 border border-slate-700">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Belum ada project</h3>
                <p class="text-slate-400 text-sm max-w-sm">Mulai buat project pertama kamu untuk mengelola tugas bersama tim.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($projects as $project): ?>
                    <?php
                        $total = (int)$project['total_subtasks'];
                        $done = (int)$project['done_subtasks'];
                        $progress = $total > 0 ? round(($done / $total) * 100) : 0;
                        $is_owner = ((int)$project['owner_id'] === (int)$user_id);
                    ?>
                    <div class="bg-dark-800/60 backdrop-blur-sm border border-slate-700/50 rounded-2xl p-5 hover:border-primary-500/50 hover:shadow-xl hover:-translate-y-1 transition-all cursor-pointer flex flex-col" onclick="window.location.href='project_detail.php?id=<?= $project['id'] ?>'">
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="text-lg font-bold text-white line-clamp-1 flex-1 pr-3"><?= htmlspecialchars($project['title']) ?></h3>
                            <?php if ($is_owner): ?>
                                <span class="px-2.5 py-1 text-[10px] font-bold tracking-wider rounded-lg bg-primary-500/10 text-primary-400 border border-primary-500/20 uppercase">Ketua</span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 text-[10px] font-bold tracking-wider rounded-lg bg-slate-700/50 text-slate-300 border border-slate-600 uppercase">Anggota</span>
                            <?php endif; ?>
                        </div>

                        <p class="text-sm text-slate-400 line-clamp-2 mb-6 flex-1">
                            <?= htmlspecialchars($project['description'] ?? 'Tidak ada deskripsi') ?>
                        </p>

                        <!-- Deadlines -->
                        <div class="mb-4">
                            <?php if (!empty($project['my_next_deadline'])): ?>
                            <div class="mb-2">
                                <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Deadline Subtask Terdekat:</span>
                                <div class="flex items-center gap-1.5 mt-0.5" data-deadline="<?= $project['my_next_deadline'] ?>">
                                    <span class="text-xl font-bold countdown-text tracking-tight"></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($project['global_deadline'])): ?>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Deadline Project Global:</span>
                                <div class="text-sm font-semibold text-white mt-0.5">
                                    <?= date('d M Y', strtotime($project['global_deadline'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-5">
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-xs font-medium text-slate-300">Progress</span>
                                <span class="text-xs font-semibold <?= $progress === 100 ? 'text-emerald-400' : 'text-primary-400' ?>"><?= $done ?>/<?= $total ?> subtask</span>
                            </div>
                            <div class="h-2 w-full bg-dark-900 rounded-full overflow-hidden border border-slate-700/50">
                                <div class="h-full rounded-full transition-all duration-500 <?= $progress === 100 ? 'bg-emerald-500' : 'bg-primary-500' ?>" style="width: <?= $progress ?>%"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-700/50">
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-300">
                                <div class="w-6 h-6 rounded-full bg-slate-700 flex items-center justify-center text-white">
                                    <?= strtoupper(substr($project['owner_username'], 0, 1)) ?>
                                </div>
                                <span><?= htmlspecialchars($project['owner_username']) ?></span>
                            </div>

                                <form method="POST" action="../src/controllers/ProjectController.php" onclick="event.stopPropagation();" onsubmit="return confirm('Arsipkan project ini?');">
                                    <input type="hidden" name="action" value="archive">
                                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                    <button type="submit" class="p-1.5 text-slate-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors" title="Arsipkan Project">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                                    </button>
                                </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Modal Create Project -->
    <div id="modalCreateProject" class="hidden fixed inset-0 z-50 bg-dark-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-dark-800 border border-slate-700 rounded-2xl w-full max-w-md p-6 shadow-2xl animate-slide-up">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl font-bold text-white">Buat Project Baru</h2>
                <button onclick="document.getElementById('modalCreateProject').classList.add('hidden')" class="text-slate-400 hover:text-white transition-colors">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form method="POST" action="../src/controllers/ProjectController.php">
                <input type="hidden" name="action" value="create_project">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Nama Project <span class="text-red-400">*</span></label>
                    <input type="text" name="title" required placeholder="Masukkan nama project"
                           class="w-full bg-dark-900 border border-slate-600 text-white text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="3" placeholder="Deskripsikan project kamu..."
                              class="w-full bg-dark-900 border border-slate-600 text-white text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all resize-none"></textarea>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Deadline Global Project</label>
                    <input type="datetime-local" name="global_deadline" min="<?= date('Y-m-d\TH:i') ?>"
                           class="w-full bg-dark-900 border border-slate-600 text-white text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modalCreateProject').classList.add('hidden')" 
                            class="flex-1 py-2.5 border border-slate-600 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-primary-600/20">
                        Buat Project
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer class="text-center text-xs text-slate-500 py-6 mt-8">
        &copy; <?= date('Y') ?> Ganbat! &mdash; All rights reserved.
    </footer>
    <script src="js/countdown.js"></script>
</body>
</html>