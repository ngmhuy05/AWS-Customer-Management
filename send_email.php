<?php

require 'vendor/autoload.php';

$email = new \SendGrid\Mail\Mail(); 

$email->setFrom("nguyenhuy142005@gmail.com", "Customer System");
$email->setSubject("Customer Notification");

$email->addTo($_GET['email']);

$email->addContent(
    "text/plain",
    "Hello, this is a test email from AWS Customer Management System."
);

$sendgrid = new \SendGrid("YOUR_SENDGRID_API_KEY");

try {

$response = $sendgrid->send($email);

echo "<h2>Email Sent Successfully</h2>";

} catch (Exception $e) {

echo "Email failed: " . $e->getMessage();

}

echo "<br><br>";
echo "<a href='customers.php'>Back</a>";

?>
