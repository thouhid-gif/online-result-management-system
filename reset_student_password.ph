<?php

session_start();

include 'config/database.php';


// ======================================================
// MESSAGE
// ======================================================

$message = "";
$message_type = "";


// ======================================================
// RESET PASSWORD
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = (int)($_POST['student_id'] ?? 0);

    $new_password =
        $_POST['new_password'] ?? '';

    $confirm_password =
        $_POST['confirm_password'] ?? '';


    // ==================================================
    // VALIDATION
    // ==================================================

    if ($student_id <= 0) {

        $message =
            "Please select a student.";

        $message_type = "danger";

    }

    elseif ($new_password === '') {

        $message =
            "Please enter a new password.";

        $message_type = "danger";

    }

    elseif (strlen($new_password) < 6) {

        $message =
            "Password must be at least 6 characters.";

        $message_type = "danger";

    }

    elseif ($new_password !== $confirm_password) {

        $message =
            "Password and Confirm Password do not match.";

        $message_type = "danger";

    }

    else {

        // ==================================================
        // CHECK STUDENT
        // ==================================================

        $stmt = mysqli_prepare(
            $conn,

            "SELECT
                student_id,
                student_roll,
                full_name,
                email
             FROM students
             WHERE student_id = ?
             LIMIT 1"
        );


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $student_id
            );

            mysqli_stmt_execute($stmt);

            $result =
                mysqli_stmt_get_result($stmt);

            $student =
                mysqli_fetch_assoc($result);

            mysqli_stmt_close($stmt);


            if (!$student) {

                $message =
                    "Student not found.";

                $message_type = "danger";

            }

            else {

                // ==================================================
                // HASH NEW PASSWORD
                // ==================================================

                $hashed_password =
                    password_hash(
                        $new_password,
                        PASSWORD_DEFAULT
                    );


                // ==================================================
                // UPDATE PASSWORD
                // ==================================================

                $update = mysqli_prepare(
                    $conn,

                    "UPDATE students
                     SET password = ?
                     WHERE student_id = ?"
                );


                if ($update) {

                    mysqli_stmt_bind_param(
                        $update,
                        "si",
                        $hashed_password,
                        $student_id
                    );


                    if (
                        mysqli_stmt_execute(
                            $update
                        )
                    ) {

                        $message =
                            "Password reset successfully for " .
                            $student['full_name'] .
                            ".";

                        $message_type =
                            "success";

                    }

                    else {

                        $message =
                            "Password reset failed.";

                        $message_type =
                            "danger";
                    }


                    mysqli_stmt_close(
                        $update
                    );

                }

                else {

                    $message =
                        "Database error.";

                    $message_type =
                        "danger";
                }
            }

        }

        else {

            $message =
                "Database error.";

            $message_type =
                "danger";
        }
    }
}


// ======================================================
// GET ALL STUDENTS
// ======================================================

$students = [];

$query = mysqli_query(
    $conn,

    "SELECT
        student_id,
        student_roll,
        full_name,
        email,
        status
     FROM students
     ORDER BY full_name ASC"
);


if ($query) {

    while (
        $row =
        mysqli_fetch_assoc($query)
    ) {

        $students[] = $row;
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
    Reset Student Password
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

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    background:
        linear-gradient(
            135deg,
            #eef4ff,
            #f8fbff
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;
}


/* =====================================================
   CARD
===================================================== */

.container {

    width: 95%;

    max-width: 600px;
}


.card {

    background: white;

    padding: 35px;

    border-radius: 16px;

    box-shadow:
        0 10px 35px
        rgba(0,0,0,0.12);
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

    font-size: 28px;
}


.header p {

    color: #6b7280;

    margin-top: 8px;
}


/* =====================================================
   MESSAGE
===================================================== */

.message {

    padding: 14px 16px;

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


.message.danger {

    background: #fee2e2;

    color: #991b1b;

    border:
        1px solid #fca5a5;
}


/* =====================================================
   LABEL
===================================================== */

label {

    display: block;

    margin-bottom: 8px;

    font-weight: 600;

    color: #374151;
}


/* =====================================================
   INPUT
===================================================== */

select,
input {

    width: 100%;

    padding: 13px;

    border:
        1px solid #d1d5db;

    border-radius: 8px;

    font-size: 15px;

    outline: none;

    margin-bottom: 20px;

    background: white;
}


select:focus,
input:focus {

    border-color: #2563eb;

    box-shadow:
        0 0 0 3px
        rgba(37,99,235,0.10);
}


/* =====================================================
   BUTTON
===================================================== */

.reset-btn {

    width: 100%;

    padding: 14px;

    border: none;

    border-radius: 8px;

    background: #2563eb;

    color: white;

    font-size: 16px;

    font-weight: 700;

    cursor: pointer;
}


.reset-btn:hover {

    background: #1d4ed8;
}


/* =====================================================
   BACK BUTTON
===================================================== */

.back-btn {

    display: block;

    text-align: center;

    margin-top: 18px;

    color: #2563eb;

    text-decoration: none;

    font-weight: 600;
}


.back-btn:hover {

    text-decoration: underline;
}


/* =====================================================
   WARNING
===================================================== */

.warning {

    margin-top: 20px;

    padding: 13px;

    background: #fff7ed;

    border:
        1px solid #fed7aa;

    color: #9a3412;

    border-radius: 8px;

    font-size: 13px;

    line-height: 1.5;
}

</style>

</head>


<body>


<div class="container">


    <div class="card">


        <!-- =========================================
             HEADER
        ========================================== -->

        <div class="header">

            <h1>
                🔐 Reset Student Password
            </h1>

            <p>
                Select a student and create a new password
            </p>

        </div>



        <!-- =========================================
             MESSAGE
        ========================================== -->

        <?php

        if (!empty($message)) {

        ?>

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

        <?php

        }

        ?>



        <!-- =========================================
             FORM
        ========================================== -->

        <form
            method="POST"
            action="reset_student_password.php"
        >


            <!-- STUDENT -->

            <label>

                Select Student

            </label>


            <select
                name="student_id"
                required
            >

                <option value="">

                    -- Select Student --

                </option>


                <?php

                foreach (
                    $students
                    as $student
                ) {

                ?>

                    <option
                        value="<?php
                        echo (int)
                            $student[
                                'student_id'
                            ];
                        ?>"
                    >

                        <?php

                        echo htmlspecialchars(
                            $student[
                                'full_name'
                            ]
                        );

                        ?>

                        -
                        <?php

                        echo htmlspecialchars(
                            $student[
                                'student_roll'
                            ]
                        );

                        ?>

                        -

                        <?php

                        echo htmlspecialchars(
                            $student[
                                'email'
                            ]
                        );

                        ?>

                    </option>

                <?php

                }

                ?>

            </select>



            <!-- NEW PASSWORD -->

            <label>

                New Password

            </label>


            <input
                type="password"
                name="new_password"
                placeholder="Enter new password"
                minlength="6"
                required
            >



            <!-- CONFIRM PASSWORD -->

            <label>

                Confirm Password

            </label>


            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm new password"
                minlength="6"
                required
            >



            <!-- RESET -->

            <button
                type="submit"
                class="reset-btn"
                onclick="
                    return confirm(
                        'Are you sure you want to reset this student password?'
                    );
                "
            >

                🔑 Reset Password

            </button>


        </form>



        <!-- =========================================
             WARNING
        ========================================== -->

        <div class="warning">

            <strong>Important:</strong>

            This page is for password recovery/testing.
            After fixing the required student passwords,
            delete or protect this file.

        </div>



        <!-- =========================================
             BACK
        ========================================== -->

        <a
            href="admin/student.php"
            class="back-btn"
        >

            ← Back to Student Management

        </a>


    </div>

</div>


</body>

</html>