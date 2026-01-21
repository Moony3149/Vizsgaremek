<?php
session_start();
include "connect.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT ID, password, approved FROM firm WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $firm = $result->fetch_assoc();

    if ($firm && password_verify($password, $firm["password"])) {
        if ($firm['approved'] == 0) {
            $error = "A fiókod még jóváhagyásra vár az admintól!";
        } else {
            $_SESSION["firm_id"] = $firm["ID"];
            header("Location: firm_dashboard.php");
            exit;
        }
    } else {
        $error = "Hibás email vagy jelszó!";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Céges Belépés</title>
    <style>
        body { background: linear-gradient(135deg, #1e3c72, #2a5298); font-family: 'Segoe UI', sans-serif; height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; }
        .login-box { background: white; padding: 40px; width: 350px; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); }
        h2 { text-align: center; color: #1e3c72; }
        input { width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #1e3c72; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .error { color: #e74c3c; text-align: center; background: #fdf2f2; padding: 8px; border-radius: 5px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Partner Login</h2>
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Céges email" required>
            <input type="password" name="password" placeholder="Jelszó" required>
            <button type="submit">Belépés az irodába</button>
        </form>
    </div>
</body>
</html>