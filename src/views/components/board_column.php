<?php
// src/views/components/board_column.php
// Deskripsi: Komponen reusable kolom Kanban Board.
//             Menerima variabel $column dari parent (index.php) melalui foreach loop.
//             Struktur $column: ['id', 'title', 'status', 'tasks' => [...]]

$priority_config = [
    'high'   => [
        'label' => 'Tinggi',
        'badge' => 'bg-red-500/10 text-red-400 border border-red-500/20',
        'dot'   => 'bg-red-400',
    ],
    'medium' => [
        'label' => 'Sedang',
        'badge' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
        'dot'   => 'bg-amber-400',
    ],
    'low'    => [
        'label' => 'Rendah',
        'badge' => 'bg-slate-500/10 text-slate-400 border border-slate-500/20',
        'dot'   => 'bg-slate-500',
    ],
];

$column_config = [
    'todo'  => [
        'indicator' => 'bg-slate-400',
        'header_bg' => 'bg-slate-400/10 border-slate-400/20',
        'count_bg'  => 'bg-slate-700/60 text-slate-300',
    ],
    'doing' => [
        'indicator' => 'bg-primary-500',
        'header_bg' => 'bg-primary-500/10 border-primary-500/20',
        'count_bg'  => 'bg-primary-500/20 text-primary-300',
    ],
    'done'  => [
        'indicator' => 'bg-emerald-500',
        'header_bg' => 'bg-emerald-500/10 border-emerald-500/20',
        'count_bg'  => 'bg-emerald-500/20 text-emerald-300',
    ],
];

$col_cfg   = $column_config[$column['status']] ?? $column_config['todo'];
$task_count = count($column['tasks']);
?>

<div class="bg-dark-800/60 backdrop-blur-sm border border-slate-700/50 rounded-2xl flex flex-col overflow-hidden shadow-xl animate-slide-up">

    <!-- Column Header -->
    <div class="px-4 pt-4 pb-3 border-b border-slate-700/50">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 <?= $col_cfg['indicator'] ?>"></span>
                <h3 class="text-sm font-semibold text-white tracking-wide">
                    <?= htmlspecialchars($column['title']) ?>
                </h3>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?= $col_cfg['count_bg'] ?>">
                    <?= $task_count ?>
                </span>
            </div>
            <button onclick="document.getElementById('modalCreateTask').classList.remove('hidden')"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-white hover:bg-slate-700/40
                           transition-colors duration-200"
                    aria-label="Tambah tugas ke kolom <?= htmlspecialchars($column['title']) ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Task Cards -->
    <div class="flex flex-col gap-3 p-3 col-scroll overflow-y-auto max-h-[65vh]">

        <?php if (empty($column['tasks'])): ?>
        <div class="flex flex-col items-center justify-center py-10 text-center">
            <div class="w-12 h-12 rounded-2xl bg-slate-800 border border-slate-700/50 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="text-sm text-slate-600">Belum ada tugas</p>
        </div>
        <?php endif; ?>

        <?php foreach ($column['tasks'] as $task): ?>
            <?php include __DIR__ . '/task_card.php'; ?>
        <?php endforeach; ?>

    </div>
</div>