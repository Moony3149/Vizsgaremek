<?php
session_start();
include "connect.php";

// Ellenőrizzük, hogy be van-e jelentkezve a cég
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

$cegNev = $firm_info['brand_name'];
$dolgozoNev = $firm_info['worker_name'];

// --- TERMÉK HOZZÁADÁSA ---
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $amount = $_POST['amount'];
    $weight = intval($_POST['weight']); // Az SQL-edben INT(8), így számmá alakítjuk
    $type = $_POST['type']; 
    
    $picture = "no_image.png";
    if (!empty($_FILES['picture']['name'])) {
        $picture = time() . "_" . $_FILES['picture']['name'];
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['picture']['tmp_name'], "uploads/" . $picture);
    }

    // SQL előkészítése (Fontos: az oszloprendnek egyeznie kell a bind_param-mal)
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, amount, weight, active, type, picture, firm_id, approved) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, 0)");
    $stmt->bind_param("ssdiissi", $name, $description, $price, $amount, $weight, $type, $picture, $firm_id);    
    
    if ($stmt->execute()) {
        $msg = "Termék sikeresen hozzáadva és jóváhagyásra vár!";
    } else {
        $msg = "Hiba történt: " . $conn->error;
    }
}

// --- TERMÉK MÓDOSÍTÁSA ---
if (isset($_POST['edit_product'])) {
    $p_id = $_POST['p_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $amount = $_POST['amount'];
    $weight = intval($_POST['weight']);
    $active = isset($_POST['active']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, amount = ?, weight = ?, active = ? WHERE ID = ? AND firm_id = ?");
    $stmt->bind_param("sdiisii", $name, $price, $amount, $weight, $active, $p_id, $firm_id);
    $stmt->execute();
    $msg = "Változtatások elmentve!";
}

// Termékek lekérése (Prepared statement a biztonságért)
$stmt_list = $conn->prepare("SELECT * FROM products WHERE firm_id = ?");
$stmt_list->bind_param("i", $firm_id);
$stmt_list->execute();
$result = $stmt_list->get_result();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #99bff8ff; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; margin-bottom: 20px; padding-bottom: 10px; }
        .add-box { background: #f8f9fa; border: 1px solid #e1e4e8; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #1e3c72; color: white; }
        .btn { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: bold; text-decoration: none; }
        .btn-add { background: #28a745; }
        .btn-save { background: #007bff; }
        .logout { color: #dc3545; font-weight: bold; }
        input, select, textarea { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-flex">
        <div>
        <h2 style="margin:0;"><?= htmlspecialchars($cegNev) ?></h2>
        <span style="color: #555;">Fiók:  <?= htmlspecialchars($dolgozoNev) ?></span>
    </div>
    <a href="logout.php" class="logout">✖ Kijelentkezés</a>
</div>
    <?php if($msg): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <div class="add-box">
        <h3>➕ Új termék hozzáadása</h3>
        <form method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                <input type="text" name="name" placeholder="Termék neve" required>
                <input type="number" step="0.01" name="price" placeholder="Ár (Ft)" required>
                <input type="number" name="amount" placeholder="Készlet (db)" required>
                <input type="number" name="weight" placeholder="Súly (gramm)" required title="Csak számot adj meg!">
                
                <select name="type" required>
                    <option value="">-- Kategória --</option>
                    <?php
                    // Fontos: Ezeknek egyezniük kell az SQL ENUM értékeivel!
                    $types = ['Zöldség és gyümölcs','Tejtermék- tojás','Pékáru','Húsáru','Mélyhűtött','Alapvető élelmiszerek','Italok','Speciális','Háztartás','Drogéria','Kisállat','Otthon-hobbi'];
                    foreach($types as $t) echo "<option value='$t'>$t</option>";
                    ?>
                </select>
            </div>
            <textarea name="description" placeholder="Termék leírása..." style="width: 100%; height: 60px; margin-top: 10px;"></textarea><br>
            
            <div style="margin-top: 10px;">
                <label>Termékfotó: </label>
                <input type="file" name="picture" accept="image/*">
                <button type="submit" name="add_product" class="btn btn-add">Termék feltöltése</button>
            </div>
        </form>
    </div>

    <h3>📦 Aktuális kínálatod</h3>
    <table>
        <thead>
            <tr>
                <th>Név</th>
                <th>Ár (Ft)</th>
                <th>Súly (g)</th>
                <th>Készlet</th>
                <th>Aktív</th>
                <th>Státusz</th>
                <th>Művelet</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <form method="POST">
                    <td><input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" style="width: 100%;"></td>
                    <td><input type="number" step="0.01" name="price" value="<?= $row['price'] ?>" style="width: 80px;"></td>
                    <td><input type="number" name="weight" value="<?= $row['weight'] ?>" style="width: 70px;"></td>
                    <td><input type="number" name="amount" value="<?= $row['amount'] ?>" style="width: 60px;"></td>
                    <td>
                        <input type="checkbox" name="active" <?= $row['active'] ? 'checked' : '' ?>>
                    </td>
                    <td>
                        <?= $row['approved'] ? "<span style='color:green'>✔ Élő</span>" : "<span style='color:orange'>⏳ Ellenőrzés</span>" ?>
                    </td>
                    <td>
                        <input type="hidden" name="p_id" value="<?= $row['ID'] ?>">
                        <button type="submit" name="edit_product" class="btn btn-save">Mentés</button>
                    </td>
                </form>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>