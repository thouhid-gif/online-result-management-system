<?php

session_start();

include '../config/database.php';
include '../config/session.php';

checkAdmin();

$message = "";
$messageType = "";


/* ==================================================
   ADD EXAM
================================================== */

if (isset($_POST['add_exam'])) {

    $exam_name = mysqli_real_escape_string(
        $conn,
        $_POST['exam_name']
    );

    $department_id = intval(
        $_POST['department_id']
    );

    $semester_id = intval(
        $_POST['semester_id']
    );

    $course_id = intval(
        $_POST['course_id']
    );

    $exam_date = mysqli_real_escape_string(
        $conn,
        $_POST['exam_date']
    );

    $total_marks = intval(
        $_POST['total_marks']
    );


    /* ==============================================
       CHECK DUPLICATE EXAM
    ============================================== */

    $check = mysqli_query($conn, "

        SELECT *
        FROM exams

        WHERE exam_name = '$exam_name'

        AND course_id = '$course_id'

    ");


    if (mysqli_num_rows($check) > 0) {

        $message =
            "This exam already exists for this course.";

        $messageType = "danger";

    } else {


        /* ==========================================
           INSERT EXAM
        ========================================== */

        $insert = mysqli_query($conn, "

            INSERT INTO exams

            (
                exam_name,
                department_id,
                semester_id,
                course_id,
                exam_date,
                total_marks
            )

            VALUES

            (
                '$exam_name',
                '$department_id',
                '$semester_id',
                '$course_id',
                '$exam_date',
                '$total_marks'
            )

        ");


        if ($insert) {

            $message =
                "Exam Added Successfully.";

            $messageType = "success";

        } else {

            $message =
                "Failed to add exam: "
                . mysqli_error($conn);

            $messageType = "danger";

        }

    }

}


/* ==================================================
   DELETE EXAM
================================================== */

if (isset($_GET['delete'])) {

    $id = intval(
        $_GET['delete']
    );


    mysqli_query($conn, "

        DELETE FROM exams

        WHERE exam_id = '$id'

    ");


    header("Location: exam.php");

    exit();

}


/* ==================================================
   LOAD DEPARTMENTS
================================================== */

$departments = mysqli_query($conn, "

    SELECT *
    FROM departments

    ORDER BY department_name

");


/* ==================================================
   LOAD SEMESTERS
================================================== */

$semesters = mysqli_query($conn, "

    SELECT *
    FROM semesters

    ORDER BY semester_id

");


/* ==================================================
   LOAD COURSES
================================================== */

$courses = mysqli_query($conn, "

    SELECT *
    FROM courses

    ORDER BY course_name

");


/* ==================================================
   SEARCH
================================================== */

$search = "";


if (isset($_GET['search'])) {

    $search = mysqli_real_escape_string(
        $conn,
        $_GET['search']
    );


    $exams = mysqli_query($conn, "

        SELECT

            e.*,

            d.department_name,

            s.semester_name,

            c.course_name

        FROM exams e

        LEFT JOIN departments d
            ON e.department_id =
               d.department_id

        LEFT JOIN semesters s
            ON e.semester_id =
               s.semester_id

        LEFT JOIN courses c
            ON e.course_id =
               c.course_id

        WHERE

            e.exam_name LIKE '%$search%'

        OR

            c.course_name LIKE '%$search%'

        OR

            d.department_name LIKE '%$search%'

        ORDER BY
            e.exam_id DESC

    ");

} else {

    $exams = mysqli_query($conn, "

        SELECT

            e.*,

            d.department_name,

            s.semester_name,

            c.course_name

        FROM exams e

        LEFT JOIN departments d
            ON e.department_id =
               d.department_id

        LEFT JOIN semesters s
            ON e.semester_id =
               s.semester_id

        LEFT JOIN courses c
            ON e.course_id =
               c.course_id

        ORDER BY
            e.exam_id DESC

    ");

}


/* ==================================================
   TOTAL EXAMS
================================================== */

$totalExamResult = mysqli_query(
    $conn,
    "SELECT * FROM exams"
);

$totalExam =
    mysqli_num_rows($totalExamResult);

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
    Exam Management
</title>


<!-- Bootstrap -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<!-- Font Awesome -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


<style>

/* ==================================================
   BODY
================================================== */

body {

    background: #f4f7fb;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

}


/* ==================================================
   CARD
================================================== */

.card {

    border: none;

    border-radius: 15px;

}


.shadow {

    box-shadow:
        0 5px 18px
        rgba(0, 0, 0, 0.10)
        !important;

}


/* ==================================================
   HEADER
================================================== */

.page-title {

    font-weight: 700;

}


/* ==================================================
   FORM LABEL
================================================== */

.form-label {

    font-weight: 600;

}


/* ==================================================
   TABLE
================================================== */

.table th {

    white-space: nowrap;

    vertical-align: middle;

}


.table td {

    vertical-align: middle;

}


/* ==================================================
   BADGE
================================================== */

.exam-badge {

    font-size: 13px;

    padding:
        7px 10px;

}


/* ==================================================
   SEARCH
================================================== */

.search-box {

    max-width: 400px;

}


/* ==================================================
   RESPONSIVE
================================================== */

@media (max-width: 768px) {

    .container {

        margin-top: 15px !important;

    }

}

</style>

</head>


<body>


<div class="container mt-4 mb-5">


    <!-- ==================================================
         TOP HEADER
    ================================================== -->

    <div class="row mb-4">


        <div class="col-md-4">

            <div
                class="card bg-primary
                text-white shadow"
            >

                <div class="card-body">

                    <div
                        class="d-flex
                        justify-content-between
                        align-items-center"
                    >

                        <div>

                            <h6 class="mb-2">

                                Total Exams

                            </h6>

                            <h2 class="mb-0">

                                <?php

                                echo $totalExam;

                                ?>

                            </h2>

                        </div>


                        <i
                            class="fa fa-file-alt
                            fa-3x opacity-75"
                        ></i>

                    </div>

                </div>

            </div>

        </div>


        <div
            class="col-md-8
            d-flex justify-content-end
            align-items-center"
        >

            <a
                href="dashboard.php"
                class="btn btn-secondary"
            >

                <i class="fa fa-arrow-left"></i>

                Back Dashboard

            </a>

        </div>


    </div>



    <!-- ==================================================
         MESSAGE
    ================================================== -->

    <?php if ($message != "") { ?>

        <div
            class="alert
            alert-<?php echo $messageType; ?>
            alert-dismissible fade show"
        >

            <i
                class="fa
                <?php

                if ($messageType == "success") {

                    echo "fa-check-circle";

                } else {

                    echo "fa-exclamation-circle";

                }

                ?>"
            ></i>

            <?php

            echo htmlspecialchars(
                $message
            );

            ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php } ?>



    <!-- ==================================================
         ADD EXAM
    ================================================== -->

    <div class="card shadow mb-4">


        <div
            class="card-header
            bg-primary text-white"
        >

            <h4 class="mb-0">

                <i class="fa fa-file-circle-plus"></i>

                Add New Exam

            </h4>

        </div>


        <div class="card-body">


            <form
                method="POST"
                action=""
            >


                <div class="row">


                    <!-- ==================================
                         EXAM NAME
                    ================================== -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Exam Name

                        </label>


                        <select
                            name="exam_name"
                            class="form-select"
                            required
                        >

                            <option value="">

                                Select Exam

                            </option>


                            <option
                                value="Term Test 1"
                            >

                                Term Test 1

                            </option>


                            <option
                                value="Term Test 2"
                            >

                                Term Test 2

                            </option>


                            <option
                                value="Final Exam"
                            >

                                Final Exam

                            </option>

                        </select>

                    </div>



                    <!-- ==================================
                         EXAM DATE
                    ================================== -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Exam Date

                        </label>


                        <input
                            type="date"
                            name="exam_date"
                            class="form-control"
                            required
                        >

                    </div>



                    <!-- ==================================
                         DEPARTMENT
                    ================================== -->

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Department

                        </label>


                        <select
                            name="department_id"
                            class="form-select"
                            required
                        >

                            <option value="">

                                Select Department

                            </option>


                            <?php

                            while (
                                $d =
                                mysqli_fetch_assoc(
                                    $departments
                                )
                            ) {

                            ?>

                                <option
                                    value="<?php
                                        echo $d[
                                            'department_id'
                                        ];
                                    ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $d[
                                            'department_name'
                                        ]
                                    );

                                    ?>

                                </option>

                            <?php

                            }

                            ?>

                        </select>

                    </div>



                    <!-- ==================================
                         SEMESTER
                    ================================== -->

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Semester

                        </label>


                        <select
                            name="semester_id"
                            class="form-select"
                            required
                        >

                            <option value="">

                                Select Semester

                            </option>


                            <?php

                            while (
                                $s =
                                mysqli_fetch_assoc(
                                    $semesters
                                )
                            ) {

                            ?>

                                <option
                                    value="<?php
                                        echo $s[
                                            'semester_id'
                                        ];
                                    ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $s[
                                            'semester_name'
                                        ]
                                    );

                                    ?>

                                </option>

                            <?php

                            }

                            ?>

                        </select>

                    </div>



                    <!-- ==================================
                         COURSE
                    ================================== -->

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Course

                        </label>


                        <select
                            name="course_id"
                            class="form-select"
                            required
                        >

                            <option value="">

                                Select Course

                            </option>


                            <?php

                            while (
                                $c =
                                mysqli_fetch_assoc(
                                    $courses
                                )
                            ) {

                            ?>

                                <option
                                    value="<?php
                                        echo $c[
                                            'course_id'
                                        ];
                                    ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $c[
                                            'course_name'
                                        ]
                                    );

                                    ?>

                                </option>

                            <?php

                            }

                            ?>

                        </select>

                    </div>



                    <!-- ==================================
                         TOTAL MARKS
                    ================================== -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Total Marks

                        </label>


                        <input
                            type="number"
                            name="total_marks"
                            class="form-control"
                            min="1"
                            max="1000"
                            placeholder="Enter total marks"
                            required
                        >

                    </div>



                    <!-- ==================================
                         SAVE BUTTON
                    ================================== -->

                    <div
                        class="col-md-6 mb-3
                        d-flex align-items-end"
                    >

                        <button
                            type="submit"
                            name="add_exam"
                            class="btn btn-success
                            w-100"
                        >

                            <i class="fa fa-save"></i>

                            Save Exam

                        </button>

                    </div>


                </div>


            </form>

        </div>

    </div>



    <!-- ==================================================
         EXAM LIST
    ================================================== -->

    <div class="card shadow">


        <div class="card-header bg-dark text-white">


            <div
                class="d-flex
                justify-content-between
                align-items-center
                flex-wrap gap-2"
            >

                <h5 class="mb-0">

                    <i class="fa fa-list"></i>

                    Exam List

                </h5>


                <!-- SEARCH -->

                <form
                    method="GET"
                    class="d-flex search-box"
                >

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search exam or course..."
                        value="<?php
                            echo htmlspecialchars(
                                $search
                            );
                        ?>"
                    >


                    <button
                        class="btn btn-light ms-2"
                        type="submit"
                    >

                        <i class="fa fa-search"></i>

                    </button>


                    <?php if ($search != "") { ?>

                        <a
                            href="exam.php"
                            class="btn btn-warning ms-2"
                        >

                            <i
                                class="fa fa-refresh"
                            ></i>

                        </a>

                    <?php } ?>

                </form>


            </div>

        </div>



        <div class="card-body">


            <div class="table-responsive">


                <table
                    class="table
                    table-bordered
                    table-hover
                    text-center"
                >


                    <thead
                        class="table-primary"
                    >

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Exam Name
                            </th>

                            <th>
                                Department
                            </th>

                            <th>
                                Semester
                            </th>

                            <th>
                                Course
                            </th>

                            <th>
                                Exam Date
                            </th>

                            <th>
                                Total Marks
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    if (
                        $exams &&
                        mysqli_num_rows(
                            $exams
                        ) > 0
                    ) {

                        $sl = 1;


                        while (
                            $exam =
                            mysqli_fetch_assoc(
                                $exams
                            )
                        ) {


                            /* ==========================
                               EXAM BADGE
                            ========================== */

                            if (
                                $exam['exam_name']
                                == 'Final Exam'
                            ) {

                                $badge =
                                    'bg-danger';

                            } elseif (
                                $exam['exam_name']
                                == 'Term Test 2'
                            ) {

                                $badge =
                                    'bg-warning text-dark';

                            } else {

                                $badge =
                                    'bg-primary';

                            }

                    ?>

                        <tr>


                            <!-- Serial -->

                            <td>

                                <?php

                                echo $sl++;

                                ?>

                            </td>


                            <!-- Exam Name -->

                            <td>

                                <span
                                    class="badge
                                    <?php
                                        echo $badge;
                                    ?>
                                    exam-badge"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $exam[
                                            'exam_name'
                                        ]
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- Department -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $exam[
                                        'department_name'
                                    ]
                                );

                                ?>

                            </td>


                            <!-- Semester -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $exam[
                                        'semester_name'
                                    ]
                                );

                                ?>

                            </td>


                            <!-- Course -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $exam[
                                        'course_name'
                                    ]
                                );

                                ?>

                            </td>


                            <!-- Exam Date -->

                            <td>

                                <?php

                                if (
                                    !empty(
                                        $exam[
                                            'exam_date'
                                        ]
                                    )
                                ) {

                                    echo date(
                                        'd M Y',
                                        strtotime(
                                            $exam[
                                                'exam_date'
                                            ]
                                        )
                                    );

                                } else {

                                    echo "N/A";

                                }

                                ?>

                            </td>


                            <!-- Total Marks -->

                            <td>

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $exam[
                                            'total_marks'
                                        ]
                                    );

                                ?>

                                </strong>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <a
                                    href="exam.php?delete=<?php
                                        echo $exam[
                                            'exam_id'
                                        ];
                                    ?>"
                                    class="btn btn-sm
                                    btn-danger"
                                    onclick="
                                        return confirm(
                                            'Are you sure you want to delete this exam?'
                                        );
                                    "
                                >

                                    <i
                                        class="fa fa-trash"
                                    ></i>

                                    Delete

                                </a>

                            </td>


                        </tr>


                    <?php

                        }

                    } else {

                    ?>

                        <tr>

                            <td
                                colspan="8"
                                class="text-center
                                text-muted py-4"
                            >

                                <i
                                    class="fa
                                    fa-folder-open
                                    fa-2x mb-2"
                                ></i>

                                <br>

                                No Exam Found.

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


</div>



<!-- Bootstrap JS -->

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>