```php
<?php

session_start();

include '../config/database.php';
include '../config/session.php';

// Admin Login Check
checkAdmin();


// ===============================
// Dashboard Statistics
// ===============================

$totalStudents = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM students")
);

$totalTeachers = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM teachers")
);

$totalDepartments = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM departments")
);

$totalSemesters = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM semesters")
);

$totalCourses = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM courses")
);


// ===============================
// Latest Teachers
// ===============================

$teacherQuery = mysqli_query(
    $conn,
    "SELECT * FROM teachers
     ORDER BY teacher_id DESC
     LIMIT 5"
);


// ===============================
// Latest Students
// ===============================

$studentQuery = mysqli_query(
    $conn,
    "SELECT * FROM students
     ORDER BY student_id DESC
     LIMIT 5"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard</title>


<!-- Bootstrap -->

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<!-- Font Awesome -->

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


<style>

/* =========================
   BODY
========================= */

body{

    margin:0;

    padding:0;

    background:#f5f5f5;

    font-family:Arial, Helvetica, sans-serif;

}


/* =========================
   SIDEBAR
========================= */

.sidebar{

    position:fixed;

    left:0;

    top:0;

    width:230px;

    height:100vh;

    background:#0d6efd;

    padding-top:20px;

}


.sidebar h3{

    color:white;

    text-align:center;

    margin-bottom:25px;

    font-size:22px;

}


.sidebar a{

    display:block;

    padding:12px 20px;

    color:white;

    text-decoration:none;

    font-weight:500;

}


.sidebar a:hover{

    background:#084298;

}


.sidebar i{

    width:25px;

}


/* =========================
   MAIN CONTENT
========================= */

.main{

    margin-left:230px;

    padding:25px;

}


/* =========================
   TOP BAR
========================= */

.top-bar{

    background:white;

    border:1px solid #ddd;

    padding:15px 20px;

    border-radius:8px;

    margin-bottom:25px;

}


/* =========================
   CARDS
========================= */

.card{

    border:1px solid #ddd;

    border-radius:8px;

    background:white;

}


.stat-card{

    padding:20px;

}


.stat-card h6{

    color:#666;

    margin-bottom:10px;

}


.stat-card h3{

    margin:0;

    font-size:28px;

}


/* =========================
   TABLE
========================= */

.table-card{

    margin-top:25px;

}


.table-card .card-header{

    background:#0d6efd;

    color:white;

    font-weight:bold;

}


/* =========================
   FOOTER
========================= */

footer{

    text-align:center;

    padding:20px;

    margin-top:30px;

    color:#666;

}


/* =========================
   MOBILE
========================= */

@media(max-width:768px){

    .sidebar{

        position:relative;

        width:100%;

        height:auto;

    }

    .main{

        margin-left:0;

    }

}

</style>

</head>


<body>


<!-- ==================================================
     SIDEBAR
================================================== -->

<div class="sidebar">


    <h3>

        <i class="fa-solid fa-graduation-cap"></i>

        <br>

        Admin Panel

    </h3>


    <a href="dashboard.php">

        <i class="fa fa-home"></i>

        Dashboard

    </a>


    <a href="teacher-approval.php">

        <i class="fa fa-user-check"></i>

        Teacher Approval

    </a>


    <a href="student.php">

        <i class="fa fa-user-graduate"></i>

        Students

    </a>


    <a href="department.php">

        <i class="fa fa-building"></i>

        Departments

    </a>


    <a href="semester.php">

        <i class="fa fa-calendar"></i>

        Semesters

    </a>


    <a href="course.php">

        <i class="fa fa-book"></i>

        Courses

    </a>
    
    <a href="exam.php">

        <i class="fa fa-file-lines"></i>

        Exams

    </a>
    <a href="publish-result.php">

        <i class="fa fa-upload"></i>

        Publish Result

    </a>


    <a href="report.php">

        <i class="fa fa-chart-column"></i>

        Reports

    </a>


    <a href="settings.php">

        <i class="fa fa-gear"></i>

        Settings

    </a>


    <a href="profile.php">

        <i class="fa fa-user"></i>

        Profile

    </a>


    <a href="../logout.php">

        <i class="fa fa-sign-out-alt"></i>

        Logout

    </a>


</div>


<!-- ==================================================
     MAIN CONTENT
================================================== -->

<div class="main">


    <!-- TOP BAR -->

    <div class="top-bar">

        <div class="d-flex justify-content-between align-items-center">


            <div>

                <h3 class="mb-1">

                    Admin Dashboard

                </h3>

                <span class="text-muted">

                    Welcome,

                    <strong>

                        <?php

                        echo isset($_SESSION['name'])
                            ? $_SESSION['name']
                            : 'Administrator';

                        ?>

                    </strong>

                </span>

            </div>


            <div>

                <a href="profile.php"
                   class="btn btn-primary btn-sm">

                    <i class="fa fa-user"></i>

                    Profile

                </a>


                <a href="../logout.php"
                   class="btn btn-danger btn-sm">

                    <i class="fa fa-sign-out-alt"></i>

                    Logout

                </a>

            </div>


        </div>

    </div>



    <!-- ==================================================
         STATISTICS
    ================================================== -->

    <div class="row">


        <!-- Students -->

        <div class="col-md-3 mb-3">

            <div class="card stat-card">

                <h6>

                    <i class="fa fa-user-graduate"></i>

                    Total Students

                </h6>

                <h3>

                    <?php echo $totalStudents; ?>

                </h3>

                <a href="student.php"
                   class="btn btn-primary btn-sm mt-3">

                    View Students

                </a>

            </div>

        </div>



        <!-- Teachers -->

        <div class="col-md-3 mb-3">

            <div class="card stat-card">

                <h6>

                    <i class="fa fa-chalkboard-teacher"></i>

                    Total Teachers

                </h6>

                <h3>

                    <?php echo $totalTeachers; ?>

                </h3>

                <a href="teacher-approval.php"
                   class="btn btn-success btn-sm mt-3">

                    View Teachers

                </a>

            </div>

        </div>



        <!-- Departments -->

        <div class="col-md-3 mb-3">

            <div class="card stat-card">

                <h6>

                    <i class="fa fa-building"></i>

                    Departments

                </h6>

                <h3>

                    <?php echo $totalDepartments; ?>

                </h3>

                <a href="department.php"
                   class="btn btn-info btn-sm mt-3">

                    View Departments

                </a>

            </div>

        </div>



        <!-- Courses -->

        <div class="col-md-3 mb-3">

            <div class="card stat-card">

                <h6>

                    <i class="fa fa-book"></i>

                    Total Courses

                </h6>

                <h3>

                    <?php echo $totalCourses; ?>

                </h3>

                <a href="course.php"
                   class="btn btn-secondary btn-sm mt-3">

                    View Courses

                </a>

            </div>

        </div>


    </div>



    <!-- ==================================================
         QUICK ACTIONS
    ================================================== -->

    <div class="card mt-3">

        <div class="card-header">

            Quick Actions

        </div>


        <div class="card-body">


            <a href="student.php"
               class="btn btn-primary me-2 mb-2">

                <i class="fa fa-user-graduate"></i>

                Students

            </a>


            <a href="teacher-approval.php"
               class="btn btn-success me-2 mb-2">

                <i class="fa fa-user-check"></i>

                Teachers

            </a>


            <a href="department.php"
               class="btn btn-info me-2 mb-2">

                <i class="fa fa-building"></i>

                Departments

            </a>


            <a href="semester.php"
               class="btn btn-warning me-2 mb-2">

                <i class="fa fa-calendar"></i>

                Semesters

            </a>


            <a href="course.php"
               class="btn btn-secondary me-2 mb-2">

                <i class="fa fa-book"></i>

                Courses

            </a>


            <a href="exam.php"
               class="btn btn-dark me-2 mb-2">

                <i class="fa fa-file-lines"></i>

                Exams

            </a>


            <a href="publish-result.php"
               class="btn btn-danger mb-2">

                <i class="fa fa-upload"></i>

                Publish Result

            </a>


        </div>

    </div>



    <!-- ==================================================
         LATEST TEACHERS
    ================================================== -->

    <div class="card table-card">


        <div class="card-header">

            <i class="fa fa-chalkboard-teacher"></i>

            Latest Registered Teachers

        </div>


        <div class="card-body">


            <div class="table-responsive">


                <table class="table table-bordered table-hover">


                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>

                            <th>Photo</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Phone</th>

                            <th>Status</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    if(mysqli_num_rows($teacherQuery) > 0)
                    {

                        while($teacher =
                            mysqli_fetch_assoc($teacherQuery))
                        {

                    ?>


                    <tr>


                        <td>

                            <?php
                            echo $teacher['teacher_id'];
                            ?>

                        </td>


                        <td>


                            <?php

                            if(!empty($teacher['photo']))
                            {

                            ?>

                                <img
                                src="../uploads/teachers/<?php
                                echo $teacher['photo'];
                                ?>"
                                width="45"
                                height="45"
                                class="rounded-circle">

                            <?php

                            }
                            else
                            {

                                echo "No Photo";

                            }

                            ?>

                        </td>


                        <td>

                            <?php
                            echo $teacher['full_name'];
                            ?>

                        </td>


                        <td>

                            <?php
                            echo $teacher['email'];
                            ?>

                        </td>


                        <td>

                            <?php
                            echo $teacher['phone'];
                            ?>

                        </td>


                        <td>


                            <?php

                            if($teacher['status'] == "Approved")
                            {

                                echo '<span class="badge bg-success">
                                      Approved
                                      </span>';

                            }
                            elseif($teacher['status'] == "Pending")
                            {

                                echo '<span class="badge bg-warning text-dark">
                                      Pending
                                      </span>';

                            }
                            else
                            {

                                echo '<span class="badge bg-danger">
                                      Rejected
                                      </span>';

                            }

                            ?>


                        </td>


                    </tr>


                    <?php

                        }

                    }
                    else
                    {

                    ?>


                    <tr>

                        <td colspan="6"
                            class="text-center text-danger">

                            No Teacher Found

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



    <!-- ==================================================
         LATEST STUDENTS
    ================================================== -->

    <div class="card table-card">


        <div class="card-header bg-success text-white">

            <i class="fa fa-user-graduate"></i>

            Latest Registered Students

        </div>


        <div class="card-body">


            <div class="table-responsive">


                <table class="table table-bordered table-hover">


                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>

                            <th>Roll</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Semester</th>

                            <th>Status</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    if(mysqli_num_rows($studentQuery) > 0)
                    {

                        while($student =
                            mysqli_fetch_assoc($studentQuery))
                        {

                    ?>


                    <tr>


                        <td>

                            <?php
                            echo $student['student_id'];
                            ?>

                        </td>


                        <td>

                            <?php
                            echo $student['student_roll'];
                            ?>

                        </td>


                        <td>

                            <?php
                            echo $student['full_name'];
                            ?>

                        </td>


                        <td>

                            <?php
                            echo $student['email'];
                            ?>

                        </td>


                        <td>

                            <?php
                            echo $student['semester_id'];
                            ?>

                        </td>


                        <td>


                            <?php

                            if($student['status'] == "Active")
                            {

                                echo '<span class="badge bg-success">
                                      Active
                                      </span>';

                            }
                            else
                            {

                                echo '<span class="badge bg-danger">
                                      Inactive
                                      </span>';

                            }

                            ?>


                        </td>


                    </tr>


                    <?php

                        }

                    }
                    else
                    {

                    ?>


                    <tr>

                        <td colspan="6"
                            class="text-center text-danger">

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



    <!-- ==================================================
         FOOTER
    ================================================== -->

    <footer>

        <p class="mb-1">

            © <?php echo date("Y"); ?>

            Online Result Management System

        </p>

        <small>

            Developed by Md Thouhidur Rahman and Ibrahim

        </small>

    </footer>


</div>


<!-- Bootstrap JS -->

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>
```
