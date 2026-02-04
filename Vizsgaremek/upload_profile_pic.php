<?php
session_start();
include "connect.php";

// Ellenőrizzük, be van-e jelentkezve valaki
$user_id = $_SESSION['user_id'] ?? null;
$firm_id = $_SESSION['firm_id'] ?? null;

if (!$user_id && !$firm_id) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    
    // Alapvető adatok
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    // Kiterjesztés ellenőrzése
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif');

    if (in_array($fileExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 2000000) { // Max 2MB
                // Egyedi nevet adunk a fájlnak, hogy ne írják felül egymást
                $fileNameNew = "profile_" . ($user_id ?: $firm_id) . "_" . time() . "." . $fileExt;
                $fileDestination = 'uploads/profiles/' . $fileNameNew;

                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Adatbázis frissítése
                    if ($user_id) {
                        $sql = "UPDATE users SET profile_pic = ? WHERE ID = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("si", $fileNameNew, $user_id);
                    } else {
                        $sql = "UPDATE firm SET profile_pic = ? WHERE ID = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("si", $fileNameNew, $firm_id);
                    }

                    if ($stmt->execute()) {
                        header("Location: index.php?msg=success");
                    } else {
                        echo "Hiba az adatbázis frissítésekor.";
                    }
                } else {
                    echo "Hiba történt a fájl mozgatásakor.";
                }
            } else {
                echo "A fájl túl nagy! (Max 2MB)";
            }
        } else {
            echo "Hiba történt a feltöltés során.";
        }
    } else {
        echo "Ilyen típusú fájlt nem tölthetsz fel! (Csak: jpg, jpeg, png, gif)";
    }
}
?>