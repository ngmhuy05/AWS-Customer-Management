<?php
require_once "auth.php";
$servername = "customer-db.c50s0oueymyt.ap-southeast-1.rds.amazonaws.com";
$username = "admin";
$password = "Customer123!";
$dbname = "customerdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    if ($action === 'add') {
        $name=$conn->real_escape_string($_POST['name']);$email=$conn->real_escape_string($_POST['email']);
        $phone=$conn->real_escape_string($_POST['phone']);$address=$conn->real_escape_string($_POST['address']);
        if ($conn->query("INSERT INTO customers (name,email,phone,address) VALUES ('$name','$email','$phone','$address')")) {
            $id=$conn->insert_id;
            echo json_encode(['success'=>true,'id'=>$id,'name'=>$name,'email'=>$email,'phone'=>$phone,'address'=>$address]);
        } else echo json_encode(['success'=>false,'error'=>$conn->error]);
        exit;
    }
    if ($action === 'update') {
        $id=$conn->real_escape_string($_POST['id']);$name=$conn->real_escape_string($_POST['name']);
        $email=$conn->real_escape_string($_POST['email']);$phone=$conn->real_escape_string($_POST['phone']);$address=$conn->real_escape_string($_POST['address']);
        echo $conn->query("UPDATE customers SET name='$name',email='$email',phone='$phone',address='$address' WHERE id=$id")
            ? json_encode(['success'=>true]) : json_encode(['success'=>false,'error'=>$conn->error]);
        exit;
    }
    if ($action === 'delete') {
        $id=intval($_POST['id']);$conn->query("DELETE FROM customers WHERE id=$id");
        echo json_encode(['success'=>true]);exit;
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Sora:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
*{font-family:'Plus Jakarta Sans',sans-serif;}
.navbar-brand{font-family:'Sora',sans-serif!important;letter-spacing:.5px;}

/* Navbar extended */
.navbar{padding:0 32px;display:flex;align-items:center;justify-content:space-between;}
.navbar-center{display:flex;flex-direction:column;align-items:center;justify-content:center;}
.navbar-center .title{font-family:'Sora',sans-serif;font-size:15px;font-weight:600;color:var(--text);}
.navbar-center .sub{font-size:11px;color:var(--text-muted);}

/* Full-width layout */
.app-layout{display:grid;grid-template-columns:1fr 380px;gap:0;height:calc(100vh - 64px);overflow:hidden;}
.left-pane{overflow-y:auto;padding:24px 20px 24px 28px;border-right:1px solid var(--border);}
.right-pane{overflow-y:auto;padding:24px 28px 24px 20px;background:var(--bg);}

/* Bulk bar */
.bulk-bar{display:none;align-items:center;gap:8px;background:var(--primary-light);border:1px solid var(--primary);border-radius:var(--radius-sm);padding:10px 14px;margin-bottom:14px;font-size:13px;color:var(--primary);flex-wrap:wrap;}
.bulk-bar.visible{display:flex;}
input[type="checkbox"]{width:16px;height:16px;cursor:pointer;accent-color:var(--primary);}

/* Icon btns */
.icon-btn{width:30px;height:30px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;border:none;background:transparent;transition:all .15s;}
.icon-btn:hover{transform:scale(1.15);}
.icon-btn.edit{background:#fef3c7;}
.icon-btn.del{background:#fee2e2;}

/* Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:200;align-items:center;justify-content:center;}
.modal-overlay.active{display:flex;}
.modal{background:var(--white);border-radius:var(--radius);padding:28px;max-width:520px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.modal h3{font-family:'Sora',sans-serif;font-size:18px;margin-bottom:20px;}
.modal-actions{display:flex;gap:12px;justify-content:flex-end;margin-top:24px;}

/* Toast */
.toast{position:fixed;top:76px;right:24px;background:var(--white);border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 16px;box-shadow:var(--shadow-lg);font-size:13px;display:flex;align-items:center;gap:8px;z-index:999;transform:translateY(-10px);opacity:0;transition:all .3s;max-width:280px;}
.toast.show{transform:translateY(0);opacity:1;}
.toast.success{border-left:3px solid var(--success);}
.toast.danger{border-left:3px solid var(--danger);}

/* Right panel sections */
.panel-section{margin-bottom:20px;}
.panel-section h3{font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;}
.stat-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);}
.stat-row:last-child{border-bottom:none;}
.stat-row .lbl{font-size:13px;color:var(--text-muted);}
.stat-row .val{font-size:22px;font-weight:600;font-family:'Sora',sans-serif;color:var(--text);}

/* Email compose area */
.compose-box{background:var(--white);border:1px solid var(--border);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow);}
.recipient-chips{display:flex;flex-wrap:wrap;gap:6px;min-height:32px;padding:6px 10px;border:1px solid var(--border);border-radius:var(--radius-sm);margin-bottom:12px;background:var(--bg);cursor:text;}
.chip{background:var(--primary-light);color:var(--primary);font-size:12px;padding:3px 8px;border-radius:999px;display:flex;align-items:center;gap:4px;}
.chip button{border:none;background:none;cursor:pointer;color:var(--primary);font-size:14px;line-height:1;padding:0;}
.send-btn-full{width:100%;justify-content:center;margin-top:12px;}
</style>
</head>
<body>

<nav class="navbar">
  <a href="customers.php" class="navbar-brand"><span class="dot"></span> CustomerHub</a>
  <div class="navbar-center">
    <div class="title">Customer Management</div>
    <div class="sub">Manage and communicate with your customers</div>
  </div>
  <div style="display:flex;align-items:center;gap:8px">
    <span style="font-size:13px;color:var(--text-muted)">👤 <?= $_SESSION['username'] ?></span>
    <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙 Dark</button>
    <a href="logout.php" class="theme-toggle" style="text-decoration:none;color:var(--danger)">Logout</a>
  </div>
</nav>

<div class="app-layout">
  <!-- LEFT -->
  <div class="left-pane">
    <div class="bulk-bar" id="bulkBar">
      <span id="selectedCount">0 selected</span>
      <button class="btn btn-danger btn-sm" onclick="bulkDelete()">🗑️ Delete Selected</button>
      <button class="btn btn-outline btn-sm" onclick="clearSelection()">✕ Clear</button>
    </div>

    <div class="card">
      <div class="card-header">
        <h2>All Customers</h2>
        <div style="display:flex;gap:8px">
          <button class="btn btn-outline btn-sm" onclick="toggleSelectAll()" id="selectAllBtn">☐ Select All</button>
          <button class="btn btn-primary" onclick="openAddModal()">+ Add Customer</button>
        </div>
      </div>
      <div class="table-wrap">
        <div id="emptyState" style="display:<?= $total===0?'block':'none' ?>">
          <div class="empty-state"><div class="icon">👤</div><p>No customers yet.</p></div>
        </div>
        <table id="customerTable" style="display:<?= $total===0?'none':'table' ?>">
          <thead>
            <tr>
              <th style="width:36px"></th>
              <th>Customer</th>
              <th>Phone</th>
              <th>Address</th>
              <th style="width:70px">Actions</th>
            </tr>
          </thead>
          <tbody id="tbody">
            <?php foreach($rows as $row): ?>
            <tr id="row-<?= $row['id'] ?>">
              <td><input type="checkbox" class="customer-cb" value="<?= $row['id'] ?>" data-email="<?= htmlspecialchars($row['email']) ?>" data-name="<?= htmlspecialchars($row['name']) ?>" onchange="updateBulk()"></td>
              <td>
                <div style="display:flex;align-items:center">
                  <span class="avatar"><?= strtoupper(substr($row['name'],0,1)) ?></span>
                  <div>
                    <div style="font-weight:500"><?= htmlspecialchars($row['name']) ?></div>
                    <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($row['email']) ?></div>
                  </div>
                </div>
              </td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($row['address']) ?></td>
              <td>
                <div style="display:flex;gap:4px">
                  <button class="icon-btn edit" onclick='openEditModal(<?= json_encode($row) ?>)' title="Edit">✏️</button>
                  <button class="icon-btn del" onclick="confirmDelete(<?= $row['id'] ?>,'<?= htmlspecialchars(addslashes($row['name'])) ?>')" title="Delete">🗑️</button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="right-pane">

    <!-- Stats -->
    <div class="panel-section">
      <h3>Overview</h3>
      <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow)">
        <div class="stat-row"><span class="lbl">Total Customers</span><span class="val" id="statTotal"><?= $total ?></span></div>
        <div class="stat-row"><span class="lbl">Selected</span><span class="val" id="statSelected">0</span></div>
      </div>
    </div>

    <!-- Compose Email -->
    <div class="panel-section">
      <h3>Send Email</h3>
      <div class="compose-box">
        <div class="form-group">
          <label class="form-label">Subject</label>
          <input type="text" id="quickSubject" class="form-control" value="Customer Notification">
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Message</label>
          <textarea id="quickMessage" class="form-control" rows="8" style="resize:vertical">This is a notification from AWS Customer Management System.</textarea>
        </div>
        <button class="btn btn-primary send-btn-full" id="sendEmailBtn" onclick="sendBulkEmail()" disabled>✉️ Send Email</button>
      </div>
    </div>
  </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="formModal">
  <div class="modal">
    <h3 id="modalTitle">Add Customer</h3>
    <input type="hidden" id="editId">
    <div class="form-row">
      <div class="form-group"><label class="form-label">Full Name *</label><input type="text" id="fName" class="form-control" placeholder="Nguyen Van A"></div>
      <div class="form-group"><label class="form-label">Email *</label><input type="email" id="fEmail" class="form-control" placeholder="example@email.com"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Phone</label><input type="text" id="fPhone" class="form-control" placeholder="0765386605"></div>
      <div class="form-group"><label class="form-label">Address</label><input type="text" id="fAddress" class="form-control" placeholder="Ho Chi Minh City"></div>
    </div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeFormModal()">Cancel</button>
      <button class="btn btn-primary" id="saveBtn" onclick="saveCustomer()">Add Customer</button>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal" style="max-width:360px;text-align:center">
    <div style="font-size:36px;margin-bottom:12px">🗑️</div>
    <h3 style="font-size:17px">Delete Customer?</h3>
    <p id="deleteMsg" style="color:var(--text-muted);font-size:14px;margin:8px 0 0"></p>
    <div class="modal-actions" style="justify-content:center">
      <button class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
      <button class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const saved=localStorage.getItem('theme');
if(saved==='dark'){document.documentElement.setAttribute('data-theme','dark');document.getElementById('themeBtn').textContent='☀️ Light';}
function toggleTheme(){const d=document.documentElement.getAttribute('data-theme')==='dark';d?(document.documentElement.removeAttribute('data-theme'),localStorage.setItem('theme','light'),document.getElementById('themeBtn').textContent='🌙 Dark'):(document.documentElement.setAttribute('data-theme','dark'),localStorage.setItem('theme','dark'),document.getElementById('themeBtn').textContent='☀️ Light');}

function showToast(msg,type='success'){const t=document.getElementById('toast');t.textContent=(type==='success'?'✅ ':'❌ ')+msg;t.className='toast show '+type;setTimeout(()=>t.className='toast',3000);}

function updateBulk(){
  const checked=document.querySelectorAll('.customer-cb:checked');
  const n=checked.length;
  document.getElementById('statSelected').textContent=n;
  document.getElementById('selectedCount').textContent=n+' selected';
  document.getElementById('bulkBar').classList.toggle('visible',n>0);
  document.getElementById('sendEmailBtn').disabled=n===0;
  document.getElementById('sendEmailBtn').textContent=n>0?`✉️ Send to ${n} customer(s)`:'✉️ Send Email';
  document.getElementById('selectAllBtn').textContent=n>0&&n===document.querySelectorAll('.customer-cb').length?'☑ Deselect All':'☐ Select All';

  // Update send button text
}
function clearSelection(){document.querySelectorAll('.customer-cb:checked').forEach(cb=>cb.checked=false);updateBulk();}
let allSel=false;
function toggleSelectAll(){allSel=!allSel;document.querySelectorAll('.customer-cb').forEach(cb=>cb.checked=allSel);updateBulk();}

function sendBulkEmail(){
  const checked=document.querySelectorAll('.customer-cb:checked');
  if(checked.length===0){showToast('Select at least 1 customer','danger');return;}
  const subject=document.getElementById('quickSubject').value;
  const message=document.getElementById('quickMessage').value;
  const btn=document.getElementById('sendEmailBtn');
  btn.textContent='Sending...';btn.disabled=true;
  const fd=new FormData();
  fd.append('subject',subject);fd.append('message',message);
  checked.forEach(cb=>{fd.append('emails[]',cb.dataset.email);fd.append('names[]',cb.dataset.name);});
  fetch('send_bulk_email.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(data=>{
      clearSelection();
      showToast('Sent to '+data.success+' customer(s)'+(data.failed>0?', '+data.failed+' failed':''),data.failed>0?'danger':'success');
    })
    .catch(()=>showToast('Something went wrong','danger'))
    .finally(()=>{btn.disabled=true;btn.textContent='✉️ Send Email';});
}

function bulkDelete(){
  const checked=document.querySelectorAll('.customer-cb:checked');
  if(!checked.length)return;
  if(!confirm('Delete '+checked.length+' customer(s)?'))return;
  const ids=[...checked].map(cb=>cb.value);
  Promise.all(ids.map(id=>{const fd=new FormData();fd.append('action','delete');fd.append('id',id);return fetch('customers.php',{method:'POST',body:fd}).then(r=>r.json());}))
    .then(()=>{
      ids.forEach(id=>{const r=document.getElementById('row-'+id);if(r)r.remove();});
      const total=parseInt(document.getElementById('statTotal').textContent)-ids.length;
      document.getElementById('statTotal').textContent=total;
      if(total===0){document.getElementById('customerTable').style.display='none';document.getElementById('emptyState').style.display='block';}
      updateBulk();showToast(ids.length+' customer(s) deleted!');
    });
}

function openAddModal(){
  document.getElementById('modalTitle').textContent='Add Customer';document.getElementById('saveBtn').textContent='Add Customer';
  document.getElementById('editId').value='';['fName','fEmail','fPhone','fAddress'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('formModal').classList.add('active');setTimeout(()=>document.getElementById('fName').focus(),100);
}
function closeFormModal(){document.getElementById('formModal').classList.remove('active');}

function openEditModal(row){
  document.getElementById('modalTitle').textContent='Edit Customer';document.getElementById('saveBtn').textContent='Save Changes';
  document.getElementById('editId').value=row.id;document.getElementById('fName').value=row.name;
  document.getElementById('fEmail').value=row.email;document.getElementById('fPhone').value=row.phone;document.getElementById('fAddress').value=row.address;
  document.getElementById('formModal').classList.add('active');
}

function saveCustomer(){
  const id=document.getElementById('editId').value;
  const name=document.getElementById('fName').value.trim();const email=document.getElementById('fEmail').value.trim();
  const phone=document.getElementById('fPhone').value.trim();const address=document.getElementById('fAddress').value.trim();
  if(!name||!email){showToast('Name and email required','danger');return;}
  const fd=new FormData();fd.append('action',id?'update':'add');if(id)fd.append('id',id);
  fd.append('name',name);fd.append('email',email);fd.append('phone',phone);fd.append('address',address);
  fetch('customers.php',{method:'POST',body:fd}).then(r=>r.json()).then(data=>{
    if(data.success){
      closeFormModal();
      if(id){
        const row=document.getElementById('row-'+id);
        row.querySelectorAll('td')[1].innerHTML=`<div style="display:flex;align-items:center"><span class="avatar">${name.charAt(0).toUpperCase()}</span><div><div style="font-weight:500">${name}</div><div style="font-size:12px;color:var(--text-muted)">${email}</div></div></div>`;
        row.querySelectorAll('td')[2].textContent=phone;row.querySelectorAll('td')[3].textContent=address;
        row.querySelector('.customer-cb').dataset.email=email;row.querySelector('.customer-cb').dataset.name=name;
        row.querySelector('.icon-btn.edit').setAttribute('onclick',`openEditModal(${JSON.stringify({id,name,email,phone,address})})`);
        showToast('Customer updated!');
      } else {
        const tr=document.createElement('tr');tr.id='row-'+data.id;
        tr.innerHTML=`<td><input type="checkbox" class="customer-cb" value="${data.id}" data-email="${data.email}" data-name="${data.name}" onchange="updateBulk()"></td>
          <td><div style="display:flex;align-items:center"><span class="avatar">${data.name.charAt(0).toUpperCase()}</span><div><div style="font-weight:500">${data.name}</div><div style="font-size:12px;color:var(--text-muted)">${data.email}</div></div></div></td>
          <td>${data.phone}</td><td style="color:var(--text-muted);font-size:13px">${data.address}</td>
          <td><div style="display:flex;gap:4px"><button class="icon-btn edit" onclick='openEditModal(${JSON.stringify(data)})'>✏️</button><button class="icon-btn del" onclick="confirmDelete(${data.id},'${data.name.replace(/'/g,"\\'")}')">🗑️</button></div></td>`;
        document.getElementById('tbody').insertBefore(tr,document.getElementById('tbody').firstChild);
        document.getElementById('statTotal').textContent=parseInt(document.getElementById('statTotal').textContent)+1;
        document.getElementById('customerTable').style.display='table';document.getElementById('emptyState').style.display='none';
        showToast('Customer added!');
      }
    } else showToast(data.error||'Error','danger');
  });
}

let deleteTarget=null;
function confirmDelete(id,name){deleteTarget=id;document.getElementById('deleteMsg').textContent='Delete "'+name+'"? This cannot be undone.';document.getElementById('deleteModal').classList.add('active');document.getElementById('confirmDeleteBtn').onclick=doDelete;}
function closeDeleteModal(){document.getElementById('deleteModal').classList.remove('active');}
function doDelete(){
  const fd=new FormData();fd.append('action','delete');fd.append('id',deleteTarget);
  fetch('customers.php',{method:'POST',body:fd}).then(r=>r.json()).then(data=>{
    if(data.success){document.getElementById('row-'+deleteTarget).remove();closeDeleteModal();
      const total=parseInt(document.getElementById('statTotal').textContent)-1;document.getElementById('statTotal').textContent=total;
      if(total===0){document.getElementById('customerTable').style.display='none';document.getElementById('emptyState').style.display='block';}
      updateBulk();showToast('Customer deleted!');}
  });
}
['formModal','deleteModal'].forEach(id=>{document.getElementById(id).addEventListener('click',function(e){if(e.target===this)this.classList.remove('active');});});
</script>
</body>
</html>
