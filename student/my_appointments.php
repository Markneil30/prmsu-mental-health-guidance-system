<?php

session_start();

include("../includes/db.php");

// =====================================
// CHECK STUDENT LOGIN
// =====================================

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'student'
) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];


// =====================================
// GET STUDENT ID
// =====================================

$stmt = $conn->prepare("
    SELECT student_id
    FROM students
    WHERE user_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database Error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Student account not found.");
}

$student = $result->fetch_assoc();
$student_id = (int) $student['student_id'];

$stmt->close();


// =====================================
// MARK APPOINTMENT NOTIFICATIONS AS READ
// =====================================

$stmt = $conn->prepare("
    UPDATE notifications
    SET is_read = 1
    WHERE user_id = ?
    AND type = 'appointment'
    AND is_read = 0
");

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}


// =====================================
// GET APPOINTMENTS
// =====================================

$sql = "
    SELECT
        appointment_id,
        appointment_date,
        appointment_time,
        concern,
        status,
        created_at
    FROM appointments
    WHERE student_id = ?
    ORDER BY appointment_date DESC, appointment_time DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(
        "Appointment Database Error: " .
        htmlspecialchars($conn->error)
    );
}

$stmt->bind_param("i", $student_id);
$stmt->execute();

$query = $stmt->get_result();

$total_appointments = $query->num_rows;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Appointments | PRMSU Guidance</title>

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

html,
body {
    width: 100%;
    min-height: 100%;
}

body {
    background: #f4f6f9;
    font-family: "Poppins", sans-serif;
    color: #172554;
    overflow-x: hidden;
    overflow-y: auto;
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

    background: linear-gradient(
        180deg,
        #002147 0%,
        #073b78 100%
    );

    color: white;

    z-index: 1045;

    display: flex;
    flex-direction: column;

    overflow-y: auto;
    overflow-x: hidden;

    -webkit-overflow-scrolling: touch;
}

.sidebar::-webkit-scrollbar {
    width: 5px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255,255,255,.05);
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,.25);
    border-radius: 10px;
}


/* =====================================
   SIDEBAR CLOSE BUTTON
===================================== */

.sidebar-close {
    display: none;

    position: absolute;

    top: 14px;
    right: 14px;

    width: 36px;
    height: 36px;

    border: none;
    border-radius: 8px;

    background: rgba(255,255,255,.10);

    color: white;

    align-items: center;
    justify-content: center;

    font-size: 16px;

    cursor: pointer;

    z-index: 1100;

    transition: background .2s ease;
}

.sidebar-close:hover {
    background: rgba(255,255,255,.20);
}


/* =====================================
   MOBILE HEADER
===================================== */

.mobile-header {
    display: none;

    position: fixed;

    top: 0;
    left: 0;

    width: 100%;

    min-height: 62px;

    z-index: 1040;

    background: #002147;

    color: white;

    padding: 10px 15px;

    align-items: center;

    box-shadow:
        0 2px 10px
        rgba(0,0,0,.12);
}

.mobile-brand {
    display: flex;

    align-items: center;

    gap: 10px;

    flex: 1;

    min-width: 0;
}

.mobile-brand img {
    width: 34px;
    height: 34px;

    object-fit: contain;

    flex-shrink: 0;
}

.mobile-brand span {
    font-size: 15px;

    font-weight: 600;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.toggle-btn {
    width: 40px;
    height: 40px;

    padding: 0;

    margin-left: auto;

    border: 1px solid rgba(255,255,255,.30);

    border-radius: 8px;

    background: transparent;

    color: white;

    font-size: 22px;

    cursor: pointer;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

    transition:
        background .2s ease,
        border-color .2s ease;
}

.toggle-btn:hover {
    background: rgba(255,255,255,.10);

    border-color: rgba(255,255,255,.50);
}


/* =====================================
   BRAND
===================================== */

.brand {
    padding: 20px;

    display: flex;
    align-items: center;

    gap: 12px;

    border-bottom:
        1px solid rgba(255,255,255,.15);

    flex-shrink: 0;
}

.brand img {
    width: 48px;
    height: 48px;

    object-fit: contain;
}

.brand h3 {
    margin: 0;

    color: white;

    font-size: 17px;

    font-weight: 700;
}

.brand small {
    color: rgba(255,255,255,.65);

    font-size: 11px;
}


/* =====================================
   MAIN MENU
===================================== */

.menu-title {
    padding: 22px 20px 8px;

    color: rgba(255,255,255,.45);

    font-size: 11px;

    font-weight: 600;

    text-transform: uppercase;

    letter-spacing: .8px;

    flex-shrink: 0;
}


/* =====================================
   NAVIGATION
===================================== */

.nav-menu {
    padding: 5px 12px 20px;

    flex: 1;
}

.nav-link {
    display: flex;

    align-items: center;

    gap: 12px;

    width: 100%;

    padding: 13px 15px;

    margin-bottom: 5px;

    border-radius: 10px;

    color: rgba(255,255,255,.82);

    text-decoration: none;

    font-size: 13.5px;

    font-weight: 500;

    transition:
        background .2s ease,
        color .2s ease;
}

.nav-link i {
    width: 22px;

    font-size: 18px;

    text-align: center;

    flex-shrink: 0;
}

.nav-link:hover {
    color: white;

    background:
        rgba(255,255,255,.10);
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

.logout-container {
    padding: 10px 12px 20px;

    flex-shrink: 0;

    margin-top: auto;
}

.logout-link {
    display: flex;

    align-items: center;

    gap: 12px;

    width: 100%;

    padding: 13px 15px;

    border-radius: 10px;

    color: rgba(255,255,255,.85);

    background: rgba(255,255,255,.08);

    text-decoration: none;

    font-size: 13.5px;

    font-weight: 500;

    transition:
        background .2s ease,
        color .2s ease;
}

.logout-link i {
    width: 22px;

    font-size: 18px;

    text-align: center;
}

.logout-link:hover {
    color: white;

    background: rgba(220,38,38,.85);
}


/* =====================================
   MAIN CONTENT
===================================== */

.main {
    margin-left: 260px;

    width: calc(100% - 260px);

    min-height: 100vh;

    padding: 35px;

    overflow-x: hidden;

    overflow-y: visible;
}


/* =====================================
   HEADER
===================================== */

.page-header {
    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 25px;
}

.page-title {
    display: flex;

    align-items: center;

    gap: 15px;

    min-width: 0;
}

.title-icon {
    width: 58px;
    height: 58px;

    border-radius: 16px;

    background: #e8efff;

    color: #2563eb;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 27px;

    flex-shrink: 0;
}

.page-title h2 {
    margin: 0;

    font-size: 28px;

    font-weight: 700;

    color: #10204a;

    line-height: 1.25;
}

.page-title p {
    margin: 5px 0 0;

    color: #718096;

    font-size: 13px;

    line-height: 1.5;
}


/* =====================================
   NEW APPOINTMENT BUTTON
===================================== */

.new-btn {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    background: #2563eb;

    color: white;

    border: none;

    border-radius: 10px;

    padding: 13px 20px;

    font-size: 13px;

    font-weight: 600;

    text-decoration: none;

    box-shadow:
        0 6px 15px
        rgba(37,99,235,.20);

    transition:
        background .2s ease,
        transform .2s ease;

    white-space: nowrap;

    flex-shrink: 0;
}

.new-btn:hover {
    background: #1d4ed8;

    color: white;

    transform: translateY(-1px);
}


/* =====================================
   SUMMARY
===================================== */

.summary-card {
    background: white;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    padding: 20px;

    margin-bottom: 22px;

    box-shadow:
        0 6px 20px
        rgba(15,23,42,.05);
}

.summary-icon {
    width: 48px;
    height: 48px;

    border-radius: 12px;

    background: #e8efff;

    color: #2563eb;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 22px;

    flex-shrink: 0;
}

.summary-number {
    font-size: 25px;

    font-weight: 700;

    color: #172554;
}

.summary-label {
    color: #64748b;

    font-size: 12px;
}


/* =====================================
   APPOINTMENT CARD
===================================== */

.appointment-card {
    background: white;

    border-radius: 18px;

    border: 1px solid #e5e7eb;

    box-shadow:
        0 8px 25px
        rgba(15,23,42,.06);

    overflow: hidden;
}

.card-header-custom {
    padding: 21px 24px;

    border-bottom:
        1px solid #e5e7eb;
}

.card-header-custom h5 {
    margin: 0;

    font-size: 17px;

    font-weight: 600;

    color: #172554;
}

.card-header-custom p {
    margin: 4px 0 0;

    color: #64748b;

    font-size: 12px;

    line-height: 1.5;
}


/* =====================================
   TABLE
===================================== */

.table-wrapper {
    width: 100%;

    overflow-x: auto;

    overflow-y: hidden;

    -webkit-overflow-scrolling: touch;

    scrollbar-width: thin;
}

.table-wrapper::-webkit-scrollbar {
    height: 7px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: #cbd5e1;

    border-radius: 10px;
}

.appointment-table {
    width: 100%;

    min-width: 680px;

    margin: 0;

    border-collapse: collapse;
}

.appointment-table th {
    background: #17202b;

    color: white;

    padding: 16px 18px;

    font-size: 12px;

    font-weight: 600;

    text-align: left;

    text-transform: uppercase;

    white-space: nowrap;
}

.appointment-table td {
    padding: 18px;

    vertical-align: middle;

    border-bottom:
        1px solid #e5e7eb;

    font-size: 13px;
}

.appointment-table tbody tr {
    transition: background .2s ease;
}

.appointment-table tbody tr:hover {
    background: #f8faff;
}

.appointment-table tbody tr:last-child td {
    border-bottom: none;
}


/* =====================================
   DATE & TIME & STATUS
===================================== */

.date-box {
    display: flex;

    align-items: center;

    gap: 10px;
}

.date-icon {
    width: 42px;
    height: 42px;

    border-radius: 11px;

    background: #eff6ff;

    color: #2563eb;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;

    flex-shrink: 0;
}

.date-text strong {
    display: block;

    color: #172554;

    font-size: 13px;

    white-space: nowrap;
}

.date-text small {
    color: #64748b;

    font-size: 11px;
}

.time-box {
    display: inline-flex;

    align-items: center;

    gap: 7px;

    color: #334155;

    font-weight: 600;

    white-space: nowrap;
}

.time-box i {
    color: #2563eb;
}

.concern {
    max-width: 320px;

    color: #475569;

    line-height: 1.6;

    word-break: break-word;
}

.status {
    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 7px 12px;

    border-radius: 30px;

    font-size: 11px;

    font-weight: 600;

    white-space: nowrap;
}

.status-dot {
    width: 6px;
    height: 6px;

    border-radius: 50%;

    background: currentColor;
}

.pending {
    background: #fff7ed;
    color: #c2410c;
}

.approved {
    background: #ecfdf5;
    color: #047857;
}

.rejected {
    background: #fef2f2;
    color: #dc2626;
}

.completed {
    background: #eff6ff;
    color: #1d4ed8;
}

.cancelled {
    background: #fef2f2;
    color: #b91c1c;
}

.default-status {
    background: #f1f5f9;
    color: #475569;
}

.created-date {
    color: #64748b;

    white-space: nowrap;

    font-size: 12px;
}

.created-date small {
    font-size: 11px;
}


/* =====================================
   EMPTY
===================================== */

.empty {
    text-align: center;

    padding: 70px 20px;
}

.empty-icon {
    width: 72px;
    height: 72px;

    margin: auto;

    border-radius: 18px;

    background: #f1f5f9;

    color: #94a3b8;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 32px;
}

.empty h5 {
    margin-top: 18px;

    color: #172554;

    font-weight: 600;
}

.empty p {
    color: #94a3b8;

    font-size: 13px;

    margin-bottom: 20px;
}


/* =====================================
   BOOTSTRAP OFFCANVAS
===================================== */

@media (min-width: 992px) {

    .sidebar.offcanvas-lg {
        position: fixed;

        visibility: visible !important;

        transform: none !important;

        width: 260px !important;

        height: 100vh !important;

        background: linear-gradient(
            180deg,
            #002147 0%,
            #073b78 100%
        );

        border: none;
    }

}


/* =====================================
   TABLET / MOBILE
===================================== */

@media (max-width: 991.98px) {

    body {
        padding-top: 62px;
    }

    .mobile-header {
        display: flex;
    }

    .sidebar {
        width: 270px !important;

        max-width: 85vw;

        height: 100vh !important;

        background: linear-gradient(
            180deg,
            #002147 0%,
            #073b78 100%
        );

        border: none;
    }

    .sidebar-close {
        display: flex;
    }

    .brand {
        padding: 20px 55px 20px 20px;
    }

    .main {
        margin-left: 0;

        width: 100%;

        min-height: calc(100vh - 62px);

        padding: 25px 20px 30px;

        overflow-x: hidden;
    }

    .page-header {
        flex-direction: column;

        align-items: stretch;

        gap: 15px;
    }

    .new-btn {
        width: 100%;

        min-height: 46px;
    }

    .page-title h2 {
        font-size: 25px;
    }

    .page-title p {
        font-size: 13px;
    }

    .appointment-table {
        min-width: 680px;
    }

}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 767.98px) {

    .mobile-header {
        padding: 10px 14px;
    }

    .mobile-brand span {
        font-size: 14px;
    }

    .main {
        padding: 22px 15px 30px;
    }

    .page-title {
        gap: 12px;

        align-items: flex-start;
    }

    .title-icon {
        width: 50px;
        height: 50px;

        border-radius: 14px;

        font-size: 23px;
    }

    .page-title h2 {
        font-size: 23px;

        line-height: 1.25;
    }

    .page-title p {
        margin-top: 4px;

        font-size: 12px;
    }

    .summary-card {
        padding: 16px;

        border-radius: 14px;
    }

    .summary-icon {
        width: 44px;
        height: 44px;

        font-size: 20px;
    }

    .summary-number {
        font-size: 22px;
    }

    .summary-label {
        font-size: 11px;
    }

    .appointment-card {
        border-radius: 15px;
    }

    .card-header-custom {
        padding: 17px 16px;
    }

    .card-header-custom h5 {
        font-size: 16px;
    }

    .card-header-custom p {
        font-size: 11.5px;
    }

    .appointment-table th {
        padding: 14px 15px;

        font-size: 11px;
    }

    .appointment-table td {
        padding: 15px;

        font-size: 12px;
    }

    .date-icon {
        width: 38px;
        height: 38px;

        font-size: 16px;
    }

    .date-text strong {
        font-size: 12px;
    }

    .date-text small {
        font-size: 10px;
    }

    .concern {
        max-width: 250px;
    }

    .empty {
        padding: 55px 18px;
    }

}


/* =====================================
   SMALL MOBILE
===================================== */

@media (max-width: 480px) {

    .mobile-header {
        min-height: 60px;
    }

    body {
        padding-top: 60px;
    }

    .toggle-btn {
        width: 38px;
        height: 38px;

        font-size: 21px;
    }

    .mobile-brand img {
        width: 32px;
        height: 32px;
    }

    .mobile-brand span {
        font-size: 13.5px;
    }

    .sidebar {
        width: 250px !important;

        max-width: 84vw;
    }

    .main {
        padding: 20px 12px 25px;
    }

    .page-title {
        gap: 10px;
    }

    .title-icon {
        width: 46px;
        height: 46px;

        font-size: 21px;
    }

    .page-title h2 {
        font-size: 21px;
    }

    .page-title p {
        font-size: 11.5px;
    }

    .new-btn {
        padding: 12px 15px;

        font-size: 12px;
    }

    .summary-card {
        padding: 14px;
    }

    .summary-icon {
        width: 42px;
        height: 42px;

        font-size: 19px;
    }

    .summary-number {
        font-size: 21px;
    }

    .summary-label {
        font-size: 10.5px;
    }

    .card-header-custom {
        padding: 15px;
    }

    .card-header-custom h5 {
        font-size: 15px;
    }

    .card-header-custom p {
        font-size: 11px;
    }

}


/* =====================================
   EXTRA SMALL
===================================== */

@media (max-width: 360px) {

    .mobile-brand span {
        font-size: 13px;
    }

    .main {
        padding-left: 10px;
        padding-right: 10px;
    }

    .page-title h2 {
        font-size: 20px;
    }

    .title-icon {
        width: 43px;
        height: 43px;

        font-size: 19px;
    }

    .new-btn {
        font-size: 11.5px;
    }

}

</style>

</head>


<body>


<!-- =====================================
     MOBILE TOP BAR
===================================== -->

<div class="mobile-header">

    <div class="mobile-brand">

        <img
            src="../assets/images/prmsu-logo.png"
            alt="PRMSU Logo"
        >

        <span>PRMSU Guidance</span>

    </div>

    <button
        type="button"
        class="toggle-btn"
        data-bs-toggle="offcanvas"
        data-bs-target="#sidebarMenu"
        aria-controls="sidebarMenu"
        aria-label="Open navigation menu"
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
    aria-labelledby="sidebarLabel"
>


    <!-- CLOSE BUTTON -->

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

            <h3 id="sidebarLabel">
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

        <a
            href="dashboard.php"
            class="nav-link"
        >
            <i class="bi bi-speedometer2"></i>

            <span>
                Dashboard
            </span>
        </a>


        <a
            href="request_appointment.php"
            class="nav-link"
        >
            <i class="bi bi-calendar-plus"></i>

            <span>
                Request Appointment
            </span>
        </a>


        <a
            href="my_appointments.php"
            class="nav-link active"
        >
            <i class="bi bi-calendar-check"></i>

            <span>
                My Appointments
            </span>
        </a>


        <a
            href="guidance_request.php"
            class="nav-link"
        >
            <i class="bi bi-chat-left-text"></i>

            <span>
                Guidance Request
            </span>
        </a>


        <a
            href="my_requests.php"
            class="nav-link"
        >
            <i class="bi bi-file-earmark-text"></i>

            <span>
                My Guidance Requests
            </span>
        </a>


        <a
            href="announcements.php"
            class="nav-link"
        >
            <i class="bi bi-megaphone-fill"></i>

            <span>
                Announcements
            </span>
        </a>

    </div>


    <!-- LOGOUT -->

    <div class="logout-container">

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

        <div class="page-title">

            <div class="title-icon">

                <i class="bi bi-calendar-check"></i>

            </div>

            <div>

                <h2>
                    My Appointments
                </h2>

                <p>
                    View and monitor your appointment requests.
                </p>

            </div>

        </div>


        <a
            href="request_appointment.php"
            class="new-btn"
        >

            <i class="bi bi-plus-lg"></i>

            New Appointment

        </a>

    </div>


    <!-- SUMMARY -->

    <div class="summary-card">

        <div class="d-flex align-items-center gap-3">

            <div class="summary-icon">

                <i class="bi bi-calendar2-check"></i>

            </div>

            <div>

                <div class="summary-number">
                    <?= $total_appointments; ?>
                </div>

                <div class="summary-label">
                    Total Appointments
                </div>

            </div>

        </div>

    </div>


    <!-- APPOINTMENTS -->

    <div class="appointment-card">


        <div class="card-header-custom">

            <h5>
                Appointment History
            </h5>

            <p>
                Your submitted appointment requests and their current status.
            </p>

        </div>


        <?php if ($total_appointments > 0): ?>


            <div class="table-wrapper">

                <table class="appointment-table">

                    <thead>

                        <tr>

                            <th>
                                Date
                            </th>

                            <th>
                                Time
                            </th>

                            <th>
                                Concern
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Submitted
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while ($row = $query->fetch_assoc()): ?>


                        <?php

                        $status = trim(
                            $row['status'] ?? 'Pending'
                        );

                        $status_class = 'default-status';

                        switch (strtolower($status)) {

                            case 'pending':
                                $status_class = 'pending';
                                break;

                            case 'approved':
                                $status_class = 'approved';
                                break;

                            case 'rejected':
                                $status_class = 'rejected';
                                break;

                            case 'completed':
                                $status_class = 'completed';
                                break;

                            case 'cancelled':
                                $status_class = 'cancelled';
                                break;

                        }

                        ?>


                        <tr>


                            <!-- DATE -->

                            <td>

                                <div class="date-box">

                                    <div class="date-icon">

                                        <i class="bi bi-calendar-event"></i>

                                    </div>

                                    <div class="date-text">

                                        <strong>

                                            <?= htmlspecialchars(
                                                date(
                                                    "M d, Y",
                                                    strtotime(
                                                        $row['appointment_date']
                                                    )
                                                )
                                            ); ?>

                                        </strong>

                                        <small>

                                            <?= htmlspecialchars(
                                                date(
                                                    "l",
                                                    strtotime(
                                                        $row['appointment_date']
                                                    )
                                                )
                                            ); ?>

                                        </small>

                                    </div>

                                </div>

                            </td>


                            <!-- TIME -->

                            <td>

                                <div class="time-box">

                                    <i class="bi bi-clock"></i>

                                    <?= htmlspecialchars(
                                        date(
                                            "h:i A",
                                            strtotime(
                                                $row['appointment_time']
                                            )
                                        )
                                    ); ?>

                                </div>

                            </td>


                            <!-- CONCERN -->

                            <td>

                                <div class="concern">

                                    <?php

                                    $concern = trim(
                                        $row['concern'] ?? ''
                                    );

                                    if ($concern === '') {

                                        echo '<span class="text-muted">
                                                No concern provided.
                                              </span>';

                                    } else {

                                        echo nl2br(
                                            htmlspecialchars($concern)
                                        );

                                    }

                                    ?>

                                </div>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="status <?= htmlspecialchars($status_class); ?>"
                                >

                                    <span class="status-dot"></span>

                                    <?= htmlspecialchars($status); ?>

                                </span>

                            </td>


                            <!-- SUBMITTED -->

                            <td>

                                <div class="created-date">

                                    <i class="bi bi-calendar3 me-1"></i>

                                    <?= htmlspecialchars(
                                        date(
                                            "M d, Y",
                                            strtotime(
                                                $row['created_at']
                                            )
                                        )
                                    ); ?>

                                    <br>

                                    <small>

                                        <?= htmlspecialchars(
                                            date(
                                                "h:i A",
                                                strtotime(
                                                    $row['created_at']
                                                )
                                            )
                                        ); ?>

                                    </small>

                                </div>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <div class="empty">

                <div class="empty-icon">

                    <i class="bi bi-calendar-x"></i>

                </div>

                <h5>
                    No Appointments Yet
                </h5>

                <p>
                    You have not submitted an appointment request yet.
                </p>

                <a
                    href="request_appointment.php"
                    class="btn btn-primary"
                >

                    <i class="bi bi-plus-lg me-1"></i>

                    Request an Appointment

                </a>

            </div>


        <?php endif; ?>


    </div>


</div>


<!-- =====================================
     BOOTSTRAP JS
===================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<!-- =====================================
     CLOSE SIDEBAR AFTER CLICKING A LINK
===================================== -->

<script>

document.querySelectorAll('#sidebarMenu .nav-link').forEach(function(link) {

    link.addEventListener('click', function() {

        if (window.innerWidth < 992) {

            const sidebar =
                document.getElementById('sidebarMenu');

            const offcanvas =
                bootstrap.Offcanvas.getInstance(sidebar);

            if (offcanvas) {
                offcanvas.hide();
            }

        }

    });

});

</script>


</body>
</html>

<?php

$stmt->close();

?>