<?php
session_start();
include "connect.php";

$user_id = $_SESSION['user_id'] ?? null;
$firm_id = $_SESSION['firm_id'] ?? null;

if (!$user_id && !$firm_id) {
    header("Location: login.php");
    exit();
}

$bodyClass = '';
if (isset($_SESSION['admin']) && $_SESSION['admin'] === 'admin') {
    $bodyClass = 'role-admin';
} elseif (isset($_SESSION['firm_id'])) {
    $bodyClass = 'role-firm';
} else {
    $bodyClass = 'role-user';
}

if ($user_id) {
    $stmt = $conn->prepare("SELECT username as name, email, profile_pic FROM users WHERE ID = ?");
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $conn->prepare("SELECT brand_name as name, email, profile_pic FROM firm WHERE ID = ?");
    $stmt->bind_param("i", $firm_id);
}

$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Ha nincs adat az adatbázisban ezzel az ID-val
if (!$userData) {
    // Biztonsági játék: ha nincs ilyen user, dobjuk ki a loginra
    header("Location: logout.php"); 
    exit();
}

// Most már biztonságosan elérhetjük a tömböt
$current_pic = $userData['profile_pic'] ?: ($user_id ? 'default_user.png' : 'default_firm.png');
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilom - Termék<srtong>VISION</strong></title>
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
            --lighter-blue: #0c9dd6ef
        }

        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: var(--light-bg); 
            margin: 0; 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px; /* Hely a mobil szélén */
            box-sizing: border-box;
        }

        /* SZEREPKÖRÖK */
        body.role-firm { background: #e3f2fd; }
        body.role-firm .profile-header { background: #1b263b !important; }
        body.role-firm .btn-update { background: #1976d2 !important; }

        body.role-admin { background: #121212; color: #e0e0e0; }
        body.role-admin .profile-header { background: #000 !important; border-bottom: 2px solid var(--accent-blue); }
        body.role-admin .profile-card { background: #1e1e1e; border: 1px solid #333; }
        body.role-admin input { background: #2d2d2d; border-color: #444; color: white; }
        body.role-admin .btn-update { background: var(--accent-blue); color: #000; }
        body.role-admin .btn-container { background: #161616; border-top: 1px solid #333; }
        body.role-admin .side-col { border-right: 1px solid #333; }

        .profile-card {
            background: var(--white);
            width: 100%;
            max-width: 1200px; /* Max szélesség, de rugalmas */
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
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap; /* Mobilon egymás alá törhet */
            gap: 15px;
        }

        .profile-header h2 { margin: 0; font-size: 1.5rem; }

        .main-layout {
            display: flex;
            padding: 30px;
            gap: 40px;
            flex-grow: 1;
        }

        .side-col { 
            flex: 1; 
            border-right: 1px solid #eee; 
            padding-right: 20px; 
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .main-col { 
            flex: 2; 
        }

        .img-section img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid var(--accent-blue);
            margin-bottom: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--accent-blue);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid var(--accent-blue);
            padding-bottom: 8px;
        }

        .form-group { margin-bottom: 15px; }
        label { 
            display: block; 
            font-size: 0.85rem; 
            font-weight: 700; 
            margin-bottom: 6px; 
            color: var(--text-muted); 
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #edf2f7;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 1rem;
        }

        .password-row {
            display: flex; 
            gap: 20px;
        }

        .btn-container {
            padding: 20px 30px;
            background: #f8f9fa;
            text-align: right;
            border-top: 1px solid #eee;
        }

        .btn-update {
            background: #ffffff1e; /* Félig átlátszó az alap színen */
            color: white;
            border: none;
            padding: 16px 45px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }

            /* Amikor ráviszed az egeret a gombra, legyen még látványosabb */
        .btn-update:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 210, 255, 0.4);
            background: var(--accent-blue);
            color: var(--dark-blue);
            filter: brightness(1.1);
            }

        .btn-update {
            transform: translateY(-3px); /* Megemelkedik */
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            background: var(--accent-blue); /* Kicsit kivilágosodik */
            color: var(--dark-blue);
            }

        /* --- RESPONSIVE BREAKPOINTS --- */

        @media (max-width: 992px) {
            .main-layout {
                flex-direction: column; /* Egymás alá rakja az oszlopokat */
                padding: 20px;
            }
            .side-col {
                border-right: none;
                border-bottom: 1px solid #eee;
                padding-right: 0;
                padding-bottom: 20px;
            }
        }

        @media (max-width: 600px) {
            .password-row {
                flex-direction: column; /* Jelszavak egymás alá */
                gap: 0;
            }
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            .btn-update {
                width: 100%; /* Teljes szélességű gomb mobilon */
                padding: 16px 20px;
            }
        }
        /* --- Jelszó mezők és wrapper --- */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            padding-right: 45px !important;
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

        /* --- Struktúra segédosztályok (inline helyett) --- */
        .mt-30 {
            margin-top: 30px;
        }

        .flex-1 {
            flex: 1;
        }

        .text-welcome {
            margin-top: 20px;
            font-style: italic;
            color: var(--text-muted);
        }

        /* Sötét mód / Admin korrekció */
        body.role-admin .toggle-password {
            color: #888;
        }
    </style>
</head>
<body class="<?= $bodyClass ?>">

    <div class="profile-card">
        <div class="profile-header">
            <h2><i class="fas fa-id-card"></i> Felhasználói Profil</h2>
            <a href="index.php" class="back-link" style="color: white; text-decoration: none;"><i class="fas fa-chevron-left"></i> Vissza a fő oldalra</a>
        </div>

        <form action="update_profile_process.php" method="POST" enctype="multipart/form-data">
            <div class="main-layout">
                
                <div class="side-col">
                    <div class="img-section">
        <img src="uploads/profiles/<?= $current_pic ?>" id="preview-img" alt="Profilkép">
        <div style="margin-top: 10px;">
            <label for="file-upload" style="cursor: pointer; color: var(--accent-blue); font-size: 0.9rem;">
                <i class="fas fa-cloud-upload-alt"></i> Új profilkép választása
            </label>
            <input id="file-upload" type="file" name="profile_image" style="display: none;" accept="image/*" onchange="previewImage(this)">
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview-img');
        
            // Ellenőrizzük, hogy van-e kiválasztott fájl
            if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                // Beállítjuk az img src-jét az olvasott adatra
                preview.src = e.target.result;
                
                // Egy kis vizuális visszajelzés (opcionális: animáció)
                preview.style.opacity = '0';
                setTimeout(() => {
                    preview.style.transition = 'opacity 0.5s ease';
                    preview.style.opacity = '1';
                }, 50);
            }

            // Elolvassuk a fájlt mint adat URL
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>       
            <p class="text-welcome">
                Üdvözlünk, <strong><?= htmlspecialchars($userData['name']) ?></strong>!
            </p>
            </div>

            <div class="main-col">
                <div class="section-title"><i class="fas fa-user-edit"></i> Alapadatok</div>
                
                <div class="form-group">
                    <label>Teljes név / Brand név</label>
                    <input type="text" name="display_name" value="<?= htmlspecialchars($userData['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>E-mail cím</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required>
                </div>

                <div class="section-title mt-30">
                    <i class="fas fa-lock"></i> Biztonság
                </div>

                <div class="form-group">
                    <label>Jelenlegi jelszó</label>
                    <div class="password-wrapper">
                        <input type="password" name="old_password" id="old_p" placeholder="Módosításhoz kötelező">
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('old_p', this)"></i>
                    </div>
                </div>

                <div class="password-row">
                    <div class="form-group flex-1">
                        <label>Új jelszó</label>
                        <div class="password-wrapper">
                            <input type="password" name="new_password" id="new_p" placeholder="Min. 8 karakter">
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('new_p', this)"></i>
                        </div>
                    </div>
                    
                    <div class="form-group flex-1">
                        <label>Megerősítés</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="conf_p" placeholder="Ismételd meg">
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('conf_p', this)"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="btn-container">
            <button type="submit" class="btn-update">
                <i class="fas fa-save"></i> Mentés
            </button>
        </div>
    </form>
</div>
<script>
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