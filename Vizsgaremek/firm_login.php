<?php
session_start();
include "connect.php"; // Itt jön létre a $conn változó

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // 1. Lekérdezzük a céget az email alapján a 'firm' táblából
    // Fontos: lekérjük az 'approved' státuszt is!
    $stmt = $conn->prepare("SELECT ID, password, approved FROM firm WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $firm = $result->fetch_assoc();

    // 2. Ellenőrzés: Létezik a cég ÉS jó a jelszó?
    if ($firm && password_verify($password, $firm["password"])) {
        
        // 3. Ellenőrzés: Jóvá van hagyva a cég?
        if ($firm['approved'] == 1) {
            $_SESSION["firm_id"] = $firm["ID"]; // Eltároljuk a cég egyedi azonosítóját
            header("Location: firm_dashboard.php");
            exit;
        } else {
            $error = "A fiókja még jóváhagyásra vár az adminisztrátortól.";
        }
        
    } else {
        $error = "Hibás céges email vagy jelszó!";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Céges Belépés</title>
    <style>
        body { 
            background: linear-gradient(135deg, #1e3c72, #2a5298); /* Sötétebb kék a cégeknek */
            font-family: 'Segoe UI', Arial, sans-serif; 
            height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; 
        }
        .login-box { 
            background: white; padding: 40px; width: 350px; border-radius: 15px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.3); 
        }
        h2 { text-align: center; color: #1e3c72; margin-bottom: 25px; }
        input { 
            width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; 
            border: 1px solid #ddd; box-sizing: border-box; 
        }
        button { 
            width: 100%; padding: 12px; background: #1e3c72; color: white; 
            border: none; border-radius: 8px; cursor: pointer; font-weight: bold; 
        }
        button:hover { background: #16305a; }
        .error { 
            color: #e74c3c; text-align: center; background: #fdf2f2; 
            padding: 8px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; 
        }
        .footer-links { text-align: center; margin-top: 20px; font-size: 13px; }
        .footer-links a { color: #1e3c72; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Partner Portál</h2>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Céges email cím" required>
            <input type="password" name="password" placeholder="Jelszó" required>
            <button type="submit">Belépés az irodába</button>
        </form>

        <div class="footer-links">
            <p>Még nincs partnerfiókja? <br>
            <a href="firm_register.php">Regisztráljon cégként itt!</a></p>
            <hr>
            <a href="login.php">Vásárlói belépés</a>
        </div>
    </div>
</body>
</html>