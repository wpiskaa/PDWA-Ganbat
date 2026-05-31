<?php
// public/index.php
// Ditulis oleh: Bima Baraja
// Deskripsi: Halaman utama Kanban Board — menampilkan 3 kolom (Todo, Doing, Done)
//             beserta navbar dan card tugas dummy untuk demonstrasi UI.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

$current_user = $_SESSION['username'] ?? 'User';

$columns = [
    [
        'id'     => 'todo',
        'title'  => 'Todo',
        'status' => 'todo',
        'tasks'  => [
            [
                'title'     => 'Design landing page mockup',
                'desc'      => 'Buat wireframe dan mockup high-fidelity untuk halaman utama.',
                'priority'  => 'high',
                'deadline'  => '2025-06-10',
                'assignees' => ['H', 'B'],
            ],
            [
                'title'     => 'Setup CI/CD pipeline',
                'desc'      => 'Konfigurasi GitHub Actions untuk automated testing dan deployment.',
                'priority'  => 'medium',
                'deadline'  => '2025-06-15',
                'assignees' => ['R'],
            ],
        ],
    ],
    [
        'id'     => 'doing',
        'title'  => 'Doing',
        'status' => 'doing',
        'tasks'  => [
            [
                'title'     => 'Implementasi sistem autentikasi',
                'desc'      => 'Bangun login, register, dan session management dengan PHP native.',
                'priority'  => 'high',
                'deadline'  => '2025-06-08',
                'assignees' => ['H', 'F'],
            ],
            [
                'title'     => 'Kanban Board UI',
                'desc'      => 'Buat tampilan board responsif dengan komponen PHP reusable.',
                'priority'  => 'medium',
                'deadline'  => '2025-06-09',
                'assignees' => ['B'],
            ],
        ],
    ],
    [
        'id'     => 'done',
        'title'  => 'Done',
        'status' => 'done',
        'tasks'  => [
            [
                'title'     => 'Desain skema database',
                'desc'      => 'Definisikan tabel users, tasks, assignees, dan comments.',
                'priority'  => 'high',
                'deadline'  => '2025-06-01',
                'assignees' => ['R', 'F'],
            ],
            [
                'title'     => 'Setup repository proyek',
                'desc'      => 'Inisialisasi Git repo, struktur folder, dan dokumentasi README.',
                'priority'  => 'low',
                'deadline'  => '2025-05-30',
                'assignees' => ['H'],
            ],
        ],
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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        dark: {
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                    animation: {
                        'fade-in':  'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.4s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%':   { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%':   { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
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
            <button class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-500 active:bg-primary-700
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

</body>
</html>