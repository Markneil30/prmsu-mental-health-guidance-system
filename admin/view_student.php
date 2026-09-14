<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
   header("Location: login.php");
exit();
}

if(!isset($_GET['id'])){
    header("Location: students.php");
    exit();
}

$id = intval($_GET['id']);

$sql = "SELECT users.*, students.*
        FROM users
        INNER JOIN students
        ON users.id = students.user_id
        WHERE users.id='$id'";

$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result)==0){
    header("Location: students.php");
    exit();
}

$row = mysqli_fetch_assoc($result);

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>View Student</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Poppins,sans-serif;
}

body{
background:#f4f6f9;
}

.sidebar{
position:fixed;
left:0;
top:0;
width:250px;
height:100vh;
background:#002147;
}

.sidebar h3{
padding:20px;
text-align:center;
color:white;
border-bottom:1px solid rgba(255,255,255,.2);
}

.sidebar a{
display:block;
padding:15px 20px;
color:white;
text-decoration:none;
}

.sidebar a:hover{
background:#0A3D91;
}

.main{
margin-left:250px;
padding:30px;
}

.card{
border:none;
border-radius:15px;
}

table th{
width:220px;
}

</style>

</head>

<body>

<div class="sidebar">

<h3>PRMSU</h3>

<a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>

<a href="students.php"><i class="bi bi-people-fill"></i> Students</a>

<a href="counselors.php"><i class="bi bi-person-workspace"></i> Counselors</a>

<a href="appointments.php"><i class="bi bi-calendar-check"></i> Appointments</a>

<a href="announcements.php"><i class="bi bi-megaphone-fill"></i> Announcements</a>

<a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>

</div>

<div class="main">

<div class="card shadow">

<div class="card-header bg-info text-white">

<h4>

<i class="bi bi-person-vcard"></i>

Student Profile

</h4>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th>Full Name</th>
<td><?= $row['fullname']; ?></td>
</tr>

<tr>
<th>Student Number</th>
<td><?= $row['student_number']; ?></td>
</tr>

<tr>
<th>Username</th>
<td><?= $row['username']; ?></td>
</tr>

<tr>
<th>Email</th>
<td><?= $row['email']; ?></td>
</tr>

<tr>
<th>Course</th>
<td><?= $row['course']; ?></td>
</tr>

<tr>
<th>Year Level</th>
<td><?= $row['year_level']; ?></td>
</tr>

<tr>
<th>Section</th>
<td><?= $row['section']; ?></td>
</tr>

<tr>
<th>Gender</th>
<td><?= $row['gender']; ?></td>
</tr>

<tr>
<th>Contact Number</th>
<td><?= $row['contact_number']; ?></td>
</tr>

<tr>
<th>Address</th>
<td><?= $row['address']; ?></td>
</tr>

<tr>
<th>Status</th>
<td><?= $row['status']; ?></td>
</tr>

</table>

<a href="students.php" class="btn btn-secondary">
<i class="bi bi-arrow-left"></i>
Back
</a>

<a href="edit_student.php?id=<?= $row['id']; ?>" class="btn btn-warning">
<i class="bi bi-pencil"></i>
Edit
</a>

</div>

</div>

</div>

</body>

</html>