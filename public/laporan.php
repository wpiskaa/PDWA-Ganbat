<?php
session_start();
require_once __DIR__ . '/../src/config/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get all activity logs for projects the user is part of
$stmt = $pdo->prepare("
    SELECT DISTINCT a.* 
    FROM activity_logs a
    JOIN projects p ON a.project_id = p.id
    LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.user_id = :uid2 AND pm.status_invite = 'accepted'
    WHERE p.owner_id = :uid1 OR pm.user_id IS NOT NULL
    ORDER BY a.created_at DESC
    LIMIT 100
");
$stmt->execute(['uid1' => $user_id, 'uid2' => $user_id]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Aktivitas - Ganbat!</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-dark-950 text-white font-sans min-h-screen">

    <!-- Background Glow Decorations -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-primary-600 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute top-1/2 -right-40 w-96 h-96 bg-emerald-500 rounded-full opacity-10 blur-3xl"></div>
    </div>

    <?php include __DIR__ . '/../src/views/components/navbar.php'; ?>

    <main class="relative px-4 py-8 md:px-8 max-w-screen-md mx-auto animate-fade-in">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-white mb-1">Laporan Aktivitas</h1>
            <p class="text-slate-400 text-sm">Log pergerakan tugas pada semua project Anda.</p>
        </div>

        <?php if (empty($logs)): ?>
            <div class="flex flex-col items-center justify-center py-20 text-center border border-dashed border-slate-700/60 rounded-2xl bg-dark-900/30">
                <div class="w-16 h-16 bg-dark-800 rounded-full flex items-center justify-center text-slate-500 mb-4 border border-slate-700">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Belum ada aktivitas</h3>
                <p class="text-slate-400 text-sm max-w-sm">Mulai bekerja pada subtask untuk melihat log laporan di sini.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($logs as $log): ?>
                    <div class="bg-dark-800/60 backdrop-blur-sm border border-slate-700/50 rounded-xl p-4 flex items-start gap-4 hover:border-primary-500/30 transition-colors">
                        <div class="mt-1 w-8 h-8 rounded-full bg-primary-500/20 text-primary-400 flex items-center justify-center flex-shrink-0">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-slate-300 text-sm leading-relaxed font-medium">
                                <?= htmlspecialchars($log['log_text']) ?>
                            </p>
                            <div class="mt-2 text-xs text-slate-500 font-medium">
                                <?= date('d M Y, H:i', strtotime($log['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <footer class="text-center text-xs text-slate-500 py-6 mt-8">
        &copy; <?= date('Y') ?> Ganbat! &mdash; All rights reserved.
    </footer>
</body>
</html>
