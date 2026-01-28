<?php
include "connect.php";
$msg = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = $_POST['reg_type']; // 'user' vagy 'firm'
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['name'];

    // Ellenőrizzük, létezik-e már az email
    $check_user = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_user->bind_param("s", $email);
    $check_user->execute();
    if ($check_user->get_result()->num_rows > 0) { $error = "Ez az email cím már foglalt!"; }

    if (!$error) {
        if ($type === 'user') {
            $username = $_POST['username'];
            // A role alapértelmezetten 'user'
            $stmt = $conn->prepare("INSERT INTO users (name, userName, email, password, admin) VALUES (?, ?, ?, ?, 'user')");
            $stmt->bind_param("ssss", $name, $username, $email, $pass);
        } else {
            $brand = $_POST['brand_name'];
            // A cég alapból jóváhagyásra vár (approved = 0)
            $stmt = $conn->prepare("INSERT INTO firm (brand_name, worker_name, email, password, approved) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("ssss", $brand, $name, $email, $pass);
        }

        if ($stmt->execute()) {
            $msg = "Sikeres regisztráció! " . ($type === 'firm' ? "A cég jóváhagyásra vár." : "Most már beléphetsz.");
        } else {
            $error = "Hiba történt a mentés során!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció - SzuperShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #2c3e50, #4ca1af); font-family: 'Segoe UI', sans-serif; height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; }
        .reg-box { background: white; padding: 35px; width: 400px; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); position: relative; }
        .back-home { position: absolute; top: 15px; left: 15px; text-decoration: none; color: #7f8c8d; font-size: 0.8rem; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        
        /* Típus választó gombok */
        .type-select { display: flex; gap: 10px; margin-bottom: 20px; }
        .type-select label { flex: 1; text-align: center; padding: 10px; border: 1px solid #ddd; border-radius: 8px; cursor: pointer; transition: 0.3s; font-weight: bold; font-size: 0.9rem; }
        input[type="radio"] { display: none; }
        input[type="radio"]:checked + label { background: #3498db; color: white; border-color: #3498db; }

        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-reg { width: 100%; padding: 12px; background: #27ae60; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .msg { padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 10px; font-size: 0.9rem; }
        .success { background: #e8f5e9; color: #2e7d32; }
        .error-msg { background: #ffebee; color: #c62828; }
        .footer { text-align: center; margin-top: 15px; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="reg-box">
    <a href="index.php" class="back-home"><i class="fas fa-arrow-left"></i> Főoldal</a>
    <h2>Regisztráció</h2>

    <?php if ($msg): ?> <div class="msg success"><?= $msg ?></div> <?php endif; ?>
    <?php if ($error): ?> <div class="msg error-msg"><?= $error ?></div> <?php endif; ?>

    <form method="POST">
        <div class="type-select">
            <input type="radio" name="reg_type" value="user" id="r_u" checked onclick="toggleFields()">
            <label for="r_u"><i class="fas fa-user"></i> Vásárló</label>
            
            <input type="radio" name="reg_type" value="firm" id="r_f" onclick="toggleFields()">
            <label for="r_f"><i class="fas fa-store"></i> Cég</label>
        </div>

        <input type="text" name="name" placeholder="Teljes név" required>
        <input type="email" name="email" placeholder="Email cím" required>
        <input type="password" name="password" placeholder="Jelszó" required>
        
        <div id="u_fields">
            <input type="text" name="username" placeholder="Felhasználónév">
        </div>
        <div id="f_fields" style="display:none;">
            <input type="text" name="brand_name" placeholder="Cégnév / Márka">
        </div>

        <button type="submit" class="btn-reg">Fiók létrehozása</button>
    </form>

    <div class="footer">
        Van már fiókod? <a href="login.php" style="color:#3498db; text-decoration:none; font-weight:bold;">Lépj be!</a>
    </div>
</div>

<script>
    function toggleFields() {
        const isUser = document.getElementById('r_u').checked;
        document.getElementById('u_fields').style.display = isUser ? 'block' : 'none';
        document.getElementById('f_fields').style.display = isUser ? 'none' : 'block';
    }
</script>

</body>
</html>