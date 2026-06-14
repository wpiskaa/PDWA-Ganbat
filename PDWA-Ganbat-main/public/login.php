<?php
// public/login.php
// Ditulis oleh: Hafiz Kurniawan
// Deskripsi: Antarmuka form login dengan desain modern menggunakan Tailwind CSS CDN.
//             Menangani pesan error/sukses dari session dan submit ke AuthController.

// Mulai session untuk membaca pesan error/sukses
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika pengguna sudah login, langsung arahkan ke dashboard
if (!empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

// Ambil & hapus pesan sementara dari session (flash message)
$error   = $_SESSION['error']   ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Ganbat</title>
    <meta name="description" content="Masuk ke akun Ganbat Anda untuk mengelola tugas dan proyek tim secara efisien.">
    <!-- Tailwind CSS via CDN -->
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
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.4s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%':   { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%':   { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-dark-950 min-h-screen font-sans flex items-center justify-center p-4">

    <!-- Background Glow Decoration -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-primary-600 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-blue-500 rounded-full opacity-10 blur-3xl"></div>
    </div>

    <!-- Login Card -->
    <div class="relative w-full max-w-md animate-slide-up">

        <!-- Logo / Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-600 rounded-2xl mb-4 shadow-lg shadow-primary-600/30">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white tracking-tight">Ganbat</h1>
            <p class="text-slate-400 mt-1 text-sm">Sistem Manajemen Tugas Tim</p>
        </div>

        <!-- Card Container -->
        <div class="bg-dark-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8 shadow-2xl">

            <h2 class="text-xl font-semibold text-white mb-6">Masuk ke Akun Anda</h2>

            <!-- Flash Message: Error -->
            <?php if (!empty($error)): ?>
            <div id="alert-error" class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 mb-5 text-sm animate-fade-in">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                          clip-rule="evenodd"/>
                </svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Flash Message: Success -->
            <?php if (!empty($success)): ?>
            <div id="alert-success" class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 mb-5 text-sm animate-fade-in">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                          clip-rule="evenodd"/>
                </svg>
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <!-- Form Login -->
            <form id="form-login" action="../src/controllers/AuthController.php" method="POST" novalidate>
                <input type="hidden" name="action" value="login">

                <!-- Field: Username -->
                <div class="mb-4">
                    <label for="login-username" class="block text-sm font-medium text-slate-300 mb-2">
                        Username
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="login-username"
                            name="username"
                            autocomplete="username"
                            placeholder="Masukkan username Anda"
                            required
                            class="w-full bg-dark-900 border border-slate-600 text-white placeholder-slate-500
                                   rounded-xl pl-10 pr-4 py-3 text-sm
                                   focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500
                                   transition-colors duration-200"
                        >
                    </div>
                </div>

                <!-- Field: Password -->
                <div class="mb-6">
                    <label for="login-password" class="block text-sm font-medium text-slate-300 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input
                            type="password"
                            id="login-password"
                            name="password"
                            autocomplete="current-password"
                            placeholder="Masukkan password Anda"
                            required
                            class="w-full bg-dark-900 border border-slate-600 text-white placeholder-slate-500
                                   rounded-xl pl-10 pr-12 py-3 text-sm
                                   focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500
                                   transition-colors duration-200"
                        >
                        <!-- Toggle Show/Hide Password -->
                        <button
                            type="button"
                            id="btn-toggle-password"
                            onclick="togglePassword('login-password', 'eye-icon-login')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300 transition-colors"
                            aria-label="Toggle tampilkan password"
                        >
                            <svg id="eye-icon-login" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                                         -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Tombol Login -->
                <button
                    type="submit"
                    id="btn-login-submit"
                    class="w-full bg-primary-600 hover:bg-primary-500 active:bg-primary-700
                           text-white font-semibold py-3 px-4 rounded-xl
                           transition-all duration-200 transform hover:scale-[1.01] active:scale-[0.99]
                           shadow-lg shadow-primary-600/30 text-sm"
                >
                    Masuk
                </button>
            </form>

            <!-- Link ke Register -->
            <p class="text-center text-sm text-slate-400 mt-6">
                Belum punya akun?
                <a href="register.php"
                   class="text-primary-400 hover:text-primary-300 font-medium transition-colors duration-200 underline underline-offset-2">
                    Daftar sekarang
                </a>
            </p>

        </div><!-- /Card Container -->

        <!-- Footer note -->
        <p class="text-center text-xs text-slate-600 mt-6">
            &copy; <?= date('Y') ?> Ganbat — Sistem Manajemen Tugas
        </p>

    </div><!-- /Login Card -->

    <script>
        /**
         * Toggle visibilitas password pada input field.
         * @param {string} inputId - ID dari elemen <input> password
         * @param {string} iconId  - ID dari elemen SVG ikon mata
         */
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                // Ganti ikon ke "mata tertutup"
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
                             a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242
                             M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5
                             c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                `;
            } else {
                input.type = 'password';
                // Kembali ke ikon "mata terbuka"
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                             -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }
    </script>
</body>
</html>
