<?php
session_start();

require_once '../config/database.php';

if (!isset($conn)) {
    die("Database connection variable \$conn not found.");
}

$success = "";
$error = "";


/* =========================================================
   BULK PUBLISH RESULT
   Department + Semester wise
========================================================= */

if (
    isset($_POST['bulk_publish']) &&
    isset($_POST['department_id']) &&
    isset($_POST['semester_id'])
) {

    $department_id = (int) $_POST['department_id'];
    $semester_id   = (int) $_POST['semester_id'];

    if ($department_id <= 0 || $semester_id <= 0) {

        $error = "Please select both Department and Semester.";

    } else {

        mysqli_begin_transaction($conn);

        try {

            /* =================================================
               STEP 1:
               Get all exams of selected Department + Semester
            ================================================= */

            $exam_query = mysqli_prepare(
                $conn,
                "SELECT exam_id
                 FROM exams
                 WHERE department_id = ?
                 AND semester_id = ?"
            );

            mysqli_stmt_bind_param(
                $exam_query,
                "ii",
                $department_id,
                $semester_id
            );

            mysqli_stmt_execute($exam_query);

            $exam_result = mysqli_stmt_get_result($exam_query);

            $exam_ids = [];

            while ($exam = mysqli_fetch_assoc($exam_result)) {
                $exam_ids[] = (int) $exam['exam_id'];
            }

            mysqli_stmt_close($exam_query);


            if (count($exam_ids) == 0) {

                throw new Exception(
                    "No exam found for this Department and Semester."
                );
            }


            /* =================================================
               STEP 2:
               Get all students who submitted marks
            ================================================= */

            $student_query = mysqli_prepare(
                $conn,
                "SELECT DISTINCT m.student_id
                 FROM marks m
                 INNER JOIN exams e
                    ON m.exam_id = e.exam_id
                 WHERE e.department_id = ?
                 AND e.semester_id = ?"
            );

            mysqli_stmt_bind_param(
                $student_query,
                "ii",
                $department_id,
                $semester_id
            );

            mysqli_stmt_execute($student_query);

            $student_result =
                mysqli_stmt_get_result($student_query);

            $student_ids = [];

            while ($student = mysqli_fetch_assoc($student_result)) {

                $student_ids[] =
                    (int) $student['student_id'];
            }

            mysqli_stmt_close($student_query);


            if (count($student_ids) == 0) {

                throw new Exception(
                    "No student marks found for this Department and Semester."
                );
            }


            /* =================================================
               STEP 3:
               Publish all course results
            ================================================= */

            $published_count = 0;


            foreach ($student_ids as $student_id) {

                $mark_query = mysqli_prepare(
                    $conn,
                    "SELECT
                        m.exam_id,
                        m.marks
                     FROM marks m
                     INNER JOIN exams e
                        ON m.exam_id = e.exam_id
                     WHERE m.student_id = ?
                     AND e.department_id = ?
                     AND e.semester_id = ?"
                );

                mysqli_stmt_bind_param(
                    $mark_query,
                    "iii",
                    $student_id,
                    $department_id,
                    $semester_id
                );

                mysqli_stmt_execute($mark_query);

                $mark_result =
                    mysqli_stmt_get_result($mark_query);


                while ($mark = mysqli_fetch_assoc($mark_result)) {

                    $exam_id =
                        (int) $mark['exam_id'];

                    $marks =
                        (float) $mark['marks'];


                    /* Check existing result */

                    $check_result_query = mysqli_prepare(
                        $conn,
                        "SELECT result_id
                         FROM results
                         WHERE student_id = ?
                         AND exam_id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $check_result_query,
                        "ii",
                        $student_id,
                        $exam_id
                    );

                    mysqli_stmt_execute(
                        $check_result_query
                    );

                    $check_result =
                        mysqli_stmt_get_result(
                            $check_result_query
                        );

                    $existing =
                        mysqli_fetch_assoc(
                            $check_result
                        );

                    mysqli_stmt_close(
                        $check_result_query
                    );


                    /* Update existing */

                    if ($existing) {

                        $result_id =
                            (int) $existing['result_id'];

                        $update_query = mysqli_prepare(
                            $conn,
                            "UPDATE results
                             SET
                                department_id = ?,
                                semester_id = ?,
                                marks = ?,
                                status = 'Published',
                                publish_date = NOW()
                             WHERE result_id = ?"
                        );

                        mysqli_stmt_bind_param(
                            $update_query,
                            "iidi",
                            $department_id,
                            $semester_id,
                            $marks,
                            $result_id
                        );

                        mysqli_stmt_execute(
                            $update_query
                        );

                        mysqli_stmt_close(
                            $update_query
                        );

                    } else {

                        /* Insert new result */

                        $insert_query = mysqli_prepare(
                            $conn,
                            "INSERT INTO results
                            (
                                exam_id,
                                department_id,
                                semester_id,
                                student_id,
                                marks,
                                publish_date,
                                status
                            )
                            VALUES
                            (
                                ?, ?, ?, ?, ?, NOW(), 'Published'
                            )"
                        );

                        mysqli_stmt_bind_param(
                            $insert_query,
                            "iiiid",
                            $exam_id,
                            $department_id,
                            $semester_id,
                            $student_id,
                            $marks
                        );

                        mysqli_stmt_execute(
                            $insert_query
                        );

                        mysqli_stmt_close(
                            $insert_query
                        );
                    }

                    $published_count++;
                }

                mysqli_stmt_close($mark_query);
            }


            /* =================================================
               STEP 4:
               Generate Final Result
            ================================================= */

            $final_student_count = 0;


            foreach ($student_ids as $student_id) {

                $course_query = mysqli_prepare(
                    $conn,
                    "SELECT marks
                     FROM results
                     WHERE student_id = ?
                     AND department_id = ?
                     AND semester_id = ?
                     AND status = 'Published'"
                );

                mysqli_stmt_bind_param(
                    $course_query,
                    "iii",
                    $student_id,
                    $department_id,
                    $semester_id
                );

                mysqli_stmt_execute(
                    $course_query
                );

                $course_result =
                    mysqli_stmt_get_result(
                        $course_query
                    );


                $total_marks = 0;
                $total_gpa = 0;

                $course_count = 0;
                $passed_course_count = 0;

                $has_fail = false;


                while (
                    $course =
                    mysqli_fetch_assoc($course_result)
                ) {

                    $marks =
                        (float) $course['marks'];

                    $gpa = 0;


                    /* GPA Calculation */

                    if ($marks >= 80) {

                        $gpa = 4.00;

                    } elseif ($marks >= 75) {

                        $gpa = 3.75;

                    } elseif ($marks >= 70) {

                        $gpa = 3.50;

                    } elseif ($marks >= 65) {

                        $gpa = 3.25;

                    } elseif ($marks >= 60) {

                        $gpa = 3.00;

                    } elseif ($marks >= 55) {

                        $gpa = 2.75;

                    } elseif ($marks >= 50) {

                        $gpa = 2.50;

                    } elseif ($marks >= 45) {

                        $gpa = 2.25;

                    } elseif ($marks >= 40) {

                        $gpa = 2.00;

                    } else {

                        $gpa = 0.00;
                        $has_fail = true;
                    }


                    $total_marks += $marks;

                    $course_count++;


                    /* Failed course CGPA te count hobe na */

                    if ($gpa > 0) {

                        $total_gpa += $gpa;

                        $passed_course_count++;
                    }
                }

                mysqli_stmt_close($course_query);


                if ($course_count == 0) {
                    continue;
                }


                /* CGPA */

                if ($passed_course_count > 0) {

                    $cgpa =
                        $total_gpa /
                        $passed_course_count;

                } else {

                    $cgpa = 0.00;
                }

                $cgpa =
                    round($cgpa, 2);


                /* Pass / Fail */

                if ($has_fail) {

                    $result_status = "Failed";

                } else {

                    $result_status = "Passed";
                }


                /* Check existing final result */

                $check_final = mysqli_prepare(
                    $conn,
                    "SELECT final_result_id
                     FROM final_results
                     WHERE student_id = ?
                     AND department_id = ?
                     AND semester_id = ?"
                );

                mysqli_stmt_bind_param(
                    $check_final,
                    "iii",
                    $student_id,
                    $department_id,
                    $semester_id
                );

                mysqli_stmt_execute($check_final);

                $final_check_result =
                    mysqli_stmt_get_result(
                        $check_final
                    );

                $existing_final =
                    mysqli_fetch_assoc(
                        $final_check_result
                    );

                mysqli_stmt_close(
                    $check_final
                );


                /* Update Final Result */

                if ($existing_final) {

                    $final_result_id =
                        (int)
                        $existing_final['final_result_id'];

                    $update_final =
                        mysqli_prepare(
                            $conn,
                            "UPDATE final_results
                             SET
                                total_marks = ?,
                                cgpa = ?,
                                result_status = ?,
                                published_at = NOW(),
                                status = 'Published'
                             WHERE final_result_id = ?"
                        );

                    mysqli_stmt_bind_param(
                        $update_final,
                        "ddsi",
                        $total_marks,
                        $cgpa,
                        $result_status,
                        $final_result_id
                    );

                    mysqli_stmt_execute(
                        $update_final
                    );

                    mysqli_stmt_close(
                        $update_final
                    );

                } else {

                    /* Insert Final Result */

                    $insert_final =
                        mysqli_prepare(
                            $conn,
                            "INSERT INTO final_results
                            (
                                student_id,
                                department_id,
                                semester_id,
                                total_marks,
                                cgpa,
                                merit_position,
                                result_status,
                                published_at,
                                status,
                                created_at
                            )
                            VALUES
                            (
                                ?, ?, ?, ?, ?, NULL,
                                ?, NOW(), 'Published', NOW()
                            )"
                        );

                    mysqli_stmt_bind_param(
                        $insert_final,
                        "iiidds",
                        $student_id,
                        $department_id,
                        $semester_id,
                        $total_marks,
                        $cgpa,
                        $result_status
                    );

                    mysqli_stmt_execute(
                        $insert_final
                    );

                    mysqli_stmt_close(
                        $insert_final
                    );
                }


                $final_student_count++;
            }


            /* =================================================
               STEP 5:
               Generate Merit Position
            ================================================= */

            $rank_query = mysqli_prepare(
                $conn,
                "SELECT
                    final_result_id
                 FROM final_results
                 WHERE department_id = ?
                 AND semester_id = ?
                 AND status = 'Published'
                 ORDER BY
                    cgpa DESC,
                    total_marks DESC"
            );

            mysqli_stmt_bind_param(
                $rank_query,
                "ii",
                $department_id,
                $semester_id
            );

            mysqli_stmt_execute($rank_query);

            $rank_result =
                mysqli_stmt_get_result(
                    $rank_query
                );


            $position = 1;


            while (
                $rank =
                mysqli_fetch_assoc($rank_result)
            ) {

                $final_result_id =
                    (int)
                    $rank['final_result_id'];


                $update_rank = mysqli_prepare(
                    $conn,
                    "UPDATE final_results
                     SET merit_position = ?
                     WHERE final_result_id = ?"
                );

                mysqli_stmt_bind_param(
                    $update_rank,
                    "ii",
                    $position,
                    $final_result_id
                );

                mysqli_stmt_execute(
                    $update_rank
                );

                mysqli_stmt_close(
                    $update_rank
                );


                $position++;
            }

            mysqli_stmt_close($rank_query);


            mysqli_commit($conn);


            $success =
                "Final Result Published Successfully! "
                . "$final_student_count student(s) result published. "
                . "CGPA, Pass/Fail and Merit Position generated automatically.";

        } catch (Exception $e) {

            mysqli_rollback($conn);

            $error =
                "Publish Failed: "
                . $e->getMessage();
        }
    }
}


/* =========================================================
   GET DEPARTMENTS
========================================================= */

$department_query = "
    SELECT department_id, department_name
    FROM departments
    ORDER BY department_name ASC
";

$department_result =
    mysqli_query(
        $conn,
        $department_query
    );


/* =========================================================
   GET SEMESTERS
========================================================= */

$semester_query = "
    SELECT semester_id, semester_name
    FROM semesters
    ORDER BY semester_id ASC
";

$semester_result =
    mysqli_query(
        $conn,
        $semester_query
    );

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Publish Final Result</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        body {
            background: #f4f7fb;
        }


        .sidebar {

            min-height: 100vh;

            background:
                linear-gradient(
                    180deg,
                    #1e63c6,
                    #114a9c
                );

            color: white;
        }


        .sidebar a {

            color: white;

            text-decoration: none;

            display: block;

            padding: 14px 20px;

            font-size: 18px;
        }


        .sidebar a:hover {

            background:
                rgba(255,255,255,.15);
        }


        .page-title {

            font-size: 38px;

            font-weight: 700;
        }


        .publish-card {

            border: none;

            border-radius: 15px;

            box-shadow:
                0 8px 30px
                rgba(0,0,0,.10);
        }


        .card-header-custom {

            background:
                linear-gradient(
                    90deg,
                    #1463c7,
                    #2d7de0
                );

            color: white;

            font-size: 24px;

            font-weight: bold;

            padding: 20px;
        }


        .publish-btn {

            font-size: 20px;

            font-weight: bold;

            padding: 13px;

            border-radius: 10px;
        }


        .info-box {

            background: #f8f9fa;

            border-radius: 10px;

            padding: 20px;

            border-left:
                5px solid #198754;
        }

    </style>

</head>


<body>


<div class="container-fluid">

    <div class="row">


        <!-- SIDEBAR -->

        <div
            class="col-md-2 p-0 sidebar"
        >

            <h2 class="text-center py-4">

                🎓 Admin Panel

            </h2>


            <a href="dashboard.php">

                🏠 Dashboard

            </a>


            <a href="publish-result.php">

                📤 Publish Result

            </a>


            <a href="logout.php">

                🚪 Logout

            </a>

        </div>



        <!-- MAIN CONTENT -->

        <div
            class="col-md-10 p-5"
        >


            <div class="mb-4">

                <div class="page-title">

                    🎓 Bulk Final Result Publish

                </div>


                <p class="text-muted fs-5">

                    Select Department and Semester to publish
                    all student results at once.

                </p>

            </div>



            <!-- SUCCESS -->

            <?php if (!empty($success)) { ?>

                <div
                    class="
                        alert
                        alert-success
                        alert-dismissible
                        fade
                        show
                    "
                >

                    <?php
                        echo htmlspecialchars($success);
                    ?>


                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

            <?php } ?>



            <!-- ERROR -->

            <?php if (!empty($error)) { ?>

                <div
                    class="
                        alert
                        alert-danger
                        alert-dismissible
                        fade
                        show
                    "
                >

                    <?php
                        echo htmlspecialchars($error);
                    ?>


                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

            <?php } ?>



            <!-- BULK PUBLISH CARD -->

            <div
                class="
                    card
                    publish-card
                    mt-4
                "
            >


                <div
                    class="card-header card-header-custom"
                >

                    🚀 Publish Department & Semester Result

                </div>



                <div class="card-body p-5">


                    <div class="info-box mb-4">

                        <h5>

                            📌 Automatic Process

                        </h5>


                        <ul class="mb-0">

                            <li>
                                All course marks will be approved automatically
                            </li>

                            <li>
                                All students result will be published together
                            </li>

                            <li>
                                CGPA will be calculated automatically
                            </li>

                            <li>
                                Failed course will not be counted in CGPA
                            </li>

                            <li>
                                Pass / Fail status will be generated
                            </li>

                            <li>
                                Merit Position will be generated automatically
                            </li>

                        </ul>

                    </div>



                    <form
                        method="POST"
                        onsubmit="
                            return confirm(
                                'Are you sure you want to publish all student results for this Department and Semester?'
                            );
                        "
                    >


                        <div class="row">


                            <!-- DEPARTMENT -->

                            <div
                                class="col-md-6 mb-4"
                            >

                                <label
                                    class="
                                        form-label
                                        fw-bold
                                    "
                                >

                                    🏢 Select Department

                                </label>


                                <select
                                    name="department_id"
                                    class="
                                        form-select
                                        form-select-lg
                                    "
                                    required
                                >

                                    <option value="">

                                        -- Select Department --

                                    </option>


                                    <?php

                                    while (
                                        $department =
                                        mysqli_fetch_assoc(
                                            $department_result
                                        )
                                    ) {

                                    ?>


                                        <option
                                            value="<?php
                                                echo
                                                $department[
                                                    'department_id'
                                                ];
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


                                    <?php } ?>


                                </select>

                            </div>



                            <!-- SEMESTER -->

                            <div
                                class="col-md-6 mb-4"
                            >

                                <label
                                    class="
                                        form-label
                                        fw-bold
                                    "
                                >

                                    📚 Select Semester

                                </label>


                                <select
                                    name="semester_id"
                                    class="
                                        form-select
                                        form-select-lg
                                    "
                                    required
                                >

                                    <option value="">

                                        -- Select Semester --

                                    </option>


                                    <?php

                                    while (
                                        $semester =
                                        mysqli_fetch_assoc(
                                            $semester_result
                                        )
                                    ) {

                                    ?>


                                        <option
                                            value="<?php
                                                echo
                                                $semester[
                                                    'semester_id'
                                                ];
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


                                    <?php } ?>


                                </select>

                            </div>


                        </div>



                        <!-- PUBLISH BUTTON -->

                        <div
                            class="d-grid mt-3"
                        >

                            <button
                                type="submit"
                                name="bulk_publish"
                                value="1"
                                class="
                                    btn
                                    btn-success
                                    publish-btn
                                "
                            >

                                🚀 Publish All Student Results

                            </button>

                        </div>


                    </form>


                </div>

            </div>


        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>