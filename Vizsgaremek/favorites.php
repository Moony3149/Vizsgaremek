<?php
session_start();
include "connect.php";

// Csak bejelentkezett felhasználó láthatja
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Termék eltávolítása a kedvencekből
if (isset($_GET['remove'])) {
    $p_id = intval($_GET['remove']);
    $conn->query("DELETE FROM favorites WHERE user_id = $user_id AND product_id = $p_id");
    header("Location: favorites.php");
    exit;
}

// Kedvenc termékek lekérése adatokkal együtt
$sql = "SELECT p.* FROM products p 
        INNER JOIN favorites f ON p.ID = f.product_id 
        WHERE f.user_id = $user_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Kedvenceim - SzuperShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; }
        .header h1 { color: #2c3e50; margin: 0; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .card { background: white; padding: 15px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); position: relative; transition: 0.3s; }
        .card:hover { transform: translateY(-5px); }
        .card img { width: 100%; hieght: 180px; object-fit: cover; border-radius: 8px; }
        .card h3 { margin: 15px 0 5px; color: #333; }
        .card .price { color: #27ae60; font-weight: bold; font-size: 1.2rem; }
        
        .remove-btn { position: absolute; top: 10px; right: 10px; background: rgba(231, 76, 60, 0.9); color: white; border: none; padding: 8px; border-radius: 50%; cursor: pointer; transition: 0.3s; }
        .remove-btn:hover { background: #c0392b; transform: scale(1.1); }
        
        .empty-msg { text-align: center; padding: 50px; color: #7f8c8d; }
        .back-link { text-decoration: none; color: #3498db; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-heart" style="color: #e74c3c;"></i> Kedvenceim</h1>
        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Vissza a vásárláshoz</a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="grid">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <a href="favorites.php?remove=<?= $row['ID'] ?>" class="remove-btn" title="Eltávolítás">
                        <i class="fas fa-trash"></i>
                    </a>
                    <img src="img/<?= $row['image'] ?>" alt="<?= $row['name'] ?>">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p class="price"><?= number_format($row['price'], 0, ',', ' ') ?> Ft</p>
                    <a href="product_details.php?id=<?= $row['ID'] ?>" style="display:block; text-align:center; text-decoration:none; background:#3498db; color:white; padding:10px; border-radius:6px; margin-top:10px;">Megtekintés</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-msg">
            <i class="far fa-heart fa-4x"></i>
            <h2>Még nincsenek kedvenc termékeid.</h2>
            <p>Böngéssz a termékek között, és kattints a szív ikonra!</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>