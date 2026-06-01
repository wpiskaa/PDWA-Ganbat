<?php
?>

<div class="bg-white rounded-xl shadow-md p-4 mb-3 border-l-4
    <?php
        echo match($task['priority'] ?? 'medium') {
            'high'   => 'border-red-500',
            'medium' => 'border-yellow-400',
            'low'    => 'border-green-400',
            default  => 'border-gray-300',
        };
    ?> hover:shadow-lg transition-shadow duration-200">

    <div class="flex justify-between items-start mb-2">
        <h3 class="font-semibold text-gray-800 text-sm leading-tight">
            <?= htmlspecialchars($task['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </h3>
        <span class="text-xs font-medium px-2 py-0.5 rounded-full ml-2 shrink-0
            <?php
                echo match($task['priority'] ?? 'medium') {
                    'high'   => 'bg-red-100 text-red-700',
                    'medium' => 'bg-yellow-100 text-yellow-700',
                    'low'    => 'bg-green-100 text-green-700',
                    default  => 'bg-gray-100 text-gray-600',
                };
            ?>">
            <?= ucfirst(htmlspecialchars($task['priority'] ?? 'medium', ENT_QUOTES, 'UTF-8')) ?>
        </span>
    </div>

    <?php if (!empty($task['description'])): ?>
        <p class="text-xs text-gray-500 mb-3 line-clamp-2">
            <?= htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <!-- Deadline -->
    <?php if (!empty($task['deadline_date'])): ?>
        <?php
            $today    = new DateTime('today');
            $deadline = new DateTime($task['deadline_date']);
            $isOverdue = ($deadline < $today && ($task['status'] ?? '') !== 'done');
        ?>
        <div class="flex items-center gap-1 mb-3">
            <svg class="w-3 h-3 <?= $isOverdue ? 'text-red-500' : 'text-gray-400' ?>"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-xs <?= $isOverdue ? 'text-red-500 font-semibold' : 'text-gray-500' ?>">
                <?= $isOverdue ? '⚠ Overdue: ' : '' ?>
                <?= htmlspecialchars(date('d M Y', strtotime($task['deadline_date'])), ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
    <?php endif; ?>

    <div class="text-xs text-blue-500 font-mono mb-3"
         data-countdown="<?= htmlspecialchars($task['deadline_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <!-- Countdown muncul di sini via JavaScript -->
    </div>

    <div class="flex gap-1 mt-2 pt-2 border-t border-gray-100">

        <?php $currentStatus = $task['status'] ?? 'todo'; ?>

        <?php if ($currentStatus === 'todo'): ?>
            <!-- Todo → hanya bisa maju ke Doing -->
            <form method="POST" action="<?= htmlspecialchars('../../src/controllers/TaskController.php') ?>">
                <input type="hidden" name="action"  value="update_status">
                <input type="hidden" name="task_id" value="<?= (int)($task['id'] ?? 0) ?>">
                <input type="hidden" name="status"  value="doing">
                <button type="submit"
                        class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-600 px-2 py-1 rounded transition-colors">
                    ▶ Doing
                </button>
            </form>

        <?php elseif ($currentStatus === 'doing'): ?>
            <!-- Doing → bisa mundur ke Todo atau maju ke Done -->
            <form method="POST" action="<?= htmlspecialchars('../../src/controllers/TaskController.php') ?>">
                <input type="hidden" name="action"  value="update_status">
                <input type="hidden" name="task_id" value="<?= (int)($task['id'] ?? 0) ?>">
                <input type="hidden" name="status"  value="todo">
                <button type="submit"
                        class="text-xs bg-yellow-50 hover:bg-yellow-100 text-yellow-600 px-2 py-1 rounded transition-colors">
                    ↩ Todo
                </button>
            </form>
            <form method="POST" action="<?= htmlspecialchars('../../src/controllers/TaskController.php') ?>">
                <input type="hidden" name="action"  value="update_status">
                <input type="hidden" name="task_id" value="<?= (int)($task['id'] ?? 0) ?>">
                <input type="hidden" name="status"  value="done">
                <button type="submit"
                        class="text-xs bg-green-50 hover:bg-green-100 text-green-600 px-2 py-1 rounded transition-colors">
                    ✓ Done
                </button>
            </form>

        <?php elseif ($currentStatus === 'done'): ?>
            <!-- Done → hanya bisa mundur ke Doing -->
            <form method="POST" action="<?= htmlspecialchars('../../src/controllers/TaskController.php') ?>">
                <input type="hidden" name="action"  value="update_status">
                <input type="hidden" name="task_id" value="<?= (int)($task['id'] ?? 0) ?>">
                <input type="hidden" name="status"  value="doing">
                <button type="submit"
                        class="text-xs bg-gray-50 hover:bg-gray-100 text-gray-600 px-2 py-1 rounded transition-colors">
                    ↩ Doing
                </button>
            </form>

        <?php endif; ?>

    </div>
</div>


<button onclick="document.getElementById('modalCreateTask').classList.remove('hidden')"
        class="fixed bottom-6 right-6 z-40 bg-indigo-600 hover:bg-indigo-700
               text-white font-semibold px-4 py-3 rounded-full shadow-lg
               flex items-center gap-2 transition-colors duration-200">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Tugas Baru
</button>

<div id="modalCreateTask"
     class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 relative">

        <button onclick="document.getElementById('modalCreateTask').classList.add('hidden')"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <h2 class="text-lg font-bold text-gray-800 mb-5">➕ Buat Tugas Baru</h2>

        <form method="POST" action="../../src/controllers/TaskController.php">
            <input type="hidden" name="action" value="create_task">

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    Judul Tugas <span class="text-red-500">*</span>
                </label>
                <input type="text" id="title" name="title" required
                       placeholder="Contoh: Desain halaman login"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Deskripsi
                </label>
                <textarea id="description" name="description" rows="3"
                          placeholder="Jelaskan detail tugas ini..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                                 resize-none"></textarea>
            </div>

            <div class="mb-4">
                <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                    Prioritas
                </label>
                <select id="priority" name="priority"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                               bg-white">
                    <option value="low">🟢 Low</option>
                    <option value="medium" selected>🟡 Medium</option>
                    <option value="high">🔴 High</option>
                </select>
            </div>

            <div class="mb-6">
                <label for="deadline_date" class="block text-sm font-medium text-gray-700 mb-1">
                    Deadline
                </label>
                <input type="date" id="deadline_date" name="deadline_date"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold
                               py-2 rounded-lg text-sm transition-colors duration-200">
                    Buat Tugas
                </button>
                <button type="button"
                        onclick="document.getElementById('modalCreateTask').classList.add('hidden')"
                        class="flex-1 border border-gray-300 hover:bg-gray-50 text-gray-700
                               font-semibold py-2 rounded-lg text-sm transition-colors duration-200">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>