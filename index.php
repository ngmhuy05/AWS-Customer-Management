<?php
session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: customers.php");
} else {
    header("Location: login.php");
}
exit;
?>
