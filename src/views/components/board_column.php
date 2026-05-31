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
            <button class="p-1.5 rounded-lg text-slate-500 hover:text-white hover:bg-slate-700/40
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

        <?php foreach ($column['tasks'] as $task):
            $p          = $priority_config[$task['priority']] ?? $priority_config['low'];
            $deadline   = strtotime($task['deadline']);
            $is_overdue = $deadline < time() && $column['status'] !== 'done';
            $date_label = date('d M Y', $deadline);
        ?>
        <div class="card-transition bg-dark-900/60 border border-slate-700/40 hover:border-slate-600/70
                    rounded-xl p-4 cursor-pointer group shadow-sm">

            <!-- Priority Badge -->
            <div class="flex items-center justify-between mb-3">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full <?= $p['badge'] ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?= $p['dot'] ?>"></span>
                    <?= $p['label'] ?>
                </span>

                <button class="opacity-0 group-hover:opacity-100 p-1 rounded-lg text-slate-500
                               hover:text-white hover:bg-slate-700/60 transition-all duration-200"
                        aria-label="Opsi tugas">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"/>
                    </svg>
                </button>
            </div>

            <!-- Title & Description -->
            <h4 class="text-sm font-semibold text-white leading-snug mb-1.5">
                <?= htmlspecialchars($task['title']) ?>
            </h4>
            <p class="text-xs text-slate-500 leading-relaxed line-clamp-2 mb-4">
                <?= htmlspecialchars($task['desc']) ?>
            </p>

            <!-- Footer: Assignees & Deadline -->
            <div class="flex items-center justify-between">

                <!-- Assignee Avatars -->
                <div class="flex -space-x-2">
                    <?php foreach ($task['assignees'] as $i => $initial):
                        if ($i >= 3): ?>
                        <div class="w-6 h-6 rounded-full bg-slate-700 border-2 border-dark-900
                                    flex items-center justify-center text-slate-400 text-[9px] font-bold z-10">
                            +<?= count($task['assignees']) - 3 ?>
                        </div>
                        <?php break; endif; ?>
                        <div class="w-6 h-6 rounded-full bg-primary-600 border-2 border-dark-900
                                    flex items-center justify-center text-white text-[9px] font-bold"
                             style="z-index: <?= 10 - $i ?>">
                            <?= htmlspecialchars(strtoupper($initial)) ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Deadline -->
                <div class="flex items-center gap-1 text-xs
                            <?= $column['status'] === 'done'
                                ? 'text-emerald-500'
                                : ($is_overdue ? 'text-red-400' : 'text-slate-500') ?>">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <?php if ($column['status'] === 'done'): ?>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    <?php endif; ?>
                    <?= $date_label ?>
                </div>

            </div>
        </div>
        <?php endforeach; ?>

    </div>
</div>