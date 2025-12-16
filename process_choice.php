<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['choice'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = $_POST['event_id'];
$choice = $_POST['choice'];

// Get event details
$stmt = $pdo->prepare("SELECT * FROM event_templates WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

// Get current state
$stmt = $pdo->prepare("SELECT * FROM game_state WHERE user_id = ?");
$stmt->execute([$user_id]);
$state = $stmt->fetch();

// Determine impact based on choice
$impact_cash = $choice == 'A' ? $event['choice_a_cash'] : $event['choice_b_cash'];
$impact_stock = $choice == 'A' ? $event['choice_a_stock'] : $event['choice_b_stock'];
$impact_reputation = $choice == 'A' ? $event['choice_a_reputation'] : $event['choice_b_reputation'];
$impact_debt = $choice == 'A' ? $event['choice_a_debt'] : $event['choice_b_debt'];
$result_text = $choice == 'A' ? $event['choice_a_result'] : $event['choice_b_result'];

// Apply impacts
$new_cash = $state['cash'] + $impact_cash;
$new_stock = max(0, $state['stock'] + $impact_stock);
$new_reputation = max(0, min(100, $state['reputation'] + $impact_reputation));
$new_debt = max(0, $state['debt'] + $impact_debt);

// Track personal withdrawal
$personal_withdrawal = $state['personal_withdrawal'];
if ($impact_cash < 0 && strpos(strtolower($event['event_description']), 'pribadi') !== false) {
    $personal_withdrawal += abs($impact_cash);
}

// Update game state
$stmt = $pdo->prepare("UPDATE game_state SET cash = ?, stock = ?, reputation = ?, debt = ?, personal_withdrawal = ? WHERE user_id = ?");
$stmt->execute([$new_cash, $new_stock, $new_reputation, $new_debt, $personal_withdrawal, $user_id]);

// Log event
$stmt = $pdo->prepare("INSERT INTO daily_events (user_id, day, event_id, event_name, choice_made, impact_cash, impact_stock, impact_reputation, impact_debt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $state['current_day'], $event_id, $event['event_name'], $choice, $impact_cash, $impact_stock, $impact_reputation, $impact_debt]);

// Move to next event
$_SESSION['current_event_index']++;
$_SESSION['last_result'] = $result_text;

header("Location: result.php");
exit();
?>