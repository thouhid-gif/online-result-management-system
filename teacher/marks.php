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

$teacher_name =
    $_SESSION['teacher_name'] ?? 'Teacher';


// ==================================================
// MESSAGE
// ==================================================

$message = "";
$messageType = "";


// ==================================================
// GRADE FUNCTION
// ==================================================

function getGrade($marks)
{
    if ($marks >= 80) {
        return "A+";
    } elseif ($marks >= 75) {
        return "A";
    } elseif ($marks >= 70) {
        return "A-";
    } elseif ($marks >= 65) {
        return "B+";
    } elseif ($marks >= 60) {
        return "B";
    } elseif ($marks >= 55) {
        return "B-";
    } elseif ($marks >= 50) {
        return "C+";
    } elseif ($marks >= 45) {
        return "C";
    } elseif ($marks >= 40) {
        return "D";
    } else {
        return "F";
    }
}


// ==================================================
// GPA FUNCTION
// ==================================================

function getGPA($marks)
{
    if ($marks >= 80) {
        return 4.00;
    } elseif ($marks >= 75) {
        return 3.75;
    } elseif ($marks >= 70) {
        return 3.50;
    } elseif ($marks >= 65) {
        return 3.25;
    } elseif ($marks >= 60) {
        return 3.00;
    } elseif ($marks >= 55) {
        return 2.75;
    } elseif ($marks >= 50) {
        return 2.50;
    } elseif ($marks >= 45) {
        return 2.25;
    } elseif ($marks >= 40) {
        return 2.00;
    } else {
        return 0.00;
    }
}


// ==================================================
// SELECTED COURSE
// ==================================================

$selected_course = 0;

if (isset($_POST['course_id'])) {

    $selected_course =
        intval($_POST['course_id']);

} elseif (isset($_GET['course_id'])) {

    $selected_course =
        intval($_GET['course_id']);
}


// ==================================================
// SAVE MARKS
// ==================================================

if (isset($_POST['save_marks'])) {

    $course_id =
        intval($_POST['course_id']);

    $exam_id =
        intval($_POST['exam_id']);

    $student_roll =
        trim($_POST['student_roll'] ?? '');

    $marks_value =
        floatval($_POST['marks'] ?? 0);


    $selected_course =
        $course_id;


    // ----------------------------------------------
    // BASIC VALIDATION
    // ----------------------------------------------

    if (
        $course_id <= 0 ||
        $exam_id <= 0 ||
        empty($student_roll)
    ) {

        $message =
            "Please fill all required fields.";

        $messageType =
            "danger";

    } else {


        // ------------------------------------------
        // GET EXAM TOTAL MARKS
        // ------------------------------------------

        $exam_stmt = mysqli_prepare(
            $conn,
            "SELECT total_marks
             FROM exams
             WHERE exam_id = ?
             AND course_id = ?
             LIMIT 1"
        );


        if (!$exam_stmt) {

            $message =
                "Database error while checking exam.";

            $messageType =
                "danger";

        } else {

            mysqli_stmt_bind_param(
                $exam_stmt,
                "ii",
                $exam_id,
                $course_id
            );


            mysqli_stmt_execute(
                $exam_stmt
            );


            $exam_result =
                mysqli_stmt_get_result(
                    $exam_stmt
                );


            $exam =
                mysqli_fetch_assoc(
                    $exam_result
                );


            mysqli_stmt_close(
                $exam_stmt
            );


            if (!$exam) {

                $message =
                    "Selected exam does not belong to this course.";

                $messageType =
                    "danger";

            } else {


                $total_marks =
                    floatval(
                        $exam['total_marks']
                    );


                // --------------------------------------
                // CHECK MARKS LIMIT
                // --------------------------------------

                if (
                    $marks_value < 0 ||
                    $marks_value > $total_marks
                ) {

                    $message =
                        "Marks must be between 0 and "
                        . $total_marks
                        . ".";

                    $messageType =
                        "danger";

                } else {


                    // ----------------------------------
                    // FIND STUDENT
                    // ----------------------------------

                    $student_stmt =
                        mysqli_prepare(
                            $conn,
                            "SELECT
                                student_id,
                                student_roll,
                                full_name
                             FROM students
                             WHERE student_roll = ?
                             AND status = 'Active'
                             LIMIT 1"
                        );


                    if (!$student_stmt) {

                        $message =
                            "Database error while finding student.";

                        $messageType =
                            "danger";

                    } else {


                        mysqli_stmt_bind_param(
                            $student_stmt,
                            "s",
                            $student_roll
                        );


                        mysqli_stmt_execute(
                            $student_stmt
                        );


                        $student_result =
                            mysqli_stmt_get_result(
                                $student_stmt
                            );


                        $student =
                            mysqli_fetch_assoc(
                                $student_result
                            );


                        mysqli_stmt_close(
                            $student_stmt
                        );


                        if (!$student) {

                            $message =
                                "Student not found or student is not Active.";

                            $messageType =
                                "danger";

                        } else {


                            $student_id =
                                $student['student_id'];


                            // --------------------------------
                            // CHECK DUPLICATE MARKS
                            // --------------------------------

                            $check_stmt =
                                mysqli_prepare(
                                    $conn,
                                    "SELECT mark_id
                                     FROM marks
                                     WHERE student_id = ?
                                     AND exam_id = ?
                                     AND course_id = ?
                                     LIMIT 1"
                                );


                            if (!$check_stmt) {

                                $message =
                                    "Database error while checking marks.";

                                $messageType =
                                    "danger";

                            } else {


                                mysqli_stmt_bind_param(
                                    $check_stmt,
                                    "iii",
                                    $student_id,
                                    $exam_id,
                                    $course_id
                                );


                                mysqli_stmt_execute(
                                    $check_stmt
                                );


                                $check_result =
                                    mysqli_stmt_get_result(
                                        $check_stmt
                                    );


                                $already_exists =
                                    mysqli_num_rows(
                                        $check_result
                                    );


                                mysqli_stmt_close(
                                    $check_stmt
                                );


                                if (
                                    $already_exists > 0
                                ) {

                                    $message =
                                        "Marks already entered for this student in this exam.";

                                    $messageType =
                                        "warning";

                                } else {


                                    // ----------------------------
                                    // GRADE + GPA
                                    // ----------------------------

                                    $grade =
                                        getGrade(
                                            $marks_value
                                        );


                                    $gpa =
                                        getGPA(
                                            $marks_value
                                        );


                                    // ----------------------------
                                    // INSERT MARKS
                                    // ----------------------------

                                    $insert_stmt =
                                        mysqli_prepare(
                                            $conn,
                                            "INSERT INTO marks
                                            (
                                                student_id,
                                                exam_id,
                                                course_id,
                                                marks,
                                                grade,
                                                gpa
                                            )
                                            VALUES
                                            (?, ?, ?, ?, ?, ?)"
                                        );


                                    if (!$insert_stmt) {

                                        $message =
                                            "Database error while saving marks.";

                                        $messageType =
                                            "danger";

                                    } else {


                                        mysqli_stmt_bind_param(
                                            $insert_stmt,
                                            "iiidsd",
                                            $student_id,
                                            $exam_id,
                                            $course_id,
                                            $marks_value,
                                            $grade,
                                            $gpa
                                        );


                                        if (
                                            mysqli_stmt_execute(
                                                $insert_stmt
                                            )
                                        ) {

                                            $message =
                                                "Marks entered successfully for "
                                                . $student['full_name']
                                                . ".";

                                            $messageType =
                                                "success";

                                        } else {

                                            $message =
                                                "Failed to save marks. "
                                                . mysqli_error($conn);

                                            $messageType =
                                                "danger";
                                        }


                                        mysqli_stmt_close(
                                            $insert_stmt
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}


// ==================================================
// GET COURSES
// ==================================================

$courses =
    mysqli_query(
        $conn,
        "SELECT *
         FROM courses
         ORDER BY course_code ASC"
    );


// ==================================================
// GET EXAMS FOR SELECTED COURSE
// ==================================================

$exams = [];


if ($selected_course > 0) {

    $exam_stmt =
        mysqli_prepare(
            $conn,
            "SELECT *
             FROM exams
             WHERE course_id = ?
             ORDER BY exam_date ASC"
        );


    if ($exam_stmt) {

        mysqli_stmt_bind_param(
            $exam_stmt,
            "i",
            $selected_course
        );


        mysqli_stmt_execute(
            $exam_stmt
        );


        $exam_result =
            mysqli_stmt_get_result(
                $exam_stmt
            );


        while (
            $exam_row =
            mysqli_fetch_assoc(
                $exam_result
            )
        ) {

            $exams[] =
                $exam_row;
        }


        mysqli_stmt_close(
            $exam_stmt
        );
    }
}


// ==================================================
// GET RECENT MARKS
// ==================================================

$recent_marks =
    mysqli_query(
        $conn,
        "SELECT
            m.mark_id,
            m.marks,
            m.grade,
            m.gpa,

            s.student_roll,
            s.full_name,

            c.course_code,
            c.course_name,

            e.exam_name

         FROM marks m

         INNER JOIN students s
            ON m.student_id = s.student_id

         INNER JOIN courses c
            ON m.course_id = c.course_id

         INNER JOIN exams e
            ON m.exam_id = e.exam_id

         ORDER BY m.mark_id DESC

         LIMIT 10"
    );

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>
    Enter Marks | Teacher Panel
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

    background: #f4f7fb;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

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

.teacher-box {

    text-align: center;

    padding: 25px 10px 28px;

    border-bottom:
        1px solid
        rgba(255,255,255,.2);

    flex-shrink: 0;

}


.teacher-icon {

    width: 85px;

    height: 85px;

    background: white;

    color: #0d6efd;

    border-radius: 50%;

    display: flex;

    justify-content: center;

    align-items: center;

    margin: 0 auto 15px;

    font-size: 38px;

    box-shadow:
        0 4px 12px
        rgba(0,0,0,.15);

}


.teacher-box strong {

    font-size: 18px;

}


.teacher-box small {

    font-size: 15px;

    opacity: .85;

}


/* =====================================
   MENU
===================================== */

.main-menu {

    flex: 1;

    padding: 15px 0;

}


.menu a {

    display: flex;

    align-items: center;

    color: white;

    text-decoration: none;

    padding: 14px 28px;

    font-size: 17px;

    transition: all .25s ease;

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
        rgba(255,255,255,.14);

    padding-left: 34px;

}


.menu a.active {

    background:
        rgba(255,255,255,.20);

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

    padding: 13px 15px !important;

    background:
        #dc3545 !important;

    color: white !important;

    border-radius: 13px;

    font-size: 17px;

    font-weight: 700;

    text-decoration: none;

    transition: all .3s ease;

    box-sizing: border-box;

}


.logout a i {

    width: auto !important;

    min-width: auto !important;

    margin-right: 3px !important;

}


.logout a:hover {

    background:
        #bb2d3b !important;

    transform:
        translateY(-2px);

    box-shadow:
        0 6px 15px
        rgba(220,53,69,.35);

}


/* =====================================
   MAIN
===================================== */

.main {

    margin-left: 260px;

    padding: 25px;

}


/* =====================================
   TOPBAR
===================================== */

.topbar {

    background: white;

    padding: 20px 25px;

    border-radius: 14px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,.08);

    margin-bottom: 25px;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.topbar h3 {

    margin: 0;

    font-weight: bold;

}


.topbar small {

    font-size: 14px;

}


/* =====================================
   MESSAGE
===================================== */

.alert {

    border-radius: 10px;

    font-weight: 600;

}


/* =====================================
   CARD
===================================== */

.card-box {

    background: white;

    padding: 25px;

    border-radius: 14px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,.08);

    margin-bottom: 25px;

}


.card-box h5 {

    font-weight: 700;

}


/* =====================================
   FORM
===================================== */

.form-label {

    font-weight: 600;

    margin-bottom: 7px;

}


.form-control,
.form-select {

    padding: 11px 13px;

    border-radius: 8px;

}


.form-control:focus,
.form-select:focus {

    border-color: #0d6efd;

    box-shadow:
        0 0 0 .2rem
        rgba(13,110,253,.12);

}


/* =====================================
   BUTTON
===================================== */

.btn {

    border-radius: 8px;

    font-weight: 600;

}


/* =====================================
   TABLE
===================================== */

.table-responsive {

    border-radius: 8px;

}


.table {

    margin-bottom: 0;

}


.table th {

    background: #0d6efd;

    color: white;

    white-space: nowrap;

}


.table td,
.table th {

    vertical-align: middle;

    padding: 12px;

}


.table tbody tr:hover {

    background: #f8fafc;

}


/* =====================================
   BADGE
===================================== */

.badge {

    font-size: 13px;

    padding: 7px 10px;

}


/* =====================================
   MOBILE
===================================== */

@media(max-width: 768px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

        min-height: auto;

    }


    .main-menu {

        flex: none;

    }


    .logout {

        margin-top: 5px;

    }


    .main {

        margin-left: 0;

        padding: 15px;

    }


    .topbar {

        flex-direction: column;

        align-items: flex-start;

        gap: 10px;

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


    <!-- TEACHER -->

    <div class="teacher-box">

        <div class="teacher-icon">

            <i class="fa-solid fa-user-tie"></i>

        </div>


        <strong>

            <?= htmlspecialchars(
                $teacher_name
            ) ?>

        </strong>


        <br>


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


        <a href="courses.php">

            <i class="fa-solid fa-book"></i>

            <span>My Courses</span>

        </a>


        <a href="students.php">

            <i class="fa-solid fa-users"></i>

            <span>Students</span>

        </a>


        <a
            href="marks.php"
            class="active">

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


    <!-- TOPBAR -->

    <div class="topbar">

        <div>

            <h3>

                <i
                    class="fa-solid fa-pen-to-square text-primary">
                </i>

                Enter Marks

            </h3>

        </div>


        <small class="text-muted">

            Teacher:

            <?= htmlspecialchars(
                $teacher_name
            ) ?>

        </small>

    </div>



    <!-- =====================================
         MESSAGE
    ===================================== -->

    <?php if ($message != "") { ?>

        <div
            class="alert alert-<?= htmlspecialchars(
                $messageType
            ) ?>"
        >

            <?= htmlspecialchars(
                $message
            ) ?>

        </div>

    <?php } ?>



    <!-- =====================================
         ENTER MARKS FORM
    ===================================== -->

    <div class="card-box">


        <h5 class="mb-4">

            <i
                class="fa-solid fa-square-plus text-primary">
            </i>

            Enter Student Marks

        </h5>


        <form
            method="POST"
            action=""
        >


            <div class="row g-3">


                <!-- COURSE -->

                <div class="col-md-6">

                    <label
                        class="form-label"
                    >

                        Select Course

                    </label>


                    <select
                        name="course_id"
                        id="course_id"
                        class="form-select"
                        required
                        onchange="loadExams()"
                    >

                        <option value="">

                            Select Course

                        </option>


                        <?php

                        if ($courses) {

                            while (
                                $course =
                                mysqli_fetch_assoc(
                                    $courses
                                )
                            ) {

                        ?>

                            <option
                                value="<?= (int)
                                    $course['course_id'] ?>"

                                <?= (
                                    $selected_course ==
                                    $course['course_id']
                                )
                                ? 'selected'
                                : '' ?>
                            >

                                <?= htmlspecialchars(
                                    $course['course_code']
                                ) ?>

                                -

                                <?= htmlspecialchars(
                                    $course['course_name']
                                ) ?>

                            </option>


                        <?php

                            }

                        }

                        ?>

                    </select>

                </div>



                <!-- EXAM -->

                <div class="col-md-6">

                    <label
                        class="form-label"
                    >

                        Exam Name

                    </label>


                    <select
                        name="exam_id"
                        class="form-select"
                        required
                    >

                        <option value="">

                            Select Exam

                        </option>


                        <?php foreach (
                            $exams as $exam
                        ) { ?>

                            <option
                                value="<?= (int)
                                    $exam['exam_id'] ?>"
                            >

                                <?= htmlspecialchars(
                                    $exam['exam_name']
                                ) ?>

                                —

                                <?= htmlspecialchars(
                                    $exam['total_marks']
                                ) ?>

                                Marks

                            </option>

                        <?php } ?>


                    </select>

                </div>



                <!-- STUDENT ROLL -->

                <div class="col-md-6">

                    <label
                        class="form-label"
                    >

                        Student Registration / Roll Number

                    </label>


                    <input
                        type="text"
                        name="student_roll"
                        class="form-control"
                        placeholder="Enter Student Registration / Roll Number"
                        required
                    >

                </div>



                <!-- MARKS -->

                <div class="col-md-6">

                    <label
                        class="form-label"
                    >

                        Enter Marks

                    </label>


                    <input
                        type="number"
                        name="marks"
                        class="form-control"
                        placeholder="Enter Marks"
                        min="0"
                        step="0.01"
                        required
                    >

                </div>



                <!-- BUTTONS -->

                <div class="col-12">


                    <button
                        type="submit"
                        name="save_marks"
                        class="btn btn-primary px-4"
                    >

                        <i
                            class="fa-solid fa-floppy-disk">
                        </i>

                        Save Marks

                    </button>


                    <a
                        href="dashboard.php"
                        class="btn btn-secondary"
                    >

                        <i
                            class="fa-solid fa-arrow-left">
                        </i>

                        Back to Dashboard

                    </a>


                </div>


            </div>


        </form>


    </div>



    <!-- =====================================
         RECENT MARKS
    ===================================== -->

    <div class="card-box">


        <div
            class="d-flex justify-content-between
                   align-items-center mb-3"
        >


            <h5 class="mb-0">

                <i
                    class="fa-solid fa-table">
                </i>

                Recently Entered Marks

            </h5>


        </div>



        <div class="table-responsive">


            <table
                class="table table-bordered table-hover"
            >


                <thead>

                    <tr>

                        <th>#</th>

                        <th>Registration</th>

                        <th>Student Name</th>

                        <th>Course</th>

                        <th>Exam</th>

                        <th>Marks</th>

                        <th>Grade</th>

                        <th>GPA</th>

                    </tr>

                </thead>


                <tbody>


                <?php

                $count = 1;


                if (
                    $recent_marks &&
                    mysqli_num_rows(
                        $recent_marks
                    ) > 0
                ) {


                    while (
                        $row =
                        mysqli_fetch_assoc(
                            $recent_marks
                        )
                    ) {

                ?>

                    <tr>


                        <td>

                            <?= $count++ ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['student_roll']
                            ) ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['full_name']
                            ) ?>

                        </td>


                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $row['course_code']
                                ) ?>

                            </strong>

                            <br>

                            <small>

                                <?= htmlspecialchars(
                                    $row['course_name']
                                ) ?>

                            </small>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['exam_name']
                            ) ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['marks']
                            ) ?>

                        </td>


                        <td>

                            <span
                                class="badge bg-primary"
                            >

                                <?= htmlspecialchars(
                                    $row['grade']
                                ) ?>

                            </span>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['gpa']
                            ) ?>

                        </td>


                    </tr>


                <?php

                    }

                } else {

                ?>


                    <tr>

                        <td
                            colspan="8"
                            class="text-center text-muted py-4"
                        >

                            No marks entered yet.

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



<!-- Bootstrap JS -->

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>



<!-- =====================================
     COURSE CHANGE
===================================== -->

<script>

function loadExams()
{

    const course =
        document.getElementById(
            "course_id"
        ).value;


    if (course !== "") {

        window.location.href =
            "marks.php?course_id="
            + encodeURIComponent(course);

    }

}

</script>


</body>

</html>