<?php
session_start();
include "connect.php";

// JAVÍTÁS: $_SESSION['admin']-t használunk, mert az adatbázisban is az van
$role = $_SESSION['admin'] ?? null; 
$user_id = $_SESSION['user_id'] ?? null;
$firm_id = $_SESSION['firm_id'] ?? null;
// ... a keresési logika marad ugyanaz ...

$msg = $_GET['msg'] ?? null;

// Keresés logika (ha van beírt szöveg)
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $sql = "SELECT p.*, f.brand_name FROM products p 
            LEFT JOIN firm f ON p.firm_id = f.ID 
            WHERE p.active = 1 AND p.approved = 1 AND (p.name LIKE ? OR f.brand_name LIKE ?)";
    $stmt = $conn->prepare($sql);
    $term = "%$search_query%";
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT p.*, f.brand_name FROM products p LEFT JOIN firm f ON p.firm_id = f.ID WHERE p.active = 1 AND p.approved = 1";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SzuperShop - Főoldal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --success: #27ae60; --danger: #e74c3c; }
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; margin: 0; }
        
        /* FEJLÉC SZERKEZETE */
        header { 
            background: var(--primary); 
            color: white; 
            padding: 0.8rem 5%; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.2); 
        }

        .logo { font-size: 1.5rem; font-weight: bold; display: flex; align-items: center; gap: 10px; min-width: 150px; }
        
        /* KERESŐ DOBOZ */
        .search-container { flex-grow: 1; display: flex; justify-content: center; padding: 0 20px; }
        .search-box { position: relative; width: 100%; max-width: 500px; }
        .search-box input { 
            width: 100%; 
            padding: 10px 15px 10px 40px; 
            border-radius: 25px; 
            border: none; 
            outline: none; 
            font-size: 0.9rem;
        }
        .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #7f8c8d; }

        /* NAVIGÁCIÓ */
        nav { display: flex; gap: 20px; align-items: center; min-width: 150px; justify-content: flex-end; }
        nav a { color: white; text-decoration: none; font-size: 1.2rem; transition: 0.3s; position: relative; }
        nav a:hover { color: var(--accent); }
        nav a span { font-size: 0.7rem; display: block; text-align: center; }

        /* Termék kártyák (maradt a korábbi) */
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; }
        .card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); text-align: center; }
        .card img { width: 100%; height: 180px; object-fit: contain; }
        .price-tag { font-size: 1.3rem; font-weight: bold; color: var(--success); margin: 10px 0; }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <i class="fas fa-shopping-bag"></i> SzuperShop
    </div>

    <div class="search-container">
        <form action="index.php" method="GET" class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Keresés termékek között..." value="<?= htmlspecialchars($search_query) ?>">
        </form>
    </div>

    <nav>
        <?php if($user_id || $firm_id): ?>
            <?php if($role === 'admin'): ?>
                <a href="admin.php" title="Admin"><i class="fas fa-user-shield"></i></a>
            <?php endif; ?>
            
            <?php if(!$firm_id): ?>
                <a href="favorites.php" title="Kedvencek"><i class="fas fa-heart"></i></a>
                <a href="cart_page.php" title="Kosár"><i class="fas fa-shopping-cart"></i></a>
            <?php else: ?>
                <a href="firm_dashboard.php" title="Irodám"><i class="fas fa-store"></i></a>
            <?php endif; ?>
            
            <a href="logout.php" title="Kilépés"><i class="fas fa-sign-out-alt"></i></a>
        <?php else: ?>
            <a href="login.php" title="Bejelentkezés"><i class="fas fa-user"></i><span>Belépés</span></a>
            <a href="register.php" title="Regisztráció"><i class="fas fa-user-plus"></i><span>Regisztráció</span></a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <div class="card">
            <img src="uploads/<?= $row['picture'] ?: 'no_image.png' ?>" alt="termék">
            <h4><?= htmlspecialchars($row['name']) ?></h4>
            <p style="font-size: 0.8rem; color: #7f8c8d;"><?= htmlspecialchars($row['brand_name'] ?: 'Saját termék') ?></p>
            <div class="price-tag"><?= number_format($row['price'], 0, ',', ' ') ?> Ft</div>
            
            <?php if($role === 'user'): ?>
                <div style="display:flex; gap:10px; margin-top:15px;">
                    <a href="cart_actions.php?add_to_cart=<?= $row['ID'] ?>&price=<?= $row['price'] ?>" 
                       style="flex:1; background:var(--primary); color:white; padding:10px; border-radius:8px; text-decoration:none;">
                       <i class="fas fa-cart-plus"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; grid-column: 1/-1;">Nincs a keresésnek megfelelő termék.</p>
    <?php endif; ?>
</div>

</body>
</html>