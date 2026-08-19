<?php
session_start();

include '../config/database.php';
include '../config/session.php';

checkStudent();

/* ==================================================
   STUDENT SESSION
================================================== */

$student_id = $_SESSION['user_id'];


/* ==================================================
   GET STUDENT INFORMATION
================================================== */

$query = mysqli_query($conn, "
    SELECT 
        s.*,
        d.department_name,
        sem.semester_name
    FROM students s

    LEFT JOIN departments d
        ON s.department_id = d.department_id

    LEFT JOIN semesters sem
        ON s.semester_id = sem.semester_id

    WHERE s.student_id = '$student_id'

    LIMIT 1
");


$student = mysqli_fetch_assoc($query);


/* ==================================================
   STUDENT NOT FOUND
================================================== */

if (!$student) {

    session_destroy();

    header("Location: ../login.php");

    exit();
}


/* ==================================================
   ENROLLED COURSE COUNT
================================================== */

$enrolled_count = 0;

$table_check = mysqli_query(
    $conn,
    "SHOW TABLES LIKE 'student_courses'"
);

if ($table_check && mysqli_num_rows($table_check) > 0) {

    $course_query = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM student_courses
        WHERE student_id = '$student_id'
    ");

    if ($course_query) {

        $course_row = mysqli_fetch_assoc(
            $course_query
        );

        $enrolled_count = $course_row['total'];
    }
}


/* ==================================================
   RESULT COUNT
================================================== */

$result_count = 0;

$result_table = mysqli_query(
    $conn,
    "SHOW TABLES LIKE 'final_results'"
);

if ($result_table && mysqli_num_rows($result_table) > 0) {

    $result_query = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM final_results
        WHERE student_id = '$student_id'
        AND status = 'Published'
    ");

    if ($result_query) {

        $result_row = mysqli_fetch_assoc(
            $result_query
        );

        $result_count = $result_row['total'];
    }
}



/* ==================================================
   FINAL RESULT NOTIFICATIONS
================================================== */

$finalNotifications = [];

$finalTableCheck = mysqli_query(
    $conn,
    "SHOW TABLES LIKE 'final_results'"
);

if ($finalTableCheck && mysqli_num_rows($finalTableCheck) > 0) {

    $notificationQuery = mysqli_prepare(
        $conn,
        "SELECT
            fr.final_result_id,
            fr.cgpa,
            fr.result_status,
            fr.published_at,
            sem.semester_name,
            d.department_name
         FROM final_results fr
         LEFT JOIN semesters sem
            ON fr.semester_id = sem.semester_id
         LEFT JOIN departments d
            ON fr.department_id = d.department_id
         WHERE fr.student_id = ?
         AND fr.status = 'Published'
         ORDER BY fr.published_at DESC"
    );

    if ($notificationQuery) {

        mysqli_stmt_bind_param(
            $notificationQuery,
            "i",
            $student_id
        );

        mysqli_stmt_execute($notificationQuery);

        $notificationResult =
            mysqli_stmt_get_result($notificationQuery);

        while (
            $notification =
            mysqli_fetch_assoc($notificationResult)
        ) {

            $finalNotifications[] = $notification;
        }

        mysqli_stmt_close($notificationQuery);
    }
}


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Student Dashboard
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>

        /* ==================================================
           BODY
        ================================================== */

        body {

            margin: 0;

            background: #f4f7fb;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

        }


        /* ==================================================
           SIDEBAR
        ================================================== */

        .sidebar {

            position: fixed;

            left: 0;

            top: 0;

            width: 250px;

            height: 100vh;

            background: #0d6efd;

            padding-top: 20px;

            box-shadow:
                3px 0 10px
                rgba(0, 0, 0, 0.15);

        }


        .sidebar h3 {

            font-weight: bold;

            margin-bottom: 20px;

        }


        .sidebar hr {

            margin: 0 15px 10px;

        }


        .sidebar a {

            display: block;

            padding: 15px 20px;

            color: white;

            text-decoration: none;

            font-weight: 600;

            transition: 0.3s;

        }


        .sidebar a:hover {

            background: #084298;

        }


        .sidebar a.active {

            background: #084298;

        }


        .sidebar i {

            width: 28px;

        }


        /* ==================================================
           MAIN CONTENT
        ================================================== */

        .content {

            margin-left: 250px;

            padding: 30px;

        }


        /* ==================================================
           TOP BAR
        ================================================== */

        .topbar {

            background: white;

            padding: 20px 25px;

            border-radius: 15px;

            margin-bottom: 25px;

            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.08);

        }


        .topbar h3 {

            margin: 0;

            font-weight: bold;

        }


        .topbar small {

            font-size: 15px;

        }


        /* ==================================================
           PROFILE CARD
        ================================================== */

        .profile-card {

            border: none;

            border-radius: 15px;

            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.10);

        }


        .profile {

            width: 120px;

            height: 120px;

            border-radius: 50%;

            object-fit: cover;

        }


        /* ==================================================
           STATISTICS CARD
        ================================================== */

        .stat-card {

            border: none;

            border-radius: 15px;

            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.10);

            transition: 0.3s;

        }


        .stat-card:hover {

            transform: translateY(-3px);

        }


        .stat-icon {

            font-size: 38px;

            margin-bottom: 10px;

        }


        /* ==================================================
           INFORMATION CARD
        ================================================== */

        .information-card {

            border: none;

            border-radius: 15px;

            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.10);

        }


        .information-card .card-header {

            border-radius:
                15px 15px 0 0;

        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 768px) {

            .sidebar {

                position: relative;

                width: 100%;

                height: auto;

            }


            .content {

                margin-left: 0;

                padding: 15px;

            }

        }

    </style>

</head>


<body>


<!-- ==================================================
     SIDEBAR
================================================== -->

<div class="sidebar">


    <h3 class="text-center text-white">

        <i class="fa fa-user-graduate"></i>

        Student Panel

    </h3>


    <hr class="bg-white">


    <!-- Dashboard -->

    <a
        href="dashboard.php"
        class="active"
    >

        <i class="fa fa-home"></i>

        Dashboard

    </a>


    <!-- Student Profile -->

    <a href="profile.php">

        <i class="fa fa-user"></i>

        Student Profile

    </a>


    <!-- Enroll Course -->

    <a href="enroll_course.php">

        <i class="fa fa-book"></i>

        Enroll Course

    </a>


    <!-- View Result -->

    <a href="result.php">

        <i class="fa fa-graduation-cap"></i>

        View Result

    </a>


    <!-- Logout -->

    <a href="../logout.php">

        <i class="fa fa-sign-out-alt"></i>

        Logout

    </a>


</div>



<!-- ==================================================
     MAIN CONTENT
================================================== -->

<div class="content">


    <!-- ==================================================
         TOP BAR
    ================================================== -->

    <div class="topbar">

        <h3>

            Welcome,

            <?php
            echo htmlspecialchars(
                $student['full_name']
            );
            ?>

            👋

        </h3>


        <small class="text-muted">

            Student Dashboard

        </small>

    </div>



    <!-- ==================================================
         FINAL RESULT NOTIFICATIONS
    ================================================== -->

    <?php if (!empty($finalNotifications)): ?>

        <div class="card information-card mb-4">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">
                    <i class="fa fa-bell me-2"></i>
                    Result Notifications
                </h5>

            </div>

            <div class="card-body">

                <?php foreach ($finalNotifications as $notification): ?>

                    <a href="result.php?final_result_id=<?= (int)$notification['final_result_id'] ?>"
                       class="text-decoration-none">

                        <div class="border rounded-3 p-3 mb-3 bg-light">

                            <div class="d-flex justify-content-between align-items-start gap-3">

                                <div>

                                    <h5 class="text-success fw-bold mb-2">
                                        <i class="fa-solid fa-circle-check me-1"></i>
                                        Your <?= htmlspecialchars($notification['semester_name'] ?? 'Semester') ?>
                                        Result Published
                                    </h5>

                                    <p class="text-dark mb-1">
                                        Your overall result and marksheet are now available.
                                    </p>

                                    <small class="text-muted">
                                        <?= htmlspecialchars($notification['department_name'] ?? '') ?>

                                        <?php if (!empty($notification['published_at'])): ?>
                                            • <?= htmlspecialchars($notification['published_at']) ?>
                                        <?php endif; ?>
                                    </small>

                                </div>

                                <div class="text-end">

                                    <span class="badge bg-primary fs-6">
                                        CGPA:
                                        <?= number_format((float)$notification['cgpa'], 2) ?>
                                    </span>

                                    <div class="mt-2">
                                        <span class="btn btn-success btn-sm">
                                            View Result & Marksheet
                                            <i class="fa fa-arrow-right ms-1"></i>
                                        </span>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </a>

                <?php endforeach; ?>

            </div>

        </div>

    <?php endif; ?>


    <!-- ==================================================
         PROFILE + STATISTICS
    ================================================== -->

    <div class="row">


        <!-- ==================================================
             STUDENT PROFILE
        ================================================== -->

        <div class="col-lg-4 mb-4">

            <div class="card profile-card">

                <div class="card-body text-center">


                    <?php

                    if (!empty($student['photo'])) {

                    ?>

                        <img
                            src="../uploads/students/<?php
                            echo htmlspecialchars(
                                $student['photo']
                            );
                            ?>"
                            class="profile border border-primary border-3"
                            alt="Student Photo"
                        >

                    <?php

                    } else {

                    ?>

                        <img
                            src="../assets/default.png"
                            class="profile border border-primary border-3"
                            alt="Default Profile"
                        >

                    <?php

                    }

                    ?>


                    <h4 class="mt-3">

                        <?php
                        echo htmlspecialchars(
                            $student['full_name']
                        );
                        ?>

                    </h4>


                    <p class="text-muted">

                        Student

                    </p>


                    <hr>


                    <p>

                        <strong>
                            Student ID
                        </strong>

                        <br>

                        <?php
                        echo htmlspecialchars(
                            $student['student_id']
                        );
                        ?>

                    </p>


                    <p>

                        <strong>
                            Department
                        </strong>

                        <br>

                        <?php
                        echo htmlspecialchars(
                            $student['department_name']
                        );
                        ?>

                    </p>


                    <p>

                        <strong>
                            Semester
                        </strong>

                        <br>

                        <?php
                        echo htmlspecialchars(
                            $student['semester_name']
                        );
                        ?>

                    </p>


                    <a
                        href="profile.php"
                        class="btn btn-primary w-100"
                    >

                        <i class="fa fa-user-edit"></i>

                        View Profile

                    </a>


                </div>

            </div>

        </div>



        <!-- ==================================================
             STATISTICS
        ================================================== -->

        <div class="col-lg-8">

            <div class="row">


                <!-- Student ID -->

                <div class="col-md-4 mb-4">

                    <div class="card stat-card bg-primary text-white">

                        <div class="card-body text-center">


                            <div class="stat-icon">

                                <i class="fa fa-id-card"></i>

                            </div>


                            <h6>

                                Student ID

                            </h6>


                            <h5>

                                <?php
                                echo htmlspecialchars(
                                    $student['student_id']
                                );
                                ?>

                            </h5>


                        </div>

                    </div>

                </div>



                <!-- Enrolled Courses -->

                <div class="col-md-4 mb-4">

                    <div class="card stat-card bg-success text-white">

                        <div class="card-body text-center">


                            <div class="stat-icon">

                                <i class="fa fa-book-open"></i>

                            </div>


                            <h6>

                                Enrolled Courses

                            </h6>


                            <h2>

                                <?php
                                echo $enrolled_count;
                                ?>

                            </h2>


                        </div>

                    </div>

                </div>



                <!-- Results -->

                <div class="col-md-4 mb-4">

                    <div class="card stat-card bg-warning">

                        <div class="card-body text-center">


                            <div class="stat-icon">

                                <i class="fa fa-file-alt"></i>

                            </div>


                            <h6>

                                Results

                            </h6>


                            <h2>

                                <?php
                                echo $result_count;
                                ?>

                            </h2>


                        </div>

                    </div>

                </div>


            </div>


        </div>

    </div>



    <!-- ==================================================
         STUDENT INFORMATION
    ================================================== -->

    <div class="card information-card mt-2">


        <div class="card-header bg-dark text-white">

            <h5 class="mb-0">

                <i class="fa fa-circle-info"></i>

                Student Information

            </h5>

        </div>



        <div class="card-body">


            <div class="row">


                <!-- LEFT INFORMATION -->

                <div class="col-md-6">

                    <table class="table table-bordered">


                        <tr>

                            <th width="40%">
                                Student ID
                            </th>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student['student_id']
                                );
                                ?>

                            </td>

                        </tr>


                        <tr>

                            <th>
                                Name
                            </th>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student['full_name']
                                );
                                ?>

                            </td>

                        </tr>


                        <tr>

                            <th>
                                Department
                            </th>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student['department_name']
                                );
                                ?>

                            </td>

                        </tr>


                        <tr>

                            <th>
                                Semester
                            </th>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student['semester_name']
                                );
                                ?>

                            </td>

                        </tr>


                    </table>

                </div>



                <!-- RIGHT INFORMATION -->

                <div class="col-md-6">

                    <table class="table table-bordered">


                        <tr>

                            <th width="40%">
                                Email
                            </th>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student['email']
                                );
                                ?>

                            </td>

                        </tr>


                        <tr>

                            <th>
                                Phone
                            </th>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student['phone']
                                );
                                ?>

                            </td>

                        </tr>


                        <tr>

                            <th>
                                Status
                            </th>

                            <td>

                                <span class="badge bg-success">

                                    <?php
                                    echo htmlspecialchars(
                                        $student['status']
                                    );
                                    ?>

                                </span>

                            </td>

                        </tr>


                        <tr>

                            <th>
                                Profile
                            </th>

                            <td>

                                <a
                                    href="profile.php"
                                    class="btn btn-primary btn-sm"
                                >

                                    <i class="fa fa-user-edit"></i>

                                    Edit Profile

                                </a>

                            </td>

                        </tr>


                    </table>

                </div>


            </div>


        </div>

    </div>


</div>



<!-- ==================================================
     BOOTSTRAP JS
================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>