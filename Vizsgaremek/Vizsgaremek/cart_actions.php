<?php
session_start();
include "connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- KOSÁRBA HELYEZÉS ---
if (isset($_GET['add_to_cart'])) {
    $p_id = (int)$_GET['add_to_cart'];
    $price = (float)$_GET['price'];

    // Megnézzük, benne van-e már
    $check = $conn->prepare("SELECT id FROM shopping_list WHERE user_id = ? AND product_id = ?");
    $check->bind_param("ii", $user_id, $p_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $conn->query("UPDATE shopping_list SET quantity = quantity + 1 WHERE user_id = $user_id AND product_id = $p_id");
    } else {
        $stmt = $conn->prepare("INSERT INTO shopping_list (user_id, product_id, product_price, quantity) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("iid", $user_id, $p_id, $price);
        $stmt->execute();
    }
    header("Location: index.php?msg=Kosárba téve!");
}

// --- KEDVENCEKHEZ ADÁS ---
if (isset($_GET['add_to_fav'])) {
    $p_id = (int)$_GET['add_to_fav'];
    $stmt = $conn->prepare("INSERT IGNORE INTO favorites (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $p_id);
    $stmt->execute();
    header("Location: index.php?msg=Kedvencekhez adva!");
}

// 3. KEDVENC TÖRLÉSE (az index oldalról)
if (isset($_GET['remove_fav'])) {
    $p_id = (int)$_GET['remove_fav'];
    $conn->query("DELETE FROM favorites WHERE user_id = $user_id AND product_id = $p_id");
}

// Visszairányítás oda, ahonnan jött (index-re vagy favorites-re)
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>