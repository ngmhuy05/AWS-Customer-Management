<?php
require_once "auth.php";
require_once "db.php";
require 'vendor/autoload.php';

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

$success = false;
$error = "";
$emailTo = isset($_GET['email']) ? $_GET['email'] : "";

if ($_SERVER["REQUEST_METHOD"] == "POST" || $emailTo) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $emailTo  = $_POST['email'];
        $subject  = $_POST['subject'];
        $message  = $_POST['message'];
    } else {
        $subject = "Customer Notification";
        $message = "This is a notification from AWS Customer Management System.";
    }

    $ses = new SesClient([
        'region'  => 'us-east-1',
        'version' => 'latest',
    ]);

    try {
        $ses->sendEmail([
            'Source' => 'CustomerHub <nguyenhuy142005@gmail.com>',
            'Destination' => ['ToAddresses' => [$emailTo]],
            'Message' => [
                'Subject' => ['Data' => $subject],
                'Body'    => ['Text' => ['Data' => $message]],
            ],
        ]);
        $success = true;
        logActivity($conn, $_SESSION['user_id'], 'email', "Gửi email đến: $emailTo");
    } catch (AwsException $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Send Email — CustomerHub</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>*{font-family:'Inter',sans-serif;}</style>
</head>
<body>
<nav class="navbar">
  <a href="/customers" class="navbar-brand"><span class="dot"></span> CustomerHub</a>
  <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙 Dark</button>
</nav>

<div class="container-sm">
  <?php if ($success): ?>
  <div class="result-page">
    <div class="result-box">
      <div class="result-icon">✉️</div>
      <h2>Email Sent!</h2>
      <p>Email successfully delivered to <strong><?= htmlspecialchars($emailTo) ?></strong></p>
      <div class="alert alert-success">✅ Sent via <strong>AWS SES</strong> — delivery confirmed</div>
      <a href="/customers" class="btn btn-primary">← Back to Customers</a>
    </div>
  </div>
  <?php else: ?>
  <div class="page-header" style="margin-top:32px">
    <h1>Send Email</h1>
  </div>
  <?php if ($error): ?>
    <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <div class="card">
    <div class="card-body">
      <form method="post">
        <div class="form-group">
          <label class="form-label">To</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($emailTo) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Subject</label>
          <input type="text" name="subject" class="form-control" value="Customer Notification" required>
        </div>
        <div class="form-group">
          <label class="form-label">Message</label>
          <textarea name="message" class="form-control" rows="6" style="resize:vertical" required>This is a notification from AWS Customer Management System.</textarea>
        </div>
        <div class="form-actions">
          <a href="/customers" class="btn btn-outline">← Back</a>
          <button type="submit" class="btn btn-primary">✉️ Send Email</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
const saved=localStorage.getItem('theme');
if(saved==='dark'){document.documentElement.setAttribute('data-theme','dark');document.getElementById('themeBtn').textContent='☀️ Light';}
function toggleTheme(){const d=document.documentElement.getAttribute('data-theme')==='dark';d?(document.documentElement.removeAttribute('data-theme'),localStorage.setItem('theme','light'),document.getElementById('themeBtn').textContent='🌙 Dark'):(document.documentElement.setAttribute('data-theme','dark'),localStorage.setItem('theme','dark'),document.getElementById('themeBtn').textContent='☀️ Light');}
</script>
</body>
</html>
