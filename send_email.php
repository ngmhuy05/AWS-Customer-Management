<?php
require 'vendor/autoload.php';

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

$success = false;
$error = "";
$emailTo = isset($_GET['email']) ? $_GET['email'] : "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emailTo  = $_POST['email'];
    $subject  = $_POST['subject'];
    $message  = $_POST['message'];

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
<title>Send Email</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar">
  <a href="customers.php" class="navbar-brand"><span class="dot"></span> CustomerHub</a>
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
      <a href="customers.php" class="btn btn-primary">← Back to Customers</a>
    </div>
  </div>
<?php else: ?>

  <?php if ($error): ?>
    <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="page-header">
    <h1>Send Email</h1>
    <p>Send a message to <strong><?= htmlspecialchars($emailTo) ?></strong></p>
  </div>

  <div class="card" style="margin-bottom:16px">
    <div class="card-header"><h2>⚡ Quick Send</h2></div>
    <div class="card-body">
      <p style="color:var(--text-muted);font-size:14px;margin-bottom:16px">Send a default notification message instantly.</p>
      <form method="post">
        <input type="hidden" name="email" value="<?= htmlspecialchars($emailTo) ?>">
        <input type="hidden" name="subject" value="Customer Notification">
        <input type="hidden" name="message" value="Hello, this is a notification from AWS Customer Management System.">
        <button type="submit" class="btn btn-primary">⚡ Send Default Message</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h2>✏️ Custom Message</h2></div>
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="email" value="<?= htmlspecialchars($emailTo) ?>">
        <div class="form-group">
          <label class="form-label">To</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($emailTo) ?>" disabled>
        </div>
        <div class="form-group">
          <label class="form-label">Subject</label>
          <input type="text" name="subject" class="form-control" placeholder="Enter subject..." required>
        </div>
        <div class="form-group">
          <label class="form-label">Message</label>
          <textarea name="message" class="form-control" rows="5" placeholder="Write your message here..." required style="resize:vertical"></textarea>
        </div>
        <div class="form-actions">
          <a href="customers.php" class="btn btn-outline">← Back</a>
          <button type="submit" class="btn btn-primary">✉️ Send Email</button>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>
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
