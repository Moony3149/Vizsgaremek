<?php
session_start();
include "connect.php";

$error = "";
$available_roles = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. SZEREPKÖR KIVÁLASZTÁSA (ha több profil van)
    if (isset($_POST['select_role'])) {
        $_SESSION['admin'] = $_POST['chosen_role']; // Itt tároljuk le: 'admin' vagy 'user'
        $id = $_POST['chosen_id'];

        if ($_POST['role_type'] === 'firm') {
            $_SESSION['firm_id'] = $id;
            header("Location: firm_dashboard.php");
        } else {
            $_SESSION['user_id'] = $id;
            // Ha az érték 'admin', az admin felületre dob, egyébként a főoldalra
            header("Location: " . ($_SESSION['admin'] === 'admin' ? "admin.php" : "index.php"));
        }
        exit;
    }

    // 2. ELSŐ BELÉPÉSI KÍSÉRLET
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Felhasználók és adminok keresése (az admin oszlop alapján)
    $stmt = $conn->prepare("SELECT id, password, admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $available_roles[] = [
                'id' => $row['id'], 
                'admin' => $row['admin'], 
                'type' => 'user', 
                'label' => ($row['admin'] == 'admin' ? 'Adminisztrátor' : 'Vásárlói fiók'),
                'icon' => ($row['admin'] == 'admin' ? 'fa-user-shield' : 'fa-user')
            ];
        }
    }

    // Cégek keresése
    $stmt = $conn->prepare("SELECT ID, password, brand_name, approved FROM firm WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            if ($row['approved'] == 1) {
                $available_roles[] = [
                    'id' => $row['ID'], 
                    'admin' => 'firm', 
                    'type' => 'firm', 
                    'label' => 'Cég: ' . $row['brand_name'],
                    'icon' => 'fa-store'
                ];
            } else {
                $error = "A céges profil jóváhagyásra vár!";
            }
        }
    }

    // Automatikus beléptetés, ha csak 1 találat van
    if (count($available_roles) === 1 && empty($error)) {
        $p = $available_roles[0];
        $_SESSION['admin'] = $p['admin'];
        if ($p['type'] === 'firm') { 
            $_SESSION['firm_id'] = $p['id']; 
            header("Location: firm_dashboard.php"); 
        } else {
            $_SESSION['user_id'] = $p['id'];
            header("Location: " . ($p['admin'] === 'admin' ? "admin.php" : "index.php"));
        }
        exit;
    } elseif (count($available_roles) === 0 && empty($error)) {
        $error = "Hibás email vagy jelszó!";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Bejelentkezés - SzuperShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Teljesen megegyezik a regisztrációs oldaladdal */
        body { background: linear-gradient(135deg, #2c3e50, #4ca1af); font-family: 'Segoe UI', sans-serif; height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; }
        .login-box { background: white; padding: 35px; width: 400px; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); position: relative; }
        .back-home { position: absolute; top: 15px; left: 15px; text-decoration: none; color: #7f8c8d; font-size: 0.8rem; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        
        input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        
        .btn-main { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px; transition: 0.3s; }
        .btn-main:hover { background: #2980b9; }

        /* Profilválasztó stílus */
        .role-option { 
            width: 100%; display: flex; align-items: center; gap: 15px; padding: 15px; 
            margin-bottom: 10px; border: 1px solid #ddd; border-radius: 8px; 
            background: #fdfdfd; cursor: pointer; transition: 0.3s; text-align: left;
        }
        .role-option:hover { background: #f1f1f1; border-color: #3498db; }
        .role-option i { font-size: 1.2rem; color: #3498db; width: 25px; text-align: center; }

        .msg-error { background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px; font-size: 0.9rem; }
        .footer { text-align: center; margin-top: 15px; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="login-box">
    <a href="index.php" class="back-home"><i class="fas fa-arrow-left"></i> Főoldal</a>
    
    <?php if (count($available_roles) > 1): ?>
        <h2>Válassz profilt!</h2>
        <?php foreach ($available_roles as $role): ?>
            <form method="POST">
                <input type="hidden" name="chosen_id" value="<?= $role['id'] ?>">
                <input type="hidden" name="chosen_role" value="<?= $role['admin'] ?>">
                <input type="hidden" name="role_type" value="<?= $role['type'] ?>">
                <button type="submit" name="select_role" class="role-option">
                    <i class="fas <?= $role['icon'] ?>"></i>
                    <span style="font-weight: bold; color: #2c3e50;"><?= htmlspecialchars($role['label']) ?></span>
                </button>
            </form>
        <?php endforeach; ?>

    <?php else: ?>
        <h2>Belépés</h2>
        <?php if ($error): ?> <div class="msg-error"><?= $error ?></div> <?php endif; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Email cím" required>
            <input type="password" name="password" placeholder="Jelszó" required>
            <button type="submit" class="btn-main">Bejelentkezés</button>
        </form>

        <div class="footer">
            Nincs még fiókod? <a href="register.php" style="color:#27ae60; text-decoration:none; font-weight:bold;">Regisztráció</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>