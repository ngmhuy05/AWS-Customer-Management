<?php

$servername = "customer-db.c50s0oueymyt.ap-southeast-1.rds.amazonaws.com";
$username = "admin";
$password = "Customer123!";
$dbname = "customerdb";

$conn = new mysqli($servername,$username,$password,$dbname);

if($conn->connect_error){
die("Connection failed: ".$conn->connect_error);
}

if($_SERVER["REQUEST_METHOD"] == "POST"){

$name=$_POST["name"];
$email=$_POST["email"];
$address=$_POST["address"];
$phone=$_POST["phone"];

$sql="INSERT INTO customers (name,email,address,phone)
VALUES ('$name','$email','$address','$phone')";

if($conn->query($sql)===TRUE){
echo "Customer added successfully<br>";
echo "<a href='customers.php'>Back to Customer List</a>";
}else{
echo "Error: ".$conn->error;
}

}

?>

<h2>Add Customer</h2>

<form method="post">

Name:<br>
<input type="text" name="name" required><br><br>

Email:<br>
<input type="email" name="email" required><br><br>

Address:<br>
<input type="text" name="address"><br><br>

Phone:<br>
<input type="text" name="phone"><br><br>

<input type="submit" value="Add Customer">

</form>
