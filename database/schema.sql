CREATE DATABASE IF NOT EXISTS ganbat
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ganbat;

CREATE TABLE IF NOT EXISTS users (
    id              INT          AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50)  NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS projects (
    id          INT           AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150)  NOT NULL,
    description TEXT          DEFAULT NULL,
    owner_id    INT           NOT NULL,
    is_archived TINYINT(1)    NOT NULL DEFAULT 0,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS project_members (
    project_id    INT         NOT NULL,
    user_id       INT         NOT NULL,
    status_invite ENUM('pending', 'accepted') NOT NULL DEFAULT 'pending',
    PRIMARY KEY (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS subtasks (
    id            INT          AUTO_INCREMENT PRIMARY KEY,
    project_id    INT          NOT NULL,
    title         VARCHAR(150) NOT NULL,
    assigned_to   INT          DEFAULT NULL,
    status        ENUM('todo', 'ongoing', 'done') NOT NULL DEFAULT 'todo',
    deadline_date DATE         DEFAULT NULL,
    FOREIGN KEY (project_id)  REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id)    ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS notifications (
    id         INT        AUTO_INCREMENT PRIMARY KEY,
    user_id    INT        NOT NULL,
    message    TEXT       NOT NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);