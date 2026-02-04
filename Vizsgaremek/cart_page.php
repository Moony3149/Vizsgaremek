<?php
session_start();
include "connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Törlés a kosárból
if (isset($_GET['remove'])) {
    $item_id = (int)$_GET['remove'];
    $conn->query("DELETE FROM shopping_list WHERE id = $item_id AND user_id = $user_id");
    header("Location: cart_page.php");
}

$sql = "SELECT s.*, p.name, p.picture 
        FROM shopping_list s 
        JOIN products p ON s.product_id = p.ID 
        WHERE s.user_id = $user_id";
$result = $conn->query($sql);
$total = 0;
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Kosaram</title>
    <style>
        body { font-family: sans-serif; padding: 40px; background: #f4f4f4; }
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c3e50; color: white; }
        .total { font-size: 1.5rem; text-align: right; margin-top: 20px; font-weight: bold; }
        .remove-btn { color: #e74c3c; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🛒 Bevásárlókosár</h1>
    <a href="index.php">← Vissza a vásárláshoz</a>
    
    <table>
        <tr>
            <th>Termék</th>
            <th>Ár</th>
            <th>Mennyiség</th>
            <th>Részösszeg</th>
            <th>Művelet</th>
        </tr>
        <?php while($item = $result->fetch_assoc()): 
            $subtotal = $item['product_price'] * $item['quantity'];
            $total += $subtotal;
        ?>
        <tr>
            <td>
            <div style="display: flex; align-items: center; gap: 10px;">
            <img src="uploads/<?= $item['picture'] ?: 'no_image.jpg' ?>" width="50" style="border-radius: 5px;">
            <span><?= htmlspecialchars($item['name']) ?></span>
        </div>
            </td>
            <td><?= number_format($item['product_price'], 0, ',', ' ') ?> Ft</td>
            <td><?= $item['quantity'] ?> db</td>
            <td><strong><?= number_format($subtotal, 0, ',', ' ') ?> Ft</strong></td>
            <td>
                <a href="cart_page.php?remove=<?= $item['id'] ?>" class="remove-btn" style="color: #e74c3c;">
                    <i class="fas fa-times-circle"></i>✖ Törlés
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div class="total">Végösszeg: <?= number_format($total, 0, ',', ' ') ?> Ft</div>
</body>
</html>