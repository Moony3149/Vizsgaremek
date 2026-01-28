<?php
session_start();
include "connect.php";

// JAVÍTÁS: A login.php-ban $_SESSION['role']-t állítottunk be, azt ellenőrizzük!
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$msg = "";

// Cég jóváhagyása
if (isset($_GET['approve_firm'])) {
    $id = intval($_GET['approve_firm']);
    $stmt = $conn->prepare("UPDATE firm SET approved = 1 WHERE ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $msg = "Cég jóváhagyva!";
}

// Termék jóváhagyása
if (isset($_GET['approve_product'])) {
    $id = intval($_GET['approve_product']);
    $stmt = $conn->prepare("UPDATE products SET approved = 1 WHERE ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $msg = "Termék jóváhagyva!";
}

// Függőben lévő cégek
$pending_firms = $conn->query("SELECT * FROM firm WHERE approved = 0");

// JAVÍTÁS: Itt hozunk létre $pending_products változót és szűrünk az approved = 0-ra
$pending_products = $conn->query("SELECT p.*, f.brand_name FROM products p LEFT JOIN firm f ON p.firm_id = f.ID WHERE p.approved = 0");
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Adminisztrációs Felület</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .admin-container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background: white; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; color: #333; }
        .btn-ok { background: #27ae60; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: bold; }
        .btn-ok:hover { background: #219150; }
        .msg { background: #e8f5e9; color: #2e7d32; padding: 15px; margin-bottom: 20px; border-radius: 5px; border-left: 5px solid #2e7d32; }
        .nav-link { font-size: 1rem; text-decoration: none; color: #3498db; }
    </style>
</head>
<body>

<div class="admin-container">
    <h1>
        <span><i class="fas fa-user-shield"></i> Rendszer Adminisztráció</span>
        <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Kijelentkezés</a>
    </h1>
    
    <?php if($msg): ?>
        <div class="msg"><i class="fas fa-check-circle"></i> <?= $msg ?></div>
    <?php endif; ?>

    <h3>⏳ Jóváhagyásra váró cégek</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Cégnév (Márka)</th>
            <th>Email</th>
            <th>Művelet</th>
        </tr>
        <?php if($pending_firms->num_rows > 0): ?>
            <?php while($f = $pending_firms->fetch_assoc()): ?>
            <tr>
                <td><?= $f['ID'] ?></td>
                <td><?= htmlspecialchars($f['brand_name']) ?></td> <td><?= htmlspecialchars($f['email']) ?></td>
                <td><a href="admin.php?approve_firm=<?= $f['ID'] ?>" class="btn-ok">Jóváhagyás</a></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">Nincs várakozó cég.</td></tr>
        <?php endif; ?>
    </table>

    <h3>📦 Jóváhagyásra váró termékek</h3>
    <table>
        <tr>
            <th>Név</th>
            <th>Cég</th>
            <th>Ár</th>
            <th>Művelet</th>
        </tr>
        <?php if($pending_products && $pending_products->num_rows > 0): ?>
            <?php while($p = $pending_products->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['brand_name'] ?: 'Nincs megadva') ?></td> <td><?= number_format($p['price'], 0, ',', ' ') ?> Ft</td>
                <td><a href="admin.php?approve_product=<?= $p['ID'] ?>" class="btn-ok">Engedélyezés</a></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">Nincs várakozó termék.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>