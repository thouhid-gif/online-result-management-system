<?php
session_start();

include '../config/database.php';
include '../config/session.php';

checkAdmin();

$message = "";

// Delete Student
if(isset($_GET['delete']))
{
    $id = intval($_GET['delete']);

    mysqli_query($conn,"DELETE FROM students WHERE student_id='$id'");

    $message = "Student Deleted Successfully.";
}

// Search

$search = "";

$sql = "SELECT students.*, departments.department_name, semesters.semester_name
FROM students
LEFT JOIN departments
ON students.department_id=departments.department_id
LEFT JOIN semesters
ON students.semester_id=semesters.semester_id";

if(isset($_GET['search']))
{
    $search=mysqli_real_escape_string($conn,$_GET['search']);

    $sql.=" WHERE
    full_name LIKE '%$search%'
    OR student_roll LIKE '%$search%'
    OR email LIKE '%$search%'";
}

$sql.=" ORDER BY student_id DESC";

$students=mysqli_query($conn,$sql);

?>
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Student Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

body{

background:#f5f6fa;

}

.sidebar{

position:fixed;

left:0;

top:0;

width:250px;

height:100vh;

background:#0d6efd;

}

.sidebar h3{

color:white;

text-align:center;

padding:20px;

}

.sidebar a{

display:block;

padding:15px;

color:white;

text-decoration:none;

font-weight:bold;

}

.sidebar a:hover{

background:#084298;

}

.main{

margin-left:250px;

padding:20px;

}

</style>

</head>

<body>
<div class="main">

<h2>

Student Management

</h2>

<hr>

<?php if($message!=""){ ?>

<div class="alert alert-success">

<?php echo $message; ?>

</div>

<?php } ?>

<div class="row mb-3">

<div class="col-md-6">

<form method="GET">

<div class="input-group">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Student"
value="<?php echo $search;?>">

<button class="btn btn-primary">

<i class="fa fa-search"></i>

Search

</button>

</div>

</form>

</div>

<div class="col-md-6 text-end">

<a href="../student-register.php"

class="btn btn-success">

<i class="fa fa-plus"></i>

Add Student

</a>

</div>

</div>
<div class="card shadow">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">
            <i class="fa fa-user-graduate"></i>
            Student List
        </h5>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-dark">

                    <tr>

                        <th>ID</th>

                        <th>Photo</th>

                        <th>Roll</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Phone</th>

                        <th>Department</th>

                        <th>Semester</th>

                        <th>Status</th>

                        <th width="180">Action</th>

                    </tr>

                </thead>

                <tbody>

                <?php

                if(mysqli_num_rows($students)>0)
                {

                    while($row=mysqli_fetch_assoc($students))
                    {

                ?>

                <tr>

                    <td><?php echo $row['student_id']; ?></td>

                    <td>

                        <?php

                        if(!empty($row['photo']))
                        {

                        ?>

                        <img src="../uploads/students/<?php echo $row['photo']; ?>"

                        width="50"

                        height="50"

                        class="rounded-circle">

                        <?php

                        }

                        else

                        {

                            echo "<span class='text-muted'>No Photo</span>";

                        }

                        ?>

                    </td>

                    <td><?php echo $row['student_roll']; ?></td>

                    <td><?php echo $row['full_name']; ?></td>

                    <td><?php echo $row['email']; ?></td>

                    <td><?php echo $row['phone']; ?></td>

                    <td><?php echo $row['department_name']; ?></td>

                    <td><?php echo $row['semester_name']; ?></td>

                    <td>

                        <?php

                        if($row['status']=="Active")
                        {

                            echo "<span class='badge bg-success'>Active</span>";

                        }

                        else

                        {

                            echo "<span class='badge bg-danger'>Inactive</span>";

                        }

                        ?>

                    </td>

                   <td>

<button
class="btn btn-info btn-sm"
data-bs-toggle="modal"
data-bs-target="#viewStudent<?php echo $row['student_id']; ?>">

<i class="fa fa-eye"></i>

</button>

<a href="edit-student.php?id=<?php echo $row['student_id']; ?>"
class="btn btn-warning btn-sm">

<i class="fa fa-edit"></i>

</a>

<a href="student.php?delete=<?php echo $row['student_id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Are you sure you want to delete this student?')">

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

                    <td colspan="10" class="text-center text-danger">

                        No Student Found

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
<div class="row mt-4">

    <div class="col-md-4">

        <div class="card bg-primary text-white shadow">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <h5>Total Students</h5>

                        <h2>

                        <?php
                        $count=mysqli_query($conn,"SELECT * FROM students");
                        echo mysqli_num_rows($count);
                        ?>

                        </h2>

                    </div>

                    <div>

                        <i class="fa fa-user-graduate fa-3x"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<hr>

<footer class="text-center mt-4">

<p>© <?php echo date("Y"); ?> Online Result Management System</p>

</footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>