<?php

session_start();

include '../config/database.php';
include '../config/session.php';

checkStudent();


/* ==================================================
   STUDENT ID
================================================== */

$student_id = $_SESSION['user_id'];


/* ==================================================
   GET STUDENT INFORMATION
================================================== */

$student_query = mysqli_query($conn, "

    SELECT
        s.student_id,
        s.full_name,
        s.department_id,
        s.semester_id,

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


if (!$student_query || mysqli_num_rows($student_query) == 0) {

    session_destroy();

    header("Location: ../login.php");

    exit();

}


$student = mysqli_fetch_assoc($student_query);


/* ==================================================
   GRADE FUNCTION
================================================== */

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


/* ==================================================
   GRADE POINT FUNCTION
================================================== */

function getGradePoint($marks)
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


/* ==================================================
   GRADE BADGE CLASS
================================================== */

function gradeClass($grade)
{

    if ($grade == "F") {

        return "danger";

    } elseif (
        $grade == "A+" ||
        $grade == "A" ||
        $grade == "A-"
    ) {

        return "success";

    } else {

        return "primary";
    }
}


/* ==================================================
   GET PUBLISHED RESULTS
================================================== */

$result_sql = "

    SELECT

        r.result_id,

        r.exam_id,

        r.department_id,

        r.semester_id,

        r.publish_date,

        r.status,

        r.student_id,

        r.marks,

        e.course_id,

        c.course_name

    FROM results r

    LEFT JOIN exams e
        ON r.exam_id = e.exam_id

    LEFT JOIN courses c
        ON e.course_id = c.course_id

    WHERE r.student_id = '$student_id'

    AND r.department_id =
        '{$student['department_id']}'

    AND r.semester_id =
        '{$student['semester_id']}'

    AND r.status = 'Published'

    ORDER BY
        r.publish_date DESC,
        r.result_id DESC
";


$result_query = mysqli_query(
    $conn,
    $result_sql
);


/* ==================================================
   STATISTICS
================================================== */

$total_courses = 0;

$total_marks = 0;

$total_grade_points = 0;

$cgpa = 0;


/* ==================================================
   STORE RESULT DATA
================================================== */

$result_data = [];


if ($result_query) {

    while (
        $row = mysqli_fetch_assoc(
            $result_query
        )
    ) {

        $marks = (float)$row['marks'];

        $grade = getGrade($marks);

        $grade_point =
            getGradePoint($marks);


        $total_courses++;

        $total_marks += $marks;

        $total_grade_points +=
            $grade_point;


        $row['calculated_grade'] =
            $grade;

        $row['calculated_grade_point'] =
            $grade_point;


        $result_data[] = $row;
    }
}


/* ==================================================
   CALCULATE CGPA
================================================== */

if ($total_courses > 0) {

    $cgpa =
        $total_grade_points /
        $total_courses;

}


/* ==================================================
   MERIT POSITION
================================================== */

$merit_position = null;
$total_students_ranked = 0;

$dept_id = (int)$student['department_id'];
$current_semester_id = (int)$student['semester_id'];

$final_result_sql = "
    SELECT student_id, total_marks
    FROM final_results
    WHERE department_id = '$dept_id'
      AND semester_id = '$current_semester_id'
      AND status = 'Published'
    ORDER BY total_marks DESC, student_id ASC
";

$final_result_query = mysqli_query($conn, $final_result_sql);

if ($final_result_query) {
    $position = 0;
    while ($rank_row = mysqli_fetch_assoc($final_result_query)) {
        $position++;
        $total_students_ranked++;
        if ((string)$rank_row['student_id'] === (string)$student_id) {
            $merit_position = $position;
        }
    }
}

/* ==================================================
   RESULT STATUS
================================================== */

$has_result =
    count($result_data) > 0;

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
    View Result | Student Panel
</title>


<!-- ==================================================
     BOOTSTRAP
================================================== -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<!-- ==================================================
     FONT AWESOME
================================================== -->

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

    margin:
        0 15px 10px;

}


.sidebar a {

    display: block;

    padding:
        15px 20px;

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

    padding:
        20px 25px;

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


/* ==================================================
   CARD
================================================== */

.card {

    border: none;

    border-radius: 15px;

    box-shadow:
        0 5px 15px
        rgba(0, 0, 0, 0.10);

}


/* ==================================================
   STUDENT INFORMATION
================================================== */

.info-label {

    color: #6c757d;

    font-size: 14px;

    margin-bottom: 5px;

}


.info-value {

    font-weight: 600;

    font-size: 16px;

}


/* ==================================================
   STAT CARD
================================================== */

.stat-card {

    border-radius: 15px;

    min-height: 135px;

}


.stat-icon {

    font-size: 32px;

    margin-bottom: 8px;

}


/* ==================================================
   RESULT HEADER
================================================== */

.result-header {

    background: #0d6efd;

    color: white;

    padding: 18px;

    border-radius:
        15px 15px 0 0;

}


/* ==================================================
   RESULT TABLE
================================================== */

.result-table th {

    background: #f1f5f9;

    white-space: nowrap;

}


.result-table td {

    vertical-align: middle;

}


/* ==================================================
   CGPA BOX
================================================== */

.cgpa-box {

    background: #198754;

    color: white;

    border-radius: 15px;

    padding: 25px;

    text-align: center;

}


.cgpa-value {

    font-size: 42px;

    font-weight: bold;

}


/* ==================================================
   NO RESULT
================================================== */

.no-result {

    padding: 60px 20px;

    text-align: center;

}


.no-result i {

    font-size: 60px;

    color: #adb5bd;

    margin-bottom: 20px;

}


/* ==================================================
   PRINT
================================================== */

@media print {

    .sidebar,
    .topbar,
    .no-print {

        display: none !important;

    }


    .content {

        margin-left: 0;

        padding: 0;

    }


    body {

        background: white;

    }

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

    <a href="dashboard.php">

        <i class="fa fa-home"></i>

        Dashboard

    </a>


    <!-- Profile -->

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

    <a
        href="result.php"
        class="active"
    >

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

        <div
            class="d-flex justify-content-between
            align-items-center"
        >

            <div>

                <h3>

                    <i
                        class="fa fa-graduation-cap
                        text-primary"
                    ></i>

                    My Result

                </h3>

                <small class="text-muted">

                    View your published examination results

                </small>

            </div>


            <?php if ($has_result) { ?>

                <button
                    onclick="window.print()"
                    class="btn btn-primary no-print"
                >

                    <i class="fa fa-print"></i>

                    Print Result

                </button>

            <?php } ?>

        </div>

    </div>



    <!-- ==================================================
         STUDENT INFORMATION
    ================================================== -->

    <div class="card mb-4">


        <div class="card-header bg-dark text-white">

            <h5 class="mb-0">

                <i class="fa fa-user"></i>

                Student Information

            </h5>

        </div>


        <div class="card-body">


            <div class="row">


                <!-- Student ID -->

                <div class="col-md-3 mb-3">

                    <div class="info-label">

                        Student ID

                    </div>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $student['student_id']
                        );

                        ?>

                    </div>

                </div>


                <!-- Name -->

                <div class="col-md-3 mb-3">

                    <div class="info-label">

                        Name

                    </div>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $student['full_name']
                        );

                        ?>

                    </div>

                </div>


                <!-- Department -->

                <div class="col-md-3 mb-3">

                    <div class="info-label">

                        Department

                    </div>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $student['department_name']
                        );

                        ?>

                    </div>

                </div>


                <!-- Semester -->

                <div class="col-md-3 mb-3">

                    <div class="info-label">

                        Semester

                    </div>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $student['semester_name']
                        );

                        ?>

                    </div>

                </div>


            </div>

        </div>

    </div>



    <?php if ($has_result) { ?>


        <!-- ==================================================
             STATISTICS
        ================================================== -->

        <div class="row mb-4">


            <!-- Total Courses -->

            <div class="col-md-3 mb-3">

                <div
                    class="card stat-card
                    bg-primary text-white"
                >

                    <div class="card-body text-center">

                        <div class="stat-icon">

                            <i class="fa fa-book-open"></i>

                        </div>

                        <h6>

                            Published Courses

                        </h6>

                        <h2>

                            <?php

                            echo $total_courses;

                            ?>

                        </h2>

                    </div>

                </div>

            </div>



            <!-- Total Marks -->

            <div class="col-md-4 mb-3">

                <div
                    class="card stat-card
                    bg-warning text-dark"
                >

                    <div class="card-body text-center">

                        <div class="stat-icon">

                            <i class="fa fa-chart-line"></i>

                        </div>

                        <h6>

                            Total Marks

                        </h6>

                        <h2>

                            <?php

                            echo number_format(
                                $total_marks,
                                2
                            );

                            ?>

                        </h2>

                    </div>

                </div>

            </div>



            <!-- CGPA -->

            <div class="col-md-4 mb-3">

                <div
                    class="card stat-card
                    bg-success text-white"
                >

                    <div class="card-body text-center">

                        <div class="stat-icon">

                            <i
                                class="fa fa-graduation-cap"
                            ></i>

                        </div>

                        <h6>

                            CGPA

                        </h6>

                        <h2>

                            <?php

                            echo number_format(
                                $cgpa,
                                2
                            );

                            ?>

                        </h2>

                    </div>

                </div>

            </div>


            <!-- Merit Position -->
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-danger text-white">
                    <div class="card-body text-center">
                        <div class="stat-icon"><i class="fa fa-trophy"></i></div>
                        <h6>Merit Position</h6>
                        <h2><?php echo $merit_position !== null ? $merit_position : 'N/A'; ?></h2>
                        <?php if ($total_students_ranked > 0) { ?>
                            <small>Out of <?php echo $total_students_ranked; ?> Students</small>
                        <?php } ?>
                    </div>
                </div>
            </div>

        </div>



        <!-- ==================================================
             RESULT TABLE
        ================================================== -->

        <div class="card mb-4">


            <div class="result-header">

                <h5 class="mb-0">

                    <i class="fa fa-table"></i>

                    Course-wise Result

                </h5>

            </div>


            <div class="card-body">


                <div class="table-responsive">


                    <table
                        class="table table-bordered
                        table-hover result-table"
                    >


                        <thead>

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    Course
                                </th>

                                <th>
                                    Exam ID
                                </th>

                                <th>
                                    Marks
                                </th>

                                <th>
                                    Grade
                                </th>

                                <th>
                                    Grade Point
                                </th>

                                <th>
                                    Publish Date
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php

                        $sl = 1;


                        foreach (
                            $result_data
                            as $result
                        ) {


                            $marks =
                                (float)$result['marks'];


                            $grade =
                                $result[
                                    'calculated_grade'
                                ];


                            $grade_point =
                                $result[
                                    'calculated_grade_point'
                                ];


                            $grade_class =
                                gradeClass(
                                    $grade
                                );

                        ?>


                            <tr>


                                <!-- Serial -->

                                <td>

                                    <?php

                                    echo $sl++;

                                    ?>

                                </td>


                                <!-- Course -->

                                <td>

                                    <?php

                                    if (
                                        !empty(
                                            $result[
                                                'course_name'
                                            ]
                                        )
                                    ) {

                                        echo htmlspecialchars(
                                            $result[
                                                'course_name'
                                            ]
                                        );

                                    } else {

                                        echo "Course Not Found";

                                    }

                                    ?>

                                </td>


                                <!-- Exam ID -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $result[
                                            'exam_id'
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- Marks -->

                                <td>

                                    <strong>

                                        <?php

                                        echo number_format(
                                            $marks,
                                            2
                                        );

                                        ?>

                                    </strong>

                                    / 100

                                </td>


                                <!-- Grade -->

                                <td>

                                    <span
                                        class="badge
                                        bg-<?php
                                            echo $grade_class;
                                        ?> fs-6"
                                    >

                                        <?php

                                        echo $grade;

                                        ?>

                                    </span>

                                </td>


                                <!-- Grade Point -->

                                <td>

                                    <strong>

                                        <?php

                                        echo number_format(
                                            $grade_point,
                                            2
                                        );

                                        ?>

                                    </strong>

                                </td>


                                <!-- Publish Date -->

                                <td>

                                    <?php

                                    if (
                                        !empty(
                                            $result[
                                                'publish_date'
                                            ]
                                        )
                                    ) {

                                        echo date(
                                            'd M Y',
                                            strtotime(
                                                $result[
                                                    'publish_date'
                                                ]
                                            )
                                        );

                                    } else {

                                        echo "N/A";

                                    }

                                    ?>

                                </td>


                                <!-- Status -->

                                <td>

                                    <span
                                        class="badge bg-success"
                                    >

                                        <i
                                            class="fa fa-check"
                                        ></i>

                                        Published

                                    </span>

                                </td>


                            </tr>


                        <?php

                        }

                        ?>


                        </tbody>


                        <!-- ==================================================
                             RESULT SUMMARY
                        ================================================== -->

                        <tfoot>

                            <tr
                                class="table-light fw-bold"
                            >

                                <td
                                    colspan="3"
                                    class="text-end"
                                >

                                    Total Marks

                                </td>


                                <td>

                                    <?php

                                    echo number_format(
                                        $total_marks,
                                        2
                                    );

                                    ?>

                                </td>


                                <td colspan="3">

                                </td>


                                <td>

                                    <?php

                                    echo $total_courses;

                                    ?>

                                    Courses

                                </td>

                            </tr>


                            <tr class="table-warning fw-bold">
                                <td colspan="5" class="text-end">Merit Position</td>
                                <td><?php echo $merit_position !== null ? $merit_position : 'N/A'; ?></td>
                                <td colspan="2"><?php echo $total_students_ranked > 0 ? 'Out of ' . $total_students_ranked . ' Students' : 'Ranking not available'; ?></td>
                            </tr>

                            <tr
                                class="table-success
                                fw-bold"
                            >

                                <td
                                    colspan="5"
                                    class="text-end"
                                >

                                    CGPA

                                </td>


                                <td>

                                    <?php

                                    echo number_format(
                                        $cgpa,
                                        2
                                    );

                                    ?>

                                </td>


                                <td colspan="2">

                                    Overall CGPA

                                </td>

                            </tr>

                        </tfoot>


                    </table>

                </div>

            </div>

        </div>



        <!-- ==================================================
             CGPA SUMMARY
        ================================================== -->

        <div class="row">


            <div class="col-md-6 mx-auto">


                <div class="cgpa-box">


                    <div>

                        <i
                            class="fa fa-graduation-cap fa-2x"
                        ></i>

                    </div>


                    <h5 class="mt-2">

                        Overall CGPA

                    </h5>


                    <div class="cgpa-value">

                        <?php

                        echo number_format(
                            $cgpa,
                            2
                        );

                        ?>

                    </div>


                    <small>

                        Based on all published courses

                    </small>


                </div>

            </div>

        </div>


    <?php } else { ?>


        <!-- ==================================================
             NO RESULT
        ================================================== -->

        <div class="card">


            <div class="card-body no-result">


                <i
                    class="fa fa-file-circle-xmark"
                ></i>


                <h4>

                    No Result Published

                </h4>


                <p class="text-muted">

                    Your result has not been published yet.

                </p>


                <p class="text-muted">

                    Please check again after the
                    administrator publishes your result.

                </p>


                <a
                    href="dashboard.php"
                    class="btn btn-primary"
                >

                    <i class="fa fa-arrow-left"></i>

                    Back to Dashboard

                </a>


            </div>

        </div>


    <?php } ?>


    <!-- ==================================================
         FOOTER
    ================================================== -->

    <div
        class="text-center text-muted mt-4 no-print"
    >

        <small>

            <i class="fa fa-shield-halved"></i>

            Only published results are displayed.

        </small>

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