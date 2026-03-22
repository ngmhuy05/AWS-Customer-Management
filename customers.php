<?php
require_once "auth.php";
require_once "db.php";

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    if ($action === 'add') {
        $name=$conn->real_escape_string($_POST['name']);
        $email=$conn->real_escape_string($_POST['email']);
        $phone=$conn->real_escape_string($_POST['phone']);
        $address=$conn->real_escape_string($_POST['address']);
        if ($conn->query("INSERT INTO customers (name,email,phone,address,user_id) VALUES ('$name','$email','$phone','$address',$user_id)")) {
            $id=$conn->insert_id;
            logActivity($conn,$user_id,'add',"Thêm khách hàng: $name (ID: $id)");
            echo json_encode(['success'=>true,'id'=>$id,'name'=>$name,'email'=>$email,'phone'=>$phone,'address'=>$address]);
        } else echo json_encode(['success'=>false,'error'=>$conn->error]);
        exit;
    }
    if ($action === 'update') {
        $id=$conn->real_escape_string($_POST['id']);
        $name=$conn->real_escape_string($_POST['name']);
        $email=$conn->real_escape_string($_POST['email']);
        $phone=$conn->real_escape_string($_POST['phone']);
        $address=$conn->real_escape_string($_POST['address']);
        if ($conn->query("UPDATE customers SET name='$name',email='$email',phone='$phone',address='$address' WHERE id=$id")) {
            logActivity($conn,$user_id,'update',"Sửa khách hàng: $name (ID: $id)");
            echo json_encode(['success'=>true]);
        } else echo json_encode(['success'=>false,'error'=>$conn->error]);
        exit;
    }
    if ($action === 'delete') {
        $id=intval($_POST['id']);
        $row=$conn->query("SELECT name FROM customers WHERE id=$id")->fetch_assoc();
        $conn->query("DELETE FROM customers WHERE id=$id");
        logActivity($conn,$user_id,'delete',"Xóa khách hàng: {$row['name']} (ID: $id)");
        echo json_encode(['success'=>true]);
        exit;
    }
}

$result=$conn->query("SELECT * FROM customers ORDER BY id DESC");
$total=$result->num_rows;
$rows=$result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>CustomerHub</title>
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
.bulk-bar{display:none;align-items:center;gap:8px;background:var(--primary-light);border:1px solid var(--primary);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:14px;color:var(--primary);flex-wrap:wrap;}
.bulk-bar.visible{display:flex;}
input[type="checkbox"]{width:16px;height:16px;cursor:pointer;accent-color:var(--primary);}
.table-wrap{overflow:auto;max-height:calc(100vh - 230px);}
table{width:100%;border-collapse:collapse;min-width:700px;}
th{padding:12px 16px;text-align:left;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);border-bottom:1px solid var(--border);background:var(--bg);}
td{padding:15px 16px;border-bottom:1px solid var(--border);font-size:15px;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:var(--bg);}
.avatar{width:38px;height:38px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;}
.icon-btn{width:32px;height:32px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer;border:none;background:transparent;transition:all .15s;}
.icon-btn:hover{transform:scale(1.12);}
.icon-btn.edit{background:#fef3c7;}
.icon-btn.del{background:#fee2e2;}
@keyframes modalIn{from{opacity:0;transform:translateY(16px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
@keyframes overlayIn{from{opacity:0}to{opacity:1}}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;}
.modal-overlay.active{display:flex;animation:overlayIn .2s ease;}
.modal-overlay.active .modal{animation:modalIn .25s cubic-bezier(.34,1.56,.64,1);}
.modal{background:var(--white);border-radius:16px;padding:32px;width:90%;box-shadow:0 24px 64px rgba(0,0,0,.2);}
.modal h3{font-size:20px;font-weight:700;margin-bottom:24px;color:var(--text);}
.modal-actions{display:flex;gap:12px;justify-content:flex-end;margin-top:28px;}
.modal .form-control{font-family:'Inter',sans-serif;font-size:15px;padding:11px 14px;}
.modal .form-label{font-family:'Inter',sans-serif;font-size:14px;font-weight:500;margin-bottom:7px;display:block;text-align:left;}
.modal .form-group{margin-bottom:18px;}
.email-modal{max-width:600px;}
.recipient-chips{display:flex;flex-wrap:wrap;gap:6px;min-height:42px;padding:8px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg);}
.chip{background:var(--primary-light);color:var(--primary);font-size:13px;padding:4px 10px;border-radius:999px;display:flex;align-items:center;gap:4px;font-weight:500;}
.chip button{border:none;background:none;cursor:pointer;color:var(--primary);font-size:15px;line-height:1;padding:0;}
.toast{position:fixed;bottom:24px;right:24px;background:var(--white);border:1px solid var(--border);border-radius:10px;padding:13px 18px;box-shadow:0 8px 24px rgba(0,0,0,.12);font-size:14px;display:flex;align-items:center;gap:8px;z-index:999;transform:translateY(10px);opacity:0;transition:all .3s;max-width:300px;}
.toast.show{transform:translateY(0);opacity:1;}
.toast.success{border-left:3px solid var(--success);}
.toast.danger{border-left:3px solid var(--danger);}
.btn{font-family:'Inter',sans-serif;font-size:14px;font-weight:500;}
.card-header h2{font-size:16px;font-weight:600;}
</style>
</head>
<body>

<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <a href="/customers" class="sidebar-logo"><span class="dot"></span><span class="logo-text">CustomerHub</span></a>
    <button class="toggle-btn" id="toggleBtn" onclick="toggleSidebar()">◀</button>
  </div>
  <nav class="sidebar-nav">
    <a href="/customers" class="nav-item active" data-tip="Khách hàng"><span class="icon">👥</span><span class="nav-label">Khách hàng</span></a>
    <a href="/history" class="nav-item" data-tip="Lịch sử"><span class="icon">📋</span><span class="nav-label">Lịch sử</span></a>
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
    <div><div class="topbar-title">Quản lý khách hàng</div><div class="topbar-sub">Thêm, sửa, xóa và liên lạc với khách hàng</div></div>
    <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
      
      <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙 Dark</button>
    </div>
  </div>
  <div class="content">
    <div class="bulk-bar" id="bulkBar">
      <span id="selectedCount">0 đã chọn</span>
      <button class="btn btn-primary btn-sm" onclick="openEmailModal()">✉️ Gửi email đã chọn</button>
      <button class="btn btn-danger btn-sm" onclick="bulkDelete()">🗑️ Xóa đã chọn</button>
      <button class="btn btn-outline btn-sm" onclick="clearSelection()">✕ Bỏ chọn</button>
    </div>
    <div class="card">
      <div class="card-header">
        <h2>Tất cả khách hàng <span style="font-size:13px;font-weight:400;color:var(--text-muted);margin-left:8px">(<span id="statTotal"><?= $total ?></span> khách hàng)</span></h2>
        <button class="btn btn-primary" onclick="openAddModal()">+ Thêm khách hàng</button>
      </div>
      <div class="table-wrap">
        <div id="emptyState" style="display:<?= $total===0?'block':'none' ?>">
          <div class="empty-state"><div class="icon">👥</div><p>Chưa có khách hàng nào.</p></div>
        </div>
        <table id="customerTable" style="display:<?= $total===0?'none':'table' ?>">
          <thead>
            <tr>
              <th style="width:40px"><input type="checkbox" id="checkAll" onchange="toggleCheckAll(this)" title="Chọn tất cả"></th>
              <th>Họ tên</th><th>Email</th><th>Điện thoại</th><th>Địa chỉ</th><th style="width:80px">Thao tác</th>
            </tr>
          </thead>
          <tbody id="tbody">
            <?php foreach($rows as $row): ?>
            <tr id="row-<?= $row['id'] ?>">
              <td><input type="checkbox" class="customer-cb" value="<?= $row['id'] ?>" data-email="<?= htmlspecialchars($row['email']) ?>" data-name="<?= htmlspecialchars($row['name']) ?>" onchange="updateBulk()"></td>
              <td><div style="display:flex;align-items:center;gap:10px"><span class="avatar"><?= strtoupper(substr($row['name'],0,1)) ?></span><span style="font-weight:600"><?= htmlspecialchars($row['name']) ?></span></div></td>
              <td style="color:var(--text-muted)"><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td style="color:var(--text-muted)"><?= htmlspecialchars($row['address']) ?></td>
              <td><div style="display:flex;gap:4px"><button class="icon-btn edit" onclick='openEditModal(<?= json_encode($row) ?>)'>✏️</button><button class="icon-btn del" onclick="confirmDelete(<?= $row['id'] ?>,'<?= htmlspecialchars(addslashes($row['name'])) ?>')">🗑️</button></div></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="formModal">
  <div class="modal" style="max-width:420px">
    <h3 id="modalTitle">Thêm khách hàng</h3>
    <input type="hidden" id="editId">
    <div class="form-group"><label class="form-label">Họ tên *</label><input type="text" id="fName" class="form-control" placeholder="Nguyễn Văn A"></div>
    <div class="form-group"><label class="form-label">Email *</label><input type="email" id="fEmail" class="form-control" placeholder="example@email.com"></div>
    <div class="form-group"><label class="form-label">Điện thoại</label><input type="text" id="fPhone" class="form-control" placeholder="09xxxxxxxx"></div>
    <div class="form-group"><label class="form-label">Địa chỉ</label><input type="text" id="fAddress" class="form-control" placeholder="TP. Hồ Chí Minh"></div>
    <div class="modal-actions"><button class="btn btn-outline" onclick="closeFormModal()">Hủy</button><button class="btn btn-primary" id="saveBtn" onclick="saveCustomer()">Thêm</button></div>
  </div>
</div>

<div class="modal-overlay" id="deleteModal">
  <div class="modal" style="max-width:360px;text-align:center">
    <div style="font-size:40px;margin-bottom:12px">🗑️</div>
    <h3>Xóa khách hàng?</h3>
    <p id="deleteMsg" style="color:var(--text-muted);font-size:15px;margin:8px 0 0"></p>
    <div class="modal-actions" style="justify-content:center"><button class="btn btn-outline" onclick="closeDeleteModal()">Hủy</button><button class="btn btn-danger" id="confirmDeleteBtn">Xóa</button></div>
  </div>
</div>

<div class="modal-overlay" id="emailModal">
  <div class="modal email-modal">
    <h3>✉️ Gửi Email</h3>
    <div class="form-group"><label class="form-label">Người nhận</label><div class="recipient-chips" id="recipientChips"><span style="font-size:14px;color:var(--text-muted)">Chưa chọn khách hàng nào</span></div></div>
    <div class="form-group"><label class="form-label">Tiêu đề</label><input type="text" id="emailSubject" class="form-control" value="Thông báo từ CustomerHub"></div>
    <div class="form-group"><label class="form-label">Nội dung</label><textarea id="emailMessage" class="form-control" rows="7" style="resize:vertical">Xin chào, đây là thông báo từ hệ thống CustomerHub.</textarea></div>
    <div class="modal-actions"><button class="btn btn-outline" onclick="closeEmailModal()">Hủy</button><button class="btn btn-primary" id="sendBtn" onclick="sendEmails()">✉️ Gửi</button></div>
  </div>
</div>

<div class="toast" id="toast"></div>
<script>
const saved=localStorage.getItem('theme');
if(saved==='dark'){document.documentElement.setAttribute('data-theme','dark');document.getElementById('themeBtn').textContent='☀️ Light';}
function toggleTheme(){const d=document.documentElement.getAttribute('data-theme')==='dark';d?(document.documentElement.removeAttribute('data-theme'),localStorage.setItem('theme','light'),document.getElementById('themeBtn').textContent='🌙 Dark'):(document.documentElement.setAttribute('data-theme','dark'),localStorage.setItem('theme','dark'),document.getElementById('themeBtn').textContent='☀️ Light');}
const sc=localStorage.getItem('sidebar')==='collapsed';
if(sc){document.getElementById('sidebar').classList.add('collapsed');document.getElementById('mainWrap').classList.add('collapsed');document.getElementById('toggleBtn').textContent='▶';}
function toggleSidebar(){const s=document.getElementById('sidebar'),m=document.getElementById('mainWrap'),b=document.getElementById('toggleBtn');const c=s.classList.toggle('collapsed');m.classList.toggle('collapsed',c);b.textContent=c?'▶':'◀';localStorage.setItem('sidebar',c?'collapsed':'open');}
function showToast(msg,type='success'){const t=document.getElementById('toast');t.textContent=(type==='success'?'✅ ':'❌ ')+msg;t.className='toast show '+type;setTimeout(()=>t.className='toast',3000);}
function toggleCheckAll(el){document.querySelectorAll('.customer-cb').forEach(cb=>cb.checked=el.checked);updateBulk();}
function updateBulk(){const cbs=document.querySelectorAll('.customer-cb'),checked=document.querySelectorAll('.customer-cb:checked'),n=checked.length;document.getElementById('checkAll').checked=n===cbs.length&&cbs.length>0;document.getElementById('checkAll').indeterminate=n>0&&n<cbs.length;document.getElementById('selectedCount').textContent=n+' đã chọn';document.getElementById('bulkBar').classList.toggle('visible',n>0);}
function clearSelection(){document.querySelectorAll('.customer-cb:checked').forEach(cb=>cb.checked=false);document.getElementById('checkAll').checked=false;document.getElementById('checkAll').indeterminate=false;updateBulk();}
function openEmailModal(){const checked=document.querySelectorAll('.customer-cb:checked');const chips=document.getElementById('recipientChips');chips.innerHTML='';if(!checked.length){chips.innerHTML='<span style="font-size:14px;color:var(--text-muted)">Chưa chọn khách hàng nào</span>';}else{checked.forEach(cb=>{const chip=document.createElement('span');chip.className='chip';chip.dataset.email=cb.dataset.email;chip.innerHTML=`${cb.dataset.name} <button onclick="this.parentElement.remove()">×</button>`;chips.appendChild(chip);});}document.getElementById('emailModal').classList.add('active');}
function closeEmailModal(){document.getElementById('emailModal').classList.remove('active');}
function sendEmails(){const chips=document.querySelectorAll('#recipientChips .chip');if(!chips.length){showToast('Chưa có người nhận','danger');return;}const btn=document.getElementById('sendBtn');btn.textContent='Đang gửi...';btn.disabled=true;const fd=new FormData();fd.append('subject',document.getElementById('emailSubject').value);fd.append('message',document.getElementById('emailMessage').value);chips.forEach(c=>{fd.append('emails[]',c.dataset.email);fd.append('names[]',c.textContent.replace('×','').trim());});fetch('/send_bulk_email',{method:'POST',body:fd}).then(r=>r.json()).then(data=>{closeEmailModal();clearSelection();showToast('Đã gửi '+data.success+' email'+(data.failed>0?', '+data.failed+' thất bại':''),data.failed>0?'danger':'success');}).catch(()=>showToast('Có lỗi xảy ra','danger')).finally(()=>{btn.disabled=false;btn.textContent='✉️ Gửi';});}
function openAddModal(){document.getElementById('modalTitle').textContent='Thêm khách hàng';document.getElementById('saveBtn').textContent='Thêm';document.getElementById('editId').value='';['fName','fEmail','fPhone','fAddress'].forEach(id=>document.getElementById(id).value='');document.getElementById('formModal').classList.add('active');setTimeout(()=>document.getElementById('fName').focus(),100);}
function closeFormModal(){document.getElementById('formModal').classList.remove('active');}
function openEditModal(row){document.getElementById('modalTitle').textContent='Sửa khách hàng';document.getElementById('saveBtn').textContent='Lưu';document.getElementById('editId').value=row.id;document.getElementById('fName').value=row.name;document.getElementById('fEmail').value=row.email;document.getElementById('fPhone').value=row.phone;document.getElementById('fAddress').value=row.address;document.getElementById('formModal').classList.add('active');}
function saveCustomer(){const id=document.getElementById('editId').value;const name=document.getElementById('fName').value.trim();const email=document.getElementById('fEmail').value.trim();const phone=document.getElementById('fPhone').value.trim();const address=document.getElementById('fAddress').value.trim();if(!name||!email){showToast('Cần nhập họ tên và email','danger');return;}const fd=new FormData();fd.append('action',id?'update':'add');if(id)fd.append('id',id);fd.append('name',name);fd.append('email',email);fd.append('phone',phone);fd.append('address',address);fetch('/customers',{method:'POST',body:fd}).then(r=>r.json()).then(data=>{if(data.success){closeFormModal();if(id){const row=document.getElementById('row-'+id);row.cells[1].innerHTML=`<div style="display:flex;align-items:center;gap:10px"><span class="avatar">${name[0].toUpperCase()}</span><span style="font-weight:600">${name}</span></div>`;row.cells[2].textContent=email;row.cells[3].textContent=phone;row.cells[4].textContent=address;row.querySelector('.customer-cb').dataset.email=email;row.querySelector('.customer-cb').dataset.name=name;row.querySelector('.icon-btn.edit').setAttribute('onclick',`openEditModal(${JSON.stringify({id,name,email,phone,address})})`);showToast('Đã cập nhật!');}else{const tr=document.createElement('tr');tr.id='row-'+data.id;tr.innerHTML=`<td><input type="checkbox" class="customer-cb" value="${data.id}" data-email="${data.email}" data-name="${data.name}" onchange="updateBulk()"></td><td><div style="display:flex;align-items:center;gap:10px"><span class="avatar">${data.name[0].toUpperCase()}</span><span style="font-weight:600">${data.name}</span></div></td><td style="color:var(--text-muted)">${data.email}</td><td>${data.phone}</td><td style="color:var(--text-muted)">${data.address}</td><td><div style="display:flex;gap:4px"><button class="icon-btn edit" onclick='openEditModal(${JSON.stringify(data)})'>✏️</button><button class="icon-btn del" onclick="confirmDelete(${data.id},'${data.name.replace(/'/g,"\\'")}')">🗑️</button></div></td>`;document.getElementById('tbody').insertBefore(tr,document.getElementById('tbody').firstChild);document.getElementById('statTotal').textContent=parseInt(document.getElementById('statTotal').textContent)+1;document.getElementById('customerTable').style.display='table';document.getElementById('emptyState').style.display='none';showToast('Đã thêm khách hàng!');}}else showToast(data.error||'Có lỗi','danger');});}
let deleteTarget=null;
function confirmDelete(id,name){deleteTarget=id;document.getElementById('deleteMsg').textContent='Xóa "'+name+'"? Không thể hoàn tác.';document.getElementById('deleteModal').classList.add('active');document.getElementById('confirmDeleteBtn').onclick=doDelete;}
function closeDeleteModal(){document.getElementById('deleteModal').classList.remove('active');}
function doDelete(){const fd=new FormData();fd.append('action','delete');fd.append('id',deleteTarget);fetch('/customers',{method:'POST',body:fd}).then(r=>r.json()).then(data=>{if(data.success){document.getElementById('row-'+deleteTarget).remove();closeDeleteModal();const total=parseInt(document.getElementById('statTotal').textContent)-1;document.getElementById('statTotal').textContent=total;if(!total){document.getElementById('customerTable').style.display='none';document.getElementById('emptyState').style.display='block';}updateBulk();showToast('Đã xóa!');}});}
function bulkDelete(){const checked=document.querySelectorAll('.customer-cb:checked');if(!checked.length)return;if(!confirm('Xóa '+checked.length+' khách hàng?'))return;const ids=[...checked].map(cb=>cb.value);Promise.all(ids.map(id=>{const fd=new FormData();fd.append('action','delete');fd.append('id',id);return fetch('/customers',{method:'POST',body:fd}).then(r=>r.json());})).then(()=>{ids.forEach(id=>document.getElementById('row-'+id)?.remove());const total=parseInt(document.getElementById('statTotal').textContent)-ids.length;document.getElementById('statTotal').textContent=total;if(!total){document.getElementById('customerTable').style.display='none';document.getElementById('emptyState').style.display='block';}updateBulk();showToast(ids.length+' khách hàng đã xóa!');});}
['formModal','deleteModal','emailModal'].forEach(id=>{document.getElementById(id).addEventListener('click',function(e){if(e.target===this)this.classList.remove('active');});});
</script>
</body>
</html>
