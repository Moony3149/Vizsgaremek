<?php
session_start();
include "connect.php";

// Itt érdemes lenne egy plusz ellenőrzés, hogy valóban az admin lépett-e be
// Pl.: if($_SESSION['role'] !== 'admin') die("Nincs jogosultságod!");

$msg = "";

// Cég jóváhagyása
if (isset($_GET['approve_firm'])) {
    $id = $_GET['approve_firm'];
    $stmt = $conn->prepare("UPDATE firm SET approved = 1 WHERE ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $msg = "Cég jóváhagyva!";
}

// Termék jóváhagyása
if (isset($_GET['approve_product'])) {
    $id = $_GET['approve_product'];
    $stmt = $conn->prepare("UPDATE products SET approved = 1 WHERE ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $msg = "Termék jóváhagyva!";
}

// Függőben lévő cégek lekérése
$pending_firms = $conn->query("SELECT * FROM firm WHERE approved = 0");

// Függőben lévő termékek lekérése
$pending_products = $conn->query("SELECT p.*, f.name as firm_name FROM products p JOIN firm f ON p.firm_id = f.ID WHERE p.approved = 0");
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Adminisztrációs Felület</title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 20px; }
        .admin-container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h1 { color: #d32f2f; border-bottom: 2px solid #d32f2f; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #eee; }
        .btn-ok { background: #2e7d32; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .msg { background: #e8f5e9; color: #2e7d32; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
    </style>
</head>
<body>

<div class="admin-container">
    <h1>Rendszer Adminisztráció</h1>
    
    <?php if($msg) echo "<div class='msg'>$msg</div>"; ?>

    <h3>⏳ Jóváhagyásra váró cégek</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Cégnév</th>
            <th>Email</th>
            <th>Művelet</th>
        </tr>
        <?php while($f = $pending_firms->fetch_assoc()): ?>
        <tr>
            <td><?= $f['ID'] ?></td>
            <td><?= $f['name'] ?></td>
            <td><?= $f['email'] ?></td>
            <td><a href="admin.php?approve_firm=<?= $f['ID'] ?>" class="btn-ok">Jóváhagyás</a></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h3>📦 Jóváhagyásra váró termékek</h3>
    <table>
        <tr>
            <th>Név</th>
            <th>Cég</th>
            <th>Ár</th>
            <th>Művelet</th>
        </tr>
        <?php while($p = $pending_products->fetch_assoc()): ?>
        <tr>
            <td><?= $p['name'] ?></td>
            <td><?= $p['firm_name'] ?></td>
            <td><?= $p['price'] ?> Ft</td>
            <td><a href="admin.php?approve_product=<?= $p['ID'] ?>" class="btn-ok">Engedélyezés</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>