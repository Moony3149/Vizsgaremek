<?php
session_start();
include "connect.php"; // Itt jön létre a $conn változó

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // 1. Felhasználó lekérése MySQLi bind_param használatával
    $stmt = $conn->prepare("SELECT ID, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // 2. Jelszó ellenőrzése
    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["ID"];
        // Ha nincs role oszlop a tábládban, adjunk neki egy alapértéket
        $_SESSION["role"] = isset($user["role"]) ? $user["role"] : "user";
        
        header("Location: index.php");
        exit;
    } else {
        $error = "Hibás email vagy jelszó";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Bejelentkezés</title>
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50, #4ca1af);
            font-family: 'Segoe UI', Arial, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .login-box {
            background: #ffffff;
            padding: 40px;
            width: 350px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .login-box input {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-sizing: border-box;
            font-size: 14px;
        }

        .login-box button {
            width: 100%;
            padding: 12px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 10px;
        }

        .login-box button:hover {
            background: #34495e;
        }

        .register-btn {
            margin-top: 15px;
            background: #ecf0f1 !important;
            color: #2c3e50 !important;
            border: 1px solid #bdc3c7 !important;
        }

        .register-btn:hover {
            background: #dfe6e9 !important;
        }

        .error {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
            background: #fdf2f2;
            padding: 8px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Bejelentkezés</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email cím" required>
        <input type="password" name="password" placeholder="Jelszó" required>
        <button type="submit">Belépés</button>
    </form>

    <form action="register.php" method="GET">
        <button type="submit" class="register-btn">Regisztráció</button>
    </form>
</div>

</body>
</html>