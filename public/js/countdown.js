/**
 * ============================================================
 *  GANBAT - Sistem Manajemen Tugas
 *  File   : public/js/countdown.js
 * ============================================================
 *  Countdown timer real-time untuk deadline subtask.
 *  Cari elemen dengan atribut data-deadline, lalu update
 *  child element .countdown-text setiap detik.
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", function () {

    function formatCountdown(ms) {
        if (ms <= 0) return '⚠️ Overdue';

        var totalSeconds = Math.floor(ms / 1000);
        var days    = Math.floor(totalSeconds / 86400);
        var hours   = Math.floor((totalSeconds % 86400) / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;

        var parts = [];
        if (days > 0)    parts.push(days + 'h');
        if (hours > 0)   parts.push(hours + 'j');
        if (minutes > 0) parts.push(minutes + 'm');
        parts.push(seconds + 'd');

        return '⏳ ' + parts.join(' ');
    }

    function updateAllCountdowns() {
        var elements = document.querySelectorAll('[data-deadline]');

        elements.forEach(function (el) {
            var deadline = el.getAttribute('data-deadline');
            if (!deadline || deadline.trim() === '') return;

            var displayEl = el.querySelector('.countdown-text');
            if (!displayEl) return;

            var deadlineStr = deadline.trim();
            if (deadlineStr.indexOf(' ') !== -1) {
                deadlineStr = deadlineStr.replace(' ', 'T');
            } else if (deadlineStr.indexOf('T') === -1 && deadlineStr.length === 10) {
                deadlineStr += 'T23:59:59';
            }
            var deadlineDate = new Date(deadlineStr);
            var now = new Date();
            var diff = deadlineDate - now;

            displayEl.textContent = formatCountdown(diff);

            // Remove old color classes
            displayEl.classList.remove('text-red-400', 'text-amber-400', 'text-green-400', 'text-slate-400');

            if (diff <= 0) {
                displayEl.classList.add('text-red-400');
            } else if (diff <= (5 * 3600000)) { // <= 5 jam
                displayEl.classList.add('text-red-400');
            } else if (diff <= (12 * 3600000)) { // <= 12 jam
                displayEl.classList.add('text-amber-400');
            } else { // > 12 jam (hitungan hari)
                displayEl.classList.add('text-green-400');
            }
        });
    }

    updateAllCountdowns();
    setInterval(updateAllCountdowns, 1000);
});