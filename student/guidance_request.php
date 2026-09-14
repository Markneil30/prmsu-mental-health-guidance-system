
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
    header("Location: login.php");
    exit();
}


// =====================================
// CURRENT USER
// =====================================

$user_id = (int) $_SESSION['user_id'];


// =====================================
// GET STUDENT INFORMATION
// =====================================

$stmt = $conn->prepare("
    SELECT
        students.student_id,
        users.id AS user_id,
        users.fullname,
        users.email,
        students.student_number
    FROM students
    INNER JOIN users
        ON students.user_id = users.id
    WHERE users.id = ?
");

if (!$stmt) {
    die("Database Error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    die("Student record not found.");
}

$student = $result->fetch_assoc();

$student_id = (int) $student['student_id'];

$stmt->close();


// =====================================
// SUBMIT GUIDANCE REQUEST
// =====================================

$message = "";

if (isset($_POST['submit'])) {

    $subject = isset($_POST['subject'])
        ? trim($_POST['subject'])
        : "";

    $message_text = isset($_POST['message'])
        ? trim($_POST['message'])
        : "";


    // =====================================
    // VALIDATION
    // =====================================

    if ($subject === "") {

        $message = "
            <div class='alert alert-danger'>
                <i class='bi bi-exclamation-circle-fill me-2'></i>
                Please enter your concern or subject.
            </div>
        ";

    } else {

        $status = "Pending";


        // =====================================
        // INSERT GUIDANCE REQUEST
        // =====================================

        $stmt = $conn->prepare("
            INSERT INTO guidance_requests
            (
                student_id,
                subject,
                message,
                status
            )
            VALUES (?, ?, ?, ?)
        ");

        if (!$stmt) {

            $message = "
                <div class='alert alert-danger'>
                    <i class='bi bi-exclamation-circle-fill me-2'></i>
                    Failed to prepare request.
                    <br>
                    " . htmlspecialchars($conn->error) . "
                </div>
            ";

        } else {

            $stmt->bind_param(
                "isss",
                $student_id,
                $subject,
                $message_text,
                $status
            );


            if ($stmt->execute()) {

                $message = "
                    <div class='alert alert-success'>
                        <i class='bi bi-check-circle-fill me-2'></i>
                        Guidance Request Submitted Successfully.
                    </div>
                ";

                $_POST['subject'] = "";
                $_POST['message'] = "";

            } else {

                $message = "
                    <div class='alert alert-danger'>
                        <i class='bi bi-exclamation-circle-fill me-2'></i>
                        Failed to submit guidance request.
                        <br>
                        " . htmlspecialchars($stmt->error) . "
                    </div>
                ";
            }

            $stmt->close();
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

<title>Guidance Request | PRMSU Guidance</title>

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

html,
body {
    width: 100%;
    min-height: 100%;
}

html {
    overflow-x: hidden;
}

body {
    background: #f4f7fb;

    font-family:
        "Poppins",
        Arial,
        sans-serif;

    color: #1f2937;

    overflow-x: hidden;
    overflow-y: auto;
}


/* =====================================
   SIDEBAR
===================================== */

.sidebar {
    position: fixed;

    top: 0;
    left: 0;

    width: 245px;
    height: 100vh;

    background:
        linear-gradient(
            180deg,
            #002147 0%,
            #073b78 100%
        );

    color: white;

    z-index: 1045;

    box-shadow:
        4px 0 18px
        rgba(0, 0, 0, .08);

    display: flex;
    flex-direction: column;

    overflow-y: auto;
    overflow-x: hidden;

    -webkit-overflow-scrolling: touch;
}

.sidebar::-webkit-scrollbar {
    width: 5px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255,255,255,.05);
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,.25);
    border-radius: 10px;
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

    transition:
        background .2s ease;
}

.sidebar-close:hover {
    background:
        rgba(255,255,255,.20);
}


/* =====================================
   MOBILE HEADER
===================================== */

.mobile-header {
    display: none;

    position: fixed;

    top: 0;
    left: 0;

    width: 100%;

    min-height: 62px;

    z-index: 1040;

    background: #002147;

    color: white;

    padding: 10px 15px;

    align-items: center;

    box-shadow:
        0 2px 10px
        rgba(0,0,0,.12);
}

.mobile-brand {
    display: flex;

    align-items: center;

    gap: 10px;

    flex: 1;

    min-width: 0;
}

.mobile-brand img {
    width: 34px;
    height: 34px;

    object-fit: contain;

    flex-shrink: 0;
}

.mobile-brand span {
    font-size: 15px;

    font-weight: 600;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.toggle-btn {
    width: 40px;
    height: 40px;

    padding: 0;

    margin-left: auto;

    border:
        1px solid
        rgba(255,255,255,.30);

    border-radius: 8px;

    background: transparent;

    color: white;

    font-size: 22px;

    cursor: pointer;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

    transition:
        background .2s ease,
        border-color .2s ease;
}

.toggle-btn:hover {
    background:
        rgba(255,255,255,.10);

    border-color:
        rgba(255,255,255,.50);
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
    width: 52px;
    height: 52px;

    object-fit: contain;
}

.brand-text {
    line-height: 1.15;
}

.brand-title {
    font-size: 17px;

    font-weight: 700;

    margin: 0;

    color: white;
}

.brand-subtitle {
    font-size: 10px;

    opacity: .75;

    margin-top: 4px;
}


/* =====================================
   NAVIGATION
===================================== */

.nav-menu {
    padding: 18px 12px 20px;

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

    color:
        rgba(255,255,255,.88);

    text-decoration: none;

    padding: 11px 13px;

    margin-bottom: 4px;

    border-radius: 8px;

    font-size: 13px;

    transition:
        background .2s ease,
        color .2s ease,
        transform .2s ease;
}

.sidebar a i {
    width: 20px;

    text-align: center;

    font-size: 16px;

    flex-shrink: 0;
}

.sidebar a:hover {
    background:
        rgba(255,255,255,.12);

    color: white;

    transform: translateX(2px);
}

.sidebar a.active {
    background: #0d6efd;

    color: white;
}


/* =====================================
   LOGOUT
===================================== */

.logout-area {
    padding: 12px;

    border-top:
        1px solid
        rgba(255,255,255,.14);

    flex-shrink: 0;

    margin-top: auto;
}

.logout-btn {
    display: flex !important;

    align-items: center;

    gap: 12px;

    width: 100%;

    padding: 11px 13px !important;

    margin: 0 !important;

    border-radius: 8px;

    color:
        rgba(255,255,255,.88) !important;

    text-decoration: none;

    font-size: 13px;
}

.logout-btn:hover {
    background:
        rgba(220,53,69,.20) !important;

    color: #fff !important;

    transform: none !important;
}


/* =====================================
   MAIN
===================================== */

.main {
    margin-left: 245px;

    min-height: 100vh;

    padding: 30px 32px;

    overflow-x: hidden;
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
    margin-bottom: 24px;
}

.page-header h2 {
    font-size: 24px;

    font-weight: 700;

    margin: 0;

    color: #172033;

    line-height: 1.3;
}

.page-header p {
    margin: 5px 0 0;

    color: #718096;

    font-size: 13px;

    line-height: 1.5;
}


/* =====================================
   FORM CARD
===================================== */

.form-card {
    background: white;

    border:
        1px solid
        #e9eef5;

    border-radius: 14px;

    overflow: hidden;

    box-shadow:
        0 6px 20px
        rgba(0,0,0,.05);
}


/* =====================================
   FORM HEADER
===================================== */

.form-header {
    background:
        linear-gradient(
            135deg,
            #002147,
            #0d6efd
        );

    color: white;

    padding: 22px 25px;
}

.form-header h4 {
    font-size: 18px;

    font-weight: 600;

    margin: 0;

    line-height: 1.35;
}

.form-header p {
    margin: 5px 0 0;

    font-size: 12px;

    color:
        rgba(255,255,255,.82);

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

    font-size: 13px;

    font-weight: 600;

    margin-bottom: 8px;
}


/* =====================================
   INPUT
===================================== */

.form-control {
    width: 100%;

    min-height: 46px;

    border:
        1px solid
        #dbe2ea;

    border-radius: 10px;

    padding: 11px 14px;

    font-family:
        "Poppins",
        Arial,
        sans-serif;

    font-size: 13px;

    color: #334155;
}

.form-control:focus {
    border-color: #2563eb;

    box-shadow:
        0 0 0 3px
        rgba(37,99,235,.10);
}


/* =====================================
   TEXTAREA
===================================== */

textarea.form-control {
    min-height: 150px;

    resize: vertical;

    line-height: 1.6;
}


/* =====================================
   OPTIONAL TEXT
===================================== */

.optional-text {
    font-size: 11px;

    color: #94a3b8;

    font-weight: 400;

    margin-left: 4px;
}


/* =====================================
   BUTTON AREA
===================================== */

.button-area {
    display: flex;

    align-items: center;

    gap: 10px;

    margin-top: 25px;
}


/* =====================================
   SUBMIT BUTTON
===================================== */

.submit-btn {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    background: #0d6efd;

    color: white;

    border: none;

    border-radius: 9px;

    padding: 12px 20px;

    font-family:
        "Poppins",
        Arial,
        sans-serif;

    font-size: 13px;

    font-weight: 600;

    transition:
        background .2s ease,
        transform .2s ease;
}

.submit-btn:hover {
    background: #0b5ed7;

    color: white;

    transform: translateY(-1px);
}


/* =====================================
   BACK BUTTON
===================================== */

.back-btn {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    background: #64748b;

    color: white;

    border: none;

    border-radius: 9px;

    padding: 12px 20px;

    font-family:
        "Poppins",
        Arial,
        sans-serif;

    font-size: 13px;

    font-weight: 600;

    text-decoration: none;

    transition:
        background .2s ease,
        transform .2s ease;
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

    font-size: 13px;

    line-height: 1.5;
}


/* =====================================
   DESKTOP OFFCANVAS RESET
===================================== */

@media (min-width: 992px) {

    .sidebar.offcanvas-lg {
        position: fixed;

        visibility: visible !important;

        transform: none !important;

        width: 245px !important;

        height: 100vh !important;

        background:
            linear-gradient(
                180deg,
                #002147 0%,
                #073b78 100%
            );

        border: none;
    }

}


/* =====================================
   TABLET / MOBILE
===================================== */

@media (max-width: 991.98px) {

    body {
        padding-top: 62px;
    }

    .mobile-header {
        display: flex;
    }

    .sidebar {
        width: 270px !important;

        max-width: 85vw;

        height: 100vh !important;

        background:
            linear-gradient(
                180deg,
                #002147 0%,
                #073b78 100%
            );

        border: none;
    }

    .sidebar-close {
        display: flex;
    }

    .brand {
        height: 78px;

        padding:
            12px 55px 12px 20px;
    }

    .main {
        margin-left: 0;

        width: 100%;

        min-height:
            calc(100vh - 62px);

        padding:
            25px 20px 35px;
    }

    .form-container {
        max-width: 100%;
    }

}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 767.98px) {

    .mobile-header {
        padding:
            10px 14px;
    }

    .mobile-brand span {
        font-size: 14px;
    }

    .toggle-btn {
        width: 40px;
        height: 40px;

        font-size: 21px;
    }

    .main {
        padding:
            22px 15px 30px;
    }

    .page-header {
        margin-bottom: 18px;
    }

    .page-header h2 {
        font-size: 21px;
    }

    .page-header p {
        font-size: 11.5px;
    }

    .form-card {
        border-radius: 12px;
    }

    .form-header {
        padding: 18px;
    }

    .form-header h4 {
        font-size: 17px;
    }

    .form-header p {
        font-size: 11px;
    }

    .form-body {
        padding:
            22px 17px;
    }

    .form-label {
        font-size: 12px;
    }

    .form-control {
        min-height: 45px;

        padding:
            11px 12px;

        font-size: 13px;
    }

    textarea.form-control {
        min-height: 140px;
    }

    .button-area {
        flex-direction: column;

        align-items: stretch;

        gap: 10px;

        margin-top: 20px;
    }

    .submit-btn,
    .back-btn {
        width: 100%;

        min-height: 46px;

        font-size: 13px;
    }

    .alert {
        font-size: 12px;
    }

}


/* =====================================
   SMALL MOBILE
===================================== */

@media (max-width: 480px) {

    body {
        padding-top: 60px;
    }

    .mobile-header {
        min-height: 60px;

        padding:
            9px 12px;
    }

    .mobile-brand {
        gap: 8px;
    }

    .mobile-brand img {
        width: 32px;
        height: 32px;
    }

    .mobile-brand span {
        font-size: 13.5px;
    }

    .toggle-btn {
        width: 38px;
        height: 38px;

        font-size: 20px;
    }

    .sidebar {
        width: 250px !important;

        max-width: 84vw;
    }

    .brand {
        height: 70px;

        padding:
            10px 55px 10px 15px;
    }

    .brand img {
        width: 43px;
        height: 43px;
    }

    .brand-title {
        font-size: 15px;
    }

    .brand-subtitle {
        font-size: 9px;
    }

    .nav-menu {
        padding:
            15px 10px 20px;
    }

    .sidebar a {
        padding:
            11px 12px;

        font-size: 12.5px;
    }

    .main {
        padding:
            18px 11px 25px;
    }

    .page-header {
        margin-bottom: 16px;
    }

    .page-header h2 {
        font-size: 19px;
    }

    .page-header p {
        font-size: 10.5px;
    }

    .form-header {
        padding: 16px;
    }

    .form-header h4 {
        font-size: 16px;
    }

    .form-header p {
        font-size: 10.5px;
    }

    .form-body {
        padding:
            19px 14px;
    }

    .form-label {
        font-size: 11.5px;
    }

    .form-control {
        min-height: 44px;

        font-size: 12px;
    }

    textarea.form-control {
        min-height: 130px;
    }

    .submit-btn,
    .back-btn {
        min-height: 44px;

        font-size: 12px;

        padding:
            10px 15px;
    }

}


/* =====================================
   EXTRA SMALL
===================================== */

@media (max-width: 360px) {

    .mobile-brand span {
        font-size: 13px;
    }

    .sidebar {
        width: 240px !important;
    }

    .main {
        padding:
            16px 9px 22px;
    }

    .page-header h2 {
        font-size: 18px;
    }

    .page-header p {
        font-size: 10px;
    }

    .form-header {
        padding: 15px;
    }

    .form-header h4 {
        font-size: 15px;
    }

    .form-header p {
        font-size: 10px;
    }

    .form-body {
        padding:
            17px 12px;
    }

    .form-control {
        font-size: 11.5px;
    }

    textarea.form-control {
        min-height: 120px;
    }

    .submit-btn,
    .back-btn {
        font-size: 11.5px;
    }

}

</style>

</head>


<body>


<!-- =====================================
     MOBILE HEADER
===================================== -->

<div class="mobile-header">

    <div class="mobile-brand">

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
        class="toggle-btn"
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
    class="sidebar offcanvas-lg offcanvas-start"
    tabindex="-1"
    id="sidebarMenu"
    aria-labelledby="sidebarLabel"
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
                id="sidebarLabel"
            >
                PRMSU Guidance
            </p>

            <div class="brand-subtitle">
                Counseling System
            </div>

        </div>

    </div>


    <!-- NAVIGATION -->

    <div class="nav-menu">

        <div class="nav-label">
            Main Menu
        </div>


        <a href="dashboard.php">

            <i class="bi bi-grid-1x2-fill"></i>

            <span>
                Dashboard
            </span>

        </a>


        <a href="request_appointment.php">

            <i class="bi bi-calendar-plus"></i>

            <span>
                Request Appointment
            </span>

        </a>


        <a href="my_appointments.php">

            <i class="bi bi-calendar-check"></i>

            <span>
                My Appointments
            </span>

        </a>


        <a
            href="guidance_request.php"
            class="active"
        >

            <i class="bi bi-chat-left-text-fill"></i>

            <span>
                Guidance Request
            </span>

        </a>


        <a href="my_requests.php">

            <i class="bi bi-file-earmark-text"></i>

            <span>
                My Guidance Requests
            </span>

        </a>


        <a href="announcements.php">

            <i class="bi bi-megaphone"></i>

            <span>
                Announcements
            </span>

        </a>

    </div>


    <!-- LOGOUT -->

    <div class="logout-area">

        <a
            href="../logout.php"
            class="logout-btn"
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
===================================== -->

<div class="main">

    <div class="form-container">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <h2>
                Guidance Request
            </h2>

            <p>
                Submit a concern or request to the Guidance Office.
            </p>

        </div>


        <!-- FORM CARD -->

        <div class="form-card">


            <!-- FORM HEADER -->

            <div class="form-header">

                <h4>

                    <i
                        class="bi bi-chat-left-text-fill me-2"
                    ></i>

                    Guidance Request

                </h4>

                <p>
                    Please provide the details of your concern.
                </p>

            </div>


            <!-- FORM BODY -->

            <div class="form-body">

                <?= $message; ?>


                <form method="POST">


                    <!-- SUBJECT -->

                    <div class="mb-4">

                        <label
                            class="form-label"
                            for="subject"
                        >

                            Subject

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <input
                            type="text"
                            name="subject"
                            id="subject"
                            class="form-control"
                            placeholder="Enter your concern"
                            value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>"
                            required
                        >

                    </div>


                    <!-- MESSAGE -->

                    <div class="mb-3">

                        <label
                            class="form-label"
                            for="message"
                        >

                            Message

                            <span class="optional-text">
                                (Optional)
                            </span>

                        </label>


                        <textarea
                            name="message"
                            id="message"
                            rows="6"
                            class="form-control"
                            placeholder="You may provide additional details about your concern..."
                        ><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>

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
     CLOSE SIDEBAR AFTER NAVIGATION
===================================== -->

<script>

document
    .querySelectorAll('#sidebarMenu .nav-link')
    .forEach(function(link) {

        link.addEventListener('click', function() {

            if (window.innerWidth < 992) {

                const sidebar =
                    document.getElementById('sidebarMenu');

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

