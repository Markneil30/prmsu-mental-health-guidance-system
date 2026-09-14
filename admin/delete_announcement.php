<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");

    exit();
}

if (!isset($_GET['id'])) {
    header("Location: announcements.php");
    exit();
}

$id = intval($_GET['id']);

// Check if announcement exists
$check = mysqli_query($conn, "SELECT * FROM announcements WHERE announcement_id='$id'");

if (mysqli_num_rows($check) == 0) {
    header("Location: announcements.php");
    exit();
}

// Delete announcement
$sql = "DELETE FROM announcements WHERE announcement_id='$id'";

if (mysqli_query($conn, $sql)) {

    header("Location: announcements.php?deleted=1");
    exit();

} else {

    echo "Error: " . mysqli_error($conn);

}
?>