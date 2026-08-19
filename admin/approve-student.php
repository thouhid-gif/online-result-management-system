<?php

session_start();

include '../config/database.php';
include '../config/session.php';

checkAdmin();


// ==================================================
// CHECK STUDENT ID
// ==================================================

if (!isset($_GET['id'])) {

    header("Location: student_approval.php");

    exit();
}


$student_id = (int) $_GET['id'];


if ($student_id <= 0) {

    header("Location: student_approval.php");

    exit();
}


// ==================================================
// APPROVE STUDENT
// ==================================================

$stmt = mysqli_prepare(
    $conn,

    "UPDATE students

     SET status = 'Active'

     WHERE student_id = ?

     AND status = 'Pending'"
);


if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $student_id
    );


    mysqli_stmt_execute($stmt);


    mysqli_stmt_close($stmt);
}


// ==================================================
// BACK
// ==================================================

header(
    "Location: student_approval.php?success=approved"
);

exit();

?>