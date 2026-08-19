<?php
session_start();

include '../config/database.php';
include '../config/session.php';

checkAdmin();

$message = "";
$messageType = "";

/*=========================
ADD SEMESTER
=========================*/

if(isset($_POST['add_semester']))
{

    $semester_name = mysqli_real_escape_string($conn,$_POST['semester_name']);

    $check = mysqli_query($conn,
    "SELECT * FROM semesters
    WHERE semester_name='$semester_name'");

    if(mysqli_num_rows($check)>0)
    {

        $message="Semester Already Exists.";

        $messageType="danger";

    }
    else
    {

        mysqli_query($conn,
        "INSERT INTO semesters(semester_name)
        VALUES('$semester_name')");

        $message="Semester Added Successfully.";

        $messageType="success";

    }

}

/*=========================
DELETE SEMESTER
=========================*/

if(isset($_GET['delete']))
{

    $id = intval($_GET['delete']);

    mysqli_query($conn,
    "DELETE FROM semesters
    WHERE semester_id='$id'");

    header("Location: semester.php");
    exit();

}

/*=========================
SEARCH
=========================*/

$search="";

if(isset($_GET['search']))
{

    $search=mysqli_real_escape_string($conn,$_GET['search']);

    $semesters=mysqli_query($conn,"
    SELECT *
    FROM semesters
    WHERE semester_name LIKE '%$search%'
    ORDER BY semester_id DESC");

}
else
{

    $semesters=mysqli_query($conn,"
    SELECT *
    FROM semesters
    ORDER BY semester_id DESC");

}

$totalSemester=mysqli_num_rows(mysqli_query($conn,
"SELECT * FROM semesters"));

?>
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>Semester Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body class="bg-light">

<div class="container mt-5">

<h2>

<i class="fa fa-calendar-alt"></i>

Semester Management

</h2>

<hr>
<div class="mb-3">

    <a href="dashboard.php" class="btn btn-secondary">

        <i class="fa fa-arrow-left"></i>

        Back to Dashboard

    </a>

</div>
<div class="row mb-4">

<div class="col-md-4">

<div class="card bg-primary text-white shadow">

<div class="card-body">

<h5>Total Semesters</h5>

<h2>

<?php echo $totalSemester; ?>

</h2>

</div>

</div>

</div>

</div>
<?php if($message!=""){ ?>

<div class="alert alert-<?php echo $messageType; ?>">

<?php echo $message; ?>

</div>

<?php } ?>
<div class="card shadow mb-4">

<div class="card-header bg-primary text-white">

<h5>

<i class="fa fa-plus-circle"></i>

Add New Semester

</h5>

</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-10">

<input
type="text"
name="semester_name"
class="form-control"
placeholder="Enter Semester Name"
required>

</div>

<div class="col-md-2">

<button
type="submit"
name="add_semester"
class="btn btn-success w-100">

<i class="fa fa-save"></i>

Save

</button>

</div>

</div>

</form>

</div>

</div>
<div class="card shadow mb-3">

<div class="card-body">

<form method="GET">

<div class="row">

<div class="col-md-10">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Semester"
value="<?php echo $search; ?>">

</div>

<div class="col-md-2">

<button
class="btn btn-primary w-100">

<i class="fa fa-search"></i>

Search

</button>

</div>

</div>

</form>

</div>

</div>
<div class="card shadow">

<div class="card-header bg-dark text-white">

<h5>

<i class="fa fa-list"></i>

Semester List

</h5>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-primary">

<tr>

<th>ID</th>

<th>Semester Name</th>

<th width="180">Action</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($semesters)>0)
{

while($row=mysqli_fetch_assoc($semesters))
{

?>

<tr>

<td><?php echo $row['semester_id']; ?></td>

<td><?php echo $row['semester_name']; ?></td>

<td>

<a
href="edit-semester.php?id=<?php echo $row['semester_id']; ?>"
class="btn btn-warning btn-sm">

<i class="fa fa-edit"></i>

</a>

<a
href="semester.php?delete=<?php echo $row['semester_id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this semester?')">

<i class="fa fa-trash"></i>

</a>

</td>

</tr>

<?php

}

}
else
{

?>

<tr>

<td colspan="3" class="text-center text-danger">

No Semester Found

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>