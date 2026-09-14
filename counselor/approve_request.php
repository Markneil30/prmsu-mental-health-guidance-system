<?php

session_start();

include("../includes/db.php");


// =====================================
// CHECK COUNSELOR LOGIN
// =====================================

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'counselor'
) {
    header("Location: login.php");
    exit();
}


// =====================================
// CHECK REQUEST ID
// =====================================

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    header("Location: guidance_requests.php");
    exit();
}

$request_id = (int) $_GET['id'];


// =====================================
// GET GUIDANCE REQUEST
// =====================================

$stmt = $conn->prepare("
    SELECT
        request_id,
        student_id,
        subject,
        status
    FROM guidance_requests
    WHERE request_id = ?
");

if (!$stmt) {
    die("Database Error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $request_id);
$stmt->execute();

$result = $stmt->get_result();


// =====================================
// CHECK REQUEST
// =====================================

if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: guidance_requests.php");
    exit();
}

$request = $result->fetch_assoc();

$stmt->close();


// =====================================
// IF ALREADY APPROVED
// =====================================

if ($request['status'] === 'Approved') {

    header(
        "Location: set_schedule.php?id=" . $request_id
    );

    exit();
}


// =====================================
// ONLY PENDING CAN BE APPROVED
// =====================================

if ($request['status'] !== 'Pending') {

    header("Location: view_request.php?id=" . $request_id);

    exit();
}


// =====================================
// APPROVE REQUEST
// =====================================

$stmt = $conn->prepare("
    UPDATE guidance_requests
    SET status = 'Approved'
    WHERE request_id = ?
    AND status = 'Pending'
");

if (!$stmt) {
    die("Database Error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $request_id);

if (!$stmt->execute()) {

    $error = $stmt->error;

    $stmt->close();

    die(
        "Error approving request: " .
        htmlspecialchars($error)
    );
}

$stmt->close();


// =====================================
// GET STUDENT USER ID
// =====================================

$stmt = $conn->prepare("
    SELECT user_id
    FROM students
    WHERE student_id = ?
");

if (!$stmt) {
    die("Database Error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param(
    "i",
    $request['student_id']
);

$stmt->execute();

$student_result = $stmt->get_result();


// =====================================
// CREATE NOTIFICATION
// =====================================

if ($student_result->num_rows > 0) {

    $student = $student_result->fetch_assoc();

    $student_user_id = (int) $student['user_id'];

    $stmt->close();


    $notification_message =
        "Your guidance request \"" .
        $request['subject'] .
        "\" has been approved. Please wait while the counselor sets your counseling schedule.";


    $stmt = $conn->prepare("
        INSERT INTO notifications
        (
            user_id,
            type,
            message,
            is_read
        )
        VALUES
        (?, 'guidance', ?, 0)
    ");

    if ($stmt) {

        $stmt->bind_param(
            "is",
            $student_user_id,
            $notification_message
        );

        $stmt->execute();

        $stmt->close();
    }

} else {

    $stmt->close();

}


// =====================================
// REDIRECT TO SET SCHEDULE
// =====================================

header(
    "Location: set_schedule.php?id=" . $request_id
);

exit();

?>