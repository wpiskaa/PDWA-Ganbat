-- ============================================================
--  GANBAT - Sistem Manajemen Tugas
--  File   : database/schema.sql
--  Tugas  : Gandhi Muhammad Bagas Saputra (Database & Config)
-- ============================================================
--
--  Jalankan file ini di phpMyAdmin (tab SQL) atau CLI MySQL
--  untuk membuat database + tabel-tabel yang dibutuhkan.
-- ============================================================

CREATE DATABASE IF NOT EXISTS ganbat
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ganbat;

-- 1. Tabel users
CREATE TABLE IF NOT EXISTS users (
    id              INT          AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50)  NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabel projects (proyek/kelompok tugas)
CREATE TABLE IF NOT EXISTS projects (
    id              INT           AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(150)  NOT NULL,
    description     TEXT          DEFAULT NULL,
    global_deadline DATETIME      DEFAULT NULL,
    owner_id        INT           NOT NULL,
    is_archived     TINYINT(1)    NOT NULL DEFAULT 0,
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Tabel project_members (anggota proyek + sistem invite)
CREATE TABLE IF NOT EXISTS project_members (
    project_id    INT         NOT NULL,
    user_id       INT         NOT NULL,
    status_invite ENUM('pending', 'accepted') NOT NULL DEFAULT 'pending',
    is_archived   TINYINT(1)  NOT NULL DEFAULT 0,
    created_at    TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Tabel subtasks (pekerjaan di dalam proyek)
CREATE TABLE IF NOT EXISTS subtasks (
    id            INT          AUTO_INCREMENT PRIMARY KEY,
    project_id    INT          NOT NULL,
    title         VARCHAR(150) NOT NULL,
    description   TEXT         DEFAULT NULL,
    assigned_to   INT          DEFAULT NULL,
    status        ENUM('todo', 'ongoing', 'done') NOT NULL DEFAULT 'todo',
    priority      ENUM('low', 'medium', 'high')   NOT NULL DEFAULT 'medium',
    deadline_date DATETIME     DEFAULT NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id)  REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. Tabel notifications (notifikasi untuk invite, dll)
CREATE TABLE IF NOT EXISTS notifications (
    id           INT          AUTO_INCREMENT PRIMARY KEY,
    user_id      INT          NOT NULL,
    type         VARCHAR(50)  NOT NULL DEFAULT 'info',
    message      TEXT         NOT NULL,
    reference_id INT          DEFAULT NULL,
    is_read      TINYINT(1)   NOT NULL DEFAULT 0,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Tabel activity_logs (Laporan)
CREATE TABLE IF NOT EXISTS activity_logs (
    id          INT           AUTO_INCREMENT PRIMARY KEY,
    project_id  INT           NOT NULL,
    user_id     INT           NOT NULL,
    log_text    TEXT          NOT NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;