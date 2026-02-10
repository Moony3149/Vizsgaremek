<?php
session_start();
include "connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- MODOSÍTÁSI LOGIKA (Saját fájlon belül) ---
if (isset($_GET['update_qty']) && isset($_GET['id'])) {
    $cart_id = (int)$_GET['id'];
    $action = $_GET['update_qty'];

    if ($action === 'plus') {
        $sql = "UPDATE shopping_list SET quantity = quantity + 1 WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
    } 
    elseif ($action === 'minus') {
        $sql = "UPDATE shopping_list SET quantity = quantity - 1 WHERE id = ? AND user_id = ? AND quantity > 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
    } 
    elseif ($action === 'set' && isset($_GET['new_val'])) {
        $new_qty = (int)$_GET['new_val'];
        if ($new_qty < 1) $new_qty = 1;
        $sql = "UPDATE shopping_list SET quantity = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $new_qty, $cart_id, $user_id);
        $stmt->execute();
    }
    // Fontos: miután lefutott a SQL, frissítjük az oldalt, hogy eltűnjenek a GET paraméterek az URL-ből
    header("Location: cart_page.php");
    exit;
}

// --- TÖRLÉS ---
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
    <title>Kosaram - SzuperShop</title>
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
        }

        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: var(--light-bg); 
            margin: 0; 
            color: var(--text-main);
            padding-bottom: 50px;
        }

        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }

        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
            background: var(--dark-blue);
            padding: 20px 30px;
            border-radius: 20px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header h1 { margin: 0; font-size: 1.6rem; display: flex; align-items: center; gap: 12px; }
        .back-link { text-decoration: none; color: var(--accent-blue); font-weight: bold; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
        .back-link:hover { color: white; transform: translateX(-5px); }

        .cart-list { display: flex; flex-direction: column; gap: 15px; }

        .cart-item { 
            background: white; 
            padding: 15px 25px; 
            border-radius: 20px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .cart-item:hover { transform: scale(1.01); box-shadow: 0 6px 15px rgba(0,0,0,0.08); }

        .item-info { display: flex; align-items: center; gap: 20px; flex: 1; }
        .item-info img { width: 80px; height: 80px; object-fit: contain; border-radius: 12px; background: #f9f9f9; }
        .item-details h3 { margin: 0; font-size: 1.1rem; color: var(--dark-blue); }
        .item-details p { margin: 5px 0 0; color: var(--success-green); font-weight: bold; }

        .quantity-controls { 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            background: #f0f2f5; 
            padding: 8px 15px; 
            border-radius: 50px;
            margin: 0 20px;
        }
        .quantity-controls a { text-decoration: none; color: var(--dark-blue); font-weight: bold; font-size: 1.2rem; transition: 0.2s; }
        .quantity-controls a:hover { color: var(--accent-blue); }

        .qty-input {
            width: 45px;
            border: none;
            background: transparent;
            text-align: center;
            font-weight: bold;
            font-size: 1rem;
            color: var(--dark-blue);
            -moz-appearance: textfield;
        }

        .qty-input::-webkit-outer-spin-button,
        .qty-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .qty-input:focus {
            outline: none;
            background: #fff;
            border-radius: 5px;
        }

        .subtotal { font-weight: bold; font-size: 1.1rem; min-width: 120px; text-align: right; }

        .remove-link { color: var(--danger-red); text-decoration: none; font-size: 1.2rem; margin-left: 20px; transition: 0.3s; }
        .remove-link:hover { transform: scale(1.2) rotate(90deg); }

        .cart-footer { 
            margin-top: 30px; 
            background: white; 
            padding: 30px; 
            border-radius: 25px; 
            text-align: right;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .total-label { font-size: 1.1rem; color: var(--text-muted); }
        .total-amount { font-size: 2rem; font-weight: 900; color: var(--dark-blue); display: block; margin: 10px 0; }

        .empty-cart { text-align: center; padding: 60px; background: white; border-radius: 25px; }
        .empty-cart i { color: #eee; margin-bottom: 20px; }
    </style>
    
    <script>
    function updateQty(input, cartId) {
        let newQty = input.value;
        if (newQty < 1) {
            newQty = 1;
            input.value = 1;
        }
        // Átváltottunk cart_page.php-ra cart_actions helyett!
        window.location.href = `cart_page.php?update_qty=set&id=${cartId}&new_val=${newQty}`;
    }
    </script>
</head>
<body>
    <div class="container">
    <div class="header">
        <h1><i class="fas fa-shopping-basket" style="color: var(--accent-blue);"></i> Kosaram</h1>
        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Vissza a bolthoz</a>
    </div>

    <div class="cart-list">
        <?php if ($result->num_rows > 0): ?>
            <?php while($item = $result->fetch_assoc()): 
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
            ?>
                <div class="cart-item">
                    <div class="item-info">
                        <img src="uploads/<?= $item['picture'] ?: 'no_image.jpg' ?>" alt="termék">
                        <div class="item-details">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p><?= number_format($item['price'], 0, ',', ' ') ?> Ft / db</p>
                        </div>
                    </div>

                    <div class="quantity-controls">
                        <a href="cart_page.php?update_qty=minus&id=<?= $item['cart_item_id'] ?>" title="Csökkentés">
                            <i class="fas fa-minus-circle"></i>
                        </a>
                        
                        <input type="number" 
                               value="<?= $item['quantity'] ?>" 
                               class="qty-input" 
                               min="1" 
                               max="99" 
                               onchange="updateQty(this, <?= $item['cart_item_id'] ?>)">

                        <a href="cart_page.php?update_qty=plus&id=<?= $item['cart_item_id'] ?>" title="Növelés">
                            <i class="fas fa-plus-circle"></i>
                        </a>
                    </div>

                    <div class="subtotal">
                        <?= number_format($subtotal, 0, ',', ' ') ?> Ft
                    </div>

                    <a href="cart_page.php?remove=<?= $item['cart_item_id'] ?>" class="remove-link" onclick="return confirm('Biztosan eltávolítod ezt a terméket?')">
                        <i class="fas fa-times-circle"></i>
                    </a>
                </div>
            <?php endwhile; ?>

            <div class="cart-footer">
                <span class="total-label">Összesen:</span>
                <span class="total-amount"><?= number_format($total, 0, ',', ' ') ?> Ft</span>
            </div>

        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart fa-5x"></i>
                <h2>A kosarad jelenleg üres!</h2>
                <p>Nézz körül a webshopban, hátha megtetszik valami.</p>
                <a href="index.php" style="text-decoration:none; color:var(--accent-blue); font-weight:bold;">Vásárlás megkezdése</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>