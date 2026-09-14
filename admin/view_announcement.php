<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
   header("Location: login.php");
exit();
}

if(!isset($_GET['id'])){
    header("Location: announcements.php");
    exit();
}

$id = intval($_GET['id']);

$query = mysqli_query($conn,"
SELECT
announcements.*,
users.fullname

FROM announcements

LEFT JOIN users
ON announcements.posted_by = users.id

WHERE announcement_id='$id'
");

if(mysqli_num_rows($query)==0){
    header("Location: announcements.php");
    exit();
}

$row = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>View Announcement</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f4f6f9;
    font-family:Poppins,sans-serif;
}

.card{
    margin-top:40px;
    border-radius:15px;
}

</style>

</head>

<body>

<div class="container">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4>Announcement Details</h4>

</div>

<div class="card-body">

<h3><?= htmlspecialchars($row['title']); ?></h3>

<hr>

<p><?= nl2br(htmlspecialchars($row['description'])); ?></p>

<hr>

<p>

<strong>Posted By:</strong>

<?= htmlspecialchars($row['fullname']); ?>

</p>

<p>

<strong>Date Posted:</strong>

<?= $row['created_at']; ?>

</p>

<a href="announcements.php" class="btn btn-secondary">

Back

</a>

</div>

</div>

</div>

</body>

</html>