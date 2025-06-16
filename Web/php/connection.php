<?php
$_servername = "localhost:3307";
$_username = "root";
$_password = "parabook";
$_database = "parabook";

$connect = mysqli_connect($_servername, $_username, $_password, $_database);

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
?>