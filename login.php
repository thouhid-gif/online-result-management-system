<?php

session_start();

include 'config/database.php';
include 'config/functions.php';

$message = "";
$messageType = "";

// ===========================
// GET ROLE
// ===========================

$role = "";

if (isset($_GET['role'])) {
    $role = clean($_GET['role']);
}


// ===========================
// LOGIN PROCESS
// ===========================

if (isset($_POST['login'])) {

    $role = clean($_POST['role']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];


    // ===========================
    // ADMIN LOGIN
    // ===========================

    if ($role == "admin") {

        $query = mysqli_query(
            $conn,
            "SELECT * FROM admins
             WHERE email='$email'
             LIMIT 1"
        );

        if (mysqli_num_rows($query) == 1) {

            $admin = mysqli_fetch_assoc($query);

            // Plain password OR hashed password
            if (
                $password == $admin['password'] ||
                password_verify($password, $admin['password'])
            ) {

                // Admin session
                $_SESSION['user_id'] =
                    $admin['admin_id'];

                $_SESSION['role'] =
                    "admin";

                $_SESSION['name'] =
                    $admin['full_name'];

                // Admin dashboard
                header(
                    "Location: admin/dashboard.php"
                );

                exit();

            } else {

                $message =
                    "Incorrect Password.";

                $messageType =
                    "danger";
            }

        } else {

            $message =
                "Admin Account Not Found.";

            $messageType =
                "danger";
        }
    }


    // ===========================
    // TEACHER LOGIN
    // ===========================

    elseif ($role == "teacher") {

        $query = mysqli_query(
            $conn,
            "SELECT * FROM teachers
             WHERE email='$email'
             AND status='Active'
             LIMIT 1"
        );

        if (mysqli_num_rows($query) == 1) {

            $teacher =
                mysqli_fetch_assoc($query);


            // Check password
            if (
                password_verify(
                    $password,
                    $teacher['password']
                )
            ) {

                // ===========================
                // TEACHER SESSION
                // ===========================

                $_SESSION['teacher_id'] =
                    $teacher['teacher_id'];

                $_SESSION['teacher_name'] =
                    $teacher['full_name'];

                $_SESSION['teacher_email'] =
                    $teacher['email'];

                $_SESSION['role'] =
                    "teacher";


                // ===========================
                // TEACHER DASHBOARD
                // ===========================

                header(
                    "Location: teacher/dashboard.php"
                );

                exit();

            } else {

                $message =
                    "Incorrect Password.";

                $messageType =
                    "danger";
            }

        } else {

            $message =
                "Teacher account not approved or not found.";

            $messageType =
                "warning";
        }
    }


    // ===========================
    // STUDENT LOGIN
    // ===========================

    elseif ($role == "student") {

        $query = mysqli_query(
            $conn,
            "SELECT * FROM students
             WHERE email='$email'
             AND status='Active'
             LIMIT 1"
        );

        if (mysqli_num_rows($query) == 1) {

            $student =
                mysqli_fetch_assoc($query);


            // Check password
            if (
                password_verify(
                    $password,
                    $student['password']
                )
            ) {

                // Student session
                $_SESSION['user_id'] = $student['student_id'];
                $_SESSION['student_id'] =
                    $student['student_id'];

                $_SESSION['student_name'] =
                    $student['full_name'];

                $_SESSION['student_email'] =
                    $student['email'];

                $_SESSION['role'] =
                    "student";


                // Student dashboard
                header(
                    "Location: student/dashboard.php"
                );

                exit();

            } else {

                $message =
                    "Incorrect Password.";

                $messageType =
                    "danger";
            }

        } else {

            $message =
                "Student account not found.";

            $messageType =
                "warning";
        }
    }


    // ===========================
    // INVALID ROLE
    // ===========================

    else {

        $message =
            "Please select a valid user type.";

        $messageType =
            "warning";
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Login | Online Result Management System
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

/* ===========================
   BODY
=========================== */

body {

    background:
    linear-gradient(
        135deg,
        #0d6efd,
        #4facfe
    );

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    font-family:
    Arial,
    Helvetica,
    sans-serif;

}


/* ===========================
   LOGIN CARD
=========================== */

.login-card {

    width: 430px;

    border: none;

    border-radius: 15px;

    overflow: hidden;

    box-shadow:
    0 10px 30px
    rgba(0,0,0,.25);

}


/* ===========================
   CARD HEADER
=========================== */

.card-header {

    background:
    #0d6efd;

    color:
    white;

    font-size:
    28px;

    font-weight:
    bold;

    text-align:
    center;

    padding:
    20px;

}


/* ===========================
   FORM LABEL
=========================== */

.form-label {

    font-weight:
    600;

}


/* ===========================
   LOGIN BUTTON
=========================== */

.btn-login {

    background:
    #0d6efd;

    color:
    white;

    font-weight:
    bold;

    padding:
    11px;

}


.btn-login:hover {

    background:
    #084298;

    color:
    white;

}


/* ===========================
   INPUT
=========================== */

.form-control,
.form-select {

    padding:
    11px;

    border-radius:
    8px;

}


/* ===========================
   LINKS
=========================== */

a {

    color:
    #0d6efd;

}


/* ===========================
   RESPONSIVE
=========================== */

@media(max-width:500px) {

    .login-card {

        width:
        95%;

    }

}

</style>

</head>


<body>


<div class="card login-card">


    <!-- ===========================
         HEADER
    =========================== -->

    <div class="card-header">

        <i
        class="fa-solid fa-user-lock">
        </i>

        System Login

    </div>


    <!-- ===========================
         BODY
    =========================== -->

    <div class="card-body p-4">


        <!-- MESSAGE -->

        <?php

        if ($message != "") {

        ?>

            <div
            class="alert alert-<?php
            echo $messageType;
            ?>">

                <?php
                echo htmlspecialchars(
                    $message
                );
                ?>

            </div>

        <?php

        }

        ?>


        <!-- ===========================
             LOGIN FORM
        =========================== -->

        <form
        action=""
        method="POST">


            <!-- USER TYPE -->

            <div class="mb-3">

                <label
                class="form-label">

                    User Type

                </label>


                <select
                name="role"
                class="form-select"
                required>


                    <option
                    value="">

                        Select User

                    </option>


                    <option
                    value="admin"
                    <?php

                    if ($role == "admin")
                        echo "selected";

                    ?>>

                        Admin

                    </option>


                    <option
                    value="teacher"
                    <?php

                    if ($role == "teacher")
                        echo "selected";

                    ?>>

                        Teacher

                    </option>


                    <option
                    value="student"
                    <?php

                    if ($role == "student")
                        echo "selected";

                    ?>>

                        Student

                    </option>


                </select>

            </div>


            <!-- EMAIL -->

            <div class="mb-3">

                <label
                class="form-label">

                    Email

                </label>


                <input

                type="email"

                name="email"

                class="form-control"

                placeholder="Enter Email"

                required>

            </div>


            <!-- PASSWORD -->

            <div class="mb-3">

                <label
                class="form-label">

                    Password

                </label>


                <input

                type="password"

                name="password"

                class="form-control"

                placeholder="Enter Password"

                required>

            </div>


            <!-- LOGIN BUTTON -->

            <div class="d-grid">

                <button

                type="submit"

                name="login"

                class="btn btn-login">

                    <i
                    class="fa-solid fa-right-to-bracket">
                    </i>

                    Login

                </button>

            </div>


        </form>


        <hr>


        <!-- ===========================
             REGISTRATION LINKS
        =========================== -->

        <div
        class="text-center">


            <a
            href="teacher-register.php">

                Teacher Registration

            </a>


            |

            <a
            href="student-register.php">

                Student Registration

            </a>


            <br>
            <br>


            <a
            href="index.php">

                ← Back to Home

            </a>


        </div>


    </div>

</div>


<!-- Bootstrap JS -->

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>