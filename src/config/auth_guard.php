<?php
/**
 * ============================================================
 *  GANBAT - Sistem Manajemen Tugas
 *  File   : src/config/auth_guard.php
 * ============================================================
 *  Include file ini di awal halaman yang membutuhkan login.
 *  Jika user belum login, akan di-redirect ke login.php.
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}