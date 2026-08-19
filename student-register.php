<?php

session_start();

include "config/database.php";


// ======================================================
// MESSAGE
// ======================================================

$message = "";
$message_type = "";


// ======================================================
// FORM SUBMIT
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ==================================================
    // GET FORM DATA
    // ==================================================

    $student_roll = trim($_POST['student_roll'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $department_id = (int)($_POST['department_id'] ?? 0);
    $semester_id = (int)($_POST['semester_id'] ?? 0);

    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';


    // ==================================================
    // VALIDATION
    // ==================================================

    if (
        $student_roll === '' ||
        $full_name === '' ||
        $email === '' ||
        $phone === '' ||
        $department_id <= 0 ||
        $semester_id <= 0 ||
        $password === '' ||
        $confirm_password === ''
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "danger";

    } elseif ($password !== $confirm_password) {

        $message = "Password and Confirm Password do not match.";
        $message_type = "danger";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "danger";

    } else {

        // ==================================================
        // CHECK EMAIL
        // ==================================================

        $stmt = mysqli_prepare(
            $conn,
            "SELECT student_id
             FROM students
             WHERE email = ?
             LIMIT 1"
        );

        $email_exists = false;

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $email
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $email_exists = true;
            }

            mysqli_stmt_close($stmt);
        }


        // ==================================================
        // CHECK STUDENT ROLL
        // ==================================================

        $stmt = mysqli_prepare(
            $conn,
            "SELECT student_id
             FROM students
             WHERE student_roll = ?
             LIMIT 1"
        );

        $roll_exists = false;

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $student_roll
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $roll_exists = true;
            }

            mysqli_stmt_close($stmt);
        }


        // ==================================================
        // DUPLICATE EMAIL
        // ==================================================

        if ($email_exists) {

            $message = "This email is already registered.";
            $message_type = "danger";

        } elseif ($roll_exists) {

            // ==================================================
            // DUPLICATE ROLL
            // ==================================================

            $message = "This student roll is already registered.";
            $message_type = "danger";

        } else {

            // ==================================================
            // PASSWORD HASH
            // ==================================================

            $hashed_password =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            // ==================================================
            // PHOTO UPLOAD
            // ==================================================

            $photo = "";

            if (
                isset($_FILES['photo']) &&
                $_FILES['photo']['error'] === UPLOAD_ERR_OK
            ) {

                $upload_dir =
                    "assets/uploads/students/";


                // Create upload directory

                if (!is_dir($upload_dir)) {

                    mkdir(
                        $upload_dir,
                        0777,
                        true
                    );
                }


                $original_name =
                    $_FILES['photo']['name'];

                $tmp_name =
                    $_FILES['photo']['tmp_name'];

                $extension =
                    strtolower(
                        pathinfo(
                            $original_name,
                            PATHINFO_EXTENSION
                        )
                    );


                $allowed_extensions = [
                    "jpg",
                    "jpeg",
                    "png",
                    "webp"
                ];


                if (
                    in_array(
                        $extension,
                        $allowed_extensions,
                        true
                    )
                ) {

                    $photo =
                        "student_" .
                        time() .
                        "_" .
                        mt_rand(1000, 9999) .
                        "." .
                        $extension;


                    move_uploaded_file(
                        $tmp_name,
                        $upload_dir . $photo
                    );
                }
            }


            // ==================================================
            // INSERT STUDENT
            // IMPORTANT: STATUS = Pending
            // ==================================================

            $stmt = mysqli_prepare(
                $conn,

                "INSERT INTO students
                (
                    student_roll,
                    full_name,
                    email,
                    phone,
                    department_id,
                    semester_id,
                    password,
                    photo,
                    status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    'Pending'
                )"
            );


            if ($stmt) {

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssiiss",
                    $student_roll,
                    $full_name,
                    $email,
                    $phone,
                    $department_id,
                    $semester_id,
                    $hashed_password,
                    $photo
                );


                if (mysqli_stmt_execute($stmt)) {

                    $message =
                        "Registration successful! Your account is waiting for Admin approval.";

                    $message_type = "success";


                    // Clear form after successful registration

                    $_POST = [];

                } else {

                    $message =
                        "Registration failed. Please try again.";

                    $message_type = "danger";
                }


                mysqli_stmt_close($stmt);

            } else {

                $message =
                    "Database error. Please try again.";

                $message_type = "danger";
            }
        }
    }
}


// ======================================================
// GET DEPARTMENTS
// ======================================================

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
        $row =
        mysqli_fetch_assoc($department_query)
    ) {

        $departments[] = $row;
    }
}


// ======================================================
// GET SEMESTERS
// ======================================================

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
        $row =
        mysqli_fetch_assoc($semester_query)
    ) {

        $semesters[] = $row;
    }
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
    Student Registration
</title>


<style>

/* =====================================================
   RESET
===================================================== */

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    padding: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #eef4ff,
            #f8fbff
        );

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;
}


/* =====================================================
   CONTAINER
===================================================== */

.container {

    width: 95%;

    max-width: 850px;

    margin: 40px auto;
}


/* =====================================================
   CARD
===================================================== */

.card {

    background: white;

    border-radius: 16px;

    padding: 35px;

    box-shadow:
        0 10px 35px
        rgba(0, 0, 0, 0.10);
}


/* =====================================================
   HEADER
===================================================== */

.header {

    text-align: center;

    margin-bottom: 30px;
}


.header h1 {

    margin: 0;

    color: #172033;

    font-size: 30px;
}


.header p {

    margin-top: 8px;

    color: #6b7280;
}


/* =====================================================
   MESSAGE
===================================================== */

.message {

    padding: 14px 17px;

    border-radius: 8px;

    margin-bottom: 22px;

    line-height: 1.5;
}


.message.success {

    background: #dcfce7;

    color: #166534;

    border:
        1px solid #86efac;
}


.message.danger {

    background: #fee2e2;

    color: #991b1b;

    border:
        1px solid #fca5a5;
}


/* =====================================================
   FORM GRID
===================================================== */

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


.form-group.full {

    grid-column: 1 / -1;
}


/* =====================================================
   LABEL
===================================================== */

label {

    margin-bottom: 7px;

    font-weight: 600;

    color: #374151;

    font-size: 14px;
}


/* =====================================================
   INPUT & SELECT
===================================================== */

input,
select {

    width: 100%;

    padding: 12px 13px;

    border:
        1px solid #d1d5db;

    border-radius: 8px;

    font-size: 15px;

    outline: none;

    background: white;
}


input:focus,
select:focus {

    border-color: #2563eb;

    box-shadow:
        0 0 0 3px
        rgba(37, 99, 235, 0.10);
}


/* =====================================================
   PHOTO
===================================================== */

.photo-input {

    padding: 10px;
}


/* =====================================================
   APPROVAL NOTICE
===================================================== */

.approval-note {

    margin-top: 20px;

    padding: 15px;

    background: #eff6ff;

    border:
        1px solid #bfdbfe;

    color: #1e40af;

    border-radius: 8px;

    font-size: 14px;

    line-height: 1.6;
}


/* =====================================================
   BUTTON
===================================================== */

.submit-btn {

    width: 100%;

    margin-top: 25px;

    padding: 14px;

    border: none;

    border-radius: 8px;

    background: #2563eb;

    color: white;

    font-size: 16px;

    font-weight: 700;

    cursor: pointer;
}


.submit-btn:hover {

    background: #1d4ed8;
}


/* =====================================================
   LOGIN LINK
===================================================== */

.login-link {

    text-align: center;

    margin-top: 22px;

    color: #6b7280;
}


.login-link a {

    color: #2563eb;

    font-weight: 600;

    text-decoration: none;
}


.login-link a:hover {

    text-decoration: underline;
}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 700px) {

    .container {

        width: 94%;

        margin: 20px auto;
    }


    .card {

        padding: 22px;
    }


    .form-grid {

        grid-template-columns: 1fr;
    }


    .form-group.full {

        grid-column: auto;
    }


    .header h1 {

        font-size: 25px;
    }

}

</style>

</head>


<body>


<div class="container">


    <div class="card">


        <!-- =================================================
             HEADER
        ================================================== -->

        <div class="header">

            <h1>
                Student Registration
            </h1>

            <p>
                Create your student account
            </p>

        </div>



        <!-- =================================================
             MESSAGE
        ================================================== -->

        <?php if (!empty($message)) { ?>

            <div
                class="message
                <?php
                echo htmlspecialchars(
                    $message_type
                );
                ?>"
            >

                <?php

                echo htmlspecialchars(
                    $message
                );

                ?>

            </div>

        <?php } ?>



        <!-- =================================================
             REGISTRATION FORM
        ================================================== -->

        <form
            method="POST"
            action="student-register.php"
            enctype="multipart/form-data"
        >


            <div class="form-grid">


                <!-- STUDENT ROLL -->

                <div class="form-group">

                    <label>
                        Student Roll *
                    </label>

                    <input
                        type="text"
                        name="student_roll"
                        placeholder="Enter student roll"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST['student_roll']
                            ?? ''
                        );
                        ?>"
                        required
                    >

                </div>



                <!-- FULL NAME -->

                <div class="form-group">

                    <label>
                        Full Name *
                    </label>

                    <input
                        type="text"
                        name="full_name"
                        placeholder="Enter full name"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST['full_name']
                            ?? ''
                        );
                        ?>"
                        required
                    >

                </div>



                <!-- EMAIL -->

                <div class="form-group">

                    <label>
                        Email *
                    </label>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter email"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST['email']
                            ?? ''
                        );
                        ?>"
                        required
                    >

                </div>



                <!-- PHONE -->

                <div class="form-group">

                    <label>
                        Phone *
                    </label>

                    <input
                        type="text"
                        name="phone"
                        placeholder="Enter phone number"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST['phone']
                            ?? ''
                        );
                        ?>"
                        required
                    >

                </div>



                <!-- DEPARTMENT -->

                <div class="form-group">

                    <label>
                        Department *
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
                        ) { ?>

                            <option
                                value="<?php
                                echo (int)
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

                <div class="form-group">

                    <label>
                        Semester *
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
                        ) { ?>

                            <option
                                value="<?php
                                echo (int)
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



                <!-- PASSWORD -->

                <div class="form-group">

                    <label>
                        Password *
                    </label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Minimum 6 characters"
                        required
                    >

                </div>



                <!-- CONFIRM PASSWORD -->

                <div class="form-group">

                    <label>
                        Confirm Password *
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Confirm password"
                        required
                    >

                </div>



                <!-- PHOTO -->

                <div class="form-group full">

                    <label>
                        Student Photo
                    </label>

                    <input
                        type="file"
                        name="photo"
                        class="photo-input"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                </div>


            </div>



            <!-- =================================================
                 APPROVAL NOTICE
            ================================================== -->

            <div class="approval-note">

                <strong>
                    Important:
                </strong>

                After registration, your account will remain
                <strong>Pending</strong>.

                An Admin must approve your account before
                you can login.

            </div>



            <!-- =================================================
                 REGISTER BUTTON
            ================================================== -->

            <button
                type="submit"
                class="submit-btn"
            >

                📝 Register

            </button>


        </form>



        <!-- =================================================
             LOGIN
        ================================================== -->

        <div class="login-link">

            Already have an account?

            <a href="login.php?role=student">

                Student Login

            </a>

        </div>


    </div>

</div>


</body>

</html>