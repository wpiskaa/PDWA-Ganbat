<?php
require_once __DIR__ . '/src/config/database.php';

try {
    // Add global_deadline to projects table
    $pdo->exec("ALTER TABLE projects ADD COLUMN global_deadline DATE DEFAULT NULL AFTER description");
    echo "Column global_deadline added to projects table successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
