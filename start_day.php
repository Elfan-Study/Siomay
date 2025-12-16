<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get game state
$stmt = $pdo->prepare("SELECT * FROM game_state WHERE user_id = ?");
$stmt->execute([$user_id]);
$state = $stmt->fetch();

// AUTOMATIC DAILY STOCK PURCHASE
// Setiap hari otomatis beli 100 porsi (Rp 3.000 per porsi)
$daily_stock_purchase = 100;
$cost_per_portion = 3000;
$daily_cost = $daily_stock_purchase * $cost_per_portion; // Rp 300.000

// Cek apakah uang cukup
if ($state['cash'] >= $daily_cost) {
    $new_cash = $state['cash'] - $daily_cost;
    $new_stock = min(500, $state['stock'] + $daily_stock_purchase); // Max 500 porsi

    // Update state
    $stmt = $pdo->prepare("UPDATE game_state SET cash = ?, stock = ? WHERE user_id = ?");
    $stmt->execute([$new_cash, $new_stock, $user_id]);

    // Refresh state
    $stmt = $pdo->prepare("SELECT * FROM game_state WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $state = $stmt->fetch();
} else {
    // Tidak cukup uang untuk beli stok - game over
    $stmt = $pdo->prepare("UPDATE game_state SET is_game_over = 1, game_over_reason = 'no_stock_money' WHERE user_id = ?");
    $stmt->execute([$user_id]);
    header("Location: gameover.php");
    exit();
}

// Generate 2-3 random events for today
$num_events = rand(2, 3);

// Get event pool with weighted probability
// 60% positive, 40% negative
$positive_events = $pdo->query("SELECT * FROM event_templates WHERE event_type = 'positive' ORDER BY RAND() LIMIT 10")->fetchAll();
$negative_events = $pdo->query("SELECT * FROM event_templates WHERE event_type = 'negative' ORDER BY RAND() LIMIT 10")->fetchAll();

$selected_events = [];
for ($i = 0; $i < $num_events; $i++) {
    $rand = rand(1, 100);
    if ($rand <= 60 && count($positive_events) > 0) {
        $selected_events[] = array_shift($positive_events);
    } elseif (count($negative_events) > 0) {
        $selected_events[] = array_shift($negative_events);
    } elseif (count($positive_events) > 0) {
        $selected_events[] = array_shift($positive_events);
    }
}

// Store events in session
$_SESSION['daily_events'] = $selected_events;
$_SESSION['current_event_index'] = 0;
$_SESSION['day_started'] = true;
$_SESSION['starting_cash'] = $state['cash'];

// Redirect to first event
header("Location: event.php");
exit();
?>