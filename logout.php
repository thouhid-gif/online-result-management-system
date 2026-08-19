<?php
session_start();

// Remove all session variables
$_SESSION = array();

// Destroy the session
setcookie("remember_email", "", time()-3600, "/");
setcookie("remember_role", "", time()-3600, "/");
session_destroy();

// Prevent browser cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to Login Page
header("Location: login.php");

exit();
?>