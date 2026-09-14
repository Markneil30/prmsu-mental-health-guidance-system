<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
exit();
}

if(!isset($_GET['id'])){
    header("Location: counselors.php");
    exit();
}

$id = intval($_GET['id']);

$query = mysqli_query($conn,"
SELECT *
FROM users
WHERE id='$id'
AND role='counselor'
");

if(mysqli_num_rows($query)==0){
    header("Location: counselors.php");
    exit();
}

$row = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>View Counselor</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
font-family:Poppins,sans-serif;
}

.card{
margin-top:40px;
border-radius:15px;
}

table th{
width:220px;
}

</style>

</head>

<body>

<div class="container">

<div class="card shadow">

<div class="card-header bg-info text-white">

<h4>

<i class="bi bi-person-vcard"></i>

Counselor Information

</h4>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th>Full Name</th>
<td><?= $row['fullname']; ?></td>
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
<th>Role</th>
<td><?= ucfirst($row['role']); ?></td>
</tr>

<tr>
<th>Status</th>
<td><?= $row['status']; ?></td>
</tr>

</table>

<a href="counselors.php" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>

<a href="edit_counselor.php?id=<?= $row['id']; ?>" class="btn btn-warning">

<i class="bi bi-pencil"></i>

Edit

</a>

</div>

</div>

</div>

</body>

</html>