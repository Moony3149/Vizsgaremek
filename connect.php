<?php
$servername = "localhost";
$username = "newsletter";
$password = "N3w#l3dd3r!";
$dbname = "firms";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>

<html>
<body>
<form action="index.php" method="POST">
Cég neve: <input type="text" name="name" required><br>
Cég azonosítója: <input type="int" name="ID" required><br>
E-mail: <input type="text" name="email" required><br>
Password: <input type="password" name="password" required><br>
<input type="submit">
</form>
</body>
</html>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $ID = $conn->real_escape_string($_POST['ID']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO firm (name, ID, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siss", $name, $ID, $email, $password);

    if ($stmt->execute()) {
        echo "Sikeres regisztráció! <a href='login.php'>Bejelentkezés</a>";
    } else {
        echo "Hiba: " . $stmt->error;
    }
    $stmt->close();
}