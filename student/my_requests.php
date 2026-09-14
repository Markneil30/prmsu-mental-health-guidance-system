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
    header("Location: ../login.php");
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
    die(
        "Database Error: " .
        htmlspecialchars($conn->error)
    );
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    $stmt->close();

    die("Student account not found.");
}

$student = $result->fetch_assoc();

$student_id = (int) $student['student_id'];

$stmt->close();


// =====================================
// GET GUIDANCE REQUESTS
// =====================================

$stmt = $conn->prepare("
    SELECT
        request_id,
        subject,
        message,
        status,
        schedule_date,
        schedule_time,
        created_at
    FROM guidance_requests
    WHERE student_id = ?
    ORDER BY created_at DESC
");

if (!$stmt) {
    die(
        "Database Error: " .
        htmlspecialchars($conn->error)
    );
}

$stmt->bind_param(
    "i",
    $student_id
);

$stmt->execute();

$query = $stmt->get_result();

$total_requests = $query->num_rows;

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>My Guidance Requests | PRMSU Guidance</title>

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
   HTML / BODY
===================================== */

html {
    width: 100%;
    min-height: 100%;
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

    position: fixed;

    top: 0;
    left: 0;

    width: 100%;

    min-height: 62px;

    padding: 10px 14px;

    background: #002147;

    align-items: center;

    z-index: 1050;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,.12);
}

.brand-mini {

    display: flex;

    align-items: center;

    gap: 10px;

    flex: 1;

    min-width: 0;
}

.brand-mini img {

    width: 40px;
    height: 40px;

    object-fit: contain;

    flex-shrink: 0;
}

.brand-mini span {

    color: white;

    font-size: 15px;

    font-weight: 600;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.mobile-menu-btn {

    width: 40px;
    height: 40px;

    padding: 0;

    margin-left: auto;

    flex: 0 0 40px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 8px;

    font-size: 20px;
}


/* =====================================
   SIDEBAR
===================================== */

.sidebar {

    width: 260px;

    height: 100vh;

    background:
        linear-gradient(
            180deg,
            #002147 0%,
            #073b78 100%
        );

    color: white;

    z-index: 1045;

    box-shadow:
        4px 0 18px
        rgba(0,0,0,.08);

    display: flex;

    flex-direction: column;

    overflow-y: auto;

    -webkit-overflow-scrolling: touch;
}


/* =====================================
   DESKTOP SIDEBAR
===================================== */

@media (min-width: 992px) {

    .sidebar.offcanvas-lg {

        position: fixed;

        top: 0;
        left: 0;

        visibility: visible !important;

        transform: none !important;

        width: 260px !important;

        height: 100vh !important;

        border: none;
    }
}


/* =====================================
   SIDEBAR BRAND
===================================== */

.brand {

    min-height: 88px;

    padding: 18px 20px;

    display: flex;

    align-items: center;

    gap: 12px;

    flex-shrink: 0;

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

    background:
        rgba(255,255,255,.10);

    color: white;

    align-items: center;

    justify-content: center;

    font-size: 16px;

    cursor: pointer;

    z-index: 1100;
}

.sidebar-close:hover {

    background:
        rgba(255,255,255,.18);

    color: white;
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

    flex-shrink: 0;
}


/* =====================================
   NAVIGATION
===================================== */

.nav-menu {

    padding:
        5px 12px 15px;

    flex: 1;
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

    width: auto;

    padding: 35px;

    overflow-x: hidden;
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

    line-height: 1.3;
}

.title-area p {

    margin: 4px 0 0;

    color: #718096;

    font-size: 13px;

    line-height: 1.5;
}


/* =====================================
   NEW REQUEST
===================================== */

.new-request {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    padding: 12px 18px;

    background: #2563eb;

    color: white;

    border-radius: 10px;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

    white-space: nowrap;

    transition:
        background .2s ease,
        transform .2s ease;
}

.new-request:hover {

    background: #1d4ed8;

    color: white;

    transform:
        translateY(-1px);
}


/* =====================================
   SUMMARY CARD
===================================== */

.summary-card {

    background: white;

    border:
        1px solid #e5e7eb;

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

    flex-shrink: 0;

    border-radius: 12px;

    background: #e8efff;

    color: #2563eb;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 22px;
}

.summary-number {

    font-size: 25px;

    font-weight: 700;

    color: #172554;

    line-height: 1.2;
}

.summary-label {

    color: #64748b;

    font-size: 12px;

    line-height: 1.4;
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

    line-height: 1.5;
}


/* =====================================
   TABLE WRAPPER
===================================== */

.table-wrapper {

    width: 100%;

    max-width: 100%;

    overflow-x: auto;

    overflow-y: visible;

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
}

.request-table th {

    padding: 16px 18px;

    background: #17202b;

    color: white;

    font-size: 12px;

    font-weight: 600;

    text-align: left;

    text-transform: uppercase;

    white-space: nowrap;
}

.request-table td {

    padding: 18px;

    border-bottom:
        1px solid #e5e7eb;

    vertical-align: middle;

    font-size: 13px;
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
   SUBJECT
===================================== */

.subject {

    color: #172554;

    font-weight: 600;

    line-height: 1.5;
}


/* =====================================
   MESSAGE
===================================== */

.message {

    max-width: 400px;

    color: #64748b;

    line-height: 1.6;

    word-break: break-word;

    overflow-wrap: anywhere;
}


/* =====================================
   STATUS
===================================== */

.status {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 6px 12px;

    border-radius: 30px;

    font-size: 11px;

    font-weight: 600;

    white-space: nowrap;
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

.default-status {

    background: #f1f5f9;

    color: #475569;
}


/* =====================================
   SCHEDULE
===================================== */

.schedule {

    min-width: 175px;
}

.schedule-date {

    color: #172554;

    font-weight: 600;

    white-space: nowrap;

    margin-bottom: 4px;
}

.schedule-time {

    color: #64748b;

    font-size: 12px;

    white-space: nowrap;
}

.no-schedule {

    color: #94a3b8;

    font-size: 12px;

    font-style: italic;

    white-space: nowrap;
}


/* =====================================
   DATE
===================================== */

.date {

    color: #64748b;

    white-space: nowrap;
}


/* =====================================
   EMPTY
===================================== */

.empty {

    text-align: center;

    padding: 70px 20px;
}

.empty-icon {

    width: 70px;
    height: 70px;

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

    font-size: 17px;
}

.empty p {

    color: #94a3b8;

    font-size: 13px;

    margin-bottom: 20px;
}


/* =====================================
   TABLET
===================================== */

@media (max-width: 991.98px) {

    body {
        padding-top: 62px;
    }

    .mobile-navbar {
        display: flex;
    }

    .sidebar {

        width: 270px !important;

        max-width: 85vw;

        height: 100vh !important;

        min-height: 100vh;

        position: fixed;

        top: 0;

        left: 0;

        border: none;

        overflow-y: auto;

        -webkit-overflow-scrolling: touch;
    }

    .sidebar-close {
        display: flex;
    }

    .brand {

        min-height: 80px;

        padding: 16px 20px;
    }

    .brand img {

        width: 44px;
        height: 44px;
    }

    .brand h3 {

        font-size: 16px;
    }

    .brand small {

        font-size: 9px;
    }

    .main {

        width: 100%;

        margin-left: 0;

        min-height: calc(100vh - 62px);

        padding: 25px 20px 35px;
    }

    .page-header {

        margin-bottom: 20px;
    }

    .title-area h2 {

        font-size: 24px;
    }

    .title-area p {

        font-size: 12px;
    }

    .new-request {

        padding: 11px 16px;

        font-size: 12px;
    }
}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 767.98px) {

    .mobile-navbar {

        min-height: 60px;

        padding: 9px 12px;
    }

    .brand-mini img {

        width: 38px;
        height: 38px;
    }

    .brand-mini span {

        font-size: 14px;
    }

    .mobile-menu-btn {

        width: 39px;
        height: 39px;

        flex-basis: 39px;

        font-size: 19px;
    }

    .sidebar {

        width: 270px !important;

        max-width: 84vw;
    }

    .brand {

        min-height: 75px;

        padding: 14px 17px;
    }

    .brand img {

        width: 42px;
        height: 42px;
    }

    .brand h3 {

        font-size: 15px;
    }

    .brand small {

        font-size: 9px;
    }

    .menu-title {

        padding:
            18px 17px 7px;

        font-size: 9px;
    }

    .nav-menu {

        padding:
            5px 10px 18px;
    }

    .nav-link {

        padding: 11px 13px;

        font-size: 12px;

        gap: 10px;
    }

    .nav-link i {

        width: 21px;

        min-width: 21px;

        font-size: 16px;
    }

    .main {

        padding: 20px 14px 30px;
    }

    .page-header {

        flex-direction: column;

        align-items: stretch;

        gap: 14px;

        margin-bottom: 18px;
    }

    .title-area {

        gap: 11px;

        align-items: flex-start;
    }

    .title-icon {

        width: 46px;
        height: 46px;

        border-radius: 12px;

        font-size: 21px;
    }

    .title-area h2 {

        font-size: 20px;

        line-height: 1.3;
    }

    .title-area p {

        font-size: 11px;

        line-height: 1.5;
    }

    .new-request {

        width: 100%;

        min-height: 44px;

        font-size: 12px;
    }

    .summary-card {

        padding: 16px;

        margin-bottom: 16px;

        border-radius: 13px;
    }

    .summary-icon {

        width: 43px;
        height: 43px;

        font-size: 19px;
    }

    .summary-number {

        font-size: 22px;
    }

    .summary-label {

        font-size: 11px;
    }

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

    .request-table {

        min-width: 1000px;
    }

    .request-table th {

        padding: 13px 14px;

        font-size: 10px;
    }

    .request-table td {

        padding: 14px;

        font-size: 12px;
    }

    .message {

        max-width: 320px;
    }

    .empty {

        padding: 50px 18px;
    }

    .empty h5 {

        font-size: 16px;
    }

    .empty p {

        font-size: 11px;

        line-height: 1.5;
    }
}


/* =====================================
   SMALL MOBILE
===================================== */

@media (max-width: 480px) {

    .mobile-navbar {

        padding:
            9px 10px;
    }

    .brand-mini {

        gap: 8px;
    }

    .brand-mini img {

        width: 36px;
        height: 36px;
    }

    .brand-mini span {

        font-size: 13px;
    }

    .mobile-menu-btn {

        width: 38px;
        height: 38px;

        flex-basis: 38px;
    }

    .sidebar {

        width: 260px !important;

        max-width: 82vw;
    }

    .brand {

        padding: 12px 14px;
    }

    .brand img {

        width: 40px;
        height: 40px;
    }

    .brand h3 {

        font-size: 14px;
    }

    .brand small {

        font-size: 8.5px;
    }

    .nav-menu {

        padding:
            5px 9px 16px;
    }

    .nav-link {

        padding: 10px 11px;

        font-size: 11px;
    }

    .nav-link i {

        font-size: 15px;
    }

    .main {

        padding: 17px 10px 25px;
    }

    .title-area {

        gap: 9px;
    }

    .title-icon {

        width: 42px;
        height: 42px;

        font-size: 19px;

        border-radius: 11px;
    }

    .title-area h2 {

        font-size: 18px;
    }

    .title-area p {

        font-size: 10.5px;
    }

    .new-request {

        min-height: 43px;

        font-size: 11.5px;
    }

    .summary-card {

        padding: 14px;
    }

    .summary-number {

        font-size: 21px;
    }

    .summary-label {

        font-size: 10.5px;
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

        min-width: 950px;
    }
}


/* =====================================
   VERY SMALL PHONE
===================================== */

@media (max-width: 360px) {

    .brand-mini span {

        font-size: 12px;
    }

    .mobile-menu-btn {

        width: 36px;
        height: 36px;

        flex-basis: 36px;
    }

    .sidebar {

        width: 250px !important;

        max-width: 80vw;
    }

    .brand {

        padding: 10px 12px;
    }

    .brand img {

        width: 37px;
        height: 37px;
    }

    .brand h3 {

        font-size: 13px;
    }

    .brand small {

        font-size: 8px;
    }

    .nav-link {

        padding: 9px 10px;

        font-size: 10px;
    }

    .main {

        padding: 15px 9px 22px;
    }

    .title-area h2 {

        font-size: 17px;
    }

    .title-area p {

        font-size: 10px;
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

        <span>
            PRMSU Guidance
        </span>

    </div>


    <button
        type="button"
        class="btn btn-outline-light btn-sm mobile-menu-btn"
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
    id="sidebarMenu"
    tabindex="-1"
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


        <!-- REQUEST APPOINTMENT -->

        <a
            href="request_appointment.php"
            class="nav-link"
        >

            <i class="bi bi-calendar-plus"></i>

            <span>
                Request Appointment
            </span>

        </a>


        <!-- MY APPOINTMENTS -->

        <a
            href="my_appointments.php"
            class="nav-link"
        >

            <i class="bi bi-calendar-check"></i>

            <span>
                My Appointments
            </span>

        </a>


        <!-- GUIDANCE REQUEST -->

        <a
            href="guidance_request.php"
            class="nav-link"
        >

            <i class="bi bi-chat-left-text"></i>

            <span>
                Guidance Request
            </span>

        </a>


        <!-- MY GUIDANCE REQUESTS -->

        <a
            href="my_requests.php"
            class="nav-link active"
        >

            <i class="bi bi-file-earmark-text"></i>

            <span>
                My Guidance Requests
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

                <i class="bi bi-file-earmark-text"></i>

            </div>


            <div>

                <h2>
                    My Guidance Requests
                </h2>

                <p>
                    View your submitted guidance requests and counseling schedules.
                </p>

            </div>


        </div>


        <a
            href="guidance_request.php"
            class="new-request"
        >

            <i class="bi bi-plus-lg"></i>

            New Request

        </a>


    </div>



    <!-- =====================================
         TOTAL REQUESTS
    ====================================== -->

    <div class="summary-card">

        <div class="d-flex align-items-center gap-3">

            <div class="summary-icon">

                <i class="bi bi-file-text"></i>

            </div>


            <div>

                <div class="summary-number">

                    <?= $total_requests; ?>

                </div>


                <div class="summary-label">

                    Total Guidance Requests

                </div>

            </div>

        </div>

    </div>



    <!-- =====================================
         DATA CARD
    ====================================== -->

    <div class="data-card">


        <div class="data-header">

            <h5>
                Guidance Request History
            </h5>

            <p>
                Your submitted requests, current status, and counseling schedule.
            </p>

        </div>



        <?php if ($total_requests > 0): ?>


            <div class="table-wrapper">


                <table class="request-table">


                    <thead>

                        <tr>

                            <th>
                                Subject
                            </th>

                            <th>
                                Message
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Counseling Schedule
                            </th>

                            <th>
                                Date Submitted
                            </th>

                        </tr>

                    </thead>



                    <tbody>


                    <?php while ($row = $query->fetch_assoc()): ?>


                        <?php

                        // =====================================
                        // STATUS
                        // =====================================

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
                        }


                        // =====================================
                        // SCHEDULE
                        // =====================================

                        $schedule_date =
                            $row['schedule_date'] ?? null;

                        $schedule_time =
                            $row['schedule_time'] ?? null;

                        ?>


                        <tr>


                            <!-- SUBJECT -->

                            <td>

                                <div class="subject">

                                    <?= htmlspecialchars(
                                        $row['subject'] ?? ''
                                    ); ?>

                                </div>

                            </td>



                            <!-- MESSAGE -->

                            <td>

                                <div class="message">

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $row['message'] ?? ''
                                        )
                                    ); ?>

                                </div>

                            </td>



                            <!-- STATUS -->

                            <td>

                                <span
                                    class="status <?= htmlspecialchars(
                                        $status_class
                                    ); ?>"
                                >

                                    <i
                                        class="bi bi-circle-fill"
                                        style="font-size:6px;"
                                    ></i>

                                    <?= htmlspecialchars(
                                        $status
                                    ); ?>

                                </span>

                            </td>



                            <!-- COUNSELING SCHEDULE -->

                            <td>

                                <div class="schedule">


                                    <?php if (
                                        !empty($schedule_date) &&
                                        !empty($schedule_time)
                                    ): ?>


                                        <div class="schedule-date">

                                            <i
                                                class="bi bi-calendar-check me-1"
                                            ></i>

                                            <?= htmlspecialchars(
                                                date(
                                                    "M d, Y",
                                                    strtotime(
                                                        $schedule_date
                                                    )
                                                )
                                            ); ?>

                                        </div>


                                        <div class="schedule-time">

                                            <i
                                                class="bi bi-clock me-1"
                                            ></i>

                                            <?= htmlspecialchars(
                                                date(
                                                    "h:i A",
                                                    strtotime(
                                                        $schedule_time
                                                    )
                                                )
                                            ); ?>

                                        </div>


                                    <?php else: ?>


                                        <div class="no-schedule">

                                            <i
                                                class="bi bi-calendar-x me-1"
                                            ></i>

                                            Schedule not set yet

                                        </div>


                                    <?php endif; ?>


                                </div>

                            </td>



                            <!-- DATE SUBMITTED -->

                            <td>

                                <div class="date">

                                    <i
                                        class="bi bi-calendar3 me-1"
                                    ></i>

                                    <?= htmlspecialchars(
                                        date(
                                            "M d, Y h:i A",
                                            strtotime(
                                                $row['created_at']
                                            )
                                        )
                                    ); ?>

                                </div>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <!-- =====================================
                 EMPTY STATE
            ====================================== -->

            <div class="empty">


                <div class="empty-icon">

                    <i class="bi bi-file-earmark-text"></i>

                </div>


                <h5>
                    No Guidance Requests Yet
                </h5>


                <p>
                    You have not submitted a guidance request yet.
                </p>


                <a
                    href="guidance_request.php"
                    class="btn btn-primary"
                >

                    <i class="bi bi-plus-lg me-1"></i>

                    Submit a Request

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


</body>

</html>


<?php

$stmt->close();

?>