<?php
/**
 * ============================================================
 *  GANBAT - Sistem Manajemen Tugas
 *  File   : src/utils/helpers.php
 * ============================================================
 *  Fungsi-fungsi pembantu (helper) untuk tampilan UI.
 * ============================================================
 */

/**
 * Format tanggal ke locale Indonesia singkat.
 */
function formatDeadlineDate(?string $date): string
{
    if (empty($date)) return '-';

    $bulan = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agt',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    $ts = strtotime($date);
    return date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Menghitung status tenggat waktu.
 */
function getDeadlineStatus(?string $deadline_date): array
{
    if (empty($deadline_date)) {
        return ['status' => 'no-deadline', 'diff_days' => null, 'label' => 'Tanpa deadline'];
    }

    $today    = new DateTime('today');
    $deadline = new DateTime($deadline_date);
    $diff     = $today->diff($deadline);
    $days     = (int)$diff->days;
    $invert   = (bool)$diff->invert;

    if ($invert) {
        return ['status' => 'overdue', 'diff_days' => -$days, 'label' => $days === 0 ? 'Jatuh tempo hari ini' : "Terlambat {$days} hari"];
    } elseif ($days === 0) {
        return ['status' => 'due-today', 'diff_days' => 0, 'label' => 'Jatuh tempo hari ini'];
    } elseif ($days <= 3) {
        return ['status' => 'due-soon', 'diff_days' => $days, 'label' => "Sisa {$days} hari"];
    } else {
        return ['status' => 'on-track', 'diff_days' => $days, 'label' => "Sisa {$days} hari"];
    }
}

/**
 * CSS classes untuk status deadline (dark theme).
 */
function getDeadlineClasses(string $status): array
{
    $map = [
        'overdue'     => ['text' => 'text-red-400 font-semibold', 'badge' => 'bg-red-500/10 text-red-400 border border-red-500/20', 'icon' => '🚨'],
        'due-today'   => ['text' => 'text-orange-400 font-semibold', 'badge' => 'bg-orange-500/10 text-orange-400 border border-orange-500/20', 'icon' => '⏰'],
        'due-soon'    => ['text' => 'text-yellow-400 font-medium', 'badge' => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20', 'icon' => '⚠️'],
        'on-track'    => ['text' => 'text-green-400', 'badge' => 'bg-green-500/10 text-green-400 border border-green-500/20', 'icon' => '✅'],
        'no-deadline' => ['text' => 'text-slate-500', 'badge' => 'bg-slate-500/10 text-slate-400 border border-slate-500/20', 'icon' => '📋'],
    ];
    return $map[$status] ?? $map['no-deadline'];
}

/**
 * CSS classes untuk prioritas (dark theme).
 */
function getPriorityBadge(string $priority): string
{
    $map = [
        'low'    => 'bg-primary-500/10 text-primary-400 border border-primary-500/20',
        'medium' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
        'high'   => 'bg-red-500/10 text-red-400 border border-red-500/20',
    ];
    return $map[strtolower($priority)] ?? $map['medium'];
}

/**
 * CSS classes untuk status subtask (dark theme).
 */
function getStatusBadge(string $status): string
{
    $map = [
        'todo'    => 'bg-slate-500/10 text-slate-300 border border-slate-500/20',
        'ongoing' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
        'done'    => 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20',
    ];
    return $map[strtolower($status)] ?? $map['todo'];
}

/**
 * Label untuk prioritas.
 */
function getPriorityLabel(string $priority): string
{
    $map = ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi'];
    return $map[$priority] ?? ucfirst($priority);
}

/**
 * Label untuk status.
 */
function getStatusLabel(string $status): string
{
    $map = ['todo' => 'Todo', 'ongoing' => 'Ongoing', 'done' => 'Done'];
    return $map[$status] ?? ucfirst($status);
}