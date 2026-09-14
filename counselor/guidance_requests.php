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
// GET GUIDANCE REQUESTS
// =====================================

$query = mysqli_query($conn, "
    SELECT
        guidance_requests.*,
        users.fullname,
        students.student_number
    FROM guidance_requests
    INNER JOIN students
        ON guidance_requests.student_id = students.student_id
    INNER JOIN users
        ON students.user_id = users.id
    ORDER BY guidance_requests.created_at DESC
");

if (!$query) {
    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );
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

<title>Guidance Requests | PRMSU Guidance</title>

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
    font-family: 'Poppins', sans-serif;
}

html,
body {
    width: 100%;
    min-height: 100%;
}

body {
    background:
        linear-gradient(
            rgba(244,247,251,.92),
            rgba(244,247,251,.92)
        ),
        url("../assets/images/dashboard-bg.jpg");

    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;

    color: #1f2937;
    min-height: 100vh;
}


/* =====================================
   SIDEBAR
===================================== */

.sidebar {
    position: fixed;
    top: 0;
    left: 0;

    width: 260px;
    height: 100vh;

    background:
        linear-gradient(
            180deg,
            #002147 0%,
            #073b78 100%
        );

    color: white;

    z-index: 1050;

    box-shadow:
        4px 0 18px
        rgba(0,0,0,.08);

    transition: transform .3s ease;
}


/* =====================================
   BRAND
===================================== */

.brand {
    height: 88px;

    display: flex;
    align-items: center;

    gap: 12px;

    padding: 15px 20px;

    border-bottom:
        1px solid
        rgba(255,255,255,.14);
}

.brand img {
    width: 48px;
    height: 48px;
    object-fit: contain;
}

.brand-text {
    line-height: 1.2;
}

.brand-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: white;
}

.brand-subtitle {
    font-size: 10px;
    opacity: .75;
    margin-top: 2px;
    text-transform: uppercase;
    letter-spacing: .5px;
}


/* =====================================
   NAVIGATION
===================================== */

.nav-menu {
    padding: 20px 12px;
}

.nav-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: .55;
    padding: 0 12px 8px;
}

.sidebar a {
    display: flex;
    align-items: center;

    gap: 12px;

    color:
        rgba(255,255,255,.85);

    text-decoration: none;

    padding: 12px 14px;

    margin-bottom: 4px;

    border-radius: 8px;

    font-size: 13.5px;
    font-weight: 500;

    transition: all .2s ease;
}

.sidebar a i {
    width: 20px;
    text-align: center;
    font-size: 17px;
}

.sidebar a:hover {
    background:
        rgba(255,255,255,.12);

    color: white;

    transform:
        translateX(3px);
}

.sidebar a.active {
    background: #0d6efd;
    color: white;

    box-shadow:
        0 4px 12px
        rgba(13,110,253,.3);
}


/* =====================================
   LOGOUT
===================================== */

.logout {
    position: absolute;

    bottom: 20px;
    left: 12px;
    right: 12px;
}

.logout a {
    color: #ffdede;

    background:
        rgba(220,53,69,.15);
}

.logout a:hover {
    background: #dc3545;
    color: white;
}


/* =====================================
   MOBILE NAVBAR
===================================== */

.mobile-navbar {
    display: none;

    background: #002147;
    color: white;

    padding: 12px 18px;

    position: sticky;
    top: 0;

    z-index: 1040;

    box-shadow:
        0 2px 10px
        rgba(0,0,0,.1);
}

.brand-mini {
    display: flex;
    align-items: center;
    gap: 10px;
}

.mobile-navbar img {
    width: 36px;
    height: 36px;
}


/* =====================================
   MAIN
===================================== */

.main {
    margin-left: 260px;

    min-height: 100vh;

    padding: 28px 32px;
}


/* =====================================
   PAGE HEADER
===================================== */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    gap: 20px;

    margin-bottom: 24px;
}

.page-title {
    display: flex;
    align-items: center;
    gap: 14px;
}

.title-icon {
    width: 52px;
    height: 52px;

    border-radius: 13px;

    background: #e8f1ff;
    color: #0d6efd;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 24px;
}

.page-title h2 {
    margin: 0;

    font-size: 24px;
    font-weight: 700;

    color: #172033;
}

.page-title p {
    margin: 4px 0 0;

    color: #718096;

    font-size: 12px;
}


/* =====================================
   CARD
===================================== */

.request-card {
    background:
        rgba(255,255,255,.95);

    backdrop-filter: blur(10px);

    border-radius: 14px;

    border: 1px solid #e9eef5;

    box-shadow:
        0 6px 20px
        rgba(0,0,0,.05);

    overflow: hidden;
}


/* =====================================
   CARD HEADER
===================================== */

.request-header {
    background:
        linear-gradient(
            135deg,
            #002147,
            #0d6efd
        );

    color: white;

    padding: 18px 22px;
}

.request-header h4 {
    margin: 0;

    font-size: 17px;
    font-weight: 600;
}


/* =====================================
   CARD BODY
===================================== */

.request-body {
    padding: 20px;
}


/* =====================================
   ALERT
===================================== */

.alert {
    font-size: 12px;
    border-radius: 8px;
}


/* =====================================
   TABLE WRAPPER
===================================== */

.table-wrapper {
    width: 100%;

    overflow-x: auto;

    -webkit-overflow-scrolling: touch;

    scrollbar-width: thin;
}


/* =====================================
   TABLE
===================================== */

.request-table {
    width: 100%;

    min-width: 1050px;

    margin: 0;

    border-collapse: collapse;

    font-size: 12.5px;
}

.request-table th {
    padding: 12px 10px;

    font-size: 11px;
    font-weight: 600;

    white-space: nowrap;

    vertical-align: middle;
}

.request-table td {
    padding: 11px 10px;

    white-space: nowrap;

    vertical-align: middle;
}

.request-table tbody tr {
    transition: background .2s ease;
}

.request-table tbody tr:hover {
    background: #f8faff;
}


/* =====================================
   STATUS
===================================== */

.status-badge {
    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 10.5px;

    font-weight: 600;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-approved {
    background: #d1e7dd;
    color: #0f5132;
}

.status-rejected {
    background: #f8d7da;
    color: #842029;
}

.status-completed {
    background: #cfe2ff;
    color: #084298;
}

.status-default {
    background: #e9ecef;
    color: #495057;
}


/* =====================================
   SCHEDULE
===================================== */

.schedule-date {
    color: #172554;
    font-weight: 600;
}

.schedule-time {
    color: #475569;
    font-size: 11.5px;
}

.no-schedule {
    color: #94a3b8;

    font-size: 11px;

    font-style: italic;
}


/* =====================================
   ACTION BUTTONS
===================================== */

.action-buttons {
    display: flex;

    align-items: center;

    gap: 5px;

    white-space: nowrap;
}

.action-buttons .btn {
    font-size: 10.5px;

    padding: 6px 9px;

    border-radius: 6px;
}


/* =====================================
   BACK BUTTON
===================================== */

.back-btn {
    margin-top: 18px;

    font-size: 12px;

    padding: 7px 13px;
}


/* =====================================
   EMPTY
===================================== */

.empty-state {
    padding: 45px 15px !important;

    text-align: center;

    color: #6c757d;
}

.empty-state i {
    display: block;

    font-size: 34px;

    margin-bottom: 8px;
}


/* =====================================
   TABLET
===================================== */

@media (max-width: 991px) {

    /* SIDEBAR */

    .sidebar {
        width: 260px;

        height: 100vh;

        position: fixed;

        top: 0;
        left: 0;

        transform: translateX(-100%);

        overflow-y: auto;
        overflow-x: hidden;

        -webkit-overflow-scrolling: touch;

        z-index: 1050;
    }

    .sidebar.show {
        transform: translateX(0);
    }


    /* MOBILE NAVBAR */

    .mobile-navbar {
        display: flex;

        align-items: center;

        justify-content: space-between;

        width: 100%;
    }


    /* MAIN */

    .main {
        margin-left: 0;

        width: 100%;

        padding: 20px 16px;
    }

}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 768px) {

    body {
        background:
            #f4f6f9;

        background-attachment:
            scroll;
    }


    /* SIDEBAR */

    .sidebar {
        width: 260px;

        max-width: 85vw;

        transform: translateX(-100%);
    }

    .sidebar.show {
        transform: translateX(0);
    }


    /* BRAND */

    .brand {
        height: 78px;

        padding: 12px 15px;
    }

    .brand img {
        width: 43px;
        height: 43px;
    }

    .brand-title {
        font-size: 15px;
    }

    .brand-subtitle {
        font-size: 9px;
    }


    /* NAVIGATION */

    .nav-menu {
        padding: 18px 12px;
    }

    .nav-label {
        padding-bottom: 8px;
    }

    .sidebar a {
        width: 100%;

        padding: 12px 14px;

        margin-bottom: 5px;

        font-size: 13px;

        white-space: normal;
    }

    .sidebar a:hover {
        transform: none;
    }


    /* LOGOUT */

    .logout {
        position: static;

        margin: 15px 12px 20px;
    }


    /* MAIN */

    .main {
        padding: 16px 10px 25px;
    }


    /* HEADER */

    .page-header {
        flex-direction: column;

        align-items: stretch;

        margin-bottom: 18px;
    }

    .page-title {
        gap: 10px;

        min-width: 0;
    }

    .title-icon {
        width: 44px;
        height: 44px;

        font-size: 20px;

        border-radius: 11px;

        flex-shrink: 0;
    }

    .page-title h2 {
        font-size: 19px;

        line-height: 1.3;
    }

    .page-title p {
        font-size: 10.5px;

        line-height: 1.5;
    }


    /* CARD */

    .request-card {
        border-radius: 11px;
    }

    .request-header {
        padding: 14px 15px;
    }

    .request-header h4 {
        font-size: 15px;

        line-height: 1.4;
    }

    .request-body {
        padding: 11px;
    }


    /* TABLE */

    .table-wrapper {
        width: 100%;

        overflow-x: auto;

        -webkit-overflow-scrolling: touch;
    }

    .request-table {
        min-width: 1000px;

        font-size: 12px;
    }

    .request-table th {
        font-size: 10.5px;

        padding: 8px;

        white-space: nowrap;
    }

    .request-table td {
        padding: 8px;

        white-space: nowrap;
    }


    /* ACTION */

    .action-buttons {
        gap: 5px;
    }

    .action-buttons .btn {
        font-size: 10px;

        padding: 5px 7px;
    }


    /* STATUS */

    .status-badge {
        font-size: 10px;

        padding: 5px 8px;
    }


    /* BACK */

    .back-btn {
        font-size: 11px;

        padding: 7px 12px;
    }

}


/* =====================================
   SMALL PHONE
===================================== */

@media (max-width: 480px) {

    .mobile-navbar {
        padding: 10px 12px;
    }

    .mobile-navbar img {
        width: 34px;
        height: 34px;
    }

    .mobile-navbar .fw-bold {
        font-size: 14px !important;
    }


    .main {
        padding: 13px 7px 22px;
    }


    .page-title {
        gap: 8px;
    }

    .page-title h2 {
        font-size: 18px;
    }

    .page-title p {
        font-size: 10px;
    }


    .title-icon {
        width: 42px;
        height: 42px;

        font-size: 19px;
    }


    .request-header {
        padding: 13px 14px;
    }

    .request-header h4 {
        font-size: 14px;
    }


    .request-body {
        padding: 9px;
    }


    .request-table {
        min-width: 950px;
    }

    .request-table th {
        font-size: 10px;
    }

    .request-table td {
        font-size: 11.5px;
    }

}


/* =====================================
   VERY SMALL PHONE
===================================== */

@media (max-width: 360px) {

    .mobile-navbar {
        padding: 9px 10px;
    }


    .main {
        padding: 12px 6px 20px;
    }


    .page-title h2 {
        font-size: 17px;
    }


    .page-title p {
        font-size: 9.5px;
    }


    .request-table {
        min-width: 900px;
    }

}

</style>

</head>


<body>


<!-- =====================================
     MOBILE NAVBAR
===================================== -->

<div class="mobile-navbar">

    <div class="brand-mini">

        <img
            src="../assets/images/prmsu-logo.png"
            alt="PRMSU Logo"
        >

        <span class="fw-bold">
            PRMSU Guidance
        </span>

    </div>


    <button
        class="btn btn-outline-light btn-sm"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#sidebarMenu"
        aria-controls="sidebarMenu"
        aria-label="Open navigation menu"
    >

        <i class="bi bi-list fs-5"></i>

    </button>

</div>


<!-- =====================================
     SIDEBAR
===================================== -->

<div
    class="offcanvas-lg offcanvas-start sidebar"
    tabindex="-1"
    id="sidebarMenu"
>


    <!-- BRAND -->

    <div class="brand">

        <img
            src="../assets/images/prmsu-logo.png"
            alt="PRMSU Logo"
        >

        <div class="brand-text">

            <p class="brand-title">
                PRMSU Guidance
            </p>

            <div class="brand-subtitle">
                Counselor Portal
            </div>

        </div>

    </div>


    <!-- NAVIGATION -->

    <div class="nav-menu">

        <div class="nav-label">
            Main Menu
        </div>


        <!-- DASHBOARD -->

        <a href="dashboard.php">

            <i class="bi bi-speedometer2"></i>

            Dashboard

        </a>


        <!-- APPOINTMENTS -->

        <a href="appointment_requests.php">

            <i class="bi bi-calendar-check"></i>

            Appointment Requests

        </a>


        <!-- GUIDANCE REQUESTS -->

        <a
            href="guidance_requests.php"
            class="active"
        >

            <i class="bi bi-chat-left-text-fill"></i>

            Guidance Requests

        </a>


        <!-- RECORDS -->

        <a href="records.php">

            <i class="bi bi-folder2-open"></i>

            Counseling Records

        </a>


        <!-- ANNOUNCEMENTS -->

        <a href="announcements.php">

            <i class="bi bi-megaphone-fill"></i>

            Announcements

        </a>

    </div>


    <!-- LOGOUT -->

    <div class="logout">

        <a href="../logout.php">

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>

    </div>

</div>


<!-- =====================================
     MAIN CONTENT
===================================== -->

<div class="main">


    <!-- PAGE HEADER -->

    <div class="page-header">

        <div class="page-title">

            <div class="title-icon">

                <i class="bi bi-chat-left-text-fill"></i>

            </div>


            <div>

                <h2>
                    Guidance Requests
                </h2>

                <p>
                    Review student guidance requests and manage counseling schedules.
                </p>

            </div>

        </div>

    </div>


    <!-- =====================================
         REQUEST CARD
    ===================================== -->

    <div class="request-card">


        <!-- HEADER -->

        <div class="request-header">

            <h4>

                <i class="bi bi-chat-left-text-fill me-1"></i>

                Student Guidance Requests

            </h4>

        </div>


        <!-- BODY -->

        <div class="request-body">


            <!-- SUCCESS MESSAGE -->

            <?php if (isset($_GET['scheduled'])) { ?>

                <div class="alert alert-success py-2 px-3">

                    <i class="bi bi-check-circle-fill me-1"></i>

                    Counseling schedule successfully set.

                </div>

            <?php } ?>


            <?php if (isset($_GET['updated'])) { ?>

                <div class="alert alert-success py-2 px-3">

                    <i class="bi bi-calendar-check-fill me-1"></i>

                    Counseling schedule successfully updated.

                </div>

            <?php } ?>


            <!-- TABLE -->

            <div class="table-wrapper">

                <table
                    class="table table-bordered table-hover request-table"
                >

                    <thead class="table-dark">

                        <tr>

                            <th>#</th>

                            <th>Student</th>

                            <th>Student No.</th>

                            <th>Subject</th>

                            <th>Status</th>

                            <th>Counseling Date</th>

                            <th>Counseling Time</th>

                            <th>Date Submitted</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php

                    $count = 1;

                    if (mysqli_num_rows($query) > 0) {

                        while (
                            $row = mysqli_fetch_assoc($query)
                        ) {

                            $status =
                                trim(
                                    $row['status'] ?? ''
                                );

                    ?>

                        <tr>


                            <!-- NUMBER -->

                            <td>
                                <?= $count++; ?>
                            </td>


                            <!-- STUDENT -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['fullname'] ?? ''
                                ); ?>

                            </td>


                            <!-- STUDENT NUMBER -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['student_number'] ?? ''
                                ); ?>

                            </td>


                            <!-- SUBJECT -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['subject'] ?? ''
                                ); ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php if (
                                    strtolower($status)
                                    === 'pending'
                                ) { ?>

                                    <span
                                        class="status-badge status-pending"
                                    >

                                        <i class="bi bi-clock"></i>

                                        Pending

                                    </span>


                                <?php } elseif (
                                    strtolower($status)
                                    === 'approved'
                                ) { ?>

                                    <span
                                        class="status-badge status-approved"
                                    >

                                        <i class="bi bi-check-circle"></i>

                                        Approved

                                    </span>


                                <?php } elseif (
                                    strtolower($status)
                                    === 'rejected'
                                ) { ?>

                                    <span
                                        class="status-badge status-rejected"
                                    >

                                        <i class="bi bi-x-circle"></i>

                                        Rejected

                                    </span>


                                <?php } elseif (
                                    strtolower($status)
                                    === 'completed'
                                ) { ?>

                                    <span
                                        class="status-badge status-completed"
                                    >

                                        <i class="bi bi-check-circle-fill"></i>

                                        Completed

                                    </span>


                                <?php } else { ?>

                                    <span
                                        class="status-badge status-default"
                                    >

                                        <?= htmlspecialchars(
                                            $status
                                        ); ?>

                                    </span>

                                <?php } ?>

                            </td>


                            <!-- COUNSELING DATE -->

                            <td>

                                <?php

                                if (
                                    !empty(
                                        $row['schedule_date']
                                    ) &&
                                    $row['schedule_date']
                                    != '0000-00-00'
                                ) {

                                ?>

                                    <span class="schedule-date">

                                        <i
                                            class="bi bi-calendar-event me-1"
                                        ></i>

                                        <?= htmlspecialchars(
                                            date(
                                                "M d, Y",
                                                strtotime(
                                                    $row['schedule_date']
                                                )
                                            )
                                        ); ?>

                                    </span>

                                <?php

                                } else {

                                ?>

                                    <span class="no-schedule">
                                        Not scheduled
                                    </span>

                                <?php

                                }

                                ?>

                            </td>


                            <!-- COUNSELING TIME -->

                            <td>

                                <?php

                                if (
                                    !empty(
                                        $row['schedule_time']
                                    ) &&
                                    $row['schedule_time']
                                    != '00:00:00'
                                ) {

                                ?>

                                    <span class="schedule-time">

                                        <i
                                            class="bi bi-clock me-1"
                                        ></i>

                                        <?= htmlspecialchars(
                                            date(
                                                "h:i A",
                                                strtotime(
                                                    $row['schedule_time']
                                                )
                                            )
                                        ); ?>

                                    </span>

                                <?php

                                } else {

                                ?>

                                    <span class="no-schedule">
                                        Not scheduled
                                    </span>

                                <?php

                                }

                                ?>

                            </td>


                            <!-- DATE SUBMITTED -->

                            <td>

                                <?= !empty(
                                    $row['created_at']
                                )
                                    ? htmlspecialchars(
                                        date(
                                            "M d, Y",
                                            strtotime(
                                                $row['created_at']
                                            )
                                        )
                                    )
                                    : 'N/A';
                                ?>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <div class="action-buttons">


                                    <!-- VIEW -->

                                    <a
                                        href="view_request.php?id=<?= (int)$row['request_id']; ?>"
                                        class="btn btn-info btn-sm text-white"
                                    >

                                        <i class="bi bi-eye"></i>

                                        View

                                    </a>


                                    <!-- SET / UPDATE SCHEDULE -->

                                    <?php

                                    if (
                                        strtolower($status)
                                        === 'approved'
                                    ) {

                                    ?>

                                        <a
                                            href="set_schedule.php?id=<?= (int)$row['request_id']; ?>"
                                            class="btn btn-warning btn-sm"
                                        >

                                            <i
                                                class="bi bi-calendar-plus"
                                            ></i>

                                            <?php

                                            if (
                                                !empty(
                                                    $row['schedule_date']
                                                ) &&
                                                !empty(
                                                    $row['schedule_time']
                                                )
                                            ) {

                                                echo "Update Schedule";

                                            } else {

                                                echo "Set Schedule";

                                            }

                                            ?>

                                        </a>

                                    <?php

                                    }

                                    ?>


                                </div>

                            </td>


                        </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <!-- EMPTY -->

                        <tr>

                            <td
                                colspan="9"
                                class="empty-state"
                            >

                                <i
                                    class="bi bi-inbox"
                                ></i>

                                No guidance requests found.

                            </td>

                        </tr>


                    <?php

                    }

                    ?>

                    </tbody>

                </table>

            </div>


            <!-- BACK -->

            <a
                href="dashboard.php"
                class="btn btn-secondary back-btn"
            >

                <i
                    class="bi bi-arrow-left me-1"
                ></i>

                Back

            </a>


        </div>

    </div>

</div>


<!-- =====================================
     BOOTSTRAP JS
===================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>