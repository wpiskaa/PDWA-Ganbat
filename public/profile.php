<?php

require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth_guard.php';

$user_id = (int)$_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT username, profile_picture FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}

$username = htmlspecialchars($user['username'] ?? 'User', ENT_QUOTES);
$profile_picture = htmlspecialchars($user['profile_picture'] ?? '', ENT_QUOTES);

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya — Ganbat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
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
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-dark-950 min-h-screen font-sans text-white">

    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-primary-600 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-blue-500 rounded-full opacity-10 blur-3xl"></div>
    </div>

    <?php include __DIR__ . '/../src/views/components/navbar.php'; ?>

    <main class="relative px-4 py-12 md:px-8 max-w-lg mx-auto">

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
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
            <div class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 mb-6 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <div class="flex flex-col items-center justify-center text-center pb-8 border-b border-slate-700/50 mb-8">
                <div class="relative w-28 h-28 mb-4">
                    <?php if ($profile_picture): ?>
                        <img src="<?= $profile_picture ?>" alt="<?= $username ?>"
                             class="w-full h-full rounded-full object-cover ring-4 ring-primary-500/30">
                    <?php else: ?>
                        <div class="w-full h-full rounded-full bg-primary-600/20 border-2 border-dashed border-primary-500/50 flex items-center justify-center text-primary-400 font-extrabold text-3xl">
                            <?= mb_strtoupper(mb_substr($username, 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h3 class="text-xl font-semibold text-white"><?= $username ?></h3>
                <p class="text-xs text-slate-400 mt-1">Anggota Tim Ganbat</p>
            </div>

            <form action="../src/controllers/ProfileController.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_profile_picture">

                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Pilih Foto Profil Baru</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="profile_picture" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-600 border-dashed rounded-xl cursor-pointer bg-dark-900/50 hover:bg-dark-900 hover:border-primary-500 transition-colors duration-200">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="mb-1 text-sm text-slate-300 font-medium">Klik untuk memilih file</p>
                                <p class="text-xs text-slate-500">PNG, JPG, JPEG, GIF (Maks. 2MB)</p>
                            </div>
                            <input id="profile_picture" name="profile_picture" type="file" accept="image/*" class="hidden" required onchange="displayFileName(this)">
                        </label>
                    </div>
                    <p id="file-name-display" class="text-xs text-primary-400 mt-2 text-center font-medium"></p>
                </div>

                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-500 active:bg-primary-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-primary-600/30 text-sm">
                    Simpan Foto Profil
                </button>
            </form>
        </div>
    </main>

    <script>
        function displayFileName(input) {
            const display = document.getElementById('file-name-display');
            if (input.files && input.files[0]) {
                display.textContent = 'File terpilih: ' + input.files[0].name;
            } else {
                display.textContent = '';
            }
        }
    </script>
</body>
</html>
