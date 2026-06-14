<?php
/**
 * ============================================================
 *  GANBAT - Sistem Manajemen Tugas
 *  File   : src/views/components/navbar.php
 * ============================================================
 */
$nav_username = $_SESSION['username'] ?? 'User';
$nav_profile_picture = $_SESSION['profile_picture'] ?? '';
?>

<nav class="sticky top-0 z-50 bg-dark-900/80 backdrop-blur-xl border-b border-slate-700/60">
    <div class="max-w-screen-xl mx-auto px-4 md:px-8 py-3 flex items-center justify-between">

        <!-- Left: Brand -->
        <a href="/Ganbat-project/public/my_project.php" class="flex items-center gap-2.5">
            <div class="w-9 h-9 bg-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-600/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <span class="text-xl font-bold text-white tracking-tight">Ganbat</span>
        </a>

        <!-- Center: Navigation -->
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        <div class="flex items-center gap-1 bg-dark-800/50 p-1 rounded-xl border border-slate-700/50">
            <a href="my_task.php" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $current_page === 'my_task.php' ? 'bg-primary-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-700/50' ?>">
                My Task
            </a>
            <a href="my_project.php" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= ($current_page === 'my_project.php' || $current_page === 'index.php') ? 'bg-primary-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-700/50' ?>">
                My Project
            </a>
            <a href="laporan.php" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $current_page === 'laporan.php' ? 'bg-primary-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-700/50' ?>">
                Laporan
            </a>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-3">

            <!-- Notification Bell -->
            <div class="relative" id="notif-container">
                <button id="notif-bell" onclick="toggleNotifications()"
                        class="relative p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/40 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span id="notif-badge" class="hidden absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">0</span>
                </button>

                <!-- Notification Dropdown -->
                <div id="notif-dropdown" class="hidden absolute right-0 top-full mt-2 w-80 bg-dark-800 border border-slate-700/80 rounded-2xl shadow-2xl overflow-hidden z-50">
                    <div class="flex justify-between items-center px-4 py-3 border-b border-slate-700/60">
                        <h3 class="text-sm font-semibold text-white">Notifikasi</h3>
                        <button onclick="markAllRead()" class="text-xs text-primary-400 hover:text-primary-300">Tandai semua dibaca</button>
                    </div>
                    <div id="notif-list" class="max-h-[65vh] overflow-y-auto">
                        <p class="text-slate-400 text-sm p-4 text-center">Memuat...</p>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div class="relative" id="user-menu-container">
                <button onclick="toggleUserMenu()" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-700/40 transition-all">
                    <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-sm font-bold overflow-hidden">
                        <?php if ($nav_profile_picture): ?>
                            <img src="/Ganbat-project/public/<?= htmlspecialchars($nav_profile_picture) ?>" class="w-full h-full object-cover" alt="Avatar">
                        <?php else: ?>
                            <?= strtoupper(substr($nav_username, 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <span class="text-sm font-medium text-slate-300 hidden sm:block"><?= htmlspecialchars($nav_username) ?></span>
                </button>

                <div id="user-dropdown" class="hidden absolute right-0 top-full mt-2 w-48 bg-dark-800 border border-slate-700/80 rounded-xl shadow-2xl overflow-hidden z-50">
                    <a href="/Ganbat-project/public/profile.php" class="block px-4 py-2.5 text-sm text-slate-300 hover:bg-slate-700/40 hover:text-white transition-colors">
                        👤 Profil Saya
                    </a>
                    <hr class="border-slate-700/60">
                    <a href="/Ganbat-project/src/controllers/AuthController.php?action=logout"
                       class="block px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors">
                        🚪 Logout
                    </a>
                </div>
            </div>

        </div>
    </div>
</nav>

<script>
function toggleNotifications() {
    var dd = document.getElementById('notif-dropdown');
    document.getElementById('user-dropdown').classList.add('hidden');
    dd.classList.toggle('hidden');
    if (!dd.classList.contains('hidden')) fetchNotifications();
}

function toggleUserMenu() {
    document.getElementById('notif-dropdown').classList.add('hidden');
    document.getElementById('user-dropdown').classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
    if (!document.getElementById('notif-container').contains(e.target))
        document.getElementById('notif-dropdown').classList.add('hidden');
    if (!document.getElementById('user-menu-container').contains(e.target))
        document.getElementById('user-dropdown').classList.add('hidden');
});

function fetchNotifications() {
    fetch('/Ganbat-project/src/controllers/NotificationController.php?action=get_notifications')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) return;

            var badge = document.getElementById('notif-badge');
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            var list = document.getElementById('notif-list');
            if (data.notifications.length === 0) {
                list.innerHTML = '<p class="text-slate-400 text-sm p-4 text-center">Tidak ada notifikasi.</p>';
                return;
            }

            list.innerHTML = data.notifications.map(function(n) {
                var isUnread = n.is_read == 0;
                var actions = '';

                if (n.type === 'invite' && isUnread) {
                    actions = '<div class="flex gap-2 mt-2">' +
                        '<form method="POST" action="/Ganbat-project/src/controllers/NotificationController.php" class="inline">' +
                            '<input type="hidden" name="action" value="accept_invite">' +
                            '<input type="hidden" name="notification_id" value="' + n.id + '">' +
                            '<button class="text-xs bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1 rounded-lg">Terima</button>' +
                        '</form>' +
                        '<form method="POST" action="/Ganbat-project/src/controllers/NotificationController.php" class="inline">' +
                            '<input type="hidden" name="action" value="decline_invite">' +
                            '<input type="hidden" name="notification_id" value="' + n.id + '">' +
                            '<button class="text-xs bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded-lg">Tolak</button>' +
                        '</form>' +
                    '</div>';
                }

                return '<div class="px-4 py-3 border-b border-slate-700/40 ' + (isUnread ? 'bg-primary-500/5' : '') + '">' +
                    '<p class="text-sm text-slate-300">' + n.message + '</p>' +
                    '<p class="text-xs text-slate-500 mt-1">' + new Date(n.created_at).toLocaleString('id-ID') + '</p>' +
                    actions +
                '</div>';
            }).join('');
        })
        .catch(function(err) { console.error('Fetch notifications error:', err); });
}

function markAllRead() {
    fetch('/Ganbat-project/src/controllers/NotificationController.php?action=mark_all_read')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('notif-badge').classList.add('hidden');
                fetchNotifications();
            }
        });
}

document.addEventListener('DOMContentLoaded', function() {
    fetch('/Ganbat-project/src/controllers/NotificationController.php?action=get_notifications')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.unread_count > 0) {
                var badge = document.getElementById('notif-badge');
                badge.textContent = data.unread_count;
                badge.classList.remove('hidden');
            }
        })
        .catch(function() {});
});
</script>