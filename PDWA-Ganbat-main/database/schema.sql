-- ============================================================
--  GANBAT - Sistem Manajemen Tugas
--  File   : database/schema.sql
--  Tugas  : Gandhi Muhammad Bagas Saputra (Database & Config)
-- ============================================================


CREATE DATABASE IF NOT EXISTS ganbat
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ganbat;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `priority` varchar(20) DEFAULT 'medium',
  `status` varchar(20) DEFAULT 'todo',
  `deadline_date` date,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `task_assignees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE
);

CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `comment_text` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE
);

-- ============================================================
-- (Opsional) Data contoh untuk testing.
-- Hapus blok ini kalau tidak diperlukan.
-- ============================================================
-- INSERT INTO users (username, password) VALUES
-- ('gandhi', '$2y$10$contohhashpasswordsaja');
