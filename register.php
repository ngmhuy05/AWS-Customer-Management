
<?php
session_start();
require_once "db.php";

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: /customers");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (strlen($username) < 3) {
        $error = "Tên đăng nhập phải có ít nhất 3 ký tự.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ.";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } elseif ($password !== $confirm) {
        $error = "Mật khẩu xác nhận không khớp.";
    } else {
        $check = $conn->query("SELECT id FROM users WHERE username='$username' OR email='$email'");
        if ($check->num_rows > 0) {
            $error = "Tên đăng nhập hoặc email đã tồn tại.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $conn->query("INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed')");
            $success = "Đăng ký thành công!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng ký — CustomerHub</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{font-family:'Inter',sans-serif!important;box-sizing:border-box;margin:0;padding:0;}
:root{--primary:#1a56db;--primary-dark:#1240a8;--border:#e5e7eb;--bg:#f8fafc;--white:#ffffff;--text:#111827;--text-muted:#6b7280;}
[data-theme="dark"]{--border:#334155;--bg:#0f172a;--white:#1e293b;--text:#f1f5f9;--text-muted:#94a3b8;}
body{background:var(--bg);color:var(--text);}
.navbar{background:var(--white);border-bottom:1px solid var(--border);padding:0 28px;height:64px;display:flex;align-items:center;justify-content:space-between;}
.navbar-brand{font-size:18px;font-weight:700;color:var(--primary);text-decoration:none;display:flex;align-items:center;gap:8px;letter-spacing:-0.3px;font-family:'Inter',sans-serif!important;}
.navbar-brand .dot{width:8px;height:8px;border-radius:50%;background:#0ea5e9;flex-shrink:0;}
.theme-toggle{background:var(--bg);border:1px solid var(--border);border-radius:999px;padding:6px 14px;cursor:pointer;font-size:13px;color:var(--text-muted);transition:all .15s;}
.theme-toggle:hover{border-color:var(--primary);color:var(--primary);}
.auth-wrap{display:flex;align-items:center;justify-content:center;padding:40px 24px;}
.auth-box{width:100%;max-width:420px;}
.auth-header{text-align:center;margin-bottom:24px;}
.auth-icon{font-size:40px;margin-bottom:14px;}
.auth-title{font-size:24px;font-weight:700;color:var(--text);letter-spacing:-0.5px;margin-bottom:5px;}
.auth-sub{font-size:14px;color:var(--text-muted);}
.auth-card{background:var(--white);border:1px solid var(--border);border-radius:16px;padding:28px;box-shadow:0 4px 24px rgba(0,0,0,.06);}
.form-group{margin-bottom:16px;}
.form-label{display:block;font-size:14px;font-weight:500;color:var(--text);margin-bottom:6px;}
.form-control{width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;font-size:15px;color:var(--text);background:var(--white);outline:none;transition:border .15s,box-shadow .15s;}
.form-control:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(26,86,219,.1);}
.btn-auth{width:100%;padding:12px;border-radius:10px;font-size:15px;font-weight:600;background:var(--primary);color:#fff;border:none;cursor:pointer;transition:background .15s,transform .1s;margin-top:6px;}
.btn-auth:hover{background:var(--primary-dark);transform:translateY(-1px);}
.auth-footer{text-align:center;margin-top:18px;font-size:14px;color:var(--text-muted);}
.auth-footer a{color:var(--primary);text-decoration:none;font-weight:500;}
.auth-footer a:hover{text-decoration:underline;}
.alert-danger{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;font-size:14px;margin-bottom:16px;}
.alert-success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;border-radius:10px;padding:12px 16px;font-size:14px;margin-bottom:16px;}
</style>
</head>
<body>
<nav class="navbar">
  <a href="/login" class="navbar-brand"><span class="dot"></span>CustomerHub</a>
  <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙 Dark</button>
</nav>

<div class="auth-wrap">
  <div class="auth-box">
    <div class="auth-header">
      <div class="auth-icon">📝</div>
      <div class="auth-title">Tạo tài khoản</div>
      <div class="auth-sub">Đăng ký để sử dụng CustomerHub</div>
    </div>

    <?php if ($error): ?>
      <div class="alert-danger">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert-success">✅ <?= htmlspecialchars($success) ?> <a href="/login" style="color:#065f46;font-weight:600">Đăng nhập ngay</a></div>
    <?php endif; ?>

    <div class="auth-card">
      <form method="post">
        <div class="form-group">
          <label class="form-label">Tên đăng nhập</label>
          <input type="text" name="username" class="form-control" placeholder="Nhập tên đăng nhập" required autofocus>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="Nhập email" required>
        </div>
        <div class="form-group">
          <label class="form-label">Mật khẩu</label>
          <input type="password" name="password" class="form-control" placeholder="Ít nhất 6 ký tự" required>
        </div>
        <div class="form-group" style="margin-bottom:4px">
          <label class="form-label">Xác nhận mật khẩu</label>
          <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" required>
        </div>
        <button type="submit" class="btn-auth">Đăng ký →</button>
      </form>
    </div>

    <div class="auth-footer">
      Đã có tài khoản? <a href="/login">Đăng nhập</a>
    </div>
  </div>
</div>

<script>
const saved=localStorage.getItem('theme');
if(saved==='dark'){document.documentElement.setAttribute('data-theme','dark');document.getElementById('themeBtn').textContent='☀️ Light';}
function toggleTheme(){const d=document.documentElement.getAttribute('data-theme')==='dark';d?(document.documentElement.removeAttribute('data-theme'),localStorage.setItem('theme','light'),document.getElementById('themeBtn').textContent='🌙 Dark'):(document.documentElement.setAttribute('data-theme','dark'),localStorage.setItem('theme','dark'),document.getElementById('themeBtn').textContent='☀️ Light');}
</script>
</body>
</html>
