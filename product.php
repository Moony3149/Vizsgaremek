<?php
include('connect.php');
echo ("Termékek");

// adat felvitel
if (($_SERVER["REQUEST_METHOD"] == "POST") && isset($_POST['ID']) && isset($_POST['name']) && !isset($_POST['description']) 
    && !isset($_POST['price']) && !isset($_POST['amount']) && !isset($_POST['type'])) {
    
    $pid = $conn->real_escape_string($_POST['ID']);
    $name = $conn->real_escape_string($_POST['name']);
    $desc= $conn->real_escape_string($_POST['description']);
    $price= $conn->real_escape_string($_POST['price']);
    $amount= $conn->real_escape_string($_POST['amount']);
    $type= $conn->real_escape_string($_POST['type']);
    $pic= $conn->real_escape_string($_POST['picture']);

    if(isset($_POST['active']) && $_POST['active'] == '1'){
        $active = 1;
    } else {
        $active = 0;
    }
    $sql = "INSERT INTO `termek`(`ID`, `name`, `description`, `price`, `amount`, `type`, `picture`, `active`) VALUES ('$pid','$name','$desc','$price','$amount','$type','$pic','$active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issiiiss", $pid, $name, $desc, $price, $amount, $type, $pic, $active);

    if ($stmt->execute()) {
        echo "Termék sikeresen felvéve!";
    } else {
        echo "Hiba: " . $stmt->error;
    }
}

// adat modositas
if (($_SERVER["REQUEST_METHOD"] == "POST") && isset($_POST['ID']) && isset($_POST['name']) && !isset($_POST['description']) 
    && !isset($_POST['price']) && !isset($_POST['amount']) && !isset($_POST['type'])) {

    if(isset($_POST['active'])){
        $active = 1;
    } else {
        $active = 0;
    }

    $pid = $conn->real_escape_string($_POST['ID']);
    $name = $conn->real_escape_string($_POST['name']);
    $desc= $conn->real_escape_string($_POST['description']);
    $price= $conn->real_escape_string($_POST['price']);
    $amount= $conn->real_escape_string($_POST['amount']);
    $type= $conn->real_escape_string($_POST['type']);
    $pic= $conn->real_escape_string($_POST['picture']);

    $sql = "UPDATE `termek` SET `name`='$name',`description`='$desc',`price`='$price',`amount`='$amount',`type`='$type',`picture`='$pic',`active`=$active WHERE `ID`=$pid";
    //echo "if utan<br>".$sql;

    if ($conn->query($sql) === TRUE) {
        echo "Adat modositva sikeresen";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$sql_query = "SELECT * FROM product;";

$result = $conn->query($sql_query);

if (!$result) {
    die("SQL error: " . $conn->error);
}

if( $result->num_rows > 0 ) {
    $product = array();
    
    while( $row = $result->fetch_assoc() ) {
        $product[] = $row;
    }

    $mizu = '';

    echo "<table border='1'>
    <tr>
    <th>Terméknév</th>
    <th>Termékszám</th>
    <th>Termék ára</th>
    <th>Termék leírása</th>
    <th>Active</th>
    <th>Bekuld</th>
    <th>Torles</th>
  </tr>";
  echo "<form action='#' method='POST'>";                    
        echo "<td>" .'<input type="text" id="" name="name" value="" placeholder="Terméknév">' . "</td>";
        echo "<td>" .'<input type="int" id="" name="id" value="" placeholder="Termékszám">' . "</td>";
        echo "<td>" .'<input type="int" id="" name="email" value="" placeholder="Termék ára">' . "</td>";
        echo "<td>" .'<input type="text" id="" name="description" value="" placeholder="Termék leírása">' . "</td>";
        echo "<td>" .'<input type="checkbox" id="" name="active" value="1">' . "</td>";
        echo "<td>" .  '<input type="submit" value="FELVISZ">' . "</td></tr>";
        echo "</form>";
    for($i = 0; $i < $result->num_rows; $i++) {
        
        if($teachers[$i]['active'] == 1){
        $mizu = 'checked';
        } else {
        $mizu = '';
        }


        echo "<form action='#' method='POST'>";
        echo "" .'<input type="hidden" id="' . $teachers[$i]['teacherID'] . '" name="tid" value="' . $teachers[$i]['teacherID'] . '" readonly>' . "";
        echo "<td>" .'<input type="text" id="' . $teachers[$i]['teacherID'] . '" name="name" value="' . $teachers[$i]['name'] . '">' . "</td>";
        echo "<td>" .'<input type="text" id="' . $teachers[$i]['teacherID'] . '" name="email" value="' . $teachers[$i]['email'] . '">' . "</td>";
        echo '<td><input type="checkbox" id="active_' . $teachers[$i]['teacherID'] . '" name="active" value="1" ' . $mizu . '></td>';
        echo "<td>" .  '<input type="submit" value="ADATVARIA">' . "</td>";
        echo "<td>" .  '<input type="button" value="Törlés" onclick="if(confirm(\'Biztosan törlöd ' . $teachers[$i]['name'] . '?\')) { window.location=\'menciform.php?id=' . $teachers[$i]['teacherID'] . '\'; }">' . "</td></tr>";
        echo "</form>";
        
    }
    
    echo "</table>";
} else {
    echo json_encode(array());

}
?>