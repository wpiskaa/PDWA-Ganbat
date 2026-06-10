<?php
// public/index.php
// Ditulis oleh: Bima Baraja
// Deskripsi: Halaman utama Kanban Board — menampilkan 3 kolom (Todo, Doing, Done)
//             beserta navbar dan card tugas dinamis dari database.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

$current_user = $_SESSION['username'] ?? 'User';

// Muat koneksi database
require_once __DIR__ . '/../src/config/database.php';

// Ambil semua user teregistrasi untuk dropdown assignees
$all_users = [];
try {
    $stmt_users = $pdo->query("SELECT id, username FROM users ORDER BY username ASC");
    $all_users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Gagal mengambil users: " . $e->getMessage());
}

// Inisialisasi struktur kolom default
$tasks_by_status = [
    'todo'  => [],
    'doing' => [],
    'done'  => [],
];

try {
    // Ambil semua task dari database
    $stmt_tasks = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
    $db_tasks = $stmt_tasks->fetchAll(PDO::FETCH_ASSOC);

    // Ambil relasi penugasan tugas (assignees)
    $assignees_by_task = [];
    $stmt_assignees = $pdo->query("
        SELECT ta.task_id, u.id as user_id, u.username
        FROM task_assignees ta
        JOIN users u ON ta.user_id = u.id
    ");
    while ($row = $stmt_assignees->fetch(PDO::FETCH_ASSOC)) {
        $assignees_by_task[$row['task_id']][] = [
            'id'       => $row['user_id'],
            'username' => $row['username'],
        ];
    }

    // Kelompokkan tugas berdasarkan statusnya
    foreach ($db_tasks as $task) {
        $status = strtolower($task['status'] ?? 'todo');
        if (!array_key_exists($status, $tasks_by_status)) {
            $status = 'todo';
        }
        
        $tasks_by_status[$status][] = [
            'id'            => $task['id'],
            'title'         => $task['title'],
            'description'   => $task['description'],
            'priority'      => $task['priority'] ?? 'medium',
            'deadline_date' => $task['deadline_date'],
            'status'        => $status,
            'assignees'     => $assignees_by_task[$task['id']] ?? [],
        ];
    }
} catch (PDOException $e) {
    error_log("Gagal mengambil data tugas: " . $e->getMessage());
}

$columns = [
    [
        'id'     => 'todo',
        'title'  => 'Todo',
        'status' => 'todo',
        'tasks'  => $tasks_by_status['todo'],
    ],
    [
        'id'     => 'doing',
        'title'  => 'Doing',
        'status' => 'doing',
        'tasks'  => $tasks_by_status['doing'],
    ],
    [
        'id'     => 'done',
        'title'  => 'Done',
        'status' => 'done',
        'tasks'  => $tasks_by_status['done'],
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board — Ganbat</title>
    <meta name="description" content="Kanban board manajemen tugas tim Ganbat.">
    <!-- Tailwind CSS (Local via style.css) -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .col-scroll { scrollbar-width: thin; scrollbar-color: #334155 transparent; }
        .col-scroll::-webkit-scrollbar { width: 4px; }
        .col-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        .card-transition { transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease; }
        .card-transition:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="bg-dark-950 min-h-screen font-sans text-white">

    <!-- Background Glow -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-primary-600 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-blue-500 rounded-full opacity-10 blur-3xl"></div>
    </div>

    <?php include __DIR__ . '/../src/views/components/navbar.php'; ?>

    <main class="relative px-4 py-6 md:px-8 md:py-8 max-w-screen-xl mx-auto animate-fade-in">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Project Board</h2>
                <p class="text-slate-400 text-sm mt-0.5">Sprint 1 &mdash; Juni 2025</p>
            </div>
            <button onclick="document.getElementById('modalCreateTask').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-500 active:bg-primary-700
                           text-white text-sm font-semibold px-4 py-2.5 rounded-xl
                           transition-all duration-200 shadow-lg shadow-primary-600/30
                           hover:scale-[1.02] active:scale-[0.98] self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Tambah Tugas
            </button>
        </div>

        <!-- Kanban Board -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-start">
            <?php foreach ($columns as $column): ?>
                <?php include __DIR__ . '/../src/views/components/board_column.php'; ?>
            <?php endforeach; ?>
        </div>

    </main>

    <footer class="relative text-center text-xs text-slate-600 py-6 mt-4">
        &copy; <?= date('Y') ?> Ganbat &mdash; Sistem Manajemen Tugas
    </footer>

    <!-- Modal Create Task -->
    <div id="modalCreateTask"
         class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-dark-800 border border-slate-700/80 rounded-2xl shadow-2xl w-full max-w-md p-6 relative animate-slide-up text-white">
            <button onclick="document.getElementById('modalCreateTask').classList.add('hidden')"
                    class="absolute top-4 right-4 text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 bg-primary-600/20 text-primary-400 rounded-lg">➕</span>
                Buat Tugas Baru
            </h2>

            <form method="POST" action="../src/controllers/TaskController.php">
                <input type="hidden" name="action" value="create_task">

                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Judul Tugas <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" required
                           placeholder="Contoh: Desain halaman login"
                           class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500
                                  focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Deskripsi
                    </label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="Jelaskan detail tugas ini..."
                              class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500
                                     focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all resize-none"></textarea>
                </div>

                <div class="mb-4">
                    <label for="priority" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Prioritas
                    </label>
                    <select id="priority" name="priority"
                            class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white
                                   focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                        <option value="low">🟢 Rendah (Low)</option>
                        <option value="medium" selected>🟡 Sedang (Medium)</option>
                        <option value="high">🔴 Tinggi (High)</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="deadline_date" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Deadline
                    </label>
                    <input type="date" id="deadline_date" name="deadline_date"
                           class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 bg-primary-600 hover:bg-primary-500 active:bg-primary-700 text-white font-semibold
                                   py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-primary-600/30">
                        Buat Tugas
                    </button>
                    <button type="button"
                            onclick="document.getElementById('modalCreateTask').classList.add('hidden')"
                            class="flex-1 border border-slate-600 hover:bg-slate-700/40 text-slate-300
                                   font-semibold py-2.5 rounded-xl text-sm transition-all">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script Countdown Realtime -->
    <script src="js/countdown.js"></script>

</body>
</html>