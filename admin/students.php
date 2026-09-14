
<?php

session_start();

include("../includes/db.php");


/* =====================================
   CHECK ADMIN SESSION
===================================== */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: login.php");
    exit();
}


/* =====================================
   SEARCH
===================================== */

$search = "";
$query = false;

if (isset($_GET['search'])) {

    $search = trim($_GET['search']);

    $search_safe = mysqli_real_escape_string(
        $conn,
        $search
    );

    $sql = "
        SELECT
            users.id,
            users.fullname,
            users.email,
            students.student_number
        FROM users
        INNER JOIN students
            ON users.id = students.user_id
        WHERE users.role = 'student'
        AND (
            users.fullname LIKE '%$search_safe%'
            OR users.email LIKE '%$search_safe%'
            OR students.student_number LIKE '%$search_safe%'
        )
        ORDER BY users.fullname ASC
    ";

} else {

    $sql = "
        SELECT
            users.id,
            users.fullname,
            users.email,
            students.student_number
        FROM users
        INNER JOIN students
            ON users.id = students.user_id
        WHERE users.role = 'student'
        ORDER BY users.fullname ASC
    ";
}


$query = mysqli_query($conn, $sql);


/* =====================================
   CHECK QUERY ERROR
===================================== */

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

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Student Management | PRMSU Guidance</title>

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

html,
body {
    width: 100%;
    min-height: 100%;
}

body {

    background:
        linear-gradient(
            rgba(244,247,251,.92),
            rgba(244,247,251,.92)
        ),
        url("../assets/images/dashboard-bg.jpg");

    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;

    font-family: 'Poppins', sans-serif;

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
        4px 0 18px
        rgba(0,0,0,.08);

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
        rgba(255,255,255,.20);

    color: white;

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

}

.brand-text {

    line-height: 1.2;

}

.brand-title {

    font-size: 16px;

    font-weight: 700;

    margin: 0;

    color: white;

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

    transition:
        background .2s ease,
        color .2s ease,
        transform .2s ease;

}

.sidebar a i {

    width: 20px;

    text-align: center;

    font-size: 17px;

}

.sidebar a:hover {

    background:
        rgba(255,255,255,.12);

    color: white;

    transform:
        translateX(3px);

}

.sidebar a.active {

    background: #0d6efd;

    color: white;

    box-shadow:
        0 4px 12px
        rgba(13,110,253,.3);

}


/* =====================================
   LOGOUT
===================================== */

.logout {

    position: static;

    padding:
        0 12px 20px;

    margin-top: auto;

    flex-shrink: 0;

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
   MOBILE NAVBAR
===================================== */

.mobile-navbar {

    display: none;

    width: 100%;

    min-height: 62px;

    background: #002147;

    color: white;

    padding:
        10px 14px;

    position: fixed;

    top: 0;
    left: 0;

    z-index: 1040;

    box-shadow:
        0 2px 10px
        rgba(0,0,0,.12);

    align-items: center;

    justify-content: flex-start;

}

.brand-mini {

    display: flex;

    align-items: center;

    gap: 10px;

    flex: 1;

    min-width: 0;

}

.mobile-navbar img {

    width: 36px;
    height: 36px;

    object-fit: contain;

    flex-shrink: 0;

}

.brand-mini span {

    font-size: 15px;

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
   MAIN
===================================== */

.main {

    margin-left: 260px;

    min-height: 100vh;

    padding:
        30px 32px;

}


/* =====================================
   TOPBAR
===================================== */

.topbar {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 25px;

}

.page-title {

    min-width: 0;

}

.page-title h2 {

    margin: 0;

    font-size: 24px;

    font-weight: 700;

    color: #172033;

    line-height: 1.3;

}

.page-title p {

    margin: 4px 0 0;

    font-size: 13px;

    color: #718096;

    line-height: 1.5;

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

    padding: 8px 14px;

    border-radius: 10px;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,.04);

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

}


/* =====================================
   CONTENT CARD
===================================== */

.student-card {

    width: 100%;

    background:
        rgba(255,255,255,.95);

    border: 1px solid #e9eef5;

    border-radius: 14px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,.05);

    overflow: hidden;

}


/* =====================================
   CARD HEADER
===================================== */

.card-header-custom {

    padding: 18px 22px;

    background:
        linear-gradient(
            135deg,
            #002147,
            #0d6efd
        );

    color: white;

    display: flex;

    align-items: center;

    justify-content:
        space-between;

    gap: 15px;

}

.card-header-custom h4 {

    margin: 0;

    font-size: 18px;

    font-weight: 600;

    line-height: 1.3;

}


/* =====================================
   CARD BODY
===================================== */

.card-content {

    padding: 22px;

}


/* =====================================
   SEARCH
===================================== */

.search-area {

    display: flex;

    gap: 10px;

    margin-bottom: 20px;

}

.search-area .form-control {

    border-radius: 8px;

    font-size: 13px;

    min-height: 40px;

}

.search-area .btn {

    font-size: 13px;

    padding:
        8px 16px;

    white-space: nowrap;

}


/* =====================================
   TABLE
===================================== */

.table-wrapper {

    width: 100%;

    overflow-x: auto;

    -webkit-overflow-scrolling:
        touch;

    border-radius: 6px;

}

.student-table {

    width: 100%;

    min-width: 750px;

    margin: 0;

    font-size: 13px;

}

.student-table th {

    padding: 12px 10px;

    font-size: 12px;

    white-space: nowrap;

    vertical-align: middle;

}

.student-table td {

    padding: 11px 10px;

    vertical-align: middle;

    white-space: nowrap;

}


/* =====================================
   ACTION
===================================== */

.action-buttons {

    display: flex;

    gap: 5px;

    white-space: nowrap;

}

.action-buttons .btn {

    font-size: 11px;

    padding:
        5px 9px;

}


/* =====================================
   EMPTY
===================================== */

.empty-state {

    text-align: center;

    padding:
        50px 20px !important;

    color: #6c757d;

}

.empty-state i {

    display: block;

    font-size: 35px;

    margin-bottom: 10px;

}


/* =====================================
   TABLET / MOBILE
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

        /* IMPORTANT:
           Bootstrap controls the
           offcanvas transform.
        */

    }

    .sidebar-close {

        display: flex;

    }

    .main {

        margin-left: 0;

        padding:
            82px 20px 25px;

    }

    .user-box {

        display: none;

    }

    .topbar {

        margin-bottom: 22px;

    }

    .page-title h2 {

        font-size: 23px;

    }

    .page-title p {

        font-size: 12.5px;

    }

    .card-header-custom {

        padding:
            17px 20px;

    }

    .card-header-custom h4 {

        font-size: 17px;

    }

}


/* =====================================
   TABLET
===================================== */

@media (max-width: 768px) {

    .mobile-navbar {

        min-height: 60px;

        padding:
            9px 12px;

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

        padding:
            78px 14px 20px;

    }

    .topbar {

        margin-bottom: 18px;

    }

    .page-title h2 {

        font-size: 21px;

    }

    .page-title p {

        font-size: 11.5px;

    }

    .card-header-custom {

        padding:
            15px 16px;

    }

    .card-header-custom h4 {

        font-size: 16px;

    }

    .card-content {

        padding: 16px;

    }

    .search-area {

        flex-direction: column;

        gap: 8px;

    }

    .search-area .form-control {

        width: 100%;

    }

    .search-area .btn {

        width: 100%;

    }

}


/* =====================================
   SMALL PHONE
===================================== */

@media (max-width: 480px) {

    .mobile-navbar {

        min-height: 58px;

        padding:
            8px 10px;

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

        padding:
            72px 9px 16px;

    }

    .page-title h2 {

        font-size: 19px;

    }

    .page-title p {

        font-size: 10.5px;

    }

    .card-header-custom {

        padding:
            13px 12px;

        gap: 10px;

    }

    .card-header-custom h4 {

        font-size: 15px;

    }

    .card-content {

        padding: 11px;

    }

    .student-table {

        min-width: 700px;

        font-size: 12px;

    }

    .student-table th {

        font-size: 10.5px;

        padding: 8px;

    }

    .student-table td {

        padding: 8px;

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

        padding:
            70px 7px 14px;

    }

    .page-title h2 {

        font-size: 18px;

    }

    .page-title p {

        font-size: 10px;

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


        <a href="dashboard.php">

            <i class="bi bi-speedometer2"></i>

            Dashboard

        </a>


        <a
            href="students.php"
            class="active"
        >

            <i class="bi bi-people-fill"></i>

            Student Management

        </a>


        <a href="counselors.php">

            <i class="bi bi-person-badge-fill"></i>

            Counselor Management

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

        <div class="page-title">

            <h2>
                Student Management
            </h2>

            <p>
                Manage registered students in the PRMSU Guidance System.
            </p>

        </div>


        <div class="user-box">

            <div class="user-icon">

                <i class="bi bi-person-fill"></i>

            </div>

            <div class="user-name">

                <?= htmlspecialchars(
                    $_SESSION['fullname'] ?? 'Administrator',
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>

            </div>

        </div>

    </div>


    <!-- STUDENT CARD -->

    <div class="student-card">


        <!-- HEADER -->

        <div class="card-header-custom">

            <h4>

                <i class="bi bi-people-fill me-2"></i>

                Students

            </h4>


            <a
                href="add_student.php"
                class="btn btn-light btn-sm"
            >

                <i class="bi bi-person-plus-fill me-1"></i>

                Add Student

            </a>

        </div>


        <!-- CONTENT -->

        <div class="card-content">


            <!-- SEARCH -->

            <form
                method="GET"
                class="search-area"
            >

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search student name, email, or student number..."
                    value="<?= htmlspecialchars(
                        $search,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>"
                >


                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i class="bi bi-search me-1"></i>

                    Search

                </button>


                <?php if ($search !== "") { ?>

                    <a
                        href="students.php"
                        class="btn btn-secondary"
                    >

                        <i class="bi bi-x-circle me-1"></i>

                        Clear

                    </a>

                <?php } ?>

            </form>


            <!-- TABLE -->

            <div class="table-wrapper">

                <table
                    class="table table-bordered table-hover student-table"
                >

                    <thead class="table-dark">

                        <tr>

                            <th>
                                Student No.
                            </th>

                            <th>
                                Full Name
                            </th>

                            <th>
                                Email
                            </th>

                            <th width="130">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php

                    if (
                        mysqli_num_rows($query) > 0
                    ) {

                        while (
                            $row =
                            mysqli_fetch_assoc($query)
                        ) {

                    ?>

                        <tr>

                            <td>

                                <?= htmlspecialchars(
                                    $row['student_number'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row['fullname'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row['email'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </td>


                            <td>

                                <div class="action-buttons">


                                    <!-- EDIT -->

                                    <a
                                        href="edit_student.php?id=<?= (int)$row['id']; ?>"
                                        class="btn btn-warning btn-sm"
                                        title="Edit Student"
                                    >

                                        <i class="bi bi-pencil"></i>

                                    </a>


                                    <!-- DELETE -->

                                    <a
                                        href="delete_student.php?id=<?= (int)$row['id']; ?>"
                                        class="btn btn-danger btn-sm"
                                        title="Delete Student"
                                        onclick="return confirm('Delete this student?');"
                                    >

                                        <i class="bi bi-trash"></i>

                                    </a>


                                </div>

                            </td>

                        </tr>

                    <?php

                        }

                    } else {

                    ?>

                        <tr>

                            <td
                                colspan="4"
                                class="empty-state"
                            >

                                <i class="bi bi-people"></i>

                                No students found.

                            </td>

                        </tr>

                    <?php

                    }

                    ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<!-- BOOTSTRAP JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
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

