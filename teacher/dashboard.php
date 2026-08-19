<?php

session_start();

include '../config/database.php';
include '../config/functions.php';

// ==================================================
// TEACHER LOGIN CHECK
// ==================================================

if (
    !isset($_SESSION['teacher_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'teacher'
) {
    header("Location: ../login.php?role=teacher");
    exit();
}

$teacher_id = (int) $_SESSION['teacher_id'];

$teacher_name = $_SESSION['teacher_name'] ?? 'Teacher';


// ==================================================
// GET TEACHER INFORMATION
// ==================================================

$stmt_teacher = mysqli_prepare(
    $conn,
    "SELECT full_name, email
     FROM teachers
     WHERE teacher_id = ?
     LIMIT 1"
);

if ($stmt_teacher) {

    mysqli_stmt_bind_param(
        $stmt_teacher,
        "i",
        $teacher_id
    );

    mysqli_stmt_execute($stmt_teacher);

    $teacher_result =
        mysqli_stmt_get_result($stmt_teacher);

    if ($teacher_row = mysqli_fetch_assoc($teacher_result)) {

        $teacher_name =
            $teacher_row['full_name'];

        $teacher_email =
            $teacher_row['email'] ?? '';
    }

    mysqli_stmt_close($stmt_teacher);
}


// ==================================================
// COUNT ASSIGNED COURSES
// ==================================================

$total_courses = 0;

$stmt_courses = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM teacher_courses
     WHERE teacher_id = ?"
);

if ($stmt_courses) {

    mysqli_stmt_bind_param(
        $stmt_courses,
        "i",
        $teacher_id
    );

    mysqli_stmt_execute($stmt_courses);

    $course_result =
        mysqli_stmt_get_result($stmt_courses);

    if ($course_row = mysqli_fetch_assoc($course_result)) {

        $total_courses =
            (int) $course_row['total'];
    }

    mysqli_stmt_close($stmt_courses);
}


// ==================================================
// GET ASSIGNED COURSES
// ==================================================

$assigned_courses = [];

$stmt_assigned = mysqli_prepare(
    $conn,
    "SELECT
        c.course_id,
        c.course_code,
        c.course_name

     FROM teacher_courses tc

     INNER JOIN courses c
        ON tc.course_id = c.course_id

     WHERE tc.teacher_id = ?

     ORDER BY c.course_name ASC"
);

if ($stmt_assigned) {

    mysqli_stmt_bind_param(
        $stmt_assigned,
        "i",
        $teacher_id
    );

    mysqli_stmt_execute($stmt_assigned);

    $assigned_result =
        mysqli_stmt_get_result($stmt_assigned);

    while (
        $course =
        mysqli_fetch_assoc($assigned_result)
    ) {

        $assigned_courses[] = $course;
    }

    mysqli_stmt_close($stmt_assigned);
}


// ==================================================
// COUNT STUDENTS
// ==================================================

$total_students = 0;

$student_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM students"
);

if ($student_query) {

    $student_row =
        mysqli_fetch_assoc($student_query);

    $total_students =
        (int) $student_row['total'];
}


// ==================================================
// COUNT MARK ENTRIES BY TEACHER
// ==================================================

$total_marks = 0;

$stmt_marks = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM marks m
     INNER JOIN teacher_courses tc
        ON m.course_id = tc.course_id
     WHERE tc.teacher_id = ?"
);

if ($stmt_marks) {

    mysqli_stmt_bind_param(
        $stmt_marks,
        "i",
        $teacher_id
    );

    mysqli_stmt_execute($stmt_marks);

    $marks_result =
        mysqli_stmt_get_result($stmt_marks);

    if ($marks_row = mysqli_fetch_assoc($marks_result)) {

        $total_marks =
            (int) $marks_row['total'];
    }

    mysqli_stmt_close($stmt_marks);
}


// ==================================================
// COUNT PENDING SUBMISSIONS
// ==================================================

$pending_results = 0;

$stmt_pending = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM result_submissions
     WHERE teacher_id = ?
     AND status = 'Pending'"
);

if ($stmt_pending) {

    mysqli_stmt_bind_param(
        $stmt_pending,
        "i",
        $teacher_id
    );

    mysqli_stmt_execute($stmt_pending);

    $pending_result =
        mysqli_stmt_get_result($stmt_pending);

    if ($pending_row = mysqli_fetch_assoc($pending_result)) {

        $pending_results =
            (int) $pending_row['total'];
    }

    mysqli_stmt_close($stmt_pending);
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
        Teacher Dashboard
    </title>


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f7fb;

            color: #1f2937;

        }


        /* ==================================================
           SIDEBAR
        ================================================== */

        .sidebar {

            position: fixed;

            left: 0;
            top: 0;

            width: 340px;
            height: 100vh;

            background:
                linear-gradient(
                    180deg,
                    #1677f5 0%,
                    #0755b8 100%
                );

            color: white;

            overflow-y: auto;

            z-index: 1000;

        }


        .profile {

            text-align: center;

            padding: 25px 15px 30px;

            border-bottom:
                1px solid
                rgba(255,255,255,0.2);

        }


        .profile h2 {

            font-size: 25px;

            text-transform: capitalize;

            margin-bottom: 12px;

        }


        .profile p {

            font-size: 18px;

            opacity: 0.9;

        }


        .menu {

            padding-top: 20px;

        }


        .menu a {

            display: flex;

            align-items: center;

            gap: 25px;

            padding: 18px 45px;

            color: white;

            text-decoration: none;

            font-size: 21px;

            transition: 0.2s;

        }


        .menu a i {

            width: 28px;

            text-align: center;

            font-size: 23px;

        }


        .menu a:hover {

            background:
                rgba(255,255,255,0.13);

        }


        .menu a.active {

            background:
                rgba(255,255,255,0.20);

        }


        /* ==================================================
           LOGOUT
        ================================================== */

        .logout-area {

            padding: 18px;

            margin-top: 10px;

            border-top:
                1px solid
                rgba(255,255,255,0.2);

        }


        .logout-btn {

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 15px;

            width: 100%;

            padding: 15px;

            background: #ef3340;

            color: white;

            text-decoration: none;

            border-radius: 18px;

            font-size: 21px;

            font-weight: bold;

        }


        .logout-btn:hover {

            background: #d92332;

        }


        /* ==================================================
           MAIN
        ================================================== */

        .main {

            margin-left: 340px;

            min-height: 100vh;

            padding: 35px;

        }


        /* ==================================================
           TOP
        ================================================== */

        .welcome {

            background: white;

            border-radius: 18px;

            padding: 30px;

            margin-bottom: 25px;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.06);

        }


        .welcome h1 {

            font-size: 32px;

            margin-bottom: 8px;

        }


        .welcome p {

            color: #6b7280;

            font-size: 18px;

        }


        /* ==================================================
           STATS
        ================================================== */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 25px;

        }


        .stat-card {

            background: white;

            border-radius: 15px;

            padding: 25px;

            box-shadow:
                0 5px 18px
                rgba(0,0,0,0.06);

        }


        .stat-icon {

            width: 50px;
            height: 50px;

            border-radius: 12px;

            background: #eaf2ff;

            color: #1769e0;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 23px;

            margin-bottom: 15px;

        }


        .stat-card h3 {

            font-size: 14px;

            color: #6b7280;

            margin-bottom: 8px;

        }


        .stat-card .number {

            font-size: 30px;

            font-weight: bold;

            color: #111827;

        }


        /* ==================================================
           QUICK ACTIONS
        ================================================== */

        .section {

            background: white;

            border-radius: 18px;

            padding: 28px;

            margin-bottom: 25px;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.06);

        }


        .section h2 {

            font-size: 23px;

            margin-bottom: 20px;

        }


        .actions {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

        }


        .action-btn {

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 10px;

            padding: 16px;

            border-radius: 10px;

            color: white;

            text-decoration: none;

            font-size: 16px;

            font-weight: bold;

            transition: 0.2s;

        }


        .action-btn:hover {

            transform: translateY(-2px);

            opacity: 0.92;

        }


        .blue {

            background: #2563eb;

        }


        .green {

            background: #16a34a;

        }


        .orange {

            background: #f59e0b;

        }


        .purple {

            background: #7c3aed;

        }


        /* ==================================================
           COURSE TABLE
        ================================================== */

        .table-wrapper {

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

        }


        th {

            background: #f8fafc;

            color: #374151;

            padding: 15px;

            text-align: left;

            border-bottom:
                1px solid #e5e7eb;

        }


        td {

            padding: 15px;

            border-bottom:
                1px solid #e5e7eb;

        }


        tr:hover td {

            background: #f9fafb;

        }


        .course-code {

            font-weight: bold;

            color: #2563eb;

        }


        .view-btn {

            display: inline-block;

            padding: 8px 13px;

            background: #2563eb;

            color: white;

            text-decoration: none;

            border-radius: 6px;

            font-size: 13px;

        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 1200px) {

            .stats {

                grid-template-columns:
                    repeat(2, 1fr);

            }


            .actions {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media (max-width: 800px) {

            .sidebar {

                width: 250px;

            }


            .main {

                margin-left: 250px;

                padding: 20px;

            }


            .menu a {

                padding: 15px 25px;

                font-size: 17px;

            }


            .stats {

                grid-template-columns: 1fr;

            }


            .actions {

                grid-template-columns: 1fr;

            }

        }


    </style>

</head>


<body>


<!-- ==================================================
     SIDEBAR
================================================== -->

<div class="sidebar">


    <!-- PROFILE -->

    <div class="profile">

        <h2>

            <?php
            echo htmlspecialchars(
                $teacher_name
            );
            ?>

        </h2>

        <p>
            Teacher
        </p>

    </div>


    <!-- MENU -->

    <div class="menu">


        <!-- DASHBOARD -->

        <a
            href="dashboard.php"
            class="active"
        >

            <i class="fa-solid fa-gauge"></i>

            <span>
                Dashboard
            </span>

        </a>


        <!-- PROFILE -->

        <a href="profile.php">

            <i class="fa-solid fa-user"></i>

            <span>
                My Profile
            </span>

        </a>


        <!-- COURSES -->

        <a href="courses.php">

            <i class="fa-solid fa-book"></i>

            <span>
                My Courses
            </span>

        </a>


        <!-- STUDENTS -->

        <a href="students.php">

            <i class="fa-solid fa-users"></i>

            <span>
                Students
            </span>

        </a>


        <!-- ENTER MARKS -->

        <a href="marks.php">

            <i class="fa-solid fa-pen-to-square"></i>

            <span>
                Enter Marks
            </span>

        </a>




        <!-- ==================================================
             SEND RESULT TO ADMIN
             IMPORTANT: DO NOT CHANGE THIS LINK
        ================================================== -->

        <a href="Send_Result_to_Admin.php">

            <i class="fa-solid fa-paper-plane"></i>

            <span>
                Send Result to Admin
            </span>

        </a>


    </div>


    <!-- LOGOUT -->

    <div class="logout-area">

        <a
            href="../logout.php"
            class="logout-btn"
            onclick="return confirm('Are you sure you want to logout?');"
        >

            <i class="fa-solid fa-right-from-bracket"></i>

            <span>
                Logout
            </span>

        </a>

    </div>


</div>


<!-- ==================================================
     MAIN CONTENT
================================================== -->

<div class="main">


    <!-- WELCOME -->

    <div class="welcome">

        <h1>

            Welcome,
            <?php
            echo htmlspecialchars(
                $teacher_name
            );
            ?>

        </h1>

        <p>
            Manage your courses, students,
            marks and results from your dashboard.
        </p>

    </div>


    <!-- ==================================================
         STATISTICS
    ================================================== -->

    <div class="stats">


        <!-- COURSES -->

        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-book"></i>

            </div>

            <h3>
                Assigned Courses
            </h3>

            <div class="number">

                <?php
                echo $total_courses;
                ?>

            </div>

        </div>


        <!-- STUDENTS -->

        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-users"></i>

            </div>

            <h3>
                Students
            </h3>

            <div class="number">

                <?php
                echo $total_students;
                ?>

            </div>

        </div>


        <!-- MARKS -->

        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-pen-to-square"></i>

            </div>

            <h3>
                Mark Entries
            </h3>

            <div class="number">

                <?php
                echo $total_marks;
                ?>

            </div>

        </div>


        <!-- PENDING -->

        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-paper-plane"></i>

            </div>

            <h3>
                Pending Results
            </h3>

            <div class="number">

                <?php
                echo $pending_results;
                ?>

            </div>

        </div>


    </div>


    <!-- ==================================================
         QUICK ACTIONS
    ================================================== -->

    <div class="section">

        <h2>
            Quick Actions
        </h2>


        <div class="actions">


            <!-- MY COURSES -->

            <a
                href="courses.php"
                class="action-btn blue"
            >

                <i class="fa-solid fa-book"></i>

                My Courses

            </a>


            <!-- ENTER MARKS -->

            <a
                href="marks.php"
                class="action-btn green"
            >

                <i class="fa-solid fa-pen-to-square"></i>

                Enter Marks

            </a>


            <!-- RESULTS -->



            <!-- ==================================================
                 SEND RESULT TO ADMIN
                 THIS IS THE IMPORTANT BUTTON
            ================================================== -->

            <a
                href="Send_Result_to_Admin.php"
                class="action-btn orange"
            >

                <i class="fa-solid fa-paper-plane"></i>

                Send Result to Admin

            </a>


        </div>

    </div>


    <!-- ==================================================
         ASSIGNED COURSES
    ================================================== -->

    <div class="section">

        <h2>
            My Assigned Courses
        </h2>


        <?php if (count($assigned_courses) > 0): ?>


            <div class="table-wrapper">

                <table>

                    <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            Course Code
                        </th>

                        <th>
                            Course Name
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                    </thead>


                    <tbody>

                    <?php

                    $serial = 1;

                    foreach (
                        $assigned_courses
                        as $course
                    ):

                    ?>

                        <tr>

                            <td>

                                <?php
                                echo $serial++;
                                ?>

                            </td>


                            <td class="course-code">

                                <?php

                                echo htmlspecialchars(
                                    $course['course_code']
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $course['course_name']
                                );

                                ?>

                            </td>


                            <td>

                                <a
                                    href="results.php?course_id=<?php echo (int)$course['course_id']; ?>"
                                    class="view-btn"
                                >

                                    View Results

                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <p
                style="
                    color:#6b7280;
                    padding:20px 0;
                "
            >

                No courses have been assigned to you yet.

            </p>


        <?php endif; ?>


    </div>


</div>


</body>

</html>