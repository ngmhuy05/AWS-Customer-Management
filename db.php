<?php
$servername = "customer-db.c50s0oueymyt.ap-southeast-1.rds.amazonaws.com";
$username = "admin";
$password = "Customer123!";
$dbname = "customerdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

function logActivity($conn, $user_id, $action, $description) {
    $user_id = intval($user_id);
    $action = $conn->real_escape_string($action);
    $description = $conn->real_escape_string($description);
    $conn->query("INSERT INTO activity_logs (user_id, action, description) VALUES ($user_id, '$action', '$description')");
}
