<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
};

$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>Főoldal</title>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}

header {
    background: #667eea;
    color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

nav a {
    color: white;
    margin-left: 20px;
    text-decoration: none;
    font-weight: bold;
}

.container {
    padding: 30px;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.btn {
    display: inline-block;
    padding: 10px 16px;
    background: #667eea;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    margin-top: 10px;
}

.btn:hover {
    background: #5563c1;
}
</style>
</head>

<body>

<header>
    <h2>🛒 Webáruház</h2>
    <nav>
        <?php if ($role === 'user'): ?>
            <a href="#">Kedvencek</a>
            <a href="#">Bevásárlólista</a>
        <?php endif; ?>

        <?php if ($role === 'company'): ?>
            <a href="product.php">Termékeim</a>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
            <a href="admin_products.php">Termék jóváhagyás</a>
            <a href="admin_firms.php">Cégek kezelése</a>
        <?php endif; ?>

        <a href="logout.php">Kilépés</a>
    </nav>
</header>

<div class="container">

    <div class="card">
        <h3>Üdvözlünk 👋</h3>

        <?php if ($role === 'user'): ?>
            <p>Bejelentkezett felhasználóként extra funkciók érhetők el.</p>
        <?php elseif ($role === 'company'): ?>
            <p>Céges fiók – termékek feltöltése és kezelése.</p>
        <?php elseif ($role === 'admin'): ?>
            <p>Admin felület – teljes hozzáférés.</p>
        <?php endif; ?>
    </div>

    <?php if ($role === 'company'): ?>
    <div class="card">
        <h3>📦 Termékkezelés</h3>
        <p>Itt tudsz új termékeket felvinni és meglévőket szerkeszteni.</p>
        <a class="btn" href="product.php">Termékek kezelése</a>
    </div>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
    <div class="card">
        <h3>🛂 Admin funkciók</h3>
        <a class="btn" href="admin_products.php">Termék jóváhagyás</a>
        <a class="btn" href="admin_firms.php">Cégek kezelése</a>
    </div>
    <?php endif; ?>

</div>

</body>
</html>
