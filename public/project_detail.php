<?php
/**
 * ============================================================
 *  GANBAT - Sistem Manajemen Tugas
 *  File   : public/project_detail.php
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['logged_in'])) { header('Location: login.php'); exit; }

require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/utils/helpers.php';

$project_id = (int) ($_GET['id'] ?? 0);
if ($project_id <= 0) { header('Location: index.php'); exit; }

$user_id = (int) $_SESSION['user_id'];

// Ambil data proyek
$stmtProj = $pdo->prepare("SELECT p.*, u.username AS owner_name FROM projects p JOIN users u ON p.owner_id = u.id WHERE p.id = ?");
$stmtProj->execute([$project_id]);
$project = $stmtProj->fetch();

if (!$project) { header('Location: index.php'); exit; }

$is_owner = ((int) $project['owner_id'] === $user_id);

// Cek apakah user adalah member yang accepted
$stmtMember = $pdo->prepare("SELECT user_id FROM project_members WHERE project_id = ? AND user_id = ? AND status_invite = 'accepted'");
$stmtMember->execute([$project_id, $user_id]);
$is_member = (bool) $stmtMember->fetch();

if (!$is_member) {
    $_SESSION['error'] = 'Anda tidak memiliki akses ke proyek ini.';
    header('Location: index.php');
    exit;
}

// Ambil members (accepted only)
$stmtMembers = $pdo->prepare("
    SELECT u.id, u.username, u.profile_picture, 
           CASE WHEN u.id = ? THEN 'owner' ELSE 'member' END AS role
    FROM project_members pm
    JOIN users u ON pm.user_id = u.id
    WHERE pm.project_id = ? AND pm.status_invite = 'accepted'
    ORDER BY (u.id = ?) DESC, u.username ASC
");
$stmtMembers->execute([$project['owner_id'], $project_id, $project['owner_id']]);
$members = $stmtMembers->fetchAll();

// Ambil subtasks
$stmtSubtasks = $pdo->prepare("
    SELECT s.*, u.username AS assigned_username
    FROM subtasks s
    LEFT JOIN users u ON s.assigned_to = u.id
    WHERE s.project_id = ?
    ORDER BY s.created_at DESC
");
$stmtSubtasks->execute([$project_id]);
$allSubtasks = $stmtSubtasks->fetchAll();

// Group by status
$subtasksByStatus = ['todo' => [], 'ongoing' => [], 'done' => []];
foreach ($allSubtasks as $st) {
    $status = $st['status'] ?? 'todo';
    if (!isset($subtasksByStatus[$status])) $status = 'todo';
    $subtasksByStatus[$status][] = $st;
}

$columns = [
    ['id' => 'todo',    'title' => 'Todo',    'icon' => '📋', 'color' => 'border-slate-500/20',   'tasks' => $subtasksByStatus['todo']],
    ['id' => 'ongoing', 'title' => 'Ongoing',  'icon' => '⚡', 'color' => 'border-amber-500/20',   'tasks' => $subtasksByStatus['ongoing']],
    ['id' => 'done',    'title' => 'Done',     'icon' => '✅', 'color' => 'border-emerald-500/20', 'tasks' => $subtasksByStatus['done']],
];

$error   = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($project['title']) ?> — Ganbat</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .col-scroll { scrollbar-width: thin; scrollbar-color: #334155 transparent; }
        .col-scroll::-webkit-scrollbar { width: 4px; }
        .col-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
</head>
<body class="bg-dark-950 min-h-screen font-sans text-white">

    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-primary-600 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-blue-500 rounded-full opacity-10 blur-3xl"></div>
    </div>

    <?php include __DIR__ . '/../src/views/components/navbar.php'; ?>

    <main class="relative px-4 py-6 md:px-8 md:py-8 max-w-screen-xl mx-auto animate-fade-in">

        <!-- Back -->
        <div class="mb-4">
            <a href="index.php" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>

        <!-- Flash Messages -->
        <?php if ($error): ?>
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl mb-4 text-sm"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-xl mb-4 text-sm"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Project Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-2xl font-bold"><?= htmlspecialchars($project['title']) ?></h1>
                    <?php if ($is_owner): ?>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-primary-500/10 text-primary-300 border border-primary-500/20">Ketua</span>
                    <?php endif; ?>
                </div>
                <p class="text-slate-400 text-sm"><?= htmlspecialchars($project['description'] ?? '') ?></p>
            </div>
            <?php if ($is_owner): ?>
            <div class="flex gap-2 flex-shrink-0">
                <button onclick="document.getElementById('modalInvite').classList.remove('hidden')"
                        class="bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-all">
                    + Invite
                </button>
                <button onclick="document.getElementById('modalCreateSubtask').classList.remove('hidden')"
                        class="bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-all">
                    + Bagi Tugas
                </button>
            </div>
            <?php endif; ?>
        </div>

        <!-- Members -->
        <div class="bg-dark-800/60 border border-slate-700/50 rounded-2xl p-5 mb-6">
            <h2 class="text-sm font-bold text-slate-300 mb-3">Anggota Tim (<?= count($members) ?>)</h2>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($members as $m): ?>
                <div class="flex items-center gap-2 bg-dark-900/60 rounded-xl px-3 py-2">
                    <div class="w-7 h-7 rounded-full bg-primary-600 flex items-center justify-center text-xs font-bold overflow-hidden flex-shrink-0">
                        <?php if (!empty($m['profile_picture'])): ?>
                            <img src="<?= htmlspecialchars($m['profile_picture']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?= strtoupper(substr($m['username'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs font-medium"><?= htmlspecialchars($m['username']) ?></span>
                    <?php if ((int) $m['id'] === (int) $project['owner_id']): ?>
                        <span class="text-[9px] text-amber-400">👑</span>
                    <?php endif; ?>
                    <?php if ($is_owner && (int) $m['id'] !== $user_id): ?>
                    <form method="POST" action="../src/controllers/LeaderController.php" class="inline" onsubmit="return confirm('Yakin kick anggota ini?')">
                        <input type="hidden" name="action" value="kick_member">
                        <input type="hidden" name="project_id" value="<?= $project_id ?>">
                        <input type="hidden" name="user_id" value="<?= $m['id'] ?>">
                        <button class="text-[9px] text-red-400 hover:text-red-300 ml-1">✕</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-start">
            <?php foreach ($columns as $col): ?>
            <div class="bg-dark-800/60 border <?= $col['color'] ?> rounded-2xl p-4 flex flex-col">
                <div class="flex items-center justify-between mb-3.5 pb-3 border-b border-slate-700/40">
                    <div class="flex items-center gap-2">
                        <span class="text-sm"><?= $col['icon'] ?></span>
                        <h3 class="text-sm font-bold text-white tracking-wide"><?= $col['title'] ?></h3>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-700/60 text-slate-400"><?= count($col['tasks']) ?></span>
                </div>
                <div class="space-y-2.5 overflow-y-auto max-h-[65vh] col-scroll flex-1">
                    <?php if (empty($col['tasks'])): ?>
                        <p class="text-xs text-slate-500 text-center py-6 italic">Belum ada subtask</p>
                    <?php else: ?>
                        <?php foreach ($col['tasks'] as $st):
                            $priorityIcons = ['high' => '🔴', 'medium' => '🟡', 'low' => '🟢'];
                            $pIcon = $priorityIcons[$st['priority']] ?? '🟡';
                            $isAssignee = ((int) ($st['assigned_to'] ?? 0) === $user_id);
                            $deadlineInfo = getDeadlineStatus($st['deadline_date']);
                            $deadlineClasses = getDeadlineClasses($deadlineInfo['status']);
                        ?>
                        <div class="bg-dark-900/80 border border-slate-700/40 rounded-xl p-3 transition-all hover:-translate-y-0.5">
                            <!-- Priority & Status -->
                            <div class="flex items-center justify-between mb-2">
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full <?= getPriorityBadge($st['priority']) ?>">
                                    <?= $pIcon ?> <?= getPriorityLabel($st['priority']) ?>
                                </span>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full <?= getStatusBadge($st['status']) ?>">
                                    <?= getStatusLabel($st['status']) ?>
                                </span>
                            </div>

                            <!-- Title -->
                            <h4 class="text-sm font-semibold text-white leading-snug mb-1"><?= htmlspecialchars($st['title']) ?></h4>

                            <!-- Description -->
                            <?php if (!empty($st['description'])): ?>
                            <p class="text-[11px] text-slate-400 mb-2 line-clamp-2"><?= htmlspecialchars($st['description']) ?></p>
                            <?php endif; ?>

                            <!-- Assignee -->
                            <?php if (!empty($st['assigned_username'])): ?>
                            <div class="flex items-center gap-1.5 mb-2">
                                <div class="w-4 h-4 rounded-full bg-primary-500/20 flex items-center justify-center text-[8px] font-bold text-primary-400">
                                    <?= strtoupper(substr($st['assigned_username'], 0, 1)) ?>
                                </div>
                                <span class="text-[10px] text-slate-400"><?= htmlspecialchars($st['assigned_username']) ?></span>
                            </div>
                            <?php else: ?>
                            <p class="text-[10px] text-slate-500 italic mb-2">Belum di-assign</p>
                            <?php endif; ?>

                            <!-- Deadline -->
                            <?php if (!empty($st['deadline_date'])): ?>
                            <div class="flex items-center gap-1.5 mb-2" data-deadline="<?= $st['deadline_date'] ?>">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-[10px] text-slate-400"><?= formatDeadlineDate($st['deadline_date']) ?></span>
                                <span class="countdown-text text-[10px] font-medium <?= $deadlineClasses['text'] ?>"></span>
                            </div>
                            <?php endif; ?>

                            <!-- Actions -->
                            <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-slate-700/40">
                                <?php if ($isAssignee || $is_owner):
                                    $statuses = ['todo' => '📋 Todo', 'ongoing' => '⚡ Ongoing', 'done' => '✅ Done'];
                                    foreach ($statuses as $sKey => $sLabel):
                                        if ($sKey === $st['status']) continue;
                                ?>
                                <form method="POST" action="../src/controllers/TaskController.php" class="inline">
                                    <input type="hidden" name="action" value="update_subtask_status">
                                    <input type="hidden" name="subtask_id" value="<?= $st['id'] ?>">
                                    <input type="hidden" name="status" value="<?= $sKey ?>">
                                    <button class="text-[9px] px-2 py-1 rounded-lg bg-slate-700/60 hover:bg-slate-600 text-slate-300 transition-colors">
                                        <?= $sLabel ?>
                                    </button>
                                </form>
                                <?php endforeach; endif; ?>

                                <?php if ($is_owner): ?>
                                <button onclick="openReassign(<?= $st['id'] ?>, '<?= htmlspecialchars($st['title']) ?>')"
                                        class="text-[9px] px-2 py-1 rounded-lg bg-primary-500/20 hover:bg-primary-500/30 text-primary-300 transition-colors">
                                    🔄 Reassign
                                </button>
                                <form method="POST" action="../src/controllers/TaskController.php" class="inline" onsubmit="return confirm('Hapus subtask ini?')">
                                    <input type="hidden" name="action" value="delete_subtask">
                                    <input type="hidden" name="subtask_id" value="<?= $st['id'] ?>">
                                    <button class="text-[9px] px-2 py-1 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-300 transition-colors">
                                        🗑 Hapus
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer class="relative text-center text-xs text-slate-600 py-6 mt-4">
        &copy; <?= date('Y') ?> Ganbat &mdash; Sistem Manajemen Tugas
    </footer>

    <?php if ($is_owner): ?>
    <!-- Modal Invite Member -->
    <div id="modalInvite" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-dark-800 border border-slate-700/80 rounded-2xl shadow-2xl w-full max-w-sm p-6 animate-slide-up">
            <button onclick="document.getElementById('modalInvite').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-white">✕</button>
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 bg-primary-600/20 text-primary-400 rounded-lg">👥</span>
                Invite Member
            </h2>
            <form method="POST" action="../src/controllers/InviteController.php">
                <input type="hidden" name="action" value="invite_member">
                <input type="hidden" name="project_id" value="<?= $project_id ?>">
                <div class="mb-4">
                    <label class="block text-sm text-slate-300 mb-1.5">Username</label>
                    <input type="text" name="username" required placeholder="Masukkan username"
                           class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white focus:border-primary-500 focus:outline-none">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-500 text-white font-semibold py-2.5 rounded-xl text-sm">Kirim Undangan</button>
                    <button type="button" onclick="document.getElementById('modalInvite').classList.add('hidden')" class="flex-1 border border-slate-600 text-slate-300 py-2.5 rounded-xl text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Create Subtask -->
    <div id="modalCreateSubtask" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-dark-800 border border-slate-700/80 rounded-2xl shadow-2xl w-full max-w-md p-6 animate-slide-up">
            <button onclick="document.getElementById('modalCreateSubtask').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-white">✕</button>
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 bg-emerald-600/20 text-emerald-400 rounded-lg">➕</span>
                Buat Subtask Baru
            </h2>
            <form method="POST" action="../src/controllers/TaskController.php">
                <input type="hidden" name="action" value="create_subtask">
                <input type="hidden" name="project_id" value="<?= $project_id ?>">
                <div class="mb-3">
                    <label class="block text-sm text-slate-300 mb-1.5">Judul <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white focus:border-primary-500 focus:outline-none">
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-slate-300 mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white resize-none focus:border-primary-500 focus:outline-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1.5">Prioritas</label>
                        <select name="priority" class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white">
                            <option value="low">🟢 Rendah</option>
                            <option value="medium" selected>🟡 Sedang</option>
                            <option value="high">🔴 Tinggi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1.5">Deadline</label>
                        <input type="datetime-local" name="deadline_date" min="<?= date('Y-m-d\TH:i') ?>" class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-slate-300 mb-1.5">Assign ke</label>
                    <select name="assigned_to" class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white">
                        <option value="">— Belum di-assign —</option>
                        <?php foreach ($members as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['username']) ?> <?= (int)$m['id'] === (int)$project['owner_id'] ? '👑' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-2.5 rounded-xl text-sm">Buat Subtask</button>
                    <button type="button" onclick="document.getElementById('modalCreateSubtask').classList.add('hidden')" class="flex-1 border border-slate-600 text-slate-300 py-2.5 rounded-xl text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reassign -->
    <div id="modalReassign" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-dark-800 border border-slate-700/80 rounded-2xl shadow-2xl w-full max-w-sm p-6 animate-slide-up">
            <h2 class="text-lg font-bold mb-4">🔄 Reassign Subtask</h2>
            <p id="reassignTitle" class="text-sm text-slate-400 mb-4"></p>
            <form method="POST" action="../src/controllers/LeaderController.php">
                <input type="hidden" name="action" value="reassign_subtask">
                <input type="hidden" name="project_id" value="<?= $project_id ?>">
                <input type="hidden" name="subtask_id" id="reassignSubtaskId" value="">
                <div class="mb-4">
                    <label class="block text-sm text-slate-300 mb-1.5">Assign ke member baru</label>
                    <select name="new_user_id" required class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white">
                        <?php foreach ($members as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-500 text-white font-semibold py-2.5 rounded-xl text-sm">Reassign</button>
                    <button type="button" onclick="document.getElementById('modalReassign').classList.add('hidden')" class="flex-1 border border-slate-600 text-slate-300 py-2.5 rounded-xl text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
    function openReassign(subtaskId, title) {
        document.getElementById('reassignSubtaskId').value = subtaskId;
        document.getElementById('reassignTitle').textContent = 'Subtask: ' + title;
        document.getElementById('modalReassign').classList.remove('hidden');
    }
    </script>
    <script src="js/countdown.js"></script>
</body>
</html>