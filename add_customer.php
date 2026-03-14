<?php
$servername = "customer-db.c50s0oueymyt.ap-southeast-1.rds.amazonaws.com";
$username = "admin";
$password = "Customer123!";
$dbname = "customerdb";

$success = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $name    = $conn->real_escape_string($_POST["name"]);
    $email   = $conn->real_escape_string($_POST["email"]);
    $address = $conn->real_escape_string($_POST["address"]);
    $phone   = $conn->real_escape_string($_POST["phone"]);
    if ($conn->query("INSERT INTO customers (name,email,address,phone) VALUES ('$name','$email','$address','$phone')") === TRUE)
        $success = true;
    else
        $error = $conn->error;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add Customer</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar">
  <a href="customers.php" class="navbar-brand"><span class="dot"></span> CustomerHub</a>
  <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙 Dark</button>
</nav>

<div class="container-sm">
  <?php if ($success): ?>
  <div class="result-page">
    <div class="result-box">
      <div class="result-icon">🎉</div>
      <h2>Customer Added!</h2>
      <p>The customer has been successfully added to the system.</p>
      <div class="result-actions">
        <a href="add_customer.php" class="btn btn-outline">+ Add Another</a>
        <a href="customers.php" class="btn btn-primary">View All Customers</a>
      </div>
    </div>
  </div>
  <?php else: ?>
  <div class="page-header">
    <h1>Add Customer</h1>
    <p>Fill in the details to add a new customer</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header"><h2>Customer Information</h2></div>
    <div class="card-body">
      <form method="post">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="name" class="form-control" placeholder="Nguyen Van A" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control" placeholder="example@email.com" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" placeholder="0765386605">
          </div>
          <div class="form-group">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" placeholder="Ho Chi Minh City">
          </div>
        </div>
        <div class="form-actions">
          <a href="customers.php" class="btn btn-outline">← Back</a>
          <button type="submit" class="btn btn-primary">Add Customer →</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>
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
