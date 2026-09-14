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
        gr.request_id,
        gr.student_id,
        gr.subject,
        gr.status,
        gr.schedule_date,
        gr.schedule_time,

        s.user_id AS student_user_id,
        s.student_number,

        u.fullname,
        u.email

    FROM guidance_requests gr

    INNER JOIN students s
        ON gr.student_id = s.student_id

    INNER JOIN users u
        ON s.user_id = u.id

    WHERE gr.request_id = ?

    LIMIT 1
");

if (!$stmt) {
    die(
        "Database Error: " .
        htmlspecialchars($conn->error)
    );
}

$stmt->bind_param("i", $request_id);
$stmt->execute();

$result = $stmt->get_result();


// =====================================
// REQUEST NOT FOUND
// =====================================

if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: guidance_requests.php");
    exit();
}

$request = $result->fetch_assoc();

$stmt->close();


// =====================================
// ONLY APPROVED REQUESTS
// =====================================

if (
    strtolower(trim($request['status'])) !== 'approved'
) {
    header("Location: guidance_requests.php");
    exit();
}


// =====================================
// VARIABLES
// =====================================

$error = "";


// =====================================
// SAVE COUNSELING SCHEDULE
// =====================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $schedule_date = trim(
        $_POST['schedule_date'] ?? ''
    );

    $schedule_time = trim(
        $_POST['schedule_time'] ?? ''
    );


    // =====================================
    // VALIDATE DATE
    // =====================================

    if ($schedule_date === '') {

        $error =
            "Please select a counseling date.";

    }

    elseif ($schedule_time === '') {

        $error =
            "Please select a counseling time.";

    }

    elseif ($schedule_date < date('Y-m-d')) {

        $error =
            "The counseling date cannot be in the past.";

    }

    else {

        // =====================================
        // START TRANSACTION
        // =====================================

        $conn->begin_transaction();

        try {

            // =====================================
            // UPDATE GUIDANCE REQUEST
            // =====================================

            $update = $conn->prepare("
                UPDATE guidance_requests

                SET
                    schedule_date = ?,
                    schedule_time = ?

                WHERE request_id = ?
            ");

            if (!$update) {

                throw new Exception(
                    "Database Error: " .
                    $conn->error
                );
            }


            $update->bind_param(
                "ssi",
                $schedule_date,
                $schedule_time,
                $request_id
            );


            if (!$update->execute()) {

                throw new Exception(
                    "Error saving counseling schedule: " .
                    $update->error
                );
            }


            $update->close();


            // =====================================
            // GET STUDENT USER ID
            // =====================================

            /*
             * IMPORTANT:
             *
             * notifications.user_id
             * uses users.id.
             *
             * The student_user_id comes from:
             *
             * students.user_id
             *
             */

            $student_user_id =
                (int) $request['student_user_id'];


            if ($student_user_id <= 0) {

                throw new Exception(
                    "Student user account was not found."
                );
            }


            // =====================================
            // FORMAT DATE
            // =====================================

            $formatted_date = date(
                "F d, Y",
                strtotime($schedule_date)
            );


            // =====================================
            // FORMAT TIME
            // =====================================

            $formatted_time = date(
                "h:i A",
                strtotime($schedule_time)
            );


            // =====================================
            // NOTIFICATION MESSAGE
            // =====================================

            $notification_message =
                "Your counseling schedule has been set for " .
                $formatted_date .
                " at " .
                $formatted_time .
                ".";


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
                    'guidance',
                    ?,
                    0,
                    NOW()
                )
            ");


            if (!$notify) {

                throw new Exception(
                    "Notification Database Error: " .
                    $conn->error
                );
            }


            $notify->bind_param(
                "is",
                $student_user_id,
                $notification_message
            );


            if (!$notify->execute()) {

                throw new Exception(
                    "Notification Error: " .
                    $notify->error
                );
            }


            $notify->close();


            // =====================================
            // COMMIT
            // =====================================

            $conn->commit();


            // =====================================
            // REDIRECT
            // =====================================

            header(
                "Location: guidance_requests.php?scheduled=1"
            );

            exit();

        }

        catch (Exception $e) {

            // =====================================
            // ROLLBACK
            // =====================================

            $conn->rollback();

            $error = $e->getMessage();
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

<title>
    Set Counseling Schedule | PRMSU Guidance
</title>


<link
    rel="icon"
    href="../assets/images/prmsu-logo.png"
>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    rel="stylesheet"
>


<link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
>


<style>

/* =====================================
   GLOBAL
===================================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


body {

    background: #f4f7fb;

    font-family:
        "Poppins",
        Arial,
        sans-serif;

    color: #1f2937;

    min-height: 100vh;

}


/* =====================================
   MAIN
===================================== */

.main {

    width: 100%;

    min-height: 100vh;

    padding: 30px;

}


/* =====================================
   CONTAINER
===================================== */

.page-container {

    width: 100%;

    max-width: 850px;

    margin: 0 auto;

}


/* =====================================
   HEADER
===================================== */

.page-header {

    margin-bottom: 20px;

}


.page-header h2 {

    margin: 0;

    font-size: 24px;

    font-weight: 700;

    color: #172033;

}


.page-header p {

    margin: 5px 0 0;

    font-size: 13px;

    color: #718096;

}


/* =====================================
   CARD
===================================== */

.schedule-card {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    overflow: hidden;

    box-shadow:
        0 6px 20px
        rgba(0,0,0,.05);

}


/* =====================================
   CARD HEADER
===================================== */

.card-header-custom {

    background:
        linear-gradient(
            135deg,
            #002147,
            #0d6efd
        );

    color: #ffffff;

    padding: 20px 24px;

}


.card-header-custom h4 {

    margin: 0;

    font-size: 18px;

    font-weight: 600;

}


.card-header-custom p {

    margin: 5px 0 0;

    font-size: 12px;

    color:
        rgba(255,255,255,.82);

}


/* =====================================
   BODY
===================================== */

.card-body-custom {

    padding: 24px;

}


/* =====================================
   STUDENT INFORMATION
===================================== */

.student-info {

    background: #f8fafc;

    border: 1px solid #e5e7eb;

    border-radius: 10px;

    padding: 16px;

    margin-bottom: 22px;

}


.info-row {

    display: flex;

    gap: 10px;

    margin-bottom: 8px;

    font-size: 13px;

}


.info-row:last-child {

    margin-bottom: 0;

}


.info-label {

    min-width: 115px;

    font-weight: 600;

    color: #475569;

}


.info-value {

    color: #1e293b;

}


/* =====================================
   FORM
===================================== */

.form-label {

    font-size: 13px;

    font-weight: 600;

    color: #334155;

    margin-bottom: 7px;

}


.form-control {

    min-height: 44px;

    border-radius: 7px;

    font-size: 13px;

}


.form-control:focus {

    border-color: #0d6efd;

    box-shadow:
        0 0 0 .15rem
        rgba(13,110,253,.15);

}


/* =====================================
   BUTTONS
===================================== */

.form-actions {

    display: flex;

    gap: 8px;

    margin-top: 22px;

}


.form-actions .btn {

    min-height: 42px;

    padding: 8px 16px;

    border-radius: 7px;

    font-size: 12.5px;

    font-weight: 500;

}


/* =====================================
   ALERT
===================================== */

.alert {

    font-size: 12.5px;

    border-radius: 8px;

}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 768px) {

    .main {

        padding: 18px 12px 30px;

    }


    .page-header h2 {

        font-size: 20px;

    }


    .page-header p {

        font-size: 11px;

    }


    .card-header-custom {

        padding: 17px;

    }


    .card-header-custom h4 {

        font-size: 16px;

    }


    .card-header-custom p {

        font-size: 10.5px;

    }


    .card-body-custom {

        padding: 16px;

    }


    .info-row {

        display: block;

        font-size: 12px;

        margin-bottom: 10px;

    }


    .info-label {

        display: block;

        margin-bottom: 2px;

    }


    .form-actions {

        flex-direction: column;

    }


    .form-actions .btn {

        width: 100%;

    }

}


/* =====================================
   SMALL PHONE
===================================== */

@media (max-width: 480px) {

    .main {

        padding: 14px 9px 25px;

    }


    .page-header h2 {

        font-size: 18px;

    }


    .card-header-custom {

        padding: 15px;

    }


    .card-body-custom {

        padding: 13px;

    }

}

</style>

</head>


<body>


<div class="main">

<div class="page-container">


<!-- =====================================
     PAGE HEADER
===================================== -->

<div class="page-header">

<h2>
    Set Counseling Schedule
</h2>

<p>
    Set the date and time for the student's counseling session.
</p>

</div>


<!-- =====================================
     CARD
===================================== -->

<div class="schedule-card">


<!-- HEADER -->

<div class="card-header-custom">

<h4>

<i class="bi bi-calendar-plus me-2"></i>

Counseling Schedule

</h4>

<p>
    Please select the appropriate date and time.
</p>

</div>


<!-- BODY -->

<div class="card-body-custom">


<!-- =====================================
     ERROR
===================================== -->

<?php if ($error !== ''): ?>

<div class="alert alert-danger">

<i class="bi bi-exclamation-circle me-2"></i>

<?= htmlspecialchars($error); ?>

</div>

<?php endif; ?>


<!-- =====================================
     STUDENT INFORMATION
===================================== -->

<div class="student-info">


<div class="info-row">

<span class="info-label">
Student:
</span>

<span class="info-value">

<?= htmlspecialchars(
    $request['fullname']
); ?>

</span>

</div>


<div class="info-row">

<span class="info-label">
Student No.:
</span>

<span class="info-value">

<?= htmlspecialchars(
    $request['student_number']
); ?>

</span>

</div>


<div class="info-row">

<span class="info-label">
Email:
</span>

<span class="info-value">

<?= htmlspecialchars(
    $request['email']
); ?>

</span>

</div>


<div class="info-row">

<span class="info-label">
Subject:
</span>

<span class="info-value">

<?= htmlspecialchars(
    $request['subject']
); ?>

</span>

</div>


<div class="info-row">

<span class="info-label">
Status:
</span>

<span class="info-value">

<span class="badge bg-success">

Approved

</span>

</span>

</div>


</div>


<!-- =====================================
     SCHEDULE FORM
===================================== -->

<form
    method="POST"
    action=""
>


<!-- DATE -->

<div class="mb-3">

<label
    for="schedule_date"
    class="form-label"
>

<i class="bi bi-calendar3 me-1"></i>

Counseling Date

</label>


<input
    type="date"
    id="schedule_date"
    name="schedule_date"
    class="form-control"
    value="<?= htmlspecialchars(
        $request['schedule_date'] ?? ''
    ); ?>"
    min="<?= date('Y-m-d'); ?>"
    required
>

</div>


<!-- TIME -->

<div class="mb-3">

<label
    for="schedule_time"
    class="form-label"
>

<i class="bi bi-clock me-1"></i>

Counseling Time

</label>


<input
    type="time"
    id="schedule_time"
    name="schedule_time"
    class="form-control"
    value="<?= htmlspecialchars(
        $request['schedule_time'] ?? ''
    ); ?>"
    required
>

</div>


<!-- =====================================
     ACTIONS
===================================== -->

<div class="form-actions">


<a
    href="guidance_requests.php"
    class="btn btn-secondary"
>

<i class="bi bi-arrow-left me-1"></i>

Back

</a>


<button
    type="submit"
    class="btn btn-primary"
>

<i class="bi bi-calendar-check me-1"></i>

Save Schedule

</button>


</div>


</form>


</div>

</div>

</div>

</div>


</body>

</html>