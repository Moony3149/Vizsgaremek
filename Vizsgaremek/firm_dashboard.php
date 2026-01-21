<?php
session_start();
include "connect.php";

if (!isset($_SESSION["firm_id"])) {
    header("Location: firm_login.php");
    exit;
}

$firm_id = $_SESSION["firm_id"];
$msg = "";

// --- TERMÉK HOZZÁADÁSA ---
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $amount = $_POST['amount'];
    $weight = $_POST['weight']; // Súly
    $type = $_POST['type'];     // Kategória
    
    $picture = "no_image.png";
    if (!empty($_FILES['picture']['name'])) {
        $picture = time() . "_" . $_FILES['picture']['name'];
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['picture']['tmp_name'], "uploads/" . $picture);
    }

    // SQL: name, description, price, amount, weight, active, type, picture, firm_id, approved
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, amount, weight, active, type, picture, firm_id, approved) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, 0)");
    $stmt->bind_param("ssdissis", $name, $description, $price, $amount, $weight, $type, $picture, $firm_id);    
    if ($stmt->execute()) {
        $msg = "Termék sikeresen hozzáadva!";
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
    $weight = $_POST['weight'];
    $active = isset($_POST['active']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, amount = ?, weight = ?, active = ? WHERE ID = ? AND firm_id = ?");
    $stmt->bind_param("sdissii", $name, $price, $amount, $weight, $active, $p_id, $firm_id);
    $stmt->execute();
    $msg = "Változtatások elmentve!";
}

$result = $conn->query("SELECT * FROM products WHERE firm_id = $firm_id");
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Céges Vezérlőpult</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .add-box { background: #f8f9fa; border: 1px solid #e1e4e8; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #1e3c72; color: white; }
        .btn { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: bold; }
        .btn-add { background: #28a745; }
        .btn-save { background: #007bff; }
        input, select, textarea { padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 5px; }
    </style>
</head>
<body>

<div class="container">
    <h2>🏢 Termékek Kezelése</h2>
    <p><a href="logout.php" style="color:red; text-decoration:none;">✖ Kijelentkezés</a></p>

    <?php if($msg): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <div class="add-box">
        <h3>➕ Új termék hozzáadása</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Termék neve" required style="width: 250px;">
            <input type="number" step="0.01" name="price" placeholder="Ár (Ft)" required style="width: 100px;">
            <input type="number" name="amount" placeholder="Készlet" required style="width: 80px;">
            <input type="text" name="weight" placeholder="Súly (pl. 500g)" style="width: 100px;">
            
            <select name="type" required>
                <option value="">-- Kategória választása --</option>
                <?php
                $types = ['Zöldség és gyümölcs','Tejtermék- tojás','Pékáru','Húsáru','Mélyhűtött','Alapvető élelmiszerek','Italok','Speciális','Háztartás','Drogéria','Kisállat','Otthon-hobbi'];
                foreach($types as $t) echo "<option value='$t'>$t</option>";
                ?>
            </select><br>

            <textarea name="description" placeholder="Termék leírása..." style="width: 100%; height: 60px;"></textarea><br>
            
            <label>Termékfotó: </label>
            <input type="file" name="picture" accept="image/*">
            
            <button type="submit" name="add_product" class="btn btn-add">Termék feltöltése</button>
        </form>
    </div>

    <h3>📦 Aktuális kínálatod</h3>
    <table>
        <tr>
            <th>Név</th>
            <th>Ár (Ft)</th>
            <th>Súly</th>
            <th>Készlet</th>
            <th>Aktív</th>
            <th>Státusz</th>
            <th>Művelet</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <form method="POST">
                <td><input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>"></td>
                <td><input type="number" step="0.01" name="price" value="<?= $row['price'] ?>" style="width:80px;"></td>
                <td><input type="text" name="weight" value="<?= htmlspecialchars($row['weight']) ?>" style="width:80px;"></td>
                <td><input type="number" name="amount" value="<?= $row['amount'] ?>" style="width:60px;"></td>
                <td><input type="checkbox" name="active" <?= $row['active'] ? 'checked' : '' ?>></td>
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
    </table>
</div>

</body>
</html>