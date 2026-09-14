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


// =====================================
// GET APPOINTMENT DETAILS
// =====================================

$stmt = $conn->prepare("
    SELECT
        appointments.appointment_id,
        appointments.student_id,
        appointments.appointment_date,
        appointments.appointment_time,
        appointments.concern,
        appointments.status,
        appointments.created_at,

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
    die(
        "Database Error: " .
        htmlspecialchars($conn->error)
    );
}


$stmt->bind_param(
    "i",
    $appointment_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows == 0) {

    $stmt->close();

    header("Location: appointment_requests.php");

    exit();
}


$appointment = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>View Appointment</title>


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

    color: #475569;

    background: white;

}


.status-badge {

    display: inline-block;

    padding: 6px 12px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: 600;

}


.status-pending {

    background: #fef3c7;

    color: #92400e;

}


.status-approved {

    background: #dcfce7;

    color: #166534;

}


.status-rejected {

    background: #fee2e2;

    color: #991b1b;

}


.status-completed {

    background: #dbeafe;

    color: #1e40af;

}


.button-area {

    display: flex;

    gap: 8px;

    flex-wrap: wrap;

    margin-top: 25px;

}


.button-area .btn {

    border-radius: 9px;

    padding: 10px 17px;

    font-weight: 500;

}


@media (max-width: 768px) {

    .container {

        padding-left: 15px;

        padding-right: 15px;

    }


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

Appointment Details

</h4>

</div>


<div class="card-body">


<!-- =====================================
     APPOINTMENT INFORMATION
===================================== -->

<div class="info-box">


<!-- STUDENT NAME -->

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


<!-- APPOINTMENT DATE -->

<div class="info-row">

<div class="info-label">
Appointment Date
</div>

<div class="info-value">

<?php

if (
    !empty($appointment['appointment_date']) &&
    $appointment['appointment_date'] != '0000-00-00'
) {

    echo htmlspecialchars(
        date(
            "F d, Y",
            strtotime(
                $appointment['appointment_date']
            )
        )
    );

} else {

    echo '<span class="text-muted">
            Not scheduled
          </span>';

}

?>

</div>

</div>


<!-- APPOINTMENT TIME -->

<div class="info-row">

<div class="info-label">
Appointment Time
</div>

<div class="info-value">

<?php

if (
    !empty($appointment['appointment_time']) &&
    $appointment['appointment_time'] != '00:00:00'
) {

    echo htmlspecialchars(
        date(
            "h:i A",
            strtotime(
                $appointment['appointment_time']
            )
        )
    );

} else {

    echo '<span class="text-muted">
            Not scheduled
          </span>';

}

?>

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


<!-- STATUS -->

<div class="info-row">

<div class="info-label">
Status
</div>

<div class="info-value">

<?php

$status = $appointment['status'];


if ($status == "Pending") {

    echo '
    <span class="status-badge status-pending">
        <i class="bi bi-clock me-1"></i>
        Pending
    </span>
    ';

}

elseif ($status == "Approved") {

    echo '
    <span class="status-badge status-approved">
        <i class="bi bi-check-circle me-1"></i>
        Approved
    </span>
    ';

}

elseif ($status == "Rejected") {

    echo '
    <span class="status-badge status-rejected">
        <i class="bi bi-x-circle me-1"></i>
        Rejected
    </span>
    ';

}

elseif ($status == "Completed") {

    echo '
    <span class="status-badge status-completed">
        <i class="bi bi-check-circle-fill me-1"></i>
        Completed
    </span>
    ';

}

else {

    echo '
    <span class="status-badge">
        ' .
        htmlspecialchars($status) .
        '
    </span>
    ';

}

?>

</div>

</div>


</div>


<!-- =====================================
     ACTION BUTTONS
===================================== -->

<div class="button-area">


<?php if ($appointment['status'] == "Pending") { ?>


<!-- =====================================
     APPROVE
     APPROVE → SET SCHEDULE
===================================== -->

<a
    href="set_appointment_schedule.php?id=<?= $appointment_id; ?>"
    class="btn btn-success"
>

<i class="bi bi-check-circle me-1"></i>

Approve

</a>


<!-- =====================================
     REJECT
===================================== -->

<a
    href="reject_appointment.php?id=<?= $appointment_id; ?>"
    class="btn btn-danger"
    onclick="return confirm('Are you sure you want to reject this appointment?');"
>

<i class="bi bi-x-circle me-1"></i>

Reject

</a>


<?php } ?>


<?php if ($appointment['status'] == "Approved") { ?>


<!-- =====================================
     UPDATE SCHEDULE
===================================== -->

<a
    href="set_appointment_schedule.php?id=<?= $appointment_id; ?>"
    class="btn btn-warning"
>

<i class="bi bi-calendar-plus me-1"></i>

Update Schedule

</a>


<?php } ?>


<!-- BACK -->

<a
    href="appointment_requests.php"
    class="btn btn-secondary"
>

<i class="bi bi-arrow-left me-1"></i>

Back

</a>


</div>


</div>

</div>

</div>


</body>

</html>