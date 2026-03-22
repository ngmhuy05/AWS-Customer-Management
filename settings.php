<?php
require_once "auth.php";
require_once "db.php";

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

$success = "";
$error = "";

// Lấy thông tin user hiện tại
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $new_username = $conn->real_escape_string(trim($_POST['username']));
        $new_email    = $conn->real_escape_string(trim($_POST['email']));

        if (strlen($new_username) < 3) {
            $error = "Tên đăng nhập phải có ít nhất 3 ký tự.";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email không hợp lệ.";
        } else {
            $check = $conn->query("SELECT id FROM users WHERE (username='$new_username' OR email='$new_email') AND id != $user_id");
            if ($check->num_rows > 0) {
                $error = "Tên đăng nhập hoặc email đã tồn tại.";
            } else {
                $conn->query("UPDATE users SET username='$new_username', email='$new_email' WHERE id=$user_id");
                $_SESSION['username'] = $new_username;
                $username = $new_username;
                logActivity($conn, $user_id, 'update', "Cập nhật thông tin tài khoản");
                $success = "Cập nhật thành công!";
                $user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
            }
        }
    }

    if ($action === 'change_password') {
        $current  = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm  = $_POST['confirm_password'];

        if (!password_verify($current, $user['password'])) {
            $error = "Mật khẩu hiện tại không đúng.";
        } elseif (strlen($new_pass) < 6) {
            $error = "Mật khẩu mới phải có ít nhất 6 ký tự.";
        } elseif ($new_pass !== $confirm) {
            $error = "Mật khẩu xác nhận không khớp.";
        } else {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed' WHERE id=$user_id");
            logActivity($conn, $user_id, 'update', "Đổi mật khẩu");
            $success = "Đổi mật khẩu thành công!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cài đặt — CustomerHub</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
*{font-family:'Inter',sans-serif;}
.sidebar{position:fixed;top:0;left:0;height:100vh;width:220px;background:var(--white);border-right:1px solid var(--border);display:flex;flex-direction:column;transition:width .25s ease;z-index:100;overflow:hidden;}
.sidebar.collapsed{width:56px;}
.sidebar-header{height:64px;display:flex;align-items:center;padding:0 14px;gap:10px;border-bottom:1px solid var(--border);flex-shrink:0;}
.sidebar-logo{font-size:18px;font-weight:700;color:var(--primary);white-space:nowrap;display:flex;align-items:center;gap:8px;text-decoration:none;overflow:hidden;}
.sidebar-logo .dot{width:8px;height:8px;border-radius:50%;background:#0ea5e9;flex-shrink:0;}
.logo-text{transition:opacity .2s,width .2s;white-space:nowrap;overflow:hidden;width:130px;}
.sidebar.collapsed .logo-text{opacity:0;width:0;}
.toggle-btn{margin-left:auto;background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:15px;padding:4px;flex-shrink:0;}
.sidebar-nav{flex:1;padding:10px 8px;display:flex;flex-direction:column;gap:2px;overflow:hidden;}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 11px;border-radius:8px;text-decoration:none;color:var(--text-muted);font-size:14px;font-weight:500;cursor:pointer;border:none;background:none;width:100%;text-align:left;transition:background .15s,color .15s;white-space:nowrap;position:relative;}
.nav-item:hover{background:var(--bg);color:var(--text);}
.nav-item.active{background:var(--primary-light);color:var(--primary);}
.nav-item .icon{font-size:16px;flex-shrink:0;width:22px;text-align:center;}
.nav-label{transition:opacity .2s,width .2s;overflow:hidden;white-space:nowrap;width:140px;}
.sidebar.collapsed .nav-label{opacity:0;width:0;}
.nav-item.danger{color:var(--danger);}
.nav-item.danger:hover{background:#fee2e2;}
.sidebar-footer{padding:10px 8px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:2px;}
.user-info{display:flex;align-items:center;gap:10px;padding:8px 11px;border-radius:8px;background:var(--bg);overflow:hidden;}
.user-avatar{width:30px;height:30px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;}
.user-name{font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;transition:opacity .2s,width .2s;width:120px;}
.sidebar.collapsed .user-name{opacity:0;width:0;}
.main-wrap{margin-left:220px;transition:margin-left .25s ease;min-height:100vh;display:flex;flex-direction:column;}
.main-wrap.collapsed{margin-left:56px;}
.topbar{height:68px;background:var(--white);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 28px;position:sticky;top:0;z-index:50;}
.topbar-title{font-size:20px;font-weight:700;color:var(--text);}
.topbar-sub{font-size:13px;color:var(--text-muted);margin-top:2px;}
.content{padding:24px 28px;flex:1;}
.settings-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;}
.form-group{margin-bottom:18px;}
.form-label{display:block;font-size:14px;font-weight:500;color:var(--text);margin-bottom:6px;}
.form-control{width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;font-size:15px;color:var(--text);background:var(--white);outline:none;transition:border .15s,box-shadow .15s;font-family:'Inter',sans-serif;}
.form-control:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(26,86,219,.1);}
.btn{font-family:'Inter',sans-serif;font-size:14px;font-weight:500;}
.alert{padding:12px 16px;border-radius:10px;font-size:14px;margin-bottom:20px;display:flex;align-items:center;gap:8px;}
.alert-success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;}
.alert-danger{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}
@media(max-width:768px){.settings-grid{grid-template-columns:1fr;}}
</style>
</head>
<body>

<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <a href="/customers" class="sidebar-logo"><span class="dot"></span><span class="logo-text">CustomerHub</span></a>
    <button class="toggle-btn" id="toggleBtn" onclick="toggleSidebar()">◀</button>
  </div>
  <nav class="sidebar-nav">
    <a href="/customers" class="nav-item" data-tip="Khách hàng"><span class="icon">👥</span><span class="nav-label">Khách hàng</span></a>
    <a href="/history" class="nav-item" data-tip="Lịch sử"><span class="icon">📋</span><span class="nav-label">Lịch sử</span></a>
    <a href="/settings" class="nav-item active" data-tip="Cài đặt"><span class="icon">⚙️</span><span class="nav-label">Cài đặt</span></a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"><?= strtoupper(substr($username,0,1)) ?></div>
      <span class="user-name"><?= htmlspecialchars($username) ?></span>
    </div>
    <a href="/logout" class="nav-item danger" data-tip="Đăng xuất"><span class="icon">🚪</span><span class="nav-label">Đăng xuất</span></a>
  </div>
</div>

<div class="main-wrap" id="mainWrap">
  <div class="topbar">
    <div><div class="topbar-title">Cài đặt</div><div class="topbar-sub">Quản lý tài khoản và mật khẩu</div></div>
    <div style="margin-left:auto">
      <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙 Dark</button>
    </div>
  </div>
  <div class="content">

    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="settings-grid">
      <!-- Update profile -->
      <div class="card">
        <div class="card-header"><h2>👤 Thông tin tài khoản</h2></div>
        <div class="card-body">
          <form method="post">
            <input type="hidden" name="action" value="update_profile">
            <div class="form-group">
              <label class="form-label">Tên đăng nhập</label>
              <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
          </form>
        </div>
      </div>

      <!-- Change password -->
      <div class="card">
        <div class="card-header"><h2>🔒 Đổi mật khẩu</h2></div>
        <div class="card-body">
          <form method="post">
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
              <label class="form-label">Mật khẩu hiện tại</label>
              <input type="password" name="current_password" class="form-control" placeholder="••••••" required>
            </div>
            <div class="form-group">
              <label class="form-label">Mật khẩu mới</label>
              <input type="password" name="new_password" class="form-control" placeholder="Ít nhất 6 ký tự" required>
            </div>
            <div class="form-group">
              <label class="form-label">Xác nhận mật khẩu mới</label>
              <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới" required>
            </div>
            <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
          </form>
        </div>
      </div>
    </div>

    <!-- System info -->
    <div class="card" style="margin-top:24px">
      <div class="card-header"><h2>☁️ Thông tin hệ thống</h2></div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
          <div style="text-align:center;padding:16px;background:var(--bg);border-radius:10px">
            <div style="font-size:24px;margin-bottom:6px">☁️</div>
            <div style="font-weight:600;font-size:14px">Cloud Platform</div>
            <div style="color:var(--text-muted);font-size:13px">Amazon EC2</div>
          </div>
          <div style="text-align:center;padding:16px;background:var(--bg);border-radius:10px">
            <div style="font-size:24px;margin-bottom:6px">✉️</div>
            <div style="font-weight:600;font-size:14px">Email Service</div>
            <div style="color:var(--text-muted);font-size:13px">Amazon SES</div>
          </div>
          <div style="text-align:center;padding:16px;background:var(--bg);border-radius:10px">
            <div style="font-size:24px;margin-bottom:6px">🗄️</div>
            <div style="font-weight:600;font-size:14px">Database</div>
            <div style="color:var(--text-muted);font-size:13px">Amazon RDS MySQL</div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
const saved=localStorage.getItem('theme');
if(saved==='dark'){document.documentElement.setAttribute('data-theme','dark');document.getElementById('themeBtn').textContent='☀️ Light';}
function toggleTheme(){const d=document.documentElement.getAttribute('data-theme')==='dark';d?(document.documentElement.removeAttribute('data-theme'),localStorage.setItem('theme','light'),document.getElementById('themeBtn').textContent='🌙 Dark'):(document.documentElement.setAttribute('data-theme','dark'),localStorage.setItem('theme','dark'),document.getElementById('themeBtn').textContent='☀️ Light');}
const sc=localStorage.getItem('sidebar')==='collapsed';
if(sc){document.getElementById('sidebar').classList.add('collapsed');document.getElementById('mainWrap').classList.add('collapsed');document.getElementById('toggleBtn').textContent='▶';}
function toggleSidebar(){const s=document.getElementById('sidebar'),m=document.getElementById('mainWrap'),b=document.getElementById('toggleBtn');const c=s.classList.toggle('collapsed');m.classList.toggle('collapsed',c);b.textContent=c?'▶':'◀';localStorage.setItem('sidebar',c?'collapsed':'open');}
</script>
</body>
</html>
