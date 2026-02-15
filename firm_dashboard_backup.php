<?php
session_start();
include "connect.php";

// 1. AUTH & DATA FETCH
if (!isset($_SESSION["firm_id"])) {
    header("Location: firm_login.php");
    exit;
}

$firm_id = $_SESSION["firm_id"];
$msg = "";

$stmt = $conn->prepare("SELECT brand_name, worker_name FROM firm WHERE ID = ?");
$stmt->bind_param("i", $firm_id);
$stmt->execute();
$firm_info = $stmt->get_result()->fetch_assoc();

// Olvasatlan értesítések lekérése
$notifs = $conn->prepare("SELECT id, message FROM notifications WHERE firm_id = ? AND is_read = 0");
$notifs->bind_param("i", $firm_id);
$notifs->execute();
$notif_result = $notifs->get_result();

// Értesítés törlése (ha a cég bezárja/elolvassa)
if (isset($_GET['clear_notif'])) {
    $n_id = intval($_GET['clear_notif']);
    $stmt_clear = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND firm_id = ?");
    $stmt_clear->bind_param("ii", $n_id, $firm_id);
    $stmt_clear->execute();
    header("Location: firm_dashboard.php"); // Frissítés, hogy eltűnjön
    exit;
}

// --- LAPOZÁS BEÁLLÍTÁSA ---
$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE firm_id = ?");
$count_query->bind_param("i", $firm_id);
$count_query->execute();
$total_products = $count_query->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

$cegNev = $firm_info['brand_name'] ?? "Cég";
$dolgozoNev = $firm_info['worker_name'] ?? "Admin";

// 2. ACTIONS (POST)
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $amount = $_POST['amount'];
    $capacity = intval($_POST['capacity']);
    $type = $_POST['type']; 
    
    $picture = "no_image.png";
    if (!empty($_FILES['picture']['name'])) {
        $picture = time() . "_" . $_FILES['picture']['name'];
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['picture']['tmp_name'], "uploads/" . $picture);
    }

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, amount, capacity, active, type, picture, firm_id, approved) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, 0)");
    $stmt->bind_param("ssdiissi", $name, $description, $price, $amount, $capacity, $type, $picture, $firm_id);
    if ($stmt->execute()) $msg = "Sikeres feltöltés!";
}

if (isset($_POST['edit_product'])) {
    $p_id = $_POST['id']; // Fontos: be kell olvasni a rejtett inputból!
    $name = $_POST['name'];
    $price = $_POST['price'];
    $amount = $_POST['amount'];
    $capacity = intval($_POST['capacity']);
    $description = $_POST['description'];
    $active = isset($_POST['active']) ? 1 : 0;

    // Javított SQL: 7 paraméter (name, price, amount, capacity, description, active + ID és firm_id a WHERE-hez)
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, amount = ?, capacity = ?, description = ?, active = ? WHERE ID = ? AND firm_id = ?");
    // Típusok: s (string), d (double), i (int), i (int), s (string), i (int), i (int), i (int)
    $stmt->bind_param("sdiisiii", $name, $price, $amount, $capacity, $description, $active, $p_id, $firm_id);

    if ($stmt->execute()) {
        $msg = "Termék sikeresen frissítve!";
    }

    // Képfrissítés javítása: csak azt a terméket frissítse, amit épp szerkesztünk!
    if (!empty($_FILES['new_picture']['name'])) {
        $new_pic = time() . "_" . $_FILES['new_picture']['name'];
        if (move_uploaded_file($_FILES['new_picture']['tmp_name'], "uploads/" . $new_pic)) {
            $stmt_pic = $conn->prepare("UPDATE products SET picture = ? WHERE ID = ? AND firm_id = ?");
            $stmt_pic->bind_param("sii", $new_pic, $p_id, $firm_id);
            $stmt_pic->execute();
        }
    }
}

$stmt_list = $conn->prepare("SELECT * FROM products WHERE firm_id = ? LIMIT ? OFFSET ?");
$stmt_list->bind_param("iii", $firm_id, $limit, $offset);
$stmt_list->execute();
$result = $stmt_list->get_result();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Céges Portál - <?= htmlspecialchars($cegNev) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --bg: #0f172a; 
            --card-bg: #1e293b; 
            --accent: #38bdf8; 
            --text: #f1f5f9; 
            --text-dim: #94a3b8;
            --success: #22c55e;
            --warning: #f59e0b;
        }

        body { 
            font-family: 'Inter', 'Segoe UI', sans-serif; 
            background-color: var(--bg); 
            color: var(--text); 
            margin: 0; padding: 0; 
            line-height: 1.5;
            overflow-x: hidden;
        }

        .container { 
            max-width: 1660px; 
            margin: 20px auto; 
            padding: 0 15px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .header-line { 
            display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; 
            background: var(--card-bg); padding: 15px 20px; border-radius: 15px; 
            border: 1px solid rgba(255,255,255,0.05); gap: 15px;
        }

        .header-left { display: flex; align-items: center; gap: 15px; }
        .header-left h2 { margin: 0; color: var(--accent); font-size: 1.3rem; }
        .worker-tag { color: var(--text-dim); border-left: 1px solid #444; padding-left: 15px; font-size: 0.85rem; }

        .header-right { display: flex; gap: 10px; }
        .nav-btn { 
            text-decoration: none; font-size: 0.85rem; padding: 8px 12px; border-radius: 8px; 
            display: flex; align-items: center; gap: 8px; transition: 0.3s;
        }

        .main-btn { color: var(--text); background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); }
        .logout-btn { color: #ef4444; border: 1px solid #ef4444; }
        .logout-btn:hover { background: #ef4444; color: white; }

        .search-bar-container {
            display: flex; align-items: center; background: var(--card-bg); padding: 12px 20px; 
            border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);
        }
        .search-bar-container i { color: var(--accent); margin-right: 12px; }
        #productSearch { background: transparent; border: none; color: white; width: 100%; outline: none; font-size: 1rem; }

        input, select, textarea { 
            background: #0f172a; border: 1px solid #334155; color: white; 
            padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box; font-size: 14px;
        }
        textarea { resize: vertical; min-height: 70px; }

        .add-box { background: var(--card-bg); padding: 20px; border-radius: 15px; }
        .grid-inputs { 
            display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); 
            gap: 15px; margin-bottom: 15px;
        }
        .field-group label { display: block; font-size: 10px; color: var(--accent); margin-bottom: 4px; text-transform: uppercase; }
        .action-bar { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px; margin-top: 15px; }
        .btn-submit { background: var(--accent); color: #0f172a; border: none; padding: 10px 20px; border-radius: 8px; font-capacity: bold; cursor: pointer; }

        .product-item {
            background: var(--card-bg); margin-bottom: 20px; padding: 2px 20px; 
            border-radius: 15px; border: 1px solid rgba(255,255,255,0.05);
        }
        .product-item form { display: flex; flex-direction: column; gap: 20px; }

        .product-item {
            background: var(--card-bg);
            padding: 20px 20px; /* 20px helyett csak 10px felül és alul */
            margin-bottom: 22px; /* A kártyák közötti távolságot is kicsit szűkítjük */
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.05);
            }

        @media (min-width: 1024px) {
            .product-item form { flex-direction: row; align-items: stretch; gap: 20px; }
            .info-col { flex: 2; margin-top: 21px; } /* Az ID/Név oszlopot is lejjebb toljuk az egyensúlyért */
            .params-col { flex: 2; }
            .desc-col { flex: 2.5; }
            .status-col { flex: 1.2; margin-top: 35px; }
            .desc-col textarea { margin-top: 6.5px; height: 100%;  }
        }

        .desc-col label {
            display: block; font-size: 9px; color: var(--accent); 
            text-transform: uppercase; margin-bottom: 1px; height: 0px; padding-top: 22px; 
        }

        .item-col { display: flex; flex-direction: column; gap: 10px; width: 100%; }
        .info-col { flex-direction: row !important; gap: 15px; align-items: flex-start; }
        .item-img { width: 90px; height: 99px; border-radius: 10px; object-fit: cover; background: #000; flex-shrink: 0; }
        .name-block { flex-grow: 1; display: flex; flex-direction: column; gap: 5px; }
        
        /* Stílusos ID szerkesztő */
        .id-input-small { 
            background: transparent; border: 1px solid var(--accent); color: var(--accent); 
            font-size: 10px; padding: 2px 5px; width: 60px; border-radius: 4px;
        }

        .item-name { font-capacity: bold; border: none; background: transparent; border-bottom: 1px solid #334155; }

        /* Paraméterek rácsa - itt a lényeg az igazításhoz */
        .params-grid { 
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; 
            margin-top: 21px; /* Letoljuk a Leírás label szintjére */
        }
        .input-unit label { font-size: 10px; color: var(--text-dim); margin-bottom: 3px; display: block; }

        .status-row { display: flex; justify-content: space-between; align-items: center; }
        .badge { padding: 4px 8px; border-radius: 6px; font-size: 10px; font-capacity: bold; }
        .live { background: rgba(34, 197, 94, 0.2); color: var(--success); }
        .wait { background: rgba(245, 158, 11, 0.2); color: var(--warning); }

        .btn-save-item {
            background: var(--success); color: white; border: none; padding: 12px; 
            border-radius: 8px; font-capacity: bold; cursor: pointer; width: 100%; margin-top: auto;
        }

        .pagination { display: flex; justify-content: center; gap: 8px; margin: 20px 0; flex-wrap: wrap; }
        .pagination a {
            padding: 8px 14px; background: var(--card-bg); color: white; 
            text-decoration: none; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);
        }
        .pagination a.active { background: var(--accent); color: var(--bg); font-capacity: bold; }

        .alert-box { background: rgba(56, 189, 248, 0.1); color: var(--accent); padding: 15px; border-radius: 12px; border: 1px solid var(--accent); }
    </style>
</head>
<body>

<div class="container">
    <?php while($n = $notif_result->fetch_assoc()): ?>
        <div class="alert-box" style="background: rgba(239, 68, 68, 0.1); border-left: 5px solid #ef4444; color: #f1f5f9; display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 15px; border-radius: 12px;">
            <span><i class="fas fa-exclamation-circle" style="color: #ef4444; margin-right: 10px;"></i> <?= htmlspecialchars($n['message']) ?></span>
            <a href="?clear_notif=<?= $n['id'] ?>" style="color: var(--text-dim); text-decoration: none; font-size: 24px; line-height: 1;">&times;</a>
        </div>
    <?php endwhile; ?>

   <header class="header-line">
        <div class="header-left">
            <h2><i class="fas fa-building"></i> <?= htmlspecialchars($cegNev) ?></h2>
            <span class="worker-tag"><?= htmlspecialchars($dolgozoNev) ?></span>
        </div>
        
        <div class="header-right">
            <a href="index.php" class="nav-btn main-btn" title="Vissza a főoldalra">
                <i class="fas fa-home"></i>
            </a>
            <a href="logout.php" class="nav-btn logout-btn">
                <i class="fas fa-power-off"></i> Kijelentkezés
            </a>
        </div>
    </header>

    <div class="search-bar-container">
        <i class="fas fa-search"></i>
        <input type="text" id="productSearch" placeholder="Keresés név alapján..." onkeyup="searchProducts()">
    </div>

    <?php if($msg): ?>
        <div class="alert-box"><i class="fas fa-info-circle"></i> <?= $msg ?></div>
    <?php endif; ?>

    <section class="add-box">
        <h3 style="margin-top:0"><i class="fas fa-plus-circle"></i> Új termék</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="grid-inputs">
                <div class="field-group"><label>Megnevezés</label><input type="text" name="name" required></div>
                <div class="field-group"><label>Ár (Ft)</label><input type="number" step="0.01" name="price" required></div>
                <div class="field-group"><label>Készlet</label><input type="number" name="amount" required></div>
                <div class="field-group"><label>Súly (g)</label><input type="number" name="capacity" required></div>
                <div class="field-group">
                    <label>Kategória</label>
                    <select name="type" required>
                        <option value="">-- Válassz --</option>
                        <?php 
                        $types = ['Zöldség és gyümölcs','Tejtermék- tojás','Pékáru','Húsáru','Mélyhűtött','Alapvető élelmiszerek','Italok','Speciális','Háztartás','Drogéria','Kisállat','Otthon-hobbi'];
                        foreach($types as $t) echo "<option value='$t'>$t</option>"; 
                        ?>
                    </select>
                </div>
            </div>
            <textarea name="description" placeholder="Termékleírás..."></textarea>
            <div class="action-bar">
                <input type="file" name="picture" accept="image/*" style="width: auto; border:none; padding:0">
                <button type="submit" name="add_product" class="btn-submit">Mentés</button>
            </div>
        </form>
    </section>

    <section>
        <h3 style="margin-bottom: 15px;"><i class="fas fa-boxes"></i> Termékek kezelése</h3>
        <div id="productList">
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="product-item product-row">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $row['ID'] ?>">
                    
                    <div class="item-col info-col">
                        <img src="uploads/<?= $row['picture'] ?>" class="item-img" onerror="this.src='uploads/no_image.png'">
                        <div class="name-block">
                            <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" class="item-name p-name">
                            <input type="file" name="new_picture" style="font-size: 10px; border:none; padding:0; background:transparent">
                        </div>
                    </div>

                    <div class="item-col params-col">
                        <div class="params-grid">
                            <div class="input-unit"><label>Ár</label><input type="number" step="0.01" name="price" value="<?= $row['price'] ?>"></div>
                            <div class="input-unit"><label>Készlet</label><input type="number" name="amount" value="<?= $row['amount'] ?>"></div>
                            <div class="input-unit"><label>Súly</label><input type="number" name="capacity" value="<?= $row['capacity'] ?>"></div>
                        </div>
                    </div>

                    <div class="item-col desc-col">
                        <label>Leírás</label>
                        <textarea name="description"><?= htmlspecialchars($row['description']) ?></textarea>
                    </div>

                    <div class="item-col status-col">
                        <div class="status-row">
                            <label style="font-size: 13px; display:flex; align-items:center; gap:5px; cursor:pointer">
                                <input type="checkbox" name="active" <?= $row['active'] ? 'checked' : '' ?> style="width:16px; height:16px"> Aktív
                            </label>
                            <?= $row['approved'] ? '<span class="badge live">ÉLŐ</span>' : '<span class="badge wait">VÁR...</span>' ?>
                        </div>
                        <button type="submit" name="edit_product" class="btn-save-item">
                            <i class="fas fa-save"></i> Mentés
                        </button>
                    </div>
                </form>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <nav class="pagination">
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="<?= ($page == $i) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </nav>
</div>

<script>
function searchProducts() {
    let input = document.getElementById('productSearch').value.toLowerCase();
    let rows = document.getElementsByClassName('product-row');
    for (let i = 0; i < rows.length; i++) {
        let productName = rows[i].querySelector('.p-name').value.toLowerCase();
        rows[i].style.display = productName.includes(input) ? "" : "none";
    }
}
</script>
</body>
</html>