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

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        /* --- HEADER STÍLUS --- */
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 40px; 
            background: var(--dark-blue);
            padding: 20px 30px;
            border-radius: 20px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .header h1 { margin: 0; font-size: 1.8rem; display: flex; align-items: center; gap: 15px; }
        .back-link { 
            text-decoration: none; 
            color: var(--accent-blue); 
            font-weight: bold; 
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .back-link:hover { color: white; transform: translateX(-5px); }

        /* --- GRID ÉS KÁRTYÁK --- */
        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 30px; 
        }

        .card { 
            background: white; 
            padding: 20px; 
            border-radius: 20px; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.05); 
            position: relative; 
            transition: 0.3s ease; 
            display: flex; 
            flex-direction: column; 
        }

        .card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.12); }

        /* --- EGYSÉGES KÉPMÉRET --- */
        .image-container { 
            height: 200px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin-bottom: 15px;
            overflow: hidden;
        }
        .image-container img { 
            max-width: 100%; 
            max-height: 100%; 
            object-fit: contain; 
            transition: 0.5s;
        }
        .card:hover .image-container img { transform: scale(1.1); }

        .card h3 { 
            margin: 10px 0 5px; 
            color: var(--dark-blue); 
            font-size: 1.15rem; 
            min-height: 2.3rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .card-details { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin: 15px 0; 
            border-top: 1px solid #eee; 
            padding-top: 15px; 
        }
        .brand-name { font-size: 0.85rem; color: var(--text-muted); margin: 0; }
        .price { color: var(--success-green); font-weight: 700; font-size: 1.2rem; margin: 0; }
        
        /* --- GOMBOK --- */
        .remove-btn { 
            position: absolute; 
            top: 15px; 
            right: 15px; 
            background: var(--white); 
            color: var(--danger-red); 
            border: 1px solid #eee;
            width: 35px;
            height: 35px;
            border-radius: 50%; 
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer; 
            text-decoration: none; 
            z-index: 10; 
            transition: 0.3s; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .remove-btn:hover { background: var(--danger-red); color: white; transform: rotate(90deg); }
        
        .view-btn { 
            display: block; 
            background: var(--dark-blue); 
            color: white; 
            padding: 12px; 
            border-radius: 12px; 
            text-decoration: none; 
            text-align: center; 
            transition: 0.3s; 
            font-weight: bold;
            margin-top: auto; 
        }
        .view-btn:hover { background: var(--accent-blue); }
        
        /* --- ÜRES ÁLLAPOT --- */
        .empty-msg { 
            text-align: center; 
            padding: 80px 20px; 
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .empty-msg i { color: #ddd; margin-bottom: 20px; }
        .empty-msg h2 { color: var(--dark-blue); }
        .empty-msg p { color: var(--text-muted); margin-bottom: 30px; }
        
        .browse-btn {
            display: inline-block;
            background: var(--accent-blue);
            color: var(--dark-blue);
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .browse-btn:hover { background: var(--dark-blue); color: white; }

    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-heart" style="color: var(--accent-blue);"></i> Kedvenceim</h1>
        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Vissza a bolthoz</a>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="grid">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <a href="favorites.php?remove=<?= $row['ID'] ?>" class="remove-btn" title="Eltávolítás">
                        <i class="fas fa-times"></i>
                    </a>
                    
                    <a href="description.php?id=<?= $row['ID'] ?>" class="image-container">
                        <img src="uploads/<?= $row['picture'] ?: 'no_image.jpg' ?>" alt="termék">
                    </a>

                    <h3><?= htmlspecialchars($row['name']) ?></h3>
    
                    <div class="card-details">
                        <p class="brand-name"><?= htmlspecialchars($row['brand_name'] ?: 'Saját termék') ?></p>
                        <p class="price"><?= number_format($row['price'], 0, ',', ' ') ?> Ft</p>
                    </div>
                    
                    <a href="description.php?id=<?= $row['ID'] ?>" class="view-btn">Termék megtekintése</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-msg">
            <i class="far fa-heart fa-5x"></i>
            <h2>A listád még üres...</h2>
            <p>Még nem jelöltél meg egyetlen terméket sem kedvencként.</p>
            <a href="index.php" class="browse-btn">Böngészés a termékek között</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>