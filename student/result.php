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

function getOrdinal($number)
{
    if ($number === null) {
        return "N/A";
    }

    $number = (int)$number;
    $suffix = "th";

    if ($number % 100 < 11 || $number % 100 > 13) {
        switch ($number % 10) {
            case 1: $suffix = "st"; break;
            case 2: $suffix = "nd"; break;
            case 3: $suffix = "rd"; break;
        }
    }

    return $number . $suffix;
}

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

        c.course_name,
        c.credit

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

$total_credits = 0;

$passed_courses = 0;

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

        $credit = (float)($row['credit'] ?? 0);


        $total_courses++;

        $total_marks += $marks;

        // Only passed courses are included in CGPA calculation.
        // CGPA is weighted by each course's credit.
        if ($grade !== "F" && $credit > 0) {
            $total_grade_points += ($grade_point * $credit);
            $total_credits += $credit;
            $passed_courses++;
        }


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

if ($total_credits > 0) {

    $cgpa =
        $total_grade_points /
        $total_credits;

}


/* ==================================================
   MERIT POSITION - SAME DEPARTMENT OVERALL CGPA
================================================== */

$merit_position = null;
$total_students_ranked = 0;

$dept_id = (int)$student['department_id'];

/*
   All published results from every semester of the same
   department are used to calculate each student's Overall CGPA.
   Students are then ranked from highest CGPA to lowest CGPA.
*/
$department_merit_sql = "
    SELECT
        r.student_id,
        ROUND(
            SUM(
                CASE
                    WHEN r.marks >= 80 THEN 4.00 * c.credit
                    WHEN r.marks >= 75 THEN 3.75 * c.credit
                    WHEN r.marks >= 70 THEN 3.50 * c.credit
                    WHEN r.marks >= 65 THEN 3.25 * c.credit
                    WHEN r.marks >= 60 THEN 3.00 * c.credit
                    WHEN r.marks >= 55 THEN 2.75 * c.credit
                    WHEN r.marks >= 50 THEN 2.50 * c.credit
                    WHEN r.marks >= 45 THEN 2.25 * c.credit
                    WHEN r.marks >= 40 THEN 2.00 * c.credit
                    ELSE 0
                END
            ) / NULLIF(
                SUM(
                    CASE
                        WHEN r.marks >= 40 THEN c.credit
                        ELSE 0
                    END
                ), 0
            ), 2
        ) AS overall_cgpa
    FROM results r
    LEFT JOIN exams e
        ON r.exam_id = e.exam_id
    LEFT JOIN courses c
        ON e.course_id = c.course_id
    WHERE r.department_id = '$dept_id'
      AND r.status = 'Published'
    GROUP BY r.student_id
    ORDER BY overall_cgpa DESC, r.student_id ASC
";

$department_merit_query = mysqli_query($conn, $department_merit_sql);

if ($department_merit_query) {

    $rank = 0;
    $previous_cgpa = null;

    while ($rank_row = mysqli_fetch_assoc($department_merit_query)) {

        $total_students_ranked++;

        $rank_cgpa = (float)$rank_row['overall_cgpa'];

        /* Same CGPA = same merit position */
        if ($previous_cgpa === null || $rank_cgpa < $previous_cgpa) {
            $rank = $total_students_ranked;
        }

        if ((string)$rank_row['student_id'] === (string)$student_id) {
            $merit_position = $rank;
        }

        $previous_cgpa = $rank_cgpa;
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
   PROFESSIONAL PRINT / PDF DESIGN
================================================== */
.print-header { display: none; }
.print-footer { display: none; }

@media print {
    @page { size: A4; margin: 10mm; }

    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    body {
        background: #ffffff !important;
        color: #172033 !important;
        font-size: 11px;
    }

    .sidebar, .topbar, .no-print { display: none !important; }
    .content { margin: 0 !important; padding: 0 !important; width: 100% !important; }

    .print-header {
        display: block !important;
        border: 1px solid #d8e2f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 12px;
        page-break-inside: avoid;
    }

    .print-title {
        background: linear-gradient(135deg, #12355b, #1d6fb8, #6d3fc5) !important;
        color: #ffffff !important;
        display: flex; align-items: center; gap: 14px;
        padding: 15px 18px;
    }
    .print-logo {
        width: 46px; height: 46px; border-radius: 50%;
        background: rgba(255,255,255,.18) !important;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px; border: 1px solid rgba(255,255,255,.45);
    }
    .print-title h1 { margin: 0; font-size: 18px; font-weight: 800; letter-spacing: .4px; }
    .print-title p { margin: 3px 0 0; font-size: 10px; opacity: .92; }

    .print-student-summary {
        display: grid !important; grid-template-columns: repeat(4, 1fr);
        background: #f4f8fd !important; padding: 10px 14px; gap: 8px;
    }
    .print-student-summary div { border-right: 1px solid #d8e2f0; padding: 0 10px; }
    .print-student-summary div:last-child { border-right: 0; }
    .print-student-summary span { display:block; color:#667085; font-size:9px; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px; }
    .print-student-summary strong { display:block; font-size:11px; color:#172033; }

    .content > .card.mb-4:first-of-type { display: none !important; }
    .card, .result-header, .cgpa-box { box-shadow: none !important; }
    .card { border: 1px solid #d8e2f0 !important; border-radius: 10px !important; overflow: hidden; margin-bottom: 10px !important; }
    .result-header {
        background: linear-gradient(90deg, #0f6ea9, #2767d9, #7444c7) !important;
        padding: 10px 14px !important; border-radius: 0 !important;
    }
    .result-header h5 { font-size: 13px !important; font-weight: 700 !important; }
    .card-body { padding: 0 !important; }

    .table-responsive { overflow: visible !important; }
    .result-table { margin: 0 !important; width: 100% !important; border-collapse: collapse !important; }
    .result-table th {
        background: #23355d !important; color: #ffffff !important;
        padding: 8px 9px !important; border: 1px solid #c8d4e3 !important;
        font-size: 10px; text-transform: uppercase; letter-spacing: .3px;
    }
    .result-table td { padding: 8px 9px !important; border: 1px solid #d6dee9 !important; }
    .result-table tbody tr:nth-child(even) td { background: #f7faff !important; }
    .result-table tfoot tr:nth-child(1) td { background: #eef4fb !important; }
    .result-table tfoot tr:nth-child(2) td { background: #fff4d6 !important; }
    .result-table tfoot tr:nth-child(3) td { background: #e2f5e9 !important; }
    .badge { border-radius: 12px !important; padding: 4px 8px !important; }

    .cgpa-box {
        display: flex !important; align-items: center; justify-content: center; gap: 14px;
        background: linear-gradient(135deg, #147a52, #2fae7d) !important;
        border-radius: 10px !important; padding: 14px 20px !important;
        color: #fff !important; page-break-inside: avoid;
    }
    .cgpa-box h5 { margin: 0 !important; font-size: 13px !important; }
    .cgpa-box .cgpa-value { font-size: 28px !important; line-height: 1 !important; }
    .cgpa-box small { font-size: 9px !important; }
    .row, .col-md-6 { margin: 0 !important; width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; }

    .print-footer {
        display: flex !important; justify-content: space-between; align-items: center;
        border-top: 1px solid #cfd8e6; margin-top: 14px; padding-top: 7px;
        color: #667085; font-size: 9px;
    }
}

/* ==================================================
   PRINT
================================================== */



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

    <!-- PRINT ONLY PROFESSIONAL HEADER -->
    <div class="print-header">
        <div class="print-title">
            <div class="print-logo"><i class="fa fa-graduation-cap"></i></div>
            <div>
                <h1>STUDENT RESULT MANAGEMENT SYSTEM</h1>
                <p>Official Academic Result &amp; Performance Report</p>
            </div>
        </div>
        <div class="print-student-summary">
            <div><span>Student Name</span><strong><?php echo htmlspecialchars($student['full_name']); ?></strong></div>
            <div><span>Student ID</span><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></div>
            <div><span>Department</span><strong><?php echo htmlspecialchars($student['department_name']); ?></strong></div>
            <div><span>Semester</span><strong><?php echo htmlspecialchars($student['semester_name']); ?></strong></div>
        </div>
    </div>


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
                                <th>Course</th>
                                <th>Credit</th>
                                <th>Marks</th>
                                <th>Grade</th>
                                <th>Grade Point</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($result_data as $result) {
                            $marks = (float)$result['marks'];
                            $grade = $result['calculated_grade'];
                            $grade_point = $result['calculated_grade_point'];
                            $grade_class = gradeClass($grade);
                        ?>
                            <tr>
                                <!-- Course -->
                                <td>
                                    <?php
                                    echo !empty($result['course_name'])
                                        ? htmlspecialchars($result['course_name'])
                                        : 'Course Not Found';
                                    ?>
                                </td>

                                <!-- Credit -->
                                <td>
                                    <strong>
                                        <?php echo number_format((float)($result['credit'] ?? 0), 2); ?>
                                    </strong>
                                </td>

                                <!-- Marks -->
                                <td>
                                    <strong>
                                        <?php echo number_format($marks, 2); ?>
                                    </strong> / 100
                                </td>

                                <!-- Grade -->
                                <td>
                                    <span class="badge bg-<?php echo $grade_class; ?> fs-6">
                                        <?php echo htmlspecialchars($grade); ?>
                                    </span>
                                </td>

                                <!-- Grade Point -->
                                <td>
                                    <strong>
                                        <?php echo number_format($grade_point, 2); ?>
                                    </strong>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>

                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="2" class="text-end">Total Marks</td>
                                <td><?php echo number_format($total_marks, 2); ?></td>
                                <td colspan="2"><?php echo $total_courses; ?> Courses | <?php echo number_format($total_credits, 2); ?> Passed Credits</td>
                            </tr>

                            <tr class="table-warning fw-bold">
                                <td colspan="2" class="text-end">Merit Position</td>
                                <td colspan="3">
                                    <?php echo $merit_position !== null ? getOrdinal($merit_position) : 'N/A'; ?>
                                    <?php if ($total_students_ranked > 0) { ?>
                                        <span class="ms-2">Out of <?php echo $total_students_ranked; ?> Students</span>
                                    <?php } ?>
                                </td>
                            </tr>

                            <tr class="table-success fw-bold">
                                <td colspan="2" class="text-end">CGPA</td>
                                <td><?php echo number_format($cgpa, 2); ?></td>
                                <td colspan="2">Overall CGPA: <?php echo number_format($cgpa, 2); ?></td>
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


    <div class="print-footer">
        <span>This is a computer-generated academic result.</span>
        <span>Generated on: <?php echo date('d M Y'); ?></span>
    </div>

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