<?php

session_start();

include 'config/database.php';

$message = "";
$messageType = "";


if (isset($_POST['register']))
{
    $teacher_name = trim($_POST['teacher_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department_id = intval($_POST['department_id']);

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];


    // Password check
    if ($password != $confirm_password)
    {
        $message = "Password and Confirm Password do not match.";
        $messageType = "danger";
    }
    else
    {
        // Check email
        $check = mysqli_query(
            $conn,
            "SELECT teacher_id
             FROM teachers
             WHERE email='$email'"
        );


        if (mysqli_num_rows($check) > 0)
        {
            $message = "Email already exists.";
            $messageType = "warning";
        }
        else
        {
            // Password hash
            $password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            // Default photo
            $photo = "default.png";


            // Photo upload
            if (
                isset($_FILES['photo']) &&
                $_FILES['photo']['error'] == 0
            )
            {
                $extension = strtolower(
                    pathinfo(
                        $_FILES['photo']['name'],
                        PATHINFO_EXTENSION
                    )
                );


                $allowed = array(
                    'jpg',
                    'jpeg',
                    'png'
                );


                if (in_array($extension, $allowed))
                {
                    $photo =
                        time() . "_" .
                        basename($_FILES['photo']['name']);


                    $uploadDir =
                        "uploads/teachers/";


                    if (!is_dir($uploadDir))
                    {
                        mkdir(
                            $uploadDir,
                            0777,
                            true
                        );
                    }


                    move_uploaded_file(
                        $_FILES['photo']['tmp_name'],
                        $uploadDir . $photo
                    );
                }
            }


            // INSERT
            $sql = "INSERT INTO teachers
            (
                full_name,
                email,
                phone,
                department_id,
                password,
                photo,
                status
            )
            VALUES
            (
                '$teacher_name',
                '$email',
                '$phone',
                '$department_id',
                '$password',
                '$photo',
                'Pending'
            )";


            if (mysqli_query($conn, $sql))
            {
                $message =
                    "Registration successful! Please wait for Admin approval.";

                $messageType = "success";
            }
            else
            {
                $message =
                    "Registration failed: " .
                    mysqli_error($conn);

                $messageType = "danger";
            }
        }
    }
}


// Departments
$departments = mysqli_query(
    $conn,
    "SELECT *
     FROM departments
     ORDER BY department_name ASC"
);

?>


<!DOCTYPE html>

<html>

<head>

<title>Teacher Registration</title>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>


<body class="bg-light">


<div class="container mt-5">

<div class="card mx-auto shadow"
     style="max-width:800px;">


<div class="card-header bg-primary text-white">

<h3 class="text-center mb-0">

Teacher Registration

</h3>

</div>


<div class="card-body">


<?php

if ($message != "")
{

?>

<div class="alert alert-<?php echo $messageType; ?>">

<?php

echo $message;

?>

</div>

<?php

}

?>


<form method="POST"
      enctype="multipart/form-data">


<div class="row">


<div class="col-md-6 mb-3">

<label class="form-label">

Full Name

</label>

<input
type="text"
name="teacher_name"
class="form-control"
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
required>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

Phone

</label>

<input
type="text"
name="phone"
class="form-control"
required>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

Department

</label>

<select
name="department_id"
class="form-select"
required>

<option value="">

Select Department

</option>


<?php

while ($d = mysqli_fetch_assoc($departments))
{

?>

<option value="<?php echo $d['department_id']; ?>">

<?php

echo htmlspecialchars(
    $d['department_name']
);

?>

</option>

<?php

}

?>

</select>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

Password

</label>

<input
type="password"
name="password"
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


<div class="col-md-12">

<button
type="submit"
name="register"
class="btn btn-primary w-100">

Register

</button>

</div>


</div>

</form>


</div>

</div>

</div>


</body>

</html>