<?php
// =========================================
// Online Result Management System
// Database Connection
// =========================================

$host = "localhost";
$username = "root";
$password = "";
$database = "online_result_management_system";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
date_default_timezone_set("Asia/Dhaka");
?>