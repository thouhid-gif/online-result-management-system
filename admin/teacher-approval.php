<?php

session_start();

include '../config/database.php';
include '../config/session.php';

checkAdmin();


// Pending teachers
$sql = "SELECT
            t.*,
            d.department_name
        FROM teachers t
        LEFT JOIN departments d
        ON t.department_id = d.department_id
        WHERE t.status = 'Pending'
        ORDER BY t.teacher_id DESC";


$teachers = mysqli_query(
    $conn,
    $sql
);

?>

<!DOCTYPE html>

<html>

<head>

<title>Teacher Approval</title>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>


<body class="bg-light">


<div class="container mt-4">


<div class="d-flex
            justify-content-between
            align-items-center
            mb-3">

<h3>

Teacher Approval

</h3>


<a
href="dashboard.php"
class="btn btn-secondary">

Back

</a>

</div>


<?php

if (isset($_GET['success']))
{

if ($_GET['success'] == 'approved')
{

?>

<div class="alert alert-success">

Teacher approved successfully.

</div>

<?php

}


if ($_GET['success'] == 'rejected')
{

?>

<div class="alert alert-danger">

Teacher rejected successfully.

</div>

<?php

}

}

?>


<div class="card shadow">


<div class="card-header bg-primary text-white">

Pending Teacher Registration

</div>


<div class="card-body">


<div class="table-responsive">


<table class="table table-bordered
              table-hover">


<thead class="table-dark">

<tr>

<th>ID</th>

<th>Name</th>

<th>Email</th>

<th>Phone</th>

<th>Department</th>

<th>Status</th>

<th>Action</th>

</tr>

</thead>


<tbody>


<?php

if (
    $teachers &&
    mysqli_num_rows($teachers) > 0
)
{

while (
    $row =
    mysqli_fetch_assoc($teachers)
)
{

?>


<tr>

<td>

<?php

echo $row['teacher_id'];

?>

</td>


<td>

<?php

echo htmlspecialchars(
    $row['full_name']
);

?>

</td>


<td>

<?php

echo htmlspecialchars(
    $row['email']
);

?>

</td>


<td>

<?php

echo htmlspecialchars(
    $row['phone']
);

?>

</td>


<td>

<?php

echo htmlspecialchars(
    $row['department_name']
);

?>

</td>


<td>

<span class="badge bg-warning text-dark">

Pending

</span>

</td>


<td>


<a
href="approve-teacher.php?id=<?php echo $row['teacher_id']; ?>"
class="btn btn-success btn-sm"
onclick="return confirm('Approve this teacher?');">

Approve

</a>


<a
href="reject-teacher.php?id=<?php echo $row['teacher_id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Reject this teacher?');">

Reject

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

<td
colspan="7"
class="text-center text-danger">

No Pending Teacher Found

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


</body>

</html>