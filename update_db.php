<?php
require_once 'config.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add 'debt' column if not exists
    try {
        $pdo->query("SELECT debt FROM game_state LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE game_state ADD COLUMN debt DECIMAL(15,2) DEFAULT 0");
        echo "Added 'debt' column.<br>";
    }

    // Add 'shop_level' column if not exists
    try {
        $pdo->query("SELECT shop_level FROM game_state LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE game_state ADD COLUMN shop_level INT DEFAULT 1");
        echo "Added 'shop_level' column.<br>";
    }

    echo "Database updated successfully! You can delete this file now.";

} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>