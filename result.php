<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_result'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$result = $_SESSION['last_result'];

// Get updated state
$stmt = $pdo->prepare("SELECT * FROM game_state WHERE user_id = ?");
$stmt->execute([$user_id]);
$state = $stmt->fetch();

$events_remaining = count($_SESSION['daily_events']) - $_SESSION['current_event_index'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Keputusan</title>
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

        .result-card {
            background: white;
            border-radius: 25px;
            max-width: 600px;
            width: 100%;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin: 2rem 0;
        }

        .status-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
        }

        .btn-next {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.1rem;
        }
    </style>
</head>

<body>
    <div class="result-card">
        <h2 class="mb-4">📊 Hasil Keputusan</h2>
        <p class="lead"><?php echo htmlspecialchars($result); ?></p>

        <div class="status-grid">
            <div class="status-item">
                <small class="text-muted">Kas</small>
                <h5>Rp <?php echo number_format($state['cash']); ?></h5>
            </div>
            <div class="status-item">
                <small class="text-muted">Stok</small>
                <h5><?php echo $state['stock']; ?> porsi</h5>
            </div>
            <div class="status-item">
                <small class="text-muted">Reputasi</small>
                <h5><?php echo $state['reputation']; ?>/100</h5>
            </div>
            <div class="status-item">
                <small class="text-muted">Utang</small>
                <h5>Rp <?php echo number_format($state['debt']); ?></h5>
            </div>
        </div>

        <?php if ($events_remaining > 0): ?>
            <p class="text-muted">Masih ada <?php echo $events_remaining; ?> event lagi hari ini</p>
            <a href="event.php" class="btn btn-next">Lanjut <i class="fas fa-arrow-right"></i></a>
        <?php else: ?>
            <p class="text-muted">Semua event selesai. Saatnya tutup buku!</p>
            <a href="end_day.php" class="btn btn-next">Tutup Buku <i class="fas fa-moon"></i></a>
        <?php endif; ?>
    </div>
</body>

</html>