<?php
$servername = "customer-db.c50s0oueymyt.ap-southeast-1.rds.amazonaws.com";
$username = "admin";
$password = "Customer123!";
$dbname = "customerdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT * FROM customers ORDER BY id DESC");
$total = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Customer Management</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
  <a href="customers.php" class="navbar-brand">
    <span class="dot"></span> CustomerHub
  </a>
  <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙 Dark</button>
</nav>

<div class="container">
  <div class="page-header">
    <h1>Customer Management</h1>
    <p>Manage and communicate with your customers</p>
  </div>

  <div class="stats">
    <div class="stat-card">
      <div class="label">Total Customers</div>
      <div class="value"><?= $total ?> <span class="icon">👥</span></div>
    </div>
    <div class="stat-card">
      <div class="label">Email Service</div>
      <div class="value" style="font-size:18px;margin-top:4px">SendGrid <span class="icon">✉️</span></div>
    </div>
    <div class="stat-card">
      <div class="label">Cloud Platform</div>
      <div class="value" style="font-size:18px;margin-top:4px">AWS EC2 <span class="icon">☁️</span></div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h2>All Customers</h2>
      <a href="add_customer.php" class="btn btn-primary">+ Add Customer</a>
    </div>
    <div class="table-wrap">
      <?php if ($total === 0): ?>
        <div class="empty-state">
          <div class="icon">👤</div>
          <p>No customers yet. <a href="add_customer.php">Add your first customer</a></p>
        </div>
      <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td style="color:var(--text-muted);font-size:13px"><?= $row['id'] ?></td>
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
              <div class="btn-actions">
                <a href="update_customer.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                <a href="send_email.php?email=<?= urlencode($row['email']) ?>" class="btn btn-outline btn-sm">✉️ Email</a>
                <button class="btn btn-danger btn-sm"
                  onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['name'])) ?>')">
                  🗑️ Delete
                </button>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Custom Delete Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <div class="modal-icon">🗑️</div>
    <h3>Delete Customer?</h3>
    <p id="modalMsg">This action cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeModal()">Cancel</button>
      <a href="#" class="btn btn-danger" id="confirmBtn">Delete</a>
    </div>
  </div>
</div>

<script>
// Dark mode
const saved = localStorage.getItem('theme');
if (saved === 'dark') {
  document.documentElement.setAttribute('data-theme', 'dark');
  document.getElementById('themeBtn').textContent = '☀️ Light';
}
function toggleTheme() {
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  if (isDark) {
    document.documentElement.removeAttribute('data-theme');
    localStorage.setItem('theme', 'light');
    document.getElementById('themeBtn').textContent = '🌙 Dark';
  } else {
    document.documentElement.setAttribute('data-theme', 'dark');
    localStorage.setItem('theme', 'dark');
    document.getElementById('themeBtn').textContent = '☀️ Light';
  }
}

// Delete modal
function confirmDelete(id, name) {
  document.getElementById('modalMsg').textContent = 'Are you sure you want to delete "' + name + '"?';
  document.getElementById('confirmBtn').href = 'delete_customer.php?id=' + id;
  document.getElementById('deleteModal').classList.add('active');
}
function closeModal() {
  document.getElementById('deleteModal').classList.remove('active');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>
</body>
</html>
