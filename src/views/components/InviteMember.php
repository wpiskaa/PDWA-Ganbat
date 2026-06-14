<?php
// src/views/components/invite_member.php
// Komponen UI form undangan anggota ke dalam proyek
// Cara pakai: include file ini di dalam public/project_detail.php
// Variabel yang dibutuhkan dari parent:
//   $project_id  → ID proyek yang sedang dibuka
//   $pdo         → koneksi database PDO

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_user_id = (int) $_SESSION['user_id'];

// Ambil daftar semua user KECUALI diri sendiri dan yang sudah ada di proyek ini
$stmtUsers = $pdo->prepare(
    "SELECT u.id, u.username
     FROM users u
     WHERE u.id != :current_user_id
       AND u.id NOT IN (
           SELECT user_id FROM project_members WHERE project_id = :project_id
       )
     ORDER BY u.username ASC"
);
$stmtUsers->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
$stmtUsers->bindParam(':project_id',      $project_id,      PDO::PARAM_INT);
$stmtUsers->execute();
$availableUsers = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ============================================================
     TOMBOL BUKA MODAL INVITE
     Tombol ini hanya muncul jika user adalah owner proyek
     ============================================================ -->
<button onclick="document.getElementById('modalInviteMember').classList.remove('hidden')"
        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700
               text-white text-sm font-semibold px-4 py-2 rounded-lg
               transition-colors duration-200 shadow">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
    </svg>
    Undang Anggota
</button>

<!-- ============================================================
     MODAL FORM UNDANG ANGGOTA
     ============================================================ -->
<div id="modalInviteMember"
     class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 relative">

        <!-- Tombol tutup modal -->
        <button onclick="document.getElementById('modalInviteMember').classList.add('hidden')"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <h2 class="text-lg font-bold text-gray-800 mb-1">👥 Undang Anggota</h2>
        <p class="text-sm text-gray-500 mb-5">
            Pilih pengguna yang ingin kamu undang ke proyek ini.
        </p>

        <?php if (empty($availableUsers)): ?>
            <!-- Tampilkan pesan jika tidak ada user yang bisa diundang -->
            <div class="text-center py-6 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                </svg>
                <p class="text-sm">Semua pengguna sudah menjadi anggota proyek ini.</p>
            </div>
        <?php else: ?>
            <!-- Form invite — dikirim ke InviteController.php -->
            <form method="POST" action="../../src/controllers/InviteController.php">
                <input type="hidden" name="action"     value="invite_member">
                <input type="hidden" name="project_id" value="<?= (int) $project_id ?>">

                <!-- Dropdown pilih user -->
                <div class="mb-5">
                    <label for="invitee_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Pilih Pengguna <span class="text-red-500">*</span>
                    </label>
                    <select id="invitee_id" name="invitee_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white
                                   focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                        <option value="" disabled selected>-- Pilih pengguna --</option>
                        <?php foreach ($availableUsers as $user): ?>
                            <option value="<?= (int) $user['id'] ?>">
                                <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Info status undangan -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-5">
                    <p class="text-xs text-blue-700">
                        ℹ️ Undangan akan dikirim dengan status <strong>Pending</strong>.
                        Anggota perlu menerima undangan terlebih dahulu sebelum bisa mengakses proyek.
                    </p>
                </div>

                <!-- Tombol aksi -->
                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white
                                   font-semibold py-2 rounded-lg text-sm transition-colors duration-200">
                        Kirim Undangan
                    </button>
                    <button type="button"
                            onclick="document.getElementById('modalInviteMember').classList.add('hidden')"
                            class="flex-1 border border-gray-300 hover:bg-gray-50 text-gray-700
                                   font-semibold py-2 rounded-lg text-sm transition-colors duration-200">
                        Batal
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================
     DAFTAR ANGGOTA PROYEK (status invite)
     Ditampilkan di bawah tombol invite sebagai tabel ringkas
     ============================================================ -->
<?php
// Ambil daftar anggota yang sudah diundang (pending & accepted)
$stmtMembers = $pdo->prepare(
    "SELECT u.username, pm.status_invite
     FROM project_members pm
     JOIN users u ON u.id = pm.user_id
     WHERE pm.project_id = :project_id
     ORDER BY pm.status_invite ASC, u.username ASC"
);
$stmtMembers->bindParam(':project_id', $project_id, PDO::PARAM_INT);
$stmtMembers->execute();
$members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!empty($members)): ?>
<div class="mt-4">
    <h3 class="text-sm font-semibold text-gray-700 mb-2">Daftar Anggota</h3>
    <div class="space-y-2">
        <?php foreach ($members as $member): ?>
            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                <span class="text-sm text-gray-700 font-medium">
                    <?= htmlspecialchars($member['username'], ENT_QUOTES, 'UTF-8') ?>
                </span>
                <!-- Badge status invite -->
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                    <?= $member['status_invite'] === 'accepted'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-yellow-100 text-yellow-700' ?>">
                    <?= $member['status_invite'] === 'accepted' ? '✅ Accepted' : '⏳ Pending' ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>