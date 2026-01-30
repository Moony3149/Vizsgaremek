<?php
session_start();
include "connect.php";

$user_id = $_SESSION['user_id'] ?? null;
$firm_id = $_SESSION['firm_id'] ?? null;

if (!$user_id && !$firm_id) {
    header("Location: login.php");
    exit();
}

if ($user_id) {
    $stmt = $conn->prepare("SELECT username as name, email, profile_pic FROM users WHERE ID = ?");
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $conn->prepare("SELECT brand_name as name, email, profile_pic FROM firm WHERE ID = ?");
    $stmt->bind_param("i", $firm_id);
}

$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$current_pic = $userData['profile_pic'] ?: ($user_id ? 'default_user.png' : 'default_firm.png');
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilom - SzuperShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --dark-blue: #1a2530ff; 
            --accent-blue: #34495e;
            --light-bg: #f5f6fa; 
            --white: #ffffff;
            --success: #27ae60; 
            --danger: #e74c3c; 
        }

        body { 
            font-family: 'Segoe UI', sans-serif; 
            background-color: var(--dark-blue); 
            margin: 0; 
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden; /* Megakadályozza a görgetést az egész oldalon */
        }

        .profile-card {
            background: var(--white);
            width: 900px;
            max-height: 95vh;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .profile-header {
            background: var(--accent-blue);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .profile-header h2 { margin: 0; font-size: 1.2rem; font-weight: 300; }

        .main-layout {
            display: flex;
            padding: 20px;
            gap: 30px;
        }

        /* Bal oszlop - Fotó és Alapadatok */
        .side-col { flex: 1; border-right: 1px solid #eee; padding-right: 20px; }
        
        /* Jobb oszlop - Jelszó */
        .main-col { flex: 1; }

        .img-section {
            text-align: center;
            margin-bottom: 15px;
        }

        .img-section img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--dark-blue);
        }

        .section-title {
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--dark-blue);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group { margin-bottom: 12px; }
        label { display: block; font-size: 0.75rem; font-weight: 600; margin-bottom: 4px; color: #666; }

        input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 0.9rem;
        }

        input:focus { border-color: var(--dark-blue); outline: none; }

        .btn-container {
            padding: 15px 20px;
            background: #f9f9f9;
            text-align: right;
            border-top: 1px solid #eee;
        }

        .btn-update {
            background: var(--dark-blue);
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }

        .btn-update:hover { background: #1a252f; }

        .msg-bar {
            position: absolute;
            top: 10px;
            width: 100%;
            text-align: center;
            z-index: 100;
        }

        .msg-bubble {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .back-link { color: white; text-decoration: none; font-size: 0.8rem; opacity: 0.8; }
        .back-link:hover { opacity: 1; }
    </style>
</head>
<body>

<?php if(isset($_GET['msg'])): ?>
    <div class="msg-bar">
        <?php if($_GET['msg'] == 'updated' || $_GET['msg'] == 'success'): ?>
            <div class="msg-bubble" style="background: var(--success); color: white;">Sikeres mentés!</div>
        <?php else: ?>
            <div class="msg-bubble" style="background: var(--danger); color: white;">Hiba történt!</div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="profile-card">
    <div class="profile-header">
        <h2><i class="fas fa-user-circle"></i> Fiókbeállítások</h2>
        <a href="index.php" class="back-link"><i class="fas fa-times"></i> Bezárás</a>
    </div>

    <form action="update_profile_process.php" method="POST" enctype="multipart/form-data">
        <div class="main-layout">
            
            <div class="side-col">
                <div class="img-section">
                    <img src="uploads/profiles/<?= $current_pic ?>" alt="Profil">
                    <div style="margin-top: 8px;">
                        <input type="file" name="profile_image" style="font-size: 0.7rem; border: none; background: none;">
                    </div>
                </div>

                <div class="section-title">Alapadatok</div>
                
                <div class="form-group">
                    <label>Név</label>
                    <input type="text" name="display_name" value="<?= htmlspecialchars($userData['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required>
                </div>
            </div>

            <div class="main-col">
                <div class="section-title"><i class="fas fa-key"></i> Jelszó módosítása</div>
                <p style="font-size: 0.7rem; color: #999; margin-bottom: 15px;">Csak akkor töltsd ki, ha változtatni szeretnél.</p>

                <div class="form-group">
                    <label>Jelenlegi jelszó</label>
                    <input type="password" name="old_password">
                </div>

                <div class="form-group">
                    <label>Új jelszó</label>
                    <input type="password" name="new_password">
                </div>

                <div class="form-group">
                    <label>Új jelszó ismét</label>
                    <input type="password" name="confirm_password">
                </div>
                
                <div style="margin-top: 20px; padding: 10px; background: #fff8e1; border-radius: 6px; font-size: 0.7rem; color: #856404;">
                    <i class="fas fa-info-circle"></i> A név módosítása után az "admin" név felszabadulhat mások számára.
                </div>
            </div>

        </div>

        <div class="btn-container">
            <button type="submit" class="btn-update">
                MENTÉS
            </button>
        </div>
    </form>
</div>

</body>
</html>