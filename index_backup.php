<?php
session_start();
include "connect.php";

$role = $_SESSION['admin'] ?? null; 
$user_id = $_SESSION['user_id'] ?? 0;
$firm_id = $_SESSION['firm_id'] ?? 0;

// Név ÉS Profilkép lekérése az adatbázisból
$display_name = "";
$profile_pic = "default_user.png"; 
$bodyClass = '';

if (isset($_SESSION['admin']) && $_SESSION['admin'] === 'admin') {
    $bodyClass = 'role-admin'; // Admin -> Sötét
} elseif (isset($_SESSION['firm_id'])) {
    $bodyClass = 'role-firm';  // Cég -> Kék
} else {
    $bodyClass = 'role-user';  // Mezei user / Vendég -> Alap
}

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

    $categories = [];
    $type_query = $conn->query("SHOW COLUMNS FROM products LIKE 'type'");
    $type_row = $type_query->fetch_assoc();
    preg_match("/^enum\(\'(.*)\'\)$/", $type_row['Type'], $matches);
    $categories = explode("','", $matches[1]);

    $msg = $_GET['msg'] ?? null;

    $search_query = $_GET['search'] ?? '';
    $selected_cat = $_GET['cat'] ?? '';

    // Szűrési feltételek
    $where_clauses = ["p.active = 1", "p.approved = 1"];
    if ($selected_cat !== '') {
        $where_clauses[] = "p.type = '" . $conn->real_escape_string($selected_cat) . "'";
    }
    if ($search_query !== '') {
        $where_clauses[] = "(p.name LIKE '%" . $conn->real_escape_string($search_query) . "%' OR f.brand_name LIKE '%" . $conn->real_escape_string($search_query) . "%')";
    }
    $where_sql = implode(" AND ", $where_clauses);
    
        $limit = 15; 
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Összes sor kiszámolása a szűrőkkel (Lapozáshoz elengedhetetlen)
    $count_sql = "SELECT COUNT(*) as total FROM products p LEFT JOIN firm f ON p.firm_id = f.ID WHERE $where_sql";
    $count_res = $conn->query($count_sql);
    $totalRows = $count_res->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $limit);

    // Jóváhagyásra váró dolgok számolása az Adminnak
    $pending_count = 0;
    if (isset($_SESSION['admin']) && $_SESSION['admin'] === 'admin') {
        $q_firms = $conn->query("SELECT COUNT(*) as total FROM firm WHERE approved = 0");
        $q_prods = $conn->query("SELECT COUNT(*) as total FROM products WHERE approved = 0");
        
        $f_count = $q_firms->fetch_assoc()['total'] ?? 0;
        $p_count = $q_prods->fetch_assoc()['total'] ?? 0;
        
        $pending_count = $f_count + $p_count;
    }

    // Végleges SQL (LIMIT és OFFSET a legvégén!)
    $sql = "SELECT p.*, f.brand_name,
        (SELECT COUNT(*) FROM favorites WHERE user_id = $user_id AND product_id = p.ID) as is_fav,
        (SELECT COUNT(*) FROM shopping_list WHERE user_id = $user_id AND product_id = p.ID) as is_in_cart
        FROM products p
        LEFT JOIN firm f ON p.firm_id = f.ID
        WHERE $where_sql
        ORDER BY p.name ASC
        LIMIT $limit OFFSET $offset";

    $result = $conn->query($sql);

    function getPageUrl($p) {
        $params = $_GET;
        $params['page'] = $p;
        return "?" . http_build_query($params);
    }
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/Vizsgaremek/">
    <title>TermékVISION - Főoldal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- VÁLTOZÓK --- */
        :root { 
            --dark-blue: #1b263b; 
            --accent-blue: #00d2ff; 
            --light-bg: #f0f2f5;
            --white: #ffffff; 
            --success-green: #2ecc71; 
            --danger-red: #ff4757;
            --text-main: #333;
            --text-muted: #888888ff;
        }


        /* --- ALAP BEÁLLÍTÁSOK --- */
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: var(--light-bg); 
            margin: 0; 
            color: var(--text-main);
        }


        /* --- FEJLÉC SZERKEZET --- */
        header { 
            background: var(--dark-blue); 
            padding: 20px 5%; 
            color: white; 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .header-top { 
            display: flex; 
            justify-content: flex-end; 
            align-items: center; 
            margin-bottom: 20px; 
            gap: 20px; 
        }

        .header-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between; /* Elosztja a logót, keresőt és az ikonokat */
            gap: 20px;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* --- LOGO --- */
        .logo { 
            font-size: 1.8rem; 
            font-weight: 300;
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            letter-spacing: 0.5px;
            transition: 0.3s;
            white-space: nowrap; /* Nem engedi, hogy a logó két sorba törjön, még mobilon sem */
        }

        .logo span {
            color: var(--accent-blue);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 1.7rem;
            margin-left: 4px;
            letter-spacing: 1px;
        }

        .logo:hover {
            text-shadow: 0 0 15px rgba(0, 210, 255, 0.5);
            transform: translateY(-1px);
        }

        .header-content {
            display: flex;
            flex-wrap: wrap; /* Engedi, hogy ha nem fér el, törjön */
            justify-content: space-between;
            align-items: center;
        }


        /* --- KERESŐ ÉS KATEGÓRIA --- */
        .filter-form { 
            display: flex; 
            align-items: center; 
            gap: 20px; 
            flex-grow: 1; 
        }

        .search-capsule {
            background: rgba(255,255,255,0.1); 
            border-radius: 50px; 
            padding: 12px 25px; 
            display: flex; 
            align-items: center; 
            border: 1px solid var(--accent-blue); 
            flex-grow: 1;
        }

        .search-capsule input { 
            background: transparent; 
            border: none; 
            color: white; 
            width: 100%; 
            outline: none; 
            padding-left: 10px; 
            font-size: 1rem; 
        }

        .category-box { 
            background: rgba(255,255,255,0.15); 
            border: 1px solid var(--accent-blue); 
            border-radius: 15px; 
            padding: 8px 15px; 
            width: 80%;
            margin: 0 auto;
            max-width: 200px; 
        }

        .cat-label { 
            display: block; 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            color: var(--accent-blue); 
            margin-bottom: 2px;
            font-weight: bold;
        }

        .category-box select { 
            background: transparent; 
            border: none; 
            color: white; 
            font-size: 0.95rem; 
            outline: none; 
            width: 100%; 
            cursor: pointer; 
        }

        .category-box select option { 
            background: var(--dark-blue); 
            color: white; 
        }

        /* --- NAVIGÁCIÓS IKONOK --- */
        .main-nav { 
            display: flex; 
            gap: 30px; 
            align-items: center; 
        }

        .main-nav a { 
            color: white; 
            text-decoration: none; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            transition: 0.3s; 
        }

        .main-nav i { 
            font-size: 2rem; 
            margin-bottom: 5px; 
        }

        .main-nav span { 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }

        .main-nav a:hover { 
            color: var(--accent-blue); 
            transform: translateY(-3px);
        }

        .nav-icons-group {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        /* Profilkép kerete*/
        .nav-img-wrapper {
            width: 32px; height: 32px;
            border-radius: 50%;
            border: 2px solid var(--accent-blue);
            overflow: hidden; /* Hogy a kép ne lógjon ki a körből */
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
        }

        .nav-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Kitölti a kört torzítás nélkül */
        }

        /* --- FELHASZNÁLÓI INFÓ --- */
        .user-pill { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            background: rgba(255,255,255,0.1); 
            padding: 6px 18px; 
            border-radius: 50px; 
        }

        .user-pill img { 
            width: 32px; 
            height: 32px; 
            border-radius: 50%; 
            border: 2px solid var(--accent-blue); 
            object-fit: cover;
        }

        .logout-link {
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            opacity: 0.7;
            transition: 0.3s;
        }

        .logout-link:hover {
            opacity: 1;
            color: var(--danger-red);
        }


        /* --- TERMÉK GRID --- */
        .container { 
            width: 95%;
            max-width: 1800px; 
            margin: 50px auto; 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 35px; 
            padding: 0 25px; 
        }


        /* --- TERMÉKKÁRTYA --- */
        .card { 
            background: white; 
            border-radius: 20px; 
            padding: 20px; 
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
            position: relative;
            display: flex; 
            flex-direction: column;
        }

        .card:hover { 
            transform: translateY(-10px); 
            box-shadow: 0 15px 30px rgba(0,0,0,0.12);
        }

        .image-container { 
            height: 220px; width: 100%;
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin-bottom: 15px;
            overflow: hidden; /* Ha valami kilógna, vágja le */
            background: #fff;
            border-radius: 12px;
        }

        .image-container img { 
            max-width: 100%; 
            max-height: 100%; 
            object-fit: contain; 
            transition: transform 0.5s ease;
        }

        .icon-nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: white;
            transition: 0.3s;
            position: relative;
        }

        .icon-nav-link i {
            font-size: 1.6rem; /* Hasonló méret, mint a többi ikonod */
            margin-bottom: 4px;
        }

        .icon-nav-link span {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-content { 
            flex-grow: 1; 
            min-height: 80px;      /* Biztosítja, hogy a szövegnek mindig legyen helye */
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .card-content h4 { 
            margin: 0; 
            font-size: 1.2rem;
            color: var(--dark-blue);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .brand-text { 
            color: var(--text-muted); 
            font-size: 0.9rem; 
            margin: 8px 0; 
        }
        .brand-name {
            color: var(--accent-blue) !important; /* Kapjon egy kis neonos színt, hogy kitűnjön */
            font-weight: 600;
            opacity: 1; /* Ne legyen átlátszó */
        }

        .price-tag { 
            font-size: 1.4rem; 
            font-weight: 700; 
            color: var(--success-green);
            margin-top: 10px; /* Csak egy pici hely a szövegtől */
            margin-bottom: 5px; /* Szinte rátapad az elválasztó vonalra */
        }


        .card-actions {
            display: flex;
            justify-content: center; /* Középre igazítja az elemeket vízszintesen */
            align-items: center;     /* Középre igazítja az elemeket függőlegesen */
            gap: 40px;               /* Távolság a két ikon között */
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .card-actions a {
            text-decoration: none;
            color: #ddd;
        }

        .card-actions i { 
            font-size: 1.8rem; 
            transition: 0.3s; 
        }

        .card-actions a:hover i {
            transform: scale(1.2);
        }

        .active-fav { 
            color: var(--danger-red) !important; 
        }

        .active-cart { 
            color: var(--success-green) !important; 
        }


        /* --- PAGINATION --- */
        .pagination { 
            display: flex; 
            justify-content: center; 
            gap: 12px; 
            padding: 60px 0; 
        }

        .pagination a { 
            padding: 12px 22px; 
            border-radius: 14px; 
            text-decoration: none; 
            background: white; 
            color: var(--dark-blue); 
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: 0.3s;
        }

        .pagination a.active { 
            background: var(--dark-blue); 
            color: white; 
        }
        /* --- ALAP STÍLUS (USER) --- */
        body.role-user {
            background: #f0f2f5;
            color: #333;
        }

        /* --- CÉGES STÍLUS (KÉK) --- */
        body.role-firm {
            background: #e3f2fd; /* Világoskék háttér */
        }
        body.role-firm .navbar, body.role-firm .header {
            background: #1976d2 !important; /* Céges kék fejléc */
        }

        /* --- ADMIN STÍLUS (SÖTÉT) --- */
        body.role-admin {
            background: #121212; /* Éjfekete háttér */
            color: #e0e0e0;
        }
        body.role-admin .navbar, body.role-admin .header {
            background: #000000 !important; /* Fekete fejléc */
            border-bottom: 2px solid #00d2ff; /* Egy kis neon kék csík */
        }
        body.role-admin .cart-item, body.role-admin .login-box {
            background: #1e1e1e;
            border: 1px solid #333;
            color: white;
        }
        .nav-profile-img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid var(--accent-blue);
            object-fit: cover;
            margin-bottom: 4px;
        }

        .icon-nav-link:hover {
            color: var(--accent-blue);
            transform: translateY(-2px);
        }

        .logout-icon:hover {
            color: var(--danger-red);
        }
        .approval-link {
            color: var(--success-green) !important;
            text-shadow: 0 0 8px rgba(46, 204, 113, 0.4);
        }

        .approval-link:hover {
            color: white !important;
            text-shadow: 0 0 15px var(--success-green);
        }

        /* Az Admin pajzs maradjon kék */
        .admin-nav-link {
            color: var(--accent-blue) !important;
        }

        /* Badge (számláló) pozícionálása a Jóváhagyásokon */
        .approval-link .badge {
            position: absolute;
            top: -2px;
            right: 5px;
            background: var(--danger-red);
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 10px;
            border: 2px solid var(--dark-blue);
        }
        .pagination {
            grid-column: 1 / -1; /* Ez kényszeríti a lapozót, hogy az összes oszlopot átfogja */
            display: flex;
            justify-content: center;
            gap: 12px;
            padding: 40px 0;
        }
        /* MOBILOS NÉZET (720px alatt) */
        @media (max-width: 720px) {
            .logo { 
                font-size: 1.3rem; 
                width: auto; /* Itt vedd ki a 100%-ot! */
                margin-bottom: 0;
                text-align: left;
            }
            .logo span {
                font-size: 1.3rem;
            }

            /* Biztosítjuk, hogy a logó és a profil ikonok egy vonalban maradjanak felül */
            .header-content {
                display: flex;
                flex-direction: row; /* Vízszintesen maradnak */
                flex-wrap: wrap; /* De a kereső alájuk törhet */
                justify-content: space-between;
                align-items: center;
            }

            .header-bottom {
                width: 100%;
            }

            /* A logó és a profil/ikonok egymás mellett legyenek mobilon is a tetején */
            header {
                padding: 15px 3%;
            }

            /* Kereső és Szűrő egy sorban */
            .filter-form {
                order: 3; /* Ez kerül legalulra a fejlécen belül */
                width: 100%;
                margin-top: 10px;
            }

            .search-capsule {
                flex-grow: 2;
                padding: 8px 15px;
            }

            /* A kategória választó tölcsér ikonossá tétele */
            .category-box {
                width: 50px; /* Csak az ikonnak és a nyílnak */
                min-width: 50px;
                padding: 8px;
                position: relative;
                display: flex;
                justify-content: center;
                background: var(--accent-blue); /* Kiemeljük, hogy látszódjon gombként */
            }

            /* Eltüntetjük a szöveget, csak az ikont hagyjuk (trükk) */
            .category-box select {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                opacity: 0; /* Láthatatlan, de kattintható */
                z-index: 2;
            }

            /* A tölcsér ikon megjelenítése a select mögött */
            .category-box::before {
                content: '\f0b0'; /* FontAwesome tölcsér kódja */
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                color: white;
                font-size: 1.2rem;
            }

            .cat-label { display: none; } /* Mobilon felesleges a felirat */

            /* --- TERMÉK GRID: 2 TERMÉK EGY SORBAN --- */
            .container {
                grid-template-columns: repeat(2, 1fr); /* Ez teszi 2 oszlopossá */
                gap: 15px; /* Kisebb hely mobilon */
                padding: 15px;
                margin-top: 20px;
            }

            .card {
                padding: 10px;
                border-radius: 12px;
            }

            .image-container {
                height: 140px; /* Kisebb kép mobilon */
            }

            .card-content h4 {
                font-size: 0.95rem; /* Kisebb betű, hogy elférjen */
            }

            .price-tag {
                font-size: 1.1rem;
            }

            .card-actions {
                gap: 20px; /* Szűkebb hely az ikonoknak */
            }

            .card-actions i {
                font-size: 1.4rem;
            }
            
            /* Navigációs ikonok kicsinyítése, hogy elférjenek */
            .nav-icons-group {
                gap: 15px;
            }
            .icon-nav-link span {
                display: none; /* Mobilon csak az ikonok maradjanak, a szöveg nem fér el */
            }
        }
    </style>

</head>
<body class="<?= $bodyClass ?>">

<header>
    <div class="header-content">
        <a href="index.php" class="logo">Termék<span>Vision</span></a>
        <form action="index.php" method="GET" class="filter-form">
            <div class="search-capsule">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Keresés a termékek között..." value="<?= htmlspecialchars($search_query) ?>">
            </div>

            <div class="category-box">
                <select name="cat" onchange="this.form.submit()">
                    <option value="">Összes kategória</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($selected_cat == $cat) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <div class="nav-icons-group">
            <?php if ($user_id || $firm_id): ?>
                
                <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] === 'admin'): ?>
                    <a href="admin.php" class="icon-nav-link approval-link">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Jóváhagyások</span>
                        <?php if($pending_count > 0): ?>
                            <span class="badge"><?= $pending_count ?></span>
                        <?php endif; ?>
                    </a>

                <?php elseif (isset($_SESSION['firm_id'])): ?>
                    <a href="firm_dashboard.php" class="icon-nav-link" style="color: var(--accent-blue);">
                        <i class="fas fa-barcode"></i>
                        <span>Termékkezelés</span>
                    </a>

                <?php else: ?>
                    <a href="favorites.php" class="icon-nav-link">
                        <i class="fas fa-heart"></i>
                        <span>Kedvencek</span>
                    </a>

                    <a href="cart_page.php" class="icon-nav-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Kosár</span>
                    </a>
                <?php endif; ?>

                <a href="profile.php" class="icon-nav-link">
                    <div class="nav-img-wrapper">
                        <img src="uploads/profiles/<?= $profile_pic ?>" alt="P">
                    </div>
                    <span><?= htmlspecialchars($display_name) ?></span>
                </a>

                <a href="logout.php" class="icon-nav-link logout-icon">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Kilépés</span>
                </a>

            <?php else: ?>
                <a href="login.php" class="icon-nav-link">
                    <i class="fas fa-user"></i>
                    <span>Bejelentkezés</span>
                </a>
                <a href="register.php" class="icon-nav-link">
                    <i class="fas fa-user-plus"></i>
                    <span>Regisztráció</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php
$cat_res = $conn->query("SELECT 'type' FROM products ORDER BY name ASC");
?>
<div class="container">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <?php 
                $firm_name = $row['brand_name'] ?: 'sajat-termek';
                $url_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $firm_name)));
                $friendly_url = "shop/" . $url_slug . "/" . $row['ID'];
            ?>
            <div class="card">
                <a href="<?= $friendly_url ?>" class="image-container">
                    <img src="uploads/<?= $row['picture'] ?: 'no_image.jpg' ?>" alt="termék">
                </a>

                <div class="card-content">
                    <h4><?= htmlspecialchars($row['name']) ?></h4>
                    <p class="brand-name"><?= htmlspecialchars($row['brand_name'] ?: 'Saját termék') ?></p>
                </div>

                <div class="price-tag">
                    <?= number_format($row['price'], 0, ',', ' ') ?> Ft
                </div>

                <div class="card-actions">
                    <div class="action-icons">
                        <?php if ($row['is_fav'] > 0): ?>
                            <a href="cart_actions.php?remove_fav=<?= $row['ID'] ?>"><i class="fa-solid fa-heart" style="color: #ff4757;"></i></a>
                        <?php else: ?>
                            <a href="cart_actions.php?add_to_fav=<?= $row['ID'] ?>"><i class="fa-regular fa-heart"></i></a>
                        <?php endif; ?>
                    </div>

                    <?php if ($row['is_in_cart'] > 0): ?>
                        <a href="cart_page.php"><i class="fa-solid fa-cart-shopping" style="color: #2ecc71;"></i></a>
                    <?php else: ?>
                        <a href="cart_actions.php?add_to_cart=<?= $row['ID'] ?>"><i class="fa-solid fa-cart-plus"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-result">Sajnos nem találtunk ilyen terméket.</div>
    <?php endif; ?>
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?= getPageUrl($i) ?>" class="<?= ($page == $i) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
</body>
</html>