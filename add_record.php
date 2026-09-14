<?php

session_start();

include("../includes/db.php");

/* =========================
   COUNSELOR SESSION CHECK
========================= */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'counselor'
) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];


/* =========================
   GET COUNSELOR ID
========================= */

$counselor_stmt = mysqli_prepare(
    $conn,
    "SELECT counselor_id FROM counselors WHERE user_id = ? LIMIT 1"
);

mysqli_stmt_bind_param(
    $counselor_stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($counselor_stmt);

$counselor_result = mysqli_stmt_get_result($counselor_stmt);
$counselor_data = mysqli_fetch_assoc($counselor_result);

mysqli_stmt_close($counselor_stmt);

if (!$counselor_data) {
    die("Counselor account is not properly linked to the counselors table.");
}

$counselor_id = (int) $counselor_data['counselor_id'];


/* =========================
   VARIABLES
========================= */

$error = "";


/* =========================
   SAVE COUNSELING RECORD
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = isset($_POST['student_id'])
        ? (int) $_POST['student_id']
        : 0;

    $session_date = $_POST['session_date'] ?? '';

    $notes = trim($_POST['notes'] ?? '');


    /* =========================
       VALIDATION
    ========================= */

    if ($student_id <= 0) {

        $error = "Please select a student.";

    } elseif (empty($session_date)) {

        $error = "Please select a session date.";

    } elseif (empty($notes)) {

        $error = "Please enter the counseling notes.";

    }


    /* =========================
       CHECK STUDENT
    ========================= */

    if (empty($error)) {

        $check_stmt = mysqli_prepare(
            $conn,
            "SELECT student_id FROM students WHERE student_id = ? LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check_stmt,
            "i",
            $student_id
        );

        mysqli_stmt_execute($check_stmt);

        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) === 0) {
            $error = "Selected student does not exist.";
        }

        mysqli_stmt_close($check_stmt);
    }


    /* =========================
       INSERT COUNSELING RECORD
    ========================= */

    if (empty($error)) {

        $stmt = mysqli_prepare(
            $conn,
            "
            INSERT INTO counseling_records
            (
                student_id,
                counselor_id,
                session_date,
                notes
            )
            VALUES (?, ?, ?, ?)
            "
        );

        if (!$stmt) {

            $error = "Failed to prepare counseling record.";

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "iiss",
                $student_id,
                $counselor_id,
                $session_date,
                $notes
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: records.php?success=record_added");
                exit;

            } else {

                $error = "Failed to save counseling record.";

                mysqli_stmt_close($stmt);
            }
        }
    }
}


/* =========================
   GET STUDENTS
========================= */

$students_query = "
    SELECT
        students.student_id,
        students.student_number,
        students.year_level,
        students.course,
        students.gender,
        users.fullname,
        users.email
    FROM students
    INNER JOIN users
        ON students.user_id = users.id
    WHERE users.role = 'student'
    ORDER BY users.fullname ASC
";

$students_result = mysqli_query($conn, $students_query);

if (!$students_result) {
    die("Failed to load students.");
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

    <title>Add Counseling Record | PRMSU Guidance</title>

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

    <!-- FONT AWESOME -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >

    <style>

        /* =========================
           RESET
        ========================= */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            width: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f9;
            color: #333;
            min-height: 100vh;
            overflow-x: hidden;
        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            width: 250px;
            height: 100vh;
            background: linear-gradient(
                180deg,
                #002147 0%,
                #073b78 100%
            );
            color: white;
            z-index: 1050;
            box-shadow: 4px 0 18px rgba(0,0,0,.08);

            display: flex;
            flex-direction: column;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }


        /* =========================
           SIDEBAR BRAND
        ========================= */

        .sidebar-header {
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255,255,255,.12);
            flex-shrink: 0;
        }

        .sidebar-header img {
            width: 55px;
            height: 55px;
            object-fit: contain;
        }


        /* =========================
           SIDEBAR MENU
        ========================= */

        .sidebar-menu {
            padding: 20px 15px;
            flex: 1;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 13px 15px;
            margin-bottom: 6px;
            text-decoration: none;
            color: rgba(255,255,255,.82);
            border-radius: 8px;
            font-size: 14px;
            transition: background .2s ease,
                        color .2s ease,
                        transform .2s ease;
        }

        .sidebar-menu a i {
            width: 20px;
            min-width: 20px;
            text-align: center;
            font-size: 15px;
        }

        .sidebar-menu a:hover {
            background: rgba(255,255,255,.10);
            color: #ffffff;
            transform: translateX(2px);
        }

        .sidebar-menu a.active {
            background: #0d6efd;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(13,110,253,.20);
        }

        .logout-link {
            color: #ffb4b4 !important;
            margin-top: 10px;
        }

        .logout-link:hover {
            background: rgba(220,53,69,.15) !important;
            color: #ffffff !important;
        }


        /* =========================
           MOBILE NAVBAR
        ========================= */

        .mobile-navbar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 62px;
            background: #002147;
            z-index: 1040;
            padding: 0 14px;
            align-items: center;
        }

        .mobile-brand {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
            flex: 1;
        }

        .mobile-brand img {
            width: 38px;
            height: 38px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .mobile-brand-text {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
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

            border: 1px solid rgba(255,255,255,.25);
            border-radius: 8px;
            background: transparent;
            color: #ffffff;
            font-size: 20px;
        }

        .mobile-menu-btn:hover {
            background: rgba(255,255,255,.10);
            color: #ffffff;
        }


        /* =========================
           SIDEBAR CLOSE BUTTON
        ========================= */

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
            background: rgba(255,255,255,.18);
        }


        /* =========================
           MAIN CONTENT
        ========================= */

        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            padding: 30px;
        }

        .content-wrapper {
            width: 100%;
            max-width: 1100px;
        }


        /* =========================
           PAGE HEADER
        ========================= */

        .page-header {
            margin-bottom: 25px;
        }

        .page-header h1 {
            font-size: 27px;
            color: #002147;
            margin-bottom: 7px;
            font-weight: 700;
        }

        .page-header p {
            color: #777;
            font-size: 14px;
            margin: 0;
        }


        /* =========================
           FORM CARD
        ========================= */

        .form-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 950px;
        }

        .form-section-title {
            font-size: 18px;
            color: #002147;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }


        /* =========================
           FORM
        ========================= */

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #d7d7d7;
            border-radius: 7px;
            font-size: 14px;
            outline: none;
            background: #fff;
            transition: border .2s ease,
                        box-shadow .2s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #073b78;
            box-shadow: 0 0 0 2px rgba(7,59,120,0.08);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 180px;
            line-height: 1.5;
        }


        /* =========================
           STUDENT INFORMATION
        ========================= */

        .student-info {
            display: none;
            background: #f1f5fa;
            border: 1px solid #d8e1ec;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .student-info h3 {
            font-size: 16px;
            color: #002147;
            margin-bottom: 18px;
            font-weight: 600;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .info-item {
            background: #ffffff;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            padding: 14px;
            min-width: 0;
        }

        .info-item span {
            display: block;
            font-size: 12px;
            color: #777;
            margin-bottom: 6px;
        }

        .info-item strong {
            display: block;
            font-size: 14px;
            color: #002147;
            font-weight: 600;
            word-break: break-word;
            overflow-wrap: anywhere;
        }


        /* =========================
           BUTTONS
        ========================= */

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .btn {
            border: none;
            border-radius: 7px;
            padding: 11px 20px;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background .2s ease,
                        transform .2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: #e9ecef;
            color: #333;
        }

        .btn-cancel:hover {
            background: #dfe3e6;
            color: #333;
        }

        .btn-save {
            background: #002147;
            color: #ffffff;
        }

        .btn-save:hover {
            background: #073b78;
            color: #ffffff;
        }


        /* =========================
           ALERTS
        ========================= */

        .alert {
            padding: 13px 15px;
            border-radius: 7px;
            margin-bottom: 20px;
            font-size: 14px;
            max-width: 950px;
        }

        .alert-error {
            background: #fff0f0;
            color: #b42318;
            border: 1px solid #f3c2c2;
        }


        /* =========================
           TABLET
        ========================= */

        @media (max-width: 991.98px) {

            .sidebar {
                width: 250px;
                max-width: 85vw;
            }

            .sidebar-close {
                display: flex;
            }

            .main-content {
                margin-left: 0;
                padding: 25px 20px;
            }

            .mobile-navbar {
                display: flex;
            }

            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .page-header {
                padding-top: 45px;
            }
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 767.98px) {

            body {
                font-size: 14px;
            }

            .main-content {
                padding: 82px 14px 30px;
            }

            .page-header {
                margin-bottom: 18px;
                padding-top: 0;
            }

            .page-header h1 {
                font-size: 23px;
                line-height: 1.3;
            }

            .page-header p {
                font-size: 13px;
                line-height: 1.5;
            }

            .form-card {
                padding: 20px 16px;
                border-radius: 10px;
            }

            .form-section-title {
                font-size: 17px;
                margin-bottom: 18px;
            }

            .form-group {
                margin-bottom: 18px;
            }

            .form-group label {
                font-size: 13px;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                font-size: 14px;
                padding: 11px 12px;
            }

            .form-group textarea {
                min-height: 170px;
            }

            .student-info {
                padding: 16px;
                margin-bottom: 20px;
            }

            .student-info h3 {
                font-size: 15px;
                margin-bottom: 14px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .info-item {
                padding: 12px;
            }

            .info-item span {
                font-size: 11px;
            }

            .info-item strong {
                font-size: 13px;
            }

            .form-actions {
                flex-direction: column;
                gap: 9px;
            }

            .btn {
                width: 100%;
                min-height: 44px;
            }

            .alert {
                font-size: 13px;
                margin-bottom: 16px;
            }
        }


        /* =========================
           SMALL MOBILE
        ========================= */

        @media (max-width: 480px) {

            .mobile-navbar {
                height: 60px;
                padding: 0 11px;
            }

            .mobile-brand img {
                width: 36px;
                height: 36px;
            }

            .mobile-brand-text {
                font-size: 14px;
            }

            .mobile-menu-btn {
                width: 38px;
                height: 38px;
                flex-basis: 38px;
                font-size: 18px;
            }

            .main-content {
                padding: 76px 10px 25px;
            }

            .page-header h1 {
                font-size: 21px;
            }

            .page-header p {
                font-size: 12px;
            }

            .form-card {
                padding: 17px 13px;
            }

            .form-section-title {
                font-size: 16px;
            }

            .student-info {
                padding: 13px;
            }

            .info-item {
                padding: 11px;
            }

            .form-group textarea {
                min-height: 155px;
            }
        }


        /* =========================
           VERY SMALL PHONE
        ========================= */

        @media (max-width: 360px) {

            .mobile-brand-text {
                font-size: 13px;
            }

            .main-content {
                padding-left: 8px;
                padding-right: 8px;
            }

            .page-header h1 {
                font-size: 20px;
            }

            .form-card {
                padding: 15px 11px;
            }

            .form-section-title {
                font-size: 15px;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                font-size: 13px;
            }

            .btn {
                font-size: 13px;
            }
        }

    </style>

</head>

<body>


<!-- =========================
     MOBILE NAVBAR
========================= -->

<div class="mobile-navbar">

    <div class="mobile-brand">

        <img
            src="../assets/images/prmsu-logo.png"
            alt="PRMSU Logo"
        >

        <span class="mobile-brand-text">
            PRMSU Guidance
        </span>

    </div>


    <button
        type="button"
        class="mobile-menu-btn"
        data-bs-toggle="offcanvas"
        data-bs-target="#sidebarMenu"
        aria-controls="sidebarMenu"
        aria-label="Open menu"
    >
        <i class="bi bi-list"></i>
    </button>

</div>



<!-- =========================
     SIDEBAR
========================= -->

<div
    class="sidebar offcanvas-lg offcanvas-start"
    tabindex="-1"
    id="sidebarMenu"
    aria-labelledby="sidebarMenuLabel"
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

    <div class="sidebar-header">

        <img
            src="../assets/images/prmsu-logo.png"
            alt="PRMSU Logo"
        >

    </div>


    <!-- MENU -->

    <div class="sidebar-menu">

        <a href="dashboard.php">

            <i class="fas fa-home"></i>

            <span>Dashboard</span>

        </a>


        <a href="appointment_requests.php">

            <i class="fas fa-calendar-check"></i>

            <span>Appointment Requests</span>

        </a>


        <a href="guidance_requests.php">

            <i class="fas fa-hand-holding-heart"></i>

            <span>Guidance Requests</span>

        </a>


        <a
            href="records.php"
            class="active"
        >

            <i class="fas fa-file-medical"></i>

            <span>Counseling Records</span>

        </a>


        <a href="announcements.php">

            <i class="fas fa-bullhorn"></i>

            <span>Announcements</span>

        </a>


        <a
            href="../logout.php"
            class="logout-link"
        >

            <i class="fas fa-sign-out-alt"></i>

            <span>Logout</span>

        </a>

    </div>

</div>



<!-- =========================
     MAIN CONTENT
========================= -->

<div class="main-content">

    <div class="content-wrapper">

        <!-- PAGE HEADER -->

        <div class="page-header">

            <h1>
                Add Counseling Record
            </h1>

            <p>
                Record the details of a completed counseling session.
            </p>

        </div>


        <!-- ERROR -->

        <?php if (!empty($error)): ?>

            <div class="alert alert-error">

                <i class="fas fa-exclamation-circle"></i>

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <!-- FORM CARD -->

        <div class="form-card">

            <div class="form-section-title">

                Counseling Record

            </div>


            <form
                method="POST"
                action=""
            >


                <!-- =========================
                     SELECT STUDENT
                ========================= -->

                <div class="form-group">

                    <label for="student_id">
                        Select Student
                    </label>

                    <select
                        name="student_id"
                        id="student_id"
                        required
                    >

                        <option value="">
                            Select Student
                        </option>

                        <?php while ($student = mysqli_fetch_assoc($students_result)): ?>

                            <option
                                value="<?= (int) $student['student_id']; ?>"
                                data-number="<?= htmlspecialchars($student['student_number'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-email="<?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-fullname="<?= htmlspecialchars($student['fullname'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-year="<?= htmlspecialchars($student['year_level'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-course="<?= htmlspecialchars($student['course'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-gender="<?= htmlspecialchars($student['gender'], ENT_QUOTES, 'UTF-8'); ?>"
                            >

                                <?= htmlspecialchars($student['fullname']); ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>



                <!-- =========================
                     STUDENT INFORMATION
                ========================= -->

                <div
                    class="student-info"
                    id="studentInfo"
                >

                    <h3>

                        <i class="fas fa-user"></i>

                        Student Information

                    </h3>


                    <div class="info-grid">

                        <div class="info-item">

                            <span>Full Name</span>

                            <strong id="studentFullname">
                                -
                            </strong>

                        </div>


                        <div class="info-item">

                            <span>Student Number</span>

                            <strong id="studentNumber">
                                -
                            </strong>

                        </div>


                        <div class="info-item">

                            <span>Year</span>

                            <strong id="studentYear">
                                -
                            </strong>

                        </div>


                        <div class="info-item">

                            <span>Course</span>

                            <strong id="studentCourse">
                                -
                            </strong>

                        </div>


                        <div class="info-item">

                            <span>Gender</span>

                            <strong id="studentGender">
                                -
                            </strong>

                        </div>


                        <div class="info-item">

                            <span>Email</span>

                            <strong id="studentEmail">
                                -
                            </strong>

                        </div>

                    </div>

                </div>



                <!-- =========================
                     SESSION DATE
                ========================= -->

                <div class="form-group">

                    <label for="session_date">
                        Session Date
                    </label>

                    <input
                        type="date"
                        name="session_date"
                        id="session_date"
                        value="<?= htmlspecialchars($_POST['session_date'] ?? ''); ?>"
                        required
                    >

                </div>



                <!-- =========================
                     COUNSELING NOTES
                ========================= -->

                <div class="form-group">

                    <label for="notes">
                        Counseling Notes
                    </label>

                    <textarea
                        name="notes"
                        id="notes"
                        placeholder="Enter counseling notes..."
                        required
                    ><?= htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>

                </div>



                <!-- =========================
                     BUTTONS
                ========================= -->

                <div class="form-actions">

                    <a
                        href="records.php"
                        class="btn btn-cancel"
                    >

                        <i class="fas fa-arrow-left"></i>

                        Cancel

                    </a>


                    <button
                        type="submit"
                        class="btn btn-save"
                    >

                        <i class="fas fa-save"></i>

                        Save Record

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



<!-- BOOTSTRAP JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



<!-- STUDENT INFORMATION SCRIPT -->

<script>

const studentSelect =
    document.getElementById("student_id");

const studentInfo =
    document.getElementById("studentInfo");


studentSelect.addEventListener("change", function () {

    const option =
        this.options[this.selectedIndex];


    if (this.value) {

        document.getElementById("studentFullname").textContent =
            option.getAttribute("data-fullname") || "-";

        document.getElementById("studentNumber").textContent =
            option.getAttribute("data-number") || "-";

        document.getElementById("studentYear").textContent =
            option.getAttribute("data-year") || "-";

        document.getElementById("studentCourse").textContent =
            option.getAttribute("data-course") || "-";

        document.getElementById("studentGender").textContent =
            option.getAttribute("data-gender") || "-";

        document.getElementById("studentEmail").textContent =
            option.getAttribute("data-email") || "-";

        studentInfo.style.display = "block";

    } else {

        document.getElementById("studentFullname").textContent = "-";

        document.getElementById("studentNumber").textContent = "-";

        document.getElementById("studentYear").textContent = "-";

        document.getElementById("studentCourse").textContent = "-";

        document.getElementById("studentGender").textContent = "-";

        document.getElementById("studentEmail").textContent = "-";

        studentInfo.style.display = "none";
    }

});

</script>


</body>

</html>