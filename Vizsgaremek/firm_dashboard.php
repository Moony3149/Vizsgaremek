<?php
session_start();
include "connect.php";

// Ellenőrizzük, hogy a cég be van-e jelentkezve
if (!isset($_SESSION["firm_id"])) {
    header("Location: firm_login.php");
    exit;
}

$firm_id = $_SESSION["firm_id"];
$msg = "";

/* ======================
   ÚJ TERMÉK HOZZÁADÁSA
   ====================== */
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    
    // Alapértelmezetten aktív (1), de nem jóváhagyott (0)
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, amount, type, firm_id, active, approved) VALUES (?, ?, ?, ?, ?, ?, 1, 0)");
    $stmt->bind_param("ssdisi", $name, $description, $price, $amount, $type, $firm_id);
    
    if ($stmt->execute()) {
        $msg = "Termék sikeresen hozzáadva! Jóváhagyásra vár.";
    }
}

/* ======================
   TERMÉK MÓDOSÍTÁSA
   ====================== */
if (isset($_POST['edit_product'])) {
    $p_id = $_POST['p_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $amount = $_POST['amount'];

    // Biztonsági ellenőrzés: csak a saját termékét módosíthatja!
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, amount = ? WHERE ID = ? AND firm_id = ?");
    $stmt->bind_param("sdiii", $name, $price, $amount, $p_id, $firm_id);
    
    if ($stmt->execute()) {
        $msg = "Termék adatai frissítve!";
    }
}

// A cég saját termékeinek lekérése
$stmt = $conn->prepare("SELECT * FROM products WHERE firm_id = ?");
$stmt->bind_param("i", $firm_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Céges Vezérlőpult</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        h2, h3 { color: #1e3c72; }
        
        /* Form stílus */
        .add-box { background: #eef2f7; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .add-box input, .add-box select, .add-box textarea { 
            padding: 10px; margin: 5px; border: 1px solid #ccc; border-radius: 5px; 
        }
        
        /* Táblázat stílus */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #1e3c72; color: white; }
        
        .status-wait { color: orange; font-weight: bold; }
        .status-ok { color: green; font-weight: bold; }
        
        .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold; }
        .btn-add { background: #27ae60; }
        .btn-edit { background: #2980b9; }
        .logout { float: right; color: #e74c3c; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <a href="logout.php" class="logout">Kijelentkezés</a>
    <h2>Céges Vezérlőpult</h2>
    
    <?php if($msg): ?>
        <p style="color: green; font-weight: bold;"><?= $msg ?></p>
    <?php endif; ?>

    <div class="add-box">
        <h3>➕ Új termék felvétele</h3>
        <form method="POST">
            <input type="text" name="name" placeholder="Termék neve" required>
            <input type="number" step="0.01" name="price" placeholder="Ár" required style="width: 100px;">
            <input type="number" name="amount" placeholder="Készlet" required style="width: 80px;">
            <select name="type" required>
                <option value="">-- Típus --</option>
                <option>Zöldség és gyümölcs</option>
                <option>Tejtermék- tojás</option>
                <option>Pékáru</option>
                <option>Húsáru</option>
                <option>Italok</option>
            </select><br>
            <textarea name="description" placeholder="Rövid leírás" style="width: 95%; height: 60px; margin-top: 10px;"></textarea><br>
            <button type="submit" name="add_product" class="btn btn-add">Termék mentése</button>
        </form>
    </div>

    <h3>📦 Saját termékek listája</h3>
    <table>
        <thead>
            <tr>
                <th>Név</th>
                <th>Ár</th>
                <th>Készlet</th>
                <th>Állapot</th>
                <th>Művelet</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <form method="POST">
                    <td><input type="text" name="name" value="<?= $row['name'] ?>" style="width: 150px;"></td>
                    <td><input type="number" step="0.01" name="price" value="<?= $row['price'] ?>" style="width: 80px;"></td>
                    <td><input type="number" name="amount" value="<?= $row['amount'] ?>" style="width: 60px;"></td>
                    <td>
                        <?= $row['approved'] == 1 ? '<span class="status-ok">Jóváhagyva</span>' : '<span class="status-wait">Jóváhagyásra vár</span>' ?>
                    </td>
                    <td>
                        <input type="hidden" name="p_id" value="<?= $row['ID'] ?>">
                        <button type="submit" name="edit_product" class="btn btn-edit">Mentés</button>
                    </td>
                </form>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>