<?php
session_start();

include '../config/database.php';
include '../config/session.php';

checkAdmin();

$message = "";
$messageType = "";

/* -------------------------
   Add Department
--------------------------*/

if(isset($_POST['add_department']))
{

    $department = mysqli_real_escape_string($conn,$_POST['department_name']);

    $check = mysqli_query($conn,
    "SELECT * FROM departments
    WHERE department_name='$department'");

    if(mysqli_num_rows($check)>0)
    {

        $message="Department Already Exists.";
        $messageType="danger";

    }

    else
    {

        mysqli_query($conn,
        "INSERT INTO departments(department_name)
        VALUES('$department')");

        $message="Department Added Successfully.";
        $messageType="success";

    }

}

/* -------------------------
   Delete Department
--------------------------*/

if(isset($_GET['delete']))
{

    $id = intval($_GET['delete']);

    mysqli_query($conn,
    "DELETE FROM departments
    WHERE department_id='$id'");

    header("Location: department.php");
    exit();

}

$departments = mysqli_query($conn,
"SELECT * FROM departments
ORDER BY department_id DESC");

?>
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>Department Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

body{

background:#f5f6fa;

}

.main{

padding:30px;

}

.card{

border:none;

border-radius:12px;

box-shadow:0 5px 15px rgba(0,0,0,.1);

}

</style>

</head>

<body>

<div class="container-fluid main">

<h2>

<i class="fa fa-building"></i>

Department Management

</h2>
<div class="mb-3">

    <a href="dashboard.php" class="btn btn-secondary">

        <i class="fa fa-arrow-left"></i>

        Back to Dashboard

    </a>

</div>
<hr>
<?php if($message!=""){ ?>

<div class="alert alert-<?php echo $messageType; ?>">

<?php echo $message; ?>

</div>

<?php } ?>
<div class="card mb-4">

<div class="card-header bg-primary text-white">

Add New Department

</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-10">

<input
type="text"
name="department_name"
class="form-control"
placeholder="Enter Department Name"
required>

</div>

<div class="col-md-2">

<button
class="btn btn-success w-100"
name="add_department">

<i class="fa fa-plus"></i>

Add

</button>

</div>

</div>

</form>

</div>

</div>
<!-- ========================= -->
<!-- Department List -->
<!-- ========================= -->

<div class="card">

    <div class="card-header bg-dark text-white d-flex justify-content-between">

        <h5 class="mb-0">
            <i class="fa fa-building"></i>
            Department List
        </h5>

        <span class="badge bg-warning text-dark">

            Total :
            <?php echo mysqli_num_rows($departments); ?>

        </span>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-primary">

                    <tr>

                        <th width="80">ID</th>

                        <th>Department Name</th>

                        <th width="180">Action</th>

                    </tr>

                </thead>

                <tbody>

                <?php

                if(mysqli_num_rows($departments)>0)
                {

                    while($row=mysqli_fetch_assoc($departments))
                    {

                ?>

                    <tr>

                        <td>

                            <?php echo $row['department_id']; ?>

                        </td>

                        <td>

                            <?php echo $row['department_name']; ?>

                        </td>

                        <td>

                            <a href="edit-department.php?id=<?php echo $row['department_id']; ?>"

                            class="btn btn-warning btn-sm">

                                <i class="fa fa-edit"></i>

                                Edit

                            </a>

                            <a href="department.php?delete=<?php echo $row['department_id']; ?>"

                            class="btn btn-danger btn-sm"

                            onclick="return confirm('Are you sure you want to delete this department?')">

                                <i class="fa fa-trash"></i>

                                Delete

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

                            No Department Found

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