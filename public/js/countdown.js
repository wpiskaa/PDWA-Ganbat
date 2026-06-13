/**
 * Ganbat V2 Task Management System
 * Feature: Realtime Subtask Countdown
 * Author: Rafie Rasydan Wahyudi
 */

document.addEventListener("DOMContentLoaded", function () {
    function updateCountdowns() {
        // Ambil semua elemen subtask yang memiliki atribut data-deadline
        const countdownElements = document.querySelectorAll("[data-deadline]");

        countdownElements.forEach(element => {
            const deadlineStr = element.getAttribute("data-deadline");
            if (!deadlineStr || deadlineStr.trim() === "") return;

            // Pastikan format string bisa diparsing oleh Date JS
            const deadlineTime = new Date(deadlineStr).getTime();
            const now = new Date().getTime();
            const difference = deadlineTime - now;

            // Cari elemen tempat teks akan dirender
            const displayElement = element.querySelector(".countdown-text");
            if (!displayElement) return;

            // Jika Overdue (Waktu Habis)
            if (difference <= 0) {
                displayElement.innerHTML = "⚠️ Overdue";
                displayElement.className = "countdown-text text-red-600 font-bold text-xs bg-red-50 px-2 py-1 rounded border border-red-200 inline-block mt-2";
                return;
            }

            // Kalkulasi Waktu
            const days = Math.floor(difference / (1000 * 60 * 60 * 24));
            const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((difference % (1000 * 60)) / 1000);

            // Format Tampilan
            let countdownString = "";
            if (days > 0) countdownString += `${days}h `;
            if (hours > 0 || days > 0) countdownString += `${hours}j `;
            countdownString += `${minutes}m ${seconds}s`;

            displayElement.innerHTML = `⏳ Sisa: ${countdownString}`;

            // Peringatan jika sisa waktu kurang dari 24 Jam
            if (days === 0 && hours < 24) {
                displayElement.className = "countdown-text text-amber-600 font-medium text-xs bg-amber-50 px-2 py-1 rounded border border-amber-200 inline-block mt-2";
            } else {
                displayElement.className = "countdown-text text-gray-500 text-xs bg-gray-50 px-2 py-1 rounded border border-gray-200 inline-block mt-2";
            }
        });
    }

    // Jalankan langsung dan atur interval tiap 1 detik
    updateCountdowns();
    setInterval(updateCountdowns, 1000);
});