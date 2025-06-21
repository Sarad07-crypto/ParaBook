<?php
$_servername = "localhost:3307";
$_username = "root";
$_password = "";
$_database = "parabook";

$connect = mysqli_connect($_servername, $_username, $_password, $_database);

if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}
?>