<?php
// public/index.php
// Tugas: Bima Baraja — Project Creation & Visibility (Ganbat V2)
// Deskripsi: Halaman utama menampilkan daftar Project milik user
//             (sebagai owner atau member dengan status_invite 'accepted'),
//             beserta form untuk membuat Project baru.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$current_user = $_SESSION['username'] ?? 'User';
$user_id      = $_SESSION['user_id'];

// Muat koneksi database
require_once __DIR__ . '/../src/config/database.php';

// Ambil flash message
$error   = $_SESSION['error']   ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

if (isset($_GET['status']) && $_GET['status'] === 'archived_success') {
    $success = 'Project berhasil diarsipkan.';
}
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized_archive') {
    $error = 'Kamu tidak punya izin untuk mengarsipkan project ini.';
}

// Ambil semua project di mana user adalah owner ATAU member dengan status accepted
$projects = [];
try {
    $stmt = $pdo->prepare(
        "SELECT DISTINCT p.id, p.title, p.description, p.owner_id, p.is_archived, p.created_at,
                u.username AS owner_name,
                (SELECT COUNT(*) FROM subtasks s WHERE s.project_id = p.id) AS total_subtasks,
                (SELECT COUNT(*) FROM subtasks s WHERE s.project_id = p.id AND s.status = 'done') AS done_subtasks
         FROM projects p
         JOIN users u ON u.id = p.owner_id
         LEFT JOIN project_members pm ON pm.project_id = p.id
         WHERE p.is_archived = 0
           AND (
                p.owner_id = :user_id
                OR (pm.user_id = :user_id2 AND pm.status_invite = 'accepted')
           )
         ORDER BY p.created_at DESC"
    );
    $stmt->execute([
        ':user_id'  => $user_id,
        ':user_id2' => $user_id,
    ]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Gagal mengambil data project: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects — Ganbat</title>
    <meta name="description" content="Daftar project manajemen tugas tim Ganbat.">
    <!-- Tailwind CSS (Local via style.css) -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-dark-950 min-h-screen font-sans text-white">

    <!-- Background Glow -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-primary-600 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-blue-500 rounded-full opacity-10 blur-3xl"></div>
    </div>

    <?php include __DIR__ . '/../src/views/components/navbar.php'; ?>

    <main class="relative px-4 py-6 md:px-8 md:py-8 max-w-screen-xl mx-auto animate-fade-in">

        <!-- Flash Messages -->
        <?php if (!empty($error)): ?>
        <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 mb-5 text-sm animate-fade-in">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
        <div class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 mb-5 text-sm animate-fade-in">
            <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Project Saya</h2>
                <p class="text-slate-400 text-sm mt-0.5">Daftar project yang kamu pimpin atau ikuti.</p>
            </div>
            <button onclick="document.getElementById('modalCreateProject').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-500 active:bg-primary-700
                           text-white text-sm font-semibold px-4 py-2.5 rounded-xl
                           transition-all duration-200 shadow-lg shadow-primary-600/30
                           hover:scale-[1.02] active:scale-[0.98] self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Buat Project
            </button>
        </div>

        <!-- Project Grid -->
        <?php if (empty($projects)): ?>
        <div class="flex flex-col items-center justify-center text-center py-20 bg-dark-800/40 border border-slate-700/50 rounded-2xl">
            <div class="w-14 h-14 rounded-2xl bg-slate-800 border border-slate-700/50 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
            <p class="text-slate-400 text-sm">Belum ada project. Klik "Buat Project" untuk mulai.</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($projects as $project):
                $progress = $project['total_subtasks'] > 0
                    ? round(($project['done_subtasks'] / $project['total_subtasks']) * 100)
                    : 0;
                $is_owner = (int)$project['owner_id'] === (int)$user_id;
            ?>
            <div class="bg-dark-800/60 backdrop-blur-sm border border-slate-700/50 hover:border-primary-500/50
                        rounded-2xl p-5 shadow-xl transition-all duration-200 hover:-translate-y-1 animate-slide-up">

                <a href="project_detail.php?id=<?= (int)$project['id'] ?>" class="block">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-base font-semibold text-white leading-snug pr-2">
                            <?= htmlspecialchars($project['title']) ?>
                        </h3>
                        <?php if ($is_owner): ?>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-primary-500/10 text-primary-300 border border-primary-500/20 flex-shrink-0">
                            Ketua
                        </span>
                        <?php else: ?>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-500/10 text-slate-400 border border-slate-500/20 flex-shrink-0">
                            Anggota
                        </span>
                        <?php endif; ?>
                    </div>

                    <p class="text-xs text-slate-500 leading-relaxed line-clamp-2 mb-4">
                        <?= htmlspecialchars($project['description'] ?: 'Tidak ada deskripsi.') ?>
                    </p>

                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                            <span>Progress</span>
                            <span><?= $progress ?>%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-primary-500 rounded-full" style="width: <?= $progress ?>%"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Owner: <?= htmlspecialchars($project['owner_name']) ?></span>
                        <span><?= $project['done_subtasks'] ?>/<?= $project['total_subtasks'] ?> subtask</span>
                    </div>
                </a>

                <?php if ($is_owner): ?>
                <form action="../src/controllers/ProjectController.php" method="POST"
                      onsubmit="return confirm('Arsipkan project ini?');" class="mt-4 pt-3 border-t border-slate-700/50">
                    <input type="hidden" name="action" value="archive">
                    <input type="hidden" name="project_id" value="<?= (int)$project['id'] ?>">
                    <button type="submit" class="text-xs text-slate-500 hover:text-red-400 transition-colors duration-200">
                        Arsipkan project
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </main>

    <footer class="relative text-center text-xs text-slate-600 py-6 mt-4">
        &copy; <?= date('Y') ?> Ganbat &mdash; Sistem Manajemen Tugas
    </footer>

    <!-- Modal Create Project -->
    <div id="modalCreateProject"
         class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-dark-800 border border-slate-700/80 rounded-2xl shadow-2xl w-full max-w-md p-6 relative animate-slide-up text-white">
            <button onclick="document.getElementById('modalCreateProject').classList.add('hidden')"
                    class="absolute top-4 right-4 text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 bg-primary-600/20 text-primary-400 rounded-lg">📁</span>
                Buat Project Baru
            </h2>

            <form method="POST" action="../src/controllers/ProjectController.php">
                <input type="hidden" name="action" value="create_project">

                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Judul Project <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" required maxlength="100"
                           placeholder="Contoh: Website Toko Online"
                           class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500
                                  focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                </div>

                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Deskripsi
                    </label>
                    <textarea id="description" name="description" rows="3" maxlength="255"
                              placeholder="Jelaskan singkat tujuan project ini..."
                              class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500
                                     focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all resize-none"></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 bg-primary-600 hover:bg-primary-500 active:bg-primary-700 text-white font-semibold
                                   py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-primary-600/30">
                        Buat Project
                    </button>
                    <button type="button"
                            onclick="document.getElementById('modalCreateProject').classList.add('hidden')"
                            class="flex-1 border border-slate-600 hover:bg-slate-700/40 text-slate-300
                                   font-semibold py-2.5 rounded-xl text-sm transition-all">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>