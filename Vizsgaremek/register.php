<?php
session_start();
include "connect.php"; // Itt jön létre a $conn változó

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $password2 = $_POST["password2"];

    if ($password !== $password2) {
        $error = "A jelszavak nem egyeznek";
    } else {
        // 1. Ellenőrizzük, létezik-e már az email (MySQLi formátummal)
        $stmt = $conn->prepare("SELECT ID FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Ez az email már regisztrálva van";
        } else {
            // 2. Jelszó hashelése
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';

            // 3. Beszúrás az adatbázisba
            $insert = $conn->prepare("INSERT INTO users (email, password, userName, name) VALUES (?, ?, ?, ?)");
            // Mivel az SQL dumpodban a users táblában van userName és name is, adjunk nekik alapértéket
            $tempName = explode("@", $email)[0]; // Az email eleje lesz a név ideiglenesen
            $insert->bind_param("ssss", $email, $hash, $tempName, $tempName);

            if ($insert->execute()) {
                $success = "Sikeres regisztráció! Jelentkezz be.";
            } else {
                $error = "Hiba történt a mentés során: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
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
        .login-box h2 { text-align: center; margin-bottom: 25px; color: #333; }
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
        .login-box button:hover { background: #34495e; }
        .error { color: #e74c3c; text-align: center; margin-bottom: 15px; font-size: 14px; background: #fdf2f2; padding: 8px; border-radius: 5px; }
        .success { color: #27ae60; text-align: center; margin-bottom: 15px; font-size: 14px; background: #f0fff4; padding: 8px; border-radius: 5px; }
        .back-link { display: block; text-align: center; margin-top: 20px; text-decoration: none; color: #2c3e50; font-size: 14px; }
    </style>
</head>
<body>
<div class="login-box">
    <h2>Regisztráció</h2>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email cím" required>
        <input type="password" name="password" placeholder="Jelszó" required>
        <input type="password" name="password2" placeholder="Jelszó újra" required>
        <button type="submit">Regisztráció</button>
    </form>
    <a href="login.php" class="back-link">Vissza a bejelentkezéshez</a>
</div>
</body>
</html>