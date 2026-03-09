<?php

require 'vendor/autoload.php';

use Twilio\Rest\Client;

$sid = "YOUR_TWILIO_SID";
$token = "YOUR_TWILIO_TOKEN";

$client = new Client($sid, $token);

$phone = $_GET["phone"];

if(substr($phone,0,1) == "0"){
    $phone = "+84".substr($phone,1);
}

try{

$message = $client->messages->create(
    $phone,
    [
        "from" => "+17346008274",
        "body" => "Hello from AWS Customer Management System"
    ]
);

echo "<h2>SMS Sent</h2>";

}catch(Exception $e){

echo "SMS failed: ".$e->getMessage();

}

?>
