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


// ONLY PENDING → REJECTED

$sql = "UPDATE teachers
        SET status = 'Rejected'
        WHERE teacher_id = $teacher_id
        AND status = 'Pending'";


if (mysqli_query($conn, $sql))
{
    header(
        "Location: teacher-approval.php?success=rejected"
    );

    exit;
}
else
{
    die(
        "Reject Error: " .
        mysqli_error($conn)
    );
}

?>