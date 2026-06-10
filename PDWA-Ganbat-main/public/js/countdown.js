/**
 * Ganbat – Real-time Deadline Countdown
 * File   : public/js/countdown.js
 */

(function () {
    "use strict";

    const TICK_INTERVAL_MS = 1000;

    const THRESHOLD = {
        URGENT:  24 * 60 * 60,  // 24 jam dalam detik
        WARNING: 72 * 60 * 60,  // 72 jam dalam detik
    };

    const COLOR_CLASS = {
        expired: {
            badge: "bg-gray-100 text-gray-500 border border-gray-200",
            dot:   "bg-gray-400",
            label: "text-gray-400",
        },
        urgent: {
            badge: "bg-red-50 text-red-600 border border-red-200",
            dot:   "bg-red-500 animate-pulse",
            label: "text-red-500",
        },
        warning: {
            badge: "bg-yellow-50 text-yellow-700 border border-yellow-200",
            dot:   "bg-yellow-400 animate-pulse",
            label: "text-yellow-600",
        },
        safe: {
            badge: "bg-green-50 text-green-700 border border-green-200",
            dot:   "bg-green-500",
            label: "text-green-600",
        },
    };

    // ─── Utilitas ─────────────────────────────────────────────────────────────

    function computeTimeLeft(deadlineStr) {
        // Normalisasi format MySQL "YYYY-MM-DD HH:MM:SS" agar kompatibel semua browser
        const normalized = deadlineStr.trim().replace(" ", "T");
        const deadlineMs = new Date(normalized).getTime();

        if (isNaN(deadlineMs)) {
            return { totalSeconds: 0, days: 0, hours: 0, minutes: 0, seconds: 0, expired: true };
        }

        const diffSeconds = Math.floor((deadlineMs - Date.now()) / 1000);

        if (diffSeconds <= 0) {
            return { totalSeconds: 0, days: 0, hours: 0, minutes: 0, seconds: 0, expired: true };
        }

        return {
            totalSeconds: diffSeconds,
            days:    Math.floor(diffSeconds / 86400),
            hours:   Math.floor((diffSeconds % 86400) / 3600),
            minutes: Math.floor((diffSeconds % 3600) / 60),
            seconds: diffSeconds % 60,
            expired: false,
        };
    }

    function resolveCondition(expired, totalSeconds) {
        if (expired)                          return "expired";
        if (totalSeconds < THRESHOLD.URGENT)  return "urgent";
        if (totalSeconds < THRESHOLD.WARNING) return "warning";
        return "safe";
    }

    function pad(n) {
        return String(n).padStart(2, "0");
    }

    // Hapus semua kelas warna lama, terapkan yang baru
    function applyClasses(el, newClasses, oldClasses) {
        el.classList.remove(...[...new Set(oldClasses)]);
        el.classList.add(...newClasses.split(" ").filter(Boolean));
    }

    // ─── Renderer ─────────────────────────────────────────────────────────────

    function renderCountdown(container) {
        const deadlineStr = container.dataset.deadline;
        if (!deadlineStr) return;

        const badge = container.querySelector(".countdown-badge");
        const dot   = container.querySelector(".countdown-dot");
        const label = container.querySelector(".countdown-label");
        if (!badge || !dot || !label) return;

        const { totalSeconds, days, hours, minutes, seconds, expired } = computeTimeLeft(deadlineStr);
        const condition = resolveCondition(expired, totalSeconds);
        const colors    = COLOR_CLASS[condition];

        // Kumpulkan semua kelas lama dari semua kondisi untuk di-reset
        const allBadge = Object.values(COLOR_CLASS).flatMap(c => c.badge.split(" "));
        const allDot   = Object.values(COLOR_CLASS).flatMap(c => c.dot.split(" "));
        const allLabel = Object.values(COLOR_CLASS).flatMap(c => c.label.split(" "));

        applyClasses(badge, colors.badge, allBadge);
        applyClasses(dot,   colors.dot,   allDot);
        applyClasses(label, colors.label, allLabel);

        // Format teks berdasarkan sisa waktu
        if (expired) {
            label.textContent = "Kedaluwarsa";
        } else if (days > 0) {
            label.textContent = `${days}h ${pad(hours)}j ${pad(minutes)}m`;
        } else if (hours > 0) {
            label.textContent = `${pad(hours)}j ${pad(minutes)}m ${pad(seconds)}d`;
        } else {
            // Momen kritis: tampilkan menit + detik
            label.textContent = `${pad(minutes)}m ${pad(seconds)}d`;
        }

        // Bersihkan interval jika sudah expired
        if (expired && container._countdownInterval) {
            clearInterval(container._countdownInterval);
            delete container._countdownInterval;
        }
    }

    // ─── Inisialisasi ─────────────────────────────────────────────────────────

    function initCountdown(container) {
        renderCountdown(container); // render langsung tanpa jeda 1 detik

        container._countdownInterval = setInterval(function () {
            renderCountdown(container);
        }, TICK_INTERVAL_MS);
    }

    function initAll(scope) {
        scope = scope || document;
        scope.querySelectorAll(".task-countdown[data-deadline]").forEach(function (container) {
            // Cegah inisialisasi ganda
            if (container.dataset.countdownReady === "1") return;
            container.dataset.countdownReady = "1";
            initCountdown(container);
        });
    }

    // MutationObserver: tangani kartu yang ditambah dinamis (AJAX, drag-drop)
    function observeDOM() {
        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType !== Node.ELEMENT_NODE) return;
                    if (node.matches(".task-countdown[data-deadline]")) {
                        if (node.dataset.countdownReady !== "1") {
                            node.dataset.countdownReady = "1";
                            initCountdown(node);
                        }
                    }
                    initAll(node); // cek elemen di dalam node baru
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }

    // ─── Entry Point ──────────────────────────────────────────────────────────

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function () {
            initAll();
            observeDOM();
        });
    } else {
        initAll();
        observeDOM();
    }

})();