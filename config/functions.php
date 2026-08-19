<?php

// Clean Input
function clean($data)
{
    return htmlspecialchars(trim($data));
}

// Password Hash
function encryptPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify Password
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

// Redirect
function redirect($page)
{
    header("Location: $page");
    exit();
}

// Show Alert
function alert($message)
{
    echo "<script>alert('$message');</script>";
}

// Current Date
function today()
{
    return date("Y-m-d");
}

// Current Date & Time
function now()
{
    return date("Y-m-d H:i:s");
}

// GPA Calculation
function calculateGrade($marks)
{
    if ($marks >= 80) return "A+";
    elseif ($marks >= 75) return "A";
    elseif ($marks >= 70) return "A-";
    elseif ($marks >= 65) return "B+";
    elseif ($marks >= 60) return "B";
    elseif ($marks >= 55) return "B-";
    elseif ($marks >= 50) return "C+";
    elseif ($marks >= 45) return "C";
    elseif ($marks >= 40) return "D";
    else return "F";
}

// Grade Point
function calculateGPA($marks)
{
    if ($marks >= 80) return 4.00;
    elseif ($marks >= 75) return 3.75;
    elseif ($marks >= 70) return 3.50;
    elseif ($marks >= 65) return 3.25;
    elseif ($marks >= 60) return 3.00;
    elseif ($marks >= 55) return 2.75;
    elseif ($marks >= 50) return 2.50;
    elseif ($marks >= 45) return 2.25;
    elseif ($marks >= 40) return 2.00;
    else return 0.00;
}

// CGPA
function calculateCGPA($totalPoint, $totalCredit)
{
    if ($totalCredit == 0) {
        return 0;
    }

    return round($totalPoint / $totalCredit, 2);
}
?>