<?php
// 1. Kapcsolat az adatbázissal
include "connect.php";

// 3. A fájlok kézi betöltése (PHPMailer könyvtár)
require 'C:\xampp\htdocs\Vizsgaremek_g\PHPMailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\Vizsgaremek_g\PHPMailer\src\Exception.php';
require 'C:\xampp\htdocs\Vizsgaremek_g\PHPMailer\src\SMTP.php';

$message = ""; 
$message_type = ""; 

if (isset($_POST['reset_request'])) {
    $email = $_POST['email'];
    
    // Ellenőrizzük, létezik-e a felhasználó
    $stmt = $conn->prepare("SELECT ID FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+30 minutes"));

        // Token elmentése az adatbázisba
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires);
        $stmt->execute();

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            // SMTP Beállítások
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = ' '; // A te Gmail címed
            $mail->Password   = ' '; // Az App Password-öd
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Címzettek - A setFrom és a Username egyezzen meg a Gmail miatt!
            $mail->setFrom('no-reply@szupershop.hu', 'SzuperShop');
            $mail->addAddress($email);

            // Tartalom összeállítása
            $reset_link = "http://localhost/Vizsgaremek_g/reset_password.php?token=" . $token;
            $mail->isHTML(true);
            $mail->Subject = 'Jelszó visszaállítás - SzuperShop';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; border: 1px solid #ddd; padding: 20px; border-radius: 10px; max-width: 600px;'>
                    <h2 style='color: #1b263b;'>SzuperShop Jelszóvisszaállítás</h2>
                    <p>Elfelejtetted a jelszavad? Semmi gond!</p>
                    <p>Kattints az alábbi gombra a jelszavad megváltoztatásához. A link <strong>30 percig</strong> érvényes.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$reset_link' style='background-color: #00d2ff; color: #1b263b; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Új jelszó megadása</a>
                    </div>
                    <p style='font-size: 0.8rem; color: #7f8c8d;'>Ha nem te kérted a visszaállítást, hagyd figyelmen kívül ezt a levelet.</p>
                </div>
            ";

            if($mail->send()) {
                $message = "A visszaállító linket elküldtük az e-mail címedre!";
                $message_type = "success";
            }
        } catch (Exception $e) {
            // Itt kiírjuk a pontos hibát, ha valami elszállna
            $message = "Hiba történt a küldés során: " . $mail->ErrorInfo;
            $message_type = "error";
        }
    } else {
        $message = "Ezzel az e-mail címmel nincs regisztrált felhasználó.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SzuperShop - Jelszó visszaállítás</title>
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

        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: var(--light-bg); 
            margin: 0; 
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-card {
            background: var(--white);
            width: 900px;
            min-height: 450px;
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
            padding: 30px 45px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .profile-header h2 { margin: 0; font-size: 1.5rem; letter-spacing: 0.5px; }

        .main-layout {
            display: flex;
            padding: 40px;
            gap: 50px;
            flex-grow: 1;
        }

        .side-col { 
            flex: 1; 
            border-right: 1px solid #eee; 
            padding-right: 40px; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        .main-col { flex: 1.2; display: flex; flex-direction: column; justify-content: center; }

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

        .form-group { margin-bottom: 20px; }
        
        label { 
            display: block; 
            font-size: 0.85rem; 
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
            transition: 0.3s;
        }

        input:focus { 
            border-color: var(--accent-blue); 
            outline: none; 
            box-shadow: 0 0 0 4px rgba(0,210,255,0.1);
        }

        .btn-reset {
            background: var(--dark-blue);
            color: white;
            border: none;
            padding: 16px 45px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            transition: 0.3s;
            width: 100%;
        }

        .btn-reset:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.2); background: #2c3e50; }

        .back-link { 
            color: white; 
            text-decoration: none; 
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0.8;
            transition: 0.3s;
            font-size: 0.9rem;
        }
        .back-link:hover { opacity: 1; transform: translateX(-5px); }

        .alert { padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 600; text-align: center; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .back-to-login { 
            margin-top: 15px; 
            display: inline-block; 
            color: var(--text-muted); 
            text-decoration: none; 
            font-size: 0.9rem;
            font-weight: 600;
        }
        .back-to-login:hover { color: var(--dark-blue); }
    </style>
</head>
<body>

<div class="profile-card">
    <div class="profile-header">
        <h2><i class="fas fa-lock-open"></i> Segítség a belépéshez</h2>
        <a href="login.php" class="back-link"><i class="fas fa-chevron-left"></i> Vissza a bejelentkezéshez</a>
    </div>

    <div class="main-layout">
        <div class="side-col">
            <div style="font-size: 4rem; color: var(--accent-blue); margin-bottom: 20px;">
                <i class="fas fa-key"></i>
            </div>
            <h3 style="color: var(--dark-blue); margin: 0;">Elfelejtett jelszó?</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 10px;">
                Semmi baj, bárkivel előfordulhat. Add meg az e-mail címed, és küldünk egy biztonságos linket a visszaállításhoz.
            </p>
        </div>

        <div class="main-col">
            <div class="section-title"><i class="fas fa-envelope-open-text"></i> Visszaállítás</div>

            <?php if ($message): ?>
                <div class="alert <?= $message_type ?>">
                    <i class="fas <?= $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Regisztrált E-mail címed</label>
                    <input type="email" name="email" placeholder="pelda@email.hu" required>
                </div>

                <button type="submit" name="reset_request" class="btn-reset">
                    Visszaállító link küldése
                </button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" class="back-to-login">Eszembe jutott a jelszavam, inkább belépek!</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>