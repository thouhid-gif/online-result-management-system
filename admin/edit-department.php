<?php
session_start();

include '../config/database.php';
include '../config/session.php';

checkAdmin();

$message = "";
$messageType = "";

if(!isset($_GET['id']))
{
    header("Location: department.php");
    exit();
}

$id = intval($_GET['id']);

$query = mysqli_query($conn,
"SELECT * FROM departments WHERE department_id='$id'");

if(mysqli_num_rows($query)==0)
{
    header("Location: department.php");
    exit();
}

$row = mysqli_fetch_assoc($query);

// =====================
// Update Department
// =====================

if(isset($_POST['update_department']))
{

    $department = mysqli_real_escape_string($conn,$_POST['department_name']);

    mysqli_query($conn,
    "UPDATE departments
    SET department_name='$department'
    WHERE department_id='$id'");

    $message="Department Updated Successfully.";
    $messageType="success";

    $query=mysqli_query($conn,
    "SELECT * FROM departments WHERE department_id='$id'");

    $row=mysqli_fetch_assoc($query);

}
?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Edit Department</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body style="background:#f5f6fa;">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card shadow">

<div class="card-header bg-warning">

<h4>

<i class="fa fa-edit"></i>

Edit Department

</h4>

</div>

<div class="card-body">

<?php if($message!=""){ ?>

<div class="alert alert-<?php echo $messageType; ?>">

<?php echo $message; ?>

</div>

<?php } ?>

<form method="POST">

<div class="mb-3">

<label>

Department Name

</label>

<input
type="text"
name="department_name"
class="form-control"
value="<?php echo $row['department_name']; ?>"
required>

</div>

<button
type="submit"
name="update_department"
class="btn btn-success">

<i class="fa fa-save"></i>

Update

</button>

<a href="department.php"
class="btn btn-secondary">

Back

</a>

</form>

</div>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>