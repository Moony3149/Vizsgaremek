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

// --- LAPOZÁS BEÁLLÍTÁSA ---
$limit = 5; // Hány terméket mutasson egyszerre?
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Összes termék számolása a lapozáshoz
$count_query = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE firm_id = ?");
$count_query->bind_param("i", $firm_id);
$count_query->execute();
$total_products = $count_query->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

// Cég adatok lekérése
$stmt = $conn->prepare("SELECT brand_name, worker_name FROM firm WHERE ID = ?");
$stmt->bind_param("i", $firm_id);
$stmt->execute();
$firm_info = $stmt->get_result()->fetch_assoc();
$cegNev = $firm_info['brand_name'] ?? "Cég";
$dolgozoNev = $firm_info['worker_name'] ?? "Admin";

// 2. ACTIONS (POST)
// Új termék rögzítése
if (isset($_POST['add_product'])) {
    $p_id = $_POST['ID'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $amount = $_POST['amount'];
    $weight = intval($_POST['weight']);
    $type = $_POST['type']; 
    
    $picture = "no_image.png";
    if (!empty($_FILES['picture']['name'])) {
        $picture = time() . "_" . $_FILES['picture']['name'];
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['picture']['tmp_name'], "uploads/" . $picture);
    }

    $stmt = $conn->prepare("INSERT INTO products (ID, name, description, price, amount, weight, active, type, picture, firm_id, approved) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?, 0)");
    $stmt->bind_param("issdiissi", $p_id, $name, $description, $price, $amount, $weight, $type, $picture, $firm_id);
    if ($stmt->execute()) $msg = "Sikeres feltöltés!";
}

// Termék módosítása
if (isset($_POST['edit_product'])) {
    $p_id = $_POST['p_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $amount = $_POST['amount'];
    $weight = intval($_POST['weight']);
    $description = $_POST['description'];
    $active = isset($_POST['active']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, amount = ?, weight = ?, description = ?, active = ? WHERE ID = ? AND firm_id = ?");
    $stmt->bind_param("sdiisiii", $name, $price, $amount, $weight, $description, $active, $p_id, $firm_id);
    $stmt->execute();

    if (!empty($_FILES['new_picture']['name'])) {
        $new_pic = time() . "_" . $_FILES['new_picture']['name'];
        if (move_uploaded_file($_FILES['new_picture']['tmp_name'], "uploads/" . $new_pic)) {
            $stmt_pic = $conn->prepare("UPDATE products SET picture = ? WHERE ID = ? AND firm_id = ?");
            $stmt_pic->bind_param("sii", $new_pic, $p_id, $firm_id);
            $stmt_pic->execute();
        }
    }
    $msg = "Módosítások mentve!";
}

// 3. PRODUCT LIST
$stmt_list = $conn->prepare("SELECT * FROM products WHERE firm_id = ? LIMIT ? OFFSET ?");
$stmt_list->bind_param("iii", $firm_id, $limit, $offset);
$stmt_list->execute();
$result = $stmt_list->get_result();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Céges Portál - <?= htmlspecialchars($cegNev) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- ALAPOK ÉS VÁLTOZÓK --- */
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
            margin: 0; 
            padding: 0; 
            line-height: 1.5;
        }

        .container { 
            max-width: 1750px; 
            margin: 40px auto; 
            padding: 0 20px;
            display: flex;
            flex-direction: column;
            gap: 30px; /* Szellős távolság a blokkok között */
        }

        /* --- FEJLÉC STÍLUS --- */
        .header-line { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            background: var(--card-bg); 
            padding: 15px 30px; 
            border-radius: 15px; 
            border: 1px solid rgba(255,255,255,0.05);
        }

        .header-left { 
            display: flex; 
            align-items: center; 
            gap: 20px; 
        }
        .worker-tag { 
            color: var(--text-dim); 
            border-left: 1px solid #444; 
            padding-left: 20px; 
            font-size: 0.95rem;
        }

        .header-right { 
            display: flex; 
            align-items: center; 
            gap: 15px; 
        }

        .main-btn {
            color: var(--text);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .main-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent);
        }
        .header-left h2 { margin: 0; color: var(--accent); font-size: 1.6rem; }
        .header-left span { color: var(--text-dim); margin-left: 15px; border-left: 1px solid #444; padding-left: 15px; }

        .logout-btn { 
            color: #ef4444; 
            text-decoration: none; 
            font-weight: bold; 
            padding: 8px 16px; 
            border: 1px solid #ef4444; 
            border-radius: 8px; 
            transition: 0.3s;
        }
        .logout-btn:hover { 
            background: #ef4444; 
            color: white; 
        }

        /* --- KERESŐ SÁV --- */
        .search-bar-container {
            display: flex;
            align-items: center;
            background: var(--card-bg);
            padding: 15px 25px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .search-bar-container i { color: var(--accent); margin-right: 15px; }
        #productSearch {
            background: transparent;
            border: none;
            color: white;
            width: 100%;
            outline: none;
            font-size: 1rem;
        }

        /* --- ÚJ TERMÉK PANEL (ADD-BOX) --- */
        .add-box { 
            background: var(--card-bg); 
            padding: 30px; 
            border-radius: 15px; 
        }
        .grid-inputs { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            margin-bottom: 20px;
        }
        .field-group label { display: block; font-size: 11px; color: var(--accent); margin-bottom: 5px; text-transform: uppercase; }
        
        input, select, textarea { 
            background: #0f172a; 
            border: 1px solid #334155; 
            color: white; 
            padding: 12px; 
            border-radius: 8px; 
            width: 100%; 
            box-sizing: border-box;
        }

        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; }
        .btn-submit { background: var(--accent); color: #0f172a; border: none; padding: 8px 25px; border-radius: 8px; font-weight: bold; cursor: pointer; }

        /* --- TERMÉKLISTA SOROK --- */
        .table-container h3 { margin-bottom: 20px; }
        
        .product-item {
            background: var(--card-bg);
            margin-bottom: 15px;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: 0.2s;
        }
        .product-item:hover { border-color: var(--accent); }
        .product-item form { display: flex; gap: 30px; align-items: flex-start; }

        /* Oszlopok definíciója */
        .item-col { display: flex; flex-direction: column; gap: 10px; }
        .info-col { flex: 2; flex-direction: row; align-items: center; gap: 20px; }
        .params-col { flex: 2; flex-direction: row; gap: 15px; }
        .desc-col { flex: 2.5; }
        .status-col { flex: 1.2; align-items: stretch; justify-content: space-between; }

        /* Kép és név részletei */
        .item-img { width: 80px; height: 80px; border-radius: 10px; object-fit: cover; background: #000; }
        .name-block { display: flex; flex-direction: column; gap: 5px; flex: 1; }
        .item-name { font-weight: bold; font-size: 1.1rem; border: none; background: transparent; padding: 5px 0; border-bottom: 1px solid #334155; }
        .file-small { font-size: 11px; margin-top: 5px; border: none; padding: 0; background: transparent; }

        /* Paraméterek (Ár, Készlet, Súly) */
        .input-unit label { font-size: 10px; color: var(--text-dim); margin-bottom: 3px; display: block; }
        .input-unit input { width: 100px; padding: 8px; text-align: center; }

        /* Leírás textarea */
        .desc-col textarea { height: 80px; resize: none; font-size: 13px; }

        /* Státusz és Mentés gomb */
        .status-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .active-check { cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 8px; }
        .active-check input { width: 18px; height: 18px; cursor: pointer; }

        .badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: bold; }
        .live { background: rgba(34, 197, 94, 0.2); color: var(--success); }
        .wait { background: rgba(245, 158, 11, 0.2); color: var(--warning); }

        .btn-save-item {
            background: var(--success);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-save-item:hover { background: #16a34a; transform: translateY(-2px); }

        /* --- LAPOZÓ (PAGINATION) --- */
        .pagination { display: flex; justify-content: center; gap: 10px; margin-top: 20px; }
        .pagination a {
            padding: 10px 16px;
            background: var(--card-bg);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .pagination a.active { background: var(--accent); color: var(--bg); font-weight: bold; }

        /* --- ALERT --- */
        .alert-box { background: rgba(56, 189, 248, 0.1); color: var(--accent); padding: 15px 25px; border-radius: 12px; border: 1px solid var(--accent); }
    
    </style>
</head>
<body>

<div class="container">

    <header class="header-line">
        <div class="header-left">
            <h2><i class="fas fa-building"></i> <?= htmlspecialchars($cegNev) ?></h2>
            <span class="worker-tag"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($dolgozoNev) ?></span>
        </div>
        
        <div class="header-right">
            <a href="index.php" class="nav-btn main-btn">
                <i class="fas fa-home"></i> Vissza a főoldalra
            </a>
            <a href="logout.php" class="nav-btn logout-btn">
                <i class="fas fa-power-off"></i> Kijelentkezés
            </a>
        </div>
    </header>

    <div class="search-bar-container">
        <i class="fas fa-search"></i>
        <input type="text" id="productSearch" placeholder="Keresés a termékek között név alapján..." onkeyup="searchProducts()">
    </div>

    <?php if($msg): ?>
        <div class="alert-box">
            <i class="fas fa-info-circle"></i> <?= $msg ?>
        </div>
    <?php endif; ?>

    <section class="add-box">
        <h3><i class="fas fa-plus-circle"></i> Új termék rögzítése</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="grid-inputs">
                <div class="field-group">
                    <label>Azonosító</label>
                    <input type="number" name="ID" placeholder="ID" required>
                </div>
                <div class="field-group">
                    <label>Megnevezés</label>
                    <input type="text" name="name" placeholder="Termék neve" required>
                </div>
                <div class="field-group">
                    <label>Ár (Ft)</label>
                    <input type="number" step="0.01" name="price" placeholder="Pl: 1200" required>
                </div>
                <div class="field-group">
                    <label>Készlet</label>
                    <input type="number" name="amount" placeholder="Db" required>
                </div>
                <div class="field-group">
                    <label>Súly (g)</label>
                    <input type="number" name="weight" placeholder="Gramm" required>
                </div>
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
                <input type="file" name="picture" accept="image/*">
                <button type="submit" name="add_product" class="btn-submit">Termék rögzítése</button>
            </div>
        </form>
    </section>

    <section class="table-container">
        <h3><i class="fas fa-boxes"></i> Termékkezelő lista</h3>
        
        <div class="compact-list">
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="product-item product-row"> <form method="POST" enctype="multipart/form-data">
                    
                    <div class="item-col info-col">
                        <img src="uploads/<?= $row['picture'] ?>" class="item-img" onerror="this.src='uploads/no_image.png'">
                        <div class="name-block">
                            <small>#<?= $row['ID'] ?></small>
                            <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" class="item-name p-name">
                            <input type="file" name="new_picture" class="file-small">
                        </div>
                    </div>

                    <div class="item-col params-col">
                        <div class="input-unit">
                            <label>Ár</label>
                            <input type="number" step="0.01" name="price" value="<?= $row['price'] ?>">
                        </div>
                        <div class="input-unit">
                            <label>Készlet</label>
                            <input type="number" name="amount" value="<?= $row['amount'] ?>">
                        </div>
                        <div class="input-unit">
                            <label>Súly</label>
                            <input type="number" name="weight" value="<?= $row['weight'] ?>">
                        </div>
                    </div>

                    <div class="item-col desc-col">
                        <label>Leírás</label>
                        <textarea name="description"><?= htmlspecialchars($row['description']) ?></textarea>
                    </div>

                    <div class="item-col status-col">
                        <div class="status-top">
                            <label class="active-check">
                                <input type="checkbox" name="active" <?= $row['active'] ? 'checked' : '' ?>> Aktív
                            </label>
                            <div class="badge-box">
                                <?= $row['approved'] ? '<span class="badge live">ÉLŐ</span>' : '<span class="badge wait">VÁR...</span>' ?>
                            </div>
                        </div>
                        
                        <input type="hidden" name="p_id" value="<?= $row['ID'] ?>">
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
        if (productName.includes(input)) {
            rows[i].style.display = ""; // Ha egyezik, marad
        } else {
            rows[i].style.display = "none"; // Ha nem, eltűnik
        }
    }
}
</script>
</body>
</html>