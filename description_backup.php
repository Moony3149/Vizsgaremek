<?php
session_start();
include "connect.php";

$role = $_SESSION['admin'] ?? null; 
$user_id = $_SESSION['user_id'] ?? null;
$firm_id = $_SESSION['firm_id'] ?? null;

// ID ellenőrzése
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = intval($_GET['id']);

// Termék lekérése biztonságosan
$sql = "SELECT p.*, f.brand_name FROM products p LEFT JOIN firm f ON p.firm_id = f.ID WHERE p.ID = ? AND p.active = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "A termék nem található vagy nem aktív.";
    exit();
}

// --- DINAMIKUS STÍLUS BEÁLLÍTÁSOK ---
$header_bg = '#1b263b';   // Alapértelmezett sötétkék (Index szín)
$header_text = '#ffffff';
$desc_color = '#555555'; 

if ($firm_id && $role !== 'admin') {
    // CÉG - Marad a kékes irány, de kicsit más árnyalat
    $header_bg = '#1a3a5a'; 
    $header_text = '#ffffff';
    $desc_color = '#444444'; 
} elseif ($role === 'admin') {
    // ADMIN - Tiszta fekete, ahogy az indexnél is szokás
    $header_bg = '#000000'; 
    $header_text = '#ffffff';
    $desc_color = '#e0e0e0'; 
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/Vizsgaremek/">
    <title><?= htmlspecialchars($product['name']) ?> - Részletek</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { 
            /* PHP változók átadása */
            --header-bg: <?= $header_bg ?>;
            --header-text: <?= $header_text ?>;
            --text-description: <?= $desc_color ?>;
            --accent-blue: #00d2ff; /* Az index jellegzetes neon kékje */

            /* Felhasználói kör alapú színek */
            <?php if ($role === 'admin'): ?>
                --bg-color: #121212; 
                --card-bg: #1e1e1e; 
                --text-main: #ffffff;
                --border-color: #333333;
                --img-bg: #000000;
            <?php else: ?>
                --bg-color: #f0f2f5;
                --card-bg: #ffffff;
                --text-main: #1b263b;
                --border-color: #e0e0e0;
                --img-bg: #ffffff;
            <?php endif; ?>

            --success-green: #2ecc71;
        }

        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: var(--bg-color); 
            margin: 0; 
            color: var(--text-main); 
        }

        /* --- FEJLÉC (Szerkezet marad, de színek az indexről) --- */
        header { 
            background: var(--header-bg); 
            padding: 15px 5%; 
            display: flex; 
            align-items: center;
            /* Eltávolítottam a világos keretet, kapott egy index-szerű árnyékot */
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            /* Áttetsző gomb háttér */
            background: rgba(255,255,255,0.1);
            padding: 10px 22px;
            border-radius: 50px;
            font-weight: 500;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .btn-back:hover {
            background: var(--accent-blue);
            color: #1b263b; /* Sötétkék szöveg a világoskék háttéren */
            transform: translateX(-5px);
            border-color: var(--accent-blue);
        }

        /* --- TÖBBI STÍLUS (Marad az eredeti logikád szerint) --- */
        .details-container {
            max-width: 1300px;
            margin: 40px auto;
            background: var(--card-bg);
            border-radius: 20px;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            overflow: hidden;
            border: 1px solid var(--border-color);
            box-shadow: 0 15px 45px rgba(0,0,0,0.1);
        }

        .product-image {
            background: var(--img-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            cursor: pointer;
            border-right: 1px solid var(--border-color);
        }

        .product-image img {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
            transition: transform 0.4s ease;
        }

        .product-image:hover img { transform: scale(1.03); }

        .product-info { padding: 40px; }

        .description { 
            line-height: 1.8; 
            color: var(--text-description) !important; 
            font-size: 1.1rem;
        }

        .price { font-size: 2.5rem; color: var(--success-green); font-weight: bold; margin: 20px 0; }
        .brand { color: var(--accent-blue); font-weight: bold; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 10px; }

        .meta-info {
            background: rgba(0, 210, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid var(--accent-blue);
            margin-bottom: 20px;
        }

        /* Lightbox és Mobil nézet marad... */
        @media (max-width: 992px) {
            .details-container { grid-template-columns: 1fr; margin: 15px; }
            .product-image { border-right: none; border-bottom: 1px solid var(--border-color); }
        }

                /* --- LIGHTBOX MODAL ALAP --- */
        .modal {
            display: none; /* Alapból rejtve */
            position: fixed;
            z-index: 9999;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.9); /* Ez a fekete háttér */
            justify-content: center;
            align-items: center;
            cursor: zoom-out;
        }

        /* A nagyított kép stílusa */
        .modal-content {
            max-width: 90%;
            max-height: 90%;
            margin: auto;
            display: block;
            object-fit: contain;
            border: 3px solid rgba(255,255,255,0.1);
        }

        .close-modal { 
            position: fixed; /* Absolute helyett fixed, hogy mindig a képernyőhöz képest nézzük */
            top: 30px; 
            right: 40px; 
            color: white; 
            font-size: 60px; 
            font-weight: bold;
            cursor: pointer; 
            z-index: 10001; /* Hogy biztosan minden felett legyen */
            line-height: 1;
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="btn-back">
        <i class="fas fa-chevron-left"></i> Vissza a főoldalra
    </a>
</header>

<div class="details-container">
    <div class="product-image" onclick="openLightbox()">
        <img src="/Vizsgaremek/uploads/<?= $product['picture'] ?: 'no_image.jpg' ?>" 
             alt="<?= htmlspecialchars($product['name']) ?>" id="mainImg">
    </div>

    <div class="product-info">
        <span class="brand"><?= htmlspecialchars($product['brand_name'] ?: 'Saját termék') ?></span>
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        
        <div class="price"><?= number_format($product['price'], 0, ',', ' ') ?> Ft</div>

        <div class="meta-info">
            <strong>Kategória:</strong> <?= htmlspecialchars($product['type']) ?><br>
            <strong>Készleten:</strong> <?= $product['amount'] ?> db
        </div>

        <h3>Leírás</h3>
        <p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

        <?php if($role === 'user'): ?>
            <?php elseif(!$role): ?>
            <p style="color: #e74c3c; font-weight: bold; border: 1px solid #e74c3c; padding: 10px; border-radius: 5px; display: inline-block;">
                A vásárláshoz kérjük <a href="login.php" style="color: inherit;">jelentkezzen be</a>!
            </p>
        <?php endif; ?>
    </div>
</div>

<div id="lightboxModal" class="modal" onclick="closeLightbox()">
    <span class="close-modal">&times;</span>
    <img class="modal-content" id="fullImg">
</div>
<script>
    function openLightbox() {
        const modal = document.getElementById("lightboxModal");
        const mainImg = document.getElementById("mainImg");
        const fullImg = document.getElementById("fullImg");

        // "flex" kell a "block" helyett, hogy a CSS középre tudja rakni
        modal.style.display = "flex"; 
        
        // Itt javítva a getElementById (nem getElaementById)
        fullImg.src = mainImg.src;
    }

    function closeLightbox() {
        document.getElementById("lightboxModal").style.display = "none";
    }

    // Bezárás ESC gombra
    document.addEventListener('keydown', (e) => {
        if (e.key === "Escape") closeLightbox();
    });
</script>

</body>
</html>