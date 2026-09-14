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
$message = "";

// =====================================
// GET STUDENT ID
// =====================================
$stmtStudent = $conn->prepare("
    SELECT student_id
    FROM students
    WHERE user_id = ?
    LIMIT 1
");

if (!$stmtStudent) {
    die("Database Error: " . htmlspecialchars($conn->error));
}

$stmtStudent->bind_param("i", $user_id);
$stmtStudent->execute();

$resultStudent = $stmtStudent->get_result();

if ($resultStudent->num_rows === 0) {
    $stmtStudent->close();
    die("Student record not found.");
}

$student = $resultStudent->fetch_assoc();
$student_id = (int) $student['student_id'];

$stmtStudent->close();

// =====================================
// FORM VALUES
// =====================================
$appointment_date = "";
$appointment_time = "";
$concern = "";

// =====================================
// SUBMIT APPOINTMENT
// =====================================
if (isset($_POST['submit'])) {

    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? '');
    $concern = trim($_POST['concern'] ?? '');

    $status = "Pending";

    // =====================================
    // VALIDATION
    // =====================================
    if (
        empty($appointment_date) ||
        empty($appointment_time) ||
        empty($concern)
    ) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-exclamation-circle-fill me-2'></i>
            Please complete all required fields.
        </div>";

    } else {

        // =====================================
        // CHECK DATE
        // =====================================
        $today = date('Y-m-d');

        if ($appointment_date < $today) {

            $message = "
            <div class='alert alert-danger'>
                <i class='bi bi-calendar-x-fill me-2'></i>
                Please select a valid appointment date.
            </div>";

        } else {

            // =====================================
            // INSERT APPOINTMENT
            // =====================================
            $stmt = $conn->prepare("
                INSERT INTO appointments
                (
                    student_id,
                    counselor_id,
                    appointment_date,
                    appointment_time,
                    concern,
                    status
                )
                VALUES
                (
                    ?,
                    NULL,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");

            if (!$stmt) {

                $message = "
                <div class='alert alert-danger'>
                    <i class='bi bi-exclamation-circle-fill me-2'></i>
                    Database Error: " . htmlspecialchars($conn->error) . "
                </div>";

            } else {

                $stmt->bind_param(
                    "issss",
                    $student_id,
                    $appointment_date,
                    $appointment_time,
                    $concern,
                    $status
                );

                if ($stmt->execute()) {

                    $message = "
                    <div class='alert alert-success'>
                        <i class='bi bi-check-circle-fill me-2'></i>
                        Appointment request submitted successfully.
                    </div>";

                    // Clear form
                    $appointment_date = "";
                    $appointment_time = "";
                    $concern = "";

                } else {

                    $message = "
                    <div class='alert alert-danger'>
                        <i class='bi bi-exclamation-circle-fill me-2'></i>
                        Database Error: " . htmlspecialchars($stmt->error) . "
                    </div>";
                }

                $stmt->close();
            }
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

    <title>Request Appointment | PRMSU Guidance</title>

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
            width: 100%;
            min-height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background: #f4f6f9;
            font-family: "Poppins", sans-serif;
            color: #172554;
            overflow-x: hidden;
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

            background: linear-gradient(
                180deg,
                #002147 0%,
                #073b78 100%
            );

            color: white;
            z-index: 1055;

            display: flex;
            flex-direction: column;

            overflow-y: auto;
            overflow-x: hidden;

            box-shadow:
                4px 0 18px
                rgba(0, 0, 0, .08);

            -webkit-overflow-scrolling: touch;
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
                rgba(255, 255, 255, .15);

            flex-shrink: 0;
        }

        .brand img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .brand-text {
            min-width: 0;
        }

        .brand h3 {
            margin: 0;
            color: white;
            font-size: 17px;
            font-weight: 700;
            white-space: nowrap;
        }

        .brand small {
            display: block;
            color: rgba(255, 255, 255, .65);
            font-size: 11px;
            margin-top: 3px;
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

            border: none;
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
            color: white;
        }


        /* =====================================
           MENU TITLE
        ===================================== */

        .menu-title {
            padding: 20px 20px 8px;

            color: rgba(255, 255, 255, .45);

            font-size: 11px;
            font-weight: 600;

            text-transform: uppercase;
            letter-spacing: .8px;
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

            padding: 12px 14px;
            margin-bottom: 5px;

            border-radius: 9px;

            color: rgba(255, 255, 255, .85);

            text-decoration: none;

            font-size: 14px;
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

            background: rgba(255, 255, 255, .10);

            transform: translateX(2px);
        }

        .nav-link.active {
            color: white;

            background: #0d6efd;

            box-shadow:
                0 4px 12px
                rgba(13, 110, 253, .25);
        }


        /* =====================================
           LOGOUT
        ===================================== */

        .logout-area {
            padding: 10px 12px 18px;

            border-top:
                1px solid
                rgba(255, 255, 255, .10);

            margin-top: auto;
            flex-shrink: 0;
        }

        .logout-link {
            display: flex;
            align-items: center;

            gap: 12px;

            width: 100%;

            padding: 12px 14px;

            border-radius: 9px;

            color: rgba(255, 255, 255, .85);

            text-decoration: none;

            font-size: 14px;
            font-weight: 500;

            transition:
                background .2s ease,
                color .2s ease;
        }

        .logout-link i {
            width: 22px;
            min-width: 22px;

            font-size: 17px;
            text-align: center;
        }

        .logout-link:hover {
            color: white;

            background:
                rgba(220, 38, 38, .20);
        }


        /* =====================================
           MOBILE TOP BAR
        ===================================== */

        .mobile-topbar {
            display: none;
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
           FORM CONTAINER
        ===================================== */

        .form-container {
            width: 100%;
            max-width: 900px;

            margin: 0 auto;
        }


        /* =====================================
           PAGE HEADER
        ===================================== */

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;

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

        /* HEADER FONT UPDATED */

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

            font-size: 14px;

            line-height: 1.5;
        }


        /* =====================================
           FORM CARD
        ===================================== */

        .form-card {
            background: white;

            border:
                1px solid
                #e5e7eb;

            border-radius: 18px;

            overflow: hidden;

            box-shadow:
                0 8px 25px
                rgba(15, 23, 42, .06);
        }


        /* =====================================
           FORM HEADER
        ===================================== */

        .form-header {
            padding: 21px 24px;

            background:
                linear-gradient(
                    135deg,
                    #002147,
                    #0d6efd
                );

            color: white;
        }

        /* HEADER FONT UPDATED */

        .form-header h4 {
            margin: 0;

            font-size: 20px;
            font-weight: 600;

            line-height: 1.35;
        }

        .form-header p {
            margin: 4px 0 0;

            color: rgba(255, 255, 255, .82);

            font-size: 14px;

            line-height: 1.5;
        }


        /* =====================================
           FORM BODY
        ===================================== */

        .form-body {
            padding: 30px;
        }


        /* =====================================
           LABEL
        ===================================== */

        .form-label {
            color: #172554;

            font-size: 14px;
            font-weight: 600;

            margin-bottom: 8px;
        }


        /* =====================================
           INPUTS
        ===================================== */

        .form-control,
        .form-select {
            width: 100%;

            min-height: 46px;

            border:
                1px solid
                #dbe2ea;

            border-radius: 10px;

            padding: 11px 14px;

            font-family:
                "Poppins",
                sans-serif;

            font-size: 14px;

            color: #334155;

            background: white;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;

            box-shadow:
                0 0 0 3px
                rgba(37, 99, 235, .10);
        }


        /* =====================================
           BUTTON AREA
        ===================================== */

        .button-area {
            display: flex;

            gap: 10px;

            margin-top: 25px;
        }


        /* =====================================
           BUTTONS
        ===================================== */

        .submit-btn,
        .back-btn {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 6px;

            border: none;

            border-radius: 10px;

            padding: 12px 20px;

            font-family:
                "Poppins",
                sans-serif;

            font-size: 14px;
            font-weight: 600;

            transition:
                background .2s ease,
                transform .2s ease;
        }

        .submit-btn {
            background: #2563eb;
            color: white;
        }

        .submit-btn:hover {
            background: #1d4ed8;

            color: white;

            transform: translateY(-1px);
        }

        .back-btn {
            background: #64748b;

            color: white;

            text-decoration: none;
        }

        .back-btn:hover {
            background: #475569;

            color: white;

            transform: translateY(-1px);
        }


        /* =====================================
           ALERT
        ===================================== */

        .alert {
            border: none;

            border-radius: 10px;

            font-size: 14px;

            line-height: 1.5;

            margin-bottom: 22px;
        }


        /* =====================================
           TABLET
        ===================================== */

        @media (max-width: 1199.98px) {

            .main {
                padding: 28px 24px;
            }

            .form-container {
                max-width: 850px;
            }
        }


        /* =====================================
           MOBILE / TABLET
        ===================================== */

        @media (max-width: 991.98px) {

            /* MOBILE TOPBAR */

            .mobile-topbar {
                position: fixed;

                top: 0;
                left: 0;
                right: 0;

                height: 64px;

                padding: 10px 15px;

                background:
                    linear-gradient(
                        135deg,
                        #002147,
                        #073b78
                    );

                color: white;

                display: flex;

                align-items: center;
                justify-content: space-between;

                z-index: 1045;

                box-shadow:
                    0 3px 12px
                    rgba(0, 0, 0, .15);
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

            .mobile-brand-text {
                min-width: 0;
            }

            .mobile-brand-text strong {
                display: block;

                color: white;

                font-size: 16px;
                font-weight: 600;

                white-space: nowrap;
            }

            .mobile-brand-text span {
                display: block;

                color:
                    rgba(255, 255, 255, .65);

                font-size: 11px;

                white-space: nowrap;
            }

            .menu-toggle {
                width: 42px;
                height: 42px;

                border:
                    1px solid
                    rgba(255, 255, 255, .35);

                border-radius: 9px;

                background:
                    rgba(255, 255, 255, .08);

                color: white;

                display: flex;

                align-items: center;
                justify-content: center;

                font-size: 21px;

                flex-shrink: 0;
            }

            .menu-toggle:hover {
                background:
                    rgba(255, 255, 255, .18);

                color: white;
            }


            /* SIDEBAR */

            .sidebar {
                width: 270px;
                max-width: 85vw;

                box-shadow:
                    6px 0 22px
                    rgba(0, 0, 0, .18);
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

            .brand h3 {
                font-size: 17px;
            }

            .brand small {
                font-size: 11px;
            }

            .menu-title {
                padding: 20px 18px 8px;

                font-size: 11px;
            }

            .nav-menu {
                padding: 5px 12px 20px;
            }

            .nav-link {
                padding: 12px 13px;

                font-size: 14px;
            }

            .nav-link i {
                width: 22px;
                min-width: 22px;

                font-size: 17px;
            }

            .logout-area {
                padding: 10px 12px 20px;
            }

            .logout-link {
                padding: 12px 13px;

                font-size: 14px;
            }


            /* MAIN */

            .main {
                margin-left: 0;

                width: 100%;

                min-height: 100vh;

                padding: 88px 22px 30px;
            }

            .form-container {
                max-width: 100%;
            }


            /* HEADER */

            .page-header {
                margin-bottom: 20px;
            }

            .page-title {
                gap: 12px;
            }

            .title-icon {
                width: 52px;
                height: 52px;

                border-radius: 14px;

                font-size: 24px;
            }

            /* HEADER FONT */

            .page-title h2 {
                font-size: 25px;
            }

            .page-title p {
                font-size: 14px;
            }


            /* FORM */

            .form-header {
                padding: 20px 22px;
            }

            /* HEADER FONT */

            .form-header h4 {
                font-size: 19px;
            }

            .form-header p {
                font-size: 14px;
            }

            .form-body {
                padding: 24px 22px;
            }
        }


        /* =====================================
           PHONE
        ===================================== */

        @media (max-width: 767.98px) {

            .mobile-topbar {
                height: 62px;

                padding: 9px 14px;
            }

            .mobile-brand img {
                width: 36px;
                height: 36px;
            }

            .mobile-brand-text strong {
                font-size: 15px;
            }

            .mobile-brand-text span {
                font-size: 10px;
            }

            .menu-toggle {
                width: 40px;
                height: 40px;

                font-size: 20px;
            }

            .main {
                padding: 82px 16px 26px;
            }


            /* PAGE HEADER */

            .page-header {
                margin-bottom: 18px;
            }

            .page-title {
                gap: 10px;

                align-items: flex-start;
            }

            .title-icon {
                width: 47px;
                height: 47px;

                border-radius: 12px;

                font-size: 21px;
            }

            /* HEADER FONT */

            .page-title h2 {
                font-size: 23px;

                line-height: 1.3;
            }

            .page-title p {
                font-size: 14px;

                line-height: 1.55;
            }


            /* CARD */

            .form-card {
                border-radius: 14px;
            }

            .form-header {
                padding: 18px 19px;
            }

            /* HEADER FONT */

            .form-header h4 {
                font-size: 18px;

                line-height: 1.4;
            }

            .form-header p {
                font-size: 14px;

                line-height: 1.55;
            }

            .form-body {
                padding: 22px 18px;
            }
        }


        /* =====================================
           SMALL PHONE
        ===================================== */

        @media (max-width: 480px) {

            .mobile-topbar {
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

            .mobile-brand-text strong {
                font-size: 14px;
            }

            .mobile-brand-text span {
                font-size: 9.5px;
            }

            .menu-toggle {
                width: 39px;
                height: 39px;

                font-size: 19px;
            }


            /* SIDEBAR */

            .sidebar {
                width: 265px;
            }

            .brand {
                min-height: 78px;
            }

            .brand h3 {
                font-size: 16px;
            }

            .brand small {
                font-size: 10.5px;
            }

            .nav-link {
                font-size: 14px;

                padding: 12px 13px;
            }


            /* MAIN */

            .main {
                padding: 78px 12px 23px;
            }


            /* HEADER */

            .page-title {
                gap: 9px;
            }

            .title-icon {
                width: 43px;
                height: 43px;

                font-size: 19px;
            }

            /* HEADER FONT */

            .page-title h2 {
                font-size: 22px;
            }

            .page-title p {
                font-size: 13.5px;
            }


            /* FORM */

            .form-header {
                padding: 17px 16px;
            }

            /* HEADER FONT */

            .form-header h4 {
                font-size: 18px;
            }

            .form-header p {
                font-size: 13.5px;
            }

            .form-body {
                padding: 20px 15px;
            }
        }


        /* =====================================
           VERY SMALL PHONE
        ===================================== */

        @media (max-width: 359.98px) {

            .mobile-brand-text strong {
                font-size: 13.5px;
            }

            .mobile-brand-text span {
                font-size: 9px;
            }

            .main {
                padding-left: 10px;
                padding-right: 10px;
            }


            /* HEADER FONT */

            .page-title h2 {
                font-size: 20px;
            }

            .page-title p {
                font-size: 13px;
            }

            .form-header {
                padding: 16px 14px;
            }

            /* HEADER FONT */

            .form-header h4 {
                font-size: 17px;
            }

            .form-header p {
                font-size: 13px;
            }

            .form-body {
                padding: 18px 13px;
            }
        }

    </style>

</head>


<body>


    <!-- =====================================
         MOBILE TOP BAR
    ===================================== -->

    <div class="mobile-topbar">

        <div class="mobile-brand">

            <img
                src="../assets/images/prmsu-logo.png"
                alt="PRMSU Logo"
            >

            <div class="mobile-brand-text">

                <strong>
                    PRMSU Guidance
                </strong>

                <span>
                    Counseling System
                </span>

            </div>

        </div>


        <button
            type="button"
            class="menu-toggle"
            id="menuToggle"
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

                <h3>
                    PRMSU Guidance
                </h3>

                <small>
                    Counseling System
                </small>

            </div>

        </div>


        <!-- MAIN MENU -->

        <div class="menu-title">
            Main Menu
        </div>


        <div class="nav-menu">

            <a
                href="dashboard.php"
                class="nav-link"
            >
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>


            <a
                href="request_appointment.php"
                class="nav-link active"
            >
                <i class="bi bi-calendar-plus"></i>
                <span>Request Appointment</span>
            </a>


            <a
                href="my_appointments.php"
                class="nav-link"
            >
                <i class="bi bi-calendar-check"></i>
                <span>My Appointments</span>
            </a>


            <a
                href="guidance_request.php"
                class="nav-link"
            >
                <i class="bi bi-chat-left-text"></i>
                <span>Guidance Request</span>
            </a>


            <a
                href="my_requests.php"
                class="nav-link"
            >
                <i class="bi bi-file-earmark-text"></i>
                <span>My Guidance Requests</span>
            </a>


            <a
                href="announcements.php"
                class="nav-link"
            >
                <i class="bi bi-megaphone-fill"></i>
                <span>Announcements</span>
            </a>

        </div>


        <!-- LOGOUT -->

        <div class="logout-area">

            <a
                href="../logout.php"
                class="logout-link"
            >
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>

        </div>

    </div>


    <!-- =====================================
         MAIN CONTENT
    ===================================== -->

    <div class="main">

        <div class="form-container">


            <!-- PAGE HEADER -->

            <div class="page-header">

                <div class="page-title">

                    <div class="title-icon">
                        <i class="bi bi-calendar-plus"></i>
                    </div>

                    <div>

                        <h2>
                            Request Appointment
                        </h2>

                        <p>
                            Submit a counseling appointment request to the Guidance Office.
                        </p>

                    </div>

                </div>

            </div>


            <!-- FORM CARD -->

            <div class="form-card">


                <!-- FORM HEADER -->

                <div class="form-header">

                    <h4>
                        <i class="bi bi-calendar-check me-2"></i>
                        Counseling Appointment
                    </h4>

                    <p>
                        Please provide your preferred schedule and concern.
                    </p>

                </div>


                <!-- FORM BODY -->

                <div class="form-body">


                    <!-- MESSAGE -->

                    <?= $message ?>


                    <!-- FORM -->

                    <form method="POST">


                        <!-- APPOINTMENT DATE -->

                        <div class="mb-4">

                            <label
                                class="form-label"
                                for="appointment_date"
                            >
                                Appointment Date
                            </label>

                            <input
                                type="date"
                                name="appointment_date"
                                id="appointment_date"
                                class="form-control"
                                min="<?= date('Y-m-d'); ?>"
                                value="<?= htmlspecialchars($appointment_date, ENT_QUOTES, 'UTF-8'); ?>"
                                required
                            >

                        </div>


                        <!-- APPOINTMENT TIME -->

                        <div class="mb-4">

                            <label
                                class="form-label"
                                for="appointment_time"
                            >
                                Appointment Time
                            </label>

                            <input
                                type="time"
                                name="appointment_time"
                                id="appointment_time"
                                class="form-control"
                                value="<?= htmlspecialchars($appointment_time, ENT_QUOTES, 'UTF-8'); ?>"
                                required
                            >

                        </div>


                        <!-- CONCERN -->

                        <div class="mb-4">

                            <label
                                class="form-label"
                                for="concern"
                            >
                                Concern
                            </label>

                            <select
                                name="concern"
                                id="concern"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Concern
                                </option>

                                <option
                                    value="Academic Concern"
                                    <?= ($concern === 'Academic Concern') ? 'selected' : ''; ?>
                                >
                                    Academic Concern
                                </option>

                                <option
                                    value="Personal Concern"
                                    <?= ($concern === 'Personal Concern') ? 'selected' : ''; ?>
                                >
                                    Personal Concern
                                </option>

                                <option
                                    value="Family Concern"
                                    <?= ($concern === 'Family Concern') ? 'selected' : ''; ?>
                                >
                                    Family Concern
                                </option>

                                <option
                                    value="Emotional Concern"
                                    <?= ($concern === 'Emotional Concern') ? 'selected' : ''; ?>
                                >
                                    Emotional Concern
                                </option>

                                <option
                                    value="Behavioral Concern"
                                    <?= ($concern === 'Behavioral Concern') ? 'selected' : ''; ?>
                                >
                                    Behavioral Concern
                                </option>

                                <option
                                    value="Career Guidance"
                                    <?= ($concern === 'Career Guidance') ? 'selected' : ''; ?>
                                >
                                    Career Guidance
                                </option>

                                <option
                                    value="Other"
                                    <?= ($concern === 'Other') ? 'selected' : ''; ?>
                                >
                                    Other
                                </option>

                            </select>

                        </div>


                        <!-- BUTTONS -->

                        <div class="button-area">

                            <button
                                type="submit"
                                name="submit"
                                class="submit-btn"
                            >
                                <i class="bi bi-send-fill"></i>
                                Submit Request
                            </button>


                            <a
                                href="dashboard.php"
                                class="back-btn"
                            >
                                <i class="bi bi-arrow-left"></i>
                                Back
                            </a>

                        </div>

                    </form>

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


    <!-- =====================================
         MOBILE MENU
    ===================================== -->

    <script>

        document
            .querySelectorAll(
                "#sidebarMenu .nav-link, #sidebarMenu .logout-link"
            )
            .forEach(function(link) {

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