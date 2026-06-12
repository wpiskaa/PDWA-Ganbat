-- ============================================================
--  GANBAT - Sistem Manajemen Tugas
--  File   : database/schema.sql
--  Tugas  : Gandhi Muhammad Bagas Saputra (Database & Auth Fix)
-- ============================================================
--
--  PERHATIAN: Skema ini adalah KUNCI TIM — jangan ubah nama
--  tabel atau kolom tanpa persetujuan seluruh anggota.
--
--  Cara pakai:
--    1. Buka phpMyAdmin / terminal MySQL
--    2. Jalankan seluruh isi file ini (Import / SOURCE)
--    3. Database "ganbat" beserta tabelnya otomatis terbentuk
-- ============================================================

CREATE DATABASE IF NOT EXISTS ganbat
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ganbat;

-- ------------------------------------------------------------
-- Tabel 1: users
-- Menyimpan akun pengguna.
-- Kolom profile_picture dipakai oleh modul Hafiz (upload foto).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT          AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50)  NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,               -- hasil password_hash(), BUKAN teks asli
    profile_picture VARCHAR(255) DEFAULT NULL            -- nama file foto, diisi oleh ProfileController
);

-- ------------------------------------------------------------
-- Tabel 2: projects
-- Satu project dimiliki oleh satu owner (users.id).
-- is_archived = 1 berarti project disembunyikan (modul Rafie).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
    id          INT           AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150)  NOT NULL,
    description TEXT          DEFAULT NULL,
    owner_id    INT           NOT NULL,
    is_archived TINYINT(1)    NOT NULL DEFAULT 0,        -- 0 = aktif, 1 = diarsipkan
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Tabel 3: project_members
-- Relasi user <-> project beserta status undangan.
-- status_invite: 'pending' (belum direspons) | 'accepted' (bergabung)
-- Dipakai oleh modul Aquilla (invite) dan Zaki (notifikasi).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS project_members (
    project_id    INT         NOT NULL,
    user_id       INT         NOT NULL,
    status_invite ENUM('pending', 'accepted') NOT NULL DEFAULT 'pending',
    PRIMARY KEY (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Tabel 4: subtasks
-- Pekerjaan/kartu tugas di dalam sebuah project.
-- assigned_to merujuk ke users.id (hanya member accepted).
-- status    : 'todo' | 'ongoing' | 'done'
-- Dipakai oleh modul Ikhlasul (assignment) dan Faiq (kanban).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS subtasks (
    id            INT          AUTO_INCREMENT PRIMARY KEY,
    project_id    INT          NOT NULL,
    title         VARCHAR(150) NOT NULL,
    assigned_to   INT          DEFAULT NULL,             -- NULL = belum di-assign
    status        ENUM('todo', 'ongoing', 'done') NOT NULL DEFAULT 'todo',
    deadline_date DATE         DEFAULT NULL,
    FOREIGN KEY (project_id)  REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id)    ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- Tabel 5: notifications
-- Pesan sistem untuk tiap user (undangan, perubahan status, dll).
-- is_read: 0 = belum dibaca, 1 = sudah dibaca.
-- Dipakai oleh modul Zaki (notification center).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id         INT       AUTO_INCREMENT PRIMARY KEY,
    user_id    INT       NOT NULL,
    message    TEXT      NOT NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- (Opsional) Data dummy untuk testing lokal.
-- Hapus atau komentari blok ini sebelum demo ke dosen.
-- Password semua akun dummy: "password123"
-- ============================================================
-- INSERT INTO users (username, password) VALUES
--     ('gandhi',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
--     ('hafiz',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
--     ('bima',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
--
-- INSERT INTO projects (title, description, owner_id) VALUES
--     ('Ganbat Dev Sprint 1', 'Pengembangan fitur inti platform.', 1);
--
-- INSERT INTO project_members (project_id, user_id, status_invite) VALUES
--     (1, 2, 'accepted'),
--     (1, 3, 'pending');
--
-- INSERT INTO subtasks (project_id, title, assigned_to, status, deadline_date) VALUES
--     (1, 'Setup repo & folder structure', 2, 'done',    '2025-06-01'),
--     (1, 'Buat halaman login & register',  2, 'ongoing', '2025-06-10'),
--     (1, 'Implementasi Kanban Board',       3, 'todo',    '2025-06-15');