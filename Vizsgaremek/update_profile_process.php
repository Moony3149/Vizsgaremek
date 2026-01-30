<?php
session_start();
include "connect.php";

$user_id = $_SESSION['user_id'] ?? null;
$firm_id = $_SESSION['firm_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_name = $_POST['display_name'];
    $new_email = $_POST['email'];

    // 1. ELLENŐRZÉS: Foglalt-e a név?
    if ($user_id) {
        $check = $conn->prepare("SELECT ID FROM users WHERE username = ? AND ID != ?");
        $check->bind_param("si", $new_name, $user_id);
    } else {
        $check = $conn->prepare("SELECT ID FROM firm WHERE brand_name = ? AND ID != ?");
        $check->bind_param("si", $new_name, $firm_id);
    }
    
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        header("Location: profile.php?msg=name_taken");
        exit();
    }

    // 2. KÉPFELTÖLTÉS KEZELÉSE
    if (!empty($_FILES['profile_image']['name'])) {
        $file = $_FILES['profile_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newName = "prof_" . time() . "." . $ext;
        
        if (move_uploaded_file($file['tmp_name'], "uploads/profiles/" . $newName)) {
            if ($user_id) {
                $conn->query("UPDATE users SET profile_pic = '$newName' WHERE ID = $user_id");
            } else {
                $conn->query("UPDATE firm SET profile_pic = '$newName' WHERE ID = $firm_id");
            }
        }
    }

    // 3. SZÖVEGES ADATOK FRISSÍTÉSE
    if ($user_id) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE ID = ?");
        $stmt->bind_param("ssi", $new_name, $new_email, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE firm SET brand_name = ?, email = ? WHERE ID = ?");
        $stmt->bind_param("ssi", $new_name, $new_email, $firm_id);
    }

    if ($stmt->execute()) {
        header("Location: profile.php?msg=updated");
    } else {
        echo "Hiba történt a mentéskor: " . $conn->error;
    }
}
?>