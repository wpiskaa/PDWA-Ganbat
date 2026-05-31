<?php
// src/views/components/navbar.php
// Deskripsi: Komponen navbar sticky untuk semua halaman dalam aplikasi Ganbat.
//             Menampilkan nama aplikasi, navigasi utama, notifikasi, dan info user.
?>
<nav class="sticky top-0 z-50 bg-dark-900/80 backdrop-blur-xl border-b border-slate-700/50">
    <div class="max-w-screen-xl mx-auto px-4 md:px-8 h-16 flex items-center justify-between gap-4">

        <!-- Brand -->
        <div class="flex items-center gap-3 flex-shrink-0">
            <div class="inline-flex items-center justify-center w-9 h-9 bg-primary-600 rounded-xl shadow-lg shadow-primary-600/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <span class="text-lg font-bold text-white tracking-tight">Ganbat</span>
        </div>

        <!-- Nav Links (desktop) -->
        <div class="hidden md:flex items-center gap-1">
            <a href="index.php"
               class="px-3 py-1.5 rounded-lg text-sm font-medium text-white bg-slate-700/60 transition-colors duration-200">
                Board
            </a>
            <a href="#"
               class="px-3 py-1.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-700/40 transition-colors duration-200">
                Anggota
            </a>
            <a href="#"
               class="px-3 py-1.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-700/40 transition-colors duration-200">
                Laporan
            </a>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-2 flex-shrink-0">

            <!-- Notification Bell -->
            <button class="relative p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/40 transition-colors duration-200"
                    aria-label="Notifikasi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                </svg>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-primary-500 rounded-full border-2 border-dark-900"></span>
            </button>

            <!-- User Avatar + Name -->
            <div class="flex items-center gap-2 pl-2 border-l border-slate-700/60">
                <div class="w-8 h-8 rounded-xl bg-primary-600 shadow-lg shadow-primary-600/30
                            flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                    <?= strtoupper(substr($current_user ?? 'U', 0, 1)) ?>
                </div>
                <span class="hidden sm:block text-sm font-medium text-slate-300 max-w-[100px] truncate">
                    <?= htmlspecialchars($current_user ?? 'User') ?>
                </span>
            </div>

            <!-- Logout -->
            <a href="../src/controllers/AuthController.php?action=logout"
               class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-medium
                      text-slate-400 hover:text-red-400 hover:bg-red-500/10 border border-transparent
                      hover:border-red-500/20 transition-all duration-200"
               title="Keluar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Keluar
            </a>

        </div>
    </div>
</nav>