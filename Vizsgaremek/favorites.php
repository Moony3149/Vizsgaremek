<?php
session_start();
include "connect.php";

// Csak bejelentkezett felhasználó láthatja
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Termék eltávolítása a kedvencekből
if (isset($_GET['remove'])) {
    $p_id = intval($_GET['remove']);
    $conn->query("DELETE FROM favorites WHERE user_id = $user_id AND product_id = $p_id");
    header("Location: favorites.php");
    exit;
}

// Kedvenc termékek lekérése adatokkal együtt
$sql = "SELECT p.*, f_table.brand_name 
        FROM products p 
        INNER JOIN favorites fav ON p.ID = fav.product_id 
        LEFT JOIN firm f_table ON p.firm_id = f_table.ID
        WHERE fav.user_id = $user_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Kedvenceim - SzuperShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --danger: #e74c3c; --success: #27ae60; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid var(--primary); padding-bottom: 10px; }
        .header h1 { color: var(--primary); margin: 0; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .card { background: white; padding: 15px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); position: relative; transition: 0.3s; display: flex; flex-direction: column; }
        .card:hover { transform: translateY(-5px); }
        .card img { width: 100%; height: 180px; object-fit: contain; border-radius: 8px; } /* Itt javítva a height! */
        .card h3 { margin: 15px 0 5px; color: #333; font-size: 1.1rem; }
        
        /* Ár és márka elrendezése */
        .card-details { display: flex; justify-content: space-between; align-items: center; margin: 15px 0; border-top: 1px solid #eee; padding-top: 10px; }
        .brand-name { font-size: 0.8rem; color: #7f8c8d; margin: 0; }
        .price { color: var(--success); font-weight: bold; font-size: 1.1rem; margin: 0; }
        
        .remove-btn { position: absolute; top: 10px; right: 10px; background: var(--danger); color: white; border: none; padding: 8px; border-radius: 50%; cursor: pointer; text-decoration: none; font-size: 0.8rem; z-index: 10; transition: 0.2s; }
        .remove-btn:hover { transform: scale(1.1); background: #c0392b; }
        
        .view-btn { display: block; background: var(--accent); color: white; padding: 10px; border-radius: 8px; text-decoration: none; text-align: center; transition: 0.3s; margin-top: auto; }
        .view-btn:hover { background: #2980b9; }
        
        .empty-msg { text-align: center; padding: 100px 20px; color: #7f8c8d; }
        .back-link { text-decoration: none; color: var(--accent); font-weight: bold; display: flex; align-items: center; gap: 5px; }

        .far.fa-heart.fa-4x { margin-bottom: 20px; opacity: 0.3; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-heart" style="color: var(--danger);"></i> Kedvenceim</h1>
        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Vissza a főoldalra</a>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="grid">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <a href="favorites.php?remove=<?= $row['ID'] ?>" class="remove-btn" title="Eltávolítás">
                        <i class="fas fa-trash"></i>
                    </a>
                    <a href="description.php?id=<?= $row['ID'] ?>" style="text-decoration:none; color:inherit;">
                    <img src="uploads/<?= $row['picture'] ?: 'no_image.jpg' ?>" alt="termék">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    </a>
    
                    <div class="card-details">
                    <p class="brand-name"><?= htmlspecialchars($row['brand_name'] ?: 'Saját termék') ?></p>
                    <p class="price"><?= number_format($row['price'], 0, ',', ' ') ?> Ft</p>
                    </div>
                    <a href="description.php?id=<?= $row['ID'] ?>" class="view-btn">Megtekintés</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-msg">
            <i class="far fa-heart fa-4x"></i>
            <h2>Még nincsenek kedvenc termékeid.</h2>
            <p>Böngéssz a termékek között, és gyűjtsd össze, ami tetszik!</p>
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Vissza a főoldalra</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>