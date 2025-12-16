<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Verify user exists in database
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
if (!$stmt->fetch()) {
    // User doesn't exist, logout and redirect
    session_destroy();
    header("Location: index.php");
    exit();
}

// Get game state
$stmt = $pdo->prepare("SELECT * FROM game_state WHERE user_id = ?");
$stmt->execute([$user_id]);
$state = $stmt->fetch();

if (!$state) {
    // Auto-create if missing with default values
    $pdo->prepare("INSERT INTO game_state (user_id, cash, stock, reputation, current_day, debt, personal_withdrawal, total_revenue, total_cost, is_game_over) VALUES (?, 500000, 100, 50, 1, 0, 0, 0, 0, 0)")->execute([$user_id]);
    header("Location: dashboard.php");
    exit();
}

// Allow access to dashboard even if game over (to see stats)
// User can restart from dashboard

// Get daily summary for chart
$stmt = $pdo->prepare("SELECT day, profit, ending_cash FROM daily_summary WHERE user_id = ? ORDER BY day ASC LIMIT 30");
$stmt->execute([$user_id]);
$summaries = $stmt->fetchAll();

$days = [];
$profits = [];
$cash_history = [];
foreach ($summaries as $s) {
    $days[] = "Hari " . $s['day'];
    $profits[] = $s['profit'];
    $cash_history[] = $s['ending_cash'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Warung Siomay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f0f2f5;
        }

        .stat-card {
            border-radius: 15px;
            padding: 1.5rem;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h6 {
            opacity: 0.9;
            font-size: 0.85rem;
        }

        .stat-card h3 {
            font-weight: bold;
            margin: 0.5rem 0;
        }

        .bg-cash {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .bg-stock {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .bg-debt {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .bg-reputation {
            background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
        }

        .main-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .btn-start-day {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 1rem 3rem;
            font-size: 1.2rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s;
        }

        .btn-start-day:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">🍢 Warung Siomay - <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Keluar</a>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['success_message']);
                unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['error_message']);
                unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card bg-cash">
                    <h6><i class="fas fa-wallet"></i> UANG KAS</h6>
                    <h3>Rp <?php echo number_format($state['cash'] ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-stock">
                    <h6><i class="fas fa-box"></i> STOK SIOMAY</h6>
                    <h3><?php echo $state['stock'] ?? 0; ?> porsi</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-debt">
                    <h6><i class="fas fa-file-invoice-dollar"></i> UTANG</h6>
                    <h3>Rp <?php echo number_format($state['debt'] ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-reputation">
                    <h6><i class="fas fa-star"></i> REPUTASI</h6>
                    <h3><?php echo $state['reputation'] ?? 50; ?>/100</h3>
                </div>
            </div>
        </div>


        <div class="main-card text-center mb-4">
            <h2 class="mb-3">📅 Hari ke-<?php echo $state['current_day']; ?></h2>

            <?php if (count($summaries) >= 3): ?>
                <?php
                // Analisis performa berdasarkan 5 hari terakhir
                $recent_days = array_slice($summaries, -5);
                $recent_profits = array_column($recent_days, 'profit');
                $avg_profit = array_sum($recent_profits) / count($recent_profits);

                // Hitung tren (naik/turun)
                $first_half = array_slice($recent_profits, 0, 2);
                $second_half = array_slice($recent_profits, -2);
                $avg_first = array_sum($first_half) / count($first_half);
                $avg_second = array_sum($second_half) / count($second_half);
                $trend = $avg_second > $avg_first ? 'naik' : 'turun';

                $is_good = $avg_profit > 100000 && $trend == 'naik';
                ?>

                <div class="alert alert-<?php echo $is_good ? 'success' : 'warning'; ?> mb-4">
                    <h5>📊 Analisis Performa (5 Hari Terakhir)</h5>
                    <p class="mb-2">
                        <strong>Rata-rata Profit:</strong> Rp <?php echo number_format($avg_profit); ?><br>
                        <strong>Tren Grafik:</strong> <?php echo $trend == 'naik' ? '📈 Naik' : '📉 Turun'; ?>
                    </p>

                    <?php if ($is_good): ?>
                        <p class="mb-0 text-success"><strong>✅ Bagus!</strong> Usahamu berkembang dengan baik. Pertahankan
                            strategi ini!</p>
                    <?php else: ?>
                        <p class="mb-2"><strong>⚠️ Perlu Perbaikan</strong></p>
                        <small>
                            <strong>Saran:</strong><br>
                            <?php if ($avg_profit < 50000): ?>
                                • Profit terlalu rendah. Coba tingkatkan reputasi untuk dapat lebih banyak pembeli.<br>
                            <?php endif; ?>
                            <?php if ($trend == 'turun'): ?>
                                • Grafik menurun. Evaluasi keputusan event - hindari ambil uang pribadi terlalu sering.<br>
                            <?php endif; ?>
                            <?php if ($state['stock'] > 200): ?>
                                • Stok terlalu banyak (<?php echo $state['stock']; ?> porsi). Fokus jualan dulu, jangan belanja stok
                                berlebihan.<br>
                            <?php endif; ?>
                            <?php if ($state['debt'] > 1000000): ?>
                                • Utang tinggi (Rp <?php echo number_format($state['debt']); ?>). Prioritaskan bayar utang.<br>
                            <?php endif; ?>
                            <?php if ($state['reputation'] < 50): ?>
                                • Reputasi rendah (<?php echo $state['reputation']; ?>). Pilih event yang tingkatkan reputasi.<br>
                            <?php endif; ?>
                        </small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($state['is_game_over']): ?>
                <div class="alert alert-danger mb-4">
                    <h5>🚫 GAME OVER</h5>
                    <p class="mb-0">Permainan telah berakhir. Lihat grafik dan statistik di bawah, lalu klik "Mulai Ulang"
                        untuk bermain lagi.</p>
                </div>
            <?php endif; ?>

            <p class="lead text-muted">
                <?php echo $state['is_game_over'] ? 'Permainan telah berakhir' : 'Siap menjalankan usaha hari ini?'; ?>
            </p>

            <?php if (!$state['is_game_over']): ?>
                <a href="start_day.php" class="btn btn-start-day mt-3">
                    <i class="fas fa-play"></i> Mulai Hari Baru
                </a>
            <?php else: ?>
                <button class="btn btn-secondary mt-3" disabled>
                    <i class="fas fa-ban"></i> Game Over - Tidak Bisa Lanjut
                </button>
            <?php endif; ?>

            <?php if (!$state['is_game_over']): ?>
                <div class="mt-3">
                    <a href="restock.php" class="btn btn-outline-success btn-lg"
                        onclick="return confirm('Beli 100 porsi seharga Rp 300.000?')">
                        <i class="fas fa-shopping-cart"></i> Beli Stok (100 porsi - Rp 300rb)
                    </a>
                </div>
            <?php endif; ?>

            <div class="mt-3">
                <a href="restart.php"
                    class="btn btn-<?php echo $state['is_game_over'] ? 'danger' : 'outline-danger'; ?> btn-lg"
                    onclick="return confirm('Yakin ingin mulai dari awal? Progress akan hilang!')">
                    <i class="fas fa-redo"></i> Mulai Ulang dari Hari 1
                </a>
            </div>
        </div>

        <?php if (count($summaries) > 0): ?>
            <div class="main-card">
                <h5 class="mb-4"><i class="fas fa-chart-line"></i> Grafik Keuangan</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <canvas id="profitChart"></canvas>
                    </div>
                    <div class="col-md-6 mb-3">
                        <canvas id="cashChart"></canvas>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (count($summaries) > 0): ?>
            // Profit Chart
            new Chart(document.getElementById('profitChart'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($days); ?>,
                    datasets: [{
                        label: 'Laba/Rugi Harian (Rp)',
                        data: <?php echo json_encode($profits); ?>,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { title: { display: true, text: 'Laba/Rugi Harian' } }
                }
            });

            // Cash Chart
            new Chart(document.getElementById('cashChart'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($days); ?>,
                    datasets: [{
                        label: 'Kas Akhir Hari (Rp)',
                        data: <?php echo json_encode($cash_history); ?>,
                        borderColor: '#f5576c',
                        backgroundColor: 'rgba(245, 87, 108, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { title: { display: true, text: 'Perkembangan Kas' } }
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>