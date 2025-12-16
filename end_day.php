<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get state
$stmt = $pdo->prepare("SELECT * FROM game_state WHERE user_id = ?");
$stmt->execute([$user_id]);
$state = $stmt->fetch();

$starting_cash = $_SESSION['starting_cash'] ?? $state['cash'];

// Calculate daily sales - REPUTASI = CHANCE pelanggan datang
// Reputasi tinggi = chance besar pelanggan datang
// Max pelanggan potensial per hari = 100 orang

$reputation = $state['reputation'];
$max_potential_customers = 100;
$buyers = 0;

// Setiap pelanggan potensial punya chance datang berdasarkan reputasi
for ($i = 0; $i < $max_potential_customers; $i++) {
    $chance = rand(1, 100);
    if ($chance <= $reputation) {
        // Pelanggan datang jika random <= reputasi
        $buyers++;
    }
}

// Minimal 1 pembeli
$buyers = max(1, $buyers);

// Hitung porsi yang dipesan
// Setiap pembeli pesan 3-5 porsi (normal)
// Ada chance 20% pembeli cuma beli 1 porsi (tidak puas)
$total_ordered = 0;
$low_order_count = 0; // Hitung berapa pembeli yang cuma beli 1

for ($i = 0; $i < $buyers; $i++) {
    $chance = rand(1, 100);
    if ($chance <= 20) {
        // 20% chance: pembeli cuma beli 1 porsi (tidak puas)
        $total_ordered += 1;
        $low_order_count++;
    } else {
        // 80% chance: pembeli beli 3-5 porsi (normal)
        $total_ordered += rand(3, 5);
    }
}

$sold = min($state['stock'], $total_ordered); // Terjual = min(stok, total pesanan)

// LIMIT: Maksimal 500 porsi per hari
$sold = min($sold, 500);

$price_per_unit = 15000;
$revenue = $sold * $price_per_unit;

// Reputasi turun jika banyak pembeli cuma beli 1 porsi
$reputation_change = 0;
if ($low_order_count > ($buyers * 0.3)) {
    // Jika lebih dari 30% pembeli cuma beli 1, reputasi turun
    $reputation_change = -3;
}

// Cost per unit
$cost_per_unit = 10000;
$cost = $sold * $cost_per_unit;

// Calculate profit
$profit = $revenue - $cost;

// Update state
$new_cash = $state['cash'] + $revenue - $cost;
$new_stock = $state['stock'] - $sold;
$new_reputation = max(0, min(100, $state['reputation'] + $reputation_change));
$new_day = $state['current_day'] + 1;

// Check game over conditions
$is_game_over = false;
$game_over_reason = null;

if ($new_cash <= 0) {
    $is_game_over = true;
    $game_over_reason = 'bankrupt';
} elseif ($state['debt'] > 5000000) {
    $is_game_over = true;
    $game_over_reason = 'debt';
} elseif ($state['reputation'] < 10) {
    $is_game_over = true;
    $game_over_reason = 'reputation';
} elseif ($new_day > 30) {
    // Game selesai setelah 30 hari - evaluasi performa
    $is_game_over = true;

    // Ambil profit 5 hari terakhir untuk evaluasi tren
    $stmt = $pdo->prepare("SELECT profit FROM daily_summary WHERE user_id = ? ORDER BY day DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_profits = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $avg_profit = array_sum($recent_profits) / count($recent_profits);

    if ($avg_profit > 100000) {
        $game_over_reason = 'success_good'; // Grafik bagus
    } else {
        $game_over_reason = 'success_bad'; // Grafik turun
    }
}

// Update game state
$stmt = $pdo->prepare("UPDATE game_state SET cash = ?, stock = ?, reputation = ?, current_day = ?, is_game_over = ?, game_over_reason = ? WHERE user_id = ?");
$stmt->execute([$new_cash, $new_stock, $new_reputation, $new_day, $is_game_over, $game_over_reason, $user_id]);

// Save daily summary
$stmt = $pdo->prepare("INSERT INTO daily_summary (user_id, day, starting_cash, ending_cash, revenue, cost, profit, stock_sold) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $state['current_day'], $starting_cash, $new_cash, $revenue, $cost, $profit, $sold]);

// Check for milestone days (7, 15, 30)
$milestone_days = [7, 15, 30];
if (in_array($state['current_day'], $milestone_days)) {
    // Store milestone day in session
    $_SESSION['milestone_day'] = $state['current_day'];

    // Clear other session data
    unset($_SESSION['daily_events']);
    unset($_SESSION['current_event_index']);
    unset($_SESSION['day_started']);
    unset($_SESSION['starting_cash']);
    unset($_SESSION['last_result']);

    // Redirect to milestone feedback
    header("Location: milestone.php");
    exit();
}

// Clear session
unset($_SESSION['daily_events']);
unset($_SESSION['current_event_index']);
unset($_SESSION['day_started']);
unset($_SESSION['starting_cash']);
unset($_SESSION['last_result']);

if ($is_game_over) {
    header("Location: gameover.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutup Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .summary-card {
            background: white;
            border-radius: 25px;
            max-width: 700px;
            width: 100%;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .profit-box {
            background: linear-gradient(135deg, #20bf55 0%, #01baef 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            margin: 2rem 0;
        }

        .profit-box.loss {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>

<body>
    <div class="summary-card">
        <h2 class="text-center mb-4">🌙 Tutup Buku Hari <?php echo $state['current_day']; ?></h2>

        <div class="profit-box <?php echo $profit < 0 ? 'loss' : ''; ?>">
            <h6>Laba/Rugi Hari Ini</h6>
            <h1><?php echo $profit >= 0 ? '+' : ''; ?>Rp <?php echo number_format($profit); ?></h1>
        </div>

        <div class="summary-row">
            <span>Pembeli Hari Ini</span>
            <strong class="text-info"><?php echo $buyers; ?> orang</strong>
        </div>
        <div class="summary-row">
            <span>Siomay Terjual</span>
            <strong><?php echo $sold; ?> porsi</strong>
        </div>
        <div class="summary-row">
            <span>Pendapatan</span>
            <strong class="text-success">+Rp <?php echo number_format($revenue); ?></strong>
        </div>
        <div class="summary-row">
            <span>Biaya Produksi</span>
            <strong class="text-danger">-Rp <?php echo number_format($cost); ?></strong>
        </div>
        <div class="summary-row">
            <span>Kas Awal</span>
            <strong>Rp <?php echo number_format($starting_cash); ?></strong>
        </div>
        <div class="summary-row">
            <span>Kas Akhir</span>
            <strong>Rp <?php echo number_format($new_cash); ?></strong>
        </div>
        <div class="summary-row">
            <span>Sisa Stok</span>
            <strong><?php echo $new_stock; ?> porsi</strong>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-primary btn-lg px-5">Lanjut ke Hari <?php echo $new_day; ?> <i
                    class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</body>

</html>