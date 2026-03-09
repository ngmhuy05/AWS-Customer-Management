<?php
$servername = "customer-db.c50s0oueymyt.ap-southeast-1.rds.amazonaws.com";
$username = "admin";
$password = "Customer123!";
$dbname = "customerdb";

$conn = new mysqli($servername, $username, $password, $dbname);

$id = $_GET['id'];

$result = $conn->query("SELECT * FROM customers WHERE id=$id");
$row = $result->fetch_assoc();

if(isset($_POST['update'])){

$name = $_POST['name'];
$email = $_POST['email'];
$address = $_POST['address'];
$phone = $_POST['phone'];

$sql = "UPDATE customers 
SET name='$name', email='$email', address='$address', phone='$phone'
WHERE id=$id";

$conn->query($sql);

header("Location: customers.php");
}
?>

<form method="post">
Name: <input type="text" name="name" value="<?php echo $row['name']; ?>"><br><br>
Email: <input type="text" name="email" value="<?php echo $row['email']; ?>"><br><br>
Address: <input type="text" name="address" value="<?php echo $row['address']; ?>"><br><br>
Phone: <input type="text" name="phone" value="<?php echo $row['phone']; ?>"><br><br>

<input type="submit" name="update" value="Update Customer">
</form>
