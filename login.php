<?php
session_start();

$error = "";

// Thay đổi username/password ở đây
$valid_username = "admin";
$valid_password = "Customer@123";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header("Location: customers.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login — CustomerHub</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar">
  <a href="login.php" class="navbar-brand"><span class="dot"></span> CustomerHub</a>
  <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙 Dark</button>
</nav>

<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:24px">
  <div style="width:100%;max-width:400px">

    <div style="text-align:center;margin-bottom:32px">
      <div style="font-size:40px;margin-bottom:12px">🔐</div>
      <h1 style="font-family:'DM Serif Display',serif;font-size:26px;margin-bottom:6px">Welcome back</h1>
      <p style="color:var(--text-muted);font-size:14px">Sign in to CustomerHub</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="post">
          <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px">
            Sign In →
          </button>
        </form>
      </div>
    </div>

    <p style="text-align:center;margin-top:16px;font-size:12px;color:var(--text-muted)">
      AWS Customer Management System
    </p>
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
