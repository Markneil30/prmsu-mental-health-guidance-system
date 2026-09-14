
<?php
session_start();
include("../includes/db.php");

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] != 'counselor'
) {
    header("Location: login.php");
    exit();
}

/* ==========================================
   GET LOGGED-IN COUNSELOR ID
========================================== */

$user_id = (int) $_SESSION['user_id'];

$counselor_stmt = mysqli_prepare(
    $conn,
    "SELECT counselor_id
     FROM counselors
     WHERE user_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($counselor_stmt, "i", $user_id);
mysqli_stmt_execute($counselor_stmt);

$counselor_result = mysqli_stmt_get_result($counselor_stmt);
$counselor_data = mysqli_fetch_assoc($counselor_result);

mysqli_stmt_close($counselor_stmt);

if (!$counselor_data) {
    die("Counselor account is not properly linked to the counselors table.");
}

$counselor_id = (int) $counselor_data['counselor_id'];


/* ==========================================
   DASHBOARD COUNTS
========================================== */

/* Pending Appointments */
$pending_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM appointments
     WHERE status = 'Pending'"
);

mysqli_stmt_execute($pending_stmt);
$pending_result = mysqli_stmt_get_result($pending_stmt);
$pending = (int) mysqli_fetch_assoc($pending_result)['total'];
mysqli_stmt_close($pending_stmt);


/* Approved Appointments */
$approved_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM appointments
     WHERE status = 'Approved'"
);

mysqli_stmt_execute($approved_stmt);
$approved_result = mysqli_stmt_get_result($approved_stmt);
$approved = (int) mysqli_fetch_assoc($approved_result)['total'];
mysqli_stmt_close($approved_stmt);


/* Rejected Appointments */
$rejected_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM appointments
     WHERE status = 'Rejected'
     AND counselor_id = ?"
);

mysqli_stmt_bind_param($rejected_stmt, "i", $counselor_id);
mysqli_stmt_execute($rejected_stmt);

$rejected_result = mysqli_stmt_get_result($rejected_stmt);
$rejected = (int) mysqli_fetch_assoc($rejected_result)['total'];

mysqli_stmt_close($rejected_stmt);


/* Counseling Records */
$records_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM counseling_records
     WHERE counselor_id = ?"
);

mysqli_stmt_bind_param($records_stmt, "i", $counselor_id);
mysqli_stmt_execute($records_stmt);

$records_result = mysqli_stmt_get_result($records_stmt);
$records = (int) mysqli_fetch_assoc($records_result)['total'];

mysqli_stmt_close($records_stmt);


/* Pending Guidance Requests */
$guidance_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM guidance_requests
     WHERE status = 'Pending'"
);

mysqli_stmt_execute($guidance_stmt);

$guidance_result = mysqli_stmt_get_result($guidance_stmt);
$guidance = (int) mysqli_fetch_assoc($guidance_result)['total'];

mysqli_stmt_close($guidance_stmt);


/* Approved Guidance Requests */
$approved_guidance_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM guidance_requests
     WHERE status = 'Approved'"
);

mysqli_stmt_execute($approved_guidance_stmt);

$approved_guidance_result = mysqli_stmt_get_result(
    $approved_guidance_stmt
);

$approved_guidance = (int) mysqli_fetch_assoc(
    $approved_guidance_result
)['total'];

mysqli_stmt_close($approved_guidance_stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Counselor Dashboard | PRMSU Guidance</title>

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
    overflow-x: hidden;
}

body {
    background:
        linear-gradient(
            rgba(244, 247, 251, 0.92),
            rgba(244, 247, 251, 0.92)
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
        rgba(0,0,0,0.08);

    overflow-y: auto;
}

.sidebar::-webkit-scrollbar {
    width: 4px;
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,.25);
    border-radius: 10px;
}


/* =====================================
   SIDEBAR CLOSE
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

    font-size: 17px;

    z-index: 10;
}

.sidebar-close:hover {
    background:
        rgba(255,255,255,.18);
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
        rgba(255,255,255,0.14);
}

.brand img {
    width: 48px;
    height: 48px;

    object-fit: contain;

    flex-shrink: 0;
}

.brand-text {
    line-height: 1.2;
    min-width: 0;
}

.brand-title {
    font-size: 16px;
    font-weight: 700;

    margin: 0;

    color: #ffffff;
}

.brand-subtitle {
    font-size: 10px;

    opacity: 0.75;

    margin-top: 2px;

    text-transform: uppercase;

    letter-spacing: 0.5px;
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

    opacity: 0.55;

    padding: 0 12px 8px;
}

.sidebar a {
    display: flex;

    align-items: center;

    gap: 12px;

    color:
        rgba(255,255,255,0.85);

    text-decoration: none;

    padding: 12px 14px;

    margin-bottom: 4px;

    border-radius: 8px;

    font-size: 13.5px;

    font-weight: 500;

    transition:
        background .2s ease,
        color .2s ease,
        transform .2s ease;
}

.sidebar a i {
    width: 20px;
    min-width: 20px;

    text-align: center;

    font-size: 17px;
}

.sidebar a:hover {
    background:
        rgba(255,255,255,0.12);

    color: #ffffff;

    transform:
        translateX(3px);
}

.sidebar a.active {
    background: #0d6efd;

    color: #ffffff;

    box-shadow:
        0 4px 12px
        rgba(13,110,253,0.3);
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
        rgba(220,53,69,0.15);
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

    width: 100%;

    min-height: 60px;

    background: #002147;

    color: white;

    padding: 10px 16px;

    align-items: center;

    justify-content: space-between;

    position: sticky;

    top: 0;

    z-index: 1040;

    box-shadow:
        0 2px 10px
        rgba(0,0,0,0.1);
}

.brand-mini {
    display: flex;

    align-items: center;

    gap: 10px;

    min-width: 0;
}

.brand-mini img {
    width: 38px;
    height: 38px;

    object-fit: contain;

    flex-shrink: 0;
}

.brand-mini span {
    font-size: 15px;

    font-weight: 600;

    white-space: nowrap;
}

.mobile-menu-btn {
    width: 42px;
    height: 40px;

    flex-shrink: 0;

    margin-left: auto;

    display: flex;

    align-items: center;
    justify-content: center;

    padding: 0;

    border-radius: 7px;
}

.mobile-menu-btn i {
    font-size: 23px;
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
}

.welcome p {
    margin: 4px 0 0;

    color: #718096;

    font-size: 13px;
}

.user-box {
    display: flex;

    align-items: center;

    gap: 10px;

    background:
        rgba(255, 255, 255, 0.9);

    backdrop-filter: blur(10px);

    padding: 8px 14px;

    border-radius: 10px;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,0.04);

    border:
        1px solid
        rgba(255,255,255,0.6);

    flex-shrink: 0;
}

.user-icon {
    width: 36px;
    height: 36px;

    border-radius: 50%;

    background: #e8f1ff;

    color: #0d6efd;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 18px;
}

.user-name {
    font-size: 13px;
    font-weight: 600;

    white-space: nowrap;
}


/* =====================================
   WELCOME PANEL
===================================== */

.welcome-panel {
    background:
        linear-gradient(
            135deg,
            #002147 0%,
            #0d6efd 100%
        );

    border-radius: 14px;

    padding: 24px 28px;

    color: white;

    margin-bottom: 28px;

    position: relative;

    overflow: hidden;

    box-shadow:
        0 8px 22px
        rgba(13,110,253,0.18);
}

.welcome-panel::after {
    content: "";

    position: absolute;

    width: 200px;
    height: 200px;

    border-radius: 50%;

    background:
        rgba(255,255,255,0.06);

    right: -40px;
    top: -60px;
}

.welcome-panel h3 {
    font-size: 20px;

    margin: 0 0 6px;

    font-weight: 600;

    position: relative;
    z-index: 1;
}

.welcome-panel p {
    margin: 0;

    font-size: 13px;

    opacity: 0.88;

    max-width: 650px;

    line-height: 1.5;

    position: relative;
    z-index: 1;
}


/* =====================================
   STAT CARDS
===================================== */

.card-box {
    background:
        rgba(255, 255, 255, 0.9);

    backdrop-filter: blur(10px);

    border-radius: 12px;

    padding: 20px;

    border:
        1px solid #e9eef5;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.03);

    height: 100%;

    display: flex;

    flex-direction: column;

    justify-content: space-between;

    min-width: 0;

    transition:
        transform .25s ease,
        box-shadow .25s ease;
}

.card-box:hover {
    transform:
        translateY(-4px);

    box-shadow:
        0 10px 25px
        rgba(0,0,0,0.07);
}

.card-top {
    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 10px;

    margin-bottom: 12px;
}

.icon-wrapper {
    width: 46px;
    height: 46px;

    min-width: 46px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 20px;
}


/* =====================================
   CARD COLORS
===================================== */

.bg1 .icon-wrapper {
    background: #fff8e6;
    color: #ffc107;
}

.bg2 .icon-wrapper {
    background: #e7f7ef;
    color: #198754;
}

.bg3 .icon-wrapper {
    background: #fff0f0;
    color: #dc3545;
}

.bg4 .icon-wrapper {
    background: #e8f1ff;
    color: #0d6efd;
}

.bg5 .icon-wrapper {
    background: #f3ebff;
    color: #6f42c1;
}

.bg6 .icon-wrapper {
    background: #e8f7f0;
    color: #198754;
}

.bg1 {
    border-top: 4px solid #ffc107;
}

.bg2 {
    border-top: 4px solid #198754;
}

.bg3 {
    border-top: 4px solid #dc3545;
}

.bg4 {
    border-top: 4px solid #0d6efd;
}

.bg5 {
    border-top: 4px solid #6f42c1;
}

.bg6 {
    border-top: 4px solid #198754;
}

.card-box h2 {
    font-size: 32px;

    font-weight: 700;

    margin: 0;

    color: #172033;
}

.card-box p {
    font-size: 13px;

    font-weight: 500;

    color: #6b7280;

    margin: 4px 0 0;

    line-height: 1.4;
}


/* =====================================
   INFO BOX
===================================== */

.info-box {
    background:
        rgba(255, 255, 255, 0.9);

    backdrop-filter: blur(10px);

    border-radius: 12px;

    padding: 22px;

    border:
        1px solid #e9eef5;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.03);

    height: 100%;
}

.info-box h4 {
    font-size: 16px;

    font-weight: 600;

    color: #172033;

    margin-bottom: 12px;

    display: flex;

    align-items: center;

    gap: 8px;
}

.info-box h4 i {
    color: #0d6efd;
}


/* =====================================
   TABLET
===================================== */

@media (max-width: 1199px) {

    .main {
        padding: 26px 24px;
    }

    .welcome h2 {
        font-size: 23px;
    }

    .card-box {
        padding: 19px;
    }

    .card-box h2 {
        font-size: 30px;
    }

    .card-box p {
        font-size: 13px;
    }
}


/* =====================================
   TABLET / MOBILE
===================================== */

@media (max-width: 991px) {

    body {
        background-attachment: scroll;
    }


    /* MOBILE NAVBAR */

    .mobile-navbar {
        display: flex;
    }


    /* SIDEBAR */

    .sidebar {
        width: 270px;
        max-width: 86vw;

        transform:
            translateX(-100%);

        transition:
            transform .3s ease;

        box-shadow:
            6px 0 20px
            rgba(0,0,0,.15);
    }

    .sidebar.show {
        transform:
            translateX(0);
    }


    /* CLOSE */

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

        padding: 24px 20px 32px;
    }


    /* TOPBAR */

    .topbar {
        margin-bottom: 22px;
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


    /* WELCOME */

    .welcome-panel {
        padding: 23px 25px;

        margin-bottom: 24px;
    }

    .welcome-panel h3 {
        font-size: 19px;
    }

    .welcome-panel p {
        font-size: 13px;
    }
}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 767px) {

    .mobile-navbar {
        min-height: 60px;

        padding:
            10px 14px;
    }

    .brand-mini img {
        width: 38px;
        height: 38px;
    }

    .brand-mini span {
        font-size: 15px;
    }

    .mobile-menu-btn {
        width: 42px;
        height: 40px;
    }

    .mobile-menu-btn i {
        font-size: 23px;
    }


    /* MAIN */

    .main {
        padding:
            22px 16px 30px;
    }


    /* TOPBAR */

    .topbar {
        display: block;

        margin-bottom: 20px;
    }

    .welcome h2 {
        font-size: 21px;

        line-height: 1.4;
    }

    .welcome p {
        font-size: 13px;

        line-height: 1.5;
    }


    /* WELCOME PANEL */

    .welcome-panel {
        padding:
            21px 20px;

        border-radius: 12px;

        margin-bottom: 22px;
    }

    .welcome-panel h3 {
        font-size: 18px;
    }

    .welcome-panel p {
        font-size: 13px;

        line-height: 1.55;
    }


    /* CARDS */

    .row.g-3 {
        --bs-gutter-x: 1rem;
        --bs-gutter-y: 1rem;
    }

    .card-box {
        padding:
            18px;

        border-radius: 11px;
    }

    .card-top {
        gap: 10px;

        margin-bottom: 11px;
    }

    .card-box p {
        font-size: 13px;

        line-height: 1.45;
    }

    .card-box h2 {
        font-size: 29px;
    }

    .icon-wrapper {
        width: 43px;
        height: 43px;

        min-width: 43px;

        font-size: 19px;
    }


    /* INFO BOX */

    .row.g-4 {
        --bs-gutter-x: 1rem;
        --bs-gutter-y: 1rem;
    }

    .info-box {
        padding: 20px;
    }

    .info-box h4 {
        font-size: 15px;
    }

    .info-box p {
        font-size: 13px;
    }
}


/* =====================================
   SMALL PHONE
===================================== */

@media (max-width: 575px) {

    .mobile-navbar {
        padding:
            9px 13px;
    }

    .brand-mini img {
        width: 36px;
        height: 36px;
    }

    .brand-mini span {
        font-size: 14px;
    }

    .mobile-menu-btn {
        width: 41px;
        height: 39px;
    }


    .main {
        padding:
            20px 13px 28px;
    }


    .welcome h2 {
        font-size: 20px;
    }

    .welcome p {
        font-size: 12.5px;
    }


    .welcome-panel {
        padding:
            20px 18px;
    }

    .welcome-panel h3 {
        font-size: 17px;
    }

    .welcome-panel p {
        font-size: 12.5px;
    }


    /* One card per row */

    .card-box {
        padding:
            18px;
    }

    .card-box p {
        font-size: 13px;
    }

    .card-box h2 {
        font-size: 28px;
    }

    .icon-wrapper {
        width: 42px;
        height: 42px;

        min-width: 42px;
    }

    .info-box {
        padding: 19px;
    }

    .info-box h4 {
        font-size: 14.5px;
    }

    .info-box p {
        font-size: 13px;
    }
}


/* =====================================
   VERY SMALL PHONE
===================================== */

@media (max-width: 380px) {

    .brand-mini span {
        font-size: 13.5px;
    }

    .main {
        padding:
            18px 11px 25px;
    }

    .welcome h2 {
        font-size: 19px;
    }

    .welcome p {
        font-size: 12px;
    }

    .welcome-panel {
        padding:
            18px 16px;
    }

    .welcome-panel h3 {
        font-size: 16.5px;
    }

    .welcome-panel p {
        font-size: 12px;
    }

    .card-box {
        padding:
            17px;
    }

    .card-box p {
        font-size: 13px;
    }

    .card-box h2 {
        font-size: 27px;
    }

    .info-box h4 {
        font-size: 14px;
    }

    .info-box p {
        font-size: 12.5px;
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
    class="offcanvas-lg offcanvas-start sidebar"
    tabindex="-1"
    id="sidebarMenu"
>


    <button
        type="button"
        class="sidebar-close"
        data-bs-dismiss="offcanvas"
        aria-label="Close menu"
    >

        <i class="bi bi-x-lg"></i>

    </button>


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


    <div class="nav-menu">

        <div class="nav-label">
            Main Menu
        </div>


        <a
            href="dashboard.php"
            class="active"
        >

            <i class="bi bi-speedometer2"></i>

            Dashboard

        </a>


        <a
            href="appointment_requests.php"
        >

            <i class="bi bi-calendar-check"></i>

            Appointment Requests

        </a>


        <a
            href="guidance_requests.php"
        >

            <i class="bi bi-chat-left-text-fill"></i>

            Guidance Requests

        </a>


        <a
            href="records.php"
        >

            <i class="bi bi-folder2-open"></i>

            Counseling Records

        </a>


        <a
            href="announcements.php"
        >

            <i class="bi bi-megaphone-fill"></i>

            Announcements

        </a>

    </div>


    <div class="logout">

        <a
            href="../logout.php"
        >

            <i class="bi bi-box-arrow-right"></i>

            Logout

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

                Welcome back,

                <?= htmlspecialchars($_SESSION['fullname']); ?>

                👋

            </h2>

            <p>
                Guidance Counselor Management Dashboard
            </p>

        </div>


        <div class="user-box">

            <div class="user-icon">

                <i class="bi bi-person-fill"></i>

            </div>

            <div class="user-name">

                <?= htmlspecialchars($_SESSION['fullname']); ?>

            </div>

        </div>

    </div>


    <!-- WELCOME PANEL -->

    <div class="welcome-panel">

        <h3>
            Overview & Statistics
        </h3>

        <p>
            Monitor pending appointment requests, review counseling records,
            and manage student guidance inquiries efficiently.
        </p>

    </div>


    <!-- =====================================
         STAT CARDS
    ===================================== -->

    <div class="row g-3 mb-4">


        <div class="col-xl-2 col-md-4 col-sm-6">

            <div class="card-box bg1">

                <div class="card-top">

                    <p>
                        Pending Appointments
                    </p>

                    <div class="icon-wrapper">
                        <i class="bi bi-calendar-check"></i>
                    </div>

                </div>

                <h2>
                    <?= $pending; ?>
                </h2>

            </div>

        </div>


        <div class="col-xl-2 col-md-4 col-sm-6">

            <div class="card-box bg2">

                <div class="card-top">

                    <p>
                        Approved Appointment Requests
                    </p>

                    <div class="icon-wrapper">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                </div>

                <h2>
                    <?= $approved; ?>
                </h2>

            </div>

        </div>


        <div class="col-xl-2 col-md-4 col-sm-6">

            <div class="card-box bg3">

                <div class="card-top">

                    <p>
                        Rejected
                    </p>

                    <div class="icon-wrapper">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>

                </div>

                <h2>
                    <?= $rejected; ?>
                </h2>

            </div>

        </div>


        <div class="col-xl-3 col-md-6 col-sm-6">

            <div class="card-box bg4">

                <div class="card-top">

                    <p>
                        Counseling Records
                    </p>

                    <div class="icon-wrapper">
                        <i class="bi bi-folder2-open"></i>
                    </div>

                </div>

                <h2>
                    <?= $records; ?>
                </h2>

            </div>

        </div>


        <div class="col-xl-3 col-md-6 col-sm-12">

            <div class="card-box bg5">

                <div class="card-top">

                    <p>
                        Pending Guidance Requests
                    </p>

                    <div class="icon-wrapper">
                        <i class="bi bi-chat-left-text-fill"></i>
                    </div>

                </div>

                <h2>
                    <?= $guidance; ?>
                </h2>

            </div>

        </div>


        <div class="col-xl-3 col-md-6 col-sm-12">

            <div class="card-box bg6">

                <div class="card-top">

                    <p>
                        Approved Guidance Requests
                    </p>

                    <div class="icon-wrapper">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                </div>

                <h2>
                    <?= $approved_guidance; ?>
                </h2>

            </div>

        </div>

    </div>


    <!-- =====================================
         ACTIVITY OVERVIEW
    ===================================== -->

    <div class="row g-4">


        <div class="col-lg-6">

            <div class="info-box">

                <h4>

                    <i class="bi bi-calendar-event"></i>

                    Recent Pending Appointments

                </h4>

                <p class="text-muted mb-0">

                    Appointment requests will be displayed here.

                </p>

            </div>

        </div>


        <div class="col-lg-6">

            <div class="info-box">

                <h4>

                    <i class="bi bi-chat-dots"></i>

                    Recent Guidance Requests

                </h4>

                <p class="text-muted mb-0">

                    Guidance requests will be displayed here.

                </p>

            </div>

        </div>

    </div>


</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>
</html>
