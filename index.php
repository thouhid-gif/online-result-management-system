<?php
include 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Online Result Management System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f5f7fb;
font-family:Arial,Helvetica,sans-serif;
}

.navbar{
background:#0d6efd;
}

.navbar-brand{
color:white;
font-weight:bold;
}

.nav-link{
color:white!important;
}

.hero{
padding:80px 20px;
text-align:center;
background:linear-gradient(135deg,#0d6efd,#4facfe);
color:white;
}

.hero h1{
font-size:45px;
font-weight:bold;
}

.card{
border:none;
border-radius:15px;
transition:.3s;
}

.card:hover{
transform:translateY(-5px);
box-shadow:0 15px 30px rgba(0,0,0,.2);
}

.card img{
width:90px;
margin:auto;
margin-top:20px;
}

footer{
background:#212529;
color:white;
padding:15px;
margin-top:60px;
text-align:center;
}

</style>

</head>

<body>

<nav class="navbar navbar-expand-lg">

<div class="container">

<a class="navbar-brand" href="#">Result Management System</a>

<button class="navbar-toggler bg-light" data-bs-toggle="collapse" data-bs-target="#menu">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="menu">

<ul class="navbar-nav ms-auto">

<li class="nav-item">
<a class="nav-link" href="index.php">Home</a>
</li>

<li class="nav-item">
<a class="nav-link" href="about.php">About</a>
</li>

<li class="nav-item">
<a class="nav-link" href="contact.php">Contact</a>
</li>

<li class="nav-item">
<a class="nav-link" href="teacher-register.php">Teacher Registration</a>
</li>

<li class="nav-item">
<a class="nav-link" href="student-register.php">Student Registration</a>
</li>

</ul>

</div>

</div>

</nav>

<section class="hero">

<h1>Online Result Management System</h1>

<p class="lead">
Professional University Result Management Portal
</p>

</section>

<div class="container mt-5">

<div class="row g-4">

<div class="col-md-4">

<div class="card shadow">

<img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png">

<div class="card-body text-center">

<h3>Admin</h3>

<p>Administrator Login Panel</p>

<a href="login.php?role=admin" class="btn btn-primary w-100">
Login
</a>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card shadow">

<img src="https://cdn-icons-png.flaticon.com/512/1995/1995574.png">

<div class="card-body text-center">

<h3>Teacher</h3>

<p>Teacher Login Panel</p>

<a href="login.php?role=teacher" class="btn btn-success w-100">
Login
</a>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card shadow">

<img src="https://cdn-icons-png.flaticon.com/512/4140/4140048.png">

<div class="card-body text-center">

<h3>Student</h3>

<p>Student Login Panel</p>

<a href="login.php?role=student" class="btn btn-warning w-100">
Login
</a>

</div>

</div>

</div>

</div>

</div>

<footer>

© 2026 Online Result Management System |
Developed by Md Thouhidur Rahman and Ibrahim

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>