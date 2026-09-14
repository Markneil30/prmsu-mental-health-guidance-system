<?php

session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

include("../includes/db.php");

$user_id = (int) $_SESSION['user_id'];

$appointment_count = 0;
$guidance_count = 0;
$announcement_count = 0;


// =====================================
// APPOINTMENT NOTIFICATIONS
// =====================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ?
    AND type = 'appointment'
    AND is_read = 0
");

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $appointment_count = (int) $row['total'];
    }

    $stmt->close();
}


// =====================================
// GUIDANCE NOTIFICATIONS
// =====================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ?
    AND type = 'guidance'
    AND is_read = 0
");

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $guidance_count = (int) $row['total'];
    }

    $stmt->close();
}


// =====================================
// ANNOUNCEMENT NOTIFICATIONS
// =====================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ?
    AND type = 'announcement'
    AND is_read = 0
");

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $announcement_count = (int) $row['total'];
    }

    $stmt->close();
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

    <title>Student Dashboard | PRMSU Guidance</title>

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
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
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
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
        }

        body {
            font-family: "Poppins", Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
            overflow-x: hidden;
        }


        /* =====================================
           SIDEBAR
        ===================================== */

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 245px;
            height: 100vh;
            background: linear-gradient(
                180deg,
                #002147 0%,
                #073b78 100%
            );
            color: white;
            z-index: 1100;
            box-shadow: 4px 0 18px rgba(0, 0, 0, .08);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
        }


        /* =====================================
           BRAND
        ===================================== */

        .brand {
            min-height: 88px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .14);
            flex-shrink: 0;
        }

        .brand img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .brand-text {
            line-height: 1.15;
            min-width: 0;
        }

        .brand-title {
            font-size: 17px;
            font-weight: 700;
            margin: 0;
            white-space: nowrap;
        }

        .brand-subtitle {
            font-size: 10px;
            opacity: .75;
            margin-top: 4px;
            white-space: nowrap;
        }


        /* =====================================
           MOBILE SIDEBAR CLOSE
        ===================================== */

        .sidebar-close {
            display: none;
            position: absolute;
            top: 15px;
            right: 15px;
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 8px;
            background: rgba(255, 255, 255, .12);
            color: white;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            z-index: 10;
        }

        .sidebar-close:hover {
            background: rgba(255, 255, 255, .20);
        }


        /* =====================================
           NAVIGATION
        ===================================== */

        .nav-menu {
            padding: 18px 12px;
            flex: 1;
        }

        .nav-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: .55;
            padding: 0 12px 8px;
        }


        /* =====================================
           SIDEBAR LINKS
        ===================================== */

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, .88);
            text-decoration: none;
            padding: 11px 13px;
            margin-bottom: 4px;
            border-radius: 8px;
            font-size: 13px;
            transition: background .2s ease, color .2s ease, transform .2s ease;
            position: relative;
        }

        .sidebar a i {
            width: 20px;
            min-width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, .12);
            color: white;
            transform: translateX(2px);
        }

        .sidebar a.active {
            background: #0d6efd;
            color: white;
        }


        /* =====================================
           LOGOUT
        ===================================== */

        .logout {
            padding: 0 12px 18px;
            margin-top: auto;
            flex-shrink: 0;
        }

        .logout a {
            color: #ffdede;
            background: rgba(220, 53, 69, .12);
            margin-bottom: 0;
        }

        .logout a:hover {
            background: rgba(220, 53, 69, .22);
            color: white;
        }


        /* =====================================
           MAIN
        ===================================== */

        .main {
            margin-left: 245px;
            min-height: 100vh;
            padding: 28px 32px;
        }


        /* =====================================
           TOPBAR
        ===================================== */

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .welcome {
            min-width: 0;
        }

        .welcome h2 {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            color: #172033;
            line-height: 1.35;
        }

        .welcome p {
            margin: 5px 0 0;
            color: #718096;
            font-size: 13px;
            line-height: 1.5;
        }


        /* =====================================
           USER BOX
        ===================================== */

        .user-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 7px 12px;
            border-radius: 9px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .05);
            flex-shrink: 0;
        }

        .user-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #e8f1ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            max-width: 180px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }


        /* =====================================
           WELCOME PANEL
        ===================================== */

        .welcome-panel {
            background: linear-gradient(
                135deg,
                #002147,
                #0d6efd
            );
            border-radius: 13px;
            padding: 22px 25px;
            color: white;
            margin-bottom: 22px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(13, 110, 253, .18);
        }

        .welcome-panel::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .07);
            right: -50px;
            top: -70px;
        }

        .welcome-panel h3 {
            font-size: 19px;
            margin: 0 0 5px;
            font-weight: 600;
            line-height: 1.4;
            position: relative;
            z-index: 1;
        }

        .welcome-panel p {
            margin: 0;
            font-size: 13px;
            opacity: .85;
            line-height: 1.6;
            position: relative;
            z-index: 1;
            max-width: 900px;
        }


        /* =====================================
           SECTION TITLE
        ===================================== */

        .section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 12px;
        }

        .section-title h5 {
            font-size: 15px;
            font-weight: 600;
            margin: 0;
        }

        .section-title span {
            font-size: 11px;
            color: #7b8794;
            white-space: nowrap;
        }


        /* =====================================
           DISPLAY CARDS
        ===================================== */

        .display-card {
            background: white;
            border: 1px solid #e9eef5;
            border-radius: 11px;
            padding: 22px;
            height: 100%;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .04);
            user-select: none;
        }

        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 15px;
        }

        .icon-blue {
            background: #e8f1ff;
            color: #0d6efd;
        }

        .icon-green {
            background: #e7f7ef;
            color: #198754;
        }

        .icon-red {
            background: #fff0f0;
            color: #dc3545;
        }

        .display-card h5 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .display-card p {
            color: #7a8494;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 0;
        }


        /* =====================================
           COUNT
        ===================================== */

        .count-summary {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
            display: flex;
            align-items: baseline;
            gap: 8px;
            flex-wrap: wrap;
            line-height: 1.2;
        }

        .count-summary .count-label {
            font-size: 12px;
            font-weight: 500;
            color: #8c98a4;
        }


        /* =====================================
           MOBILE NAVBAR
        ===================================== */

        .mobile-navbar {
            display: none;
        }

        .mobile-brand {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .mobile-brand img {
            width: 38px;
            height: 38px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .mobile-brand span {
            font-size: 15px;
            font-weight: 600;
            white-space: nowrap;
        }

        .mobile-menu-btn {
            width: 42px;
            height: 42px;
            padding: 0;
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 9px;
            background: transparent;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            flex-shrink: 0;
        }

        .mobile-menu-btn:hover,
        .mobile-menu-btn:focus {
            background: rgba(255, 255, 255, .10);
            color: white;
        }


        /* =====================================
           TABLET
        ===================================== */

        @media (max-width: 1199.98px) {

            .main {
                padding: 25px 24px;
            }

            .display-card {
                padding: 20px;
            }

            .welcome h2 {
                font-size: 22px;
            }

        }


        /* =====================================
           MOBILE / TABLET SIDEBAR
        ===================================== */

        @media (max-width: 991.98px) {

            .mobile-navbar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 64px;
                padding: 10px 15px;
                background: #002147;
                display: flex;
                align-items: center;
                justify-content: space-between;
                z-index: 1045;
                box-shadow: 0 3px 12px rgba(0, 0, 0, .12);
            }

            .sidebar {
                width: 270px;
                max-width: 85vw;
                z-index: 1055;
                box-shadow: 6px 0 22px rgba(0, 0, 0, .18);
            }

            .sidebar-close {
                display: flex;
            }

            .brand {
                min-height: 82px;
                padding: 14px 18px;
                padding-right: 55px;
            }

            .brand img {
                width: 48px;
                height: 48px;
            }

            .brand-title {
                font-size: 16px;
            }

            .brand-subtitle {
                font-size: 10px;
            }

            .nav-menu {
                padding: 20px 12px;
            }

            .nav-label {
                font-size: 10px;
            }

            .sidebar a {
                padding: 12px 13px;
                margin-bottom: 5px;
                font-size: 14px;
            }

            .sidebar a i {
                width: 22px;
                min-width: 22px;
                font-size: 17px;
            }

            .logout {
                padding: 0 12px 20px;
            }

            .main {
                margin-left: 0;
                width: 100%;
                padding: 88px 22px 28px;
            }

            .topbar {
                margin-bottom: 20px;
            }

            .welcome h2 {
                font-size: 22px;
            }

            .welcome p {
                font-size: 13px;
            }

            .user-box {
                display: none;
            }

            .welcome-panel {
                padding: 20px 22px;
                margin-bottom: 22px;
            }

            .welcome-panel h3 {
                font-size: 18px;
            }

            .welcome-panel p {
                font-size: 13px;
            }

            .section-title h5 {
                font-size: 15px;
            }

            .section-title span {
                font-size: 11px;
            }

            .display-card {
                padding: 20px;
            }

            .display-card h5 {
                font-size: 16px;
            }

            .display-card p {
                font-size: 13px;
            }

            .count-summary {
                font-size: 30px;
            }

        }


        /* =====================================
           SMALL TABLET / LARGE PHONE
        ===================================== */

        @media (max-width: 767.98px) {

            .mobile-navbar {
                height: 62px;
                padding: 9px 14px;
            }

            .mobile-brand img {
                width: 36px;
                height: 36px;
            }

            .mobile-brand span {
                font-size: 14px;
            }

            .mobile-menu-btn {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }

            .main {
                padding: 82px 16px 25px;
            }

            .topbar {
                display: block;
                margin-bottom: 18px;
            }

            .welcome h2 {
                font-size: 21px;
                line-height: 1.4;
            }

            .welcome p {
                font-size: 12.5px;
                line-height: 1.55;
            }

            .welcome-panel {
                padding: 19px;
                border-radius: 12px;
            }

            .welcome-panel h3 {
                font-size: 17px;
                line-height: 1.45;
            }

            .welcome-panel p {
                font-size: 12.5px;
                line-height: 1.65;
            }

            .section-title {
                margin-bottom: 13px;
            }

            .section-title h5 {
                font-size: 15px;
            }

            .section-title span {
                font-size: 10.5px;
            }

            .row.g-4 {
                --bs-gutter-x: 0;
                --bs-gutter-y: 14px;
            }

            .display-card {
                width: 100%;
                padding: 20px;
                border-radius: 12px;
            }

            .icon-box {
                width: 46px;
                height: 46px;
                font-size: 21px;
                margin-bottom: 14px;
            }

            .count-summary {
                font-size: 29px;
                gap: 7px;
            }

            .count-summary .count-label {
                font-size: 11.5px;
            }

            .display-card h5 {
                font-size: 16px;
            }

            .display-card p {
                font-size: 13px;
                line-height: 1.65;
            }

        }


        /* =====================================
           SMALL PHONE
        ===================================== */

        @media (max-width: 480px) {

            .mobile-navbar {
                height: 60px;
                padding: 8px 12px;
            }

            .mobile-brand {
                gap: 8px;
            }

            .mobile-brand img {
                width: 35px;
                height: 35px;
            }

            .mobile-brand span {
                font-size: 13.5px;
            }

            .mobile-menu-btn {
                width: 39px;
                height: 39px;
            }

            .main {
                padding: 78px 13px 22px;
            }

            .welcome h2 {
                font-size: 20px;
            }

            .welcome p {
                font-size: 12px;
            }

            .welcome-panel {
                padding: 17px;
                margin-bottom: 20px;
            }

            .welcome-panel h3 {
                font-size: 16px;
            }

            .welcome-panel p {
                font-size: 12px;
            }

            .section-title {
                align-items: flex-start;
            }

            .section-title h5 {
                font-size: 14.5px;
            }

            .section-title span {
                font-size: 10px;
            }

            .display-card {
                padding: 18px;
            }

            .icon-box {
                width: 44px;
                height: 44px;
                font-size: 20px;
                margin-bottom: 13px;
            }

            .count-summary {
                font-size: 27px;
            }

            .count-summary .count-label {
                font-size: 11px;
            }

            .display-card h5 {
                font-size: 15.5px;
            }

            .display-card p {
                font-size: 12.5px;
            }

        }


        /* =====================================
           VERY SMALL PHONE
        ===================================== */

        @media (max-width: 359.98px) {

            .mobile-brand span {
                font-size: 13px;
            }

            .main {
                padding-left: 11px;
                padding-right: 11px;
            }

            .welcome h2 {
                font-size: 19px;
            }

            .welcome p {
                font-size: 11.5px;
            }

            .welcome-panel h3 {
                font-size: 15.5px;
            }

            .welcome-panel p {
                font-size: 11.5px;
            }

            .section-title h5 {
                font-size: 14px;
            }

            .section-title span {
                font-size: 9.5px;
            }

            .display-card {
                padding: 17px;
            }

            .count-summary {
                font-size: 26px;
            }

            .display-card h5 {
                font-size: 15px;
            }

            .display-card p {
                font-size: 12px;
            }

        }

    </style>

</head>


<body>


    <!-- =====================================
         MOBILE NAVBAR
    ===================================== -->

    <div class="mobile-navbar">

        <div class="mobile-brand">

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
            class="mobile-menu-btn"
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
        class="offcanvas-lg offcanvas-start sidebar"
        tabindex="-1"
        id="sidebarMenu"
        aria-label="Student navigation"
    >

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

            <div class="brand-text">

                <p class="brand-title">
                    PRMSU Guidance
                </p>

                <div class="brand-subtitle">
                    Counseling System
                </div>

            </div>

        </div>


        <!-- NAVIGATION -->

        <div class="nav-menu">

            <div class="nav-label">
                Main Menu
            </div>


            <!-- DASHBOARD -->

            <a
                href="dashboard.php"
                class="active"
            >

                <i class="bi bi-grid-1x2-fill"></i>

                <span>
                    Dashboard
                </span>

            </a>


            <!-- REQUEST APPOINTMENT -->

            <a href="request_appointment.php">

                <i class="bi bi-calendar-plus"></i>

                <span>
                    Request Appointment
                </span>

            </a>


            <!-- MY APPOINTMENTS -->

            <a href="my_appointments.php">

                <i class="bi bi-calendar-check"></i>

                <span>
                    My Appointments
                </span>

            </a>


            <!-- GUIDANCE REQUEST -->

            <a href="guidance_request.php">

                <i class="bi bi-chat-left-text"></i>

                <span>
                    Guidance Request
                </span>

            </a>


            <!-- MY GUIDANCE REQUESTS -->

            <a href="my_requests.php">

                <i class="bi bi-file-earmark-text"></i>

                <span>
                    My Guidance Requests
                </span>

            </a>


            <!-- ANNOUNCEMENTS -->

            <a href="announcements.php">

                <i class="bi bi-megaphone"></i>

                <span>
                    Announcements
                </span>

            </a>

        </div>


        <!-- LOGOUT -->

        <div class="logout">

            <a href="../logout.php">

                <i class="bi bi-box-arrow-right"></i>

                <span>
                    Logout
                </span>

            </a>

        </div>

    </div>


    <!-- =====================================
         MAIN
    ===================================== -->

    <div class="main">


        <!-- TOPBAR -->

        <div class="topbar">

            <div class="welcome">

                <h2>
                    Welcome,
                    <?= htmlspecialchars($_SESSION['fullname'] ?? 'Student'); ?>
                    👋
                </h2>

                <p>
                    Student Portal • Guidance and Counseling Services
                </p>

            </div>


            <div class="user-box">

                <div class="user-icon">

                    <i class="bi bi-person-fill"></i>

                </div>

                <div class="user-name">

                    <?= htmlspecialchars($_SESSION['fullname'] ?? 'Student'); ?>

                </div>

            </div>

        </div>


        <!-- WELCOME PANEL -->

        <div class="welcome-panel">

            <h3>
                How can we help you today?
            </h3>

            <p>
                Access counseling services, submit a guidance request,
                manage appointments, and stay updated with announcements.
            </p>

        </div>


        <!-- SECTION -->

        <div class="section-title">

            <h5>
                Notification Overview
            </h5>

            <span>
                Student Portal
            </span>

        </div>


        <!-- CARDS -->

        <div class="row g-4">


            <!-- APPOINTMENTS -->

            <div class="col-12 col-md-6 col-lg-4">

                <div class="display-card">

                    <div class="icon-box icon-blue">

                        <i class="bi bi-calendar-check-fill"></i>

                    </div>

                    <div class="count-summary">

                        <?= $appointment_count; ?>

                        <span class="count-label">

                            Unread Notification<?= $appointment_count == 1 ? '' : 's'; ?>

                        </span>

                    </div>

                    <h5>
                        Appointments
                    </h5>

                    <p>
                        Unread notifications related to your appointments.
                    </p>

                </div>

            </div>


            <!-- GUIDANCE -->

            <div class="col-12 col-md-6 col-lg-4">

                <div class="display-card">

                    <div class="icon-box icon-green">

                        <i class="bi bi-chat-dots-fill"></i>

                    </div>

                    <div class="count-summary">

                        <?= $guidance_count; ?>

                        <span class="count-label">

                            Unread Notification<?= $guidance_count == 1 ? '' : 's'; ?>

                        </span>

                    </div>

                    <h5>
                        Guidance Requests
                    </h5>

                    <p>
                        Unread notifications regarding your guidance requests.
                    </p>

                </div>

            </div>


            <!-- ANNOUNCEMENTS -->

            <div class="col-12 col-md-6 col-lg-4">

                <div class="display-card">

                    <div class="icon-box icon-red">

                        <i class="bi bi-megaphone-fill"></i>

                    </div>

                    <div class="count-summary">

                        <?= $announcement_count; ?>

                        <span class="count-label">

                            Unread Announcement<?= $announcement_count == 1 ? '' : 's'; ?>

                        </span>

                    </div>

                    <h5>
                        Announcements
                    </h5>

                    <p>
                        Unread announcement notifications from the Guidance Office.
                    </p>

                </div>

            </div>


        </div>


    </div>


    <!-- =====================================
         BOOTSTRAP JS
    ===================================== -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>


    <script>

        const sidebarLinks =
            document.querySelectorAll("#sidebarMenu a");

        sidebarLinks.forEach(function(link) {

            link.addEventListener("click", function() {

                if (window.innerWidth <= 991) {

                    const sidebar =
                        document.getElementById("sidebarMenu");

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