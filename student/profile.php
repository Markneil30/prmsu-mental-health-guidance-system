<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
exit();
}

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    background:#f4f6f9;
    font-family:Poppins,sans-serif;
}
.card{
    max-width:700px;
    margin:40px auto;
    border-radius:15px;
    box-shadow:0 10px 20px rgba(0,0,0,.1);
}
.profile-icon{
    font-size:90px;
    color:#002147;
}
</style>

</head>
<body>

<div class="container">

<div class="card">

<div class="card-body">

<div class="text-center mb-4">

<i class="bi bi-person-circle profile-icon"></i>

<h3><?php echo $user['fullname']; ?></h3>

<p class="text-muted">Student</p>

</div>

<table class="table table-bordered">

<tr>
<th width="30%">Student ID</th>
<td><?php echo $user['student_id']; ?></td>
</tr>

<tr>
<th>Full Name</th>
<td><?php echo $user['fullname']; ?></td>
</tr>

<tr>
<th>Email</th>
<td><?php echo $user['email']; ?></td>
</tr>

<tr>
<th>Course</th>
<td><?php echo $user['course']; ?></td>
</tr>

<tr>
<th>Year Level</th>
<td><?php echo $user['year_level']; ?></td>
</tr>

<tr>
<th>Section</th>
<td><?php echo $user['section']; ?></td>
</tr>

</table>

<div class="text-center">

<a href="edit_profile.php" class="btn btn-primary">
<i class="bi bi-pencil-square"></i>
Edit Profile
</a>

<a href="dashboard.php" class="btn btn-secondary">
<i class="bi bi-arrow-left"></i>
Back
</a>

</div>

</div>

</div>

</div>

</body>
</html>