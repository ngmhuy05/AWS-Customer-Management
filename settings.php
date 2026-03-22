
<?php
require_once "auth.php";
require_once "db.php";

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $result = $conn->query("SELECT password FROM users WHERE id=$user_id");
    $user = $result->fetch_assoc();

    if (!password_verify($current, $user['password'])) {
        $error = "Mật khẩu hiện tại không đúng.";
    } elseif (strlen($new) < 6) {
        $error = "Mật khẩu mới phải có ít nhất 6 ký tự.";
    } elseif ($new !== $confirm) {
        $error = "Mật khẩu xác nhận không khớp.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$hashed' WHERE id=$user_id");
        logActivity($conn, $user_id, 'update', "Đổi mật khẩu tài khoản");
        $success = "Đổi mật khẩu thành công!";
    }
}

$user_info = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
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
.sidebar-logo{font-size:18px;font-weight:700;color:var(--primary);white-space:nowrap;display:flex;align-items:center;gap:8px;text-decoration:none;overflow:hidden;font-family:'Inter',sans-serif!important;}
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
.sidebar.collapsed .nav-item:hover::after{content:attr(data-tip);position:absolute;left:50px;top:50%;transform:translateY(-50%);background:var(--text);color:var(--white);padding:4px 10px;border-radius:6px;font-size:12px;white-space:nowrap;pointer-events:none;z-index:300;}
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
.settings-grid{display:grid;grid-template-columns:280px 1fr;gap:24px;max-width:900px;}
.settings-menu{display:flex;flex-direction:column;gap:4px;}
.settings-menu-item{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;font-size:14px;font-weight:500;color:var(--text-muted);cursor:pointer;text-decoration:none;transition:background .15s,color .15s;}
.settings-menu-item:hover{background:var(--bg);color:var(--text);}
.settings-menu-item.active{background:var(--primary-light);color:var(--primary);}
.settings-menu-item .icon{font-size:16px;width:20px;text-align:center;}
.settings-panel{background:var(--white);border:1px solid var(--border);border-radius:16px;padding:32px;}
.settings-panel h2{font-size:18px;font-weight:700;color:var(--text);margin-bottom:6px;}
.settings-panel .desc{font-size:14px;color:var(--text-muted);margin-bottom:28px;padding-bottom:20px;border-bottom:1px solid var(--border);}
.form-group{margin-bottom:20px;}
.form-label{display:block;font-size:14px;font-weight:500;color:var(--text);margin-bottom:7px;}
.form-control{width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;font-family:'Inter',sans-serif;font-size:15px;color:var(--text);background:var(--white);outline:none;transition:border .15s,box-shadow .15s;box-sizing:border-box;}
.form-control:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(26,86,219,.1);}
.form-control:disabled{background:var(--bg);color:var(--text-muted);cursor:not-allowed;}
.alert-success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;border-radius:10px;padding:12px 16px;font-size:14px;margin-bottom:20px;display:flex;align-items:center;gap:8px;}
.alert-danger{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;font-size:14px;margin-bottom:20px;display:flex;align-items:center;gap:8px;}
.profile-header{display:flex;align-items:center;gap:16px;margin-bottom:28px;padding-bottom:20px;border-bottom:1px solid var(--border);}
.profile-avatar-lg{width:64px;height:64px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:24px;flex-shrink:0;}
.profile-info .name{font-size:18px;font-weight:700;color:var(--text);}
.profile-info .email{font-size:14px;color:var(--text-muted);margin-top:2px;}
.btn{font-family:'Inter',sans-serif;font-size:14px;font-weight:500;padding:10px 20px;border-radius:10px;cursor:pointer;border:none;transition:all .15s;display:inline-flex;align-items:center;gap:6px;}
.btn-primary{background:var(--primary);color:#fff;}
.btn-primary:hover{background:var(--primary-dark);transform:translateY(-1px);}
.btn-outline{background:var(--white);color:var(--text);border:1px solid var(--border);}
.btn-outline:hover{border-color:var(--primary);color:var(--primary);}
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
    <div><div class="topbar-title">Cài đặt</div><div class="topbar-sub">Quản lý tài khoản của bạn</div></div>
    <div style="margin-left:auto">
      <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙 Dark</button>
    </div>
  </div>

  <div class="content">
    <div class="settings-grid">

      <!-- Menu trái -->
      <div>
        <div class="settings-menu">
          <a href="#profile" class="settings-menu-item active" onclick="showTab('profile',this)">
            <span class="icon">👤</span> Thông tin tài khoản
          </a>
          <a href="#password" class="settings-menu-item" onclick="showTab('password',this)">
            <span class="icon">🔒</span> Đổi mật khẩu
          </a>
        </div>
      </div>

      <!-- Panel phải -->
      <div>

        <!-- Tab: Thông tin -->
        <div class="settings-panel" id="tab-profile">
          <h2>Thông tin tài khoản</h2>
          <p class="desc">Xem thông tin tài khoản của bạn</p>
          <div class="profile-header">
            <div class="profile-avatar-lg"><?= strtoupper(substr($username,0,1)) ?></div>
            <div class="profile-info">
              <div class="name"><?= htmlspecialchars($user_info['username']) ?></div>
              <div class="email"><?= htmlspecialchars($user_info['email']) ?></div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Tên đăng nhập</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user_info['username']) ?>" disabled>
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user_info['email']) ?>" disabled>
          </div>
          <div class="form-group">
            <label class="form-label">Ngày tạo</label>
            <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($user_info['created_at'])) ?>" disabled>
          </div>
        </div>

        <!-- Tab: Đổi mật khẩu -->
        <div class="settings-panel" id="tab-password" style="display:none">
          <h2>Đổi mật khẩu</h2>
          <p class="desc">Cập nhật mật khẩu để bảo mật tài khoản</p>

          <?php if ($success): ?>
            <div class="alert-success">✅ <?= htmlspecialchars($success) ?></div>
          <?php endif; ?>
          <?php if ($error): ?>
            <div class="alert-danger">❌ <?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="post">
            <input type="hidden" name="tab" value="password">
            <div class="form-group">
              <label class="form-label">Mật khẩu hiện tại</label>
              <input type="password" name="current_password" class="form-control" placeholder="Nhập mật khẩu hiện tại" required>
            </div>
            <div class="form-group">
              <label class="form-label">Mật khẩu mới</label>
              <input type="password" name="new_password" class="form-control" placeholder="Ít nhất 6 ký tự" required>
            </div>
            <div class="form-group">
              <label class="form-label">Xác nhận mật khẩu mới</label>
              <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới" required>
            </div>
            <div style="display:flex;gap:12px">
              <button type="submit" class="btn btn-primary">🔒 Đổi mật khẩu</button>
              <button type="reset" class="btn btn-outline">Hủy</button>
            </div>
          </form>
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

function showTab(tab, el) {
  document.querySelectorAll('.settings-panel').forEach(p=>p.style.display='none');
  document.querySelectorAll('.settings-menu-item').forEach(i=>i.classList.remove('active'));
  document.getElementById('tab-'+tab).style.display='block';
  el.classList.add('active');
  return false;
}

// Nếu có lỗi/success thì mở tab đổi mật khẩu luôn
<?php if ($error || $success): ?>
showTab('password', document.querySelector('[onclick*="password"]'));
<?php endif; ?>
</script>
</body>
</html>
