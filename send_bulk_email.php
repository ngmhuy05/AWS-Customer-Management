<?php
require_once "auth.php";
require_once "db.php";
require 'vendor/autoload.php';

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$emails  = $_POST['emails'] ?? [];
$names   = $_POST['names'] ?? [];
$subject = $_POST['subject'] ?? 'Thông báo từ CustomerHub';
$message = $_POST['message'] ?? 'Xin chào, đây là thông báo từ hệ thống CustomerHub.';

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
                    'Body'    => ['Text' => ['Data' => $message]],
                ],
            ]);
            $success++;
        } catch (AwsException $e) {
            $failed++;
        }
    }

    $desc = "Gửi email: '$subject' đến $success người";
    logActivity($conn, $user_id, 'email', $desc);
}

echo json_encode(['success' => $success, 'failed' => $failed]);
