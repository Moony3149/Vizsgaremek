<?php
include "connect.php";
$pass = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("INSERT INTO users (name, userName, email, password, admin) 
              VALUES ('Admin', 'admin', 'admin@bolt.hu', '$pass', 'user')");
echo "Admin létrehozva! Email: admin@bolt.hu | Jelszó: admin123";
?>