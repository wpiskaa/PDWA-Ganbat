<?php
// src/views/components/comment_section.php
// $task_id dan $pdo harus sudah tersedia saat komponen ini dipanggil
require_once __DIR__ . '/../../../src/controllers/CommentController.php';
$comments = getCommentsByTask($pdo, $task_id);
?>

<div class="mt-3 border-t border-slate-850 pt-3">
    <h4 class="text-xs font-semibold text-slate-300 mb-2.5 flex items-center gap-1.5">
        <span>💬</span> Diskusi Tugas
    </h4>

    <!-- Daftar komentar -->
    <div class="space-y-2 max-h-40 overflow-y-auto mb-3 col-scroll">
        <?php if (empty($comments)): ?>
            <p class="text-[10px] text-slate-500 italic">Belum ada komentar di tugas ini.</p>
        <?php else: ?>
            <?php foreach ($comments as $c): ?>
                <div class="bg-dark-950/65 border border-slate-800/60 rounded-xl p-2.5 text-[11px] leading-relaxed">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="font-bold text-primary-400">
                            <?= htmlspecialchars($c['username']) ?>
                        </span>
                        <span class="text-[9px] text-slate-500">
                            <?= date('d M, H:i', strtotime($c['created_at'])) ?>
                        </span>
                    </div>
                    <p class="text-slate-300">
                        <?= htmlspecialchars($c['comment_text']) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Form kirim komentar baru -->
    <form action="../src/controllers/CommentController.php" method="POST" class="m-0 flex flex-col gap-1.5">
        <input type="hidden" name="task_id" value="<?= $task_id ?>">
        <textarea
            name="comment_text"
            rows="2"
            required
            placeholder="Tulis komentar diskusi..."
            class="w-full bg-dark-950 border border-slate-700/60 rounded-lg p-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 resize-none"
        ></textarea>
        <button
            type="submit"
            class="self-end px-3 py-1 bg-primary-600 hover:bg-primary-500 text-white text-[11px] font-semibold rounded-lg transition shadow-md shadow-primary-600/20"
        >
            Kirim
        </button>
    </form>
</div>