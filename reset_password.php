<?php
include "connect.php";

$token = $_GET['token'] ?? '';
$error = "";
$success = "";
$request = null;

// 1. Token ellenőrzése
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

        $conn->begin_transaction();
        try {
            // Jelszó frissítése a USERS táblában
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_pass, $email);
            $stmt->execute();

            // Használt token törlése
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
        :root { 
            --dark-blue: #1b263b; 
            --accent-blue: #00d2ff; 
            --light-bg: #f0f2f5;
            --white: #ffffff; 
            --text-main: #333;
            --text-muted: #888;
            --success-green: #27ae60;
        }

        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: var(--light-bg); 
            margin: 0; height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }

        .profile-card {
            background: var(--white);
            width: 900px;
            min-height: 500px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            display: flex; flex-direction: column;
            overflow: hidden;
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .profile-header {
            background: var(--dark-blue);
            color: white;
            padding: 30px 45px;
            display: flex; justify-content: space-between; align-items: center;
        }

        .main-layout { display: flex; padding: 40px; gap: 50px; flex-grow: 1; }

        .side-col { 
            flex: 1; border-right: 1px solid #eee; padding-right: 40px; 
            display: flex; flex-direction: column; justify-content: center; text-align: center;
        }

        .main-col { flex: 1.2; display: flex; flex-direction: column; justify-content: center; }

        .section-title {
            font-size: 1.2rem; font-weight: 700; color: var(--dark-blue);
            margin-bottom: 25px; display: flex; align-items: center; gap: 12px;
            border-bottom: 2px solid var(--accent-blue); padding-bottom: 10px; width: fit-content;
        }

        .form-group { margin-bottom: 20px; position: relative; }
        
        label { display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; color: var(--text-muted); text-transform: uppercase; }

        input {
            width: 100%; padding: 14px 45px 14px 20px;
            border: 2px solid #edf2f7; border-radius: 12px;
            box-sizing: border-box; font-size: 1rem; transition: 0.3s;
        }

        input:focus { border-color: var(--accent-blue); outline: none; box-shadow: 0 0 0 4px rgba(0,210,255,0.1); }

        .eye-icon { position: absolute; right: 15px; top: 38px; cursor: pointer; color: var(--text-muted); transition: 0.3s; }
        .eye-icon:hover { color: var(--dark-blue); }

        .btn-main {
            background: var(--dark-blue); color: white; border: none;
            padding: 16px 45px; border-radius: 15px; cursor: pointer;
            font-weight: 700; font-size: 1rem; transition: 0.3s; width: 100%;
        }

        .btn-main:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.2); background: #2c3e50; }

        .alert { padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 600; text-align: center; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .login-btn-link {
            display: inline-block; width: 100%; text-align: center;
            background: var(--success-green); color: white;
            padding: 16px 0; border-radius: 15px; text-decoration: none;
            font-weight: 700; transition: 0.3s;
        }
        .login-btn-link:hover { opacity: 0.9; transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="profile-card">
    <div class="profile-header">
        <h2><i class="fas fa-user-shield"></i> Biztonsági központ</h2>
        <a href="login.php" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.8;"><i class="fas fa-chevron-left"></i> Bejelentkezés</a>
    </div>

    <div class="main-layout">
        <div class="side-col">
            <div style="font-size: 4rem; color: var(--accent-blue); margin-bottom: 20px;">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3 style="color: var(--dark-blue); margin: 0;">Fiók védelme</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 10px;">
                Válassz olyan jelszót, amit máshol nem használsz, hogy fiókod maximális biztonságban legyen.
            </p>
        </div>

        <div class="main-col">
            <div class="section-title"><i class="fas fa-key"></i> Új jelszó megadása</div>

            <?php if ($error): ?>
                <div class="alert error"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
                <a href="login.php" class="login-btn-link">Tovább a bejelentkezéshez</a>
            <?php elseif ($request): ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Új jelszó</label>
                        <input type="password" name="password" id="p1" required minlength="8" placeholder="Minimum 8 karakter">
                        <i class="fas fa-eye eye-icon" onclick="togglePass('p1')"></i>
                    </div>

                    <div class="form-group">
                        <label>Új jelszó megerősítése</label>
                        <input type="password" name="confirm_password" id="p2" required minlength="8" placeholder="Ismételd meg">
                        <i class="fas fa-eye eye-icon" onclick="togglePass('p2')"></i>
                    </div>

                    <button type="submit" name="update_password" class="btn-main">Jelszó frissítése</button>
                </form>
            <?php else: ?>
                <div style="text-align: center;">
                    <p style="color: var(--text-muted);">Sajnos ez a művelet nem hajtható végre.</p>
                    <a href="forgot_password.php" class="btn-main" style="text-decoration: none; display: block;">Új link igénylése</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function togglePass(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling;
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

</body>
</html>