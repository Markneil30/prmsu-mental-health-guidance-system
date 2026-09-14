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

$user_id = (int) $_SESSION['user_id'];

// =====================================
// GET RECORD ID
// =====================================

$record_id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($record_id <= 0) {
    header("Location: records.php");
    exit();
}

// =====================================
// GET ACTUAL COUNSELOR ID
// =====================================

$counselor_stmt = mysqli_prepare(
    $conn,
    "
    SELECT counselor_id
    FROM counselors
    WHERE user_id = ?
    LIMIT 1
    "
);

if (!$counselor_stmt) {
    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );
}

mysqli_stmt_bind_param(
    $counselor_stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($counselor_stmt);

$counselor_result = mysqli_stmt_get_result(
    $counselor_stmt
);

$counselor_data = mysqli_fetch_assoc(
    $counselor_result
);

mysqli_stmt_close($counselor_stmt);

if (!$counselor_data) {
    die(
        "Counselor account is not properly linked to the counselors table."
    );
}

$counselor_id = (int) $counselor_data['counselor_id'];

// =====================================
// GET COUNSELING RECORD
// =====================================

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        cr.record_id,
        cr.student_id,
        cr.counselor_id,
        cr.session_date,
        cr.notes,
        cr.created_at,

        student_user.fullname AS student_name,
        student_user.email AS student_email,

        students.student_number,
        students.year_level,
        students.course,
        students.gender,

        counselor_user.fullname AS counselor_name

    FROM counseling_records cr

    INNER JOIN students
        ON cr.student_id = students.student_id

    INNER JOIN users AS student_user
        ON students.user_id = student_user.id

    INNER JOIN counselors
        ON cr.counselor_id = counselors.counselor_id

    INNER JOIN users AS counselor_user
        ON counselors.user_id = counselor_user.id

    WHERE cr.record_id = ?
      AND cr.counselor_id = ?

    LIMIT 1
    "
);

// =====================================
// CHECK PREPARED STATEMENT
// =====================================

if (!$stmt) {
    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $record_id,
    $counselor_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$record = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// =====================================
// RECORD NOT FOUND
// =====================================

if (!$record) {
    header("Location: records.php");
    exit();
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

<title>
    View Counseling Record | PRMSU Guidance
</title>

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
    font-family: 'Poppins', sans-serif;
}

/* =====================================
   HTML / BODY
===================================== */

html {
    min-height: 100%;
}

body {
    min-height: 100vh;
    background:
        linear-gradient(
            rgba(244,247,251,.94),
            rgba(244,247,251,.94)
        ),
        url("../assets/images/dashboard-bg.jpg");
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
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
        4px 0 18px rgba(0,0,0,.08);

    display: flex;
    flex-direction: column;

    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
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

    flex-shrink: 0;
}

.brand img {
    width: 48px;
    height: 48px;
    object-fit: contain;
    flex-shrink: 0;
}

.brand-title {
    font-size: 16px;
    font-weight: 700;
    color: white;
    margin: 0;
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
    flex: 1;
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

    color: rgba(255,255,255,.85);

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
    text-align: center;
    font-size: 17px;
    flex-shrink: 0;
}

.sidebar a:hover {
    background: rgba(255,255,255,.12);
    color: white;
    transform: translateX(3px);
}

.sidebar a.active {
    background: #0d6efd;
    color: white;
}

/* =====================================
   LOGOUT
===================================== */

.logout {
    padding: 0 12px 20px;
    margin-top: auto;
    flex-shrink: 0;
}

.logout a {
    color: #ffdede;
    background: rgba(220,53,69,.15);
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
    min-height: 62px;

    background: #002147;
    color: white;

    padding: 10px 14px;

    position: fixed;
    top: 0;
    left: 0;

    z-index: 1040;

    align-items: center;
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
   SIDEBAR CLOSE
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
   MAIN
===================================== */

.main {
    margin-left: 260px;

    min-height: 100vh;

    padding: 30px 32px;

    width: auto;
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

.page-title {
    font-size: 25px;
    font-weight: 700;

    margin: 0;

    line-height: 1.3;
}

.page-subtitle {
    margin-top: 5px;

    color: #718096;

    font-size: 13px;

    line-height: 1.5;
}

/* =====================================
   USER
===================================== */

.user-box {
    display: flex;
    align-items: center;

    gap: 10px;

    background: white;

    padding: 8px 14px;

    border-radius: 10px;

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

    flex-shrink: 0;
}

.user-name {
    font-size: 13px;
    font-weight: 600;
}

/* =====================================
   RECORD CARD
===================================== */

.record-card {
    width: 100%;

    background: white;

    border-radius: 14px;

    overflow: hidden;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,.06);
}

.record-header {
    background:
        linear-gradient(
            135deg,
            #002147,
            #0d6efd
        );

    color: white;

    padding: 18px 22px;

    display: flex;

    justify-content: space-between;
    align-items: center;

    gap: 10px;
}

.record-header h4 {
    margin: 0;
    font-size: 18px;
    line-height: 1.4;
}

.record-body {
    padding: 25px;
}

/* =====================================
   INFO
===================================== */

.info-section {
    margin-bottom: 25px;
}

.section-title {
    font-size: 14px;

    font-weight: 700;

    color: #002147;

    border-bottom:
        2px solid #e9eef5;

    padding-bottom: 8px;

    margin-bottom: 15px;
}

.info-label {
    font-size: 11px;

    color: #7a8594;

    margin-bottom: 3px;
}

.info-value {
    font-size: 13px;

    font-weight: 600;

    color: #263238;

    overflow-wrap: anywhere;
}

/* =====================================
   NOTES
===================================== */

.notes-box {
    background: #f8fafc;

    border:
        1px solid #e5e7eb;

    border-radius: 10px;

    padding: 18px;

    font-size: 13px;

    line-height: 1.8;

    white-space: pre-wrap;

    min-height: 150px;

    overflow-wrap: anywhere;

    word-break: break-word;
}

/* =====================================
   ACTION BUTTONS
===================================== */

.action-buttons {
    display: flex;

    gap: 8px;

    flex-wrap: wrap;

    margin-top: 5px;
}

.action-buttons .btn {
    font-size: 12px;

    border-radius: 7px;

    padding: 9px 14px;

    min-height: 40px;
}

/* =====================================
   TABLET
===================================== */

@media (max-width: 991.98px) {

    body {
        overflow-x: hidden;
    }

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

        padding:
            82px 20px 30px;

        width: 100%;
    }

    .topbar {
        margin-bottom: 22px;
    }

    .page-title {
        font-size: 23px;
    }

    .page-subtitle {
        font-size: 12px;
    }

    .user-box {
        display: none;
    }

    .record-body {
        padding: 22px;
    }

    .record-header {
        padding: 17px 20px;
    }

    .record-header h4 {
        font-size: 18px;
    }

    .info-value {
        font-size: 13px;
    }

    .notes-box {
        font-size: 13px;
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
        padding:
            78px 14px 25px;
    }

    .topbar {
        margin-bottom: 18px;
    }

    .page-title {
        font-size: 21px;
    }

    .page-subtitle {
        font-size: 12px;
    }

    .record-card {
        border-radius: 12px;
    }

    .record-header {
        padding: 16px 17px;
    }

    .record-header h4 {
        font-size: 17px;
    }

    .record-body {
        padding: 18px 17px;
    }

    .info-section {
        margin-bottom: 22px;
    }

    .section-title {
        font-size: 14px;
        margin-bottom: 14px;
    }

    .info-label {
        font-size: 12px;
    }

    .info-value {
        font-size: 14px;
        line-height: 1.5;
    }

    .notes-box {
        padding: 15px;

        font-size: 14px;

        line-height: 1.7;

        min-height: 140px;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;

        gap: 10px;

        width: 100%;
    }

    .action-buttons .btn {
        width: 100%;

        min-height: 46px;

        font-size: 14px;

        display: flex;
        align-items: center;
        justify-content: center;
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

    .brand-mini {
        gap: 8px;
    }

    .brand-mini img {
        width: 32px;
        height: 32px;
    }

    .brand-mini span {
        font-size: 13.5px;
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
        padding:
            72px 9px 20px;
    }

    .page-title {
        font-size: 19px;
    }

    .page-subtitle {
        font-size: 11.5px;
    }

    .record-header {
        padding: 15px;
    }

    .record-header h4 {
        font-size: 16px;
    }

    .record-body {
        padding: 16px 14px;
    }

    .section-title {
        font-size: 13.5px;
    }

    .info-label {
        font-size: 12px;
    }

    .info-value {
        font-size: 13.5px;
    }

    .notes-box {
        padding: 14px;

        font-size: 13.5px;

        line-height: 1.7;
    }

    .action-buttons .btn {
        min-height: 46px;
        font-size: 13.5px;
    }
}

/* =====================================
   VERY SMALL PHONE
===================================== */

@media (max-width: 360px) {

    .brand-mini span {
        font-size: 13px;
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
        padding:
            70px 7px 18px;
    }

    .page-title {
        font-size: 18px;
    }

    .page-subtitle {
        font-size: 11px;
    }

    .record-header {
        padding: 14px;
    }

    .record-header h4 {
        font-size: 15px;
    }

    .record-body {
        padding: 15px 12px;
    }

    .info-value {
        font-size: 13px;
    }

    .notes-box {
        font-size: 13px;
        padding: 13px;
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
    aria-label="Counselor navigation"
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

        <a href="dashboard.php">

            <i class="bi bi-speedometer2"></i>

            Dashboard

        </a>

        <a href="appointment_requests.php">

            <i class="bi bi-calendar-check"></i>

            Appointment Requests

        </a>

        <a href="guidance_requests.php">

            <i class="bi bi-chat-left-text-fill"></i>

            Guidance Requests

        </a>

        <a
            href="records.php"
            class="active"
        >

            <i class="bi bi-folder2-open"></i>

            Counseling Records

        </a>

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
     MAIN
===================================== -->

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <div>

            <h2 class="page-title">
                Counseling Record
            </h2>

            <p class="page-subtitle">
                View counseling session details
            </p>

        </div>

        <div class="user-box">

            <div class="user-icon">

                <i class="bi bi-person-fill"></i>

            </div>

            <div class="user-name">

                <?= htmlspecialchars(
                    $_SESSION['fullname'] ?? 'Counselor'
                ); ?>

            </div>

        </div>

    </div>

    <!-- =====================================
         RECORD CARD
    ====================================== -->

    <div class="record-card">

        <!-- HEADER -->

        <div class="record-header">

            <h4>

                <i class="bi bi-journal-text me-1"></i>

                Counseling Session Record

            </h4>

        </div>

        <!-- BODY -->

        <div class="record-body">

            <!-- STUDENT INFORMATION -->

            <div class="info-section">

                <div class="section-title">

                    <i class="bi bi-person me-1"></i>

                    Student Information

                </div>

                <div class="row g-4">

                    <div class="col-12 col-md-6">

                        <div class="info-label">
                            Student Name
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $record['student_name'] ?? ''
                            ); ?>

                        </div>

                    </div>

                    <div class="col-12 col-md-6">

                        <div class="info-label">
                            Student Number
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $record['student_number'] ?? ''
                            ); ?>

                        </div>

                    </div>

                    <div class="col-12 col-md-6">

                        <div class="info-label">
                            Year Level
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $record['year_level'] ?? ''
                            ); ?>

                        </div>

                    </div>

                    <div class="col-12 col-md-6">

                        <div class="info-label">
                            Course
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $record['course'] ?? ''
                            ); ?>

                        </div>

                    </div>

                    <div class="col-12 col-md-6">

                        <div class="info-label">
                            Gender
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $record['gender'] ?? ''
                            ); ?>

                        </div>

                    </div>

                    <div class="col-12 col-md-6">

                        <div class="info-label">
                            Email
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $record['student_email'] ?? ''
                            ); ?>

                        </div>

                    </div>

                </div>

            </div>

            <!-- SESSION INFORMATION -->

            <div class="info-section">

                <div class="section-title">

                    <i class="bi bi-calendar-event me-1"></i>

                    Session Information

                </div>

                <div class="row g-4">

                    <div class="col-12 col-md-4">

                        <div class="info-label">
                            Session Date
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                date(
                                    "F d, Y",
                                    strtotime(
                                        $record['session_date']
                                    )
                                )
                            ); ?>

                        </div>

                    </div>

                    <div class="col-12 col-md-4">

                        <div class="info-label">
                            Counselor
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $record['counselor_name'] ?? ''
                            ); ?>

                        </div>

                    </div>

                    <div class="col-12 col-md-4">

                        <div class="info-label">
                            Record Created
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                date(
                                    "F d, Y h:i A",
                                    strtotime(
                                        $record['created_at']
                                    )
                                )
                            ); ?>

                        </div>

                    </div>

                </div>

            </div>

            <!-- NOTES -->

            <div class="info-section">

                <div class="section-title">

                    <i class="bi bi-file-text me-1"></i>

                    Counseling Notes

                </div>

                <div class="notes-box">

                    <?= htmlspecialchars(
                        $record['notes'] ?? ''
                    ); ?>

                </div>

            </div>

            <!-- ACTIONS -->

            <div class="action-buttons">

                <a
                    href="print_record.php?id=<?= (int)$record['record_id']; ?>"
                    target="_blank"
                    class="btn btn-primary"
                >

                    <i class="bi bi-printer me-1"></i>

                    Print Report

                </a>

                <a
                    href="edit_record.php?id=<?= (int)$record['record_id']; ?>"
                    class="btn btn-warning"
                >

                    <i class="bi bi-pencil me-1"></i>

                    Edit Record

                </a>

                <a
                    href="records.php"
                    class="btn btn-secondary"
                >

                    <i class="bi bi-arrow-left me-1"></i>

                    Back to Records

                </a>

            </div>

        </div>

    </div>

</div>

<!-- =====================================
     BOOTSTRAP JS
===================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>