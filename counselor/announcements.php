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
    header("Location: ../login.php");
    exit();
}

// =====================================
// GET ANNOUNCEMENTS
// =====================================

$query = mysqli_query($conn, "
    SELECT
        announcement_id,
        title,
        content,
        created_at
    FROM announcements
    ORDER BY created_at DESC
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

```
<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Announcements | PRMSU Guidance</title>

<link
    rel="icon"
    href="../assets/images/prmsu-logo.png"
>

<!-- Bootstrap -->
<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<!-- Bootstrap Icons -->
<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    rel="stylesheet"
>

<!-- Google Font -->
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

    html {
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

        font-family: "Poppins", sans-serif;
        color: #1f2937;

        min-height: 100vh;
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
            4px 0 18px rgba(0, 0, 0, 0.08);

        overflow-y: auto;
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

        background: rgba(255, 255, 255, 0.10);

        color: white;

        align-items: center;
        justify-content: center;

        font-size: 16px;

        cursor: pointer;

        z-index: 10;
    }

    .sidebar-close:hover {
        background: rgba(255, 255, 255, 0.18);
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
            1px solid rgba(255, 255, 255, 0.14);
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
        font-size: 11px;

        text-transform: uppercase;
        letter-spacing: 1px;

        opacity: 0.55;

        padding: 0 12px 8px;
    }

    .sidebar a {
        display: flex;
        align-items: center;

        gap: 12px;

        color: rgba(255, 255, 255, 0.85);

        text-decoration: none;

        padding: 12px 14px;

        margin-bottom: 4px;

        border-radius: 8px;

        font-size: 14px;
        font-weight: 500;

        transition:
            background 0.2s ease,
            color 0.2s ease,
            transform 0.2s ease;
    }

    .sidebar a i {
        width: 20px;
        min-width: 20px;

        text-align: center;

        font-size: 17px;
    }

    .sidebar a:hover {
        background: rgba(255, 255, 255, 0.12);

        color: white;

        transform: translateX(3px);
    }

    .sidebar a.active {
        background: #0d6efd;

        color: white;

        box-shadow:
            0 4px 12px rgba(13, 110, 253, 0.3);
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
            rgba(220, 53, 69, 0.15);
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

        padding: 9px 15px;

        align-items: center;

        position: sticky;
        top: 0;

        z-index: 1040;

        box-shadow:
            0 2px 10px rgba(0, 0, 0, 0.10);
    }

    .brand-mini {
        display: flex;
        align-items: center;

        gap: 9px;

        min-width: 0;

        flex: 1;
    }

    .brand-mini img {
        width: 37px;
        height: 37px;

        object-fit: contain;

        flex-shrink: 0;
    }

    .brand-mini span {
        font-size: 15px;
        font-weight: 700;

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
        font-size: 22px;
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

        font-size: 14px;
    }

    /* =====================================
       USER BOX
    ===================================== */

    .user-box {
        display: flex;
        align-items: center;

        gap: 10px;

        background:
            rgba(255, 255, 255, 0.90);

        backdrop-filter: blur(10px);

        padding: 8px 14px;

        border-radius: 10px;

        box-shadow:
            0 3px 12px rgba(0, 0, 0, 0.04);

        border:
            1px solid rgba(255, 255, 255, 0.60);

        flex-shrink: 0;
    }

    .user-icon {
        width: 38px;
        height: 38px;

        border-radius: 50%;

        background: #e8f1ff;

        color: #0d6efd;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 18px;
    }

    .user-name {
        font-size: 14px;
        font-weight: 600;

        white-space: nowrap;
    }

    /* =====================================
       MAIN CARD
    ===================================== */

    .main-card {
        width: 100%;

        background:
            rgba(255, 255, 255, 0.95);

        backdrop-filter: blur(10px);

        border-radius: 14px;

        overflow: hidden;

        border:
            1px solid #e9eef5;

        box-shadow:
            0 5px 20px rgba(0, 0, 0, 0.06);
    }

    /* =====================================
       HEADER
    ===================================== */

    .main-header {
        background:
            linear-gradient(
                135deg,
                #002147,
                #0d6efd
            );

        color: white;

        padding: 20px 24px;
    }

    .main-header h4 {
        margin: 0;

        font-size: 19px;
        font-weight: 600;
    }

    .main-header p {
        margin: 5px 0 0;

        font-size: 13px;

        color: rgba(255, 255, 255, 0.80);
    }

    /* =====================================
       BODY
    ===================================== */

    .main-body {
        padding: 24px;
    }

    /* =====================================
       ANNOUNCEMENT CARD
    ===================================== */

    .announcement-card {
        background: white;

        border:
            1px solid #e5e7eb;

        border-radius: 12px;

        padding: 20px;

        margin-bottom: 15px;

        transition:
            box-shadow 0.2s ease,
            transform 0.2s ease;
    }

    .announcement-card:hover {
        box-shadow:
            0 5px 18px rgba(15, 23, 42, 0.07);

        transform: translateY(-1px);
    }

    /* =====================================
       ANNOUNCEMENT TITLE
    ===================================== */

    .announcement-title {
        display: flex;
        align-items: flex-start;

        gap: 9px;

        color: #172554;

        font-size: 17px;
        font-weight: 600;

        margin-bottom: 10px;

        line-height: 1.45;

        overflow-wrap: anywhere;
    }

    .announcement-title i {
        color: #0d6efd;

        margin-top: 3px;

        flex-shrink: 0;

        font-size: 17px;
    }

    /* =====================================
       ANNOUNCEMENT CONTENT
    ===================================== */

    .announcement-content {
        color: #475569;

        font-size: 14px;

        line-height: 1.7;

        word-break: break-word;

        overflow-wrap: anywhere;
    }

    /* =====================================
       DATE
    ===================================== */

    .announcement-date {
        display: block;

        margin-top: 15px;

        padding-top: 12px;

        border-top:
            1px solid #e5e7eb;

        color: #94a3b8;

        font-size: 12px;
    }

    .announcement-date i {
        color: #0d6efd;
    }

    /* =====================================
       EMPTY STATE
    ===================================== */

    .empty-state {
        text-align: center;

        padding: 60px 20px;

        color: #64748b;
    }

    .empty-icon {
        width: 65px;
        height: 65px;

        margin: 0 auto 15px;

        border-radius: 16px;

        background: #f1f5f9;

        color: #94a3b8;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 28px;
    }

    .empty-state h5 {
        margin: 0 0 6px;

        color: #334155;

        font-size: 17px;
        font-weight: 600;
    }

    .empty-state p {
        margin: 0;

        font-size: 14px;

        line-height: 1.5;
    }

    /* =====================================
       BACK BUTTON
    ===================================== */

    .back-btn {
        margin-top: 5px;

        font-size: 13px;

        padding: 8px 14px;

        border-radius: 7px;
    }

    /* =====================================
       TABLET
    ===================================== */

    @media (max-width: 1199px) {

        .main {
            padding: 25px 22px;
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
            width: 260px;

            max-width: 85vw;

            overflow-y: auto;

            box-shadow:
                6px 0 20px rgba(0, 0, 0, 0.15);
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

            padding: 24px 20px 30px;
        }

        /* TOPBAR */

        .topbar {
            margin-bottom: 22px;
        }

        .user-box {
            display: none;
        }

        /* HEADER */

        .main-header {
            padding: 20px 22px;
        }

        .main-header h4 {
            font-size: 18px;
        }

        .main-header p {
            font-size: 13px;
        }

        /* BODY */

        .main-body {
            padding: 22px;
        }

        /* ANNOUNCEMENT */

        .announcement-card {
            padding: 20px;
        }

    }

    /* =====================================
       MOBILE
    ===================================== */

    @media (max-width: 768px) {

        .mobile-navbar {
            padding: 9px 13px;
        }

        .brand-mini img {
            width: 36px;
            height: 36px;
        }

        .brand-mini span {
            font-size: 15px;
        }

        .mobile-menu-btn {
            width: 41px;
            height: 39px;

            margin-left: auto;
        }

        /* SIDEBAR */

        .sidebar {
            width: 260px;
            max-width: 85vw;
        }

        .brand {
            height: 82px;

            padding: 14px 15px;

            padding-right: 55px;
        }

        .brand img {
            width: 44px;
            height: 44px;
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
            font-size: 11px;
        }

        .sidebar a {
            padding: 12px 13px;

            font-size: 14px;
        }

        .sidebar a:hover {
            transform: none;
        }

        /* MAIN */

        .main {
            padding: 22px 16px 30px;
        }

        /* TOPBAR */

        .topbar {
            display: block;

            margin-bottom: 20px;
        }

        .welcome h2 {
            font-size: 22px;

            line-height: 1.35;
        }

        .welcome p {
            font-size: 13px;

            line-height: 1.5;
        }

        /* HEADER */

        .main-header {
            padding: 19px;
        }

        .main-header h4 {
            font-size: 18px;
        }

        .main-header p {
            font-size: 12.5px;

            line-height: 1.5;
        }

        /* BODY */

        .main-body {
            padding: 19px;
        }

        /* ANNOUNCEMENT */

        .announcement-card {
            padding: 19px;

            margin-bottom: 15px;
        }

        .announcement-title {
            font-size: 16px;

            line-height: 1.5;
        }

        .announcement-title i {
            font-size: 16px;
        }

        .announcement-content {
            font-size: 14px;

            line-height: 1.7;
        }

        .announcement-date {
            font-size: 12px;

            line-height: 1.5;
        }

        /* EMPTY */

        .empty-state {
            padding: 50px 15px;
        }

    }

    /* =====================================
       SMALL PHONE
    ===================================== */

    @media (max-width: 480px) {

        .mobile-navbar {
            padding: 9px 12px;
        }

        .brand-mini img {
            width: 35px;
            height: 35px;
        }

        .brand-mini span {
            font-size: 14px;
        }

        .mobile-menu-btn {
            width: 40px;
            height: 38px;
        }

        .mobile-menu-btn i {
            font-size: 21px;
        }

        /* MAIN */

        .main {
            padding: 20px 12px 28px;
        }

        /* TOPBAR */

        .welcome h2 {
            font-size: 21px;
        }

        .welcome p {
            font-size: 13px;
        }

        /* HEADER */

        .main-header {
            padding: 18px 17px;
        }

        .main-header h4 {
            font-size: 17px;
        }

        .main-header p {
            font-size: 12px;
        }

        /* BODY */

        .main-body {
            padding: 16px;
        }

        /* ANNOUNCEMENT */

        .announcement-card {
            padding: 17px;

            border-radius: 11px;
        }

        .announcement-title {
            font-size: 15.5px;

            gap: 8px;
        }

        .announcement-content {
            font-size: 13.5px;

            line-height: 1.7;
        }

        .announcement-date {
            font-size: 11.5px;

            margin-top: 13px;

            padding-top: 10px;
        }

        /* BACK */

        .back-btn {
            font-size: 12.5px;

            padding: 8px 13px;
        }

    }

    /* =====================================
       VERY SMALL PHONE
    ===================================== */

    @media (max-width: 360px) {

        .brand-mini span {
            font-size: 13.5px;
        }

        .mobile-menu-btn {
            width: 39px;
            height: 37px;
        }

        .main {
            padding: 18px 10px 24px;
        }

        .welcome h2 {
            font-size: 20px;
        }

        .welcome p {
            font-size: 12.5px;
        }

        .main-header {
            padding: 17px 15px;
        }

        .main-header h4 {
            font-size: 16px;
        }

        .main-header p {
            font-size: 11.5px;
        }

        .main-body {
            padding: 14px;
        }

        .announcement-card {
            padding: 16px;
        }

        .announcement-title {
            font-size: 15px;
        }

        .announcement-content {
            font-size: 13px;
        }

    }

</style>
```

</head>

<body>

```
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

        <!-- APPOINTMENT REQUESTS -->

        <a href="appointment_requests.php">

            <i class="bi bi-calendar-check"></i>

            Appointment Requests

        </a>

        <!-- GUIDANCE REQUESTS -->

        <a href="guidance_requests.php">

            <i class="bi bi-chat-left-text-fill"></i>

            Guidance Requests

        </a>

        <!-- COUNSELING RECORDS -->

        <a href="records.php">

            <i class="bi bi-folder2-open"></i>

            Counseling Records

        </a>

        <!-- ANNOUNCEMENTS -->

        <a
            href="announcements.php"
            class="active"
        >

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

    <!-- TOPBAR -->

    <div class="topbar">

        <div class="welcome">

            <h2>
                Announcements
            </h2>

            <p>
                View announcements from the guidance office.
            </p>

        </div>

        <!-- USER -->

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
         ANNOUNCEMENTS CARD
    ===================================== -->

    <div class="main-card">

        <!-- HEADER -->

        <div class="main-header">

            <h4>

                <i class="bi bi-megaphone-fill me-2"></i>

                Announcements

            </h4>

            <p>
                Stay updated with announcements from the guidance office.
            </p>

        </div>

        <!-- BODY -->

        <div class="main-body">

            <?php

            if (mysqli_num_rows($query) > 0) {

                while ($row = mysqli_fetch_assoc($query)) {

            ?>

                <!-- ANNOUNCEMENT -->

                <div class="announcement-card">

                    <!-- TITLE -->

                    <div class="announcement-title">

                        <i class="bi bi-megaphone"></i>

                        <span>

                            <?= htmlspecialchars(
                                $row['title'] ?? ''
                            ); ?>

                        </span>

                    </div>

                    <!-- CONTENT -->

                    <div class="announcement-content">

                        <?= nl2br(
                            htmlspecialchars(
                                $row['content'] ?? ''
                            )
                        ); ?>

                    </div>

                    <!-- DATE -->

                    <span class="announcement-date">

                        <i class="bi bi-calendar3 me-1"></i>

                        Posted:

                        <?= !empty($row['created_at'])
                            ? htmlspecialchars(
                                date(
                                    "M d, Y h:i A",
                                    strtotime(
                                        $row['created_at']
                                    )
                                )
                            )
                            : 'Date unavailable';
                        ?>

                    </span>

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
                        No Announcements Available
                    </h5>

                    <p>
                        There are currently no announcements from the guidance office.
                    </p>

                </div>

            <?php

            }

            ?>

            <!-- BACK -->

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
```

</body>

</html>

<?php

if (
    isset($query) &&
    $query instanceof mysqli_result
) {
    mysqli_free_result($query);
}

?>
