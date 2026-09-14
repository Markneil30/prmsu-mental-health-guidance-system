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
    header("Location: ../login.php");
    exit();
}


// =====================================
// SUCCESS MESSAGE
// =====================================

$success = "";

if (
    isset($_GET['success']) &&
    $_GET['success'] == "1"
) {

    $success = "
        <div class='alert alert-success alert-dismissible fade show' role='alert'>
            <i class='bi bi-check-circle-fill me-1'></i>
            Announcement posted successfully.
            <button
                type='button'
                class='btn-close'
                data-bs-dismiss='alert'
            ></button>
        </div>
    ";

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


// =====================================
// CHECK QUERY
// =====================================

if ($query === false) {

    die(
        "Announcements Query Error: "
        . htmlspecialchars(mysqli_error($conn))
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

<title>Manage Announcements | PRMSU Guidance</title>


<link
    rel="icon"
    href="../assets/images/prmsu-logo.png"
>


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
   GLOBAL
===================================== */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

}

body {

    background:
        linear-gradient(
            rgba(244,247,251,.94),
            rgba(244,247,251,.94)
        ),
        url("../assets/images/dashboard-bg.jpg");

    background-size: cover;

    background-position: center;

    background-repeat: no-repeat;

    background-attachment: fixed;

    font-family: "Poppins", sans-serif;

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
        rgba(0,0,0,.08);

    transition:
        transform .3s ease;

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

}

.brand img {

    width: 48px;

    height: 48px;

    object-fit: contain;

}

.brand-text {

    line-height: 1.2;

}

.brand-title {

    font-size: 16px;

    font-weight: 700;

    margin: 0;

    color: #ffffff;

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

}

.nav-label {

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: 1px;

    opacity: .55;

    padding:
        0 12px 8px;

}

.sidebar a {

    display: flex;

    align-items: center;

    gap: 12px;

    color:
        rgba(255,255,255,.85);

    text-decoration: none;

    padding: 12px 14px;

    margin-bottom: 4px;

    border-radius: 8px;

    font-size: 13.5px;

    font-weight: 500;

    transition: .2s ease;

}

.sidebar a i {

    width: 20px;

    text-align: center;

    font-size: 17px;

}

.sidebar a:hover {

    background:
        rgba(255,255,255,.12);

    color: #ffffff;

    transform:
        translateX(3px);

}

.sidebar a.active {

    background: #0d6efd;

    color: #ffffff;

    box-shadow:
        0 4px 12px
        rgba(13,110,253,.3);

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
        rgba(220,53,69,.15);

}

.logout a:hover {

    background: #dc3545;

    color: white;

}


/* =====================================
   MAIN
===================================== */

.main {

    margin-left: 260px;

    min-height: 100vh;

    padding: 28px 32px;

    transition: .3s ease;

}


/* =====================================
   TOPBAR
===================================== */

.topbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

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


/* =====================================
   USER BOX
===================================== */

.user-box {

    display: flex;

    align-items: center;

    gap: 10px;

    background:
        rgba(255,255,255,.9);

    backdrop-filter: blur(10px);

    padding: 8px 14px;

    border-radius: 10px;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,.04);

    border:
        1px solid
        rgba(255,255,255,.6);

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

}


/* =====================================
   PAGE HEADER
===================================== */

.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    margin-bottom: 20px;

}

.page-title h3 {

    margin: 0;

    color: #172033;

    font-size: 23px;

    font-weight: 700;

}

.page-title p {

    margin: 4px 0 0;

    color: #718096;

    font-size: 12px;

}


/* =====================================
   MAIN CARD
===================================== */

.main-card {

    background:
        rgba(255,255,255,.95);

    backdrop-filter: blur(10px);

    border:
        1px solid #e9eef5;

    border-radius: 14px;

    overflow: hidden;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,.05);

}


/* =====================================
   CARD HEADER
===================================== */

.card-header-custom {

    background:
        linear-gradient(
            135deg,
            #002147,
            #0d6efd
        );

    color: #ffffff;

    padding: 18px 22px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

}

.card-header-custom h4 {

    margin: 0;

    font-size: 17px;

    font-weight: 600;

}

.card-header-custom .btn {

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;

}


/* =====================================
   CARD BODY
===================================== */

.card-body-custom {

    padding: 20px;

}


/* =====================================
   ALERT
===================================== */

.alert {

    font-size: 13px;

    border-radius: 8px;

}


/* =====================================
   TABLE WRAPPER
===================================== */

.table-wrapper {

    width: 100%;

    overflow-x: auto;

    -webkit-overflow-scrolling: touch;

}


/* =====================================
   TABLE
===================================== */

.announcement-table {

    width: 100%;

    min-width: 850px;

    margin: 0;

    font-size: 13px;

}

.announcement-table th {

    padding: 11px 10px;

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;

    vertical-align: middle;

}

.announcement-table td {

    padding: 11px 10px;

    vertical-align: middle;

}


/* =====================================
   TITLE
===================================== */

.announcement-title {

    font-weight: 600;

    color: #172033;

    max-width: 220px;

}


/* =====================================
   CONTENT
===================================== */

.announcement-content {

    color: #475569;

    line-height: 1.5;

    max-width: 420px;

}


/* =====================================
   DATE
===================================== */

.date-posted {

    color: #64748b;

    font-size: 12px;

    white-space: nowrap;

}


/* =====================================
   ACTION
===================================== */

.action-buttons {

    display: flex;

    align-items: center;

    gap: 5px;

    white-space: nowrap;

}

.action-buttons .btn {

    font-size: 11.5px;

    padding: 5px 9px;

    border-radius: 6px;

}


/* =====================================
   EMPTY STATE
===================================== */

.empty-state {

    text-align: center;

    padding: 50px 20px !important;

    color: #6c757d;

}

.empty-state i {

    display: block;

    font-size: 35px;

    margin-bottom: 10px;

}

.empty-state strong {

    display: block;

    color: #475569;

    font-size: 14px;

    margin-bottom: 3px;

}

.empty-state span {

    font-size: 12px;

}


/* =====================================
   BACK BUTTON
===================================== */

.back-btn {

    margin-top: 18px;

    font-size: 12px;

    padding: 7px 12px;

}


/* =====================================
   MOBILE NAVBAR
===================================== */

.mobile-navbar {

    display: none;

    background: #002147;

    color: white;

    padding: 12px 18px;

    position: sticky;

    top: 0;

    z-index: 1040;

    box-shadow:
        0 2px 10px
        rgba(0,0,0,.1);

}

.brand-mini {

    display: flex;

    align-items: center;

    gap: 10px;

}

.brand-mini img {

    width: 36px;

    height: 36px;

    object-fit: contain;

}


/* =====================================
   SIDEBAR OVERLAY
===================================== */

.sidebar-overlay {

    display: none;

    position: fixed;

    inset: 0;

    background:
        rgba(0,0,0,.45);

    z-index: 1045;

}


/* =====================================
   TABLET
===================================== */

@media (max-width: 991px) {

    .sidebar {

        transform:
            translateX(-100%);

    }

    .sidebar.show {

        transform:
            translateX(0);

    }

    .sidebar-overlay.show {

        display: block;

    }

    .mobile-navbar {

        display: flex;

        align-items: center;

        justify-content: space-between;

    }

    .main {

        margin-left: 0;

        padding: 22px 18px;

    }

    .user-box {

        display: none;

    }

}


/* =====================================
   SMALL TABLET
===================================== */

@media (max-width: 768px) {

    .main {

        padding: 18px 12px;

    }

    .topbar {

        margin-bottom: 18px;

    }

    .welcome h2 {

        font-size: 20px;

    }

    .welcome p {

        font-size: 12px;

    }

    .page-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 10px;

    }

    .page-title h3 {

        font-size: 20px;

    }

    .card-header-custom {

        padding: 15px;

    }

    .card-header-custom h4 {

        font-size: 15px;

    }

    .card-body-custom {

        padding: 12px;

    }

}


/* =====================================
   PHONE
===================================== */

@media (max-width: 480px) {

    .mobile-navbar {

        padding: 10px 12px;

    }

    .brand-mini span {

        font-size: 14px !important;

    }

    .main {

        padding: 14px 9px;

    }

    .welcome h2 {

        font-size: 18px;

    }

    .welcome p {

        font-size: 11px;

    }

    .page-title h3 {

        font-size: 18px;

    }

    .page-title p {

        font-size: 11px;

    }

    .card-header-custom {

        padding: 13px;

        flex-direction: column;

        align-items: flex-start;

    }

    .card-header-custom .btn {

        width: 100%;

    }

    .card-body-custom {

        padding: 10px;

    }

    .announcement-table {

        min-width: 800px;

        font-size: 12px;

    }

    .announcement-table th {

        font-size: 10.5px;

        padding: 8px;

    }

    .announcement-table td {

        padding: 8px;

    }

}


/* =====================================
   VERY SMALL PHONE
===================================== */

@media (max-width: 360px) {

    .main {

        padding: 12px 7px;

    }

    .page-title h3 {

        font-size: 16px;

    }

    .card-header-custom h4 {

        font-size: 14px;

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

        <span class="fw-bold">
            PRMSU Guidance
        </span>

    </div>


    <button
        class="btn btn-outline-light btn-sm"
        type="button"
        onclick="toggleSidebar()"
    >

        <i class="bi bi-list fs-5"></i>

    </button>

</div>


<!-- =====================================
     SIDEBAR OVERLAY
===================================== -->

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
    onclick="toggleSidebar()"
></div>


<!-- =====================================
     SIDEBAR
===================================== -->

<div
    class="sidebar"
    id="sidebarMenu"
>


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
                Administrator Portal
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


        <!-- STUDENTS -->

        <a href="students.php">

            <i class="bi bi-people-fill"></i>

            Students

        </a>


        <!-- COUNSELORS -->

        <a href="counselors.php">

            <i class="bi bi-person-workspace"></i>

            Counselors

        </a>


        <!-- ANNOUNCEMENTS -->

        <a
            href="Announcements.php"
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
     MAIN
===================================== -->

<div class="main">


    <!-- =================================
         TOPBAR
    ================================== -->

    <div class="topbar">

        <div class="welcome">

            <h2>

                Welcome back,
                <?= htmlspecialchars(
                    $_SESSION['fullname'] ?? 'Administrator'
                ); ?>

                👋

            </h2>

            <p>
                Administrator Management Dashboard
            </p>

        </div>


        <div class="user-box">

            <div class="user-icon">

                <i class="bi bi-person-fill"></i>

            </div>

            <div class="user-name">

                <?= htmlspecialchars(
                    $_SESSION['fullname'] ?? 'Administrator'
                ); ?>

            </div>

        </div>

    </div>


    <!-- =================================
         PAGE HEADER
    ================================== -->

    <div class="page-header">

        <div class="page-title">

            <h3>

                <i class="bi bi-megaphone-fill me-1"></i>

                Manage Announcements

            </h3>

            <p>
                Create and manage announcements from the guidance office.
            </p>

        </div>

    </div>


    <!-- =================================
         MAIN CARD
    ================================== -->

    <div class="main-card">


        <!-- CARD HEADER -->

        <div class="card-header-custom">


            <h4>

                <i class="bi bi-megaphone-fill me-1"></i>

                Announcements

            </h4>


            <!-- ADD ANNOUNCEMENT -->

            <a
                href="add_announcement.php"
                class="btn btn-light"
            >

                <i class="bi bi-plus-circle me-1"></i>

                Add Announcement

            </a>


        </div>


        <!-- CARD BODY -->

        <div class="card-body-custom">


            <!-- SUCCESS MESSAGE -->

            <?= $success; ?>


            <!-- =================================
                 TABLE
            ================================== -->

            <div class="table-wrapper">


                <table
                    class="
                        table
                        table-bordered
                        table-hover
                        align-middle
                        announcement-table
                    "
                >


                    <thead class="table-dark">

                        <tr>

                            <th width="60">
                                #
                            </th>

                            <th>
                                Title
                            </th>

                            <th>
                                Content
                            </th>

                            <th>
                                Date Posted
                            </th>

                            <th width="170">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    $count = 1;


                    if (
                        mysqli_num_rows($query) > 0
                    ) {

                        while (
                            $row =
                            mysqli_fetch_assoc($query)
                        ) {

                    ?>


                        <tr>


                            <!-- NUMBER -->

                            <td>

                                <?= $count++; ?>

                            </td>


                            <!-- TITLE -->

                            <td>

                                <div class="announcement-title">

                                    <?= htmlspecialchars(
                                        $row['title']
                                    ); ?>

                                </div>

                            </td>


                            <!-- CONTENT -->

                            <td>

                                <div class="announcement-content">

                                    <?php

                                    $content =
                                        $row['content'] ?? '';

                                    if (
                                        strlen($content) > 100
                                    ) {

                                        echo htmlspecialchars(
                                            substr(
                                                $content,
                                                0,
                                                100
                                            )
                                        ) . "...";

                                    } else {

                                        echo htmlspecialchars(
                                            $content
                                        );

                                    }

                                    ?>

                                </div>

                            </td>


                            <!-- DATE -->

                            <td>

                                <span class="date-posted">

                                    <?php

                                    if (
                                        !empty(
                                            $row['created_at']
                                        )
                                    ) {

                                        echo htmlspecialchars(
                                            date(
                                                "M d, Y h:i A",
                                                strtotime(
                                                    $row['created_at']
                                                )
                                            )
                                        );

                                    } else {

                                        echo "—";

                                    }

                                    ?>

                                </span>

                            </td>


                            <!-- ACTION -->

                            <td>


                                <div class="action-buttons">


                                    <!-- EDIT -->

                                    <a
                                        href="edit_announcement.php?id=<?= (int)$row['announcement_id']; ?>"
                                        class="btn btn-warning btn-sm"
                                        title="Edit Announcement"
                                    >

                                        <i class="bi bi-pencil-square"></i>

                                        Edit

                                    </a>


                                    <!-- DELETE -->

                                    <a
                                        href="delete_announcement.php?id=<?= (int)$row['announcement_id']; ?>"
                                        class="btn btn-danger btn-sm"
                                        title="Delete Announcement"
                                        onclick="
                                            return confirm(
                                                'Delete this announcement?'
                                            );
                                        "
                                    >

                                        <i class="bi bi-trash"></i>

                                        Delete

                                    </a>


                                </div>


                            </td>


                        </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <!-- EMPTY STATE -->

                        <tr>

                            <td
                                colspan="5"
                                class="empty-state"
                            >

                                <i class="bi bi-megaphone"></i>

                                <strong>
                                    No announcements found.
                                </strong>

                                <span>
                                    Create an announcement to display it here.
                                </span>

                            </td>

                        </tr>


                    <?php

                    }

                    ?>


                    </tbody>


                </table>


            </div>


            <!-- =================================
                 BACK
            ================================== -->

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- =====================================
     SIDEBAR SCRIPT
===================================== -->

<script>

function toggleSidebar() {

    const sidebar =
        document.getElementById("sidebarMenu");

    const overlay =
        document.getElementById("sidebarOverlay");


    sidebar.classList.toggle("show");

    overlay.classList.toggle("show");

}


// =====================================
// CLOSE SIDEBAR AFTER CLICKING MENU
// =====================================

document
    .querySelectorAll(".sidebar a")
    .forEach(function(link) {

        link.addEventListener(
            "click",
            function() {

                if (
                    window.innerWidth <= 991
                ) {

                    document
                        .getElementById("sidebarMenu")
                        .classList.remove("show");

                    document
                        .getElementById("sidebarOverlay")
                        .classList.remove("show");

                }

            }
        );

    });

</script>


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