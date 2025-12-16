<?php
require_once 'config.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            $message = "Username sudah dipakai!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $hashed_password]);
                $user_id = $pdo->lastInsertId();

                // Initialize game state with new starting values
                $stmt = $pdo->prepare("INSERT INTO game_state (user_id, cash, stock, reputation, current_day) VALUES (?, 500000, 100, 50, 1)");
                $stmt->execute([$user_id]);

                $pdo->commit();

                header("Location: index.php?registered=1");
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Pendaftaran gagal: " . $e->getMessage();
            }
        }
    } else {
        $message = "Isi semua field!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Simulator Warung Siomay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #20bf55 0%, #01baef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }

        .card-header {
            background: linear-gradient(135deg, #20bf55 0%, #01baef 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .card-body {
            padding: 2rem;
        }

        .btn-success {
            background: linear-gradient(135deg, #20bf55 0%, #01baef 100%);
            border: none;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="card-header">
            <h2>🏪 Mulai Usaha Siomay</h2>
            <p class="mb-0">Daftar & Dapat Modal Rp 500rb + 100 Porsi</p>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg">Mulai Usaha</button>
                    <a href="index.php" class="btn btn-outline-secondary">Sudah Punya Akun</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>