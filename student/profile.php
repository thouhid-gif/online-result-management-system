<?php
session_start();

include '../config/database.php';
include '../config/session.php';

checkStudent();

$message = "";
$messageType = "";

/*
|--------------------------------------------------------------------------
| Logged-in student
|--------------------------------------------------------------------------
| students.student_id is the primary key.
*/
$student_id = isset($_SESSION['user_id'])
    ? (int)$_SESSION['user_id']
    : 0;

if ($student_id <= 0) {
    header("Location: login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Load Student
|--------------------------------------------------------------------------
*/
function getStudent($conn, $student_id)
{
    $sql = "
        SELECT
            s.student_id,
            s.student_roll,
            s.full_name,
            s.email,
            s.phone,
            s.department_id,
            s.semester_id,
            s.password,
            s.status,
            s.photo,
            d.department_name,
            sem.semester_name
        FROM students AS s
        LEFT JOIN departments AS d
            ON s.department_id = d.department_id
        LEFT JOIN semesters AS sem
            ON s.semester_id = sem.semester_id
        WHERE s.student_id = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $student = $result
        ? mysqli_fetch_assoc($result)
        : null;

    mysqli_stmt_close($stmt);

    return $student;
}

$student = getStudent($conn, $student_id);

if (!$student) {
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Not Found</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="card shadow border-0">
                <div class="card-body text-center p-5">
                    <i class="fa-solid fa-circle-exclamation text-danger fs-1 mb-3"></i>
                    <h3>Student Account Not Found</h3>
                    <p class="text-muted">
                        Your login session does not match any student record.
                    </p>
                    <a href="../logout.php" class="btn btn-primary">
                        Logout & Login Again
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    ';
    exit();
}

/*
|--------------------------------------------------------------------------
| Update Profile
|--------------------------------------------------------------------------
*/
if (isset($_POST['update_profile'])) {

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $photo = $student['photo'] ?? 'default.png';

    if ($full_name === '' || $email === '') {

        $message = "Full Name and Email are required.";
        $messageType = "danger";

    } else {

        /*
        | Photo upload
        */
        if (
            isset($_FILES['photo']) &&
            $_FILES['photo']['error'] === UPLOAD_ERR_OK
        ) {

            $folder = "../uploads/students/";

            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            $mime = mime_content_type(
                $_FILES['photo']['tmp_name']
            );

            if (isset($allowed[$mime])) {

                $filename =
                    time() . "_" .
                    bin2hex(random_bytes(4)) . "." .
                    $allowed[$mime];

                if (
                    move_uploaded_file(
                        $_FILES['photo']['tmp_name'],
                        $folder . $filename
                    )
                ) {
                    $photo = $filename;
                }
            }
        }

        $update_sql = "
            UPDATE students
            SET
                full_name = ?,
                email = ?,
                phone = ?,
                photo = ?
            WHERE student_id = ?
        ";

        $update = mysqli_prepare(
            $conn,
            $update_sql
        );

        if ($update) {

            mysqli_stmt_bind_param(
                $update,
                "ssssi",
                $full_name,
                $email,
                $phone,
                $photo,
                $student_id
            );

            if (mysqli_stmt_execute($update)) {

                $message =
                    "Profile Updated Successfully.";

                $messageType = "success";

                $student =
                    getStudent($conn, $student_id);

            } else {

                $message =
                    "Profile Update Failed.";

                $messageType = "danger";
            }

            mysqli_stmt_close($update);

        } else {

            $message =
                "Profile Update Failed.";

            $messageType = "danger";
        }
    }
}

/*
|--------------------------------------------------------------------------
| Change Password
|--------------------------------------------------------------------------
*/
if (isset($_POST['change_password'])) {

    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($new !== $confirm) {

        $message =
            "Confirm Password does not match.";

        $messageType = "danger";

    } elseif (
        empty($student['password']) ||
        !password_verify(
            $old,
            $student['password']
        )
    ) {

        $message =
            "Current Password is incorrect.";

        $messageType = "danger";

    } else {

        $hash =
            password_hash(
                $new,
                PASSWORD_DEFAULT
            );

        $password_stmt = mysqli_prepare(
            $conn,
            "UPDATE students
             SET password = ?
             WHERE student_id = ?"
        );

        if ($password_stmt) {

            mysqli_stmt_bind_param(
                $password_stmt,
                "si",
                $hash,
                $student_id
            );

            if (
                mysqli_stmt_execute(
                    $password_stmt
                )
            ) {

                $message =
                    "Password Changed Successfully.";

                $messageType = "success";

            } else {

                $message =
                    "Password Change Failed.";

                $messageType = "danger";
            }

            mysqli_stmt_close($password_stmt);

        } else {

            $message =
                "Password Change Failed.";

            $messageType = "danger";
        }
    }
}

/*
|--------------------------------------------------------------------------
| Safe display variables
|--------------------------------------------------------------------------
*/
$student_name =
    $student['full_name'] ?? 'Student';

$student_roll =
    $student['student_roll'] ?? 'N/A';

$department_name =
    $student['department_name'] ?? 'N/A';

$semester_name =
    $student['semester_name'] ?? 'N/A';

$email =
    $student['email'] ?? 'N/A';

$phone =
    $student['phone'] ?? 'N/A';

$photo =
    $student['photo'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

body{
    background:#f4f7fb;
    font-family:Arial,Helvetica,sans-serif;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,.15);
}

.profile-img{
    width:160px;
    height:160px;
    border-radius:50%;
    object-fit:cover;
    border:5px solid #0d6efd;
}

</style>

</head>

<body>

<div class="container mt-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>

<i class="fa-solid fa-user-graduate"></i>

Student Profile

</h2>

<a href="dashboard.php" class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Back Dashboard

</a>

</div>

<?php if($message!=""){ ?>

<div class="alert alert-<?php echo $messageType; ?>">

<?php echo $message; ?>

</div>

<?php } ?>

<div class="row">

<!-- LEFT SIDE -->

<div class="col-md-4">

<div class="card">

<div class="card-body text-center">

<?php

if(!empty($student['photo']))
{

?>

<img
src="../uploads/students/<?php echo htmlspecialchars($photo); ?>"
class="profile-img">

<?php

}
else
{

?>

<img
src="../assets/default.png"
class="profile-img">

<?php

}

?>

<h3 class="mt-3">

<?php echo htmlspecialchars($student_name); ?>

</h3>

<p class="text-muted">

Student

</p>

<hr>

<p>

<b>Student Roll :</b>

<br>

<?php echo htmlspecialchars($student_roll); ?>

</p>

<p>

<b>Department :</b>

<br>

<?php echo htmlspecialchars($department_name); ?>

</p>

<p>

<b>Semester :</b>

<br>

<?php echo htmlspecialchars($semester_name); ?>

</p>

<p>

<b>Email :</b>

<br>

<?php echo htmlspecialchars($email); ?>

</p>

<p>

<b>Phone :</b>

<br>

<?php echo htmlspecialchars($phone); ?>

</p>

</div>

</div>

</div>

<!-- RIGHT SIDE -->

<div class="col-md-8">

<div class="card">

<div class="card-header bg-primary text-white">

<h4>

<i class="fa fa-edit"></i>

Update Profile

</h4>

</div>

<div class="card-body">
<form method="POST" enctype="multipart/form-data">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Full Name

</label>

<input
type="text"
name="full_name"
class="form-control"
value="<?php echo htmlspecialchars($student_name); ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Email

</label>

<input
type="email"
name="email"
class="form-control"
value="<?php echo htmlspecialchars($email); ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Phone Number

</label>

<input
type="text"
name="phone"
class="form-control"
value="<?php echo htmlspecialchars($phone); ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Department

</label>

<input
type="text"
class="form-control"
value="<?php echo htmlspecialchars($department_name); ?>"
readonly>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Semester

</label>

<input
type="text"
class="form-control"
value="<?php echo htmlspecialchars($semester_name); ?>"
readonly>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Student Roll

</label>

<input
type="text"
class="form-control"
value="<?php echo htmlspecialchars($student_roll); ?>"
readonly>

</div>

<div class="col-md-12 mb-3">

<label class="form-label">

Profile Photo

</label>

<input
type="file"
name="photo"
class="form-control"
accept=".jpg,.jpeg,.png">

</div>

<div class="col-md-12 text-center mb-3">

<?php
if(!empty($student['photo']))
{
?>

<img
src="../uploads/students/<?php echo htmlspecialchars($photo); ?>"
width="120"
height="120"
class="rounded-circle border">

<?php
}
else
{
?>

<img
src="../assets/default.png"
width="120"
height="120"
class="rounded-circle border">

<?php
}
?>

</div>

<div class="col-md-12">

<button
type="submit"
name="update_profile"
class="btn btn-primary w-100">

<i class="fa fa-save"></i>

Update Profile

</button>

</div>

</div>

</form>

</div>

</div>
<!-- Change Password Card -->

<div class="card mt-4">

    <div class="card-header bg-success text-white">

        <h4>

            <i class="fa fa-key"></i>

            Change Password

        </h4>

    </div>

    <div class="card-body">

        <form method="POST">

            <div class="row">

                <div class="col-md-12 mb-3">

                    <label class="form-label">

                        Current Password

                    </label>

                    <input
                    type="password"
                    name="old_password"
                    class="form-control"
                    required>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        New Password

                    </label>

                    <input
                    type="password"
                    name="new_password"
                    class="form-control"
                    required>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Confirm Password

                    </label>

                    <input
                    type="password"
                    name="confirm_password"
                    class="form-control"
                    required>

                </div>

                <div class="col-md-12">

                    <button
                    type="submit"
                    name="change_password"
                    class="btn btn-success w-100">

                        <i class="fa fa-lock"></i>

                        Change Password

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>