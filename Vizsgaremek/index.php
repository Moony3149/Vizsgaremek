<?php
session_start();
include "connect.php";

$role = $_SESSION['admin'] ?? null; 
$user_id = $_SESSION['user_id'] ?? null;
$firm_id = $_SESSION['firm_id'] ?? null;

// Név ÉS Profilkép lekérése az adatbázisból
$display_name = "";
$profile_pic = "default_user.png"; 

if ($user_id) {
    $stmt_name = $conn->prepare("SELECT username, profile_pic FROM users WHERE ID = ?");
    $stmt_name->bind_param("i", $user_id);
    $stmt_name->execute();
    $res_name = $stmt_name->get_result();
    if ($row_name = $res_name->fetch_assoc()) {
        $display_name = $row_name['username'];
        $profile_pic = $row_name['profile_pic'] ?: 'default_user.png';
    }
} elseif ($firm_id) {
    $stmt_name = $conn->prepare("SELECT brand_name, profile_pic FROM firm WHERE ID = ?");
    $stmt_name->bind_param("i", $firm_id);
    $stmt_name->execute();
    $res_name = $stmt_name->get_result();
    if ($row_name = $res_name->fetch_assoc()) {
        $display_name = $row_name['brand_name'];
        $profile_pic = $row_name['profile_pic'] ?: 'default_firm.png';
    }
}

$msg = $_GET['msg'] ?? null;

// Keresés logika
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

        nav { display: flex; gap: 20px; align-items: center; min-width: 150px; justify-content: flex-end; }
        nav a { color: white; text-decoration: none; font-size: 1.2rem; transition: 0.3s; position: relative; display: flex; flex-direction: column; align-items: center; }
        nav a:hover { color: var(--accent); }
        nav a span { font-size: 0.7rem; display: block; text-align: center; margin-top: 2px; }

        /* Profil rész a nav-ban */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.1);
            padding: 5px 15px 5px 5px;
            border-radius: 25px;
            border: 1px solid rgba(52, 152, 219, 0.2);
            margin-right: 10px;
        }
        .nav-profile-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent);
        }
        .user-name {
            font-size: 0.85rem;
            font-weight: 500;
            color: #fff;
        }

        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; }
        .card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); text-align: center; }
        .card img { width: 100%; height: 180px; object-fit: contain; }
        .price-tag { font-size: 1.3rem; font-weight: bold; color: var(--success); margin: 10px 0; }
        .btn-cart { display: block; background: var(--primary); color: white; padding: 10px; border-radius: 8px; text-decoration: none; margin-top: 15px; }
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
        <a href="profile.php" class="user-profile" title="Profil szerkesztése" style="flex-direction: row; gap: 10px;">
            <img src="uploads/profiles/<?= $profile_pic ?>" class="nav-profile-img" alt="Profil">
            <span class="user-name" style="display: inline; font-size: 0.9rem;"><?= htmlspecialchars($display_name) ?></span>
        </a>

        <?php if($role === 'admin'): ?>
            <a href="admin.php" title="Adminisztráció"><i class="fas fa-user-shield"></i><span>Admin</span></a>
        <?php endif; ?>
        
        <?php if(!$firm_id): ?>
            <a href="favorites.php" title="Kedvencek"><i class="fas fa-heart"></i><span>Kedvenc</span></a>
            <a href="cart_page.php" title="Kosár"><i class="fas fa-shopping-cart"></i><span>Kosár</span></a>
        <?php else: ?>
            <a href="firm_dashboard.php" title="Irodám"><i class="fas fa-store"></i><span>Irodám</span></a>
        <?php endif; ?>
        
        <a href="logout.php" title="Kilépés" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i><span>Kilépés</span></a>
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
                <a href="cart_actions.php?add_to_cart=<?= $row['ID'] ?>&price=<?= $row['price'] ?>" class="btn-cart">
                    <i class="fas fa-cart-plus"></i> Kosárba
                </a>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; grid-column: 1/-1;">Nincs a keresésnek megfelelő termék.</p>
    <?php endif; ?>
</div>

</body>
</html>