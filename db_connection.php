<?php
$con = new mysqli("localhost", "root", "", "social_app");
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>
