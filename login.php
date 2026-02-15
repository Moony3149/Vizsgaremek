<?php
session_start();
include "connect.php";

$error = "";
$available_roles = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. SZEREPKÖR KIVÁLASZTÁSA
    if (isset($_POST['select_role'])) {
        $_SESSION['admin'] = $_POST['chosen_role'];
        $id = $_POST['chosen_id'];

        if ($_POST['role_type'] === 'firm') {
            $_SESSION['firm_id'] = $id;
            header("Location: firm_dashboard.php");
        } else {
            $_SESSION['user_id'] = $id;
            header("Location: " . ($_SESSION['admin'] === 'admin' ? "admin.php" : "index.php"));
        }
        exit;
    }

    // 2. ELSŐ BELÉPÉSI KÍSÉRLET
    $email = $_POST["email"];
    $password = $_POST["password"];

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés - TermékVISION</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --dark-blue: #1b263b; 
            --accent-blue: #00d2ff; 
            --light-bg: #f0f2f5;
            --white: #ffffff; 
            --text-main: #333;
            --text-muted: #888;
        }

        /* --- Alapbeállítások --- */
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: var(--light-bg); 
            margin: 0; 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        /* --- Kártya és Elrendezés --- */
        .profile-card {
            background: var(--white);
            width: 100%;
            max-width: 900px;
            min-height: 500px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
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
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .profile-header h2 { 
            margin: 0; 
            font-size: 1.5rem; 
            letter-spacing: 0.5px; 
        }

        .main-layout {
            display: flex;
            padding: 40px;
            gap: 50px;
            flex-grow: 1;
        }

        /* --- Oszlopok --- */
        .side-col { 
            flex: 1; 
            border-right: 1px solid #eee; 
            padding-right: 40px; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        .main-col { 
            flex: 1.2; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
        }

        /* --- Branding elemek --- */
        .brand-section {
            font-size: 3.5rem;
            color: var(--accent-blue);
            margin-bottom: 15px;
        }

        .brand-title {
            color: var(--dark-blue);
            margin: 0;
            font-size: 1.5rem;
        }

        .brand-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .register-prompt {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .register-text {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .register-link {
            color: var(--dark-blue);
            text-decoration: none;
            font-weight: 700;
            transition: 0.2s;
        }

        .register-link:hover {
            color: var(--accent-blue);
        }

        /* --- Form elemek --- */
        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark-blue);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 2px solid var(--accent-blue);
            padding-bottom: 10px;
            width: fit-content;
        }

        .form-group { 
            margin-bottom: 20px; 
        }

        label { 
            display: block; 
            font-size: 0.8rem; 
            font-weight: 700; 
            margin-bottom: 8px; 
            color: var(--text-muted); 
            text-transform: uppercase;
        }

        input {
            width: 100%;
            padding: 14px 20px;
            border: 2px solid #edf2f7;
            border-radius: 12px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: 0.3s ease;
        }

        input:focus { 
            border-color: var(--accent-blue); 
            outline: none; 
            box-shadow: 0 0 0 4px rgba(0,210,255,0.1);
        }

        /* --- Jelszó mező és Mutat/Elrejt ikon --- */
        .password-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .password-header label {
            margin-bottom: 0;
        }

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            padding-right: 45px;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: var(--text-muted);
            transition: 0.3s;
            z-index: 10;
        }

        .toggle-password:hover {
            color: var(--accent-blue);
        }

        .forgot-pass { 
            font-size: 0.85rem; 
            color: var(--accent-blue); 
            text-decoration: none; 
            font-weight: 600; 
            transition: 0.2s; 
        }

        .forgot-pass:hover { 
            color: var(--dark-blue); 
            text-decoration: underline; 
        }

        /* --- Gombok és Opciók --- */
        .btn-main {
            background: var(--dark-blue);
            color: white;
            border: none;
            padding: 16px 45px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-main:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 8px 25px rgba(0,0,0,0.2); 
            background: #2c3e50; 
        }

        .role-option { 
            width: 100%; 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            padding: 15px; 
            margin-bottom: 12px; 
            border: 2px solid #edf2f7; 
            border-radius: 12px; 
            background: #fff; 
            cursor: pointer; 
            transition: all 0.3s ease; 
            text-align: left;
            font-family: inherit;
        }

        .role-option:hover { 
            border-color: var(--accent-blue); 
            transform: translateX(5px); 
            background: #f8fdff;
        }

        .role-option i { 
            font-size: 1.2rem; 
            color: var(--accent-blue); 
        }

        .role-label {
            font-weight: 700;
            color: var(--dark-blue);
        }

        /* --- Üzenetek és Linkek --- */
        .back-link { 
            color: white; 
            text-decoration: none; 
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0.8;
            transition: 0.3s ease;
            font-size: 0.9rem;
        }

        .back-link:hover { opacity: 1; transform: translateX(-5px); }

        .msg-error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 12px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
            text-align: center; 
            font-size: 0.9rem; 
            border: 1px solid #f5c6cb; 
        }

        /* --- Reszponzivitás --- */
        @media (max-width: 800px) {
            .main-layout { 
                flex-direction: column; 
                padding: 30px; 
                gap: 30px; 
            }
            .side-col { 
                border-right: none; 
                border-bottom: 1px solid #eee; 
                padding-right: 0; 
                padding-bottom: 30px; 
            }
            .profile-header { 
                justify-content: center; 
                text-align: center; 
            }
        }

        @media (max-width: 480px) {
            .profile-card { border-radius: 15px; }
            .main-layout { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="profile-card">
    <div class="profile-header">
        <h2><i class="fas fa-sign-in-alt"></i> Bejelentkezés</h2>
        <a href="index.php" class="back-link back-link-style"><i class="fas fa-chevron-left"></i> Vissza</a>
    </div>

    <div class="main-layout">
        <div class="side-col">
            <div class="brand-section">
                <i class="fas fa-images"></i>
            </div>
            <h3 class="brand-title">Termék<strong>VISION</strong></h3>
            <p class="brand-subtitle">Üdvözlünk újra!</p>
            
            <div class="register-prompt">
                <p class="register-text">Nincs még fiókod?</p>
                <a href="register.php" class="register-link">Regisztrálj most!</a>
            </div>
        </div>

        <div class="main-col">
            <?php if (count($available_roles) > 1): ?>
                <div class="section-title"><i class="fas fa-user-check"></i> Válassz profilt</div>
                <p class="role-selection-text">Több fiókot találtunk ehhez az emailhez:</p>
                
                <?php foreach ($available_roles as $role): ?>
                    <form method="POST">
                        <input type="hidden" name="chosen_id" value="<?= $role['id'] ?>">
                        <input type="hidden" name="chosen_role" value="<?= $role['admin'] ?>">
                        <input type="hidden" name="role_type" value="<?= $role['type'] ?>">
                        <button type="submit" name="select_role" class="role-option">
                            <i class="fas <?= $role['icon'] ?>"></i>
                            <span class="role-label"><?= htmlspecialchars($role['label']) ?></span>
                        </button>
                    </form>
                <?php endforeach; ?>

            <?php else: ?>
                
                <?php if ($error): ?> <div class="msg-error"><?= $error ?></div> <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>E-mail cím</label>
                        <input type="email" name="email" placeholder="pelda@email.hu" required>
                    </div>

                    <div class="form-group">
                        <div class="password-header">
                            <label>Jelszó</label>
                            <a href="forgot_password.php" class="forgot-pass">Elfelejtetted?</a>
                        </div>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="login-pass" placeholder="••••••••" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('login-pass', this)"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-main">Bejelentkezés</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>
</body>
</html>