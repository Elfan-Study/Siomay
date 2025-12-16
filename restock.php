<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get current state
$stmt = $pdo->prepare("SELECT * FROM game_state WHERE user_id = ?");
$stmt->execute([$user_id]);
$state = $stmt->fetch();

// Manual restock: 100 porsi @ Rp 3.000 = Rp 300.000
$restock_amount = 100;
$cost_per_portion = 3000;
$total_cost = $restock_amount * $cost_per_portion;

// Check if enough cash
if ($state['cash'] < $total_cost) {
    $_SESSION['error_message'] = "Uang tidak cukup! Butuh Rp " . number_format($total_cost) . " untuk beli 100 porsi.";
    header("Location: dashboard.php");
    exit();
}

// Check if inventory will exceed limit
$new_stock = $state['stock'] + $restock_amount;
if ($new_stock > 500) {
    $_SESSION['error_message'] = "Inventori penuh! Maksimal 500 porsi. Stok sekarang: " . $state['stock'] . " porsi.";
    header("Location: dashboard.php");
    exit();
}

// Process restock
$new_cash = $state['cash'] - $total_cost;

$stmt = $pdo->prepare("UPDATE game_state SET cash = ?, stock = ? WHERE user_id = ?");
$stmt->execute([$new_cash, $new_stock, $user_id]);

$_SESSION['success_message'] = "Berhasil beli 100 porsi! Stok sekarang: " . $new_stock . " porsi.";
header("Location: dashboard.php");
exit();
?>