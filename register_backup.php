<?php
include "connect.php";
$msg = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = $_POST['reg_type']; 
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $pass_confirm = $_POST['password_confirm'];
    $name = $_POST['name'];
    $firmID = $_POST['ID'] ?? null;

    $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";

    if ($pass !== $pass_confirm) {
        $error = "A két jelszó nem egyezik!";
    } elseif (!preg_match($pattern, $pass)) {
        $error = "A jelszó nem felel meg a biztonsági követelményeknek!";
    }

    if (!$error){
        $check_email_u = $conn->prepare("SELECT id FROM users WHERE email = ? UNION SELECT ID FROM firm WHERE email = ?");
        $check_email_u->bind_param("ss", $email, $email);
        $check_email_u->execute();
        if ($check_email_u->get_result()->num_rows > 0) { 
            $error = "Ez az email cím már foglalt!"; 
        }
    }

    if (!$error) {
        if ($type === 'user') {
            $username = $_POST['username'];
            $check_name = $conn->prepare("SELECT id FROM users WHERE userName = ?");
            $check_name->bind_param("s", $username);
            $check_name->execute();
            if ($check_name->get_result()->num_rows > 0) {
                $error = "Ez a felhasználónév már foglalt!";
            }
        } elseif ($type === 'firm' && strlen((string)$firmID) !== 10) {
            $error = "A cég azonosítónak pontosan 10 számjegyből kell állnia!";
        }
    }

    if (!$error) {
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        if ($type === 'user') {
            $stmt = $conn->prepare("INSERT INTO users (name, userName, email, password, admin) VALUES (?, ?, ?, ?, 'user')");
            $stmt->bind_param("ssss", $name, $username, $email, $hashed_pass);
        } else {
           $brand = $_POST['brand_name'];
            // Az ID-t kihagyjuk az oszloplistából, így az AUTO_INCREMENT fog dolgozni!
            $stmt = $conn->prepare("INSERT INTO firm (company_reg_number, brand_name, worker_name, email, password, approved) VALUES (?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("sssss", $firmID, $brand, $name, $email, $hashed_pass);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció - TermékVISION</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --dark-blue: #1b263b; 
            --accent-blue: #00d2ff; 
            --light-bg: #f0f2f5;
            --white: #ffffff; 
            --success-green: #2ecc71; 
            --danger-red: #ff4757;
            --text-main: #333;
            --text-muted: #888;
        }

        /* --- Alapok --- */
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

        /* --- Kártya elrendezés --- */
        .profile-card {
            background: var(--white);
            width: 100%;
            max-width: 1100px;
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

        .profile-header h2 { margin: 0; font-size: 1.6rem; letter-spacing: 0.5px; }

        .main-layout {
            display: flex;
            padding: 40px;
            gap: 50px;
            flex-grow: 1;
        }

        .brand-title {
            color: var(--dark-blue);
            margin: 20px 0;
            font-size: 1.8rem;
            text-align: center;
            width: 100%;
        }

        .side-col { 
            flex: 1; 
            border-right: 1px solid #eee; 
            padding-right: 40px; 
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }

        .type-select, .form-group, .login-prompt {
            width: 100%;
        }

        .main-col { flex: 1.5; }

        /* --- Form elemek --- */
        .section-title {
            font-size: 1.1rem;
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

        .mt-30 { margin-top: 30px; }

        .form-group { margin-bottom: 20px; }

        .flex-1 { flex: 1; }

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

        /* --- Típus választó (Radio buttons) --- */
        .type-select { display: flex; gap: 10px; margin-bottom: 30px; }

        .type-select label { 
            flex: 1; 
            text-align: center; 
            padding: 12px; 
            border: 2px solid #edf2f7; 
            border-radius: 12px; 
            cursor: pointer; 
            transition: 0.3s; 
            font-weight: bold; 
            text-transform: none; 
            font-size: 0.9rem;
            color: var(--text-main);
            margin-bottom: 0;
        }

        input[type="radio"] { display: none; }

        input[type="radio"]:checked + label { 
            background: var(--accent-blue); 
            color: var(--dark-blue); 
            border-color: var(--accent-blue); 
        }

        /* --- Jelszó mezők --- */
        .password-grid {
            display: flex;
            gap: 20px;
        }

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .password-wrapper input {
            padding-right: 45px !important;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: var(--text-muted);
            font-size: 1.1rem;
            transition: 0.2s ease;
            z-index: 5;
        }

        .toggle-password:hover { color: var(--accent-blue); }

        .password-hint {
            color: var(--text-muted);
            display: block;
            margin-top: -10px;
            margin-bottom: 10px;
            font-size: 0.8rem;
        }

        /* --- Footer és Gombok --- */
        .btn-container {
            padding: 30px 40px;
            background: var(--dark-blue);
            text-align: right;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .btn-reg {
            background: var(--accent-blue);
            color: var(--dark-blue);
            border: none;
            padding: 16px 45px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,210,255,0.2);
        }

        .btn-reg:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 8px 25px rgba(0,210,255,0.4); 
            background: #ffffff;
        }

        /* --- Segédszövegek --- */
        .login-prompt {
            margin-top: 20px;
            color: var(--text-muted);
            font-size: 0.9rem;
            text-align: center;
        }

        .login-link {
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 700;
        }

        .back-link { 
            color: white; 
            text-decoration: none; 
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0.8;
            transition: 0.3s ease;
        }

        .back-link:hover { opacity: 1; transform: translateX(-5px); }

        /* --- Üzenetek --- */
        .msg { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; text-align: center; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-msg { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* --- Reszponzivitás --- */
        @media (max-width: 900px) {
            .main-layout { flex-direction: column; padding: 30px; gap: 30px; }
            .side-col { border-right: none; border-bottom: 1px solid #eee; padding-right: 0; padding-bottom: 30px; }
        }

        @media (max-width: 600px) {
            .password-grid { flex-direction: column; gap: 0; }
            .btn-reg { width: 100%; }
            .profile-header { justify-content: center; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="profile-card">
        <div class="profile-header">
            <h2><i class="fas fa-user-plus"></i> Regisztráció</h2>
            <a href="index.php" class="back-link"><i class="fas fa-chevron-left"></i> Vissza a főoldalra</a>
        </div>

        <form method="POST" style="display: contents;"> <div class="main-layout">
                
                <div class="side-col">
                    <div class="section-title"><i class="fas fa-users"></i> Ki vagy te?</div>
                    <h3 class="brand-title">Termék<strong>VISION</strong></h3>
                    <div class="type-select">
                        <input type="radio" name="reg_type" value="user" id="r_u" checked onclick="toggleFields()">
                        <label for="r_u"><i class="fas fa-user"></i> Vásárló</label>
                        
                        <input type="radio" name="reg_type" value="firm" id="r_f" onclick="toggleFields()">
                        <label for="r_f"><i class="fas fa-store"></i> Cég</label>
                    </div>

                    <div id="f_id" style="display:none;" class="form-group">
                        <label>Cég azonosító (10 számjegy)</label>
                        <input type="text" name="ID" id="id_input" inputmode="numeric" 
                            pattern="[0-9]{10}" maxlength="10" placeholder="0123456789"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    </div>

                    <p class="login-prompt">
                        Van már fiókod? 
                        <a href="login.php" class="login-link">Lépj be!</a>
                    </p>
                </div>

                <div class="main-col">
                    <?php if ($msg): ?> <div class="msg success"><?= $msg ?></div> <?php endif; ?>
                    <?php if ($error): ?> <div class="msg error-msg"><?= $error ?></div> <?php endif; ?>

                    <div class="section-title"><i class="fas fa-file-signature"></i> Alapadatok</div>
                    
                    <div class="form-group">
                        <label>Teljes név</label>
                        <input type="text" name="name" placeholder="Példa János" required>
                    </div>

                    <div class="form-group">
                        <label>E-mail cím</label>
                        <input type="email" name="email" placeholder="pelda@email.hu" required>
                    </div>

                    <div id="u_fields" class="form-group">
                        <label>Felhasználónév</label>
                        <input type="text" name="username" id="un_input" placeholder="janos01">
                    </div>

                    <div id="f_fields" style="display:none;" class="form-group">
                        <label>Cégnév / Márka</label>
                        <input type="text" name="brand_name" id="bn_input" placeholder="Termék Kft.">
                    </div>

                    <div class="section-title mt-30"><i class="fas fa-lock"></i> Biztonság</div>
                    
                    <div class="password-grid">
                        <div class="form-group flex-1">
                            <label>Jelszó</label>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="p1" placeholder="••••••••" required>
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('p1', this)"></i>
                            </div>
                        </div>
                        <div class="form-group flex-1">
                            <label>Megerősítés</label>
                            <div class="password-wrapper">
                                <input type="password" name="password_confirm" id="p2" placeholder="••••••••" required>
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('p2', this)"></i>
                            </div>
                        </div>
                    </div>
                    <small class="password-hint">
                        Minimum 8 karakter, kis- és nagybetű, szám és szimbólum.
                    </small>
                </div>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn-reg">
                    <i class="fas fa-check-circle"></i> Fiók létrehozása
                </button>
            </div>
        </form>
    </div>

<script>
    function toggleFields() {
        const isUser = document.getElementById('r_u').checked;
        const fIdDiv = document.getElementById('f_id');
        const uFields = document.getElementById('u_fields');
        const fFields = document.getElementById('f_fields');

        const idInput = document.getElementById('id_input');
        const unInput = document.getElementById('un_input');
        const bnInput = document.getElementById('bn_input');

        if (isUser) {
            fIdDiv.style.display = 'none';
            uFields.style.display = 'block';
            fFields.style.display = 'none';
            idInput.required = false;
            unInput.required = true;
            bnInput.required = false;
        } else {
            fIdDiv.style.display = 'block';
            uFields.style.display = 'none';
            fFields.style.display = 'block';
            idInput.required = true;
            unInput.required = false;
            bnInput.required = true;
        }
    }

    const p1 = document.getElementById('p1');
    const p2 = document.getElementById('p2');

    function checkPass() {
        if(p2.value !== "") {
            p2.style.borderColor = (p1.value === p2.value) ? "#2ecc71" : "#ff4757";
        }
    }

    p1.addEventListener('keyup', checkPass);
    p2.addEventListener('keyup', checkPass);
    window.onload = toggleFields;

    function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}
</script>
</body>
</html>