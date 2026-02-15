<?php
include "connect.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") 
    $f_id = $_POST["f_id"] ?? ''; 
    $name = $_POST["name"] ?? '';
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';
    $password_confirm = $_POST["password_confirm"] ?? '';

    if (empty($f_id) || empty($name) || empty($email) || empty($password)) {
        $error = "Minden mező kitöltése kötelező!";
    } elseif ($password !== $password_confirm) {
        $error = "A két jelszó nem egyezik!";
    } else {
        // Létezik-e már a cég?
        $check = $conn->prepare("SELECT ID FROM firm WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Ez a céges email már regisztrálva van!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Alapértelmezetten approved = 0 (adminnak kell jóváhagynia)
            $stmt = $conn->prepare("INSERT INTO firm (ID, name, email, password, approved) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("isss", $f_id, $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = "Sikeres regisztráció! Az admin jóváhagyása után léphetsz be.";
            } else {
                $error = "Hiba történt a mentés során.";
            }
        }
    }

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Partner Regisztráció</title>
    <style>
        body { background: linear-gradient(135deg, #1e3c72, #2a5298); font-family: 'Segoe UI', sans-serif; height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; }
        .login-box { background: white; padding: 40px; width: 380px; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); }
        h2 { text-align: center; color: #1e3c72; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 8px 0; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #1e3c72; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .error { color: #e74c3c; text-align: center; background: #fdf2f2; padding: 8px; border-radius: 5px; margin-bottom: 10px; font-size: 14px; }
        .success { color: #27ae60; text-align: center; background: #f0fff4; padding: 8px; border-radius: 5px; margin-bottom: 10px; font-size: 14px; }
        .back-link { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #1e3c72; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Partner Regisztráció</h2>
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <?php if($success) echo "<div class='success'>$success</div>"; ?>
        <form method="POST">
            <input type="text" name="f_id" placeholder="Cég azonosítója" required>
            <input type="text" name="name" placeholder="Cégnév" required>
            <input type="email" name="email" placeholder="Céges email" required>
            <input type="password" name="password" placeholder="Jelszó" required>
            <input type="password" name="password_confirm" placeholder="Jelszó megerősítése" required>
            <button type="submit">Regisztráció kérése</button>
        </form>
        <a href="firm_login.php" class="back-link">Vissza a belépéshez</a>
    </div>
</body>
</html>