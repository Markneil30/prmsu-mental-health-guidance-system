<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'counselor') {
   header("Location: login.php");
exit();
}

if (!isset($_GET['id'])) {
    header("Location: guidance_requests.php");
    exit();
}

$request_id = intval($_GET['id']);

// Check kung existing ang request
$check = mysqli_query($conn,"
SELECT *
FROM guidance_requests
WHERE request_id='$request_id'
");

if(mysqli_num_rows($check)==0){

    header("Location: guidance_requests.php");
    exit();

}

// Update status
$sql = "UPDATE guidance_requests
SET status='Rejected'
WHERE request_id='$request_id'";

if(mysqli_query($conn,$sql)){

    header("Location: guidance_requests.php?rejected=1");
    exit();

}else{

    echo "Error: ".mysqli_error($conn);

}
?>