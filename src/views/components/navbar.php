<?php
/**
 * Komponen Navbar – src/views/components/navbar.php
 *
 * Dependensi:
 *   - Sesi aktif dengan $_SESSION['user_id'] dan $_SESSION['username']
 *   - NotificationController sudah di-require sebelum include file ini,
 *     ATAU file ini me-require sendiri (lihat blok di bawah).
 */

if (!class_exists('NotificationController')) {
    require_once __DIR__ . '/../../controllers/NotificationController.php';
}

$navUserId       = (int) ($_SESSION['user_id'] ?? 0);
$navUsername     = htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES);
$navProfilePic   = htmlspecialchars($_SESSION['profile_picture'] ?? '', ENT_QUOTES);

$notifCtrl   = new NotificationController();
$unreadCount = $navUserId ? $notifCtrl->countUnread($navUserId) : 0;
$notifications = $navUserId ? $notifCtrl->getNotifications($navUserId) : [];

/**
 * Cek apakah sebuah notifikasi adalah undangan proyek.
 * Konvensi: pesan mengandung "project_id:<angka>"
 */
function parseInviteFromMessage(string $message): ?int
{
    if (preg_match('/project_id:(\d+)/i', $message, $m)) {
        return (int) $m[1];
    }
    return null;
}
?>

<nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">

      <!-- Logo -->
      <a href="/dashboard" class="flex items-center gap-2 shrink-0">
        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center">
          <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
          </svg>
        </div>
        <span class="text-lg font-bold text-slate-800 tracking-tight">Ganbat</span>
      </a>

      <!-- Nav kanan -->
      <div class="flex items-center gap-3">

        <!-- ─── Bell Dropdown ──────────────────────────────────────── -->
        <div class="relative" id="notif-wrapper">

          <!-- Tombol lonceng -->
          <button
            id="notif-btn"
            type="button"
            aria-label="Notifikasi"
            aria-expanded="false"
            aria-haspopup="true"
            class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-indigo-600 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
            </svg>
            <!-- Badge unread -->
            <?php if ($unreadCount > 0): ?>
            <span
              id="notif-badge"
              class="absolute top-1 right-1 min-w-[1.1rem] h-[1.1rem] bg-red-500 text-white text-[0.6rem] font-bold rounded-full flex items-center justify-center px-0.5 leading-none pointer-events-none"
            ><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
            <?php else: ?>
            <span
              id="notif-badge"
              class="absolute top-1 right-1 min-w-[1.1rem] h-[1.1rem] bg-red-500 text-white text-[0.6rem] font-bold rounded-full flex items-center justify-center px-0.5 leading-none pointer-events-none hidden"
            ></span>
            <?php endif; ?>
          </button>

          <!-- Dropdown panel -->
          <div
            id="notif-dropdown"
            role="menu"
            aria-label="Panel notifikasi"
            class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-xl ring-1 ring-slate-200 overflow-hidden"
          >
            <!-- Header dropdown -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50">
              <h3 class="text-sm font-semibold text-slate-800">Notifikasi</h3>
              <?php if ($unreadCount > 0): ?>
              <button
                id="mark-all-read-btn"
                type="button"
                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors focus:outline-none"
              >Tandai semua dibaca</button>
              <?php endif; ?>
            </div>

            <!-- Daftar notifikasi -->
            <ul id="notif-list" class="max-h-[26rem] overflow-y-auto divide-y divide-slate-100">
              <?php if (empty($notifications)): ?>
              <li id="notif-empty" class="px-4 py-10 text-center">
                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                </svg>
                <p class="text-sm text-slate-400">Tidak ada notifikasi</p>
              </li>
              <?php else: ?>
                <?php foreach ($notifications as $notif):
                    $isRead    = (bool) $notif['is_read'];
                    $projectId = parseInviteFromMessage($notif['message']);
                    // Bersihkan penanda project_id dari tampilan pesan
                    $displayMsg = preg_replace('/\s*project_id:\d+/i', '', $notif['message']);
                    $displayMsg = htmlspecialchars(trim($displayMsg), ENT_QUOTES);
                    $createdAt  = htmlspecialchars($notif['created_at'], ENT_QUOTES);
                    $notifId    = (int) $notif['id'];
                ?>
                <li
                  data-notif-id="<?= $notifId ?>"
                  class="px-4 py-3 <?= $isRead ? 'bg-white' : 'bg-indigo-50/60' ?> hover:bg-slate-50 transition-colors"
                >
                  <div class="flex items-start gap-3">
                    <!-- Dot unread -->
                    <span class="mt-1.5 shrink-0 w-2 h-2 rounded-full <?= $isRead ? 'bg-transparent' : 'bg-indigo-500' ?>"></span>

                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-slate-700 leading-snug"><?= $displayMsg ?></p>
                      <p class="text-xs text-slate-400 mt-0.5"><?= $createdAt ?></p>

                      <!-- Tombol Terima / Tolak (hanya untuk undangan pending) -->
                      <?php if ($projectId !== null && !$isRead): ?>
                      <div class="flex gap-2 mt-2" data-invite-project="<?= $projectId ?>">
                        <button
                          type="button"
                          data-action="accept_invite"
                          data-project-id="<?= $projectId ?>"
                          class="invite-action-btn inline-flex items-center gap-1 px-3 py-1 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                          </svg>
                          Terima
                        </button>
                        <button
                          type="button"
                          data-action="reject_invite"
                          data-project-id="<?= $projectId ?>"
                          class="invite-action-btn inline-flex items-center gap-1 px-3 py-1 rounded-md bg-white hover:bg-red-50 border border-slate-200 hover:border-red-300 text-slate-600 hover:text-red-600 text-xs font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                          </svg>
                          Tolak
                        </button>
                      </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>

            <!-- Footer -->
            <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50">
              <a href="/notifications" class="block text-center text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                Lihat semua notifikasi
              </a>
            </div>
          </div><!-- /dropdown -->
        </div><!-- /notif-wrapper -->

        <!-- Avatar / profil -->
        <div class="relative" id="profile-wrapper">
          <button
            id="profile-btn"
            type="button"
            aria-label="Menu profil"
            class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-lg hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
          >
            <?php if ($navProfilePic): ?>
              <img src="<?= $navProfilePic ?>" alt="<?= $navUsername ?>"
                   class="w-8 h-8 rounded-full object-cover ring-2 ring-slate-200">
            <?php else: ?>
              <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                <?= mb_strtoupper(mb_substr($navUsername, 0, 1)) ?>
              </div>
            <?php endif; ?>
            <span class="hidden sm:block text-sm font-medium text-slate-700"><?= $navUsername ?></span>
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
            </svg>
          </button>

          <div id="profile-dropdown"
               class="hidden absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-xl ring-1 ring-slate-200 py-1 overflow-hidden">
            <a href="/profile" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
              <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
              </svg>
              Profil Saya
            </a>
            <hr class="my-1 border-slate-100">
            <a href="/logout" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
              </svg>
              Keluar
            </a>
          </div>
        </div><!-- /profile-wrapper -->

      </div>
    </div>
  </div>
</nav>

<!-- ─── Inline JS (vanilla, no jQuery) ───────────────────────────────────── -->
<script>
(function () {
  'use strict';

  const CONTROLLER_URL = '/src/controllers/NotificationController.php';

  // ── Utility: toggle dropdown ─────────────────────────────────────────────
  function toggleDropdown(btn, panel) {
    const isOpen = panel.classList.toggle('hidden');
    btn.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
  }

  function closeAll() {
    document.getElementById('notif-dropdown')?.classList.add('hidden');
    document.getElementById('notif-btn')?.setAttribute('aria-expanded', 'false');
    document.getElementById('profile-dropdown')?.classList.add('hidden');
    document.getElementById('profile-btn')?.setAttribute('aria-expanded', 'false');
  }

  // ── Bell ─────────────────────────────────────────────────────────────────
  const notifBtn      = document.getElementById('notif-btn');
  const notifDropdown = document.getElementById('notif-dropdown');
  const notifBadge    = document.getElementById('notif-badge');

  notifBtn?.addEventListener('click', function (e) {
    e.stopPropagation();
    const profileDropdown = document.getElementById('profile-dropdown');
    profileDropdown?.classList.add('hidden');
    document.getElementById('profile-btn')?.setAttribute('aria-expanded', 'false');
    toggleDropdown(notifBtn, notifDropdown);
  });

  // ── Profile ──────────────────────────────────────────────────────────────
  const profileBtn      = document.getElementById('profile-btn');
  const profileDropdown = document.getElementById('profile-dropdown');

  profileBtn?.addEventListener('click', function (e) {
    e.stopPropagation();
    notifDropdown?.classList.add('hidden');
    notifBtn?.setAttribute('aria-expanded', 'false');
    toggleDropdown(profileBtn, profileDropdown);
  });

  // ── Tutup semua saat klik di luar ────────────────────────────────────────
  document.addEventListener('click', function (e) {
    if (
      !document.getElementById('notif-wrapper')?.contains(e.target) &&
      !document.getElementById('profile-wrapper')?.contains(e.target)
    ) {
      closeAll();
    }
  });

  // ── Helper: POST ke controller ───────────────────────────────────────────
  async function postAction(params) {
    const body = new URLSearchParams(params);
    const res  = await fetch(CONTROLLER_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    });
    return res.json();
  }

  // ── Badge helper ─────────────────────────────────────────────────────────
  function updateBadge(count) {
    if (!notifBadge) return;
    if (count > 0) {
      notifBadge.textContent = count > 99 ? '99+' : count;
      notifBadge.classList.remove('hidden');
    } else {
      notifBadge.classList.add('hidden');
    }
  }

  // ── Tandai semua dibaca ──────────────────────────────────────────────────
  document.getElementById('mark-all-read-btn')?.addEventListener('click', async function () {
    this.disabled = true;
    try {
      const data = await postAction({ action: 'mark_all_read' });
      if (data.success) {
        // Ubah visual semua item menjadi sudah-dibaca
        document.querySelectorAll('#notif-list li').forEach(li => {
          li.classList.remove('bg-indigo-50/60');
          li.classList.add('bg-white');
          li.querySelector('span.bg-indigo-500')?.classList.replace('bg-indigo-500', 'bg-transparent');
        });
        updateBadge(0);
        this.classList.add('hidden');
      }
    } catch (err) {
      console.error('Gagal menandai notifikasi:', err);
      this.disabled = false;
    }
  });

  // ── Terima / Tolak undangan ──────────────────────────────────────────────
  document.getElementById('notif-list')?.addEventListener('click', async function (e) {
    const btn = e.target.closest('.invite-action-btn');
    if (!btn) return;

    const action    = btn.dataset.action;    // 'accept_invite' | 'reject_invite'
    const projectId = btn.dataset.projectId;
    if (!action || !projectId) return;

    // Disable kedua tombol dalam baris ini
    const row  = btn.closest('[data-invite-project]');
    const btns = row?.querySelectorAll('.invite-action-btn');
    btns?.forEach(b => { b.disabled = true; });

    try {
      const data = await postAction({ action, project_id: projectId });

      if (data.success) {
        // Sembunyikan tombol dan tampilkan status
        const statusLabel = document.createElement('p');
        statusLabel.className = 'mt-2 text-xs font-medium ' +
          (action === 'accept_invite' ? 'text-green-600' : 'text-red-500');
        statusLabel.textContent = action === 'accept_invite' ? '✓ Undangan diterima' : '✗ Undangan ditolak';

        row?.replaceWith(statusLabel);

        // Kurangi badge
        const currentBadge = notifBadge?.textContent === '99+' ? 99 : parseInt(notifBadge?.textContent || '0', 10);
        updateBadge(Math.max(0, currentBadge - 1));
      } else {
        alert(data.message || 'Terjadi kesalahan. Coba lagi.');
        btns?.forEach(b => { b.disabled = false; });
      }
    } catch (err) {
      console.error('Fetch error:', err);
      alert('Gagal menghubungi server. Periksa koneksi Anda.');
      btns?.forEach(b => { b.disabled = false; });
    }
  });

})();
</script>