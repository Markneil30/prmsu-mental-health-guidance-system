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


// =====================================
// GET LOGGED-IN USER ID
// =====================================

$user_id = (int) $_SESSION['user_id'];


// =====================================
// GET ACTUAL COUNSELOR ID
// users.id != counselors.counselor_id
// =====================================

$counselor_stmt = mysqli_prepare(
    $conn,
    "SELECT counselor_id
     FROM counselors
     WHERE user_id = ?
     LIMIT 1"
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

$counselor_result =
    mysqli_stmt_get_result(
        $counselor_stmt
    );

$counselor_data =
    mysqli_fetch_assoc(
        $counselor_result
    );

mysqli_stmt_close(
    $counselor_stmt
);


if (!$counselor_data) {

    die(
        "Counselor account is not properly linked to the counselors table."
    );

}

$counselor_id =
    (int) $counselor_data['counselor_id'];


// =====================================
// GET COUNSELING RECORDS
// =====================================

$query = mysqli_query($conn, "

    SELECT
        counseling_records.*,
        users.fullname,
        users.email,
        students.student_number

    FROM counseling_records

    INNER JOIN students
        ON counseling_records.student_id =
           students.student_id

    INNER JOIN users
        ON students.user_id =
           users.id

    WHERE counseling_records.counselor_id =
          $counselor_id

    ORDER BY counseling_records.created_at DESC

");


// =====================================
// CHECK QUERY ERROR
// =====================================

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

<title>Counseling Records | PRMSU Guidance</title>

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
    min-height: 100%;

}

body {

    background:
        linear-gradient(
            rgba(244, 247, 251, .94),
            rgba(244, 247, 251, .94)
        ),
        url("../assets/images/dashboard-bg.jpg");

    background-size: cover;

    background-position: center;

    background-repeat: no-repeat;

    background-attachment: fixed;

    color: #1f2937;

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

    transition: .2s;

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

.brand-mini span {

    font-size: 15px;

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

    margin-bottom: 25px;

}

.page-title {

    font-size: 25px;

    font-weight: 700;

    margin: 0;

    color: #172033;

}

.page-subtitle {

    margin:
        5px 0 0;

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

    padding:
        8px 14px;

    border-radius: 10px;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,.04);

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
   RECORD CARD
===================================== */

.record-card {

    background:
        rgba(255,255,255,.97);

    border-radius: 14px;

    overflow: hidden;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,.06);

    border:
        1px solid
        rgba(233,238,245,.9);

}


/* =====================================
   RECORD HEADER
===================================== */

.record-header {

    background:
        linear-gradient(
            135deg,
            #002147,
            #0d6efd
        );

    color: white;

    padding:
        18px 22px;

    display: flex;

    align-items: center;

    justify-content:
        space-between;

    gap: 15px;

}

.record-header h4 {

    margin: 0;

    font-size: 18px;

    font-weight: 600;

}

.add-record-btn {

    background: white;

    color: #0d6efd;

    border: none;

    font-size: 12px;

    font-weight: 600;

    padding:
        8px 13px;

    border-radius: 7px;

    text-decoration: none;

}

.add-record-btn:hover {

    background: #f1f5f9;

    color: #002147;

}


/* =====================================
   RECORD BODY
===================================== */

.record-body {

    padding: 20px;

}


/* =====================================
   TABLE
===================================== */

.table-wrapper {

    width: 100%;

    overflow-x: auto;

    -webkit-overflow-scrolling: touch;

}

.record-table {

    width: 100%;

    min-width: 820px;

    margin: 0;

    font-size: 13px;

}

.record-table thead th {

    background: #002147;

    color: white;

    border-color: #002147;

    padding:
        13px 11px;

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;

}

.record-table tbody td {

    padding:
        12px 11px;

    vertical-align: middle;

    white-space: nowrap;

}

.record-table tbody tr:hover {

    background: #f7faff;

}


/* =====================================
   STUDENT NAME
===================================== */

.student-name {

    font-weight: 600;

    color: #1f2937;

}


/* =====================================
   ACTIONS
===================================== */

.action-buttons {

    display: flex;

    align-items: center;

    gap: 5px;

}

.action-buttons .btn {

    font-size: 11px;

    padding:
        6px 9px;

    border-radius: 6px;

}


/* =====================================
   EMPTY STATE
===================================== */

.empty-state {

    text-align: center;

    padding:
        55px 20px !important;

    color: #6c757d;

}

.empty-state i {

    display: block;

    font-size: 40px;

    margin-bottom: 12px;

    color: #9aa6b2;

}

.empty-state strong {

    display: block;

    color: #495057;

    margin-bottom: 4px;

}


/* =====================================
   BACK BUTTON
===================================== */

.back-btn {

    margin-top: 18px;

    font-size: 12px;

    padding:
        7px 13px;

    border-radius: 7px;

}


/* =====================================
   DELETE MODAL
===================================== */

.delete-modal {

    border: none;

    border-radius: 14px;

    overflow: hidden;

    box-shadow:
        0 15px 40px
        rgba(0,0,0,.18);

}


.delete-modal .modal-header {

    border-bottom:
        1px solid #edf0f4;

    padding:
        16px 20px;

}


.delete-modal .modal-title {

    font-size: 16px;

    font-weight: 600;

    color: #1f2937;

}


.delete-modal .modal-body {

    padding:
        25px 25px 20px;

}


.delete-icon {

    width: 64px;

    height: 64px;

    margin:
        0 auto 15px;

    border-radius: 50%;

    background: #fdecec;

    color: #dc3545;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 27px;

}


.delete-title {

    font-size: 18px;

    font-weight: 600;

    color: #1f2937;

    margin-bottom: 8px;

}


.delete-message {

    font-size: 13px;

    color: #6c757d;

    margin-bottom: 5px;

}


.delete-message strong {

    color: #1f2937;

}


.delete-warning {

    font-size: 12px;

    color: #dc3545;

    margin: 0;

}


.delete-modal .modal-footer {

    border-top:
        1px solid #edf0f4;

    padding:
        12px 20px;

}


.delete-modal .modal-footer .btn {

    font-size: 12px;

    padding:
        7px 13px;

    border-radius: 7px;

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

    .main {

        margin-left: 0;

        padding:
            20px 16px;

    }

    .mobile-navbar {

        display: flex;

        align-items: center;

        justify-content:
            space-between;

    }

    .user-box {

        display: none;

    }

}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 768px) {

    .main {

        padding:
            16px 12px;

    }

    .topbar {

        margin-bottom: 18px;

    }

    .page-title {

        font-size: 20px;

    }

    .page-subtitle {

        font-size: 12px;

    }

    .record-header {

        padding:
            14px 15px;

    }

    .record-header h4 {

        font-size: 16px;

    }

    .add-record-btn {

        font-size: 11px;

        padding:
            7px 10px;

    }

    .record-body {

        padding: 12px;

    }

    .record-table {

        min-width: 820px;

    }

}


/* =====================================
   SMALL PHONE
===================================== */

@media (max-width: 480px) {

    .mobile-navbar {

        padding:
            10px 12px;

    }

    .brand-mini img {

        width: 32px;
        height: 32px;

    }

    .brand-mini span {

        font-size: 14px;

    }

    .main {

        padding:
            12px 8px;

    }

    .page-title {

        font-size: 18px;

    }

    .page-subtitle {

        font-size: 11px;

    }

    .record-card {

        border-radius: 10px;

    }

    .record-header {

        padding:
            12px;

    }

    .record-header h4 {

        font-size: 15px;

    }

    .record-body {

        padding: 10px;

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
        class="btn btn-outline-light btn-sm"
        type="button"
        onclick="toggleSidebar()"
    >

        <i class="bi bi-list fs-5"></i>

    </button>

</div>



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

            <span>
                Dashboard
            </span>

        </a>


        <a href="appointment_requests.php">

            <i class="bi bi-calendar-check"></i>

            <span>
                Appointment Requests
            </span>

        </a>


        <a href="guidance_requests.php">

            <i class="bi bi-chat-left-text-fill"></i>

            <span>
                Guidance Requests
            </span>

        </a>


        <a
            href="records.php"
            class="active"
        >

            <i class="bi bi-folder2-open"></i>

            <span>
                Counseling Records
            </span>

        </a>


        <a href="announcements.php">

            <i class="bi bi-megaphone-fill"></i>

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

        <div>

            <h2 class="page-title">
                Counseling Records
            </h2>

            <p class="page-subtitle">
                Manage and review student counseling records
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



    <!-- RECORD CARD -->

    <div class="record-card">


        <!-- HEADER -->

        <div class="record-header">

            <h4>

                <i class="bi bi-folder2-open me-1"></i>

                Counseling Records

            </h4>


            <a
                href="add_record.php"
                class="add-record-btn"
            >

                <i class="bi bi-plus-circle me-1"></i>

                Add Record

            </a>

        </div>



        <!-- BODY -->

        <div class="record-body">


            <div class="table-wrapper">

                <table class="table record-table">


                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Student
                            </th>

                            <th>
                                Student No.
                            </th>

                            <th>
                                Session Date
                            </th>

                            <th>
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


                            <td>
                                <?= $count++; ?>
                            </td>


                            <td>

                                <span class="student-name">

                                    <?= htmlspecialchars(
                                        $row['fullname'] ?? ''
                                    ); ?>

                                </span>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row['student_number'] ?? ''
                                ); ?>

                            </td>


                            <td>

                                <?php

                                if (
                                    !empty(
                                        $row['session_date']
                                    )
                                ) {

                                    echo htmlspecialchars(
                                        date(
                                            "M d, Y",
                                            strtotime(
                                                $row['session_date']
                                            )
                                        )
                                    );

                                } else {

                                    echo "—";

                                }

                                ?>

                            </td>


                            <td>

                                <div class="action-buttons">


                                    <!-- VIEW -->

                                    <a
                                        href="view_record.php?id=<?= (int)$row['record_id']; ?>"
                                        class="btn btn-info btn-sm text-white"
                                    >

                                        <i class="bi bi-eye"></i>

                                        View

                                    </a>


                                    <!-- EDIT -->

                                    <a
                                        href="edit_record.php?id=<?= (int)$row['record_id']; ?>"
                                        class="btn btn-warning btn-sm"
                                    >

                                        <i class="bi bi-pencil"></i>

                                        Edit

                                    </a>


                                    <!-- DELETE -->

                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
                                        data-record-id="<?= (int)$row['record_id']; ?>"
                                        data-student-name="<?= htmlspecialchars(
                                            $row['fullname'] ?? '',
                                            ENT_QUOTES
                                        ); ?>"
                                    >

                                        <i class="bi bi-trash"></i>

                                        Delete

                                    </button>


                                </div>

                            </td>


                        </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <tr>

                            <td
                                colspan="5"
                                class="empty-state"
                            >

                                <i class="bi bi-folder2-open"></i>

                                <strong>
                                    No counseling records found
                                </strong>

                                <span>
                                    Counseling records will appear here after they are added.
                                </span>

                            </td>

                        </tr>


                    <?php

                    }

                    ?>


                    </tbody>

                </table>

            </div>



            <a
                href="dashboard.php"
                class="btn btn-secondary back-btn"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Back to Dashboard

            </a>


        </div>

    </div>

</div>



<!-- =====================================
     DELETE CONFIRMATION MODAL
===================================== -->

<div
    class="modal fade"
    id="deleteModal"
    tabindex="-1"
    aria-labelledby="deleteModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content delete-modal">


            <!-- HEADER -->

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="deleteModalLabel"
                >

                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>

                    Delete Counseling Record

                </h5>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <!-- BODY -->

            <div class="modal-body text-center">

                <div class="delete-icon">

                    <i class="bi bi-trash3-fill"></i>

                </div>


                <h6 class="delete-title">

                    Are you sure?

                </h6>


                <p class="delete-message">

                    You are about to delete the counseling record of

                    <strong id="deleteStudentName">
                        this student
                    </strong>.

                </p>


                <p class="delete-warning">

                    This action cannot be undone.

                </p>

            </div>


            <!-- FOOTER -->

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >

                    <i class="bi bi-x-circle me-1"></i>

                    Cancel

                </button>


                <a
                    href="#"
                    id="confirmDeleteBtn"
                    class="btn btn-danger"
                >

                    <i class="bi bi-trash me-1"></i>

                    Delete Record

                </a>

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

// =====================================
// SIDEBAR
// =====================================

function toggleSidebar() {

    const sidebar =
        document.getElementById("sidebarMenu");

    sidebar.classList.toggle("show");

}


// =====================================
// DELETE MODAL
// =====================================

const deleteModal =
    document.getElementById("deleteModal");

const confirmDeleteBtn =
    document.getElementById("confirmDeleteBtn");

const deleteStudentName =
    document.getElementById("deleteStudentName");


deleteModal.addEventListener(
    "show.bs.modal",
    function (event) {

        const button =
            event.relatedTarget;


        const recordId =
            button.getAttribute(
                "data-record-id"
            );


        const studentName =
            button.getAttribute(
                "data-student-name"
            );


        // Show student name

        deleteStudentName.textContent =
            studentName || "this student";


        // Set delete URL

        confirmDeleteBtn.href =
            "delete_record.php?id=" +
            encodeURIComponent(recordId);

    }
);

</script>


</body>

</html>

<?php

mysqli_free_result($query);

?>