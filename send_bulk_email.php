<?php
require_once "auth.php";
require 'vendor/autoload.php';

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

header('Content-Type: application/json');

$emails  = $_POST['emails'] ?? [];
$names   = $_POST['names'] ?? [];
$subject = $_POST['subject'] ?? 'Customer Notification';
$message = $_POST['message'] ?? 'Hello, this is a notification from AWS Customer Management System.';

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
}

echo json_encode(['success' => $success, 'failed' => $failed]);
