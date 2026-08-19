<?php

session_start();

include "../config/database.php";
include "../config/session.php";

checkAdmin();


// ======================================================
// MESSAGE
// ======================================================

$message = "";
$message_type = "";


// ======================================================
// APPROVE STUDENT
// ======================================================

if (isset($_GET['approve'])) {

    $student_id = intval($_GET['approve']);

    if ($student_id > 0) {

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

            if (mysqli_stmt_execute($stmt)) {

                if (mysqli_stmt_affected_rows($stmt) > 0) {

                    $message =
                        "Student approved successfully.";

                    $message_type = "success";

                } else {

                    $message =
                        "Student not found or already approved.";

                    $message_type = "warning";
                }

            } else {

                $message =
                    "Failed to approve student.";

                $message_type = "danger";
            }

            mysqli_stmt_close($stmt);

        } else {

            $message =
                "Database error.";

            $message_type = "danger";
        }
    }
}


// ======================================================
// REJECT STUDENT
// ======================================================

if (isset($_GET['reject'])) {

    $student_id = intval($_GET['reject']);

    if ($student_id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE students
             SET status = 'Rejected'
             WHERE student_id = ?
             AND status = 'Pending'"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $student_id
            );

            if (mysqli_stmt_execute($stmt)) {

                if (mysqli_stmt_affected_rows($stmt) > 0) {

                    $message =
                        "Student rejected successfully.";

                    $message_type = "warning";

                } else {

                    $message =
                        "Student not found or already processed.";

                    $message_type = "warning";
                }

            } else {

                $message =
                    "Failed to reject student.";

                $message_type = "danger";
            }

            mysqli_stmt_close($stmt);

        } else {

            $message =
                "Database error.";

            $message_type = "danger";
        }
    }
}


// ======================================================
// GET PENDING STUDENTS
// ======================================================

$sql = "
    SELECT
        s.student_id,
        s.student_roll,
        s.full_name,
        s.email,
        s.phone,
        s.photo,
        s.status,

        d.department_name,

        sem.semester_name

    FROM students s

    LEFT JOIN departments d
        ON s.department_id = d.department_id

    LEFT JOIN semesters sem
        ON s.semester_id = sem.semester_id

    WHERE s.status = 'Pending'

    ORDER BY s.student_id DESC
";

$students = mysqli_query($conn, $sql);


// ======================================================
// PENDING COUNT
// ======================================================

$count_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM students
     WHERE status = 'Pending'"
);

$count_row = mysqli_fetch_assoc($count_query);

$pending_count = intval($count_row['total']);

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
        Pending Student Approval
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

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            background: #f4f7fb;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

        }


        /* =========================================
           SIDEBAR
        ========================================= */

        .sidebar {

            position: fixed;

            top: 0;

            left: 0;

            width: 250px;

            height: 100vh;

            background:
                linear-gradient(
                    180deg,
                    #0d6efd,
                    #084298
                );

            color: white;

            overflow-y: auto;

        }


        .sidebar-title {

            padding: 25px 15px;

            text-align: center;

            font-size: 23px;

            font-weight: bold;

            border-bottom:
                1px solid
                rgba(255,255,255,0.2);

        }


        .sidebar a {

            display: flex;

            align-items: center;

            justify-content: space-between;

            color: white;

            text-decoration: none;

            padding: 15px 20px;

            font-size: 15px;

            transition: 0.2s;

        }


        .sidebar a:hover {

            background:
                rgba(255,255,255,0.15);

        }


        .sidebar a.active {

            background:
                rgba(255,255,255,0.22);

        }


        .menu-left {

            display: flex;

            align-items: center;

            gap: 12px;

        }


        /* =========================================
           MAIN
        ========================================= */

        .main {

            margin-left: 250px;

            min-height: 100vh;

        }


        /* =========================================
           TOPBAR
        ========================================= */

        .topbar {

            height: 75px;

            background: white;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 30px;

            box-shadow:
                0 2px 10px
                rgba(0,0,0,0.08);

        }


        .topbar h5 {

            margin: 0;

            font-weight: bold;

        }


        /* =========================================
           CONTENT
        ========================================= */

        .content {

            padding: 30px;

        }


        /* =========================================
           PAGE HEADER
        ========================================= */

        .page-header {

            background: white;

            border-radius: 15px;

            padding: 25px;

            margin-bottom: 25px;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.06);

        }


        .page-header h2 {

            margin: 0;

            font-size: 28px;

            font-weight: 700;

            color: #172033;

        }


        .page-header p {

            margin:
                7px 0 0;

            color: #6b7280;

        }


        /* =========================================
           CARD
        ========================================= */

        .student-card {

            background: white;

            border-radius: 15px;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.06);

            overflow: hidden;

        }


        .card-title {

            padding: 20px 25px;

            border-bottom:
                1px solid #e5e7eb;

            display: flex;

            align-items: center;

            justify-content: space-between;

        }


        .card-title h5 {

            margin: 0;

            font-weight: 700;

        }


        /* =========================================
           TABLE
        ========================================= */

        .table {

            margin: 0;

            vertical-align: middle;

        }


        .table thead th {

            background: #0d6efd;

            color: white;

            border: none;

            padding: 15px 12px;

            white-space: nowrap;

        }


        .table tbody td {

            padding: 14px 12px;

            white-space: nowrap;

        }


        .table tbody tr:hover {

            background: #f8fafc;

        }


        /* =========================================
           PHOTO
        ========================================= */

        .student-photo {

            width: 48px;

            height: 48px;

            border-radius: 50%;

            object-fit: cover;

            border:
                2px solid #e5e7eb;

        }


        .default-photo {

            width: 48px;

            height: 48px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #e5e7eb;

            color: #6b7280;

        }


        /* =========================================
           BUTTONS
        ========================================= */

        .btn-approve {

            background: #198754;

            color: white;

            border: none;

        }


        .btn-approve:hover {

            background: #146c43;

            color: white;

        }


        .btn-reject {

            background: #dc3545;

            color: white;

            border: none;

        }


        .btn-reject:hover {

            background: #b02a37;

            color: white;

        }


        /* =========================================
           EMPTY
        ========================================= */

        .empty-box {

            padding: 70px 20px;

            text-align: center;

        }


        .empty-box i {

            font-size: 60px;

            color: #198754;

            margin-bottom: 20px;

        }


        .empty-box h4 {

            font-weight: 700;

        }


        .empty-box p {

            color: #6b7280;

        }


        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 900px) {

            .sidebar {

                width: 70px;

            }


            .sidebar-title {

                font-size: 0;

            }


            .sidebar-title i {

                font-size: 22px;

            }


            .sidebar a {

                justify-content: center;

                padding: 18px 5px;

            }


            .menu-text {

                display: none;

            }


            .main {

                margin-left: 70px;

            }


            .content {

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


    <div class="sidebar-title">

        <i class="fa-solid fa-user-shield"></i>

        <span class="menu-text">

            Admin Panel

        </span>

    </div>


    <a href="dashboard.php">

        <div class="menu-left">

            <i class="fa-solid fa-gauge"></i>

            <span class="menu-text">

                Dashboard

            </span>

        </div>

    </a>


    <a
        href="pending_student.php"
        class="active"
    >

        <div class="menu-left">

            <i class="fa-solid fa-user-clock"></i>

            <span class="menu-text">

                Student Approval

            </span>

        </div>


        <?php if ($pending_count > 0) { ?>

            <span class="badge bg-danger">

                <?php echo $pending_count; ?>

            </span>

        <?php } ?>

    </a>


    <a href="students.php">

        <div class="menu-left">

            <i class="fa-solid fa-users"></i>

            <span class="menu-text">

                Students

            </span>

        </div>

    </a>


    <a href="courses.php">

        <div class="menu-left">

            <i class="fa-solid fa-book"></i>

            <span class="menu-text">

                Courses

            </span>

        </div>

    </a>


    <a href="publish-result.php">

        <div class="menu-left">

            <i class="fa-solid fa-bullhorn"></i>

            <span class="menu-text">

                Publish Result

            </span>

        </div>

    </a>


    <a href="../logout.php">

        <div class="menu-left">

            <i class="fa-solid fa-right-from-bracket"></i>

            <span class="menu-text">

                Logout

            </span>

        </div>

    </a>

</div>



<!-- ==================================================
     MAIN
================================================== -->

<div class="main">


    <!-- TOPBAR -->

    <div class="topbar">

        <div>

            <h5>

                Student Approval

            </h5>

        </div>


        <div>

            <i class="fa-solid fa-user-shield"></i>

            Admin

        </div>

    </div>



    <!-- CONTENT -->

    <div class="content">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <div
                class="d-flex
                       justify-content-between
                       align-items-center
                       flex-wrap
                       gap-3"
            >

                <div>

                    <h2>

                        <i
                            class="fa-solid
                                   fa-user-clock
                                   text-primary"
                        ></i>

                        Pending Student Approval

                    </h2>

                    <p>

                        Review student registration
                        requests and approve or reject them.

                    </p>

                </div>


                <div>

                    <span
                        class="badge
                               bg-warning
                               text-dark
                               fs-6
                               p-2"
                    >

                        <i class="fa-solid fa-clock"></i>

                        <?php echo $pending_count; ?>

                        Pending

                    </span>

                </div>

            </div>

        </div>



        <!-- MESSAGE -->

        <?php if ($message != "") { ?>

            <div
                class="alert
                       alert-<?php
                       echo $message_type;
                       ?>
                       alert-dismissible
                       fade show"
            >

                <?php

                if (
                    $message_type ==
                    "success"
                ) {

                    echo
                    '<i class="fa-solid fa-circle-check"></i>';

                } elseif (
                    $message_type ==
                    "warning"
                ) {

                    echo
                    '<i class="fa-solid fa-triangle-exclamation"></i>';

                } else {

                    echo
                    '<i class="fa-solid fa-circle-exclamation"></i>';
                }

                ?>

                &nbsp;

                <?php

                echo htmlspecialchars(
                    $message
                );

                ?>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php } ?>



        <!-- STUDENT CARD -->

        <div class="student-card">


            <div class="card-title">

                <h5>

                    <i class="fa-solid fa-users"></i>

                    Student Registration Requests

                </h5>


                <span class="badge bg-primary">

                    <?php echo $pending_count; ?>

                    Students

                </span>

            </div>



            <?php

            if (
                $students &&
                mysqli_num_rows($students) > 0
            ) {

            ?>


                <div class="table-responsive">

                    <table
                        class="table
                               table-hover
                               table-bordered"
                    >

                        <thead>

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    Photo
                                </th>

                                <th>
                                    Student Roll
                                </th>

                                <th>
                                    Full Name
                                </th>

                                <th>
                                    Email
                                </th>

                                <th>
                                    Phone
                                </th>

                                <th>
                                    Department
                                </th>

                                <th>
                                    Semester
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php

                        $serial = 1;

                        while (
                            $student =
                            mysqli_fetch_assoc(
                                $students
                            )
                        ) {


                            $photo =
                                $student['photo']
                                ?? '';


                            $photo_path =
                                "../assets/uploads/students/"
                                . $photo;

                        ?>


                            <tr>


                                <!-- NUMBER -->

                                <td>

                                    <?php
                                    echo $serial++;
                                    ?>

                                </td>


                                <!-- PHOTO -->

                                <td>

                                    <?php

                                    if (
                                        !empty($photo)
                                        &&
                                        file_exists(
                                            $photo_path
                                        )
                                    ) {

                                    ?>

                                        <img
                                            src="<?php
                                            echo htmlspecialchars(
                                                $photo_path
                                            );
                                            ?>"
                                            class="student-photo"
                                            alt="Student"
                                        >

                                    <?php

                                    } else {

                                    ?>

                                        <div
                                            class="default-photo"
                                        >

                                            <i
                                                class="fa-solid
                                                       fa-user">
                                            </i>

                                        </div>

                                    <?php

                                    }

                                    ?>

                                </td>


                                <!-- ROLL -->

                                <td>

                                    <strong>

                                        <?php

                                        echo htmlspecialchars(
                                            $student[
                                                'student_roll'
                                            ]
                                        );

                                        ?>

                                    </strong>

                                </td>


                                <!-- NAME -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $student[
                                            'full_name'
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- EMAIL -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $student[
                                            'email'
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- PHONE -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $student[
                                            'phone'
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- DEPARTMENT -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $student[
                                            'department_name'
                                        ]
                                        ??
                                        'N/A'
                                    );

                                    ?>

                                </td>


                                <!-- SEMESTER -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $student[
                                            'semester_name'
                                        ]
                                        ??
                                        'N/A'
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

                                        <i
                                            class="fa-solid
                                                   fa-clock">
                                        </i>

                                        Pending

                                    </span>

                                </td>


                                <!-- ACTION -->

                                <td>

                                    <div
                                        class="d-flex
                                               gap-2"
                                    >


                                        <!-- APPROVE -->

                                        <a
                                            href="pending_student.php?approve=<?php
                                            echo (int)
                                                $student[
                                                    'student_id'
                                                ];
                                            ?>"
                                            class="btn
                                                   btn-approve
                                                   btn-sm"
                                            onclick="
                                                return confirm(
                                                    'Are you sure you want to approve this student?'
                                                );
                                            "
                                        >

                                            <i
                                                class="fa-solid
                                                       fa-check">
                                            </i>

                                            Approve

                                        </a>


                                        <!-- REJECT -->

                                        <a
                                            href="pending_student.php?reject=<?php
                                            echo (int)
                                                $student[
                                                    'student_id'
                                                ];
                                            ?>"
                                            class="btn
                                                   btn-reject
                                                   btn-sm"
                                            onclick="
                                                return confirm(
                                                    'Are you sure you want to reject this student?'
                                                );
                                            "
                                        >

                                            <i
                                                class="fa-solid
                                                       fa-xmark">
                                            </i>

                                            Reject

                                        </a>


                                    </div>

                                </td>


                            </tr>


                        <?php

                        }

                        ?>


                        </tbody>

                    </table>

                </div>


            <?php

            } else {

            ?>


                <!-- NO PENDING STUDENT -->

                <div class="empty-box">

                    <i
                        class="fa-solid
                               fa-circle-check">
                    </i>


                    <h4>

                        No Pending Students

                    </h4>


                    <p>

                        There are currently no
                        student registration requests.

                    </p>


                    <a
                        href="dashboard.php"
                        class="btn btn-primary"
                    >

                        <i
                            class="fa-solid
                                   fa-arrow-left">
                        </i>

                        Back to Dashboard

                    </a>

                </div>


            <?php

            }

            ?>


        </div>


    </div>

</div>



<!-- Bootstrap -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>