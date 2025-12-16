<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['milestone_day'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$milestone_day = $_SESSION['milestone_day'];

// Get game state
$stmt = $pdo->prepare("SELECT * FROM game_state WHERE user_id = ?");
$stmt->execute([$user_id]);
$state = $stmt->fetch();

// Get daily summaries up to this milestone
$stmt = $pdo->prepare("SELECT * FROM daily_summary WHERE user_id = ? AND day <= ? ORDER BY day ASC");
$stmt->execute([$user_id, $milestone_day]);
$summaries = $stmt->fetchAll();

// Analyze performance
$total_profit = array_sum(array_column($summaries, 'profit'));
$avg_profit = $total_profit / count($summaries);

// Calculate trend (first half vs second half)
$mid_point = floor(count($summaries) / 2);
$first_half = array_slice($summaries, 0, $mid_point);
$second_half = array_slice($summaries, $mid_point);

$avg_first = array_sum(array_column($first_half, 'profit')) / count($first_half);
$avg_second = array_sum(array_column($second_half, 'profit')) / count($second_half);

$trend = $avg_second > $avg_first ? 'naik' : 'turun';
$trend_percentage = abs((($avg_second - $avg_first) / $avg_first) * 100);

// Performance rating
$performance = 'buruk';
$performance_class = 'danger';
if ($avg_profit > 200000 && $trend == 'naik') {
    $performance = 'sangat_baik';
    $performance_class = 'success';
} elseif ($avg_profit > 100000 && $trend == 'naik') {
    $performance = 'baik';
    $performance_class = 'success';
} elseif ($avg_profit > 50000) {
    $performance = 'cukup';
    $performance_class = 'warning';
}

// Generate suggestions
$suggestions = [];

if ($avg_profit < 100000) {
    $suggestions[] = "💰 Profit terlalu rendah (rata-rata Rp " . number_format($avg_profit) . "). Fokus tingkatkan reputasi untuk dapat lebih banyak pembeli.";
}

if ($trend == 'turun') {
    $suggestions[] = "📉 Grafik menurun " . number_format($trend_percentage, 1) . "%. Evaluasi keputusan event - hindari ambil uang pribadi terlalu sering.";
}

if ($state['stock'] > 300) {
    $suggestions[] = "📦 Stok terlalu banyak (" . $state['stock'] . " porsi). Ini mengunci modal. Fokus jualan dulu, jangan belanja berlebihan.";
}

if ($state['debt'] > 2000000) {
    $suggestions[] = "💳 Utang tinggi (Rp " . number_format($state['debt']) . "). Prioritaskan bayar utang untuk kurangi beban bunga.";
}

if ($state['reputation'] < 60) {
    $suggestions[] = "⭐ Reputasi rendah (" . $state['reputation'] . "/100). Pilih event yang tingkatkan reputasi. Reputasi = lebih banyak pembeli.";
}

if ($state['cash'] < 500000) {
    $suggestions[] = "💸 Kas menipis (Rp " . number_format($state['cash']) . "). Jaga minimal Rp 300rb untuk beli stok besok.";
}

if (empty($suggestions)) {
    $suggestions[] = "✅ Performa bagus! Pertahankan strategi ini.";
}

// Clear session
unset($_SESSION['milestone_day']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hari <?php echo $milestone_day; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .milestone-card {
            background: white;
            border-radius: 25px;
            max-width: 800px;
            width: 100%;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .performance-badge {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 1rem 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 2rem 0;
        }

        .stat-box {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
        }

        .suggestion-item {
            padding: 1rem;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            margin: 0.5rem 0;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="milestone-card">
        <div class="text-center">
            <h1>📊 Laporan Hari ke-<?php echo $milestone_day; ?></h1>
            <p class="text-muted">
                <?php
                if ($milestone_day == 7) {
                    echo "Evaluasi Minggu Pertama - Siklus 7 Hari Selesai";
                } elseif ($milestone_day == 15) {
                    echo "Evaluasi Pertengahan - Siklus 15 Hari Selesai";
                } else {
                    echo "Evaluasi Akhir - Siklus 30 Hari Selesai";
                }
                ?>
            </p>
            <div class="performance-badge bg-<?php echo $performance_class; ?> text-white">
                <?php
                switch ($performance) {
                    case 'sangat_baik':
                        echo '🌟 SANGAT BAIK!';
                        break;
                    case 'baik':
                        echo '✅ BAIK';
                        break;
                    case 'cukup':
                        echo '⚠️ CUKUP';
                        break;
                    default:
                        echo '❌ PERLU PERBAIKAN';
                }
                ?>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <small class="text-muted">Total Profit</small>
                <h5>Rp <?php echo number_format($total_profit); ?></h5>
            </div>
            <div class="stat-box">
                <small class="text-muted">Rata-rata/Hari</small>
                <h5>Rp <?php echo number_format($avg_profit); ?></h5>
            </div>
            <div class="stat-box">
                <small class="text-muted">Tren Grafik</small>
                <h5><?php echo $trend == 'naik' ? '📈 Naik' : '📉 Turun'; ?></h5>
            </div>
        </div>

        <div class="alert alert-info">
            <h5>📈 Analisis Kurva Profit:</h5>
            <p class="mb-0">
                Profit awal: <strong>Rp <?php echo number_format($avg_first); ?></strong><br>
                Profit akhir: <strong>Rp <?php echo number_format($avg_second); ?></strong><br>
                Perubahan:
                <strong><?php echo $trend == 'naik' ? '+' : '-'; ?><?php echo number_format($trend_percentage, 1); ?>%</strong>
            </p>
        </div>

        <div class="alert alert-info mb-4">
            <h6><i class="fas fa-info-circle"></i> Informasi</h6>
            <p class="mb-0">
                <?php if ($milestone_day == 30): ?>
                    Siklus 30 hari selesai! Permainan akan dimulai ulang dari hari 1. Gunakan pelajaran dari siklus ini
                    untuk performa lebih baik!
                <?php else: ?>
                    Kamu bisa lanjut ke hari berikutnya atau mulai ulang untuk coba strategi baru dengan pelajaran yang
                    sudah didapat.
                <?php endif; ?>
            </p>
        </div>

        <h5 class="mt-4 mb-3">💡 Saran Perbaikan:</h5>
        <?php foreach ($suggestions as $suggestion): ?>
            <div class="suggestion-item">
                <?php echo $suggestion; ?>
            </div>
        <?php endforeach; ?>

        <div class="text-center mt-4">
            <?php if ($milestone_day == 30): ?>
                <!-- Day 30: Only restart option -->
                <a href="restart.php" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-redo"></i> OK - Mulai Ulang dari Hari 1
                </a>
            <?php else: ?>
                <!-- Day 7 & 15: Can continue or restart -->
                <a href="dashboard.php" class="btn btn-success btn-lg px-4 me-2">
                    <i class="fas fa-arrow-right"></i> Lanjutkan ke Hari <?php echo $milestone_day + 1; ?>
                </a>
                <a href="restart.php" class="btn btn-outline-danger btn-lg px-4">
                    <i class="fas fa-redo"></i> Mulai Ulang dari Hari 1
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>