<?php
include 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Contact | Online Result Management System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f4f6f9;
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
    padding:70px;
    text-align:center;
}

.card{
    border:none;
    border-radius:15px;
}

footer{
    background:#212529;
    color:white;
    text-align:center;
    padding:15px;
    margin-top:40px;
}

.contact-icon{
    font-size:40px;
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
<a class="nav-link" href="about.php">About</a>
</li>

<li class="nav-item">
<a class="nav-link active" href="contact.php">Contact</a>
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

<h1>Contact Us</h1>

<p class="lead">
We are always happy to help you.
</p>

</section>

<div class="container mt-5">

<div class="row">

<!-- Contact Information -->

<div class="col-lg-5">

<div class="card shadow p-4">

<h3 class="mb-4">Contact Information</h3>

<p class="contact-icon">📍</p>

<h5>Address</h5>

<p>
Sylhet Engineering College<br>
Sylhet, Bangladesh
</p>

<hr>

<p class="contact-icon">📞</p>

<h5>Phone</h5>

<p>
+880 1700-000000
</p>

<hr>

<p class="contact-icon">📧</p>

<h5>Email</h5>

<p>
admin@gmail.com
</p>

<hr>

<p class="contact-icon">🌐</p>

<h5>Website</h5>

<p>
www.resultmanagement.com
</p>

</div>

</div>

<!-- Contact Form -->

<div class="col-lg-7">

<div class="card shadow p-4">

<h3 class="mb-4">
Send Message
</h3>

<form action="" method="post">

<div class="mb-3">

<label>Name</label>

<input type="text"
class="form-control"
name="name"
required>

</div>

<div class="mb-3">

<label>Email</label>

<input type="email"
class="form-control"
name="email"
required>

</div>

<div class="mb-3">

<label>Subject</label>

<input type="text"
class="form-control"
name="subject"
required>

</div>

<div class="mb-3">

<label>Message</label>

<textarea
class="form-control"
rows="5"
name="message"
required></textarea>

</div>

<button
type="submit"
class="btn btn-primary w-100">

Send Message

</button>

</form>

</div>

</div>

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