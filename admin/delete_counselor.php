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

$check = mysqli_query($conn,"
SELECT *
FROM users
WHERE id='$id'
AND role='counselor'
");

if(mysqli_num_rows($check)==0){
    header("Location: counselors.php");
    exit();
}

mysqli_query($conn,"
DELETE FROM users
WHERE id='$id'
");

header("Location: counselors.php?deleted=1");
exit();

?>