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

$message = "";
$message_type = "";


// ==================================================
// GET TEACHER NAME
// ==================================================

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

    if ($teacher_row = mysqli_fetch_assoc($teacher_result)) {

        $teacher_name =
            $teacher_row['full_name'];
    }

    mysqli_stmt_close($stmt_teacher);
}


// ==================================================
// SEND RESULT TO ADMIN
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $course_id = isset($_POST['course_id'])
        ? (int) $_POST['course_id']
        : 0;

    $exam_id = isset($_POST['exam_id'])
        ? (int) $_POST['exam_id']
        : 0;

    $department_id = isset($_POST['department_id'])
        ? (int) $_POST['department_id']
        : 0;

    $semester_id = isset($_POST['semester_id'])
        ? (int) $_POST['semester_id']
        : 0;


    // ==================================================
    // VALIDATION
    // ==================================================

    if (
        $course_id <= 0 ||
        $exam_id <= 0 ||
        $department_id <= 0 ||
        $semester_id <= 0
    ) {

        $message =
            "Please select Course, Exam, Department and Semester.";

        $message_type = "error";

    } else {


        // ==================================================
        // CHECK TEACHER ASSIGNED COURSE
        // ==================================================

        $stmt_check = mysqli_prepare(
            $conn,
            "SELECT 1
             FROM teacher_courses
             WHERE teacher_id = ?
             AND course_id = ?
             LIMIT 1"
        );

        $teacher_assigned = false;

        if ($stmt_check) {

            mysqli_stmt_bind_param(
                $stmt_check,
                "ii",
                $teacher_id,
                $course_id
            );

            mysqli_stmt_execute($stmt_check);

            $check_result =
                mysqli_stmt_get_result($stmt_check);

            if (mysqli_num_rows($check_result) > 0) {

                $teacher_assigned = true;
            }

            mysqli_stmt_close($stmt_check);
        }


        if (!$teacher_assigned) {

            $message =
                "You are not assigned to this course.";

            $message_type = "error";

        } else {


            // ==================================================
            // CHECK EXAM BELONGS TO COURSE
            // ==================================================

            $stmt_exam = mysqli_prepare(
                $conn,
                "SELECT
                    exam_id,
                    exam_name,
                    total_marks
                 FROM exams
                 WHERE exam_id = ?
                 AND course_id = ?
                 LIMIT 1"
            );

            $exam_valid = false;

            if ($stmt_exam) {

                mysqli_stmt_bind_param(
                    $stmt_exam,
                    "ii",
                    $exam_id,
                    $course_id
                );

                mysqli_stmt_execute($stmt_exam);

                $exam_result =
                    mysqli_stmt_get_result($stmt_exam);

                if (
                    $exam_row =
                    mysqli_fetch_assoc($exam_result)
                ) {

                    $exam_valid = true;
                }

                mysqli_stmt_close($stmt_exam);
            }


            if (!$exam_valid) {

                $message =
                    "Invalid exam selected for this course.";

                $message_type = "error";

            } else {


                // ==================================================
                // CHECK EXISTING RESULT
                // ==================================================

                $stmt_result = mysqli_prepare(
                    $conn,
                    "SELECT
                        result_id,
                        status
                     FROM results
                     WHERE exam_id = ?
                     AND department_id = ?
                     AND semester_id = ?
                     LIMIT 1"
                );

                $result_exists = false;
                $existing_status = "";

                if ($stmt_result) {

                    mysqli_stmt_bind_param(
                        $stmt_result,
                        "iii",
                        $exam_id,
                        $department_id,
                        $semester_id
                    );

                    mysqli_stmt_execute($stmt_result);

                    $existing_result =
                        mysqli_stmt_get_result($stmt_result);

                    if (
                        $existing_row =
                        mysqli_fetch_assoc($existing_result)
                    ) {

                        $result_exists = true;

                        $existing_status =
                            $existing_row['status'] ?? '';
                    }

                    mysqli_stmt_close($stmt_result);
                }


                // ==================================================
                // CHECK PREVIOUS SUBMISSION
                // ==================================================

                $stmt_submission = mysqli_prepare(
                    $conn,
                    "SELECT
                        submission_id,
                        status
                     FROM result_submissions
                     WHERE teacher_id = ?
                     AND course_id = ?
                     AND exam_id = ?
                     AND department_id = ?
                     AND semester_id = ?
                     LIMIT 1"
                );

                $submission_exists = false;
                $submission_status = "";

                if ($stmt_submission) {

                    mysqli_stmt_bind_param(
                        $stmt_submission,
                        "iiiii",
                        $teacher_id,
                        $course_id,
                        $exam_id,
                        $department_id,
                        $semester_id
                    );

                    mysqli_stmt_execute(
                        $stmt_submission
                    );

                    $submission_result =
                        mysqli_stmt_get_result(
                            $stmt_submission
                        );

                    if (
                        $submission_row =
                        mysqli_fetch_assoc($submission_result)
                    ) {

                        $submission_exists = true;

                        $submission_status =
                            $submission_row['status'];
                    }

                    mysqli_stmt_close(
                        $stmt_submission
                    );
                }


                // ==================================================
                // ALREADY PUBLISHED
                // ==================================================

                if (
                    $result_exists &&
                    $existing_status === 'Published'
                ) {

                    $message =
                        "This result has already been published.";

                    $message_type = "error";

                }


                // ==================================================
                // ALREADY PENDING
                // ==================================================

                elseif (
                    $submission_exists &&
                    $submission_status === 'Pending'
                ) {

                    $message =
                        "This result has already been sent to Admin.";

                    $message_type = "error";

                }


                // ==================================================
                // ALREADY APPROVED
                // ==================================================

                elseif (
                    $submission_exists &&
                    $submission_status === 'Approved'
                ) {

                    $message =
                        "This result has already been approved.";

                    $message_type = "error";

                }


                // ==================================================
                // SEND / RESUBMIT
                // ==================================================

                else {


                    // ==================================================
                    // RESUBMIT REJECTED
                    // ==================================================

                    if (
                        $submission_exists &&
                        $submission_status === 'Rejected'
                    ) {

                        $stmt_update = mysqli_prepare(
                            $conn,
                            "UPDATE result_submissions
                             SET
                                status = 'Pending',
                                submission_date = NOW(),
                                admin_note = NULL,
                                approved_date = NULL
                             WHERE teacher_id = ?
                             AND course_id = ?
                             AND exam_id = ?
                             AND department_id = ?
                             AND semester_id = ?"
                        );

                        if ($stmt_update) {

                            mysqli_stmt_bind_param(
                                $stmt_update,
                                "iiiii",
                                $teacher_id,
                                $course_id,
                                $exam_id,
                                $department_id,
                                $semester_id
                            );

                            if (
                                mysqli_stmt_execute(
                                    $stmt_update
                                )
                            ) {

                                $message =
                                    "Result successfully resubmitted to Admin.";

                                $message_type =
                                    "success";

                            } else {

                                $message =
                                    "Failed to resubmit result.";

                                $message_type =
                                    "error";
                            }

                            mysqli_stmt_close(
                                $stmt_update
                            );
                        }

                    }


                    // ==================================================
                    // NEW SUBMISSION
                    // ==================================================

                    else {

                        $stmt_insert = mysqli_prepare(
                            $conn,
                            "INSERT INTO result_submissions
                            (
                                teacher_id,
                                course_id,
                                exam_id,
                                department_id,
                                semester_id,
                                submission_date,
                                status
                            )
                            VALUES
                            (
                                ?,
                                ?,
                                ?,
                                ?,
                                ?,
                                NOW(),
                                'Pending'
                            )"
                        );

                        if ($stmt_insert) {

                            mysqli_stmt_bind_param(
                                $stmt_insert,
                                "iiiii",
                                $teacher_id,
                                $course_id,
                                $exam_id,
                                $department_id,
                                $semester_id
                            );

                            if (
                                mysqli_stmt_execute(
                                    $stmt_insert
                                )
                            ) {

                                $message =
                                    "Result successfully sent to Admin.";

                                $message_type =
                                    "success";

                            } else {

                                $message =
                                    "Failed to send result to Admin.";

                                $message_type =
                                    "error";
                            }

                            mysqli_stmt_close(
                                $stmt_insert
                            );
                        }
                    }
                }
            }
        }
    }
}


// ==================================================
// GET TEACHER ASSIGNED COURSES
// ==================================================

$courses = [];

$stmt_courses = mysqli_prepare(
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

if ($stmt_courses) {

    mysqli_stmt_bind_param(
        $stmt_courses,
        "i",
        $teacher_id
    );

    mysqli_stmt_execute(
        $stmt_courses
    );

    $courses_result =
        mysqli_stmt_get_result(
            $stmt_courses
        );

    while (
        $course_row =
        mysqli_fetch_assoc($courses_result)
    ) {

        $courses[] =
            $course_row;
    }

    mysqli_stmt_close(
        $stmt_courses
    );
}


// ==================================================
// GET DEPARTMENTS
// ==================================================

$departments = [];

$department_query = mysqli_query(
    $conn,
    "SELECT
        department_id,
        department_name
     FROM departments
     ORDER BY department_name ASC"
);

if ($department_query) {

    while (
        $department_row =
        mysqli_fetch_assoc($department_query)
    ) {

        $departments[] =
            $department_row;
    }
}


// ==================================================
// GET SEMESTERS
// ==================================================

$semesters = [];

$semester_query = mysqli_query(
    $conn,
    "SELECT
        semester_id,
        semester_name
     FROM semesters
     ORDER BY semester_id ASC"
);

if ($semester_query) {

    while (
        $semester_row =
        mysqli_fetch_assoc($semester_query)
    ) {

        $semesters[] =
            $semester_row;
    }
}


// ==================================================
// GET EXAMS
// ==================================================
// Only these three exams are available:
// Term Test 1, Term Test 2, Final Exam
// ==================================================

$exams = [];

$exam_query = mysqli_query(
    $conn,
    "SELECT
        exam_id,
        course_id,
        exam_name,
        total_marks
     FROM exams
     WHERE exam_name IN ('Term Test 1', 'Term Test 2', 'Final Exam')
     ORDER BY
        course_id ASC,
        CASE exam_name
            WHEN 'Term Test 1' THEN 1
            WHEN 'Term Test 2' THEN 2
            WHEN 'Final Exam' THEN 3
            ELSE 4
        END ASC"
);

if ($exam_query) {

    while ($exam_row = mysqli_fetch_assoc($exam_query)) {

        // Keep the exact exam names from the database.
        // These are the only exam types shown in the dropdown.
        $exam_row['display_name'] = trim($exam_row['exam_name']);

        if ($exam_row['exam_name'] === 'Term Test 1') {
            $exam_row['exam_order'] = 1;
        } elseif ($exam_row['exam_name'] === 'Term Test 2') {
            $exam_row['exam_order'] = 2;
        } elseif ($exam_row['exam_name'] === 'Final Exam') {
            $exam_row['exam_order'] = 3;
        } else {
            continue;
        }

        $exams[] = $exam_row;
    }
}

// ==================================================
// SORT EXAMS
// Term Test 1 → Term Test 2 → Final Exam
// ==================================================

usort(
    $exams,
    function ($a, $b) {

        if ((int)$a['course_id'] === (int)$b['course_id']) {
            return (int)$a['exam_order'] <=> (int)$b['exam_order'];
        }

        return (int)$a['course_id'] <=> (int)$b['course_id'];
    }
);


// ==================================================
// GET SUBMISSION HISTORY
// ==================================================

$submission_history = [];

$stmt_history = mysqli_prepare(
    $conn,
    "SELECT
        rs.submission_id,
        rs.course_id,
        rs.exam_id,
        rs.department_id,
        rs.semester_id,
        rs.submission_date,
        rs.status,
        rs.admin_note,

        c.course_code,
        c.course_name,

        e.exam_name,

        d.department_name,

        s.semester_name

     FROM result_submissions rs

     LEFT JOIN courses c
        ON rs.course_id = c.course_id

     LEFT JOIN exams e
        ON rs.exam_id = e.exam_id

     LEFT JOIN departments d
        ON rs.department_id = d.department_id

     LEFT JOIN semesters s
        ON rs.semester_id = s.semester_id

     WHERE rs.teacher_id = ?

     ORDER BY rs.submission_id DESC"
);

if ($stmt_history) {

    mysqli_stmt_bind_param(
        $stmt_history,
        "i",
        $teacher_id
    );

    mysqli_stmt_execute(
        $stmt_history
    );

    $history_result =
        mysqli_stmt_get_result(
            $stmt_history
        );

    while (
        $history_row =
        mysqli_fetch_assoc($history_result)
    ) {

        $submission_history[] =
            $history_row;
    }

    mysqli_stmt_close(
        $stmt_history
    );
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
        Send Result to Admin
    </title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f5f7fb;

            color: #1f2937;

        }


        .container {

            width: 94%;

            max-width: 1250px;

            margin: 30px auto;

        }


        /* ==========================================
           HEADER
        ========================================== */

        .header {

            background: white;

            padding: 22px 25px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow:
                0 3px 12px
                rgba(0,0,0,0.06);

            display: flex;

            justify-content:
                space-between;

            align-items: center;

        }


        .header h1 {

            font-size: 27px;

            color: #111827;

        }


        .header p {

            margin-top: 6px;

            color: #6b7280;

        }


        .back-btn {

            text-decoration: none;

            background: #374151;

            color: white;

            padding: 10px 17px;

            border-radius: 7px;

            font-size: 14px;

        }


        .back-btn:hover {

            background: #111827;

        }


        /* ==========================================
           MESSAGE
        ========================================== */

        .message {

            padding: 15px 18px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 15px;

        }


        .message.success {

            background: #dcfce7;

            color: #166534;

            border:
                1px solid #86efac;

        }


        .message.error {

            background: #fee2e2;

            color: #991b1b;

            border:
                1px solid #fca5a5;

        }


        /* ==========================================
           CARD
        ========================================== */

        .card {

            background: white;

            border-radius: 12px;

            padding: 25px;

            margin-bottom: 25px;

            box-shadow:
                0 3px 12px
                rgba(0,0,0,0.06);

        }


        .card h2 {

            font-size: 21px;

            margin-bottom: 7px;

        }


        .card-description {

            color: #6b7280;

            margin-bottom: 22px;

        }


        /* ==========================================
           FORM
        ========================================== */

        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;

        }


        .form-group {

            display: flex;

            flex-direction: column;

        }


        .form-group label {

            font-size: 14px;

            font-weight: 600;

            margin-bottom: 8px;

            color: #374151;

        }


        .form-group select {

            width: 100%;

            padding: 12px 13px;

            border:
                1px solid #d1d5db;

            border-radius: 7px;

            font-size: 15px;

            background: white;

            outline: none;

        }


        .form-group select:focus {

            border-color: #2563eb;

            box-shadow:
                0 0 0 2px
                rgba(37,99,235,0.10);

        }


        .submit-area {

            margin-top: 25px;

            text-align: right;

        }


        .submit-btn {

            border: none;

            background: #2563eb;

            color: white;

            padding: 13px 22px;

            border-radius: 7px;

            cursor: pointer;

            font-size: 15px;

            font-weight: 600;

        }


        .submit-btn:hover {

            background: #1d4ed8;

        }


        /* ==========================================
           INFO
        ========================================== */

        .info-box {

            background: #eff6ff;

            border:
                1px solid #bfdbfe;

            color: #1e40af;

            padding: 15px 18px;

            border-radius: 8px;

            margin-top: 20px;

            line-height: 1.6;

            font-size: 14px;

        }


        /* ==========================================
           TABLE
        ========================================== */

        .table-wrapper {

            width: 100%;

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse:
                collapse;

            min-width: 900px;

        }


        th {

            background: #f9fafb;

            padding: 14px 12px;

            text-align: left;

            font-size: 13px;

            border-bottom:
                1px solid #e5e7eb;

        }


        td {

            padding: 14px 12px;

            border-bottom:
                1px solid #e5e7eb;

            font-size: 14px;

        }


        tr:hover td {

            background: #f9fafb;

        }


        /* ==========================================
           STATUS
        ========================================== */

        .status {

            display: inline-block;

            padding: 6px 11px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;

        }


        .pending {

            background: #fef3c7;

            color: #92400e;

        }


        .approved {

            background: #dcfce7;

            color: #166534;

        }


        .rejected {

            background: #fee2e2;

            color: #991b1b;

        }


        /* ==========================================
           EMPTY
        ========================================== */

        .empty {

            text-align: center;

            padding: 35px 15px;

            color: #6b7280;

        }


        /* ==========================================
           RESPONSIVE
        ========================================== */

        @media (max-width: 800px) {

            .header {

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;

            }


            .form-grid {

                grid-template-columns: 1fr;

            }


            .submit-area {

                text-align: left;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- ==========================================
         HEADER
    ========================================== -->

    <div class="header">

        <div>

            <h1>
                Send Result to Admin
            </h1>

            <p>

                Teacher:

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $teacher_name
                    );

                    ?>

                </strong>

            </p>

        </div>


        <a
            href="dashboard.php"
            class="back-btn"
        >

            ← Back to Dashboard

        </a>

    </div>


    <!-- ==========================================
         MESSAGE
    ========================================== -->

    <?php if ($message !== ""): ?>

        <div
            class="message
            <?php echo htmlspecialchars($message_type); ?>"
        >

            <?php

            echo htmlspecialchars(
                $message
            );

            ?>

        </div>

    <?php endif; ?>


    <!-- ==========================================
         SUBMIT RESULT
    ========================================== -->

    <div class="card">


        <h2>
            Submit Result
        </h2>


        <p class="card-description">

            Select Course, Exam, Department
            and Semester to send the result
            to Admin.

        </p>


        <form
            method="POST"
            action="Send_Result_to_Admin.php"
        >


            <div class="form-grid">


                <!-- ==================================
                     COURSE
                =================================== -->

                <div class="form-group">

                    <label>
                        Select Course
                    </label>


                    <select
                        name="course_id"
                        id="course_id"
                        required
                    >

                        <option value="">
                            -- Select Course --
                        </option>


                        <?php foreach (
                            $courses
                            as $course
                        ): ?>

                            <option
                                value="<?php
                                echo (int)
                                    $course['course_id'];
                                ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $course['course_code']
                                    . " - "
                                    . $course['course_name']
                                );

                                ?>

                            </option>

                        <?php endforeach; ?>


                    </select>

                </div>


                <!-- ==================================
                     EXAM
                =================================== -->

                <div class="form-group">

                    <label>
                        Select Exam
                    </label>


                    <select
                        name="exam_id"
                        id="exam_id"
                        required
                        disabled
                    >

                        <option value="">
                            -- Select Exam --
                        </option>

                        <?php foreach ($exams as $exam): ?>

                            <option
                                value="<?php echo (int)$exam['exam_id']; ?>"
                                data-course="<?php echo (int)$exam['course_id']; ?>"
                            >
                                <?php
                                echo htmlspecialchars(
                                    $exam['display_name']
                                    . ' ('
                                    . $exam['total_marks']
                                    . ' Marks)'
                                );
                                ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- ==================================
                     DEPARTMENT
                =================================== -->

                <div class="form-group">

                    <label>
                        Select Department
                    </label>


                    <select
                        name="department_id"
                        required
                    >

                        <option value="">
                            -- Select Department --
                        </option>


                        <?php foreach (
                            $departments
                            as $department
                        ): ?>

                            <option
                                value="<?php
                                echo (int)
                                    $department['department_id'];
                                ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $department[
                                        'department_name'
                                    ]
                                );

                                ?>

                            </option>

                        <?php endforeach; ?>


                    </select>

                </div>


                <!-- ==================================
                     SEMESTER
                =================================== -->

                <div class="form-group">

                    <label>
                        Select Semester
                    </label>


                    <select
                        name="semester_id"
                        required
                    >

                        <option value="">
                            -- Select Semester --
                        </option>


                        <?php foreach (
                            $semesters
                            as $semester
                        ): ?>

                            <option
                                value="<?php
                                echo (int)
                                    $semester['semester_id'];
                                ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $semester[
                                        'semester_name'
                                    ]
                                );

                                ?>

                            </option>

                        <?php endforeach; ?>


                    </select>

                </div>


            </div>


            <!-- ==================================
                 INFO
            =================================== -->

            <div class="info-box">

                <strong>
                    Important:
                </strong>

                After clicking
                <strong>
                    Send Result to Admin
                </strong>,

                the result will be submitted
                with

                <strong>
                    Pending
                </strong>

                status.

                Admin will review and publish
                the result.

            </div>


            <!-- ==================================
                 BUTTON
            ================================== -->

            <div class="submit-area">

                <button
                    type="submit"
                    class="submit-btn"
                    onclick="
                        return confirm(
                            'Are you sure you want to send this result to Admin?'
                        );
                    "
                >

                    📤 Send Result to Admin

                </button>

            </div>


        </form>


    </div>


    <!-- ==========================================
         SUBMISSION HISTORY
    ========================================== -->

    <div class="card">


        <h2>
            Submission History
        </h2>


        <p class="card-description">

            Your previously submitted
            results are shown below.

        </p>


        <?php if (
            count($submission_history) > 0
        ): ?>


            <div class="table-wrapper">


                <table>


                    <thead>

                    <tr>

                        <th>#</th>

                        <th>Course</th>

                        <th>Exam</th>

                        <th>Department</th>

                        <th>Semester</th>

                        <th>Submitted Date</th>

                        <th>Status</th>

                        <th>Admin Note</th>

                    </tr>

                    </thead>


                    <tbody>


                    <?php

                    $serial = 1;

                    foreach (
                        $submission_history
                        as $history
                    ):

                    ?>


                        <tr>


                            <td>

                                <?php

                                echo $serial++;

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    ($history[
                                        'course_code'
                                    ] ?? '')
                                    . " - "
                                    .
                                    ($history[
                                        'course_name'
                                    ] ?? '')
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                $history_exam =
                                    strtolower(
                                        trim(
                                            preg_replace(
                                                '/[^a-z0-9]+/',
                                                ' ',
                                                $history[
                                                    'exam_name'
                                                ] ?? ''
                                            )
                                        )
                                    );


                                if (
                                    preg_match(
                                        '/^term\s*test\s*1$/i',
                                        $history_exam
                                    )
                                ) {

                                    echo "Term Test 1";

                                } elseif (
                                    preg_match(
                                        '/^term\s*test\s*2$/i',
                                        $history_exam
                                    )
                                ) {

                                    echo "Term Test 2";

                                } elseif (
                                    $history_exam ===
                                    'final' ||
                                    $history_exam ===
                                    'final exam'
                                ) {

                                    echo "Final Exam";

                                } else {

                                    echo htmlspecialchars(
                                        $history[
                                            'exam_name'
                                        ] ?? 'N/A'
                                    );
                                }

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $history[
                                        'department_name'
                                    ] ?? 'N/A'
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $history[
                                        'semester_name'
                                    ] ?? 'N/A'
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                if (
                                    !empty(
                                        $history[
                                            'submission_date'
                                        ]
                                    )
                                ) {

                                    echo date(
                                        'd M Y, h:i A',
                                        strtotime(
                                            $history[
                                                'submission_date'
                                            ]
                                        )
                                    );

                                } else {

                                    echo "N/A";
                                }

                                ?>

                            </td>


                            <td>

                                <?php

                                $status =
                                    $history[
                                        'status'
                                    ] ?? 'Pending';

                                ?>


                                <?php if (
                                    $status ===
                                    'Pending'
                                ): ?>

                                    <span
                                        class="
                                        status
                                        pending
                                        "
                                    >

                                        Pending

                                    </span>


                                <?php elseif (
                                    $status ===
                                    'Approved'
                                ): ?>

                                    <span
                                        class="
                                        status
                                        approved
                                        "
                                    >

                                        Approved

                                    </span>


                                <?php else: ?>

                                    <span
                                        class="
                                        status
                                        rejected
                                        "
                                    >

                                        Rejected

                                    </span>

                                <?php endif; ?>


                            </td>


                            <td>

                                <?php

                                if (
                                    !empty(
                                        $history[
                                            'admin_note'
                                        ]
                                    )
                                ) {

                                    echo htmlspecialchars(
                                        $history[
                                            'admin_note'
                                        ]
                                    );

                                } else {

                                    echo "-";
                                }

                                ?>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>


                </table>


            </div>


        <?php else: ?>


            <div class="empty">

                <p>
                    You have not submitted
                    any result yet.
                </p>

            </div>


        <?php endif; ?>


    </div>


</div>


<script>

// ==================================================
// COURSE → EXAM FILTER
// ==================================================

const courseSelect = document.getElementById('course_id');
const examSelect = document.getElementById('exam_id');

// Save all real exam options.
const allExamOptions = Array.from(
    examSelect.querySelectorAll('option[data-course]')
);

function updateExamOptions() {

    const selectedCourse = courseSelect.value;

    // Reset exam selection every time course changes.
    examSelect.value = '';

    // No course selected → disable exam dropdown.
    if (selectedCourse === '') {

        examSelect.disabled = true;

        allExamOptions.forEach(function (option) {
            option.hidden = true;
        });

        return;
    }

    // Course selected → enable exam dropdown and
    // show only Term Test 1, Term Test 2 and Final Exam
    // belonging to that course.
    examSelect.disabled = false;

    let visibleCount = 0;

    allExamOptions.forEach(function (option) {

        if (option.dataset.course === selectedCourse) {
            option.hidden = false;
            visibleCount++;
        } else {
            option.hidden = true;
        }
    });

    // If no exam exists for this course, disable dropdown again.
    if (visibleCount === 0) {
        examSelect.disabled = true;
    }
}

// Run when course changes.
courseSelect.addEventListener('change', updateExamOptions);

// Initial state.
updateExamOptions();

</script>


</body>

</html>