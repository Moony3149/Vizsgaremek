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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: var(--light-bg); 
            margin: 0; 
            color: var(--text-main);
            padding-bottom: 50px;
        }

        .container { 
            max-width: 1200px; 
            margin: 20px auto; 
            padding: 0 15px; 
        }

        /* --- HEADER STÍLUS --- */
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
            background: var(--dark-blue);
            padding: 15px 25px;
            border-radius: 15px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .header h1 { margin: 0; font-size: 1.5rem; display: flex; align-items: center; gap: 12px; }
        .back-link { 
            text-decoration: none; 
            color: var(--accent-blue); 
            font-weight: bold; 
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        .back-link:hover { color: white; transform: translateX(-3px); }

        /* --- GRID ÉS KÁRTYÁK --- */
        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); 
            gap: 20px; 
        }

        .card { 
            background: white; 
            padding: 15px; 
            border-radius: 18px; 
            box-shadow: 0 6px 12px rgba(0,0,0,0.05); 
            position: relative; 
            transition: 0.3s ease; 
            display: flex; 
            flex-direction: column; 
        }

        .card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }

        /* --- EGYSÉGES KÉPMÉRET --- */
        .image-container { 
            height: 180px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin-bottom: 12px;
            overflow: hidden;
            background: #fafafa;
            border-radius: 12px;
        }
        .image-container img { 
            max-width: 90%; 
            max-height: 90%; 
            object-fit: contain; 
            transition: 0.5s;
        }

        .card h3 { 
            margin: 5px 0; 
            color: var(--dark-blue); 
            font-size: 1rem; 
            min-height: 2.6rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.3;
        }
        
        .card-details { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin: 10px 0; 
            border-top: 1px solid #f0f0f0; 
            padding-top: 10px; 
        }
        .brand-name { font-size: 0.8rem; color: var(--text-muted); margin: 0; }
        .price { color: var(--success-green); font-weight: 700; font-size: 1.1rem; margin: 0; }
        
        /* --- GOMBOK --- */
        .remove-btn { 
            position: absolute; 
            top: 10px; 
            right: 10px; 
            background: rgba(255,255,255,0.9); 
            color: var(--danger-red); 
            border: 1px solid #eee;
            width: 32px;
            height: 32px;
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
        .remove-btn:hover { background: var(--danger-red); color: white; }
        
        .view-btn { 
            display: block; 
            background: var(--dark-blue); 
            color: white; 
            padding: 10px; 
            border-radius: 10px; 
            text-decoration: none; 
            text-align: center; 
            transition: 0.3s; 
            font-weight: bold;
            margin-top: auto; 
            font-size: 0.9rem;
        }
        .view-btn:hover { background: var(--accent-blue); color: var(--dark-blue); }
        
        /* --- ÜRES ÁLLAPOT --- */
        .empty-msg { 
            text-align: center; 
            padding: 60px 20px; 
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .empty-msg i { color: #eee; margin-bottom: 15px; }
        
        /* --- MOBIL NÉZET (MEDIA QUERIES) --- */
        @media (max-width: 600px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                padding: 20px;
            }
            .header h1 { font-size: 1.3rem; }
            .grid {
                grid-template-columns: 1fr; /* Mobilra egyetlen oszlop */
                gap: 15px;
            }
            .image-container { height: 220px; } /* Mobilon picit nagyobb kép, hogy kitöltse a szélességet */
            .container { margin: 10px auto; }
        }

        @media (min-width: 601px) and (max-width: 900px) {
            .grid { grid-template-columns: repeat(2, 1fr); } /* Tableten 2 oszlop */
        }

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
            <i class="far fa-heart fa-4x"></i>
            <h2>A listád még üres...</h2>
            <p>Még nem jelöltél meg egyetlen terméket sem kedvencként.</p>
            <br>
            <a href="index.php" style="text-decoration:none; background: var(--accent-blue); color: var(--dark-blue); padding: 12px 25px; border-radius: 50px; font-weight: bold;">Böngészés a termékek között</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>