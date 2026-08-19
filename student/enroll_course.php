```php
<?php
session_start();

include '../config/database.php';
include '../config/session.php';

checkStudent();


/* ==================================================
   STUDENT ID
================================================== */

$student_id = $_SESSION['user_id'];

$message = "";
$messageType = "";


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
   ENROLL COURSE
================================================== */

if (isset($_POST['enroll_course'])) {

    $course_id = intval($_POST['course_id']);


    /* -----------------------------------------------
       CHECK COURSE
       Department + Semester based
    ------------------------------------------------ */

    $course_check = mysqli_query($conn, "

        SELECT
            course_id,
            course_name

        FROM courses

        WHERE course_id = '$course_id'

        AND department_id =
            '{$student['department_id']}'

        AND semester_id =
            '{$student['semester_id']}'

        LIMIT 1

    ");


    if (!$course_check) {

        $message =
            "Database error while checking course.";

        $messageType = "danger";

    }

    elseif (mysqli_num_rows($course_check) == 0) {

        $message =
            "This course is not available for your department and semester.";

        $messageType = "danger";

    }

    else {


        /* -----------------------------------------------
           CHECK ALREADY ENROLLED
        ------------------------------------------------ */

        $already_check = mysqli_query($conn, "

            SELECT id

            FROM student_courses

            WHERE student_id = '$student_id'

            AND course_id = '$course_id'

            LIMIT 1

        ");


        if (
            $already_check &&
            mysqli_num_rows($already_check) > 0
        ) {

            $message =
                "You are already enrolled in this course.";

            $messageType = "warning";

        }

        else {


            /* -----------------------------------------------
               INSERT ENROLLMENT
            ------------------------------------------------ */

            $insert = mysqli_query($conn, "

                INSERT INTO student_courses
                (
                    student_id,
                    course_id,
                    department_id,
                    semester_id
                )

                VALUES
                (
                    '$student_id',
                    '$course_id',
                    '{$student['department_id']}',
                    '{$student['semester_id']}'
                )

            ");


            if ($insert) {

                $message =
                    "Course enrolled successfully.";

                $messageType = "success";

            }

            else {

                $message =
                    "Failed to enroll course. Please try again.";

                $messageType = "danger";

            }

        }

    }

}


/* ==================================================
   GET AVAILABLE COURSES
   BASED ON DEPARTMENT + SEMESTER
================================================== */

$courses = mysqli_query($conn, "

    SELECT
        course_id,
        course_name

    FROM courses

    WHERE department_id =
        '{$student['department_id']}'

    AND semester_id =
        '{$student['semester_id']}'

    ORDER BY course_name ASC

");


/* ==================================================
   GET ENROLLED COURSES
================================================== */

$enrolled_courses = mysqli_query($conn, "

    SELECT

        sc.id,

        sc.course_id,

        sc.department_id,

        sc.semester_id,

        sc.created_at,

        c.course_name

    FROM student_courses sc

    INNER JOIN courses c
        ON sc.course_id = c.course_id

    WHERE sc.student_id = '$student_id'

    ORDER BY sc.created_at DESC

");

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
    Enroll Course | Student Panel
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


.topbar small {

    font-size: 14px;

}


/* ==================================================
   COMMON CARD
================================================== */

.card {

    border: none;

    border-radius: 15px;

    box-shadow:
        0 5px 15px
        rgba(0, 0, 0, 0.10);

}


/* ==================================================
   COURSE CARD
================================================== */

.course-card {

    transition: 0.3s;

}


.course-card:hover {

    transform:
        translateY(-4px);

}


.course-icon {

    font-size: 45px;

    color: #0d6efd;

    margin-bottom: 15px;

}


/* ==================================================
   STUDENT INFO
================================================== */

.student-info {

    font-size: 16px;

}


.student-info strong {

    display: block;

    margin-bottom: 5px;

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


    <!-- Student Profile -->

    <a href="profile.php">

        <i class="fa fa-user"></i>

        Student Profile

    </a>


    <!-- Enroll Course -->

    <a
        href="enroll_course.php"
        class="active"
    >

        <i class="fa fa-book"></i>

        Enroll Course

    </a>


    <!-- View Result -->

    <a href="result.php">

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

        <h3>

            <i class="fa fa-book text-primary"></i>

            Enroll Course

        </h3>


        <small class="text-muted">

            Select a course according to your
            department and semester.

        </small>

    </div>



    <!-- ==================================================
         STUDENT ACADEMIC INFORMATION
    ================================================== -->

    <div class="card mb-4">

        <div class="card-body">


            <div class="row student-info">


                <!-- Student ID -->

                <div class="col-md-4 mb-3">

                    <strong>

                        Student ID

                    </strong>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            $student['student_id']
                        );

                        ?>

                    </span>

                </div>


                <!-- Department -->

                <div class="col-md-4 mb-3">

                    <strong>

                        Department

                    </strong>

                    <span
                        class="badge bg-primary fs-6"
                    >

                        <?php

                        echo htmlspecialchars(
                            $student['department_name']
                        );

                        ?>

                    </span>

                </div>


                <!-- Semester -->

                <div class="col-md-4 mb-3">

                    <strong>

                        Semester

                    </strong>

                    <span
                        class="badge bg-success fs-6"
                    >

                        <?php

                        echo htmlspecialchars(
                            $student['semester_name']
                        );

                        ?>

                    </span>

                </div>


            </div>

        </div>

    </div>



    <!-- ==================================================
         MESSAGE
    ================================================== -->

    <?php

    if ($message != "") {

    ?>

        <div
            class="alert alert-<?php
                echo $messageType;
            ?> alert-dismissible fade show"
            role="alert"
        >

            <i class="fa fa-circle-info"></i>

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

    <?php

    }

    ?>



    <!-- ==================================================
         AVAILABLE COURSES
    ================================================== -->

    <div class="card mb-4">


        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">

                <i class="fa fa-book-open"></i>

                Available Courses

            </h5>

        </div>


        <div class="card-body">


            <?php

            if (
                $courses &&
                mysqli_num_rows($courses) > 0
            ) {

            ?>


                <div class="row">


                    <?php

                    while (
                        $course =
                        mysqli_fetch_assoc(
                            $courses
                        )
                    ) {


                        $course_id =
                            $course['course_id'];


                        /* ----------------------------------
                           CHECK ALREADY ENROLLED
                        ---------------------------------- */

                        $enrolled_check =
                            mysqli_query($conn, "

                                SELECT id

                                FROM student_courses

                                WHERE student_id =
                                    '$student_id'

                                AND course_id =
                                    '$course_id'

                                LIMIT 1

                            ");


                        $is_enrolled =
                            (
                                $enrolled_check &&
                                mysqli_num_rows(
                                    $enrolled_check
                                ) > 0
                            );

                    ?>


                        <div
                            class="col-md-6 col-lg-4 mb-4"
                        >


                            <div
                                class="card course-card h-100"
                            >


                                <div
                                    class="card-body text-center"
                                >


                                    <div
                                        class="course-icon"
                                    >

                                        <i
                                            class="fa fa-book"
                                        ></i>

                                    </div>


                                    <h5>

                                        <?php

                                        echo htmlspecialchars(
                                            $course['course_name']
                                        );

                                        ?>

                                    </h5>


                                    <p class="text-muted">

                                        Course ID:

                                        <?php

                                        echo htmlspecialchars(
                                            $course['course_id']
                                        );

                                        ?>

                                    </p>


                                    <?php

                                    if ($is_enrolled) {

                                    ?>


                                        <button
                                            type="button"
                                            class="btn btn-secondary"
                                            disabled
                                        >

                                            <i
                                                class="fa fa-check"
                                            ></i>

                                            Already Enrolled

                                        </button>


                                    <?php

                                    }

                                    else {

                                    ?>


                                        <form
                                            method="POST"
                                            action=""
                                        >


                                            <input
                                                type="hidden"
                                                name="course_id"
                                                value="<?php

                                                echo htmlspecialchars(
                                                    $course['course_id']
                                                );

                                                ?>"
                                            >


                                            <button
                                                type="submit"
                                                name="enroll_course"
                                                class="btn btn-success"
                                            >

                                                <i
                                                    class="fa fa-plus"
                                                ></i>

                                                Enroll

                                            </button>


                                        </form>


                                    <?php

                                    }

                                    ?>


                                </div>

                            </div>

                        </div>


                    <?php

                    }

                    ?>


                </div>


            <?php

            }

            else {

            ?>


                <div class="text-center py-5">


                    <i
                        class="fa fa-book-open fa-3x text-muted mb-3"
                    ></i>


                    <h5>

                        No Courses Available

                    </h5>


                    <p class="text-muted">

                        No course is currently available
                        for your department and semester.

                    </p>


                </div>


            <?php

            }

            ?>


        </div>

    </div>



    <!-- ==================================================
         MY ENROLLED COURSES
    ================================================== -->

    <div class="card">


        <div class="card-header bg-success text-white">

            <h5 class="mb-0">

                <i class="fa fa-check-circle"></i>

                My Enrolled Courses

            </h5>

        </div>


        <div class="card-body">


            <?php

            if (
                $enrolled_courses &&
                mysqli_num_rows(
                    $enrolled_courses
                ) > 0
            ) {

            ?>


                <div class="table-responsive">


                    <table
                        class="table table-bordered table-hover align-middle"
                    >


                        <thead class="table-light">

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    Course ID
                                </th>

                                <th>
                                    Course Name
                                </th>

                                <th>
                                    Department
                                </th>

                                <th>
                                    Semester
                                </th>

                                <th>
                                    Enrolled Date
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php

                            $sl = 1;


                            while (
                                $enrolled =
                                mysqli_fetch_assoc(
                                    $enrolled_courses
                                )
                            ) {

                            ?>


                                <tr>


                                    <td>

                                        <?php

                                        echo $sl++;

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $enrolled['course_id']
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <strong>

                                            <?php

                                            echo htmlspecialchars(
                                                $enrolled['course_name']
                                            );

                                            ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $student['department_name']
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $student['semester_name']
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo date(
                                            'd M Y',
                                            strtotime(
                                                $enrolled['created_at']
                                            )
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <span
                                            class="badge bg-success"
                                        >

                                            Enrolled

                                        </span>

                                    </td>


                                </tr>


                            <?php

                            }

                            ?>


                        </tbody>


                    </table>

                </div>


            <?php

            }

            else {

            ?>


                <div class="text-center py-4">


                    <i
                        class="fa fa-info-circle fa-2x text-muted mb-2"
                    ></i>


                    <p class="text-muted mb-0">

                        You have not enrolled in any course yet.

                    </p>


                </div>


            <?php

            }

            ?>


        </div>

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
```

**একটা জিনিস নিশ্চিত করুন:** আপনার `courses` table-এ এই ৪টি column থাকতে হবে:

```text
course_id
course_name
department_id
semester_id
```

কারণ course filtering এইভাবে হচ্ছে:

```php
WHERE department_id = student_department_id
AND semester_id = student_semester_id
```

তাহলেই একজন **CSE 5th semester** student শুধু **CSE + 5th semester-এর course** দেখতে পারবে এবং সেগুলো থেকে enroll করতে পারবে।
