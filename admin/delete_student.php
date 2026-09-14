<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: students.php");
    exit();
}

$id = intval($_GET['id']);

// Check kung existing ang student
$check = mysqli_query($conn,"
SELECT *
FROM users
WHERE id='$id'
AND role='student'
");

if(mysqli_num_rows($check) == 0){
    header("Location: login.php");

    exit();
}

// Delete student information
mysqli_query($conn,"
DELETE FROM students
WHERE user_id='$id'
");

// Delete user account
mysqli_query($conn,"
DELETE FROM users
WHERE id='$id'
");

header("Location: students.php?deleted=1");
exit();

?>