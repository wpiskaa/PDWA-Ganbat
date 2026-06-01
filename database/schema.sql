-- ============================================================
--  GANBAT - Sistem Manajemen Tugas
--  File   : database/schema.sql
--  Tugas  : Gandhi Muhammad Bagas Saputra (Database & Config)
-- ============================================================
--
-- Cara pakai:
--   1. Buka phpMyAdmin / terminal MySQL
--   2. Jalankan seluruh isi file ini (Import / SOURCE)
--   3. Database "ganbat" beserta tabelnya otomatis terbentuk
-- ============================================================

CREATE DATABASE IF NOT EXISTS ganbat
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ganbat;

-- ------------------------------------------------------------
-- Tabel 1: users
-- Menyimpan akun pengguna (dipakai modul Authentication - Hafiz)
-- ------------------------------------------------------------
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,           -- simpan hasil password_hash(), bukan teks asli
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Tabel 2: tasks
-- Kartu tugas pada papan Kanban (todo / doing / done)
-- ------------------------------------------------------------
CREATE TABLE tasks (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(150) NOT NULL,
    description     TEXT,
    priority        ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status          ENUM('todo', 'doing', 'done') DEFAULT 'todo',
    deadline_date   DATE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Tabel 3: task_assignees
-- Tabel relasi (many-to-many) antara tugas dan pengguna
-- Satu tugas bisa dipegang beberapa orang, dan sebaliknya
-- ------------------------------------------------------------
CREATE TABLE task_assignees (
    task_id     INT NOT NULL,
    user_id     INT NOT NULL,
    PRIMARY KEY (task_id, user_id),
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Tabel 4: comments
-- Kolom diskusi di dalam detail kartu tugas (fitur Jundi)
-- ------------------------------------------------------------
CREATE TABLE comments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    task_id         INT  NOT NULL,
    user_id         INT  NOT NULL,
    comment_text    TEXT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- (Opsional) Data contoh untuk testing.
-- Hapus blok ini kalau tidak diperlukan.
-- ============================================================
-- INSERT INTO users (username, password) VALUES
-- ('gandhi', '$2y$10$contohhashpasswordsaja');
