<?php
session_start();
include "connect.php";

$role = $_SESSION['admin'] ?? null; 
$user_id = $_SESSION['user_id'] ?? 0;
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
    
        $limit = 8; 
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Összes sor kiszámolása a szűrőkkel (Lapozáshoz elengedhetetlen)
    $count_sql = "SELECT COUNT(*) as total FROM products p LEFT JOIN firm f ON p.firm_id = f.ID WHERE $where_sql";
    $count_res = $conn->query($count_sql);
    $totalRows = $count_res->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $limit);

    // Végleges SQL (LIMIT és OFFSET a legvégén!)
    $sql = "SELECT p.*, f.brand_name,
        (SELECT COUNT(*) FROM favorites WHERE user_id = $user_id AND product_id = p.ID) as is_fav,
        (SELECT COUNT(*) FROM shopping_list WHERE user_id = $user_id AND product_id = p.ID) as is_in_cart
        FROM products p
        LEFT JOIN firm f ON p.firm_id = f.ID
        WHERE $where_sql
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
    <title>SzuperShop - Főoldal</title>
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
            --text-muted: #888;
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
            gap: 30px; 
        }


        /* --- LOGO --- */
        .logo { 
            font-size: 2.2rem; 
            font-weight: 900; 
            color: white; 
            text-decoration: none; 
            white-space: nowrap;
        }

        .logo span { 
            color: var(--accent-blue); 
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
            min-width: 200px; 
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
            max-width: 1300px; 
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
            height: 200px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin-bottom: 20px; 
        }

        .image-container img { 
            max-width: 100%; 
            max-height: 100%; 
            object-fit: contain; 
        }

        .card-content { 
            flex-grow: 1; 
        }

        .card-content h4 { 
            margin: 0; 
            font-size: 1.2rem;
            color: var(--dark-blue);
        }

        .brand-text { 
            color: var(--text-muted); 
            font-size: 0.9rem; 
            margin: 8px 0; 
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
    </style>

</head>
<body>

<header>
    <div class="header-top">
        <?php if($user_id || $firm_id): ?>
            <div class="user-pill">
                <img src="uploads/profiles/<?= $profile_pic ?>" alt="P">
                <span style="font-weight: bold; font-size: 0.9rem;"><?= htmlspecialchars($display_name) ?></span>
            </div>
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Kijelentkezés</a>
        <?php endif; ?>
    </div>

    <div class="header-bottom">
        <a href="index.php" class="logo">Szuper<span>Shop</span></a>

        <form action="index.php" method="GET" class="filter-form">
            <div class="search-capsule">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Keresés a termékek között..." value="<?= htmlspecialchars($search_query) ?>">
            </div>

            <div class="category-box">
                <span class="cat-label">Kategóriák</span>
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

        <nav class="main-nav">
            <a href="favorites.php"><i class="far fa-heart"></i><span>Kedvencek</span></a>
            <a href="cart_page.php"><i class="fas fa-shopping-cart"></i><span>Kosár</span></a>
            <a href="login.php"><i class="far fa-user"></i><span>Bejelentkezés</span></a>
        </nav>
    </div>
</header>

<?php
$cat_res = $conn->query("SELECT 'type' FROM products ORDER BY name ASC");
?>
<div class="container">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="card">
                <a href="description.php?id=<?= $row['ID'] ?>" class="image-container">
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
</div>
</body>
</html>