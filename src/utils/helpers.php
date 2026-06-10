<?php

/**
 * Menghitung status tenggat waktu sebuah tugas.
 * Mengembalikan array berisi status string dan data pendukung lainnya.
 *
 * @param string|null $deadline_date Format 'YYYY-MM-DD'
 * @return array ['status' => string, 'diff_days' => int, 'label' => string]
 */
function getDeadlineStatus(?string $deadline_date): array
{
    // Jika tidak ada tenggat waktu, kembalikan status netral
    if (empty($deadline_date)) {
        return [
            'status'    => 'no-deadline',
            'diff_days' => null,
            'label'     => 'Tidak ada tenggat',
        ];
    }

    // Gunakan tengah malam hari ini agar perbandingan berdasarkan hari, bukan jam
    $today    = new DateTime('today');
    $deadline = new DateTime($deadline_date);
    $diff     = $today->diff($deadline); // DateInterval

    // diff->days = selisih absolut (hari), diff->invert: 0 = masa depan, 1 = masa lalu
    $days   = (int) $diff->days;
    $invert = (bool) $diff->invert; // true = deadline sudah lewat

    if ($invert) {
        // Tenggat waktu sudah terlewat
        return [
            'status'    => 'overdue',
            'diff_days' => -$days,
            'label'     => $days === 0 ? 'Jatuh tempo hari ini' : "Terlambat {$days} hari",
        ];
    } elseif ($days === 0) {
        // Tenggat waktu tepat hari ini
        return [
            'status'    => 'due-today',
            'diff_days' => 0,
            'label'     => 'Jatuh tempo hari ini',
        ];
    } elseif ($days <= 3) {
        // Tenggat waktu dalam 1–3 hari ke depan (warning)
        return [
            'status'    => 'due-soon',
            'diff_days' => $days,
            'label'     => "Sisa {$days} hari",
        ];
    } else {
        // Tenggat waktu masih jauh
        return [
            'status'    => 'on-track',
            'diff_days' => $days,
            'label'     => "Sisa {$days} hari",
        ];
    }
}

/**
 * Memetakan status tenggat waktu ke Tailwind CSS utility classes.
 * Mengembalikan classes untuk: teks label, badge background, dan ikon.
 *
 * @param string $status Nilai dari getDeadlineStatus()['status']
 * @return array ['text' => string, 'badge' => string, 'border' => string, 'icon' => string]
 */
function getDeadlineClasses(string $status): array
{
    $map = [
        'overdue'     => [
            'text'   => 'text-red-600 font-semibold',
            'badge'  => 'bg-red-100 text-red-700 border border-red-300',
            'border' => 'border-l-4 border-red-500',
            'icon'   => '🚨',
        ],
        'due-today'   => [
            'text'   => 'text-orange-600 font-semibold',
            'badge'  => 'bg-orange-100 text-orange-700 border border-orange-300',
            'border' => 'border-l-4 border-orange-500',
            'icon'   => '⏰',
        ],
        'due-soon'    => [
            'text'   => 'text-yellow-600 font-medium',
            'badge'  => 'bg-yellow-100 text-yellow-700 border border-yellow-300',
            'border' => 'border-l-4 border-yellow-400',
            'icon'   => '⚠️',
        ],
        'on-track'    => [
            'text'   => 'text-green-600',
            'badge'  => 'bg-green-100 text-green-700 border border-green-200',
            'border' => 'border-l-4 border-green-400',
            'icon'   => '✅',
        ],
        'no-deadline' => [
            'text'   => 'text-gray-400',
            'badge'  => 'bg-gray-100 text-gray-500 border border-gray-200',
            'border' => 'border-l-4 border-gray-300',
            'icon'   => '📋',
        ],
    ];

    // Fallback ke 'no-deadline' jika status tidak dikenal
    return $map[$status] ?? $map['no-deadline'];
}

/**
 * Memformat tanggal dari format 'YYYY-MM-DD' ke format lokal Indonesia.
 * Contoh: '2025-07-04' → '4 Juli 2025'
 *
 * @param string|null $date
 * @return string
 */
function formatDeadlineDate(?string $date): string
{
    if (empty($date)) {
        return '-';
    }

    $bulan = [
        1  => 'Januari', 2  => 'Februari', 3  => 'Maret',
        4  => 'April',   5  => 'Mei',       6  => 'Juni',
        7  => 'Juli',    8  => 'Agustus',   9  => 'September',
        10 => 'Oktober', 11 => 'November',  12 => 'Desember',
    ];

    $dt = new DateTime($date);
    return $dt->format('j') . ' ' . $bulan[(int)$dt->format('n')] . ' ' . $dt->format('Y');
}

/**
 * Memetakan nilai prioritas task ke Tailwind classes dan label teks.
 *
 * @param string $priority 'low' | 'medium' | 'high'
 * @return array ['badge' => string, 'label' => string]
 */
function getPriorityClasses(string $priority): array
{
    $map = [
        'high'   => ['badge' => 'bg-red-100 text-red-700 border border-red-200',    'label' => 'Tinggi'],
        'medium' => ['badge' => 'bg-yellow-100 text-yellow-700 border border-yellow-200', 'label' => 'Sedang'],
        'low'    => ['badge' => 'bg-blue-100 text-blue-700 border border-blue-200',  'label' => 'Rendah'],
    ];

    return $map[$priority] ?? ['badge' => 'bg-gray-100 text-gray-600', 'label' => ucfirst($priority)];
}

/**
 * Memetakan nilai status task ke Tailwind classes dan label teks.
 *
 * @param string $status 'todo' | 'doing' | 'done'
 * @return array ['badge' => string, 'label' => string]
 */
function getStatusClasses(string $status): array
{
    $map = [
        'todo'  => ['badge' => 'bg-gray-100 text-gray-600 border border-gray-200',    'label' => 'Belum Dimulai'],
        'doing' => ['badge' => 'bg-blue-100 text-blue-700 border border-blue-200',    'label' => 'Sedang Dikerjakan'],
        'done'  => ['badge' => 'bg-green-100 text-green-700 border border-green-200', 'label' => 'Selesai'],
    ];

    return $map[$status] ?? ['badge' => 'bg-gray-100 text-gray-600', 'label' => ucfirst($status)];
}