<?php
session_start();

include("../includes/db.php");

// =====================================
// CHECK ADMIN SESSION
// =====================================

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: login.php");
    exit();
}

// =====================================
// DASHBOARD COUNTS
// =====================================

$students = 0;
$students_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'student'"
);

if ($students_query) {
    $students_data = mysqli_fetch_assoc($students_query);
    $students = (int) ($students_data['total'] ?? 0);
}

$counselors = 0;
$counselors_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'counselor'"
);

if ($counselors_query) {
    $counselors_data = mysqli_fetch_assoc($counselors_query);
    $counselors = (int) ($counselors_data['total'] ?? 0);
}

$announcements = 0;
$announcements_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM announcements"
);

if ($announcements_query) {
    $announcements_data = mysqli_fetch_assoc($announcements_query);
    $announcements = (int) ($announcements_data['total'] ?? 0);
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

<title>Admin Dashboard | PRMSU Guidance</title>

<link
    rel="icon"
    href="../assets/images/prmsu-logo.png"
>

<link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    rel="stylesheet"
>

<style>

:root {
    --primary-navy: #002147;
    --secondary-navy: #073b78;
    --accent-blue: #0d6efd;
    --bg-light: #f4f7fb;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
}

html,
body {
    width: 100%;
    min-height: 100%;
}

body {
    min-height: 100vh;
    background-color: var(--bg-light);
    background-image:
        linear-gradient(
            rgba(244, 247, 251, 0.92),
            rgba(244, 247, 251, 0.92)
        ),
        url("../assets/images/dashboard-bg.jpg");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    color: #2c3e50;
    overflow-x: hidden;
}

/* =====================================
   MOBILE NAVBAR
===================================== */

.mobile-navbar {
    display: none;
    width: 100%;
    min-height: 62px;
    background: var(--primary-navy);
    color: white;
    padding: 10px 14px;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1040;
    box-shadow: 0 2px 10px rgba(0,0,0,.12);
    align-items: center;
    justify-content: flex-start;
}

.brand-mini {
    display: flex;
    align-items: center;
    gap: 9px;
    flex: 1;
    min-width: 0;
}

.brand-mini img {
    width: 36px;
    height: 36px;
    object-fit: contain;
    flex-shrink: 0;
}

.brand-mini span {
    font-size: 15px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* =====================================
   MOBILE MENU BUTTON
===================================== */

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
    font-size: 22px;
    line-height: 1;
}

/* =====================================
   SIDEBAR
===================================== */

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 240px;
    height: 100vh;
    background:
        linear-gradient(
            180deg,
            var(--primary-navy) 0%,
            var(--secondary-navy) 100%
        );
    color: white;
    z-index: 1050;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
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
    color: white;
}

/* =====================================
   BRAND
===================================== */

.brand-header {
    height: 82px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    flex-shrink: 0;
}

.brand-header img {
    width: 44px;
    height: 44px;
    object-fit: contain;
    flex-shrink: 0;
}

.brand-text {
    min-width: 0;
    line-height: 1.2;
}

.brand-text h4 {
    color: white;
    font-size: 15px;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
    white-space: nowrap;
}

.brand-text span {
    color: rgba(255, 255, 255, 0.6);
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
}

/* =====================================
   NAVIGATION
===================================== */

.nav-menu {
    padding: 18px 10px;
    flex: 1;
}

.nav-link-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    padding: 11px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 4px;
    transition:
        background 0.25s ease,
        color 0.25s ease,
        transform 0.2s ease;
}

.nav-link-item i {
    width: 20px;
    font-size: 17px;
    text-align: center;
    flex-shrink: 0;
}

.nav-link-item:hover {
    color: white;
    background: rgba(255, 255, 255, 0.12);
    transform: translateX(2px);
}

.nav-link-item.active {
    color: white;
    background: var(--accent-blue);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

/* =====================================
   LOGOUT
===================================== */

.nav-link-item.logout-link {
    color: #ff8e8e;
    background: rgba(220, 53, 69, 0.1);
    margin-top: 18px;
}

.nav-link-item.logout-link:hover {
    color: white;
    background: rgba(220, 53, 69, 0.25);
}

/* =====================================
   MAIN CONTENT
===================================== */

.main {
    margin-left: 240px;
    min-height: 100vh;
    padding: 30px 32px;
}

/* =====================================
   TOP HEADER
===================================== */

.top-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    padding: 18px 25px;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
}

.welcome-text {
    min-width: 0;
}

.welcome-text h3 {
    margin: 0;
    color: var(--primary-navy);
    font-size: 22px;
    font-weight: 700;
    line-height: 1.3;
}

.welcome-text p {
    margin: 3px 0 0;
    color: #6c757d;
    font-size: 13px;
    line-height: 1.5;
}

.header-icon {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-icon i {
    font-size: 35px;
    color: var(--primary-navy);
}

/* =====================================
   STATISTICS
===================================== */

.stat-card {
    height: 100%;
    padding: 24px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.9);
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08);
}

.card-icon {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    border-radius: 14px;
    font-size: 24px;
}

.stat-card h2 {
    margin-bottom: 4px;
    color: #1e293b;
    font-size: 36px;
    font-weight: 700;
    line-height: 1.2;
}

.stat-card p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
    font-weight: 600;
}

.card-students .card-icon {
    background: #e8f2ff;
    color: #0d6efd;
}

.card-counselors .card-icon {
    background: #e8fadf;
    color: #198754;
}

.card-announcements .card-icon {
    background: #ffeef0;
    color: #dc3545;
}

/* =====================================
   TABLET
===================================== */

@media (max-width: 991.98px) {

    .mobile-navbar {
        display: flex;
    }

    .sidebar {
        width: 250px;
        max-width: 82vw;
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
        padding: 82px 20px 25px;
    }

    .top-header {
        margin-bottom: 22px;
        padding: 17px 20px;
    }

    .welcome-text h3 {
        font-size: 21px;
    }

    .welcome-text p {
        font-size: 12px;
    }

    .header-icon i {
        font-size: 32px;
    }

    .stat-card {
        padding: 21px;
    }

    .stat-card h2 {
        font-size: 33px;
    }

    .stat-card p {
        font-size: 13px;
    }
}

/* =====================================
   SMALL TABLET
===================================== */

@media (max-width: 768px) {

    .mobile-navbar {
        min-height: 60px;
        padding: 9px 12px;
    }

    .mobile-navbar img {
        width: 34px;
        height: 34px;
    }

    .brand-mini span {
        font-size: 14px;
    }

    .mobile-menu-btn {
        width: 40px;
        height: 40px;
        flex-basis: 40px;
    }

    .main {
        padding: 78px 14px 20px;
    }

    .top-header {
        padding: 15px 16px;
        margin-bottom: 18px;
        border-radius: 13px;
    }

    .welcome-text h3 {
        font-size: 19px;
    }

    .welcome-text p {
        font-size: 11.5px;
    }

    .header-icon i {
        font-size: 29px;
    }

    .stat-card {
        padding: 18px;
        border-radius: 14px;
    }

    .card-icon {
        width: 48px;
        height: 48px;
        margin-bottom: 13px;
        font-size: 22px;
        border-radius: 12px;
    }

    .stat-card h2 {
        font-size: 30px;
    }

    .stat-card p {
        font-size: 12.5px;
    }
}

/* =====================================
   SMALL PHONE
===================================== */

@media (max-width: 480px) {

    .mobile-navbar {
        min-height: 58px;
        padding: 8px 10px;
    }

    .mobile-navbar img {
        width: 32px;
        height: 32px;
    }

    .brand-mini {
        gap: 8px;
    }

    .brand-mini span {
        font-size: 13.5px !important;
    }

    .mobile-menu-btn {
        width: 38px;
        height: 38px;
        flex-basis: 38px;
    }

    .mobile-menu-btn i {
        font-size: 21px;
    }

    .main {
        padding: 72px 9px 16px;
    }

    .top-header {
        padding: 14px 13px;
        gap: 10px;
        margin-bottom: 15px;
        border-radius: 12px;
    }

    .welcome-text h3 {
        font-size: 17px;
    }

    .welcome-text p {
        font-size: 10.5px;
        line-height: 1.4;
    }

    .header-icon i {
        font-size: 26px;
    }

    .stat-card {
        padding: 16px;
        border-radius: 13px;
    }

    .card-icon {
        width: 44px;
        height: 44px;
        margin-bottom: 11px;
        font-size: 20px;
        border-radius: 11px;
    }

    .stat-card h2 {
        font-size: 27px;
    }

    .stat-card p {
        font-size: 12px;
    }
}

/* =====================================
   VERY SMALL PHONE
===================================== */

@media (max-width: 360px) {

    .brand-mini span {
        font-size: 13px !important;
    }

    .mobile-menu-btn {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
    }

    .mobile-menu-btn i {
        font-size: 20px;
    }

    .main {
        padding: 70px 7px 14px;
    }

    .top-header {
        padding: 12px 11px;
    }

    .welcome-text h3 {
        font-size: 16px;
    }

    .welcome-text p {
        font-size: 10px;
    }

    .header-icon i {
        font-size: 24px;
    }

    .stat-card {
        padding: 14px;
    }

    .stat-card h2 {
        font-size: 25px;
    }

    .stat-card p {
        font-size: 11.5px;
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

        <span>PRMSU Guidance</span>
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
    aria-label="Administrator navigation"
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

    <div class="brand-header">

        <img
            src="../assets/images/prmsu-logo.png"
            alt="PRMSU Logo"
        >

        <div class="brand-text">

            <h4>PRMSU Guidance</h4>

            <span>Admin Panel</span>

        </div>

    </div>

    <!-- NAVIGATION -->

    <div class="nav-menu">

        <a
            href="dashboard.php"
            class="nav-link-item active"
        >
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <a
            href="students.php"
            class="nav-link-item"
        >
            <i class="bi bi-people-fill"></i>
            <span>Students</span>
        </a>

        <a
            href="counselors.php"
            class="nav-link-item"
        >
            <i class="bi bi-person-workspace"></i>
            <span>Counselors</span>
        </a>

        <a
            href="announcements.php"
            class="nav-link-item"
        >
            <i class="bi bi-megaphone-fill"></i>
            <span>Announcements</span>
        </a>

        <a
            href="../logout.php"
            class="nav-link-item logout-link"
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

    <!-- HEADER -->

    <div class="top-header">

        <div class="welcome-text">

            <h3>Admin Dashboard</h3>

            <p>
                Welcome to PRMSU Guidance and Counseling System
            </p>

        </div>

        <div class="header-icon">
            <i class="bi bi-person-circle"></i>
        </div>

    </div>

    <!-- =================================
         STATISTICS
    ================================== -->

    <div class="row g-4">

        <!-- STUDENTS -->

        <div class="col-12 col-sm-6 col-lg-4">

            <div class="stat-card card-students">

                <div class="card-icon">
                    <i class="bi bi-people-fill"></i>
                </div>

                <h2>
                    <?= htmlspecialchars($students); ?>
                </h2>

                <p>Total Students</p>

            </div>

        </div>

        <!-- COUNSELORS -->

        <div class="col-12 col-sm-6 col-lg-4">

            <div class="stat-card card-counselors">

                <div class="card-icon">
                    <i class="bi bi-person-workspace"></i>
                </div>

                <h2>
                    <?= htmlspecialchars($counselors); ?>
                </h2>

                <p>Total Counselors</p>

            </div>

        </div>

        <!-- ANNOUNCEMENTS -->

        <div class="col-12 col-sm-6 col-lg-4">

            <div class="stat-card card-announcements">

                <div class="card-icon">
                    <i class="bi bi-megaphone-fill"></i>
                </div>

                <h2>
                    <?= htmlspecialchars($announcements); ?>
                </h2>

                <p>Total Announcements</p>

            </div>

        </div>

    </div>

</div>

<!-- BOOTSTRAP JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>