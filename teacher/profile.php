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


// =====================================
// GET TEACHER ID
// =====================================

$teacher_id = $_SESSION['teacher_id'];

$teacher = null;


// =====================================
// GET TEACHER DATA
// =====================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM teachers
     WHERE teacher_id = ?
     LIMIT 1"
);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $teacher_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) == 1) {

        $teacher = mysqli_fetch_assoc($result);

    }

    mysqli_stmt_close($stmt);
}


// =====================================
// TEACHER NOT FOUND
// =====================================

if (!$teacher) {

    session_destroy();

    header("Location: ../login.php?role=teacher");

    exit();
}


// =====================================
// TEACHER INFORMATION
// =====================================

$teacher_name =
    $teacher['full_name'] ?? 'Teacher';

$teacher_email =
    $teacher['email'] ?? '';

$teacher_phone =
    $teacher['phone'] ?? '';

$department =
    $teacher['department'] ?? '';

$designation =
    $teacher['designation'] ?? '';

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>
    Teacher Profile
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

.sidebar .logo {

    text-align: center;

    font-size: 23px;

    font-weight: bold;

    padding: 25px 15px 28px;

    border-bottom:
        1px solid
        rgba(255,255,255,.2);

    flex-shrink: 0;

}


.sidebar .logo i {

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

    color: #fff !important;

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


.logout a:hover i {

    transform:
        translateX(3px);

}


/* =====================================
   MAIN CONTENT
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

    border-radius: 12px;

    padding: 18px 22px;

    margin-bottom: 25px;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,.08);

}


.topbar h3 {

    margin: 0;

    font-weight: bold;

}


/* =====================================
   BACK BUTTON
===================================== */

.back-area {

    margin-bottom: 20px;

}


.back-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    background: white;

    color: #1f2937;

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
   PROFILE CARD
===================================== */

.profile-card {

    background: white;

    border-radius: 15px;

    padding: 30px;

    box-shadow:
        0 4px 18px
        rgba(0,0,0,.08);

}


/* =====================================
   PROFILE HEADER
===================================== */

.profile-header {

    display: flex;

    align-items: center;

    gap: 20px;

    padding-bottom: 25px;

    margin-bottom: 25px;

    border-bottom:
        1px solid
        #e5e7eb;

}


.profile-avatar {

    width: 100px;

    height: 100px;

    min-width: 100px;

    border-radius: 50%;

    background:
        linear-gradient(
            135deg,
            #0d6efd,
            #084298
        );

    color: white;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 42px;

    box-shadow:
        0 5px 15px
        rgba(13,110,253,.25);

}


.profile-header h2 {

    margin: 0;

    font-weight: bold;

    font-size: 28px;

}


.profile-header p {

    margin: 6px 0 0;

    color: #6c757d;

    font-size: 16px;

}


/* =====================================
   INFORMATION BOX
===================================== */

.info-box {

    background:
        #f8fafc;

    border:
        1px solid
        #e5e7eb;

    border-radius: 10px;

    padding: 18px;

    height: 100%;

    transition: .25s;

}


.info-box:hover {

    background:
        #eef5ff;

    border-color:
        #cfe2ff;

    transform:
        translateY(-2px);

}


.info-label {

    color:
        #6b7280;

    font-size: 13px;

    margin-bottom: 6px;

}


.info-value {

    font-size: 16px;

    font-weight: 600;

    color:
        #1f2937;

    word-break: break-word;

}


/* =====================================
   SECTION TITLE
===================================== */

.section-title {

    font-size: 20px;

    font-weight: 700;

    margin-bottom: 20px;

    padding-bottom: 12px;

    border-bottom:
        2px solid
        #e9ecef;

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


    .profile-header {

        flex-direction: column;

        text-align: center;

    }


    .profile-card {

        padding: 20px;

    }


    .topbar {

        padding: 16px;

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


    <!-- TEACHER INFORMATION -->

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


        <a
            href="profile.php"
            class="active">

            <i class="fa-solid fa-user"></i>

            <span>My Profile</span>

        </a>


        <a href="courses.php">

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

        <h3>

            <i class="fa-solid fa-user text-primary"></i>

            Teacher Profile

        </h3>

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


    <!-- PROFILE CARD -->

    <div class="profile-card">


        <!-- PROFILE HEADER -->

        <div class="profile-header">


            <div class="profile-avatar">

                <i class="fa-solid fa-user-tie"></i>

            </div>


            <div>

                <h2>

                    <?= htmlspecialchars($teacher_name) ?>

                </h2>


                <p>

                    <?= htmlspecialchars(
                        $designation ?: 'Teacher'
                    ) ?>

                </p>

            </div>


        </div>



        <!-- INFORMATION -->

        <div class="section-title">

            <i class="fa-solid fa-circle-info text-primary"></i>

            Personal Information

        </div>


        <div class="row g-4">


            <!-- TEACHER ID -->

            <div class="col-md-6">

                <div class="info-box">

                    <div class="info-label">

                        Teacher ID

                    </div>


                    <div class="info-value">

                        <?= htmlspecialchars(
                            $teacher['teacher_id'] ?? $teacher_id
                        ) ?>

                    </div>

                </div>

            </div>


            <!-- FULL NAME -->

            <div class="col-md-6">

                <div class="info-box">

                    <div class="info-label">

                        Full Name

                    </div>


                    <div class="info-value">

                        <?= htmlspecialchars(
                            $teacher_name
                        ) ?>

                    </div>

                </div>

            </div>


            <!-- EMAIL -->

            <div class="col-md-6">

                <div class="info-box">

                    <div class="info-label">

                        Email Address

                    </div>


                    <div class="info-value">

                        <?= htmlspecialchars(
                            $teacher_email ?: 'Not Available'
                        ) ?>

                    </div>

                </div>

            </div>


            <!-- PHONE -->

            <div class="col-md-6">

                <div class="info-box">

                    <div class="info-label">

                        Phone Number

                    </div>


                    <div class="info-value">

                        <?= htmlspecialchars(
                            $teacher_phone ?: 'Not Available'
                        ) ?>

                    </div>

                </div>

            </div>


            <!-- DEPARTMENT -->

            <div class="col-md-6">

                <div class="info-box">

                    <div class="info-label">

                        Department

                    </div>


                    <div class="info-value">

                        <?= htmlspecialchars(
                            $department ?: 'Not Available'
                        ) ?>

                    </div>

                </div>

            </div>


            <!-- DESIGNATION -->

            <div class="col-md-6">

                <div class="info-box">

                    <div class="info-label">

                        Designation

                    </div>


                    <div class="info-value">

                        <?= htmlspecialchars(
                            $designation ?: 'Teacher'
                        ) ?>

                    </div>

                </div>

            </div>


        </div>


    </div>


</div>


</body>

</html>