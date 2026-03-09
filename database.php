<?php
$servername = "customer-db.c50s0oueymyt.ap-southeast-1.rds.amazonaws.com";
$username = "admin";
$password = "Customer123!";
$dbname = "customerdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM customers";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
    echo "ID: " . $row["id"] . " Name: " . $row["name"] . " Email: " . $row["email"] . "<br>";
}

$conn->close();
?>
