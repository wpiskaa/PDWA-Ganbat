<?php
/**
 * ============================================================
 *  GANBAT - Sistem Manajemen Tugas
 *  File   : public/profile.php
 * ============================================================
 */

require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth_guard.php';

$user_id = (int) $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT username, profile_picture, created_at FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}

$username        = htmlspecialchars($user['username'] ?? 'User', ENT_QUOTES);
$profile_picture = $user['profile_picture'] ?? '';
$created_at      = $user['created_at'] ?? '';

$error   = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya — Ganbat</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-dark-950 min-h-screen font-sans text-white">

    <!-- Background Glow -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-primary-600 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-blue-500 rounded-full opacity-10 blur-3xl"></div>
    </div>

    <?php include __DIR__ . '/../src/views/components/navbar.php'; ?>

    <main class="relative px-4 py-12 md:px-8 max-w-lg mx-auto">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="index.php" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        <div class="bg-dark-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8 shadow-2xl">
            <h2 class="text-2xl font-bold text-white mb-6 tracking-tight">Profil Saya</h2>

            <?php if (!empty($error)): ?>
            <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 mb-6 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
            <div class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 mb-6 text-sm">
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <!-- Avatar Section -->
            <div class="flex flex-col items-center justify-center text-center pb-8 border-b border-slate-700/50 mb-8">
                <div class="relative w-28 h-28 mb-4">
                    <?php if (!empty($profile_picture)): ?>
                        <img src="<?= htmlspecialchars($profile_picture) ?>" alt="<?= $username ?>"
                             class="w-full h-full rounded-full object-cover ring-4 ring-primary-500/30">
                    <?php else: ?>
                        <div class="w-full h-full rounded-full bg-primary-600/20 border-2 border-dashed border-primary-500/50 flex items-center justify-center text-primary-400 font-extrabold text-3xl">
                            <?= mb_strtoupper(mb_substr($username, 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h3 class="text-xl font-semibold text-white"><?= $username ?></h3>
                <?php if ($created_at): ?>
                <p class="text-xs text-slate-400 mt-1">Bergabung sejak <?= date('d M Y', strtotime($created_at)) ?></p>
                <?php endif; ?>
            </div>

            <!-- Upload Avatar Form -->
            <form action="../src/controllers/ProfileController.php" method="POST" enctype="multipart/form-data" class="mb-8 pb-8 border-b border-slate-700/50">
                <input type="hidden" name="action" value="upload_profile_picture">
                <h3 class="text-lg font-semibold mb-4">Ubah Foto Profil</h3>
                <div class="mb-4">
                    <label for="profile_picture"
                           class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-600 border-dashed rounded-xl cursor-pointer bg-dark-900/50 hover:bg-dark-900 hover:border-primary-500 transition-colors duration-200">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-xs text-slate-400"><span class="font-semibold text-primary-400">Klik untuk memilih</span> atau drag file</p>
                            <p class="text-xs text-slate-500 mt-1">JPG, PNG, GIF, WebP (maks 2MB)</p>
                        </div>
                        <input id="profile_picture" name="profile_picture" type="file" accept="image/*" class="hidden" required>
                    </label>
                    <p id="file-name" class="text-xs text-slate-400 mt-2"></p>
                </div>
                <button type="submit"
                        class="w-full bg-primary-600 hover:bg-primary-500 text-white font-semibold py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-primary-600/30">
                    Upload Foto
                </button>
            </form>

            <!-- Change Password Form -->
            <form action="../src/controllers/ProfileController.php" method="POST">
                <input type="hidden" name="action" value="update_password">
                <h3 class="text-lg font-semibold mb-4">Ubah Password</h3>
                <div class="mb-4">
                    <label class="block text-sm text-slate-300 mb-1.5">Password Lama</label>
                    <input type="password" name="old_password" required
                           class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all">
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-slate-300 mb-1.5">Password Baru</label>
                    <input type="password" name="new_password" required minlength="6"
                           class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all">
                </div>
                <div class="mb-6">
                    <label class="block text-sm text-slate-300 mb-1.5">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" required
                           class="w-full bg-dark-900 border border-slate-600 rounded-xl px-3.5 py-2.5 text-sm text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all">
                </div>
                <button type="submit"
                        class="w-full bg-slate-700 hover:bg-slate-600 text-white font-semibold py-2.5 rounded-xl text-sm transition-all">
                    Perbarui Password
                </button>
            </form>
        </div>
    </main>

    <script>
    document.getElementById('profile_picture').addEventListener('change', function(e) {
        var name = e.target.files[0] ? e.target.files[0].name : '';
        document.getElementById('file-name').textContent = name ? 'File: ' + name : '';
    });
    </script>
</body>
</html>
