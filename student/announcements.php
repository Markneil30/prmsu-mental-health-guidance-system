<?php

session_start();

include("../includes/db.php");

/* =====================================
   CHECK STUDENT LOGIN
===================================== */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'student'
) {
    header("Location: ../login.php");
    exit();
}

/* =====================================
   GET ANNOUNCEMENTS
===================================== */

$query = mysqli_query($conn, "
    SELECT
        announcement_id,
        title,
        content,
        created_at
    FROM announcements
    ORDER BY created_at DESC
");

/* =====================================
   CHECK QUERY
===================================== */

if ($query === false) {
    die(
        "<div style='
            font-family:Arial;
            padding:30px;
            color:red;
        '>
            <h3>Announcements Query Error</h3>
            <p>" .
            htmlspecialchars(mysqli_error($conn)) .
            "</p>
        </div>"
    );
}

/* =====================================
   COUNT ANNOUNCEMENTS
===================================== */

$total_announcements = mysqli_num_rows($query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Announcements | PRMSU Guidance</title>

    <!-- BOOTSTRAP -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- BOOTSTRAP ICONS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <!-- GOOGLE FONT -->
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

        html {
            width: 100%;
            overflow-x: hidden;
        }

        body {
            background: #f4f6f9;
            font-family: 'Poppins', sans-serif;
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
            color: white;
            z-index: 1040;
            align-items: center;
            justify-content: flex-start;
            box-shadow: 0 3px 12px rgba(0,0,0,.12);
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
            font-size: 15px;
            font-weight: 600;
            color: white;
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
        }

        .mobile-menu-btn i {
            font-size: 20px;
        }

        /* =====================================
           SIDEBAR
        ===================================== */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 245px;
            height: 100vh;

            background:
                linear-gradient(
                    180deg,
                    #002147 0%,
                    #073b78 100%
                );

            color: white;
            z-index: 1050;

            display: flex;
            flex-direction: column;

            overflow-y: auto;
            overflow-x: hidden;

            -webkit-overflow-scrolling: touch;

            box-shadow:
                4px 0 18px
                rgba(0,0,0,.08);
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
        }

        .sidebar-close:hover {
            background: rgba(255,255,255,.20);
        }

        /* =====================================
           BRAND
        ===================================== */

        .brand {
            min-height: 88px;
            padding: 15px 20px;

            display: flex;
            align-items: center;

            gap: 12px;

            border-bottom:
                1px solid
                rgba(255,255,255,.14);

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
            margin: 0;
            color: white;
            font-size: 17px;
            font-weight: 700;
        }

        .brand-subtitle {
            margin-top: 4px;
            color: rgba(255,255,255,.70);
            font-size: 10px;
        }

        /* =====================================
           MENU TITLE
        ===================================== */

        .menu-title {
            padding: 20px 20px 8px;

            color: rgba(255,255,255,.45);

            font-size: 10px;
            font-weight: 600;

            text-transform: uppercase;
            letter-spacing: 1px;

            flex-shrink: 0;
        }

        /* =====================================
           NAVIGATION
        ===================================== */

        .nav-menu {
            padding: 5px 12px 15px;
            flex: 1;
        }

        .nav-link {
            display: flex;
            align-items: center;

            gap: 12px;

            width: 100%;

            padding: 11px 13px;
            margin-bottom: 4px;

            border-radius: 8px;

            color: rgba(255,255,255,.88);

            text-decoration: none;

            font-size: 13px;
            font-weight: 500;

            transition:
                background .2s ease,
                color .2s ease,
                transform .2s ease;
        }

        .nav-link i {
            width: 20px;
            min-width: 20px;

            text-align: center;

            font-size: 16px;
        }

        .nav-link:hover {
            color: white;

            background:
                rgba(255,255,255,.12);

            transform: translateX(2px);
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
            color: #fecaca !important;
            margin-top: 10px;
        }

        .logout-link:hover {
            background:
                rgba(220,38,38,.18) !important;

            color:
                #ffffff !important;
        }

        /* =====================================
           MAIN CONTENT
        ===================================== */

        .main {
            margin-left: 245px;

            min-height: 100vh;

            padding: 30px 32px;
        }

        /* =====================================
           CONTENT CONTAINER
        ===================================== */

        .content-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
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

        .header-left {
            display: flex;
            align-items: center;

            gap: 15px;

            min-width: 0;
        }

        .back-btn {
            width: 44px;
            height: 44px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            background: white;

            border:
                1px solid
                #e5e7eb;

            border-radius: 11px;

            color: #64748b;

            font-size: 18px;

            text-decoration: none;

            transition: .2s ease;
        }

        .back-btn:hover {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
            transform: translateY(-1px);
        }

        .page-title {
            display: flex;
            align-items: center;

            gap: 13px;

            min-width: 0;
        }

        .page-title-icon {
            width: 50px;
            height: 50px;

            display: flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            background: #e8efff;
            color: #2563eb;

            border-radius: 12px;

            font-size: 21px;
        }

        .page-title h2 {
            margin: 0;

            font-size: 24px;
            font-weight: 700;

            color: #172554;
        }

        .page-title p {
            margin: 3px 0 0;

            font-size: 12px;
            color: #718096;

            line-height: 1.5;
        }

        /* =====================================
           ANNOUNCEMENT COUNT
        ===================================== */

        .announcement-count {
            flex-shrink: 0;

            padding: 10px 15px;

            background: white;

            border:
                1px solid
                #e5e7eb;

            border-radius: 10px;

            color: #64748b;

            font-size: 12px;

            box-shadow:
                0 4px 12px
                rgba(15,23,42,.04);
        }

        .announcement-count strong {
            color: #2563eb;
            font-size: 15px;
        }

        /* =====================================
           MAIN CARD
        ===================================== */

        .announcement-card {
            background: white;

            border:
                1px solid
                #e5e7eb;

            border-radius: 16px;

            overflow: hidden;

            box-shadow:
                0 8px 25px
                rgba(15,23,42,.06);
        }

        /* =====================================
           CARD HEADER
        ===================================== */

        .card-top {
            padding: 20px 24px;

            background:
                linear-gradient(
                    135deg,
                    #002147,
                    #0d6efd
                );

            color: white;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 15px;
        }

        .card-top h5 {
            margin: 0;

            font-size: 16px;
            font-weight: 600;
        }

        .card-top span {
            font-size: 11px;
            color: rgba(255,255,255,.78);
        }

        /* =====================================
           ANNOUNCEMENT LIST
        ===================================== */

        .announcement-list {
            padding: 24px;
            background: #f8fafc;
        }

        /* =====================================
           ANNOUNCEMENT ITEM
        ===================================== */

        .announcement-item {
            position: relative;

            background: white;

            border:
                1px solid
                #e5e7eb;

            border-radius: 14px;

            padding: 20px;

            margin-bottom: 16px;

            box-shadow:
                0 4px 15px
                rgba(15,23,42,.04);

            transition:
                transform .2s ease,
                box-shadow .2s ease,
                border-color .2s ease;

            overflow: hidden;
        }

        .announcement-item:last-child {
            margin-bottom: 0;
        }

        .announcement-item:hover {
            transform: translateY(-2px);

            border-color: #93c5fd;

            box-shadow:
                0 8px 22px
                rgba(37,99,235,.08);
        }

        /* =====================================
           ANNOUNCEMENT TOP
        ===================================== */

        .announcement-top {
            display: flex;

            justify-content: space-between;
            align-items: flex-start;

            gap: 20px;

            margin-bottom: 12px;
        }

        .announcement-heading {
            display: flex;
            align-items: flex-start;

            gap: 12px;

            min-width: 0;
        }

        .announcement-icon {
            width: 40px;
            min-width: 40px;
            height: 40px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            background: #e8efff;
            color: #2563eb;

            font-size: 17px;
        }

        .announcement-title {
            margin: 0;
            padding-top: 2px;

            font-size: 16px;
            font-weight: 600;

            color: #172554;

            line-height: 1.5;

            word-break: break-word;
            overflow-wrap: anywhere;
        }

        /* =====================================
           DATE
        ===================================== */

        .announcement-date {
            display: flex;
            align-items: center;

            gap: 6px;

            white-space: nowrap;

            font-size: 11px;
            color: #64748b;

            flex-shrink: 0;
        }

        .announcement-date i {
            font-size: 13px;
        }

        /* =====================================
           CONTENT
        ===================================== */

        .announcement-text {
            margin: 0 0 0 52px;

            color: #475569;

            font-size: 13px;
            line-height: 1.8;

            word-break: break-word;
            overflow-wrap: anywhere;
        }

        /* =====================================
           EMPTY STATE
        ===================================== */

        .empty-state {
            padding: 65px 20px;

            text-align: center;

            background: white;

            border:
                1px dashed
                #cbd5e1;

            border-radius: 12px;
        }

        .empty-icon {
            width: 70px;
            height: 70px;

            margin: 0 auto 18px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background: #f1f5f9;
            color: #94a3b8;

            font-size: 28px;
        }

        .empty-state h5 {
            font-size: 16px;
            font-weight: 600;
            color: #172554;
        }

        .empty-state p {
            margin-top: 5px;

            font-size: 13px;
            color: #94a3b8;
        }

        /* =====================================
           TABLET / MOBILE SIDEBAR
        ===================================== */

        @media (max-width: 991.98px) {

            .mobile-navbar {
                display: flex;
            }

            .sidebar {
                width: 270px;
                max-width: 85vw;
                height: 100vh;

                position: fixed;
                top: 0;
                left: 0;
            }

            .sidebar-close {
                display: flex;
            }

            .main {
                margin-left: 0;

                padding:
                    82px 20px 30px;
            }

            .page-header {
                align-items: flex-start;

                flex-direction: column;

                gap: 14px;
            }

            .announcement-count {
                align-self: flex-start;
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
            }

            .main {
                padding:
                    76px 12px 30px;
            }

            /* PAGE HEADER */

            .page-header {
                width: 100%;

                gap: 12px;

                margin-bottom: 18px;
            }

            .header-left {
                width: 100%;

                gap: 9px;

                align-items: center;
            }

            .back-btn {
                width: 40px;
                height: 40px;

                font-size: 16px;
            }

            .page-title {
                gap: 9px;

                min-width: 0;
            }

            .page-title-icon {
                width: 42px;
                height: 42px;

                font-size: 18px;
            }

            .page-title h2 {
                font-size: 20px;
                line-height: 1.3;
            }

            .page-title p {
                font-size: 10.5px;
                line-height: 1.4;
            }

            .announcement-count {
                width: 100%;

                text-align: center;

                padding: 9px 12px;

                font-size: 11px;
            }

            /* CARD */

            .announcement-card {
                border-radius: 12px;
            }

            .card-top {
                padding: 17px;

                align-items: flex-start;

                flex-direction: column;

                gap: 4px;
            }

            .card-top h5 {
                font-size: 15px;
            }

            .card-top span {
                font-size: 10px;
            }

            /* LIST */

            .announcement-list {
                padding: 12px;
            }

            /* ITEM */

            .announcement-item {
                padding: 16px;

                border-radius: 12px;
            }

            .announcement-top {
                flex-direction: column;

                gap: 10px;

                margin-bottom: 10px;
            }

            .announcement-heading {
                width: 100%;
                gap: 10px;
            }

            .announcement-icon {
                width: 37px;
                min-width: 37px;
                height: 37px;

                font-size: 15px;
            }

            .announcement-title {
                font-size: 14px;
                line-height: 1.5;
            }

            .announcement-date {
                margin-left: 47px;

                font-size: 10.5px;

                white-space: normal;
            }

            .announcement-text {
                margin-left: 0;

                margin-top: 12px;

                font-size: 12px;
                line-height: 1.7;
            }

            /* EMPTY */

            .empty-state {
                padding: 55px 15px;
            }

            .empty-icon {
                width: 60px;
                height: 60px;

                font-size: 24px;
            }

            .empty-state h5 {
                font-size: 15px;
            }

            .empty-state p {
                font-size: 11px;
                line-height: 1.6;
            }
        }

        /* =====================================
           SMALL MOBILE
        ===================================== */

        @media (max-width: 480px) {

            .mobile-navbar {
                min-height: 58px;
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

            .main {
                padding:
                    73px 10px 25px;
            }

            .header-left {
                gap: 8px;
            }

            .back-btn {
                width: 38px;
                height: 38px;
            }

            .page-title {
                gap: 8px;
            }

            .page-title-icon {
                width: 39px;
                height: 39px;

                font-size: 17px;
            }

            .page-title h2 {
                font-size: 18px;
            }

            .page-title p {
                font-size: 10px;
            }

            .announcement-list {
                padding: 10px;
            }

            .announcement-item {
                padding: 14px;
            }

            .announcement-title {
                font-size: 13px;
            }

            .announcement-text {
                font-size: 11.5px;
            }

            .announcement-date {
                font-size: 10px;
            }
        }

        /* =====================================
           VERY SMALL PHONE
        ===================================== */

        @media (max-width: 360px) {

            .brand-mini span {
                font-size: 12.5px;
            }

            .mobile-navbar {
                padding: 8px 10px;
            }

            .main {
                padding:
                    72px 9px 22px;
            }

            .page-title h2 {
                font-size: 17px;
            }

            .page-title p {
                font-size: 9.5px;
            }

            .announcement-item {
                padding: 13px;
            }

            .announcement-title {
                font-size: 12.5px;
            }

            .announcement-text {
                font-size: 11px;
            }
        }

    </style>

</head>

<body>

    <!-- =====================================
         MOBILE NAVBAR
    ====================================== -->

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
    ====================================== -->

    <div
        class="sidebar offcanvas-lg offcanvas-start"
        tabindex="-1"
        id="sidebarMenu"
        aria-labelledby="sidebarMenuLabel"
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

            <div class="brand-text">

                <p
                    class="brand-title"
                    id="sidebarMenuLabel"
                >
                    PRMSU Guidance
                </p>

                <div class="brand-subtitle">
                    Counseling System
                </div>

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
                <i class="bi bi-grid-1x2-fill"></i>

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
                class="nav-link"
            >
                <i class="bi bi-file-earmark-text"></i>

                <span>
                    My Guidance Requests
                </span>
            </a>


            <!-- ANNOUNCEMENTS -->

            <a
                href="announcements.php"
                class="nav-link active"
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
    ====================================== -->

    <div class="main">

        <div class="content-container">

            <!-- =====================================
                 PAGE HEADER
            ====================================== -->

            <div class="page-header">

                <div class="header-left">

                    <!-- BACK -->

                    <a
                        href="dashboard.php"
                        class="back-btn"
                        title="Go back"
                    >
                        <i class="bi bi-arrow-left"></i>
                    </a>


                    <!-- TITLE -->

                    <div class="page-title">

                        <div class="page-title-icon">
                            <i class="bi bi-megaphone-fill"></i>
                        </div>

                        <div>

                            <h2>
                                Announcements
                            </h2>

                            <p>
                                Stay updated with the latest guidance announcements.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- COUNT -->

                <div class="announcement-count">

                    <strong>
                        <?= $total_announcements; ?>
                    </strong>

                    announcement(s)

                </div>

            </div>


            <!-- =====================================
                 MAIN CARD
            ====================================== -->

            <div class="announcement-card">

                <!-- CARD HEADER -->

                <div class="card-top">

                    <h5>
                        <i class="bi bi-bell me-2"></i>
                        Latest Announcements
                    </h5>

                    <span>
                        PRMSU Guidance Counseling
                    </span>

                </div>


                <!-- =====================================
                     ANNOUNCEMENT LIST
                ====================================== -->

                <div class="announcement-list">

                    <?php

                    if ($total_announcements > 0) {

                        while ($row = mysqli_fetch_assoc($query)) {

                            $formatted_date = date(
                                "M d, Y - g:i A",
                                strtotime($row['created_at'])
                            );

                    ?>

                        <!-- ANNOUNCEMENT -->

                        <div class="announcement-item">

                            <div class="announcement-top">

                                <div class="announcement-heading">

                                    <div class="announcement-icon">

                                        <i class="bi bi-megaphone"></i>

                                    </div>

                                    <h5 class="announcement-title">

                                        <?= htmlspecialchars(
                                            $row['title']
                                        ); ?>

                                    </h5>

                                </div>


                                <div class="announcement-date">

                                    <i class="bi bi-calendar3"></i>

                                    <?= htmlspecialchars(
                                        $formatted_date
                                    ); ?>

                                </div>

                            </div>


                            <!-- CONTENT -->

                            <p class="announcement-text">

                                <?= nl2br(
                                    htmlspecialchars(
                                        $row['content']
                                    )
                                ); ?>

                            </p>

                        </div>

                    <?php

                        }

                    } else {

                    ?>

                        <!-- EMPTY STATE -->

                        <div class="empty-state">

                            <div class="empty-icon">

                                <i class="bi bi-megaphone"></i>

                            </div>

                            <h5>
                                No announcements yet
                            </h5>

                            <p>
                                There are currently no announcements available.
                            </p>

                        </div>

                    <?php

                    }

                    ?>

                </div>

            </div>

        </div>

    </div>


    <!-- BOOTSTRAP JS -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>

</body>

</html>