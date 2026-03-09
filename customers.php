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

echo "<h2>Customer List</h2>";
echo "<a href='add_customer.php'>Add Customer</a><br><br>";

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th>
            <th>Name</th>
	    <th>Email</th>
            <th>Address</th>
            <th>Phone</th>
	    <th>Action</th>
          </tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".$row["id"]."</td>";
        echo "<td>".$row["name"]."</td>";
        echo "<td>".$row["email"]."</td>";
        echo "<td>".$row["address"]."</td>";
        echo "<td>".$row["phone"]."</td>";
	
	echo "<td>";
	echo "<a href='update_customer.php?id=".$row["id"]."'>Update</a> | ";
	echo "<a href='delete_customer.php?id=".$row["id"]."'>Delete</a> | ";
	echo "<a href='send_email.php?email=".$row["email"]."'>Send Email</a> | ";
	echo "<a href='send_sms.php?phone=".$row["phone"]."'>Send SMS</a>";
	echo "</td>";
        
	echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No customers found";
}

$conn->close();
?>
