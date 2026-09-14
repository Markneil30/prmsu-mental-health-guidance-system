<?php

session_start();

include("../includes/db.php");


// =====================================
// CHECK COUNSELOR LOGIN
// =====================================

if (
    !isset($_SESSION['user_id']) ||
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
// GET GUIDANCE REQUEST + STUDENT INFO
// =====================================

$stmt = $conn->prepare("
    SELECT
        guidance_requests.*,
        students.student_number,
        users.fullname,
        users.email
    FROM guidance_requests
    INNER JOIN students
        ON guidance_requests.student_id = students.student_id
    INNER JOIN users
        ON students.user_id = users.id
    WHERE guidance_requests.request_id = ?
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
// CHECK REQUEST
// =====================================

if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: guidance_requests.php");
    exit();
}

$row = $result->fetch_assoc();

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

<title>View Guidance Request</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    rel="stylesheet"
>


<style>

/* =====================================
   GLOBAL
===================================== */

* {
    box-sizing: border-box;
}

html,
body {
    width: 100%;
    min-height: 100%;
}

body {

    margin: 0;

    background: #f4f6f9;

    font-family:
        Poppins,
        Arial,
        sans-serif;

    color: #212529;

}


/* =====================================
   CONTAINER
===================================== */

.page-container {

    width: 100%;

    max-width: 950px;

    margin: 0 auto;

    padding: 30px 20px;

}


/* =====================================
   CARD
===================================== */

.request-card {

    background: #ffffff;

    border-radius: 14px;

    overflow: hidden;

    border: 0;

    box-shadow:
        0 5px 18px
        rgba(0,0,0,.07);

}


/* =====================================
   HEADER
===================================== */

.request-header {

    background: #0d6efd;

    color: #ffffff;

    padding: 17px 22px;

}

.request-header h4 {

    margin: 0;

    font-size: 19px;

    font-weight: 600;

}


/* =====================================
   BODY
===================================== */

.request-body {

    padding: 25px;

}


/* =====================================
   INFORMATION TABLE
===================================== */

.info-table {

    width: 100%;

    margin-bottom: 0;

}

.info-table th {

    width: 220px;

    background: #f8f9fa;

    font-size: 13px;

    font-weight: 600;

    padding: 12px;

    vertical-align: middle;

}

.info-table td {

    font-size: 13px;

    padding: 12px;

    vertical-align: middle;

    word-break: break-word;

}


/* =====================================
   MESSAGE
===================================== */

.message-content {

    white-space: normal;

    line-height: 1.6;

}


/* =====================================
   STATUS
===================================== */

.status-badge {

    display: inline-block;

    padding: 6px 10px;

    border-radius: 6px;

    font-size: 12px;

    font-weight: 600;

}


/* =====================================
   ACTION BUTTONS
===================================== */

.action-buttons {

    display: flex;

    align-items: center;

    gap: 8px;

    margin-top: 22px;

    flex-wrap: wrap;

}

.action-buttons .btn {

    font-size: 13px;

    padding: 8px 14px;

    border-radius: 7px;

}


/* =====================================
   TABLET
===================================== */

@media (max-width: 768px) {

    .page-container {

        padding: 18px 12px;

    }

    .request-card {

        border-radius: 11px;

    }

    .request-header {

        padding: 14px 16px;

    }

    .request-header h4 {

        font-size: 17px;

    }

    .request-body {

        padding: 15px;

    }

    .info-table th {

        width: 170px;

        font-size: 12px;

        padding: 10px;

    }

    .info-table td {

        font-size: 12px;

        padding: 10px;

    }

    .action-buttons {

        gap: 7px;

    }

    .action-buttons .btn {

        font-size: 12px;

        padding: 7px 11px;

    }

}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 480px) {

    .page-container {

        padding: 12px 8px;

    }

    .request-header {

        padding: 13px 14px;

    }

    .request-header h4 {

        font-size: 15px;

    }

    .request-body {

        padding: 10px;

    }


    /* STACK INFORMATION */

    .info-table,
    .info-table tbody,
    .info-table tr,
    .info-table th,
    .info-table td {

        display: block;

        width: 100%;

    }

    .info-table tr {

        margin-bottom: 10px;

        border: 1px solid #dee2e6;

        border-radius: 7px;

        overflow: hidden;

    }

    .info-table th {

        background: #f8f9fa;

        border: 0;

        border-bottom: 1px solid #dee2e6;

        padding: 8px 10px;

        font-size: 11.5px;

    }

    .info-table td {

        border: 0;

        padding: 9px 10px;

        font-size: 12px;

    }


    /* =====================================
       MOBILE BUTTONS
    ===================================== */

    .action-buttons {

        flex-direction: column;

        align-items: stretch;

        width: 100%;

    }

    .action-buttons .btn {

        width: 100%;

        min-height: 42px;

        font-size: 12px;

    }

}


/* =====================================
   VERY SMALL PHONE
===================================== */

@media (max-width: 360px) {

    .page-container {

        padding: 10px 6px;

    }

    .request-header h4 {

        font-size: 14px;

    }

    .request-body {

        padding: 8px;

    }

    .info-table th {

        font-size: 11px;

    }

    .info-table td {

        font-size: 11.5px;

    }

}

</style>

</head>


<body>


<div class="page-container">

    <div class="request-card">


        <!-- =====================================
             HEADER
        ===================================== -->

        <div class="request-header">

            <h4>

                <i class="bi bi-eye-fill me-1"></i>

                View Guidance Request

            </h4>

        </div>


        <!-- =====================================
             BODY
        ===================================== -->

        <div class="request-body">


            <!-- =====================================
                 REQUEST INFORMATION
            ===================================== -->

            <div class="table-responsive">

                <table class="table table-bordered info-table">


                    <!-- STUDENT NAME -->

                    <tr>

                        <th>
                            Student Name
                        </th>

                        <td>
                            <?= htmlspecialchars(
                                $row['fullname']
                            ); ?>
                        </td>

                    </tr>


                    <!-- STUDENT NUMBER -->

                    <tr>

                        <th>
                            Student Number
                        </th>

                        <td>
                            <?= htmlspecialchars(
                                $row['student_number']
                            ); ?>
                        </td>

                    </tr>


                    <!-- EMAIL -->

                    <tr>

                        <th>
                            Email
                        </th>

                        <td>
                            <?= htmlspecialchars(
                                $row['email']
                            ); ?>
                        </td>

                    </tr>


                    <!-- SUBJECT -->

                    <tr>

                        <th>
                            Subject
                        </th>

                        <td>
                            <?= htmlspecialchars(
                                $row['subject']
                            ); ?>
                        </td>

                    </tr>


                    <!-- MESSAGE -->

                    <tr>

                        <th>
                            Message
                        </th>

                        <td class="message-content">

                            <?php

                            if (
                                trim($row['message']) !== ''
                            ) {

                                echo nl2br(
                                    htmlspecialchars(
                                        $row['message']
                                    )
                                );

                            } else {

                                echo '<span class="text-muted">
                                    No additional message provided.
                                </span>';

                            }

                            ?>

                        </td>

                    </tr>


                    <!-- STATUS -->

                    <tr>

                        <th>
                            Status
                        </th>

                        <td>

                            <?php

                            if (
                                $row['status'] === 'Pending'
                            ) {

                            ?>

                                <span
                                    class="status-badge bg-warning text-dark"
                                >
                                    Pending
                                </span>

                            <?php

                            } elseif (
                                $row['status'] === 'Approved'
                            ) {

                            ?>

                                <span
                                    class="status-badge bg-success text-white"
                                >
                                    Approved
                                </span>

                            <?php

                            } elseif (
                                $row['status'] === 'Rejected'
                            ) {

                            ?>

                                <span
                                    class="status-badge bg-danger text-white"
                                >
                                    Rejected
                                </span>

                            <?php

                            } else {

                            ?>

                                <span
                                    class="status-badge bg-secondary text-white"
                                >
                                    <?= htmlspecialchars(
                                        $row['status']
                                    ); ?>
                                </span>

                            <?php } ?>

                        </td>

                    </tr>


                    <!-- DATE -->

                    <tr>

                        <th>
                            Date Submitted
                        </th>

                        <td>

                            <?= date(
                                "M d, Y h:i A",
                                strtotime(
                                    $row['created_at']
                                )
                            ); ?>

                        </td>

                    </tr>


                </table>

            </div>


            <!-- =====================================
                 ACTION BUTTONS
            ===================================== -->

            <div class="action-buttons">


                <?php if (
                    $row['status'] === 'Pending'
                ) { ?>


                    <!-- APPROVE -->

                    <a
                        href="approve_request.php?id=<?= (int)$row['request_id']; ?>"
                        class="btn btn-success"
                        onclick="return confirm('Approve this guidance request? You will proceed to set the counseling schedule.');"
                    >

                        <i class="bi bi-check-circle me-1"></i>

                        Approve

                    </a>


                    <!-- REJECT -->

                    <a
                        href="reject_request.php?id=<?= (int)$row['request_id']; ?>"
                        class="btn btn-danger"
                        onclick="return confirm('Reject this guidance request?');"
                    >

                        <i class="bi bi-x-circle me-1"></i>

                        Reject

                    </a>


                <?php } ?>


                <?php if (
                    $row['status'] === 'Approved'
                ) { ?>


                    <!-- SET SCHEDULE -->

                    <a
                        href="set_schedule.php?id=<?= (int)$row['request_id']; ?>"
                        class="btn btn-warning"
                    >

                        <i class="bi bi-calendar-plus me-1"></i>

                        Set Schedule

                    </a>


                <?php } ?>


                <!-- BACK -->

                <a
                    href="guidance_requests.php"
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