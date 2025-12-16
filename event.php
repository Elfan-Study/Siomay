<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['daily_events'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$events = $_SESSION['daily_events'];
$current_index = $_SESSION['current_event_index'] ?? 0;

// Check if all events done
if ($current_index >= count($events)) {
    header("Location: end_day.php");
    exit();
}

$event = $events[$current_index];

// Get current state for display
$stmt = $pdo->prepare("SELECT * FROM game_state WHERE user_id = ?");
$stmt->execute([$user_id]);
$state = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event - <?php echo htmlspecialchars($event['event_name']); ?></title>
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

        .event-card {
            background: white;
            border-radius: 25px;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .event-header {
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .event-header.positive {
            background: linear-gradient(135deg, #20bf55 0%, #01baef 100%);
        }

        .event-header.negative {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .event-body {
            padding: 2rem;
        }

        .choice-btn {
            padding: 1.5rem;
            border-radius: 15px;
            border: 2px solid #e0e0e0;
            background: #f8f9fa;
            transition: all 0.3s;
            cursor: pointer;
            text-align: left;
        }

        .choice-btn:hover {
            border-color: #667eea;
            background: #f0f2ff;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .impact {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin: 0.2rem;
        }

        .impact.positive {
            background: #d4edda;
            color: #155724;
        }

        .impact.negative {
            background: #f8d7da;
            color: #721c24;
        }

        .impact.neutral {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-bar {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .status-bar small {
            display: inline-block;
            margin-right: 1rem;
        }
    </style>
</head>

<body>
    <div class="event-card">
        <div class="event-header <?php echo $event['event_type']; ?>">
            <h6>Event <?php echo ($current_index + 1); ?> dari <?php echo count($events); ?></h6>
            <h2><?php echo $event['event_type'] == 'positive' ? '✨' : '⚠️'; ?>
                <?php echo htmlspecialchars($event['event_name']); ?></h2>
        </div>
        <div class="event-body">
            <div class="status-bar">
                <small><i class="fas fa-wallet text-primary"></i> Kas: <strong>Rp
                        <?php echo number_format($state['cash']); ?></strong></small>
                <small><i class="fas fa-box text-danger"></i> Stok: <strong><?php echo $state['stock']; ?>
                        porsi</strong></small>
                <small><i class="fas fa-star text-warning"></i> Reputasi:
                    <strong><?php echo $state['reputation']; ?></strong></small>
            </div>

            <p class="lead"><?php echo nl2br(htmlspecialchars($event['event_description'])); ?></p>

            <h5 class="mt-4 mb-3">Pilih Tindakanmu:</h5>

            <form method="POST" action="process_choice.php">
                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                <input type="hidden" name="choice" value="A">
                <button type="submit" class="choice-btn w-100 mb-3">
                    <h6><strong>A.</strong> <?php echo htmlspecialchars($event['choice_a_text']); ?></h6>
                    <div class="mt-2">
                        <?php if ($event['choice_a_cash'] != 0): ?>
                            <span class="impact <?php echo $event['choice_a_cash'] > 0 ? 'positive' : 'negative'; ?>">
                                💰 <?php echo $event['choice_a_cash'] > 0 ? '+' : ''; ?>Rp
                                <?php echo number_format($event['choice_a_cash']); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($event['choice_a_stock'] != 0): ?>
                            <span class="impact <?php echo $event['choice_a_stock'] > 0 ? 'positive' : 'negative'; ?>">
                                📦
                                <?php echo $event['choice_a_stock'] > 0 ? '+' : ''; ?>    <?php echo $event['choice_a_stock']; ?>
                                stok
                            </span>
                        <?php endif; ?>
                        <?php if ($event['choice_a_reputation'] != 0): ?>
                            <span class="impact <?php echo $event['choice_a_reputation'] > 0 ? 'positive' : 'negative'; ?>">
                                ⭐
                                <?php echo $event['choice_a_reputation'] > 0 ? '+' : ''; ?>    <?php echo $event['choice_a_reputation']; ?>
                                reputasi
                            </span>
                        <?php endif; ?>
                        <?php if ($event['choice_a_debt'] != 0): ?>
                            <span class="impact negative">
                                💳 <?php echo $event['choice_a_debt'] > 0 ? '+' : ''; ?>Rp
                                <?php echo number_format($event['choice_a_debt']); ?> utang
                            </span>
                        <?php endif; ?>
                    </div>
                </button>
            </form>

            <form method="POST" action="process_choice.php">
                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                <input type="hidden" name="choice" value="B">
                <button type="submit" class="choice-btn w-100">
                    <h6><strong>B.</strong> <?php echo htmlspecialchars($event['choice_b_text']); ?></h6>
                    <div class="mt-2">
                        <?php if ($event['choice_b_cash'] != 0): ?>
                            <span class="impact <?php echo $event['choice_b_cash'] > 0 ? 'positive' : 'negative'; ?>">
                                💰 <?php echo $event['choice_b_cash'] > 0 ? '+' : ''; ?>Rp
                                <?php echo number_format($event['choice_b_cash']); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($event['choice_b_stock'] != 0): ?>
                            <span class="impact <?php echo $event['choice_b_stock'] > 0 ? 'positive' : 'negative'; ?>">
                                📦
                                <?php echo $event['choice_b_stock'] > 0 ? '+' : ''; ?>    <?php echo $event['choice_b_stock']; ?>
                                stok
                            </span>
                        <?php endif; ?>
                        <?php if ($event['choice_b_reputation'] != 0): ?>
                            <span class="impact <?php echo $event['choice_b_reputation'] > 0 ? 'positive' : 'negative'; ?>">
                                ⭐
                                <?php echo $event['choice_b_reputation'] > 0 ? '+' : ''; ?>    <?php echo $event['choice_b_reputation']; ?>
                                reputasi
                            </span>
                        <?php endif; ?>
                        <?php if ($event['choice_b_debt'] != 0): ?>
                            <span class="impact negative">
                                💳 <?php echo $event['choice_b_debt'] > 0 ? '+' : ''; ?>Rp
                                <?php echo number_format($event['choice_b_debt']); ?> utang
                            </span>
                        <?php endif; ?>
                    </div>
                </button>
            </form>
        </div>
    </div>
</body>

</html>