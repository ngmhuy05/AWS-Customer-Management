
<?php
require_once "auth.php";
require_once "db.php";
require 'vendor/autoload.php';

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

header('Content-Type: application/json');

$emails  = $_POST['emails'] ?? [];
$names   = $_POST['names'] ?? [];
$subject = $_POST['subject'] ?? 'Customer Notification';
$message = $_POST['message'] ?? 'This is a notification from AWS Customer Management System.';

$success = 0;
$failed  = 0;

if (!empty($emails)) {
    $ses = new SesClient([
        'region'  => 'us-east-1',
        'version' => 'latest',
    ]);

    foreach ($emails as $i => $emailTo) {
        $name = $names[$i] ?? $emailTo;
        try {
            $ses->sendEmail([
                'Source' => 'CustomerHub <nguyenhuy142005@gmail.com>',
                'Destination' => ['ToAddresses' => [$emailTo]],
                'Message' => [
                    'Subject' => ['Data' => $subject],
                    'Body'    => ['Text' => ['Data' => "Hello $name,\n\n$message"]],
                ],
            ]);
            $success++;
        } catch (AwsException $e) {
            $failed++;
        }
    }

    if ($success > 0) {
        $emailList = implode(', ', array_slice($emails, 0, 3));
        $desc = "Gửi email hàng loạt đến $success người: $emailList" . (count($emails) > 3 ? '...' : '');
        logActivity($conn, $_SESSION['user_id'], 'email', $desc);
    }
}

echo json_encode(['success' => $success, 'failed' => $failed]);
