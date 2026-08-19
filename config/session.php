<?php
// =========================================
// Session Management
// =========================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check Login
function checkLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

// Admin Only
function checkAdmin()
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
        header("Location: ../login.php");
        exit();
    }
}

// Teacher Only
function checkTeacher()
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
        header("Location: ../login.php");
        exit();
    }
}

// Student Only
function checkStudent()
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
        header("Location: ../login.php");
        exit();
    }
}
?>