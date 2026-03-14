<?php
$servername = "customer-db.c50s0oueymyt.ap-southeast-1.rds.amazonaws.com";
$username = "admin";
$password = "Customer123!";
$dbname = "customerdb";

$conn = new mysqli($servername, $username, $password, $dbname);
$id = intval($_GET['id']);
$result = $conn->query("SELECT name FROM customers WHERE id=$id");
$row = $result->fetch_assoc();
$name = $row ? $row['name'] : 'Customer';
$conn->query("DELETE FROM customers WHERE id=$id");
$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Delete Customer</title>
<link rel="stylesheet" href="style.css">
<meta http-equiv="refresh" content="2;url=customers.php">
</head>
<body>
<nav class="navbar">
  <a href="customers.php" class="navbar-brand"><span class="dot"></span> CustomerHub</a>
  <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙 Dark</button>
</nav>
<div class="container">
  <div class="result-page">
    <div class="result-box">
      <div class="result-icon">🗑️</div>
      <h2>Customer Deleted</h2>
      <p><strong><?= htmlspecialchars($name) ?></strong> has been removed from the system.</p>
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px">Redirecting automatically...</p>
      <a href="customers.php" class="btn btn-primary">← Back to Customers</a>
    </div>
  </div>
</div>
<script>
const saved = localStorage.getItem('theme');
if (saved === 'dark') { document.documentElement.setAttribute('data-theme','dark'); document.getElementById('themeBtn').textContent='☀️ Light'; }
function toggleTheme() {
  const isDark = document.documentElement.getAttribute('data-theme')==='dark';
  isDark ? (document.documentElement.removeAttribute('data-theme'), localStorage.setItem('theme','light'), document.getElementById('themeBtn').textContent='🌙 Dark')
         : (document.documentElement.setAttribute('data-theme','dark'), localStorage.setItem('theme','dark'), document.getElementById('themeBtn').textContent='☀️ Light');
}
</script>
</body>
</html>
