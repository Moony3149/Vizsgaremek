<?php
// 1. Kapcsolat az adatbázissal
include "connect.php";

// 3. A fájlok kézi betöltése
require 'C:\xampp\htdocs\Vizsgaremek\PHPMailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\Vizsgaremek\PHPMailer\src\Exception.php';
require 'C:\xampp\htdocs\Vizsgaremek\PHPMailer\src\SMTP.php';

$message = ""; // Visszajelző üzenet tárolása
$message_type = ""; // 'success' vagy 'error'

if (isset($_POST['reset_request'])) {
    $email = $_POST['email'];
    
    // Ellenőrizzük, létezik-e a felhasználó
    $stmt = $conn->prepare("SELECT ID FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // Generálunk egy egyedi tokent
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+30 minutes"));

        // Elmentjük az adatbázisba (ha már volt kérése, felülírjuk vagy újat adunk hozzá)
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires);
        $stmt->execute();

        // Levél küldése
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            // SMTP Beállítások
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'gergo.mokan@gmail.com'; // IDE A GMAIL CÍMED (jelszo2 a jelenlegi jelszó)
            $mail->Password   = 'glci kheg oynr avlt'; // IDE AZ APP PASSWORD (szóközök nélkül)
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8'; // Ékezetek miatt kötelező

            // Címzettek
            $mail->setFrom('no-reply@szupershop.hu', 'SzuperShop');
            $mail->addAddress($email);

            // Tartalom
            $reset_link = "http://localhost/Vizsgaremek/reset_password.php?token=" . $token;
            $mail->isHTML(true);
            $mail->Subject = 'Jelszó visszaállítás - SzuperShop';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; border: 1px solid #ddd; padding: 20px; border-radius: 10px;'>
                    <h2 style='color: #2c3e50;'>SzuperShop Jelszóvisszaállítás</h2>
                    <p>Elfelejtetted a jelszavad? Semmi gond!</p>
                    <p>Kattints az alábbi gombra a jelszavad megváltoztatásához. A link <strong>30 percig</strong> érvényes.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$reset_link' style='background-color: #3498db; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Új jelszó megadása</a>
                    </div>
                    <p style='font-size: 0.8rem; color: #7f8c8d;'>Ha nem te kérted a visszaállítást, hagyd figyelmen kívül ezt a levelet.</p>
                </div>
            ";

            $mail->send();
            $message = "A visszaállító linket elküldtük az e-mail címedre!";
            $message_type = "success";
        } catch (Exception $e) {
            $message = "Hiba történt a küldés során: {$mail->ErrorInfo}";
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
    <title>SzuperShop - Elfelejtett jelszó</title>
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
        .logo i { color: #3498db; }
        
        input[type="email"] { 
            width: 100%; 
            padding: 12px; 
            margin: 15px 0; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            box-sizing: border-box; 
            outline: none;
        }
        input[type="email"]:focus { border-color: #3498db; }

        .btn-reset { 
            width: 100%; 
            padding: 12px; 
            background: #3498db; 
            color: white; 
            border: none; 
            border-radius: 8px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.3s;
        }
        .btn-reset:hover { background: #2980b9; }

        .alert { 
            padding: 12px; 
            margin-bottom: 15px; 
            border-radius: 8px; 
            font-size: 0.9rem; 
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .back-to-login { 
            margin-top: 20px; 
            display: block; 
            color: #7f8c8d; 
            text-decoration: none; 
            font-size: 0.9rem;
        }
        .back-to-login:hover { color: #2c3e50; }
    </style>
</head>
<body>

<div class="reset-container">
    <div class="logo"><i class="fas fa-shopping-bag"></i> SzuperShop</div>
    <h3>Elfelejtett jelszó</h3>
    <p style="color: #7f8c8d; font-size: 0.9rem;">Add meg az e-mail címed, és küldünk egy linket a visszaállításhoz.</p>

    <?php if ($message): ?>
        <div class="alert <?= $message_type ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="pelda@email.hu" required>
        <button type="submit" name="reset_request" class="btn-reset">Link küldése</button>
    </form>

    <a href="login.php" class="back-to-login"><i class="fas fa-arrow-left"></i> Vissza a bejelentkezéshez</a>
</div>

</body>
</html>