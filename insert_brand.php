
<?php
include 'connect.php';
echo "<h2>Regisztráció</h2>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $ID = $conn->real_escape_string($_POST['ID']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO firm (name, ID, email, password) VALUES ('$name', '$ID', '$email', '$password')";
    if ($conn->query($sql) === TRUE) {
        echo "Sikeresen regisztrált!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

?>