<!-- src/views/components/task_card.php -->

<?php
/**
 * Required Variables:
 * $task
 * $currentUserId
 */

$canUpdate = ((int)$task['assigned_to'] === (int)$currentUserId);

$nextStatus = null;

if ($task['status'] === 'todo') {
    $nextStatus = 'ongoing';
} elseif ($task['status'] === 'ongoing') {
    $nextStatus = 'done';
}
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <div class="mb-3">
        <h4 class="font-semibold text-gray-800 break-words">
            <?= htmlspecialchars($task['title']) ?>
        </h4>
    </div>

    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
        <span>
            Deadline:
            <?= !empty($task['deadline_date'])
                ? htmlspecialchars($task['deadline_date'])
                : '-' ?>
        </span>

        <span class="px-2 py-1 rounded-full text-xs font-medium
            <?php if ($task['status'] === 'todo'): ?>
                bg-gray-200 text-gray-700
            <?php elseif ($task['status'] === 'ongoing'): ?>
                bg-yellow-100 text-yellow-700
            <?php else: ?>
                bg-green-100 text-green-700
            <?php endif; ?>">
            <?= ucfirst(htmlspecialchars($task['status'])) ?>
        </span>

        <!-- Tombol Aksi Status -->
        <div class="flex gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity">
            <?php $status = $task['status'] ?? 'todo'; ?>
            
            <?php if ($status === 'todo'): ?>
                <!-- Todo -> Doing -->
                <form method="POST" action="../src/controllers/TaskController.php" class="m-0">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                    <input type="hidden" name="status" value="doing">
                    <button type="submit" class="p-1 text-xs bg-slate-800 hover:bg-primary-600/20 text-slate-400 hover:text-primary-400 rounded transition border border-transparent hover:border-primary-500/30" title="Mulai Kerja">
                        ▶ Doing
                    </button>
                </form>
            <?php elseif ($status === 'doing'): ?>
                <!-- Doing -> Todo -->
                <form method="POST" action="../src/controllers/TaskController.php" class="m-0">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                    <input type="hidden" name="status" value="todo">
                    <button type="submit" class="p-1 text-xs bg-slate-800 hover:bg-amber-600/20 text-slate-400 hover:text-amber-400 rounded transition border border-transparent hover:border-amber-500/30" title="Kembalikan ke Todo">
                        ↩ Todo
                    </button>
                </form>
                <!-- Doing -> Done -->
                <form method="POST" action="../src/controllers/TaskController.php" class="m-0">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                    <input type="hidden" name="status" value="done">
                    <button type="submit" class="p-1 text-xs bg-slate-800 hover:bg-emerald-600/20 text-slate-400 hover:text-emerald-400 rounded transition border border-transparent hover:border-emerald-500/30" title="Selesaikan">
                        ✓ Done
                    </button>
                </form>
            <?php elseif ($status === 'done'): ?>
                <!-- Done -> Doing -->
                <form method="POST" action="../src/controllers/TaskController.php" class="m-0">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                    <input type="hidden" name="status" value="doing">
                    <button type="submit" class="p-1 text-xs bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-200 rounded transition border border-transparent hover:border-slate-600" title="Kerjakan Kembali">
                        ↩ Doing
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

<<<<<<< HEAD
    <?php if ($canUpdate && $nextStatus !== null): ?>
        <form action="/src/controllers/update_subtask_status.php" method="POST">
            <input
                type="hidden"
                name="subtask_id"
                value="<?= (int)$task['id'] ?>"
            >

            <input
                type="hidden"
                name="status"
                value="<?= htmlspecialchars($nextStatus) ?>"
            >

            <button
                type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 rounded-lg transition"
            >
                Move to <?= ucfirst($nextStatus) ?>
            </button>
        </form>
    <?php endif; ?>
=======
    <!-- Judul & Deskripsi -->
    <div>
        <h4 class="text-sm font-semibold text-white leading-snug mb-1">
            <?= htmlspecialchars($task['title']) ?>
        </h4>
        <?php if (!empty($task['description'])): ?>
            <p class="text-xs text-slate-400 leading-relaxed line-clamp-2">
                <?= htmlspecialchars($task['description']) ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Real-time Countdown (Sesuai countdown.js) -->
    <?php if (!empty($task['deadline_date'])): ?>
        <div class="task-countdown mt-1" data-deadline="<?= htmlspecialchars($task['deadline_date']) ?>">
            <span class="countdown-badge inline-flex items-center gap-1.5 text-[11px] font-semibold px-2 py-0.5 rounded-full">
                <span class="countdown-dot w-1.5 h-1.5 rounded-full"></span>
                <span class="countdown-label">Membaca waktu...</span>
            </span>
        </div>
    <?php endif; ?>

    <!-- Member Assignees -->
    <div class="flex items-center justify-between border-t border-slate-800/80 pt-2.5">
        <!-- Avatar List -->
        <div class="flex -space-x-1.5 overflow-hidden">
            <?php if (empty($task['assignees'])): ?>
                <span class="text-[10px] text-slate-500 italic">Belum ditugaskan</span>
            <?php else: ?>
                <?php foreach ($task['assignees'] as $idx => $assignee): 
                    if ($idx >= 3): ?>
                        <div class="w-5 h-5 rounded-full bg-slate-800 border border-dark-900 flex items-center justify-center text-slate-400 text-[8px] font-bold z-10" title="Dan lainnya">
                            +<?= count($task['assignees']) - 3 ?>
                        </div>
                        <?php break; 
                    endif; ?>
                    <div class="w-5 h-5 rounded-full bg-primary-600 border border-dark-900 flex items-center justify-center text-white text-[9px] font-bold" 
                         style="z-index: <?= 10 - $idx ?>"
                         title="<?= htmlspecialchars($assignee['username']) ?>">
                        <?= htmlspecialchars(strtoupper(substr($assignee['username'], 0, 1))) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Popover Pendelegasian (Assign Member) -->
        <div class="relative">
            <button type="button" 
                    onclick="document.getElementById('assign-popover-<?= $task['id'] ?>').classList.toggle('hidden')" 
                    class="text-[10px] bg-slate-800/80 hover:bg-slate-700 border border-slate-700/50 text-slate-300 hover:text-white px-2 py-1 rounded transition flex items-center gap-1">
                👤 Delegasi
            </button>
            <div id="assign-popover-<?= $task['id'] ?>" 
                 class="hidden absolute right-0 bottom-full mb-2 z-50 bg-dark-800 border border-slate-700/80 rounded-xl p-3 w-48 shadow-2xl text-left">
                <h5 class="text-xs font-bold text-white mb-2 pb-1 border-b border-slate-700">Tugaskan Anggota</h5>
                <form method="POST" action="../src/controllers/TaskController.php">
                    <input type="hidden" name="action" value="assign_member">
                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                    
                    <div class="max-h-32 overflow-y-auto mb-3.5 space-y-1.5 col-scroll">
                        <?php 
                        $assigned_ids = array_column($task['assignees'] ?? [], 'id');
                        if (empty($all_users)): ?>
                            <p class="text-[10px] text-slate-500 italic">Tidak ada anggota lain</p>
                        <?php else: ?>
                            <?php foreach ($all_users as $user): ?>
                                <label class="flex items-center gap-2 text-xs text-slate-300 hover:text-white cursor-pointer">
                                    <input type="checkbox" name="user_ids[]" value="<?= $user['id'] ?>" <?= in_array($user['id'], $assigned_ids) ? 'checked' : '' ?> class="rounded bg-dark-900 border-slate-600 text-primary-600 focus:ring-primary-500 focus:ring-offset-dark-900">
                                    <?= htmlspecialchars($user['username']) ?>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="w-full text-center py-1 bg-primary-600 hover:bg-primary-500 text-white rounded text-xs font-semibold transition shadow-md shadow-primary-600/20">
                        Simpan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Pemicu Komentar (Comment toggle) -->
    <div class="flex justify-end pt-0.5">
        <button type="button" 
                onclick="document.getElementById('comments-section-<?= $task['id'] ?>').classList.toggle('hidden')" 
                class="text-[11px] text-slate-400 hover:text-white transition flex items-center gap-1.5">
            💬 Diskusi
        </button>
    </div>

    <!-- Area Komentar (Hidden by default) -->
    <div id="comments-section-<?= $task['id'] ?>" class="hidden pt-1.5">
        <?php 
        $task_id = $task['id']; 
        include __DIR__ . '/comment_section.php'; 
        ?>
    </div>

>>>>>>> f9866c907aa95f92b84669ac7c9d8bc26de5253f
</div>