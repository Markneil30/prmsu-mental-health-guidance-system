
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
    header("Location: ../login.php");
    exit();
}

// =====================================
// GET APPOINTMENT REQUESTS
// =====================================
$query = mysqli_query($conn, "
    SELECT
        appointments.*,
        users.fullname,
        students.student_number
    FROM appointments
    INNER JOIN students
        ON appointments.student_id = students.student_id
    INNER JOIN users
        ON students.user_id = users.id
    ORDER BY appointments.created_at DESC
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

<title>Appointment Requests | PRMSU Guidance</title>

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
   RESET
===================================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* =====================================
   BODY
===================================== */

html {
    width: 100%;
    overflow-x: hidden;
}

body {
    background: #f4f6f9;
    font-family: "Poppins", sans-serif;
    color: #172554;
    min-height: 100vh;
    overflow-x: hidden;
}


/* =====================================
   MOBILE NAVBAR
===================================== */

.mobile-navbar {
    display: none;
    width: 100%;
    height: 58px;
    background: #002147;
    color: white;
    padding: 9px 15px;

    align-items: center;
    justify-content: space-between;

    position: sticky;
    top: 0;
    z-index: 1040;

    box-shadow: 0 2px 10px rgba(0,0,0,.12);
}

.mobile-brand {
    display: flex;
    align-items: center;
    gap: 9px;
    min-width: 0;
}

.mobile-brand img {
    width: 36px;
    height: 36px;
    object-fit: contain;
    flex-shrink: 0;
}

.mobile-brand span {
    font-size: 14px;
    font-weight: 700;
    white-space: nowrap;
}

.mobile-menu-btn {
    flex-shrink: 0;

    width: 40px;
    height: 38px;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 0;
    border-radius: 7px;

    margin-left: auto;
}

.mobile-menu-btn i {
    font-size: 22px;
}


/* =====================================
   SIDEBAR
===================================== */

.sidebar {
    position: fixed;
    left: 0;
    top: 0;

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

    overflow-y: auto;

    box-shadow:
        4px 0 18px
        rgba(0, 0, 0, .08);

    transition: transform .3s ease;
}


/* =====================================
   SIDEBAR CLOSE BUTTON
===================================== */

.sidebar-close {
    display: none;

    position: absolute;
    top: 15px;
    right: 15px;

    width: 34px;
    height: 34px;

    border: none;
    border-radius: 7px;

    background:
        rgba(255,255,255,.10);

    color: white;

    align-items: center;
    justify-content: center;

    font-size: 18px;

    z-index: 5;
}

.sidebar-close:hover {
    background:
        rgba(255,255,255,.18);
}


/* =====================================
   BRAND
===================================== */

.brand {
    min-height: 88px;

    padding: 18px 20px;

    display: flex;
    align-items: center;

    gap: 12px;

    border-bottom:
        1px solid
        rgba(255,255,255,.15);
}

.brand img {
    width: 48px;
    height: 48px;

    object-fit: contain;
    flex-shrink: 0;
}

.brand h3 {
    margin: 0;

    color: white;

    font-size: 17px;
    font-weight: 700;

    line-height: 1.2;
}

.brand small {
    display: block;

    margin-top: 3px;

    color:
        rgba(255,255,255,.65);

    font-size: 10px;
}


/* =====================================
   MENU TITLE
===================================== */

.menu-title {
    padding:
        22px 20px 8px;

    color:
        rgba(255,255,255,.45);

    font-size: 10px;
    font-weight: 600;

    text-transform: uppercase;

    letter-spacing: .8px;
}


/* =====================================
   NAVIGATION
===================================== */

.nav-menu {
    padding: 5px 12px 15px;
}

.nav-link {
    display: flex;
    align-items: center;

    gap: 12px;

    width: 100%;

    padding: 12px 14px;

    margin-bottom: 5px;

    border-radius: 9px;

    color:
        rgba(255,255,255,.84);

    text-decoration: none;

    font-size: 13px;
    font-weight: 500;

    transition:
        background .2s ease,
        color .2s ease,
        transform .2s ease;
}

.nav-link i {
    width: 22px;
    min-width: 22px;

    font-size: 17px;

    text-align: center;
}

.nav-link:hover {
    color: white;

    background:
        rgba(255,255,255,.10);

    transform:
        translateX(2px);
}

.nav-link.active {
    color: white;

    background: #0d6efd;

    box-shadow:
        0 4px 12px
        rgba(13,110,253,.25);
}


/* =====================================
   LOGOUT
===================================== */

.logout-link {
    color:
        rgba(255,255,255,.84);
}

.logout-link:hover {
    background:
        rgba(220,53,69,.18);

    color: #fff;
}


/* =====================================
   MAIN
===================================== */

.main {
    margin-left: 260px;

    min-height: 100vh;

    padding: 35px;
}


/* =====================================
   PAGE HEADER
===================================== */

.page-header {
    display: flex;

    justify-content: space-between;
    align-items: center;

    gap: 20px;

    margin-bottom: 25px;
}

.title-area {
    display: flex;
    align-items: center;

    gap: 15px;

    min-width: 0;
}

.title-icon {
    width: 58px;
    height: 58px;

    flex-shrink: 0;

    border-radius: 16px;

    background: #e8efff;

    color: #2563eb;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 27px;
}

.title-area h2 {
    margin: 0;

    color: #10204a;

    font-size: 28px;
    font-weight: 700;
}

.title-area p {
    margin: 4px 0 0;

    color: #718096;

    font-size: 13px;
}


/* =====================================
   DATA CARD
===================================== */

.data-card {
    background: white;

    border:
        1px solid #e5e7eb;

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 8px 25px
        rgba(15,23,42,.06);
}

.data-header {
    padding: 21px 24px;

    border-bottom:
        1px solid #e5e7eb;
}

.data-header h5 {
    margin: 0;

    color: #172554;

    font-size: 17px;
    font-weight: 600;
}

.data-header p {
    margin: 4px 0 0;

    color: #64748b;

    font-size: 12px;
}


/* =====================================
   ALERT
===================================== */

.alert {
    font-size: 12px;

    border-radius: 9px;
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

    min-width: 950px;

    margin: 0;

    border-collapse: collapse;

    font-size: 13px;
}

.request-table th {
    padding: 14px 15px;

    background: #17202b;

    color: white;

    font-size: 11px;
    font-weight: 600;

    text-align: left;

    text-transform: uppercase;

    white-space: nowrap;
}

.request-table td {
    padding: 15px;

    border-bottom:
        1px solid #e5e7eb;

    vertical-align: middle;

    white-space: nowrap;

    font-size: 12.5px;
}

.request-table tbody tr {
    transition:
        background .2s ease;
}

.request-table tbody tr:hover {
    background: #f8faff;
}

.request-table tbody tr:last-child td {
    border-bottom: none;
}


/* =====================================
   ACTION
===================================== */

.action-buttons {
    display: flex;
    align-items: center;

    gap: 6px;

    white-space: nowrap;
}

.action-buttons .btn {
    font-size: 11px;

    padding: 6px 10px;

    border-radius: 7px;
}


/* =====================================
   STATUS
===================================== */

.status-badge {
    display: inline-flex;
    align-items: center;

    gap: 5px;

    padding: 6px 10px;

    border-radius: 30px;

    font-size: 10.5px;
    font-weight: 600;

    white-space: nowrap;
}


/* =====================================
   EMPTY
===================================== */

.empty-state {
    text-align: center;

    padding: 65px 20px !important;

    color: #94a3b8;
}

.empty-state i {
    display: block;

    font-size: 38px;

    margin-bottom: 10px;
}


/* =====================================
   BACK BUTTON
===================================== */

.back-btn {
    margin-top: 20px;

    font-size: 12px;

    padding: 8px 14px;

    border-radius: 8px;
}


/* =====================================
   TABLET
===================================== */

@media (max-width: 991px) {

    /* MOBILE NAVBAR */
    .mobile-navbar {
        display: flex;
    }


    /* SIDEBAR */

    .sidebar {
        width: 260px;

        max-width: 85vw;

        transform: translateX(-100%);

        overflow-y: auto;

        box-shadow:
            6px 0 20px
            rgba(0,0,0,.15);
    }

    .sidebar.show {
        transform: translateX(0);
    }


    /* CLOSE BUTTON */

    .sidebar-close {
        display: flex;
    }


    /* BRAND */

    .brand {
        padding-right: 55px;
    }


    /* MAIN */

    .main {
        width: 100%;

        margin-left: 0;

        padding: 25px 20px 30px;
    }


    /* HEADER */

    .title-area h2 {
        font-size: 24px;
    }


    /* TABLE */

    .request-table {
        min-width: 900px;
    }
}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 768px) {

    .mobile-navbar {
        padding: 10px 13px;
    }

    .mobile-brand img {
        width: 35px;
        height: 35px;
    }

    .mobile-brand span {
        font-size: 13.5px;
    }

    .mobile-menu-btn {
        margin-left: auto;
    }


    /* SIDEBAR */

    .sidebar {
        width: 260px;
        max-width: 85vw;
    }


    /* BRAND */

    .brand {
        min-height: 78px;

        padding: 14px 15px;

        padding-right: 55px;
    }

    .brand img {
        width: 43px;
        height: 43px;
    }

    .brand h3 {
        font-size: 15px;
    }

    .brand small {
        font-size: 9px;
    }


    /* MENU */

    .menu-title {
        padding:
            18px 15px 8px;
    }

    .nav-menu {
        padding:
            5px 12px 15px;
    }

    .nav-link {
        padding: 12px 14px;

        font-size: 13px;
    }

    .nav-link:hover {
        transform: none;
    }


    /* MAIN */

    .main {
        padding:
            18px 12px 30px;
    }


    /* HEADER */

    .page-header {
        flex-direction: column;

        align-items: stretch;

        gap: 12px;

        margin-bottom: 18px;
    }

    .title-area {
        gap: 11px;
    }

    .title-icon {
        width: 46px;
        height: 46px;

        border-radius: 12px;

        font-size: 21px;
    }

    .title-area h2 {
        font-size: 19px;

        line-height: 1.3;
    }

    .title-area p {
        font-size: 11px;

        line-height: 1.5;
    }


    /* CARD */

    .data-card {
        border-radius: 13px;
    }

    .data-header {
        padding: 17px;
    }

    .data-header h5 {
        font-size: 15px;
    }

    .data-header p {
        font-size: 10.5px;

        line-height: 1.5;
    }


    /* TABLE */

    .table-wrapper {
        width: 100%;

        overflow-x: auto;

        -webkit-overflow-scrolling: touch;
    }

    .request-table {
        min-width: 900px;

        font-size: 12px;
    }

    .request-table th {
        padding: 12px 13px;

        font-size: 10px;
    }

    .request-table td {
        padding: 13px;

        font-size: 11.5px;
    }


    /* ACTION */

    .action-buttons {
        gap: 5px;
    }

    .action-buttons .btn {
        font-size: 10.5px;

        padding: 5px 8px;
    }


    /* STATUS */

    .status-badge {
        font-size: 10px;

        padding: 5px 9px;
    }


    /* BACK */

    .back-btn {
        width: 100%;

        min-height: 42px;
    }
}


/* =====================================
   SMALL PHONE
===================================== */

@media (max-width: 480px) {

    .mobile-navbar {
        padding: 9px 11px;
    }

    .mobile-brand img {
        width: 33px;
        height: 33px;
    }

    .mobile-brand span {
        font-size: 13px;
    }

    .mobile-menu-btn {
        width: 38px;
        height: 36px;

        margin-left: auto;
    }

    .mobile-menu-btn i {
        font-size: 20px;
    }


    .main {
        padding:
            14px 8px 25px;
    }


    .title-area {
        gap: 8px;
    }

    .title-icon {
        width: 42px;
        height: 42px;

        font-size: 19px;
    }

    .title-area h2 {
        font-size: 18px;
    }

    .title-area p {
        font-size: 10px;
    }


    .data-header {
        padding: 15px;
    }

    .data-header h5 {
        font-size: 14px;
    }

    .data-header p {
        font-size: 10px;
    }


    .request-table {
        min-width: 850px;
    }
}


/* =====================================
   VERY SMALL PHONE
===================================== */

@media (max-width: 360px) {

    .mobile-brand span {
        font-size: 12px;
    }

    .mobile-menu-btn {
        width: 36px;
        height: 35px;

        margin-left: auto;
    }

    .main {
        padding:
            12px 6px 22px;
    }

    .title-area h2 {
        font-size: 17px;
    }

    .title-area p {
        font-size: 9.5px;
    }

    .request-table {
        min-width: 820px;
    }
}

</style>

</head>

<body>


<!-- =====================================
     MOBILE NAVBAR
===================================== -->

<div class="mobile-navbar">

    <!-- LEFT -->
    <div class="mobile-brand">

        <img
            src="../assets/images/prmsu-logo.png"
            alt="PRMSU Logo"
        >

        <span>
            PRMSU Guidance
        </span>

    </div>


    <!-- RIGHT - HAMBURGER -->
    <button
        type="button"
        class="btn btn-outline-light btn-sm mobile-menu-btn"
        data-bs-toggle="offcanvas"
        data-bs-target="#sidebarMenu"
        aria-controls="sidebarMenu"
        aria-label="Open menu"
    >
        <i class="bi bi-list"></i>
    </button>

</div>


<!-- =====================================
     SIDEBAR
===================================== -->

<div
    class="sidebar offcanvas-lg offcanvas-start"
    tabindex="-1"
    id="sidebarMenu"
>


    <!-- CLOSE BUTTON MOBILE -->

    <button
        type="button"
        class="sidebar-close"
        data-bs-dismiss="offcanvas"
        aria-label="Close menu"
    >
        <i class="bi bi-x-lg"></i>
    </button>


    <!-- BRAND -->

    <div class="brand">

        <img
            src="../assets/images/prmsu-logo.png"
            alt="PRMSU Logo"
        >

        <div>

            <h3>
                PRMSU Guidance
            </h3>

            <small>
                Counseling System
            </small>

        </div>

    </div>


    <!-- MENU TITLE -->

    <div class="menu-title">
        Main Menu
    </div>


    <!-- NAVIGATION -->

    <div class="nav-menu">


        <!-- DASHBOARD -->

        <a
            href="dashboard.php"
            class="nav-link"
        >

            <i class="bi bi-speedometer2"></i>

            <span>
                Dashboard
            </span>

        </a>


        <!-- APPOINTMENTS -->

        <a
            href="appointments.php"
            class="nav-link active"
        >

            <i class="bi bi-calendar-check"></i>

            <span>
                Appointment Requests
            </span>

        </a>


        <!-- GUIDANCE REQUESTS -->

        <a
            href="guidance_requests.php"
            class="nav-link"
        >

            <i class="bi bi-chat-left-text"></i>

            <span>
                Guidance Requests
            </span>

        </a>


        <!-- RECORDS -->

        <a
            href="records.php"
            class="nav-link"
        >

            <i class="bi bi-folder2-open"></i>

            <span>
                Counseling Records
            </span>

        </a>


        <!-- ANNOUNCEMENTS -->

        <a
            href="announcements.php"
            class="nav-link"
        >

            <i class="bi bi-megaphone-fill"></i>

            <span>
                Announcements
            </span>

        </a>


        <!-- LOGOUT -->

        <a
            href="../logout.php"
            class="nav-link logout-link"
        >

            <i class="bi bi-box-arrow-right"></i>

            <span>
                Logout
            </span>

        </a>

    </div>

</div>


<!-- =====================================
     MAIN CONTENT
===================================== -->

<div class="main">


    <!-- PAGE HEADER -->

    <div class="page-header">

        <div class="title-area">

            <div class="title-icon">

                <i class="bi bi-calendar-check"></i>

            </div>

            <div>

                <h2>
                    Appointment Requests
                </h2>

                <p>
                    View and manage student appointment requests.
                </p>

            </div>

        </div>

    </div>


    <!-- =====================================
         DATA CARD
    ===================================== -->

    <div class="data-card">


        <!-- HEADER -->

        <div class="data-header">

            <h5>
                Student Appointment Requests
            </h5>

            <p>
                Review appointment requests and set counseling schedules.
            </p>

        </div>


        <!-- ALERT AREA -->

        <div class="p-3">


            <!-- SUCCESS MESSAGE -->

            <?php if (isset($_GET['scheduled'])) { ?>

                <div class="alert alert-success py-2 px-3">

                    <i class="bi bi-check-circle-fill me-1"></i>

                    Counseling schedule successfully set.

                </div>

            <?php } ?>


            <!-- RESET MESSAGE -->

            <?php if (isset($_GET['reset'])) { ?>

                <div class="alert alert-success py-2 px-3">

                    <i class="bi bi-calendar-check-fill me-1"></i>

                    Counseling schedule successfully updated.

                </div>

            <?php } ?>

        </div>


        <!-- TABLE -->

        <div class="table-wrapper">

            <table class="request-table">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Student</th>

                        <th>Student No.</th>

                        <th>Date</th>

                        <th>Time</th>

                        <th>Concern</th>

                        <th>Status</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>


                <?php

                if (mysqli_num_rows($query) > 0) {

                    $count = 1;

                    while (
                        $row = mysqli_fetch_assoc($query)
                    ) {

                ?>

                    <tr>


                        <!-- NUMBER -->

                        <td>
                            <?= $count++; ?>
                        </td>


                        <!-- STUDENT -->

                        <td>

                            <?= htmlspecialchars(
                                $row['fullname']
                            ); ?>

                        </td>


                        <!-- STUDENT NUMBER -->

                        <td>

                            <?= htmlspecialchars(
                                $row['student_number']
                            ); ?>

                        </td>


                        <!-- DATE -->

                        <td>

                        <?php

                        if (
                            !empty($row['appointment_date']) &&
                            $row['appointment_date'] != "0000-00-00"
                        ) {

                            echo date(
                                "M d, Y",
                                strtotime(
                                    $row['appointment_date']
                                )
                            );

                        } else {

                            echo '
                                <span class="text-muted">
                                    Not scheduled
                                </span>
                            ';

                        }

                        ?>

                        </td>


                        <!-- TIME -->

                        <td>

                        <?php

                        if (
                            !empty($row['appointment_time']) &&
                            $row['appointment_time'] != "00:00:00"
                        ) {

                            echo date(
                                "h:i A",
                                strtotime(
                                    $row['appointment_time']
                                )
                            );

                        } else {

                            echo '
                                <span class="text-muted">
                                    Not scheduled
                                </span>
                            ';

                        }

                        ?>

                        </td>


                        <!-- CONCERN -->

                        <td>

                            <?= htmlspecialchars(
                                $row['concern'] ?? ''
                            ); ?>

                        </td>


                        <!-- STATUS -->

                        <td>

                        <?php

                        if ($row['status'] == "Pending") {

                            echo '
                                <span class="status-badge bg-warning text-dark">
                                    <i class="bi bi-circle-fill"></i>
                                    Pending
                                </span>
                            ';

                        }

                        elseif ($row['status'] == "Approved") {

                            echo '
                                <span class="status-badge bg-success text-white">
                                    <i class="bi bi-circle-fill"></i>
                                    Approved
                                </span>
                            ';

                        }

                        elseif ($row['status'] == "Completed") {

                            echo '
                                <span class="status-badge bg-primary text-white">
                                    <i class="bi bi-circle-fill"></i>
                                    Completed
                                </span>
                            ';

                        }

                        elseif ($row['status'] == "Rejected") {

                            echo '
                                <span class="status-badge bg-danger text-white">
                                    <i class="bi bi-circle-fill"></i>
                                    Rejected
                                </span>
                            ';

                        }

                        else {

                            echo '
                                <span class="status-badge bg-secondary text-white">
                                    <i class="bi bi-circle-fill"></i>
                                    ' .
                                    htmlspecialchars(
                                        $row['status']
                                    ) .
                                '
                                </span>
                            ';

                        }

                        ?>

                        </td>


                        <!-- ACTION -->

                        <td>

                            <div class="action-buttons">


                                <!-- VIEW -->

                                <a
                                    href="view_appointment.php?id=<?= (int)$row['appointment_id']; ?>"
                                    class="btn btn-primary btn-sm"
                                >

                                    <i class="bi bi-eye"></i>

                                    View

                                </a>


                                <?php if ($row['status'] == "Approved") { ?>


                                    <!-- SET SCHEDULE -->

                                    <a
                                        href="set_appointment_schedule.php?id=<?= (int)$row['appointment_id']; ?>"
                                        class="btn btn-warning btn-sm"
                                    >

                                        <i class="bi bi-calendar-plus"></i>

                                        Set Schedule

                                    </a>


                                <?php } ?>


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
                            colspan="8"
                            class="empty-state"
                        >

                            <i class="bi bi-calendar-x"></i>

                            No appointment requests found.

                        </td>

                    </tr>


                <?php

                }

                ?>


                </tbody>

            </table>

        </div>


        <!-- BACK -->

        <div class="p-3">

            <a
                href="dashboard.php"
                class="btn btn-secondary back-btn"
            >

                <i class="bi bi-arrow-left me-1"></i>

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

