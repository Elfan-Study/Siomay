<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get state
$stmt = $pdo->prepare("SELECT * FROM game_state WHERE user_id = ?");
$stmt->execute([$user_id]);
$state = $stmt->fetch();

$reason = $state['game_over_reason'] ?? 'unknown';

// Get statistics
$stmt = $pdo->prepare("SELECT SUM(profit) as total_profit, COUNT(*) as days_played FROM daily_summary WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Educational feedback based on reason
$title = "GAME OVER";
$explanation = "";
$lesson = "";

switch ($reason) {
    case 'bankrupt':
        $title = "💸 BANGKRUT - Kehabisan Modal";
        $explanation = "Uang kas habis dan tidak bisa beli bahan baku untuk besok. Dalam bisnis, kas adalah nyawa. Meskipun punya stok banyak atau piutang, kalau tidak ada uang tunai, usaha tidak bisa jalan.";
        $lesson = "Pelajaran: Jaga cash flow! Jangan semua untung langsung diambil untuk keperluan pribadi. Sisakan modal untuk operasional.";
        break;

    case 'debt':
        $title = "💳 TERJEBAK UTANG";
        $explanation = "Utangmu sudah Rp " . number_format($state['debt']) . ", terlalu besar! Setiap hari untung habis untuk bayar bunga. Ini namanya debt trap (jebakan utang).";
        $lesson = "Pelajaran: Utang boleh, tapi harus produktif (untuk tambah modal usaha). Jangan berutang untuk konsumsi pribadi atau gaya hidup.";
        break;

    case 'reputation':
        $title = "⭐ REPUTASI HANCUR";
        $explanation = "Terlalu sering mengecewakan pelanggan. Reputasimu jatuh dan pelanggan pindah ke kompetitor. Reputasi adalah aset tak terlihat tapi sangat berharga.";
        $lesson = "Pelajaran: Jaga kualitas dan kepercayaan pelanggan. Sekali reputasi rusak, susah membangun lagi.";
        break;

    case 'success_good':
        $title = "🎉 SELAMAT! USAHA SUKSES";
        $explanation = "Kamu berhasil menjalankan usaha selama 30 hari dengan grafik profit yang bagus! Rata-rata profit 5 hari terakhir di atas Rp 100.000. Ini menunjukkan usahamu berkembang dengan baik.";
        $lesson = "Pelajaran: Konsistensi dan keputusan bijak membuat usaha tumbuh. Pertahankan strategi yang sudah berhasil!";
        break;

    case 'success_bad':
        $title = "⚠️ USAHA BERTAHAN - Tapi Perlu Perbaikan";
        $explanation = "Kamu berhasil bertahan 30 hari, tapi grafik profit menurun di akhir. Rata-rata profit 5 hari terakhir kurang dari Rp 100.000. Usaha masih jalan tapi perlu strategi baru.";
        $lesson = "Pelajaran: Bertahan saja tidak cukup. Usaha harus terus berkembang. Evaluasi keputusan dan coba strategi berbeda!";
        break;

    case 'no_stock_money':
        $title = "💸 BANGKRUT - Tidak Bisa Beli Stok";
        $explanation = "Uang kas tidak cukup untuk beli stok harian (100 porsi @ Rp 3.000 = Rp 300.000). Tanpa stok, usaha tidak bisa jalan.";
        $lesson = "Pelajaran: Selalu sisakan uang untuk beli stok besok! Jangan habiskan semua uang untuk hal lain. Modal kerja sangat penting.";
        break;

    default:
        $explanation = "Usahamu tidak bisa dilanjutkan.";
        $lesson = "Coba lagi dan buat keputusan yang lebih bijak!";
}

// Analyze mistakes and generate suggestions
$suggestions = [];

// Get recent summaries for analysis
$stmt = $pdo->prepare("SELECT * FROM daily_summary WHERE user_id = ? ORDER BY day DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_summaries = $stmt->fetchAll();

if (count($recent_summaries) > 0) {
    $recent_profits = array_column($recent_summaries, 'profit');
    $avg_profit = array_sum($recent_profits) / count($recent_profits);

    if ($avg_profit < 50000) {
        $suggestions[] = "💰 Profit terlalu rendah. Tingkatkan reputasi untuk dapat lebih banyak pembeli.";
    }

    // Check trend
    if (count($recent_summaries) >= 3) {
        $first_profit = $recent_summaries[count($recent_summaries) - 1]['profit'];
        $last_profit = $recent_summaries[0]['profit'];
        if ($last_profit < $first_profit) {
            $suggestions[] = "📉 Profit menurun. Evaluasi keputusan event - hindari ambil uang pribadi terlalu sering.";
        }
    }
}

if ($state['stock'] > 300) {
    $suggestions[] = "📦 Stok terlalu banyak mengunci modal. Fokus jualan dulu, jangan belanja berlebihan.";
}

if ($state['debt'] > 2000000) {
    $suggestions[] = "💳 Utang terlalu tinggi. Prioritaskan bayar utang untuk kurangi beban bunga.";
}

if ($state['reputation'] < 60) {
    $suggestions[] = "⭐ Reputasi rendah mengurangi pembeli. Pilih event yang tingkatkan reputasi.";
}

if ($personal_withdrawal > 1000000) {
    $suggestions[] = "💸 Terlalu banyak ambil uang pribadi (Rp " . number_format($personal_withdrawal) . "). Pisahkan uang pribadi dan usaha.";
}

// Specific suggestions based on game over reason
if ($reason == 'no_stock_money') {
    $suggestions[] = "⚠️ Selalu sisakan minimal Rp 300rb untuk beli stok besok. Jangan habiskan semua uang.";
}

if (empty($suggestions)) {
    $suggestions[] = "Coba strategi berbeda di permainan berikutnya!";
}

// Analyze mistakes
$personal_withdrawal = $state['personal_withdrawal'];
$debt = $state['debt'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Over</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #e74c3c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .gameover-card {
            background: white;
            border-radius: 25px;
            max-width: 800px;
            width: 100%;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .title {
            color: #e74c3c;
            text-align: center;
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 2rem;
        }

        .explanation-box {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 10px;
        }

        .lesson-box {
            background: #d1ecf1;
            border-left: 5px solid #0c5460;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin: 2rem 0;
        }

        .stat-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="gameover-card">
        <div class="title">GAME OVER</div>

        <h3 class="text-center mb-4"><?php echo $title; ?></h3>

        <div class="explanation-box">
            <h5>📖 Apa yang Terjadi?</h5>
            <p class="mb-0"><?php echo $explanation; ?></p>
        </div>

        <div class="lesson-box">
            <h5>💡 <?php echo $lesson; ?></h5>
        </div>

        <h5 class="mt-4 mb-3">📊 Analisis Usahamu:</h5>
        <div class="stats-grid">
            <div class="stat-item">
                <small class="text-muted">Hari Bertahan</small>
                <h4><?php echo $state['current_day'] - 1; ?> hari</h4>
            </div>
            <div class="stat-item">
                <small class="text-muted">Total Profit</small>
                <h4>Rp <?php echo number_format($stats['total_profit'] ?? 0); ?></h4>
            </div>
            <div class="stat-item">
                <small class="text-muted">Uang Pribadi Diambil</small>
                <h4 class="text-danger">Rp <?php echo number_format($personal_withdrawal); ?></h4>
            </div>
            <div class="stat-item">
                <small class="text-muted">Total Utang</small>
                <h4 class="text-warning">Rp <?php echo number_format($debt); ?></h4>
            </div>
        </div>

        <?php if ($personal_withdrawal > 500000): ?>
            <div class="alert alert-danger">
                <strong>⚠️ Kesalahan Utama:</strong> Kamu terlalu banyak mengambil uang usaha untuk keperluan pribadi (Rp
                <?php echo number_format($personal_withdrawal); ?>). Ini membuat modal usaha terkuras!
            </div>
        <?php endif; ?>

        <?php if ($debt > 2000000): ?>
            <div class="alert alert-warning">
                <strong>⚠️ Peringatan:</strong> Utangmu terlalu besar (Rp <?php echo number_format($debt); ?>). Utang harus
                dikelola dengan hati-hati.
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-home"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</body>

</html>