<?php
session_start();

include '../config/database.php';
include '../config/session.php';

checkAdmin();

$message = "";
$messageType = "";

/*==========================
ADD COURSE
==========================*/

if(isset($_POST['add_course']))
{

    $course_name = mysqli_real_escape_string($conn,$_POST['course_name']);

    $department_id = $_POST['department_id'];

    $semester_id = $_POST['semester_id'];

    $check=mysqli_query($conn,

    "SELECT * FROM courses
    WHERE course_name='$course_name'
    AND department_id='$department_id'
    AND semester_id='$semester_id'");

    if(mysqli_num_rows($check)>0)
    {

        $message="Course Already Exists.";

        $messageType="danger";

    }

    else
    {

        mysqli_query($conn,

        "INSERT INTO courses
        (course_name,department_id,semester_id)

        VALUES

        ('$course_name',
        '$department_id',
        '$semester_id')");

        $message="Course Added Successfully.";

        $messageType="success";

    }

}

/*==========================
DELETE COURSE
==========================*/

if(isset($_GET['delete']))
{

    $id=$_GET['delete'];

    mysqli_query($conn,

    "DELETE FROM courses
    WHERE course_id='$id'");

    header("Location: course.php");

    exit();

}

/*==========================
LOAD DATA
==========================*/

$departments=mysqli_query($conn,

"SELECT * FROM departments ORDER BY department_name");

$semesters=mysqli_query($conn,

"SELECT * FROM semesters ORDER BY semester_id");

$search = "";

if(isset($_GET['search']))
{
    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $courses = mysqli_query($conn,"
    SELECT c.*,d.department_name,s.semester_name

    FROM courses c

    LEFT JOIN departments d
    ON c.department_id=d.department_id

    LEFT JOIN semesters s
    ON c.semester_id=s.semester_id

    WHERE

    c.course_name LIKE '%$search%'

    OR

    d.department_name LIKE '%$search%'

    OR

    s.semester_name LIKE '%$search%'

    ORDER BY c.course_id DESC
    ");

}
else
{

    $courses = mysqli_query($conn,"
    SELECT c.*,d.department_name,s.semester_name

    FROM courses c

    LEFT JOIN departments d
    ON c.department_id=d.department_id

    LEFT JOIN semesters s
    ON c.semester_id=s.semester_id

    ORDER BY c.course_id DESC
    ");

}
?>
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>Course Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"

href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body class="bg-light">

<div class="container mt-5">
<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4>

<i class="fa fa-book"></i>

Add Course

</h4>

</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-4">

<label>Course Name</label>

<input
type="text"
name="course_name"
class="form-control"
required>

</div>

<div class="col-md-4">

<label>Department</label>

<select
name="department_id"
class="form-select"
required>

<option value="">Select</option>

<?php while($d=mysqli_fetch_assoc($departments)){ ?>

<option value="<?php echo $d['department_id']; ?>">

<?php echo $d['department_name']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-4">

<label>Semester</label>

<select
name="semester_id"
class="form-select"
required>

<option value="">Select</option>

<?php while($s=mysqli_fetch_assoc($semesters)){ ?>

<option value="<?php echo $s['semester_id']; ?>">

<?php echo $s['semester_name']; ?>

</option>

<?php } ?>

</select>

</div>

</div>

<br>

<button

class="btn btn-success"

name="add_course">

<i class="fa fa-save"></i>

Save Course

</button>

</form>

</div>

</div>
<?php

$totalCourse=mysqli_num_rows(mysqli_query($conn,"
SELECT * FROM courses
"));

?>

<div class="row mb-4">

<div class="col-md-4">

<div class="card bg-primary text-white shadow">

<div class="card-body">

<h5>Total Courses</h5>

<h2>

<?php echo $totalCourse; ?>

</h2>

</div>

</div>

</div>

</div>
<div class="card shadow mt-4">

    <div class="card-header bg-dark text-white d-flex justify-content-between">

        <h4 class="mb-0">

            <i class="fa fa-list"></i>

            Course List

        </h4>

        <span class="badge bg-warning text-dark">

            Total :
            <?php echo mysqli_num_rows($courses); ?>

        </span>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-primary">

                    <tr>

                        <th>ID</th>

                        <th>Course Name</th>

                        <th>Department</th>

                        <th>Semester</th>

                        <th width="180">Action</th>

                    </tr>

                </thead>

                <tbody>

                <?php

                if(mysqli_num_rows($courses)>0)
                {

                    while($row=mysqli_fetch_assoc($courses))
                    {

                ?>

                    <tr>

                        <td>

                            <?php echo $row['course_id']; ?>

                        </td>

                        <td>

                            <?php echo $row['course_name']; ?>

                        </td>

                        <td>

                            <?php echo $row['department_name']; ?>

                        </td>

                        <td>

                            <?php echo $row['semester_name']; ?>

                        </td>

                        <td>

                            <a href="edit-course.php?id=<?php echo $row['course_id']; ?>"

                            class="btn btn-warning btn-sm">

                                <i class="fa fa-edit"></i>

                                Edit

                            </a>

                            <a href="course.php?delete=<?php echo $row['course_id']; ?>"

                            class="btn btn-danger btn-sm"

                            onclick="return confirm('Delete this course?')">

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

                        <td colspan="5" class="text-center text-danger">

                            No Course Found

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
<div class="mb-3">

    <a href="dashboard.php" class="btn btn-secondary">

        <i class="fa fa-arrow-left"></i>

                           Back to Dashboard

    </a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
