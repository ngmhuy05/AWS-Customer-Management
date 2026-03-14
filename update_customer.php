<?php
$servername = "customer-db.c50s0oueymyt.ap-southeast-1.rds.amazonaws.com";
$username = "admin";
$password = "Customer123!";
$dbname = "customerdb";

$conn = new mysqli($servername, $username, $password, $dbname);
$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM customers WHERE id=$id");
$row = $result->fetch_assoc();
if (!$row) { header("Location: customers.php"); exit; }

$success = false;
$error = "";
if (isset($_POST['update'])) {
    $name    = $conn->real_escape_string($_POST['name']);
    $email   = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    $phone   = $conn->real_escape_string($_POST['phone']);
    if ($conn->query("UPDATE customers SET name='$name',email='$email',address='$address',phone='$phone' WHERE id=$id")) {
        $success = true;
        $row = ['id'=>$id,'name'=>$name,'email'=>$email,'address'=>$address,'phone'=>$phone];
    } else {
        $error = $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Customer</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar">
  <a href="customers.php" class="navbar-brand"><span class="dot"></span> CustomerHub</a>
  <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙 Dark</button>
</nav>

<div class="container-sm">
  <div class="page-header">
    <h1>Edit Customer</h1>
    <p>Update information for <strong><?= htmlspecialchars($row['name']) ?></strong></p>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success">✅ Customer updated successfully!</div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      <h2 style="display:flex;align-items:center">
        <span class="avatar"><?= strtoupper(substr($row['name'],0,1)) ?></span>
        Customer #<?= $row['id'] ?>
      </h2>
    </div>
    <div class="card-body">
      <form method="post">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($row['phone']) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($row['address']) ?>">
          </div>
        </div>
        <div class="form-actions">
          <a href="customers.php" class="btn btn-outline">← Back</a>
          <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
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
