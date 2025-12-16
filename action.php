<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Fetch current state
$stmt = $pdo->prepare("SELECT * FROM game_state WHERE user_id = ?");
$stmt->execute([$user_id]);
$state = $stmt->fetch();

if (!$state) {
    die("Game state not found.");
}

try {
    $pdo->beginTransaction();

    switch ($action) {
        case 'buy_ingredients': // "Belanja Stok"
            $cost = 500000;
            $quantity = 50;

            // Limit max stock to prevent insane numbers (optional, but good for gameplay)
            if ($state['siomay_stock'] > 200) {
                $_SESSION['flash'] = "Gudang penuh! (Max 200 porsi)";
            } elseif ($state['cash'] >= $cost) {
                $stmt = $pdo->prepare("UPDATE game_state SET cash = cash - ?, siomay_stock = siomay_stock + ?, daily_cost = daily_cost + ? WHERE user_id = ?");
                $stmt->execute([$cost, $quantity, $cost, $user_id]);
                $_SESSION['flash'] = "Berhasil belanja 50 porsi siomay.";
            } else {
                $_SESSION['flash'] = "Uang tidak cukup untuk belanja!";
            }
            break;

        case 'process_sales': // "Buka Toko"
            if ($state['siomay_stock'] > 0) {
                // Demand Calculation: Base 20 + (Reputation/2) + Random(-5 to 5)
                $demand = floor(20 + ($state['reputation'] / 2) + rand(-5, 10));
                $demand = max(5, $demand); // Min 5 customers

                $sold = min($state['siomay_stock'], $demand);
                $price_per_unit = 15000; // Increased price
                $revenue = $sold * $price_per_unit;

                // Reputation update
                $rep_change = 0;
                if ($sold < $demand) {
                    $rep_change = -2; // Kecewakan pelanggan karna stok habis
                    $msg_rep = "Pelanggan kecewa stok habis! (-2 Rep)";
                } else {
                    $rep_change = 1; // Pelayanan bagus
                    $msg_rep = "Pelanggan puas! (+1 Rep)";
                }

                // Random Event (20% chance)
                $event_msg = "";
                $event_cost = 0;
                $rand = rand(1, 100);
                if ($rand <= 10) {
                    $event_cost = 50000;
                    $event_msg = "Mendadak ada iuran sampah/keamanan! (-Rp 50.000).";
                } elseif ($rand <= 15) {
                    $bonus = 100000;
                    $revenue += $bonus;
                    $event_msg = "Ada turis memborong tips ekstra! (+Rp 100.000).";
                }

                $final_cash_change = $revenue - $event_cost;
                $new_rep = min(100, max(0, $state['reputation'] + $rep_change));

                $stmt = $pdo->prepare("UPDATE game_state SET 
                    cash = cash + ?, 
                    daily_revenue = daily_revenue + ?, 
                    siomay_stock = siomay_stock - ?,
                    reputation = ?,
                    daily_withdrawal = daily_withdrawal + ? 
                    WHERE user_id = ?");
                // Note: using daily_withdrawal field to track 'misc costs' like events for now, or just net cash
                // Actually let's just update cash directly and track sales revenue separate
                $stmt->execute([
                    $final_cash_change,
                    $revenue,
                    $sold,
                    $new_rep,
                    $event_cost,
                    $user_id
                ]);

                $_SESSION['flash'] = "Toko Tutup! Terjual: $sold porsi. Pendapatan: Rp " . number_format($revenue) . ". $msg_rep $event_msg";
            } else {
                $_SESSION['flash'] = "Stok habis! Belanja dulu sebelum buka toko.";
            }
            break;

        case 'pay_debt':
            $amount = 500000;
            if ($state['debt'] > 0 && $state['cash'] >= $amount) {
                $pay = min($state['debt'], $amount);
                $stmt = $pdo->prepare("UPDATE game_state SET cash = cash - ?, debt = debt - ? WHERE user_id = ?");
                $stmt->execute([$pay, $pay, $user_id]);
                $_SESSION['flash'] = "Membayar utang sebesar Rp " . number_format($pay);
            }
            break;

        case 'take_loan': // "Pinjam Modal"
            $loan = 1000000;
            if ($state['debt'] < 5000000) { // Max debt limit
                $stmt = $pdo->prepare("UPDATE game_state SET cash = cash + ?, debt = debt + ? WHERE user_id = ?");
                $stmt->execute([$loan, $loan, $user_id]);
                $_SESSION['flash'] = "Berhasil meminjam Rp 1.000.000. Hati-hati bunga!";
            } else {
                $_SESSION['flash'] = "Bank menolak! Utangmu terlalu banyak.";
            }
            break;

        case 'end_day':
            // Calculate Daily Profit
            $daily_profit = $state['daily_revenue'] - $state['daily_cost'] - $state['daily_withdrawal'];

            // Log Profit
            $stmt = $pdo->prepare("INSERT INTO daily_profit (user_id, day, profit) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $state['current_day'], $daily_profit]);

            // Debt Interest (1% per day if debt exists)
            $interest = 0;
            if ($state['debt'] > 0) {
                $interest = floor($state['debt'] * 0.01);
                $pdo->prepare("UPDATE game_state SET debt = debt + ? WHERE user_id = ?")->execute([$interest, $user_id]);
            }

            // Reset Daily Stats and Increment Day
            $stmt = $pdo->prepare("UPDATE game_state SET current_day = current_day + 1, daily_revenue = 0, daily_cost = 0, daily_withdrawal = 0 WHERE user_id = ?");
            $stmt->execute([$user_id]);

            $_SESSION['flash'] = "Hari berganti. Profit kemarin: Rp " . number_format($daily_profit) . ($interest > 0 ? ". Bunga utang: Rp " . number_format($interest) : "");
            break;

        case 'restart':
            // Reset everything
            $pdo->prepare("DELETE FROM daily_profit WHERE user_id = ?")->execute([$user_id]);
            $pdo->prepare("UPDATE game_state SET cash = 1000000, siomay_stock = 0, reputation = 50, current_day = 1, daily_revenue=0, daily_cost=0, daily_withdrawal=0, debt=0, shop_level=1 WHERE user_id = ?")->execute([$user_id]);
            $_SESSION['flash'] = "Game Restarted!";
            header("Location: dashboard.php");
            exit();
    }

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = "Error: " . $e->getMessage();
}

header("Location: dashboard.php");
exit();
?>