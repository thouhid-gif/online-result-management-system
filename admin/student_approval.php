<?php

session_start();

include '../config/database.php';
include '../config/session.php';

checkAdmin();


// ==================================================
// GET PENDING STUDENTS
// ==================================================

$sql = "SELECT
            s.*,
            d.department_name,
            sem.semester_name

        FROM students s

        LEFT JOIN departments d
            ON s.department_id = d.department_id

        LEFT JOIN semesters sem
            ON s.semester_id = sem.semester_id

        WHERE s.status = 'Pending'

        ORDER BY s.student_id DESC";


$students = mysqli_query(
    $conn,
    $sql
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Student Approval</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

</head>


<body class="bg-light">


<div class="container mt-4">


    <!-- =========================================
         HEADER
    ========================================== -->

    <div
        class="d-flex
               justify-content-between
               align-items-center
               mb-3"
    >

        <h3>

            Student Approval

        </h3>


        <a
            href="dashboard.php"
            class="btn btn-secondary"
        >

            Back

        </a>

    </div>



    <!-- =========================================
         SUCCESS / ERROR MESSAGE
    ========================================== -->

    <?php if (isset($_GET['success'])) { ?>


        <?php if ($_GET['success'] == 'approved') { ?>

            <div class="alert alert-success">

                Student approved successfully.

            </div>

        <?php } ?>


        <?php if ($_GET['success'] == 'rejected') { ?>

            <div class="alert alert-danger">

                Student rejected successfully.

            </div>

        <?php } ?>


    <?php } ?>



    <!-- =========================================
         CARD
    ========================================== -->

    <div class="card shadow">


        <div class="card-header bg-primary text-white">

            Pending Student Registration

        </div>


        <div class="card-body">


            <div class="table-responsive">


                <table
                    class="table
                           table-bordered
                           table-hover"
                >

                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>

                            <th>Roll</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Phone</th>

                            <th>Department</th>

                            <th>Semester</th>

                            <th>Status</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    if (
                        $students &&
                        mysqli_num_rows($students) > 0
                    ) {

                        while (
                            $row =
                            mysqli_fetch_assoc($students)
                        ) {

                    ?>

                        <tr>


                            <!-- ID -->

                            <td>

                                <?php

                                echo (int)
                                    $row['student_id'];

                                ?>

                            </td>


                            <!-- ROLL -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['student_roll']
                                );

                                ?>

                            </td>


                            <!-- NAME -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['full_name']
                                );

                                ?>

                            </td>


                            <!-- EMAIL -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['email']
                                );

                                ?>

                            </td>


                            <!-- PHONE -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['phone']
                                );

                                ?>

                            </td>


                            <!-- DEPARTMENT -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['department_name']
                                    ?? 'N/A'
                                );

                                ?>

                            </td>


                            <!-- SEMESTER -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['semester_name']
                                    ?? 'N/A'
                                );

                                ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="badge
                                           bg-warning
                                           text-dark"
                                >

                                    Pending

                                </span>

                            </td>


                            <!-- ACTION -->

                            <td>


                                <!-- APPROVE -->

                                <a
                                    href="approve-student.php?id=<?php
                                    echo (int)
                                        $row['student_id'];
                                    ?>"
                                    class="btn
                                           btn-success
                                           btn-sm"
                                    onclick="
                                        return confirm(
                                            'Approve this student?'
                                        );
                                    "
                                >

                                    Approve

                                </a>


                                <!-- REJECT -->

                                <a
                                    href="reject-student.php?id=<?php
                                    echo (int)
                                        $row['student_id'];
                                    ?>"
                                    class="btn
                                           btn-danger
                                           btn-sm"
                                    onclick="
                                        return confirm(
                                            'Reject this student?'
                                        );
                                    "
                                >

                                    Reject

                                </a>


                            </td>

                        </tr>


                    <?php

                        }

                    }

                    else {

                    ?>

                        <tr>

                            <td
                                colspan="9"
                                class="text-center text-danger"
                            >

                                No Pending Student Found

                            </td>

                        </tr>

                    <?php

                    }

                    ?>


                    </tbody>

                </table>

            </div>

        </div>

    </div>


</div>


</body>

</html>