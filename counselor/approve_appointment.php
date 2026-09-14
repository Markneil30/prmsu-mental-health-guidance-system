<?php

session_start();
include("../includes/db.php");

// =====================================
// CHECK COUNSELOR LOGIN
// =====================================

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['role'] != 'counselor'
) {
    header("Location: login.php");
    exit();
}

// =====================================
// CHECK APPOINTMENT ID
// =====================================

if (!isset($_GET['id'])) {
    header("Location: appointment_requests.php");
    exit();
}

$appointment_id = intval($_GET['id']);

$user_id = $_SESSION['user_id'];

$message = "";

// =====================================
// GET COUNSELOR ID
// =====================================

$stmt = $conn->prepare("
    SELECT counselor_id
    FROM counselors
    WHERE user_id = ?
");

if (!$stmt) {
    die("Database Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Counselor account not found.");
}

$counselor = $result->fetch_assoc();

$counselor_id = $counselor['counselor_id'];

$stmt->close();

// =====================================
// GET APPOINTMENT
// =====================================

$stmt = $conn->prepare("
    SELECT
        appointments.appointment_id,
        appointments.student_id,
        appointments.appointment_date,
        appointments.appointment_time,
        appointments.purpose,
        appointments.description,
        appointments.status,

        users.fullname,
        users.email,

        students.student_number,
        students.course,
        students.year_level,
        students.section,
        students.user_id AS student_user_id

    FROM appointments

    INNER JOIN students
        ON appointments.student_id = students.student_id

    INNER JOIN users
        ON students.user_id = users.id

    WHERE appointments.appointment_id = ?
");

if (!$stmt) {
    die("Database Error: " . $conn->error);
}

$stmt->bind_param("i", $appointment_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    $stmt->close();

    header("Location: appointment_requests.php");
    exit();
}

$appointment = $result->fetch_assoc();

$stmt->close();

// =====================================
// IF ALREADY APPROVED
// =====================================

if ($appointment['status'] == "Approved") {

    header(
        "Location: view_appointment.php?id=" .
        $appointment_id
    );

    exit();
}

// =====================================
// SAVE SCHEDULE
// =====================================

if (isset($_POST['save_schedule'])) {

    $schedule_date = trim($_POST['schedule_date']);
    $schedule_time = trim($_POST['schedule_time']);

    // =====================================
    // VALIDATE DATE
    // =====================================

    if ($schedule_date < date("Y-m-d")) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-exclamation-circle'></i>
            The appointment date cannot be in the past.
        </div>
        ";

    } else {

        // =====================================
        // UPDATE APPOINTMENT
        // =====================================

        $stmt = $conn->prepare("
            UPDATE appointments
            SET
                appointment_date = ?,
                appointment_time = ?,
                counselor_id = ?,
                status = 'Approved'
            WHERE appointment_id = ?
        ");

        if (!$stmt) {
            die("Database Error: " . $conn->error);
        }

        $stmt->bind_param(
            "ssii",
            $schedule_date,
            $schedule_time,
            $counselor_id,
            $appointment_id
        );

        if ($stmt->execute()) {

            $stmt->close();

            // =====================================
            // STUDENT NOTIFICATION
            // =====================================

            $notification_message =
                "Your counseling appointment has been approved. " .
                "Schedule: " .
                date(
                    "F d, Y",
                    strtotime($schedule_date)
                ) .
                " at " .
                date(
                    "h:i A",
                    strtotime($schedule_time)
                ) .
                ".";

            $notify = $conn->prepare("
                INSERT INTO notifications
                (
                    user_id,
                    type,
                    message,
                    is_read,
                    created_at
                )
                VALUES
                (?, 'appointment', ?, 0, NOW())
            ");

            if (!$notify) {
                die(
                    "Notification Database Error: " .
                    $conn->error
                );
            }

            $notify->bind_param(
                "is",
                $appointment['student_user_id'],
                $notification_message
            );

            $notify->execute();

            $notify->close();

            // =====================================
            // SUCCESS
            // =====================================

            header(
                "Location: view_appointment.php?id=" .
                $appointment_id .
                "&scheduled=1"
            );

            exit();

        } else {

            $message = "
            <div class='alert alert-danger'>
                <i class='bi bi-x-circle'></i>
                Error saving appointment:
                " .
                htmlspecialchars($stmt->error) .
                "
            </div>
            ";

            $stmt->close();
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Set Appointment Schedule</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    rel="stylesheet"
>

<style>

body {
    background: #f4f6f9;
    font-family: Poppins, sans-serif;
}

.card {
    margin-top: 40px;
    border-radius: 15px;
}

.info-box {
    background: #eef7ff;
    border-left: 4px solid #0d6efd;
    padding: 15px;
    border-radius: 8px;
}

</style>

</head>

<body>

<div class="container">

<div class="row justify-content-center">

<div class="col-md-8">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

<i class="bi bi-calendar-check-fill"></i>

Set Appointment Schedule

</h4>

</div>

<div class="card-body">

<?= $message; ?>


<!-- =====================================
     STUDENT INFORMATION
===================================== -->

<div class="info-box mb-4">

<strong>Student:</strong>
<?= htmlspecialchars($appointment['fullname']); ?>

<br>

<strong>Student Number:</strong>
<?= htmlspecialchars($appointment['student_number']); ?>

<br>

<strong>Course:</strong>
<?= htmlspecialchars($appointment['course']); ?>

<br>

<strong>Year Level:</strong>
<?= htmlspecialchars($appointment['year_level']); ?>

<br>

<strong>Section:</strong>
<?= htmlspecialchars($appointment['section']); ?>

<br>

<strong>Purpose:</strong>
<?= htmlspecialchars($appointment['purpose']); ?>

</div>


<form method="POST">

<!-- DATE -->

<div class="mb-3">

<label class="form-label">

<strong>Appointment Date</strong>

</label>

<input
    type="date"
    name="schedule_date"
    class="form-control"
    min="<?= date('Y-m-d'); ?>"
    value="<?= htmlspecialchars($appointment['appointment_date']); ?>"
    required
>

</div>


<!-- TIME -->

<div class="mb-4">

<label class="form-label">

<strong>Appointment Time</strong>

</label>

<input
    type="time"
    name="schedule_time"
    class="form-control"
    value="<?= htmlspecialchars($appointment['appointment_time']); ?>"
    required
>

</div>


<button
    type="submit"
    name="save_schedule"
    class="btn btn-success"
>

<i class="bi bi-calendar-check"></i>

Set Schedule & Approve

</button>


<a
    href="view_appointment.php?id=<?= $appointment_id; ?>"
    class="btn btn-secondary"
>

<i class="bi bi-arrow-left"></i>

Back

</a>

</form>

</div>

</div>

</div>

</div>

</div>

</body>

</html>