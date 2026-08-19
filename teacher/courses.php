<?php

session_start();

include '../config/database.php';
include '../config/functions.php';


// =====================================
// TEACHER LOGIN CHECK
// =====================================

if (
    !isset($_SESSION['teacher_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'teacher'
) {
    header("Location: ../login.php?role=teacher");
    exit();
}


$teacher_id = $_SESSION['teacher_id'];

$teacher_name = $_SESSION['teacher_name'] ?? 'Teacher';


// =====================================
// GET TEACHER NAME
// =====================================

$stmt_teacher = mysqli_prepare(
    $conn,
    "SELECT full_name
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

    if (
        $teacher_result &&
        mysqli_num_rows($teacher_result) == 1
    ) {

        $teacher_row =
            mysqli_fetch_assoc($teacher_result);

        $teacher_name =
            $teacher_row['full_name'];
    }

    mysqli_stmt_close($stmt_teacher);
}


// =====================================
// GET ASSIGNED COURSES
// teacher_courses -> courses
// =====================================

$courses = [];

$sql = "
    SELECT
        tc.id AS assignment_id,
        tc.created_at,
        c.course_id,
        c.course_code,
        c.course_name,
        c.credit,
        c.department_id,
        c.semester_id
    FROM teacher_courses tc
    INNER JOIN courses c
        ON tc.course_id = c.course_id
    WHERE tc.teacher_id = ?
    ORDER BY c.course_id DESC
";


$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $teacher_id
    );

    mysqli_stmt_execute($stmt);

    $result =
        mysqli_stmt_get_result($stmt);

    if ($result) {

        while ($row = mysqli_fetch_assoc($result)) {

            $courses[] = $row;
        }
    }

    mysqli_stmt_close($stmt);
}


$total_courses = count($courses);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>
    My Courses | Teacher Panel
</title>


<!-- Bootstrap -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">


<!-- Font Awesome -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


<style>

/* =====================================
   BODY
===================================== */

body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f4f7fb;

}


/* =====================================
   SIDEBAR
===================================== */

.sidebar {

    width: 260px;

    height: 100vh;

    position: fixed;

    left: 0;

    top: 0;

    background:
        linear-gradient(
            180deg,
            #0d6efd 0%,
            #084298 100%
        );

    color: white;

    display: flex;

    flex-direction: column;

    box-shadow:
        3px 0 15px
        rgba(0,0,0,.15);

    overflow-y: auto;

    overflow-x: hidden;

}


/* =====================================
   LOGO
===================================== */

.logo {

    text-align: center;

    font-size: 23px;

    font-weight: bold;

    padding: 25px 15px 28px;

    border-bottom:
        1px solid
        rgba(255,255,255,.2);

    flex-shrink: 0;

}


.logo i {

    font-size: 38px;

    margin-bottom: 12px;

}


/* =====================================
   TEACHER INFO
===================================== */

.teacher-info {

    text-align: center;

    padding: 28px 10px 30px;

    border-bottom:
        1px solid
        rgba(255,255,255,.2);

    flex-shrink: 0;

}


.teacher-avatar {

    width: 98px;

    height: 98px;

    border-radius: 50%;

    background: white;

    color: #0d6efd;

    display: flex;

    justify-content: center;

    align-items: center;

    margin: 0 auto 18px;

    font-size: 42px;

    box-shadow:
        0 4px 12px
        rgba(0,0,0,.12);

}


.teacher-info h6 {

    margin: 0;

    font-size: 20px;

    font-weight: 500;

    color: white;

    word-break: break-word;

}


.teacher-info small {

    display: block;

    margin-top: 10px;

    font-size: 17px;

    color:
        rgba(255,255,255,.8);

}


/* =====================================
   MENU
===================================== */

.menu {

    padding: 15px 0;

}


.main-menu {

    flex: 1;

    padding-top: 12px;

}


.menu a {

    display: flex;

    align-items: center;

    color: white;

    text-decoration: none;

    padding: 14px 28px;

    font-size: 17px;

    transition: all .25s ease;

    white-space: nowrap;

}


.menu a i {

    width: 28px;

    min-width: 28px;

    margin-right: 12px;

    font-size: 18px;

    text-align: center;

}


.menu a:hover {

    background:
        rgba(255,255,255,.13);

    padding-left: 34px;

}


.menu a.active {

    background:
        rgba(255,255,255,.18);

}


/* =====================================
   LOGOUT
===================================== */

.logout {

    flex-shrink: 0;

    padding: 12px 15px 20px;

    border-top:
        1px solid
        rgba(255,255,255,.20);

    margin-top: auto;

}


.logout a {

    display: flex !important;

    align-items: center;

    justify-content: center;

    gap: 8px;

    width: 100%;

    background:
        #dc3545 !important;

    border:
        1px solid
        #dc3545;

    color: white !important;

    padding: 13px 18px !important;

    border-radius: 13px;

    font-size: 17px;

    font-weight: 700;

    text-decoration: none;

    transition: all .3s ease;

    box-shadow:
        0 4px 10px
        rgba(0,0,0,.15);

}


.logout a i {

    width: auto !important;

    min-width: auto !important;

    margin-right: 3px !important;

    font-size: 19px;

}


.logout a:hover {

    background:
        #bb2d3b !important;

    border-color:
        #bb2d3b;

    transform:
        translateY(-2px);

    box-shadow:
        0 6px 15px
        rgba(220,53,69,.4);

}


/* =====================================
   MAIN
===================================== */

.main {

    margin-left: 260px;

    padding: 25px;

}


/* =====================================
   TOP BAR
===================================== */

.topbar {

    background: white;

    border-radius: 14px;

    padding: 20px 25px;

    margin-bottom: 25px;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,.08);

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.topbar h3 {

    margin: 0;

    font-weight: bold;

}


.topbar p {

    margin: 5px 0 0;

    color: #6c757d;

}


.course-count {

    background: #e7f1ff;

    color: #0d6efd;

    padding: 9px 16px;

    border-radius: 20px;

    font-weight: bold;

}


/* =====================================
   BACK BUTTON
===================================== */

.back-area {

    margin-bottom: 25px;

}


.back-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    background: white;

    color: #1f2937 !important;

    border:
        1px solid
        #d1d5db;

    border-radius: 8px;

    padding: 10px 18px;

    text-decoration: none;

    font-weight: 600;

    box-shadow:
        0 2px 6px
        rgba(0,0,0,.08);

    transition: .3s;

}


.back-btn:hover {

    background: #f3f4f6;

    transform:
        translateY(-2px);

}


/* =====================================
   COURSE CARD
===================================== */

.course-card {

    background: white;

    border-radius: 15px;

    padding: 25px;

    height: 100%;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,.08);

    transition: .3s;

    border:
        1px solid
        #edf0f5;

}


.course-card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 8px 22px
        rgba(0,0,0,.12);

}


/* =====================================
   COURSE ICON
===================================== */

.course-icon {

    width: 62px;

    height: 62px;

    border-radius: 13px;

    background: #e7f1ff;

    color: #0d6efd;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 26px;

    margin-bottom: 18px;

}


/* =====================================
   COURSE CODE
===================================== */

.course-code {

    color: #0d6efd;

    font-weight: bold;

    font-size: 14px;

    text-transform: uppercase;

}


/* =====================================
   COURSE NAME
===================================== */

.course-name {

    font-size: 20px;

    font-weight: bold;

    margin: 6px 0 20px;

    color: #1f2937;

}


/* =====================================
   COURSE INFO
===================================== */

.course-info {

    color: #6c757d;

    font-size: 14px;

    margin-bottom: 10px;

}


.course-info i {

    width: 22px;

    color: #0d6efd;

}


/* =====================================
   COURSE FOOTER
===================================== */

.course-footer {

    margin-top: 20px;

    padding-top: 15px;

    border-top:
        1px solid
        #e9ecef;

}


.course-id {

    color: #6c757d;

    font-size: 13px;

}


/* =====================================
   EMPTY
===================================== */

.empty-box {

    background: white;

    border-radius: 15px;

    padding: 70px 20px;

    text-align: center;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,.08);

}


.empty-box i {

    font-size: 60px;

    color: #adb5bd;

    margin-bottom: 18px;

}


.empty-box h4 {

    font-weight: bold;

    margin-bottom: 8px;

}


/* =====================================
   MOBILE
===================================== */

@media(max-width:768px) {

    .sidebar {

        width: 220px;

    }

    .main {

        margin-left: 220px;

    }

}


@media(max-width:600px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

        min-height: auto;

    }


    .main {

        margin-left: 0;

        padding: 15px;

    }


    .main-menu {

        flex: none;

    }


    .logout {

        margin-top: 5px;

    }


    .topbar {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }

}

</style>

</head>


<body>


<!-- =====================================
     SIDEBAR
===================================== -->

<div class="sidebar">


    <!-- LOGO -->

    <div class="logo">

        <i class="fa-solid fa-chalkboard-user"></i>

        <br>

        Teacher Panel

    </div>


    <!-- TEACHER INFO -->

    <div class="teacher-info">

        <div class="teacher-avatar">

            <i class="fa-solid fa-user-tie"></i>

        </div>


        <h6>

            <?= htmlspecialchars($teacher_name) ?>

        </h6>


        <small>

            Teacher

        </small>

    </div>


    <!-- MAIN MENU -->

    <div class="menu main-menu">


        <a href="dashboard.php">

            <i class="fa-solid fa-gauge"></i>

            <span>Dashboard</span>

        </a>


        <a href="profile.php">

            <i class="fa-solid fa-user"></i>

            <span>My Profile</span>

        </a>


        <a
            href="courses.php"
            class="active">

            <i class="fa-solid fa-book"></i>

            <span>My Courses</span>

        </a>


        <a href="students.php">

            <i class="fa-solid fa-users"></i>

            <span>Students</span>

        </a>


        <a href="marks.php">

            <i class="fa-solid fa-pen-to-square"></i>

            <span>Enter Marks</span>

        </a>


        <a href="results.php">

            <i class="fa-solid fa-square-poll-vertical"></i>

            <span>Results</span>

        </a>


        <a href="send_result.php">

            <i class="fa-solid fa-paper-plane"></i>

            <span>Send Result to Admin</span>

        </a>


    </div>


    <!-- LOGOUT -->

    <div class="logout">

        <a href="../logout.php">

            <i class="fa-solid fa-right-from-bracket"></i>

            <span>Logout</span>

        </a>

    </div>


</div>



<!-- =====================================
     MAIN CONTENT
===================================== -->

<div class="main">


    <!-- TOP BAR -->

    <div class="topbar">


        <div>

            <h3>

                <i class="fa-solid fa-book-open text-primary"></i>

                My Courses

            </h3>


            <p>

                Courses assigned to you

            </p>

        </div>


        <div class="course-count">

            <i class="fa-solid fa-book"></i>

            <?= $total_courses ?>

            <?= ($total_courses == 1)
                ? 'Course'
                : 'Courses'
            ?>

        </div>


    </div>



    <!-- BACK BUTTON -->

    <div class="back-area">

        <a
            href="dashboard.php"
            class="back-btn">

            <i class="fa-solid fa-arrow-left"></i>

            Back to Dashboard

        </a>

    </div>



    <!-- =====================================
         COURSES
    ===================================== -->

    <?php if ($total_courses > 0) { ?>


        <div class="row g-4">


            <?php foreach ($courses as $course) { ?>


                <div class="col-md-6 col-lg-4">


                    <div class="course-card">


                        <!-- ICON -->

                        <div class="course-icon">

                            <i class="fa-solid fa-book-open"></i>

                        </div>


                        <!-- COURSE CODE -->

                        <div class="course-code">

                            <?= htmlspecialchars(
                                $course['course_code'] ?? ''
                            ) ?>

                        </div>


                        <!-- COURSE NAME -->

                        <div class="course-name">

                            <?= htmlspecialchars(
                                $course['course_name'] ?? ''
                            ) ?>

                        </div>


                        <!-- CREDIT -->

                        <div class="course-info">

                            <i class="fa-solid fa-star"></i>

                            <strong>Credit:</strong>

                            <?= htmlspecialchars(
                                $course['credit'] ?? 'N/A'
                            ) ?>

                        </div>


                        <!-- DEPARTMENT -->

                        <div class="course-info">

                            <i class="fa-solid fa-building"></i>

                            <strong>Department:</strong>

                            <?= htmlspecialchars(
                                $course['department_id'] ?? 'N/A'
                            ) ?>

                        </div>


                        <!-- SEMESTER -->

                        <div class="course-info">

                            <i class="fa-solid fa-layer-group"></i>

                            <strong>Semester:</strong>

                            <?= htmlspecialchars(
                                $course['semester_id'] ?? 'N/A'
                            ) ?>

                        </div>


                        <!-- COURSE FOOTER -->

                        <div class="course-footer">

                            <div class="course-id">

                                <i class="fa-solid fa-hashtag"></i>

                                Course ID:

                                <strong>

                                    <?= htmlspecialchars(
                                        $course['course_id'] ?? 'N/A'
                                    ) ?>

                                </strong>

                            </div>

                        </div>


                    </div>


                </div>


            <?php } ?>


        </div>


    <?php } else { ?>


        <!-- EMPTY -->

        <div class="empty-box">


            <i class="fa-solid fa-book-open"></i>


            <h4>

                No Courses Assigned

            </h4>


            <p class="text-muted mb-0">

                No courses have been assigned
                to you yet.

            </p>


        </div>


    <?php } ?>


</div>


</body>

</html>