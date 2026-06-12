<?php
// public/register.php
// Ditulis oleh: Hafiz Kurniawan
// Deskripsi: Antarmuka form registrasi akun baru dengan desain modern menggunakan Tailwind CSS CDN.
//             Menangani pesan error dari session dan submit ke AuthController.

// Mulai session untuk membaca pesan error
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika pengguna sudah login, langsung arahkan ke dashboard
if (!empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

// Ambil & hapus pesan error sementara dari session (flash message)
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru — Ganbat</title>
    <meta name="description" content="Buat akun Ganbat baru dan mulai kelola tugas tim Anda dengan lebih efisien.">
    <!-- Tailwind CSS (Local via style.css) -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-dark-950 min-h-screen font-sans flex items-center justify-center p-4">

    <!-- Background Glow Decoration -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-primary-600 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-blue-500 rounded-full opacity-10 blur-3xl"></div>
    </div>

    <!-- Register Card -->
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

            <h2 class="text-xl font-semibold text-white mb-6">Buat Akun Baru</h2>

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

            <!-- Form Register -->
            <form id="form-register" action="../src/controllers/AuthController.php" method="POST" novalidate>
                <input type="hidden" name="action" value="register">

                <!-- Field: Username -->
                <div class="mb-4">
                    <label for="register-username" class="block text-sm font-medium text-slate-300 mb-2">
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
                            id="register-username"
                            name="username"
                            autocomplete="username"
                            placeholder="Pilih username unik Anda"
                            minlength="3"
                            maxlength="50"
                            required
                            class="w-full bg-dark-900 border border-slate-600 text-white placeholder-slate-500
                                   rounded-xl pl-10 pr-4 py-3 text-sm
                                   focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500
                                   transition-colors duration-200"
                        >
                    </div>
                    <p class="mt-1.5 text-xs text-slate-500">Minimal 3 karakter, maksimal 50 karakter.</p>
                </div>

                <!-- Field: Password -->
                <div class="mb-4">
                    <label for="register-password" class="block text-sm font-medium text-slate-300 mb-2">
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
                            id="register-password"
                            name="password"
                            autocomplete="new-password"
                            placeholder="Buat password yang kuat"
                            minlength="6"
                            required
                            oninput="checkPasswordStrength(this.value)"
                            class="w-full bg-dark-900 border border-slate-600 text-white placeholder-slate-500
                                   rounded-xl pl-10 pr-12 py-3 text-sm
                                   focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500
                                   transition-colors duration-200"
                        >
                        <!-- Toggle Show/Hide Password -->
                        <button
                            type="button"
                            id="btn-toggle-reg-password"
                            onclick="togglePassword('register-password', 'eye-icon-reg-pass')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300 transition-colors"
                            aria-label="Toggle tampilkan password"
                        >
                            <svg id="eye-icon-reg-pass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                                         -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Password Strength Indicator -->
                    <div class="mt-2">
                        <div class="flex gap-1 h-1">
                            <div id="strength-bar-1" class="flex-1 rounded-full bg-slate-700 transition-all duration-300"></div>
                            <div id="strength-bar-2" class="flex-1 rounded-full bg-slate-700 transition-all duration-300"></div>
                            <div id="strength-bar-3" class="flex-1 rounded-full bg-slate-700 transition-all duration-300"></div>
                            <div id="strength-bar-4" class="flex-1 rounded-full bg-slate-700 transition-all duration-300"></div>
                        </div>
                        <p id="strength-label" class="text-xs text-slate-500 mt-1"></p>
                    </div>
                </div>

                <!-- Field: Konfirmasi Password -->
                <div class="mb-6">
                    <label for="register-confirm" class="block text-sm font-medium text-slate-300 mb-2">
                        Konfirmasi Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944
                                         a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622
                                         5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <input
                            type="password"
                            id="register-confirm"
                            name="confirm_password"
                            autocomplete="new-password"
                            placeholder="Ulangi password Anda"
                            required
                            oninput="checkPasswordMatch()"
                            class="w-full bg-dark-900 border border-slate-600 text-white placeholder-slate-500
                                   rounded-xl pl-10 pr-12 py-3 text-sm
                                   focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500
                                   transition-colors duration-200"
                        >
                        <!-- Toggle Show/Hide Confirm Password -->
                        <button
                            type="button"
                            id="btn-toggle-reg-confirm"
                            onclick="togglePassword('register-confirm', 'eye-icon-reg-confirm')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300 transition-colors"
                            aria-label="Toggle tampilkan konfirmasi password"
                        >
                            <svg id="eye-icon-reg-confirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                                         -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <!-- Pesan match/tidak match real-time -->
                    <p id="match-msg" class="text-xs mt-1.5"></p>
                </div>

                <!-- Tombol Register -->
                <button
                    type="submit"
                    id="btn-register-submit"
                    class="w-full bg-primary-600 hover:bg-primary-500 active:bg-primary-700
                           text-white font-semibold py-3 px-4 rounded-xl
                           transition-all duration-200 transform hover:scale-[1.01] active:scale-[0.99]
                           shadow-lg shadow-primary-600/30 text-sm"
                >
                    Buat Akun
                </button>
            </form>

            <!-- Link ke Login -->
            <p class="text-center text-sm text-slate-400 mt-6">
                Sudah punya akun?
                <a href="login.php"
                   class="text-primary-400 hover:text-primary-300 font-medium transition-colors duration-200 underline underline-offset-2">
                    Masuk di sini
                </a>
            </p>

        </div><!-- /Card Container -->

        <!-- Footer note -->
        <p class="text-center text-xs text-slate-600 mt-6">
            &copy; <?= date('Y') ?> Ganbat — Sistem Manajemen Tugas
        </p>

    </div><!-- /Register Card -->

    <script>
        /**
         * Toggle visibilitas password pada input field.
         */
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
                             a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242
                             M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5
                             c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                `;
            } else {
                input.type = 'password';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                             -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }

        /**
         * Menampilkan indikator kekuatan password secara real-time.
         */
        function checkPasswordStrength(value) {
            const bars   = [1, 2, 3, 4].map(n => document.getElementById(`strength-bar-${n}`));
            const label  = document.getElementById('strength-label');
            const colors = ['bg-slate-700', 'bg-slate-700', 'bg-slate-700', 'bg-slate-700'];

            let strength = 0;
            if (value.length >= 6)  strength++;
            if (value.length >= 10) strength++;
            if (/[A-Z]/.test(value) && /[a-z]/.test(value)) strength++;
            if (/[0-9!@#$%^&*]/.test(value)) strength++;

            const levelColors = ['bg-red-500', 'bg-orange-400', 'bg-yellow-400', 'bg-green-500'];
            const levelLabels = ['', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'];

            bars.forEach((bar, idx) => {
                bar.className = `flex-1 rounded-full transition-all duration-300 ${idx < strength ? levelColors[strength - 1] : 'bg-slate-700'}`;
            });

            label.textContent = value.length > 0 ? levelLabels[strength] : '';
            label.className   = `text-xs mt-1 ${strength <= 1 ? 'text-red-400' : strength === 2 ? 'text-orange-400' : strength === 3 ? 'text-yellow-400' : 'text-green-400'}`;
        }

        /**
         * Memeriksa kecocokan password dan konfirmasi secara real-time.
         */
        function checkPasswordMatch() {
            const pass    = document.getElementById('register-password').value;
            const confirm = document.getElementById('register-confirm').value;
            const msg     = document.getElementById('match-msg');

            if (confirm.length === 0) {
                msg.textContent = '';
                return;
            }

            if (pass === confirm) {
                msg.textContent = '✓ Password cocok';
                msg.className   = 'text-xs mt-1.5 text-green-400';
            } else {
                msg.textContent = '✗ Password tidak cocok';
                msg.className   = 'text-xs mt-1.5 text-red-400';
            }
        }
    </script>
</body>
</html>
