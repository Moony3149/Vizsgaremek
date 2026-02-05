<?php
session_start();
include "connect.php";

$role = $_SESSION['admin'] ?? null; 
$user_id = $_SESSION['user_id'] ?? null;
$firm_id = $_SESSION['firm_id'] ?? null;

// ID ellenőrzése
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = intval($_GET['id']);

// Termék lekérése biztonságosan (Prepared Statement)
$sql = "SELECT p.*, f.brand_name 
        FROM products p 
        LEFT JOIN firm f ON p.firm_id = f.ID 
        WHERE p.ID = ? AND p.active = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// Ha nincs ilyen termék
if (!$product) {
    echo "A termék nem található vagy nem aktív.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Részletek</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --success: #27ae60; }
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; margin: 0; color: #333; }
        
        /* Egyszerű fejléc a visszalépéshez */
        header { background: var(--primary); padding: 15px; color: white; display: flex; align-items: center; }
        header a { color: white; text-decoration: none; display: flex; align-items: center; gap: 5px; font-weight: bold; }
        header a:hover { text-decoration: underline; }

        .details-container {
            max-width: 1000px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .product-image img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #eee;
        }

        .product-info h1 { margin-top: 0; color: var(--primary); font-size: 2rem; }
        .brand { color: #7f8c8d; font-style: italic; margin-bottom: 20px; display: block; }
        
        .price { 
            font-size: 2rem; 
            color: var(--success); 
            font-weight: bold; 
            margin: 20px 0; 
        }

        .description { line-height: 1.6; color: #555; margin-bottom: 30px; }
        
        .meta-info {
            background: #f1f2f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .btn-cart-lg {
            display: inline-block;
            background: var(--accent);
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1.2rem;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-cart-lg:hover { background: #2980b9; }

        @media (max-width: 768px) {
            .details-container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header>
    <a href="index.php"><i class="fas fa-arrow-left"></i> Vissza a főoldalra</a>
</header>

<div class="details-container">
    <div class="product-image">
        <img src="uploads/<?= $product['picture'] ?: 'no_image.jpg' ?>" alt="<?= htmlspecialchars($product['name']) ?>">
    </div>

    <div class="product-info">
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <span class="brand">Gyártó/Eladó: <?= htmlspecialchars($product['brand_name'] ?: 'Saját termék') ?></span>
        
        <div class="price"><?= number_format($product['price'], 0, ',', ' ') ?> Ft</div>

        <div class="meta-info">
            <strong>Kategória:</strong> <?= htmlspecialchars($product['type']) ?><br>
            <strong>Készleten:</strong> <?= $product['amount'] ?> db
        </div>

        <h3>Leírás</h3>
        <p class="description">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
        </p>

        <?php if($role === 'user'): ?>
            <a href="cart_actions.php?add_to_cart=<?= $product['ID'] ?>&price=<?= $product['price'] ?>" class="btn-cart-lg">
                <i class="fas fa-cart-plus"></i> Kosárba teszem
            </a>
        <?php elseif(!$role): ?>
            <p style="color: red;">Kedvenc termékek jelöléséhez kérjük <a href="login.php">jelentkezzen be</a>!</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>