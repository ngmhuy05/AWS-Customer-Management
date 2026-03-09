<?php
$servername = "customer-db.c50s0oueymyt.ap-southeast-1.rds.amazonaws.com";
$username = "admin";
$password = "Customer123!";
$dbname = "customerdb";

$conn = new mysqli($servername, $username, $password, $dbname);

$id = $_GET['id'];

$sql = "DELETE FROM customers WHERE id=$id";

if ($conn->query($sql) === TRUE) {
    echo "Customer deleted successfully";
} else {
    echo "Error deleting record";
}

$conn->close();

header("Location: customers.php");
?>
