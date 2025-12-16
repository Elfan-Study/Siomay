<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Reset game state
$pdo->prepare("UPDATE game_state SET cash = 500000, stock = 100, debt = 0, reputation = 50, current_day = 1, personal_withdrawal = 0, total_revenue = 0, total_cost = 0, is_game_over = 0, game_over_reason = NULL WHERE user_id = ?")->execute([$user_id]);

// Clear history
$pdo->prepare("DELETE FROM daily_events WHERE user_id = ?")->execute([$user_id]);
$pdo->prepare("DELETE FROM daily_summary WHERE user_id = ?")->execute([$user_id]);

// Clear and recreate session
session_destroy();
session_start();
$_SESSION['user_id'] = $user_id;
$_SESSION['username'] = $username;

header("Location: dashboard.php");
exit();
?>