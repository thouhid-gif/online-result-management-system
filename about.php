<?php
include 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>About | Online Result Management System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f7fb;
    font-family:Arial,Helvetica,sans-serif;
}

.navbar{
    background:#0d6efd;
}

.navbar-brand,
.nav-link{
    color:#fff !important;
}

.hero{
    background:linear-gradient(135deg,#0d6efd,#4facfe);
    color:white;
    padding:70px 20px;
    text-align:center;
}

.card{
    border:none;
    border-radius:15px;
    transition:.3s;
}

.card:hover{
    transform:translateY(-5px);
    box-shadow:0 15px 30px rgba(0,0,0,.15);
}

.icon{
    font-size:45px;
}

footer{
    margin-top:50px;
    background:#212529;
    color:white;
    text-align:center;
    padding:15px;
}

</style>

</head>

<body>

<!-- Navbar -->

<nav class="navbar navbar-expand-lg">

<div class="container">

<a class="navbar-brand" href="index.php">
Online Result Management System
</a>

<button class="navbar-toggler bg-light"
data-bs-toggle="collapse"
data-bs-target="#menu">

<span class="navbar-toggler-icon"></span>

</button>

<div class="collapse navbar-collapse" id="menu">

<ul class="navbar-nav ms-auto">

<li class="nav-item">
<a class="nav-link" href="index.php">Home</a>
</li>

<li class="nav-item">
<a class="nav-link active" href="about.php">About</a>
</li>

<li class="nav-item">
<a class="nav-link" href="contact.php">Contact</a>
</li>

<li class="nav-item">
<a class="nav-link" href="teacher-register.php">
Teacher Registration
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="student-register.php">
Student Registration
</a>
</li>

</ul>

</div>

</div>

</nav>

<!-- Hero -->

<section class="hero">

<h1>About Our System</h1>

<p class="lead">
Professional Online Result Management System
for Universities and Colleges
</p>

</section>

<!-- About -->

<div class="container mt-5">

<div class="row">

<div class="col-lg-6">

<h2>About Project</h2>

<p class="text-muted">

The Online Result Management System is a web-based
application developed to simplify the process of
student result management.

It allows administrators, teachers, and students
to securely access academic information through
their own dashboards.

</p>

<p class="text-muted">

The system minimizes paperwork, saves time,
reduces human errors, and provides a modern
digital platform for managing examination results.

</p>

</div>

<div class="col-lg-6">

<img src="assets/images/about.png"
class="img-fluid"
alt="About">

</div>

</div>

</div>

<!-- Features -->

<div class="container mt-5">

<h2 class="text-center mb-4">
System Features
</h2>

<div class="row g-4">

<div class="col-md-4">

<div class="card shadow p-4 text-center">

<div class="icon">👨‍💼</div>

<h4 class="mt-3">
Admin Panel
</h4>

<p>

Manage departments, teachers,
students, courses,
subjects, semesters and results.

</p>

</div>

</div>

<div class="col-md-4">

<div class="card shadow p-4 text-center">

<div class="icon">👨‍🏫</div>

<h4 class="mt-3">
Teacher Panel
</h4>

<p>

Teachers can upload marks,
manage students,
and publish examination scores.

</p>

</div>

</div>

<div class="col-md-4">

<div class="card shadow p-4 text-center">

<div class="icon">🎓</div>

<h4 class="mt-3">
Student Panel
</h4>

<p>

Students can log in,
view semester results,
download PDFs,
and check GPA & CGPA.

</p>

</div>

</div>

</div>

</div>

<!-- Vision -->

<div class="container mt-5">

<div class="card shadow p-4">

<h3>
Our Vision
</h3>

<p>

To provide a secure,
fast,
reliable,
and user-friendly academic result
management platform that helps
educational institutions embrace digital transformation.

</p>

</div>

</div>

<footer>

© 2026 Online Result Management System

<br>

Developed by Md Thouhidur Rahman

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>