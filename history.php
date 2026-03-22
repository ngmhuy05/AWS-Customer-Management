<?php
require_once "auth.php";
require_once "db.php";

$username = $_SESSION['username'];

$logs = $conn->query("
    SELECT a.*, u.username
    FROM activity_logs a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 100
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lịch sử — CustomerHub</title>
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
table{width:100%;border-collapse:collapse;}
th{padding:12px 16px;text-align:left;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);border-bottom:1px solid var(--border);background:var(--bg);}
td{padding:15px 16px;border-bottom:1px solid var(--border);font-size:15px;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:var(--bg);}
.action-badge{display:inline-block;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;}
.action-add{background:#d1fae5;color:#065f46;}
.action-update{background:#fef3c7;color:#92400e;}
.action-delete{background:#fee2e2;color:#991b1b;}
.action-email{background:#e0e7ff;color:#3730a3;}
.btn{font-family:'Inter',sans-serif;font-size:14px;font-weight:500;}
.toast{position:fixed;bottom:24px;right:24px;background:var(--white);border:1px solid var(--border);border-radius:10px;padding:13px 18px;box-shadow:0 8px 24px rgba(0,0,0,.12);font-size:14px;display:flex;align-items:center;gap:8px;z-index:999;transform:translateY(10px);opacity:0;transition:all .3s;max-width:300px;}
.toast.show{transform:translateY(0);opacity:1;}
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
    <a href="/history" class="nav-item active" data-tip="Lịch sử"><span class="icon">📋</span><span class="nav-label">Lịch sử</span></a>
    <a href="/settings" class="nav-item" data-tip="Cài đặt"><span class="icon">⚙️</span><span class="nav-label">Cài đặt</span></a>
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
    <div><div class="topbar-title">Lịch sử hoạt động</div><div class="topbar-sub">Toàn bộ thao tác của tất cả người dùng</div></div>
    <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
      <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙 Dark</button>
    </div>
  </div>
  <div class="content">
    <div class="card">
      <div class="table-wrap">
        <?php if(empty($logs)): ?>
          <div class="empty-state" style="padding:48px;text-align:center">
            <div style="font-size:32px;margin-bottom:8px">📭</div>
            <p style="color:var(--text-muted)">Chưa có lịch sử hoạt động.</p>
          </div>
        <?php else: ?>
        <table>
          <thead>
            <tr><th>Thời gian</th><th>Người dùng</th><th>Hành động</th><th>Chi tiết</th></tr>
          </thead>
          <tbody>
            <?php foreach($logs as $log): ?>
            <tr>
              <td style="font-size:13px;color:var(--text-muted);white-space:nowrap"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
              <td><span style="font-weight:600">👤 <?= htmlspecialchars($log['username'] ?? 'Unknown') ?></span></td>
              <td>
                <?php $badge = match($log['action']) {
                  'add'    => ['class'=>'action-add',    'label'=>'➕ Thêm'],
                  'update' => ['class'=>'action-update', 'label'=>'✏️ Sửa'],
                  'delete' => ['class'=>'action-delete', 'label'=>'🗑️ Xóa'],
                  'email'  => ['class'=>'action-email',  'label'=>'✉️ Email'],
                  default  => ['class'=>'',              'label'=>$log['action']],
                }; ?>
                <span class="action-badge <?= $badge['class'] ?>"><?= $badge['label'] ?></span>
              </td>
              <td style="font-size:14px"><?= htmlspecialchars($log['description']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
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
