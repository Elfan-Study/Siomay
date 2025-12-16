<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Username atau password salah!";
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
    <title>Login - Simulator Warung Siomay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Floating decorations */
        .decoration {
            position: absolute;
            opacity: 0.3;
            animation: float 6s ease-in-out infinite;
        }

        .decoration:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .decoration:nth-child(2) {
            top: 70%;
            left: 80%;
            animation-delay: 2s;
        }

        .decoration:nth-child(3) {
            top: 30%;
            right: 15%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .login-container {
            background: white;
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            padding: 3rem;
            max-width: 450px;
            width: 90%;
            position: relative;
            z-index: 10;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-section h1 {
            font-family: 'Fredoka One', cursive;
            color: #FF6B6B;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logo-section p {
            color: #666;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .form-control {
            border-radius: 15px;
            border: 2px solid #e0e0e0;
            padding: 0.8rem 1.2rem;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #FF6B6B;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #FF6B6B 0%, #F38181 100%);
            border: none;
            border-radius: 50px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            width: 100%;
            margin-top: 1rem;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6);
        }

        .btn-register {
            background: white;
            border: 2px solid #4ECDC4;
            border-radius: 50px;
            padding: 0.8rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            color: #4ECDC4;
            width: 100%;
            margin-top: 0.5rem;
            transition: all 0.3s;
        }

        .btn-register:hover {
            background: #4ECDC4;
            color: white;
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 15px;
            border: none;
        }

        .icon-input {
            position: relative;
        }

        .icon-input i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .icon-input .form-control {
            padding-left: 45px;
        }
    </style>
</head>

<body>
    <!-- Floating decorations -->
    <div class="decoration" style="font-size: 3rem;">🍢</div>
    <div class="decoration" style="font-size: 2.5rem;">💰</div>
    <div class="decoration" style="font-size: 2rem;">🏪</div>

    <div class="login-container">
        <div class="logo-section">
            <h1>🍢 SIOMAY BOSS!</h1>
            <p>Mulai Bisnismu Sekarang</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3 icon-input">
                <i class="fas fa-user"></i>
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3 icon-input">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
            <a href="register.php" class="btn btn-register">
                <i class="fas fa-user-plus"></i> Daftar Baru
            </a>
        </form>
    </div>
</body>

</html>