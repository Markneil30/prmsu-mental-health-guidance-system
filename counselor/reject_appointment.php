<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'counselor') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: appointment_requests.php");
    exit();
}

$appointment_id = intval($_GET['id']);

/* Get actual counselor_id from counselors table */
$user_id = (int) $_SESSION['user_id'];

$counselor_stmt = mysqli_prepare(
    $conn,
    "SELECT counselor_id
     FROM counselors
     WHERE user_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($counselor_stmt, "i", $user_id);
mysqli_stmt_execute($counselor_stmt);

$counselor_result = mysqli_stmt_get_result($counselor_stmt);
$counselor_data = mysqli_fetch_assoc($counselor_result);

mysqli_stmt_close($counselor_stmt);

if (!$counselor_data) {
    die("Counselor account is not properly linked to the counselors table.");
}

$counselor_id = (int) $counselor_data['counselor_id'];


/* Check if appointment exists */
$check = mysqli_prepare(
    $conn,
    "SELECT appointment_id
     FROM appointments
     WHERE appointment_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($check, "i", $appointment_id);
mysqli_stmt_execute($check);

$check_result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($check_result) == 0) {
    mysqli_stmt_close($check);
    header("Location: appointment_requests.php");
    exit();
}

mysqli_stmt_close($check);


/* Reject appointment */
$sql = "UPDATE appointments
        SET status = 'Rejected',
            counselor_id = ?
        WHERE appointment_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $counselor_id,
    $appointment_id
);

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header("Location: appointment_requests.php?success=rejected");
    exit();

} else {

    echo "Error: " . mysqli_error($conn);

    mysqli_stmt_close($stmt);
}
?>