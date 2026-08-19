<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include 'config/database.php';

$message = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {

        $message = "❌ Please enter email and password.";

    } else {

        $sql = "SELECT *
                FROM teachers
                WHERE email = ?
                LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {

            die("Database Error: " . mysqli_error($conn));

        }

        mysqli_stmt_bind_param($stmt, "s", $email);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 0) {

            $message = "❌ Email not found.";

        } else {

            $teacher = mysqli_fetch_assoc($result);

            /* CHECK STATUS */

            if ($teacher['status'] != 'Active') {

                $message =
                    "❌ Your account is not approved yet. Current status: "
                    . $teacher['status'];

            }

            /* CHECK PASSWORD */

            elseif (
                !password_verify(
                    $password,
                    $teacher['password']
                )
            ) {

                $message = "❌ Password is incorrect.";

            }

            /* LOGIN SUCCESS */

            else {

                $_SESSION['teacher_id'] =
                    $teacher['teacher_id'];

                $_SESSION['teacher_name'] =
                    $teacher['full_name'];

                $_SESSION['teacher_email'] =
                    $teacher['email'];

                $_SESSION['role'] =
                    'teacher';

                header(
                    "Location: teacher/dashboard.php"
                );

                exit;
            }
        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Teacher Login</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

body {
    margin: 0;
    min-height: 100vh;

    display: flex;
    justify-content: center;
    align-items: center;

    background: #f1f5f9;

    font-family: Arial, sans-serif;
}

.login-box {

    width: 420px;

    background: white;

    padding: 35px;

    border-radius: 15px;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.12);
}

.login-title {

    text-align: center;

    margin-bottom: 25px;
}

.login-title h2 {

    font-weight: bold;

    color: #0d6efd;
}

.login-icon {

    width: 70px;
    height: 70px;

    border-radius: 50%;

    background: #e7f1ff;

    color: #0d6efd;

    display: flex;

    align-items: center;

    justify-content: center;

    margin: 0 auto 15px;

    font-size: 30px;
}

.form-label {

    font-weight: 600;
}

.btn-login {

    width: 100%;

    padding: 12px;

    font-weight: bold;

    margin-top: 10px;
}

</style>

</head>

<body>


<div class="login-box">

    <div class="login-title">

        <div class="login-icon">

            👨‍🏫

        </div>

        <h2>Teacher Login</h2>

        <p class="text-muted">

            Online Result Management System

        </p>

    </div>


    <?php if (!empty($message)): ?>

        <div class="alert alert-danger">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <form method="POST"
          action="">


        <div class="mb-3">

            <label class="form-label">

                Email

            </label>

            <input
                type="email"
                name="email"
                class="form-control"
                placeholder="Enter your email"
                required
            >

        </div>


        <div class="mb-3">

            <label class="form-label">

                Password

            </label>

            <input
                type="password"
                name="password"
                class="form-control"
                placeholder="Enter your password"
                required
            >

        </div>


        <button
            type="submit"
            name="login"
            class="btn btn-primary btn-login">

            Login

        </button>


    </form>

</div>


</body>

</html>