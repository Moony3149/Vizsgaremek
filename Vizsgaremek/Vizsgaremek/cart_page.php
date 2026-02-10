<?php
session_start();
include "connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- TÖRLÉS A KOSÁRBÓL (Prepared statement-tel a biztonságért) ---
if (isset($_GET['remove'])) {
    $item_id = (int)$_GET['remove'];
    $stmt_del = $conn->prepare("DELETE FROM shopping_list WHERE id = ? AND user_id = ?");
    $stmt_del->bind_param("ii", $item_id, $user_id);
    $stmt_del->execute();
    header("Location: cart_page.php");
    exit;
}

// --- LEKÉRDEZÉS ---
$sql = "SELECT s.id AS cart_item_id, s.quantity, p.name, p.picture, p.price 
        FROM shopping_list s 
        JOIN products p ON s.product_id = p.ID 
        WHERE s.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

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
        <thead>
            <tr>
                <th>Termék</th>
                <th>Egységár</th>
                <th>Mennyiség</th>
                <th>Részösszeg</th>
                <th>Művelet</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($item = $result->fetch_assoc()): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="uploads/<?= $item['picture'] ?: 'no_image.jpg' ?>" width="50" height="50" style="border-radius: 5px; object-fit: cover;">
                            <span><?= htmlspecialchars($item['name']) ?></span>
                        </div>
                    </td>
                    <td><?= number_format($item['price'], 0, ',', ' ') ?> Ft</td>

                    <td>
                        <div style="display: flex; align-items: center; gap: 12px; background: #eee; width: fit-content; padding: 5px 10px; border-radius: 20px;">
                            <a href="cart_actions.php?update_qty=minus&id=<?= $item['cart_item_id'] ?>" 
                        style="text-decoration: none; color: #2c3e50; font-weight: bold; font-size: 1.4rem;">-</a>
                    
                            <span style="font-weight: bold; min-width: 20px; text-align: center;"><?= $item['quantity'] ?></span>
                    
                            <a href="cart_actions.php?update_qty=plus&id=<?= $item['cart_item_id'] ?>" 
                        style="text-decoration: none; color: #2c3e50; font-weight: bold; font-size: 1.4rem;">+</a>
                        </div>
                    </td>
                    <td><strong><?= number_format($subtotal, 0, ',', ' ') ?> Ft</strong></td>
                    <td>
                        <a href="cart_page.php?remove=<?= $item['cart_item_id'] ?>" class="remove-btn" onclick="return confirm('Biztosan törlöd?')">
                            <span style="font-style: normal;">Törlés</span>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px;">A kosarad jelenleg üres.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total > 0): ?>
        <div class="total">Végösszeg: <?= number_format($total, 0, ',', ' ') ?> Ft</div>
    <?php endif; ?>
</body>
</html>