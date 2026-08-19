<?php

session_start();

include '../config/database.php';
include '../config/session.php';

checkAdmin();


if (
    !isset($_GET['id']) ||
    empty($_GET['id'])
)
{
    header("Location: teacher-approval.php");
    exit;
}


$teacher_id = intval($_GET['id']);


// ONLY PENDING → ACTIVE

$sql = "UPDATE teachers
        SET status = 'Active'
        WHERE teacher_id = $teacher_id
        AND status = 'Pending'";


if (mysqli_query($conn, $sql))
{
    header(
        "Location: teacher-approval.php?success=approved"
    );

    exit;
}
else
{
    die(
        "Approve Error: " .
        mysqli_error($conn)
    );
}

?>