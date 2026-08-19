brary
/
dashboard_fixed.php


<?php

session_start();

include '../config/database.php';
include '../config/session.php';

checkAdmin();


// =====================================================
// TEACHER PENDING NOTIFICATION
// =====================================================

$pendingTeacherQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM teachers
     WHERE status = 'Pending'"
);

$pendingTeacherData = mysqli_fetch_assoc(
    $pendingTeacherQuery
);

$pendingTeachers = $pendingTeacherData['total'];


// =====================================================
// STUDENT PENDING NOTIFICATION
// =====================================================

$pendingStudentQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM students
     WHERE status = 'Pending'"
);

$pendingStudentData = mysqli_fetch_assoc(
    $pendingStudentQuery
);

$pendingStudents = $pendingStudentData['total'];


// =====================================================
// TOTAL STUDENTS
// =====================================================

$studentQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM students"
);

$studentData = mysqli_fetch_assoc(
    $studentQuery
);

$totalStudents = $studentData['total'];


// =====================================================
// TOTAL TEACHERS
// =====================================================

$teacherQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM teachers
     WHERE status = 'Active'"
);

$teacherData = mysqli_fetch_assoc(
    $teacherQuery
);

$totalTeachers = $teacherData['total'];


// =====================================================
// TOTAL DEPARTMENTS
// =====================================================

$departmentQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM departments"
);

$departmentData = mysqli_fetch_assoc(
    $departmentQuery
);

$totalDepartments = $departmentData['total'];


// =====================================================
// TOTAL SEMESTERS
// =====================================================

$semesterQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM semesters"
);

$semesterData = mysqli_fetch_assoc(
    $semesterQuery
);

$totalSemesters = $semesterData['total'];


// =====================================================
// TOTAL COURSES
// =====================================================

$courseQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM courses"
);

$courseData = mysqli_fetch_assoc(
    $courseQuery
);

$totalCourses = $courseData['total'];


// =====================================================
// ASSIGN COURSE TO TEACHER
// =====================================================

$assignMessage = "";
$assignType = "";

if (isset($_POST['assign_course'])) {

    $assignTeacherId = (int)($_POST['teacher_id'] ?? 0);
    $assignCourseId  = (int)($_POST['course_id'] ?? 0);

    if ($assignTeacherId <= 0 || $assignCourseId <= 0) {

        $assignMessage = "Please select a valid Teacher ID and Course.";
        $assignType = "danger";

    } else {

        $teacherCheck = mysqli_prepare(
            $conn,
            "SELECT teacher_id
             FROM teachers
             WHERE teacher_id = ?
             AND status = 'Active'
             LIMIT 1"
        );

        $teacherExists = false;

        if ($teacherCheck) {
            mysqli_stmt_bind_param($teacherCheck, "i", $assignTeacherId);
            mysqli_stmt_execute($teacherCheck);

            $teacherResult = mysqli_stmt_get_result($teacherCheck);

            $teacherExists =
                $teacherResult &&
                mysqli_num_rows($teacherResult) > 0;

            mysqli_stmt_close($teacherCheck);
        }

        $courseCheck = mysqli_prepare(
            $conn,
            "SELECT course_id
             FROM courses
             WHERE course_id = ?
             LIMIT 1"
        );

        $courseExists = false;

        if ($courseCheck) {
            mysqli_stmt_bind_param($courseCheck, "i", $assignCourseId);
            mysqli_stmt_execute($courseCheck);

            $courseResult = mysqli_stmt_get_result($courseCheck);

            $courseExists =
                $courseResult &&
                mysqli_num_rows($courseResult) > 0;

            mysqli_stmt_close($courseCheck);
        }

        if (!$teacherExists) {

            $assignMessage =
                "Teacher ID not found or teacher is not Active.";
            $assignType = "danger";

        } elseif (!$courseExists) {

            $assignMessage = "Selected course was not found.";
            $assignType = "danger";

        } else {

            $duplicateCheck = mysqli_prepare(
                $conn,
                "SELECT id
                 FROM teacher_courses
                 WHERE teacher_id = ?
                 AND course_id = ?
                 LIMIT 1"
            );

            $alreadyAssigned = false;

            if ($duplicateCheck) {
                mysqli_stmt_bind_param(
                    $duplicateCheck,
                    "ii",
                    $assignTeacherId,
                    $assignCourseId
                );

                mysqli_stmt_execute($duplicateCheck);

                $duplicateResult =
                    mysqli_stmt_get_result($duplicateCheck);

                $alreadyAssigned =
                    $duplicateResult &&
                    mysqli_num_rows($duplicateResult) > 0;

                mysqli_stmt_close($duplicateCheck);
            }

            if ($alreadyAssigned) {

                $assignMessage =
                    "This course is already assigned to this teacher.";
                $assignType = "warning";

            } else {

                $insertAssign = mysqli_prepare(
                    $conn,
                    "INSERT INTO teacher_courses
                     (teacher_id, course_id)
                     VALUES (?, ?)"
                );

                if ($insertAssign) {

                    mysqli_stmt_bind_param(
                        $insertAssign,
                        "ii",
                        $assignTeacherId,
                        $assignCourseId
                    );

                    if (mysqli_stmt_execute($insertAssign)) {

                        $assignMessage =
                            "Course assigned successfully to Teacher ID "
                            . $assignTeacherId . ".";
                        $assignType = "success";

                    } else {

                        $assignMessage =
                            "Course assignment failed.";
                        $assignType = "danger";
                    }

                    mysqli_stmt_close($insertAssign);

                } else {

                    $assignMessage =
                        "Unable to prepare course assignment.";
                    $assignType = "danger";
                }
            }
        }
    }
}


// =====================================================
// TEACHER LIST FOR ASSIGN COURSE
// =====================================================

$activeTeachers = mysqli_query(
    $conn,
    "SELECT teacher_id, full_name
     FROM teachers
     WHERE status = 'Active'
     ORDER BY teacher_id ASC"
);


// =====================================================
// COURSE LIST FOR ASSIGN COURSE
// =====================================================

$allCourses = mysqli_query(
    $conn,
    "SELECT course_id, course_code, course_name, credit
     FROM courses
     ORDER BY course_code ASC"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard</title>


<!-- Bootstrap -->

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<!-- Font Awesome -->

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


<style>

body
{
    margin: 0;
    padding: 0;
    background: #f4f6f9;
    font-family: Arial, sans-serif;
}


/* ============================= */
/* SIDEBAR */
/* ============================= */

.sidebar
{
    position: fixed;
    left: 0;
    top: 0;

    width: 250px;
    height: 100vh;

    background: #212529;

    color: white;

    overflow-y: auto;
}


.sidebar .logo
{
    padding: 22px 15px;

    text-align: center;

    background: #0d6efd;

    font-size: 22px;

    font-weight: bold;
}


.sidebar a
{
    display: flex;

    align-items: center;

    justify-content: space-between;

    color: #ddd;

    text-decoration: none;

    padding: 13px 18px;

    transition: 0.3s;
}


.sidebar a:hover
{
    background: #343a40;

    color: white;
}


.sidebar a.active
{
    background: #0d6efd;

    color: white;
}


.menu-left
{
    display: flex;

    align-items: center;

    gap: 10px;
}


/* ============================= */
/* MAIN CONTENT */
/* ============================= */

.main
{
    margin-left: 250px;

    min-height: 100vh;
}


/* ============================= */
/* TOP NAVBAR */
/* ============================= */

.topbar
{
    background: white;

    padding: 15px 25px;

    box-shadow:
        0 2px 5px rgba(0,0,0,0.08);

    display: flex;

    justify-content: space-between;

    align-items: center;
}


.topbar h4
{
    margin: 0;
}


/* ============================= */
/* CONTENT */
/* ============================= */

.content
{
    padding: 25px;
}


/* ============================= */
/* STAT CARD */
/* ============================= */

.stat-card
{
    border: none;

    border-radius: 12px;

    box-shadow:
        0 3px 10px rgba(0,0,0,0.08);

    transition: 0.3s;
}


.stat-card:hover
{
    transform: translateY(-3px);
}


.stat-icon
{
    font-size: 35px;

    opacity: 0.8;
}


/* ============================= */
/* NOTIFICATION */
/* ============================= */

.notification-badge
{
    min-width: 25px;

    text-align: center;
}


/* ============================= */
/* MOBILE */
/* ============================= */

@media(max-width: 768px)
{

    .sidebar
    {
        width: 70px;
    }


    .sidebar .logo
    {
        font-size: 0;
    }


    .sidebar .logo i
    {
        font-size: 22px;
    }


    .sidebar a
    {
        justify-content: center;
    }


    .sidebar a span.menu-text
    {
        display: none;
    }


    .main
    {
        margin-left: 70px;
    }


    .content
    {
        padding: 15px;
    }

}

</style>

</head>


<body>


<!-- ===================================================== -->
<!-- SIDEBAR -->
<!-- ===================================================== -->

<div class="sidebar">


<div class="logo">

<i class="fa-solid fa-user-shield"></i>

<span class="menu-text">

Admin Panel

</span>

</div>


<!-- Dashboard -->

<a href="dashboard.php"
   class="active">

<div class="menu-left">

<i class="fa-solid fa-gauge"></i>

<span class="menu-text">

Dashboard

</span>

</div>

</a>


<!-- Students -->

<a href="student.php">

<div class="menu-left">

<i class="fa-solid fa-user-graduate"></i>

<span class="menu-text">

Students

</span>

</div>

</a>


<!-- Student Approval -->

<a href="student_approval.php">

<div class="menu-left">

<i class="fa-solid fa-user-clock"></i>

<span class="menu-text">

Student Approval

</span>

</div>


<?php

if ($pendingStudents > 0)
{

?>

<span class="badge bg-danger notification-badge">

<?php

echo $pendingStudents;

?>

</span>

<?php

}

?>

</a>


<!-- Teacher Approval -->

<a href="teacher-approval.php">

<div class="menu-left">

<i class="fa-solid fa-chalkboard-user"></i>

<span class="menu-text">

Teacher Approval

</span>

</div>


<?php

if ($pendingTeachers > 0)
{

?>

<span class="badge bg-danger notification-badge">

<?php

echo $pendingTeachers;

?>

</span>

<?php

}

?>

</a>


<!-- Department -->

<a href="department.php">

<div class="menu-left">

<i class="fa-solid fa-building"></i>

<span class="menu-text">

Department

</span>

</div>

</a>


<!-- Semester -->

<a href="semester.php">

<div class="menu-left">

<i class="fa-solid fa-layer-group"></i>

<span class="menu-text">

Semester

</span>

</div>

</a>


<!-- Course -->

<a href="course.php">

<div class="menu-left">

<i class="fa-solid fa-book"></i>

<span class="menu-text">

Course

</span>

</div>

</a>


<!-- Exam -->

<a href="exam.php">

<div class="menu-left">

<i class="fa-solid fa-file-pen"></i>

<span class="menu-text">

Exam

</span>

</div>

</a>




<!-- Publish Result -->

<a href="publish-result.php">

<div class="menu-left">

<i class="fa-solid fa-bullhorn"></i>

<span class="menu-text">

Publish Result

</span>

</div>

</a>





<!-- Profile -->

<a href="profile.php">

<div class="menu-left">

<i class="fa-solid fa-user"></i>

<span class="menu-text">

Profile

</span>

</div>

</a>

<!-- Logout -->

<a href="../logout.php"
   onclick="return confirm('Are you sure you want to logout?');">

<div class="menu-left">

<i class="fa-solid fa-right-from-bracket"></i>

<span class="menu-text">

Logout

</span>

</div>

</a>


</div>


<!-- ===================================================== -->
<!-- MAIN -->
<!-- ===================================================== -->

<div class="main">


<!-- TOP BAR -->

<div class="topbar">


<div>

<h4>

Admin Dashboard

</h4>

<small class="text-muted">

Online Result Management System

</small>

</div>


<div>

<i class="fa-solid fa-user-shield"></i>

<strong>

Admin

</strong>

</div>


</div>


<!-- ===================================================== -->
<!-- CONTENT -->
<!-- ===================================================== -->

<div class="content">


<h3 class="mb-4">

Dashboard Overview

</h3>


<!-- ============================= -->
<!-- STAT CARDS -->
<!-- ============================= -->

<div class="row g-4">


<!-- Students -->

<div class="col-md-6 col-lg-3">

<div class="card stat-card">

<div class="card-body">

<div class="d-flex
            justify-content-between
            align-items-center">


<div>

<h6 class="text-muted">

Total Students

</h6>

<h2>

<?php

echo $totalStudents;

?>

</h2>

</div>


<div class="stat-icon text-primary">

<i class="fa-solid fa-user-graduate"></i>

</div>


</div>

</div>

</div>

</div>


<!-- Teachers -->

<div class="col-md-6 col-lg-3">

<div class="card stat-card">

<div class="card-body">

<div class="d-flex
            justify-content-between
            align-items-center">


<div>

<h6 class="text-muted">

Active Teachers

</h6>

<h2>

<?php

echo $totalTeachers;

?>

</h2>

</div>


<div class="stat-icon text-success">

<i class="fa-solid fa-chalkboard-user"></i>

</div>


</div>

</div>

</div>

</div>


<!-- Departments -->

<div class="col-md-6 col-lg-3">

<div class="card stat-card">

<div class="card-body">

<div class="d-flex
            justify-content-between
            align-items-center">


<div>

<h6 class="text-muted">

Departments

</h6>

<h2>

<?php

echo $totalDepartments;

?>

</h2>

</div>


<div class="stat-icon text-warning">

<i class="fa-solid fa-building"></i>

</div>


</div>

</div>

</div>

</div>


<!-- Courses -->

<div class="col-md-6 col-lg-3">

<div class="card stat-card">

<div class="card-body">

<div class="d-flex
            justify-content-between
            align-items-center">


<div>

<h6 class="text-muted">

Courses

</h6>

<h2>

<?php

echo $totalCourses;

?>

</h2>

</div>


<div class="stat-icon text-danger">

<i class="fa-solid fa-book"></i>

</div>


</div>

</div>

</div>

</div>


</div>


<!-- ===================================================== -->
<!-- APPROVAL NOTIFICATIONS -->
<!-- ===================================================== -->

<div class="row g-4 mt-2">


<!-- Teacher Approval -->

<div class="col-md-6">

<div class="card shadow-sm border-0">

<div class="card-body">


<div class="d-flex
            justify-content-between
            align-items-center">


<div>

<h5>

<i class="fa-solid fa-chalkboard-user
          text-primary"></i>

Teacher Approval

</h5>


<p class="text-muted mb-0">

<?php

if ($pendingTeachers > 0)
{

echo
$pendingTeachers .
" teacher(s) waiting for approval.";

}
else
{

echo
"No pending teacher registration.";

}

?>

</p>

</div>


<?php

if ($pendingTeachers > 0)
{

?>

<span class="badge bg-danger fs-6">

<?php

echo $pendingTeachers;

?>

</span>

<?php

}
else
{

?>

<span class="badge bg-success">

0

</span>

<?php

}

?>

</div>


<?php

if ($pendingTeachers > 0)
{

?>

<a href="teacher-approval.php"
   class="btn btn-primary mt-3">

Review Teacher Applications

</a>

<?php

}

?>

</div>

</div>

</div>


<!-- Student Approval -->

<div class="col-md-6">

<div class="card shadow-sm border-0">

<div class="card-body">


<div class="d-flex
            justify-content-between
            align-items-center">


<div>

<h5>

<i class="fa-solid fa-user-graduate
          text-success"></i>

Student Approval

</h5>


<p class="text-muted mb-0">

<?php

if ($pendingStudents > 0)
{

echo
$pendingStudents .
" student(s) waiting for approval.";

}
else
{

echo
"No pending student registration.";

}

?>

</p>

</div>


<?php

if ($pendingStudents > 0)
{

?>

<span class="badge bg-danger fs-6">

<?php

echo $pendingStudents;

?>

</span>

<?php

}
else
{

?>

<span class="badge bg-success">

0

</span>

<?php

}

?>

</div>


<a href="student_approval.php"
   class="btn btn-success mt-3">

<i class="fa-solid fa-user-check"></i>
Review Student Applications

</a>

</div>

</div>

</div>


</div>


<!-- ===================================================== -->
<!-- ASSIGN COURSE TO TEACHER -->
<!-- ===================================================== -->

<div class="card shadow-sm border-0 mt-4">

<div class="card-header bg-white">

<h5 class="mb-0">

<i class="fa-solid fa-link text-primary"></i>

Assign Course to Teacher

</h5>

<small class="text-muted">

Assign any available course to an active teacher.

</small>

</div>

<div class="card-body">

<?php if ($assignMessage != "") { ?>

<div class="alert alert-<?php echo $assignType; ?> alert-dismissible fade show">

<i class="fa-solid
<?php
if ($assignType == 'success') {
    echo ' fa-circle-check';
} elseif ($assignType == 'warning') {
    echo ' fa-triangle-exclamation';
} else {
    echo ' fa-circle-exclamation';
}
?>"></i>

<?php echo htmlspecialchars($assignMessage); ?>

<button type="button"
        class="btn-close"
        data-bs-dismiss="alert"></button>

</div>

<?php } ?>

<form method="POST">

<div class="row g-3 align-items-end">

<div class="col-md-4">

<label class="form-label fw-bold">
Teacher ID
</label>

<select
name="teacher_id"
class="form-select"
required>

<option value="">
Select Teacher ID
</option>

<?php
if ($activeTeachers) {
    while ($teacher = mysqli_fetch_assoc($activeTeachers)) {
?>

<option value="<?php echo $teacher['teacher_id']; ?>">

<?php echo htmlspecialchars(
    $teacher['teacher_id'] . ' - ' . $teacher['full_name']
); ?>

</option>

<?php
    }
}
?>

</select>

</div>


<div class="col-md-5">

<label class="form-label fw-bold">
Course
</label>

<select
name="course_id"
class="form-select"
required>

<option value="">
Select Course
</option>

<?php
if ($allCourses) {
    while ($course = mysqli_fetch_assoc($allCourses)) {
?>

<option value="<?php echo $course['course_id']; ?>">

<?php echo htmlspecialchars(
    $course['course_code'] . ' - ' .
    $course['course_name'] . ' (' .
    $course['credit'] . ' Credit)'
); ?>

</option>

<?php
    }
}
?>

</select>

</div>


<div class="col-md-3">

<button
type="submit"
name="assign_course"
class="btn btn-primary w-100">

<i class="fa-solid fa-link"></i>

Assign Course

</button>

</div>

</div>

</form>

</div>

</div>


<!-- ===================================================== -->
<!-- QUICK ACTIONS -->
<!-- ===================================================== -->

<div class="card shadow-sm border-0 mt-4">


<div class="card-header bg-white">

<h5 class="mb-0">

Quick Actions

</h5>

</div>


<div class="card-body">


<div class="row g-3">


<div class="col-md-3">

<a href="student.php"
   class="btn btn-outline-primary w-100">

<i class="fa-solid fa-user-plus"></i>

Manage Students

</a>

</div>


<div class="col-md-3">

<a href="department.php"
   class="btn btn-outline-warning w-100">

<i class="fa-solid fa-building"></i>

Departments

</a>

</div>


<div class="col-md-3">

<a href="course.php"
   class="btn btn-outline-success w-100">

<i class="fa-solid fa-book"></i>

Courses

</a>

</div>


<div class="col-md-3">

<a href="exam.php"
   class="btn btn-outline-danger w-100">

<i class="fa-solid fa-file-pen"></i>

Exams

</a>

</div>


</div>

</div>

</div>


</div>

</div>


<!-- Bootstrap JS -->

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<!-- Refresh dashboard periodically so new Pending registrations appear -->
<script>
setInterval(function () {
    window.location.reload();
}, 30000);
</script>


</body>

</html>