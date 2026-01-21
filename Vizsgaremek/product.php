<?php
include("connect.php");

/* ======================
   TERMÉK FELVÉTEL
====================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add'])) {

    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $active = isset($_POST['active']) ? 1 : 0;

    // kép (csak név mentése)
    $picture = $_FILES['picture']['name'];
    if ($picture) {
        move_uploaded_file($_FILES['picture']['tmp_name'], "uploads/$picture");
    }

    $stmt = $conn->prepare("
        INSERT INTO products 
        (name, description, price, amount, type, picture, active)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssdisii",
        $name,
        $description,
        $price,
        $amount,
        $type,
        $picture,
        $active
    );

    $stmt->execute();
}

/* ======================
   TERMÉK MÓDOSÍTÁS
====================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit'])) {

    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $active = isset($_POST['active']) ? 1 : 0;

    $picture = $_POST['old_picture'];

    if (!empty($_FILES['picture']['name'])) {
        $picture = $_FILES['picture']['name'];
        move_uploaded_file($_FILES['picture']['tmp_name'], "uploads/$picture");
    }

    $stmt = $conn->prepare("
        UPDATE products 
        SET name=?, description=?, price=?, amount=?, type=?, picture=?, active=?
        WHERE ID=?
    ");

    $stmt->bind_param(
        "ssdisiii",
        $name,
        $description,
        $price,
        $amount,
        $type,
        $picture,
        $active,
        $id
    );

    $stmt->execute();
}

/* ======================
   LISTÁZÁS
====================== */
$result = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>Termékkezelés</title>
<style>
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ccc; padding: 6px; }
input, select, textarea { width: 100%; }
img { max-width: 80px; }
</style>
</head>
<body>

<h2>➕ Új termék</h2>

<form method="POST" enctype="multipart/form-data">
    <input name="name" placeholder="Név" required>
    <textarea name="description" placeholder="Leírás"></textarea>
    <input type="number" step="0.01" name="price" placeholder="Ár" required>
    <input type="number" name="amount" placeholder="Mennyiség" required>

    <select name="type" required>
        <option value="">-- Típus --</option>
        <option>Zöldség és gyümölcs</option>
        <option>Tejtermék- tojás</option>
        <option>Pékáru</option>
        <option>Húsáru</option>
        <option>Mélyhűtött</option>
        <option>Alapvető élelmiszerek</option>
        <option>Italok</option>
        <option>Speciális</option>
        <option>Háztartás</option>
        <option>Drogéria</option>
        <option>Kisállat</option>
        <option>Otthon-hobbi</option>
    </select>

    <input type="file" name="picture">
    <label><input type="checkbox" name="active"> Aktív</label>
    <br><br>
    <button name="add">Felvitel</button>
</form>

<hr>

<h2>📦 Termékek</h2>

<table>
<tr>
<th>Név</th>
<th>Leírás</th>
<th>Ár</th>
<th>Menny.</th>
<th>Típus</th>
<th>Kép</th>
<th>Aktív</th>
<th>Művelet</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<form method="POST" enctype="multipart/form-data">
<tr>
<td><input name="name" value="<?= $row['name'] ?>"></td>
<td><textarea name="description"><?= $row['description'] ?></textarea></td>
<td><input name="price" value="<?= $row['price'] ?>"></td>
<td><input name="amount" value="<?= $row['amount'] ?>"></td>

<td>
<select name="type">
<?php
$types = [
'Zöldség és gyümölcs','Tejtermék- tojás','Pékáru','Húsáru','Mélyhűtött',
'Alapvető élelmiszerek','Italok','Speciális','Háztartás','Drogéria',
'Kisállat','Otthon-hobbi'
];
foreach ($types as $t) {
    $sel = ($row['type'] == $t) ? "selected" : "";
    echo "<option $sel>$t</option>";
}
?>
</select>
</td>

<td>
<?php if ($row['picture']): ?>
<img src="uploads/<?= $row['picture'] ?>"><br>
<?php endif; ?>
<input type="file" name="picture">
</td>

<td><input type="checkbox" name="active" <?= $row['active'] ? 'checked' : '' ?>></td>

<td>
<input type="hidden" name="id" value="<?= $row['ID'] ?>">
<input type="hidden" name="old_picture" value="<?= $row['picture'] ?>">
<button name="edit">Mentés</button>
</td>
</tr>
</form>
<?php endwhile; ?>

</table>

</body>
</html>
