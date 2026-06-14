# Ganbat! - Sistem Manajemen Tugas & Proyek Tim

Ganbat! (diambil dari kata *Ganbatte* yang berarti semangat) adalah sebuah aplikasi manajemen tugas dan proyek berbasis web yang dirancang khusus untuk mempermudah kolaborasi tim, pembagian tugas, dan pelacakan tenggat waktu (deadline).

Aplikasi ini sangat cocok digunakan untuk kerja kelompok, manajemen proyek berskala kecil hingga menengah, dan pelacakan produktivitas pribadi.

---

## 🎯 Fitur Utama (Sudah Diimplementasikan)

### 1. Manajemen Akun & Profil
- **Autentikasi Aman:** Sistem Login dan Registrasi menggunakan *password hashing*.
- **Kustomisasi Profil:** Pengguna dapat mengganti foto profil (*avatar*) mereka sendiri.

### 2. Manajemen Proyek (Project)
- **Buat Proyek:** Pengguna dapat membuat proyek baru yang dilengkapi dengan deskripsi dan **Deadline Global** (hingga penentuan jam).
- **Sistem *Invite* Anggota:** Ketua proyek dapat mengundang pengguna lain ke dalam proyek menggunakan *username*.
- **Manajemen Tim:** Ketua proyek memiliki hak (privilege) untuk mengeluarkan (kick) anggota dari proyek.
- **Arsip Pribadi (Private Archive):** Setiap pengguna (baik ketua maupun anggota) dapat mengarsipkan proyek ke halaman terpisah tanpa memengaruhi tampilan pengguna lainnya.

### 3. Manajemen Subtask (Sistem Kanban)
Pembagian kerja dilakukan menggunakan sistem papan *Kanban* dengan 3 status: **To-Do**, **Ongoing**, dan **Done**.
- **Delegasi Tugas:** Subtask dapat ditugaskan (di-*assign*) kepada anggota tim spesifik.
- **Tingkat Prioritas:** Penanda visual prioritas (*Low* 🟢, *Medium* 🟡, *High* 🔴).
- **Deadline Real-time:** Tenggat waktu menggunakan *countdown timer* interaktif:
  - 🟢 **Hijau:** Waktu tersisa lebih dari 12 jam.
  - 🟡 **Kuning:** Waktu tersisa 12 jam atau kurang.
  - 🔴 **Merah:** Waktu tersisa 5 jam atau kurang (atau Overdue).

### 4. Pelacakan & Laporan
- **My Task:** Halaman khusus ("Meja Kerja") yang merangkum seluruh subtask yang di-*assign* kepada pengguna dari berbagai proyek berbeda.
- **Activity Log (Laporan):** Riwayat aktivitas yang otomatis mencatat pergerakan subtask (contoh: *[JUDUL PROJECT] - [User] sedang mengerjakan [Nama Subtask]*).
- **Sistem Notifikasi:** Pemberitahuan otomatis (ikon lonceng) saat ada undangan proyek baru.

---

## 🚧 Fitur yang Belum Diimplementasikan (Future Works)

Untuk presentasi, Anda dapat menyebutkan beberapa fitur berikut sebagai "Rencana Pengembangan Masa Depan" (Future Works):
1. **Chat & Komentar Subtask:** Saat ini diskusi masih harus dilakukan di luar aplikasi (misalnya WhatsApp). Rencananya setiap subtask akan memiliki kolom komentar.
2. **Lampiran File (Attachments):** Belum bisa mengunggah file (PDF, gambar, dokumen) langsung ke dalam subtask.
3. **Grafik Analitik (Charts):** Halaman Laporan saat ini berupa *Activity Log* teks. Ke depannya dapat ditambahkan grafik *Burndown Chart* atau statistik produktivitas anggota.
4. **Real-time WebSockets:** Notifikasi dan pergerakan *Kanban Board* saat ini memerlukan *refresh* halaman (kecuali timer *countdown* yang sudah real-time).
5. **Email Integration:** Pemberitahuan via email belum tersedia.

---

## 🏗 Struktur Proyek (Arsitektur)

Aplikasi ini dibangun menggunakan arsitektur **MVC (Model-View-Controller)** sederhana (Procedural PHP) untuk menjaga kebersihan kode:

```text
Ganbat-project/
├── database/
│   └── schema.sql          # File struktur database (6 tabel utama)
├── public/                 # Root folder yang diakses oleh browser
│   ├── assets/uploads/     # Direktori foto profil
│   ├── css/style.css       # Hasil compile Tailwind CSS
│   ├── js/                 # Script Vanilla JS (contoh: countdown.js, main.js)
│   ├── index.php           # Redirector otomatis ke my_project.php
│   ├── login.php & register.php
│   ├── my_project.php      # Dashboard Utama (Daftar Proyek)
│   ├── my_task.php         # Daftar tugas pribadi
│   ├── arsip_project.php   # Daftar proyek yang diarsipkan
│   ├── project_detail.php  # Tampilan dalam proyek (Kanban Board)
│   └── laporan.php         # Riwayat aktivitas log
├── src/
│   ├── config/
│   │   ├── database.php    # Koneksi PDO MySQL
│   │   └── auth_guard.php  # Proteksi sesi pengguna
│   ├── controllers/        # Logika bisnis (Routing aksi form)
│   │   ├── AuthController.php
│   │   ├── ProjectController.php
│   │   ├── TaskController.php
│   │   ├── InviteController.php
│   │   ├── LeaderController.php
│   │   └── ProfileController.php
│   └── views/
│       └── components/
│           └── navbar.php  # Komponen Navigasi Global
├── package.json            # Konfigurasi Node.js (untuk instalasi Tailwind)
└── tailwind.config.js      # Konfigurasi Tema UI
```

## 💻 Tech Stack (Teknologi yang Digunakan)

1. **Front-End:** 
   - HTML5 & CSS3
   - **Tailwind CSS** (Utility-first framework untuk desain UI yang premium & responsif).
   - Vanilla JavaScript (Tanpa framework JS besar, sangat ringan).
2. **Back-End:**
   - **PHP 8+** (Procedural & PDO untuk keamanan SQL Injection).
3. **Database:**
   - **MySQL** / MariaDB (Relational Database).

---

## 🚀 Panduan Menjalankan 

1. Pastikan **XAMPP / MAMP** Anda berjalan (Apache & MySQL).
2. Import file `database/schema.sql` ke dalam database `ganbat` via phpMyAdmin.
3. Buka browser dan arahkan ke `http://localhost/Ganbat-project/public/login.php`.
4. (Opsional untuk Developer): Jika melakukan perubahan tampilan CSS, jalankan *compiler* Tailwind dengan perintah: `npx tailwindcss -i ./src/input.css -o ./public/css/style.css`.

> *Proyek ini siap untuk dijalankan! Ganbatte!* 🔥
