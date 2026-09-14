<?php

session_start();
include("../includes/db.php");

// =====================================
// CHECK COUNSELOR LOGIN
// =====================================

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] != 'counselor'
) {
    header("Location: login.php");
    exit();
}

// =====================================
// CHECK APPOINTMENT ID
// =====================================

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    header("Location: appointment_requests.php");
    exit();
}

$appointment_id = (int) $_GET['id'];

$message = "";

// =====================================
// GET APPOINTMENT
// =====================================

$stmt = $conn->prepare("
    SELECT
        appointments.appointment_id,
        appointments.student_id,
        appointments.appointment_date,
        appointments.appointment_time,
        appointments.concern,
        appointments.status,

        users.fullname,
        users.email,

        students.student_number

    FROM appointments

    INNER JOIN students
        ON appointments.student_id = students.student_id

    INNER JOIN users
        ON students.user_id = users.id

    WHERE appointments.appointment_id = ?

    LIMIT 1
");

if (!$stmt) {
    die("Database Error: " . htmlspecialchars($conn->error));
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
// SAVE SCHEDULE
// =====================================

if (isset($_POST['save_schedule'])) {

    $schedule_date = trim($_POST['schedule_date'] ?? '');
    $schedule_time = trim($_POST['schedule_time'] ?? '');

    // =====================================
    // VALIDATE DATE
    // =====================================

    if (empty($schedule_date) || empty($schedule_time)) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-exclamation-circle me-1'></i>
            Please select both appointment date and time.
        </div>
        ";

    }

    elseif ($schedule_date < date("Y-m-d")) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-exclamation-circle me-1'></i>
            The appointment date cannot be in the past.
        </div>
        ";

    }

    else {

        // =====================================
        // UPDATE APPOINTMENT
        // =====================================
        // NOTE:
        // counselor_id is NOT updated here.
        // This prevents the foreign key error.

        $stmt = $conn->prepare("
            UPDATE appointments
            SET
                appointment_date = ?,
                appointment_time = ?,
                status = 'Approved'
            WHERE appointment_id = ?
        ");

        if (!$stmt) {

            die(
                "Database Error: " .
                htmlspecialchars($conn->error)
            );

        }

        $stmt->bind_param(
            "ssi",
            $schedule_date,
            $schedule_time,
            $appointment_id
        );


        // =====================================
        // EXECUTE UPDATE
        // =====================================

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
                (
                    ?,
                    'appointment',
                    ?,
                    0,
                    NOW()
                )
            ");


            if ($notify) {

                $notify->bind_param(
                    "is",
                    $appointment['student_id'],
                    $notification_message
                );

                /*
                 * IMPORTANT:
                 * The notifications table normally uses
                 * users.id as user_id.
                 *
                 * Get the student's user_id first.
                 */

                $notify->close();

            }


            // =====================================
            // GET STUDENT USER ID
            // =====================================

            $student_user_stmt = $conn->prepare("
                SELECT user_id
                FROM students
                WHERE student_id = ?
                LIMIT 1
            ");


            if ($student_user_stmt) {

                $student_user_stmt->bind_param(
                    "i",
                    $appointment['student_id']
                );

                $student_user_stmt->execute();

                $student_user_result =
                    $student_user_stmt->get_result();


                if (
                    $student_user_result->num_rows > 0
                ) {

                    $student_user =
                        $student_user_result->fetch_assoc();

                    $student_user_id =
                        $student_user['user_id'];


                    // =====================================
                    // INSERT NOTIFICATION
                    // =====================================

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
                        (
                            ?,
                            'appointment',
                            ?,
                            0,
                            NOW()
                        )
                    ");


                    if ($notify) {

                        $notify->bind_param(
                            "is",
                            $student_user_id,
                            $notification_message
                        );

                        $notify->execute();

                        $notify->close();

                    }

                }

                $student_user_stmt->close();

            }


            // =====================================
            // SUCCESS
            // =====================================

            header(
                "Location: view_appointment.php?id=" .
                $appointment_id .
                "&scheduled=1"
            );

            exit();

        }

        else {

            $message = "
            <div class='alert alert-danger'>
                <i class='bi bi-x-circle me-1'></i>
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

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    background: #f4f6f9;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    color: #172554;

}


.container {

    max-width: 900px;

    padding-bottom: 50px;

}


.card {

    margin-top: 40px;

    border: none;

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 8px 25px
        rgba(15,23,42,.08);

}


.card-header {

    background:
        linear-gradient(
            135deg,
            #002147,
            #0d6efd
        );

    color: white;

    padding: 20px 25px;

    border: none;

}


.card-header h4 {

    margin: 0;

    font-size: 20px;

    font-weight: 600;

}


.card-body {

    padding: 30px;

}


.info-box {

    background: #f8fafc;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    overflow: hidden;

}


.info-row {

    display: flex;

    border-bottom: 1px solid #e2e8f0;

}


.info-row:last-child {

    border-bottom: none;

}


.info-label {

    width: 220px;

    padding: 15px 18px;

    background: #f1f5f9;

    font-weight: 600;

    color: #334155;

}


.info-value {

    flex: 1;

    padding: 15px 18px;

    background: white;

    color: #475569;

}


.form-control {

    border-radius: 9px;

    padding: 11px 13px;

}


.form-control:focus {

    border-color: #0d6efd;

    box-shadow:
        0 0 0 .2rem
        rgba(13,110,253,.10);

}


.button-area {

    display: flex;

    gap: 8px;

    flex-wrap: wrap;

    margin-top: 25px;

}


.button-area .btn {

    border-radius: 9px;

    padding: 10px 18px;

    font-weight: 500;

}


@media (max-width: 768px) {

    .card {

        margin-top: 20px;

    }


    .card-body {

        padding: 20px;

    }


    .info-row {

        display: block;

    }


    .info-label {

        width: 100%;

        padding-bottom: 7px;

    }


    .info-value {

        padding-top: 7px;

    }

}

</style>

</head>


<body>


<div class="container">


<div class="card">


<!-- =====================================
     HEADER
===================================== -->

<div class="card-header">

<h4>

<i class="bi bi-calendar-check-fill me-2"></i>

Set Appointment Schedule

</h4>

</div>


<div class="card-body">


<?= $message; ?>


<!-- =====================================
     STUDENT INFORMATION
===================================== -->

<div class="info-box mb-4">


<!-- STUDENT -->

<div class="info-row">

<div class="info-label">
Student Name
</div>

<div class="info-value">

<?= htmlspecialchars(
    $appointment['fullname']
); ?>

</div>

</div>


<!-- STUDENT NUMBER -->

<div class="info-row">

<div class="info-label">
Student Number
</div>

<div class="info-value">

<?= htmlspecialchars(
    $appointment['student_number']
); ?>

</div>

</div>


<!-- EMAIL -->

<div class="info-row">

<div class="info-label">
Email
</div>

<div class="info-value">

<?= htmlspecialchars(
    $appointment['email']
); ?>

</div>

</div>


<!-- CONCERN -->

<div class="info-row">

<div class="info-label">
Concern
</div>

<div class="info-value">

<?= htmlspecialchars(
    $appointment['concern'] ?? 'N/A'
); ?>

</div>

</div>


</div>


<!-- =====================================
     SCHEDULE FORM
===================================== -->

<form method="POST">


<!-- DATE -->

<div class="mb-3">

<label
    for="schedule_date"
    class="form-label fw-semibold"
>

Appointment Date

</label>


<input
    type="date"
    id="schedule_date"
    name="schedule_date"
    class="form-control"
    min="<?= date('Y-m-d'); ?>"
    value="<?= htmlspecialchars(
        $appointment['appointment_date'] ?? ''
    ); ?>"
    required
>

</div>


<!-- TIME -->

<div class="mb-4">

<label
    for="schedule_time"
    class="form-label fw-semibold"
>

Appointment Time

</label>


<input
    type="time"
    id="schedule_time"
    name="schedule_time"
    class="form-control"
    value="<?= htmlspecialchars(
        $appointment['appointment_time'] ?? ''
    ); ?>"
    required
>

</div>


<!-- =====================================
     BUTTONS
===================================== -->

<div class="button-area">


<button
    type="submit"
    name="save_schedule"
    class="btn btn-success"
>

<i class="bi bi-calendar-check me-1"></i>

Set Schedule & Approve

</button>


<a
    href="view_appointment.php?id=<?= $appointment_id; ?>"
    class="btn btn-secondary"
>

<i class="bi bi-arrow-left me-1"></i>

Back

</a>


</div>


</form>


</div>

</div>

</div>


</body>

</html>