<?php

session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

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

include("../includes/db.php");

$user_id = (int) $_SESSION['user_id'];


// =====================================
// MARK ALL UNREAD NOTIFICATIONS AS READ
// =====================================
//
// Kapag binuksan ang Notifications page,
// lahat ng unread notifications ng student
// ay magiging read.
//
// Dahil dito, ang notification count
// sa dashboard/sidebar ay magiging 0.
//

$stmt = $conn->prepare("
    UPDATE notifications
    SET is_read = 1
    WHERE user_id = ?
    AND is_read = 0
");

if ($stmt) {

    $stmt->bind_param("i", $user_id);

    $stmt->execute();

    $stmt->close();
}


// =====================================
// GET ALL NOTIFICATIONS
// =====================================

$stmt = $conn->prepare("
    SELECT
        notification_id,
        type,
        message,
        is_read,
        created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
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


// =====================================
// COUNT ALL NOTIFICATIONS
// =====================================
//
// Ito ay para malaman kung may notification
// na ipapakita sa notification list.
//
// HINDI ito ang unread count.
// Dahil lahat ng notification ay minarkahan
// na as read sa taas.
//

$total_notifications = $result->num_rows;

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Notifications | PRMSU Guidance</title>

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

body {

    background: #f4f6f9;

    font-family: "Poppins", sans-serif;

    color: #172554;

    min-height: 100vh;

}


/* =====================================
   MAIN
===================================== */

.main {

    margin-left: 245px;

    padding: 35px;

    min-height: 100vh;

}


/* =====================================
   PAGE HEADER
===================================== */

.page-header {

    margin-bottom: 25px;

}

.page-header h2 {

    margin: 0;

    font-size: 27px;

    font-weight: 700;

    color: #10204a;

}

.page-header p {

    margin-top: 5px;

    color: #718096;

    font-size: 13px;

}


/* =====================================
   NOTIFICATION CARD
===================================== */

.notification-card {

    background: white;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 8px 25px
        rgba(15,23,42,.06);

}


/* =====================================
   CARD HEADER
===================================== */

.card-header-custom {

    padding: 20px 24px;

    border-bottom: 1px solid #e5e7eb;

    display: flex;

    align-items: center;

    justify-content: space-between;

}

.card-header-custom h5 {

    margin: 0;

    font-size: 17px;

    font-weight: 600;

    color: #172554;

}


/* =====================================
   NOTIFICATION COUNT
===================================== */

.notification-count {

    background: #e8efff;

    color: #2563eb;

    padding: 5px 11px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 600;

}


/* =====================================
   NOTIFICATION ITEM
===================================== */

.notification-item {

    display: flex;

    gap: 15px;

    padding: 20px 24px;

    border-bottom: 1px solid #edf0f4;

    transition: background .2s ease;

}

.notification-item:last-child {

    border-bottom: none;

}

.notification-item:hover {

    background: #f8faff;

}


/* =====================================
   READ NOTIFICATION
===================================== */

.notification-item.read {

    background: white;

}


/* =====================================
   ICON
===================================== */

.notification-icon {

    width: 45px;

    height: 45px;

    min-width: 45px;

    border-radius: 12px;

    background: #e8efff;

    color: #2563eb;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 20px;

}


/* =====================================
   CONTENT
===================================== */

.notification-content {

    flex: 1;

    min-width: 0;

}

.notification-message {

    color: #172554;

    font-size: 13px;

    line-height: 1.6;

    margin-bottom: 5px;

}

.notification-date {

    color: #94a3b8;

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

    font-size: 17px;

    font-weight: 600;

    color: #172554;

}

.empty p {

    margin-top: 6px;

    color: #94a3b8;

    font-size: 12px;

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

    z-index: 9999;

    overflow-y: auto;

}


/* =====================================
   BRAND
===================================== */

.brand {

    height: 88px;

    padding: 15px 20px;

    display: flex;

    align-items: center;

    gap: 12px;

    border-bottom:
        1px solid
        rgba(255,255,255,.15);

}

.brand img {

    width: 52px;

    height: 52px;

    object-fit: contain;

}

.brand h3 {

    margin: 0;

    font-size: 17px;

    font-weight: 700;

    color: white;

}

.brand small {

    color: rgba(255,255,255,.65);

    font-size: 10px;

}


/* =====================================
   MENU TITLE
===================================== */

.menu-title {

    padding: 18px 20px 8px;

    color: rgba(255,255,255,.45);

    font-size: 10px;

    font-weight: 600;

    text-transform: uppercase;

}


/* =====================================
   NAV MENU
===================================== */

.nav-menu {

    padding: 5px 12px 15px;

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

    transition: .2s ease;

    position: relative;

}

.nav-link i {

    width: 20px;

    min-width: 20px;

    font-size: 16px;

    text-align: center;

}

.nav-link:hover {

    color: white;

    background: rgba(255,255,255,.10);

}

.nav-link.active {

    color: white;

    background: #0d6efd;

}


/* =====================================
   LOGOUT
===================================== */

.logout {

    position: absolute;

    bottom: 15px;

    left: 12px;

    right: 12px;

}

.logout a {

    color: #ffdede;

    background:
        rgba(220,53,69,.12);

}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 768px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

    }

    .brand {

        height: 70px;

        padding: 10px 15px;

    }

    .brand img {

        width: 43px;

        height: 43px;

    }

    .menu-title {

        display: none;

    }

    .nav-menu {

        display: flex;

        gap: 4px;

        padding: 9px;

        overflow-x: auto;

    }

    .nav-link {

        flex: 0 0 auto;

        width: auto;

        padding: 9px 11px;

        margin: 0;

        font-size: 11px;

        white-space: nowrap;

    }

    .main {

        margin-left: 0;

        padding: 18px 12px 30px;

    }

    .page-header h2 {

        font-size: 21px;

    }

    .page-header p {

        font-size: 11px;

    }

    .card-header-custom {

        padding: 17px;

    }

    .notification-item {

        padding: 17px;

    }

}

</style>

</head>


<body>


<!-- =====================================
     SIDEBAR
===================================== -->

<div class="sidebar">


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


        <!-- NOTIFICATIONS -->

        <a
            href="notifications.php"
            class="nav-link active"
        >

            <i class="bi bi-bell-fill"></i>

            <span>
                Notifications
            </span>

        </a>


        <!-- ANNOUNCEMENTS -->

        <a
            href="announcements.php"
            class="nav-link"
        >

            <i class="bi bi-megaphone"></i>

            <span>
                Announcements
            </span>

        </a>


    </div>


    <!-- LOGOUT -->

    <div class="logout">

        <a
            href="../logout.php"
            class="nav-link"
        >

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


    <!-- PAGE HEADER -->

    <div class="page-header">

        <h2>

            <i class="bi bi-bell me-2"></i>

            Notifications

        </h2>

        <p>

            View notifications related to your guidance and counseling services.

        </p>

    </div>


    <!-- NOTIFICATION CARD -->

    <div class="notification-card">


        <!-- CARD HEADER -->

        <div class="card-header-custom">

            <h5>
                Your Notifications
            </h5>

            <?php if ($total_notifications > 0): ?>

                <span class="notification-count">

                    <?= $total_notifications; ?>

                    Notifications

                </span>

            <?php endif; ?>

        </div>


        <!-- =====================================
             NOTIFICATIONS
        ===================================== -->

        <?php if ($total_notifications > 0): ?>


            <?php while ($row = $result->fetch_assoc()): ?>


                <!-- READ NOTIFICATION -->

                <div class="notification-item read">


                    <!-- ICON -->

                    <div class="notification-icon">

                        <?php

                        if ($row['type'] === 'appointment') {

                            echo '<i class="bi bi-calendar-check-fill"></i>';

                        } elseif ($row['type'] === 'guidance') {

                            echo '<i class="bi bi-chat-dots-fill"></i>';

                        } elseif ($row['type'] === 'announcement') {

                            echo '<i class="bi bi-megaphone-fill"></i>';

                        } else {

                            echo '<i class="bi bi-bell-fill"></i>';

                        }

                        ?>

                    </div>


                    <!-- CONTENT -->

                    <div class="notification-content">

                        <div class="notification-message">

                            <?= nl2br(
                                htmlspecialchars(
                                    $row['message']
                                )
                            ); ?>

                        </div>


                        <div class="notification-date">

                            <i class="bi bi-clock me-1"></i>

                            <?= htmlspecialchars(
                                date(
                                    "M d, Y h:i A",
                                    strtotime(
                                        $row['created_at']
                                    )
                                )
                            ); ?>

                        </div>

                    </div>


                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- EMPTY -->

            <div class="empty">

                <div class="empty-icon">

                    <i class="bi bi-bell-slash"></i>

                </div>


                <h5>
                    No Notifications
                </h5>


                <p>
                    You don't have any notifications yet.
                </p>

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>

<?php

$stmt->close();

?>