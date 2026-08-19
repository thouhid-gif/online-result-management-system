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


$teacher_id = (int) $_SESSION['teacher_id'];

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
// SELECTED COURSE
// =====================================

$course_id = isset($_GET['course_id'])
    ? (int) $_GET['course_id']
    : 0;


// =====================================
// GET TEACHER'S ASSIGNED COURSES
// =====================================

$courses = [];

$course_sql = "
    SELECT
        c.course_id,
        c.course_code,
        c.course_name,
        c.credit
    FROM teacher_courses tc
    INNER JOIN courses c
        ON tc.course_id = c.course_id
    WHERE tc.teacher_id = ?
    ORDER BY c.course_code ASC
";


$stmt_courses =
    mysqli_prepare($conn, $course_sql);

if ($stmt_courses) {

    mysqli_stmt_bind_param(
        $stmt_courses,
        "i",
        $teacher_id
    );

    mysqli_stmt_execute($stmt_courses);

    $course_result =
        mysqli_stmt_get_result($stmt_courses);

    if ($course_result) {

        while (
            $row =
            mysqli_fetch_assoc($course_result)
        ) {

            $courses[] = $row;
        }
    }

    mysqli_stmt_close($stmt_courses);
}


// =====================================
// SELECT FIRST COURSE
// =====================================

if (
    $course_id <= 0 &&
    !empty($courses)
) {

    $course_id =
        (int) $courses[0]['course_id'];
}


// =====================================
// VERIFY SELECTED COURSE
// =====================================

$selected_course = null;

foreach ($courses as $course) {

    if (
        (int) $course['course_id'] ===
        $course_id
    ) {

        $selected_course = $course;

        break;
    }
}


// =====================================
// GET STUDENTS
// =====================================

$students = [];

if ($selected_course) {

    $student_sql = "
        SELECT DISTINCT
            s.student_id,
            s.student_roll,
            s.full_name,
            s.email,
            s.phone,
            s.department_id,
            s.semester_id
        FROM student_courses AS sc
        INNER JOIN students AS s
            ON sc.student_id = s.student_id
        WHERE sc.course_id = ?
        ORDER BY s.student_roll ASC
    ";


    $stmt_students =
        mysqli_prepare(
            $conn,
            $student_sql
        );


    if ($stmt_students) {

        mysqli_stmt_bind_param(
            $stmt_students,
            "i",
            $course_id
        );

        mysqli_stmt_execute(
            $stmt_students
        );

        $student_result =
            mysqli_stmt_get_result(
                $stmt_students
            );


        if ($student_result) {

            while (
                $row =
                mysqli_fetch_assoc(
                    $student_result
                )
            ) {

                $students[] = $row;
            }
        }


        mysqli_stmt_close(
            $stmt_students
        );
    }
}


$total_students =
    count($students);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>
    Students | Teacher Panel
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


.subtitle {

    color: #6c757d;

    margin-top: 5px;

}


.student-count {

    background: #e7f1ff;

    color: #0d6efd;

    padding: 9px 15px;

    border-radius: 20px;

    font-weight: bold;

}


/* =====================================
   COURSE SELECT
===================================== */

.course-select-card {

    background: white;

    border-radius: 14px;

    padding: 20px;

    margin-bottom: 20px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,.08);

}


.course-title {

    font-size: 20px;

    font-weight: bold;

}


.course-code {

    color: #0d6efd;

    font-weight: bold;

}


/* =====================================
   SEARCH
===================================== */

.search-card {

    background: white;

    padding: 15px;

    border-radius: 12px;

    margin-bottom: 18px;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,.06);

}


/* =====================================
   TABLE CARD
===================================== */

.table-card {

    background: white;

    border-radius: 14px;

    padding: 20px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,.08);

    overflow-x: auto;

}


/* =====================================
   TABLE
===================================== */

.student-table {

    width: 100%;

    border-collapse: collapse;

}


.student-table th {

    background: #0d6efd;

    color: white;

    padding: 13px;

    white-space: nowrap;

}


.student-table td {

    padding: 12px;

    border-bottom:
        1px solid
        #e9ecef;

    white-space: nowrap;

}


.student-table tbody tr:hover {

    background: #f8fafc;

}


.student-id {

    color: #0d6efd;

    font-weight: bold;

}


/* =====================================
   RESULT BUTTON
===================================== */

.result-btn {

    white-space: nowrap;

    border-radius: 8px;

    padding: 7px 12px;

    font-weight: 600;

}


/* =====================================
   EMPTY BOX
===================================== */

.empty-box {

    text-align: center;

    padding: 60px 20px;

}


.empty-box i {

    font-size: 55px;

    color: #adb5bd;

    margin-bottom: 15px;

}


.empty-box h4 {

    font-weight: bold;

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

        gap: 12px;

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

            <?= htmlspecialchars(
                $teacher_name
            ) ?>

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


        <a href="courses.php">

            <i class="fa-solid fa-book"></i>

            <span>My Courses</span>

        </a>


        <a
            href="students.php"
            class="active">

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

        <div class="d-flex align-items-center gap-3">

            <a href="dashboard.php"
               class="btn btn-outline-primary">

                <i class="fa-solid fa-arrow-left"></i>
                Back

            </a>

            <div>

                <h3>
                    <i class="fa-solid fa-users text-primary"></i>
                    Course Students
                </h3>

                <div class="subtitle">
                    View students and add their course result
                </div>

            </div>

        </div>

    </div>


    <div class="student-count">

        <i class="fa-solid fa-user-graduate"></i>

        <?= $total_students ?>

        <?= ($total_students == 1)
            ? 'Student'
            : 'Students'
        ?>

    </div>

</div>


    <!-- =====================================
         COURSE SELECT
    ===================================== -->

    <?php if (!empty($courses)) { ?>


        <div class="course-select-card">


            <form method="GET">


                <div class="row g-3 align-items-end">


                    <div class="col-md-9">


                        <label
                            class="form-label fw-bold">

                            Select Course

                        </label>


                        <select
                            name="course_id"
                            class="form-select"
                            required
                        >


                            <option value="">

                                Select Course

                            </option>


                            <?php foreach (
                                $courses as $course
                            ) { ?>


                                <option
                                    value="<?= (int)$course['course_id'] ?>"

                                    <?= (
                                        $course_id ==
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


                                    (<?= htmlspecialchars(
                                        $course['credit']
                                    ) ?> Credit)


                                </option>


                            <?php } ?>


                        </select>


                    </div>


                    <div class="col-md-3">


                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i class="fa-solid fa-filter"></i>

                            View Students

                        </button>


                    </div>


                </div>


            </form>


        </div>



        <!-- SELECTED COURSE -->

        <?php if ($selected_course) { ?>


            <div class="course-select-card">


                <div class="course-code">

                    <?= htmlspecialchars(
                        $selected_course['course_code']
                    ) ?>

                </div>


                <div class="course-title">

                    <?= htmlspecialchars(
                        $selected_course['course_name']
                    ) ?>

                </div>


                <div class="text-muted mt-1">

                    <?= htmlspecialchars(
                        $selected_course['credit']
                    ) ?>

                    Credit

                </div>


            </div>


            <!-- SEARCH -->

            <?php if ($total_students > 0) { ?>


                <div class="search-card">


                    <div class="input-group">


                        <span
                            class="input-group-text">

                            <i
                                class="fa-solid fa-magnifying-glass">
                            </i>

                        </span>


                        <input
                            type="text"
                            id="studentSearch"
                            class="form-control"
                            placeholder="Search Student ID, Name, Department..."
                        >


                    </div>


                </div>



                <!-- STUDENT TABLE -->

                <div class="table-card">


                    <table
                        class="student-table"
                        id="studentTable"
                    >


                        <thead>


                            <tr>

                                <th>#</th>

                                <th>Student ID</th>

                                <th>Name</th>

                                <th>Email</th>

                                <th>Department</th>

                                <th>Semester</th>

                                <th>Result</th>

                            </tr>


                        </thead>


                        <tbody>


                            <?php

                            $serial = 1;

                            foreach (
                                $students as $student
                            ) {

                            ?>


                                <tr>


                                    <td>

                                        <?= $serial++ ?>

                                    </td>


                                    <td class="student-id">

                                        <?= htmlspecialchars(
                                            $student['student_id']
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $student['full_name']
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $student['email']
                                            ?? 'N/A'
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $student['department_id']
                                            ?? 'N/A'
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $student['semester_id']
                                            ?? 'N/A'
                                        ) ?>

                                    </td>


                                    <td>


                                        <a
                                            href="marks.php?student_id=<?= urlencode(
                                                $student['student_id']
                                            ) ?>&course_id=<?= (int)$course_id ?>"

                                            class="btn btn-success btn-sm result-btn"
                                        >

                                            <i
                                                class="fa-solid fa-pen-to-square">
                                            </i>

                                            Add Result

                                        </a>


                                    </td>


                                </tr>


                            <?php } ?>


                        </tbody>


                    </table>


                </div>


            <?php } else { ?>


                <!-- NO STUDENTS -->

                <div class="table-card">


                    <div class="empty-box">


                        <i
                            class="fa-solid fa-user-group">
                        </i>


                        <h4>

                            No Students Assigned

                        </h4>


                        <p class="text-muted">

                            No students are currently
                            assigned to this course.

                        </p>


                    </div>


                </div>


            <?php } ?>


        <?php } else { ?>


            <!-- INVALID COURSE -->

            <div class="table-card">


                <div class="empty-box">


                    <i
                        class="fa-solid fa-circle-exclamation">
                    </i>


                    <h4>

                        Invalid Course

                    </h4>


                    <p class="text-muted">

                        This course is not assigned
                        to your account.

                    </p>


                </div>


            </div>


        <?php } ?>


    <?php } else { ?>


        <!-- NO COURSE -->

        <div class="table-card">


            <div class="empty-box">


                <i
                    class="fa-solid fa-book-open">
                </i>


                <h4>

                    No Course Assigned

                </h4>


                <p class="text-muted">

                    No course has been assigned
                    to you yet.

                </p>


            </div>


        </div>


    <?php } ?>


</div>



<!-- =====================================
     JAVASCRIPT
===================================== -->

<script>

const searchInput =
    document.getElementById(
        "studentSearch"
    );


if (searchInput) {

    searchInput.addEventListener(
        "keyup",
        function () {

            const value =
                this.value.toLowerCase();


            const rows =
                document.querySelectorAll(
                    "#studentTable tbody tr"
                );


            rows.forEach(function(row) {

                row.style.display =
                    row.innerText
                        .toLowerCase()
                        .includes(value)
                        ? ""
                        : "none";

            });

        }
    );

}

</script>


</body>

</html>