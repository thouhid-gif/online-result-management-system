<?php
session_start();

include '../config/database.php';
include '../config/session.php';

checkAdmin();

/* ==================================================
   CHECK RESULT ID
================================================== */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: publish-result.php");
    exit();
}

$result_id = (int) $_GET['id'];

/* ==================================================
   GET RESULT INFORMATION
================================================== */

$stmt_result = mysqli_prepare(
    $conn,
    "SELECT
        r.*,
        c.course_code,
        c.course_name,
        e.exam_name,
        e.total_marks,
        d.department_name,
        s.semester_name
     FROM results r
     LEFT JOIN exams e
        ON r.exam_id = e.exam_id
     LEFT JOIN courses c
        ON e.course_id = c.course_id
     LEFT JOIN departments d
        ON r.department_id = d.department_id
     LEFT JOIN semesters s
        ON r.semester_id = s.semester_id
     WHERE r.result_id = ?
     LIMIT 1"
);

$result = null;

if ($stmt_result) {
    mysqli_stmt_bind_param(
        $stmt_result,
        "i",
        $result_id
    );

    mysqli_stmt_execute($stmt_result);

    $result_info = mysqli_stmt_get_result($stmt_result);

    $result = mysqli_fetch_assoc($result_info);

    mysqli_stmt_close($stmt_result);
}

if (!$result) {
    header("Location: publish-result.php");
    exit();
}

/* ==================================================
   DISPLAY EXAM NAME
================================================== */

$exam_raw = strtolower(
    trim(
        preg_replace(
            '/[^a-z0-9]+/',
            ' ',
            $result['exam_name'] ?? ''
        )
    )
);

if ($exam_raw === 'term test 1') {
    $display_exam = 'Term Test 1';
} elseif ($exam_raw === 'term test 2') {
    $display_exam = 'Term Test 2';
} elseif (
    $exam_raw === 'final' ||
    $exam_raw === 'final exam'
) {
    $display_exam = 'Final Exam';
} else {
    $display_exam = $result['exam_name'] ?? 'N/A';
}

/* ==================================================
   GET SUBJECT-WISE STUDENT RESULT

   The selected result is identified by result_id.
   Its exam_id is then used to fetch the marks belonging
   to that exam.

   Each row represents:
   Student + Course + Marks + Grade + GPA
================================================== */

$students = [];

$stmt_marks = mysqli_prepare(
    $conn,
    "SELECT
        st.student_roll,
        st.full_name,
        m.course_id,
        c.course_code,
        c.course_name,
        m.marks,
        m.grade,
        m.gpa
     FROM marks m
     LEFT JOIN students st
        ON m.student_id = st.student_id
     LEFT JOIN courses c
        ON m.course_id = c.course_id
     WHERE m.exam_id = ?
     ORDER BY
        st.student_roll ASC,
        c.course_code ASC"
);

if ($stmt_marks) {
    $exam_id = (int) $result['exam_id'];

    mysqli_stmt_bind_param(
        $stmt_marks,
        "i",
        $exam_id
    );

    mysqli_stmt_execute($stmt_marks);

    $marks_result = mysqli_stmt_get_result($stmt_marks);

    while ($row = mysqli_fetch_assoc($marks_result)) {
        $students[] = $row;
    }

    mysqli_stmt_close($stmt_marks);
}

/* ==================================================
   CALCULATE STUDENT-WISE CGPA
   CGPA = average of the subject GPAs for each student
   in this selected exam/result.
================================================== */

$studentCgpa = [];

foreach ($students as $row) {

    $roll = $row['student_roll'] ?? '';

    if (!isset($studentCgpa[$roll])) {
        $studentCgpa[$roll] = [
            'total' => 0,
            'count' => 0
        ];
    }

    if (
        isset($row['gpa']) &&
        is_numeric($row['gpa'])
    ) {
        $studentCgpa[$roll]['total'] += (float) $row['gpa'];
        $studentCgpa[$roll]['count']++;
    }
}

foreach ($students as &$row) {

    $roll = $row['student_roll'] ?? '';

    if (
        isset($studentCgpa[$roll]) &&
        $studentCgpa[$roll]['count'] > 0
    ) {
        $row['cgpa'] = number_format(
            $studentCgpa[$roll]['total'] /
            $studentCgpa[$roll]['count'],
            2
        );
    } else {
        $row['cgpa'] = 'N/A';
    }
}

unset($row);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>View Result</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        body {
            background: #f4f7fb;
        }

        .result-header {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }

        .result-header .card-header {
            background: #2563eb;
            padding: 18px 22px;
        }

        .info-item {
            padding: 12px 10px;
        }

        .info-label {
            display: block;
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 600;
            color: #111827;
        }

        .result-table {
            vertical-align: middle;
        }

        .result-table th {
            white-space: nowrap;
        }

        .result-table td {
            white-space: nowrap;
        }

        .empty-result {
            padding: 45px 20px;
            text-align: center;
            color: #6b7280;
        }

        .badge-exam {
            background: #e0e7ff;
            color: #3730a3;
            padding: 7px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        @media print {

            .no-print {
                display: none !important;
            }

            body {
                background: white;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

        }

    </style>

</head>

<body>

<div class="container py-4">

    <!-- ==========================================
         RESULT INFORMATION
    =========================================== -->

    <div class="card shadow-sm mb-4 result-header">

        <div class="card-header text-white">

            <h3 class="mb-0">
                <i class="fa fa-file-lines me-2"></i>
                Published Result Details
            </h3>

        </div>

        <div class="card-body">

            <div class="row">

                <!-- COURSE -->

                <div class="col-md-6 col-lg-3">

                    <div class="info-item">

                        <span class="info-label">
                            Course
                        </span>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars(
                                ($result['course_code'] ?? '') .
                                ' - ' .
                                ($result['course_name'] ?? 'N/A')
                            );
                            ?>

                        </div>

                    </div>

                </div>

                <!-- EXAM -->

                <div class="col-md-6 col-lg-3">

                    <div class="info-item">

                        <span class="info-label">
                            Exam
                        </span>

                        <div class="info-value">

                            <span class="badge-exam">

                                <?php
                                echo htmlspecialchars(
                                    $display_exam
                                );
                                ?>

                            </span>

                        </div>

                    </div>

                </div>

                <!-- DEPARTMENT -->

                <div class="col-md-6 col-lg-3">

                    <div class="info-item">

                        <span class="info-label">
                            Department
                        </span>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars(
                                $result['department_name'] ?? 'N/A'
                            );
                            ?>

                        </div>

                    </div>

                </div>

                <!-- SEMESTER -->

                <div class="col-md-6 col-lg-3">

                    <div class="info-item">

                        <span class="info-label">
                            Semester
                        </span>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars(
                                $result['semester_name'] ?? 'N/A'
                            );
                            ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ==========================================
         SUBJECT-WISE RESULT
    =========================================== -->

    <div class="card shadow-sm">

        <div class="card-header bg-success text-white">

            <div class="d-flex justify-content-between align-items-center">

                <h4 class="mb-0">

                    <i class="fa fa-table me-2"></i>

                    Subject-wise Student Result

                </h4>

                <span class="badge bg-light text-dark">

                    <?php echo count($students); ?> Records

                </span>

            </div>

        </div>

        <div class="card-body">

            <?php if (count($students) > 0): ?>

                <div class="table-responsive">

                    <table
                        class="table table-bordered table-hover result-table mb-0"
                    >

                        <thead class="table-dark">

                            <tr>

                                <th>#</th>

                                <th>Roll</th>

                                <th>Student Name</th>

                                <th>Course</th>

                                <th>Marks</th>

                                <th>Grade</th>

                                <th>CGPA</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php

                        $serial = 1;

                        foreach ($students as $row):

                        ?>

                            <tr>

                                <td>
                                    <?php echo $serial++; ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row['student_roll'] ?? 'N/A'
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row['full_name'] ?? 'N/A'
                                    );
                                    ?>
                                </td>

                                <td>
                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            ($row['course_code'] ?? '') .
                                            ' - ' .
                                            ($row['course_name'] ?? 'N/A')
                                        );
                                        ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row['marks'] ?? 'N/A'
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row['grade'] ?? 'N/A'
                                    );
                                    ?>
                                </td>

                                <td>
                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $row['cgpa'] ?? 'N/A'
                                        );
                                        ?>
                                    </strong>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-result">

                    <i
                        class="fa fa-circle-info fa-2x mb-3"
                    ></i>

                    <h5>
                        No Result Found
                    </h5>

                    <p class="mb-0">
                        No subject-wise marks are available
                        for this published result.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- ==========================================
         BUTTONS
    =========================================== -->

    <div class="mt-4 no-print">

        <a
            href="publish-result.php"
            class="btn btn-secondary me-2"
        >

            <i class="fa fa-arrow-left me-1"></i>

            Back

        </a>

        <button
            type="button"
            onclick="window.print()"
            class="btn btn-success"
        >

            <i class="fa fa-print me-1"></i>

            Print Result

        </button>

    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>