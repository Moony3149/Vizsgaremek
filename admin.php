<?php
session_start();
include "connect.php";

// Ellenőrzés
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$msg = "";

// Cég jóváhagyása
if (isset($_GET['approve_firm'])) {
    $id = intval($_GET['approve_firm']);
    $stmt = $conn->prepare("UPDATE firm SET approved = 1 WHERE ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $msg = "Cég jóváhagyva!";
}

// Termék jóváhagyása
if (isset($_GET['approve_product'])) {
    $id = intval($_GET['approve_product']);
    $stmt = $conn->prepare("UPDATE products SET approved = 1 WHERE ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $msg = "Termék jóváhagyva!";
}

// Termék végleges törlése (akár vár, akár már élő)
if (isset($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']);
    
    // 1. Lekérjük a cég ID-t és a termék nevét, mielőtt törölnénk
    $stmt_info = $conn->prepare("SELECT firm_id, name FROM products WHERE ID = ?");
    $stmt_info->bind_param("i", $id);
    $stmt_info->execute();
    $p_data = $stmt_info->get_result()->fetch_assoc();

    if ($p_data) {
        $f_id = $p_data['firm_id'];
        $p_name = $p_data['name'];

        // 2. Értesítés beszúrása
        $notif_msg = "A(z) '$p_name' nevű termékedet az adminisztrátor törölte.";
        $stmt_notif = $conn->prepare("INSERT INTO notifications (firm_id, message) VALUES (?, ?)");
        $stmt_notif->bind_param("is", $f_id, $notif_msg);
        $stmt_notif->execute();

        // 3. Termék törlése
        $stmt_del = $conn->prepare("DELETE FROM products WHERE ID = ?");
        $stmt_del->bind_param("i", $id);
        $stmt_del->execute();
        
        $msg = "Termék törölve, cég értesítve!";
    }
}

// Jóváhagyott termékek lekérése
$approved_products = $conn->query("SELECT p.*, f.brand_name FROM products p LEFT JOIN firm f ON p.firm_id = f.ID WHERE p.approved = 1 ORDER BY p.ID DESC");

// Adatok lekérése
$pending_firms = $conn->query("SELECT * FROM firm WHERE approved = 0");
$pending_products = $conn->query("SELECT p.*, f.brand_name FROM products p LEFT JOIN firm f ON p.firm_id = f.ID WHERE p.approved = 0");
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Jóváhagyások</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --admin-bg: #121212;
            --panel-bg: #1e1e1e;
            --accent-blue: #00d2ff;
            --success-green: #2ecc71;
            --text-gray: #e0e0e0;
            --border-color: #333;
        }

        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: var(--admin-bg); 
            color: var(--text-gray);
            margin: 0;
            padding: 20px;
        }

        .admin-container { 
            max-width: 1100px; 
            margin: 40px auto; 
        }

        /* FEJLÉC STÍLUS */
        h1 { 
            color: white; 
            border-bottom: 2px solid var(--accent-blue); 
            padding-bottom: 15px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            text-shadow: 0 0 10px rgba(0, 210, 255, 0.3);
        }

        .nav-link { 
            font-size: 1rem; 
            text-decoration: none; 
            color: var(--accent-blue); 
            border: 1px solid var(--accent-blue);
            padding: 8px 15px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .nav-link:hover {
            background: var(--accent-blue);
            color: black;
            box-shadow: 0 0 15px var(--accent-blue);
        }

        /* ÜZENET BOX */
        .msg { 
            background: rgba(46, 204, 113, 0.1); 
            color: var(--success-green); 
            padding: 15px; 
            margin-bottom: 25px; 
            border-radius: 8px; 
            border-left: 5px solid var(--success-green); 
            animation: fadeIn 0.5s;
        }

        /* PANELEK ÉS TÁBLÁZATOK */
        h3 { color: var(--accent-blue); margin-top: 40px; display: flex; align-items: center; gap: 10px; }

        .table-card {
            background: var(--panel-bg);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        table { width: 100%; border-collapse: collapse; }
        
        th { 
            background: rgba(255,255,255,0.05); 
            color: var(--accent-blue); 
            text-align: left; 
            padding: 15px;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        td { 
            padding: 15px; 
            border-bottom: 1px solid var(--border-color); 
            color: #ccc;
        }

        tr:hover td { background: rgba(255,255,255,0.02); }

        /* GOMBOK */
        .btn-ok { 
            background: var(--success-green); 
            color: white; 
            padding: 8px 16px; 
            text-decoration: none; 
            border-radius: 6px; 
            font-size: 13px; 
            font-weight: bold; 
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.3s;
        }

        .btn-ok:hover { 
            background: #27ae60; 
            transform: scale(1.05);
            box-shadow: 0 0 10px rgba(46, 204, 113, 0.4);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            h1 {
                flex-direction: column; /* Egymás alá kerül a cím és a gomb */
                gap: 15px;
                text-align: center;
                align-items: stretch; /* A gomb kitölti a szélességet */
            }

            .nav-link {
                text-align: center;
                display: block;
            }

            /* TÁBLÁZAT MOBILON: Görgethetővé tesszük, hogy ne nyomja szét az oldalt */
            .table-card {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                min-width: 600px; /* Biztosítjuk, hogy az oszlopoknak legyen helye, de görgethető legyen */
            }

            th, td {
                padding: 10px;
                font-size: 0.85rem;
            }

            .btn-ok {
                padding: 6px 10px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h1>
        <span><i class="fas fa-user-shield"></i> Jóváhagyások</span>
        <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Vissza a főoldalra</a>
    </h1>
    
    <?php if($msg): ?>
        <div class="msg"><i class="fas fa-check-circle"></i> <?= $msg ?></div>
    <?php endif; ?>

    <h3><i class="fas fa-building"></i> Jóváhagyásra váró cégek</h3>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cégnév (Márka)</th>
                    <th>Email</th>
                    <th>Művelet</th>
                </tr>
            </thead>
            <tbody>
                <?php if($pending_firms->num_rows > 0): ?>
                    <?php while($f = $pending_firms->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $f['ID'] ?></td>
                        <td style="color: white; font-weight: bold;"><?= htmlspecialchars($f['brand_name']) ?></td> 
                        <td><?= htmlspecialchars($f['email']) ?></td>
                        <td><a href="admin.php?approve_firm=<?= $f['ID'] ?>" class="btn-ok"><i class="fas fa-check"></i> Jóváhagyás</a></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; padding: 30px;">Nincs várakozó cég.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h3><i class="fas fa-box-open"></i> Jóváhagyásra váró termékek</h3>
    <div class="table-card" style="margin-bottom: 50px;">
        <table>
            <thead>
                <tr>
                    <th>Név</th>
                    <th>Cég</th>
                    <th>Ár</th>
                    <th>Művelet</th>
                </tr>
            </thead>
            <tbody>
                <?php if($pending_products && $pending_products->num_rows > 0): ?>
                    <?php while($p = $pending_products->fetch_assoc()): ?>
                    <tr>
                        <td style="color: white; font-weight: bold;"><?= htmlspecialchars($p['name']) ?></td>
                        <td><i class="fas fa-industry" style="font-size: 0.8rem;"></i> <?= htmlspecialchars($p['brand_name'] ?: 'Nincs megadva') ?></td> 
                        <td style="color: var(--success-green);"><?= number_format($p['price'], 0, ',', ' ') ?> Ft</td>
                        <td>
                            <a href="admin.php?approve_product=<?= $p['ID'] ?>" class="btn-ok" title="Jóváhagyás">
                                <i class="fas fa-check"></i>
                            </a>
                            <a href="admin.php?delete_product=<?= $p['ID'] ?>" class="btn-ok" style="background: #ef4444;" 
                            onclick="return confirm('Biztosan véglegesen törlöd ezt a terméket?')" title="Törlés">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; padding: 30px;">Nincs várakozó termék.</td></tr>
                <?php endif; ?>
            </tbody>
                <h3><i class="fas fa-check-double"></i> Aktív (Élő) termékek</h3>
                <div class="table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>Név</th>
                                <th>Cég</th>
                                <th>Ár</th>
                                <th>Művelet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($approved_products->num_rows > 0): ?>
                                <?php while($ap = $approved_products->fetch_assoc()): ?>
                                <tr>
                                    <td style="color: white; font-weight: bold;"><?= htmlspecialchars($ap['name']) ?></td>
                                    <td><?= htmlspecialchars($ap['brand_name']) ?></td>
                                    <td style="color: var(--success-green);"><?= number_format($ap['price'], 0, ',', ' ') ?> Ft</td>
                                    <td>
                                        <a href="admin.php?delete_product=<?= $ap['ID'] ?>" class="btn-ok" style="background: #ef4444;" 
                                        onclick="return confirm('Véglegesen törlöd az ÉLŐ terméket? A cég értesítést kap.')">
                                            <i class="fas fa-trash"></i> Törlés
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align: center; padding: 30px;">Nincs aktív termék a rendszerben.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
</body>
</html>