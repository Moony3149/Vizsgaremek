<?php
include "connect.php";

$token = $_GET['token'] ?? '';
$error = "";
$success = "";

// 1. Token ellenőrzése: létezik-e és nem járt-e le?
if (empty($token)) {
    $error = "Érvénytelen kérelem. Nincs token megadva.";
} else {
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    $request = $res->fetch_assoc();

    if (!$request) {
        $error = "A link érvénytelen vagy már lejárt. Kérj egy új jelszóvisszaállító linket!";
    }
}

// 2. Új jelszó mentése
if (isset($_POST['update_password']) && $request) {
    $new_pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if (strlen($new_pass) < 6) {
        $error = "A jelszónak legalább 6 karakterből kell állnia!";
    } elseif ($new_pass === $confirm_pass) {
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $email = $request['email'];

        // Tranzakció: Jelszó frissítése és token törlése
        $conn->begin_transaction();
        try {
            // Jelszó frissítése
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_pass, $email);
            $stmt->execute();

            // Felhasznált token törlése
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();

            $conn->commit();
            $success = "A jelszavad sikeresen megváltozott!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Hiba történt a mentés során. Próbáld újra később!";
        }
    } else {
        $error = "A két jelszó nem egyezik!";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SzuperShop - Új jelszó</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: #f8f9fa; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .reset-container { 
            background: white; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 400px; 
            text-align: center; 
        }
        .logo { 
            font-size: 1.8rem; 
            font-weight: bold; 
            color: #2c3e50; 
            margin-bottom: 20px; 
        }
        .logo i { color: #27ae60; }
        
        input[type="password"] { 
            width: 100%; 
            padding: 12px; 
            margin: 10px 0; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            box-sizing: border-box; 
            outline: none;
        }
        input[type="password"]:focus { border-color: #27ae60; }

        .btn-update { 
            width: 100%; 
            padding: 12px; 
            background: #27ae60; 
            color: white; 
            border: none; 
            border-radius: 8px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.3s;
            margin-top: 10px;
        }
        .btn-update:hover { background: #219150; }

        .alert { 
            padding: 12px; 
            margin-bottom: 15px; 
            border-radius: 8px; 
            font-size: 0.9rem; 
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .login-link { 
            margin-top: 20px; 
            display: inline-block; 
            background: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="reset-container">
    <div class="logo"><i class="fas fa-lock-open"></i> SzuperShop</div>
    <h3>Új jelszó megadása</h3>

    <?php if ($error): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert success"><?= $success ?></div>
        <a href="login.php" class="login-link">Tovább a bejelentkezéshez</a>
    <?php elseif ($request): ?>
        <p style="color: #7f8c8d; font-size: 0.9rem;">Kérjük, add meg az új, biztonságos jelszavadat!</p>
        <form method="POST">
            <input type="password" name="password" placeholder="Új jelszó" required minlength="6">
            <input type="password" name="confirm_password" placeholder="Új jelszó megerősítése" required minlength="6">
            <button type="submit" name="update_password" class="btn-update">Jelszó mentése</button>
        </form>
    <?php else: ?>
        <a href="forgot_password.php" class="login-link">Új link igénylése</a>
    <?php endif; ?>
</div>

</body>
<script>
function togglePassword(inputId) {
    const input = document.querySelector('input[name="' + inputId + '"]');
    // Itt egy kis trükk: ha a típus 'password', átváltjuk 'text'-re
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}
<div style="position: relative;">
    <input type="password" name="password" placeholder="Új jelszó" required minlength="6">
    <i class="fas fa-eye" onclick="togglePassword('password')" 
       style="position: absolute; right: 10px; top: 20px; cursor: pointer; color: #7f8c8d;"></i>
</div>
</script>
</html>