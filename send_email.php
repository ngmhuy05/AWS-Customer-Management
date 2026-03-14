<?php
require 'vendor/autoload.php';

$success = false;
$error = "";

if (isset($_GET['email'])) {
    $emailTo = $_GET['email'];

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("nguyenhuy142005@gmail.com", "Customer System");
    $email->setSubject("Customer Notification");
    $email->addTo($emailTo);
    $email->addContent("text/plain", "Hello, this is a notification from AWS Customer Management System.");

    $sendgrid = new \SendGrid("YOUR_SENDGRID_API_KEY");
    try {
        $sendgrid->send($email);
        $success = true;
    } catch (Exception $e) {
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
  <a href="customers.php" class="navbar-brand">
    <span class="dot"></span> CustomerHub
  </a>
  <div class="navbar-nav">
    <a href="customers.php">Customers</a>
    <a href="add_customer.php">Add Customer</a>
  </div>
</nav>

<div class="container">
  <div class="success-page">
    <div class="success-box">
      <?php if ($success): ?>
        <div class="success-icon">✉️</div>
        <h2>Email Sent!</h2>
        <p>Email successfully delivered to <strong><?= htmlspecialchars($emailTo) ?></strong></p>
        <div class="alert alert-success" style="text-align:left;margin-bottom:20px">
          ✅ Sent via <strong>SendGrid</strong> — delivery confirmed
        </div>
      <?php else: ?>
        <div class="success-icon">❌</div>
        <h2>Email Failed</h2>
        <p><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
      <a href="customers.php" class="btn btn-primary">← Back to Customers</a>
    </div>
  </div>
</div>
</body>
</html>
