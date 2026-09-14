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


$message = "";


// =====================================
// SAVE ANNOUNCEMENT
// =====================================

if (isset($_POST['save'])) {

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');


    // =====================================
    // VALIDATION
    // =====================================

    if ($title === '' || $content === '') {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-exclamation-circle-fill'></i>
            <strong>Error!</strong>
            Please complete all fields.
        </div>
        ";

    } else {


        // =====================================
        // INSERT ANNOUNCEMENT
        // =====================================

        $stmt = $conn->prepare("
            INSERT INTO announcements
            (
                title,
                content
            )
            VALUES
            (
                ?,
                ?
            )
        ");


        if ($stmt === false) {

            $message = "
            <div class='alert alert-danger'>
                <i class='bi bi-exclamation-circle-fill'></i>
                <strong>Database Error!</strong>
                "
                . htmlspecialchars($conn->error)
                . "
            </div>
            ";

        } else {

            $stmt->bind_param(
                "ss",
                $title,
                $content
            );


            // =====================================
            // EXECUTE
            // =====================================

            if ($stmt->execute()) {

                $message = "
                <div class='alert alert-success'>
                    <i class='bi bi-check-circle-fill'></i>
                    <strong>Success!</strong>
                    Announcement posted successfully.
                </div>
                ";


                // Clear form after successful save

                $_POST['title'] = "";
                $_POST['content'] = "";

            } else {

                $message = "
                <div class='alert alert-danger'>
                    <i class='bi bi-exclamation-circle-fill'></i>
                    <strong>Error!</strong>
                    "
                    . htmlspecialchars($stmt->error)
                    . "
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

<title>Add Announcement | PRMSU Guidance</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
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

    font-family: Poppins, sans-serif;

}


body {

    min-height: 100vh;

    background:
        linear-gradient(
            rgba(244, 246, 249, 0.92),
            rgba(244, 246, 249, 0.92)
        ),
        url("../assets/images/dashboard-bg.jpg");

    background-size: cover;

    background-position: center;

    background-attachment: fixed;

}


/* =====================================
   SIDEBAR
===================================== */

.sidebar {

    position: fixed;

    left: 0;
    top: 0;

    width: 250px;

    height: 100vh;

    background:
        linear-gradient(
            180deg,
            #002147,
            #073b78
        );

    color: white;

    z-index: 1000;

    box-shadow:
        4px 0 15px rgba(0,0,0,.12);

}


/* =====================================
   BRAND
===================================== */

.brand-header {

    height: 90px;

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 15px 18px;

    border-bottom:
        1px solid
        rgba(255,255,255,.15);

}


.brand-header img {

    width: 52px;

    height: 52px;

    object-fit: contain;

}


.brand-text h4 {

    margin: 0;

    font-size: 17px;

    font-weight: 600;

}


.brand-text span {

    font-size: 12px;

    opacity: .75;

}


/* =====================================
   NAVIGATION
===================================== */

.nav-menu {

    padding-top: 15px;

}


.nav-link-item {

    display: flex;

    align-items: center;

    gap: 13px;

    color: white;

    text-decoration: none;

    padding: 14px 20px;

    margin: 4px 10px;

    border-radius: 9px;

    font-size: 14px;

    transition: .25s;

}


.nav-link-item i {

    width: 22px;

    font-size: 18px;

}


.nav-link-item:hover {

    background:
        rgba(255,255,255,.12);

    color: white;

}


.nav-link-item.active {

    background:
        rgba(255,255,255,.18);

    color: white;

    font-weight: 600;

}


.logout-link {

    margin-top: 20px;

}


/* =====================================
   MAIN
===================================== */

.main {

    margin-left: 250px;

    min-height: 100vh;

    padding: 35px;

}


/* =====================================
   MOBILE HEADER
===================================== */

.mobile-header {

    display: none;

    align-items: center;

    justify-content: space-between;

    background: #002147;

    color: white;

    padding: 12px 15px;

    border-radius: 10px;

    margin-bottom: 20px;

}


.mobile-header strong {

    font-size: 15px;

}


.mobile-header button {

    border: none;

    background: transparent;

    color: white;

    font-size: 25px;

}


/* =====================================
   PAGE HEADER
===================================== */

.page-header {

    margin-bottom: 25px;

}


.page-header h2 {

    color: #002147;

    font-size: 28px;

    font-weight: 700;

    margin-bottom: 5px;

}


.page-header p {

    color: #6c757d;

    font-size: 14px;

    margin: 0;

}


/* =====================================
   ANNOUNCEMENT CARD
===================================== */

.form-card {

    width: 100%;

    max-width: 850px;

    margin: 0 auto;

    background: white;

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 8px 30px rgba(0,0,0,.08);

}


/* =====================================
   CARD HEADER
===================================== */

.form-card-header {

    background:
        linear-gradient(
            135deg,
            #002147,
            #0A3D91
        );

    color: white;

    padding: 20px 25px;

}


.form-card-header h4 {

    margin: 0;

    font-size: 20px;

    font-weight: 600;

}


.form-card-header p {

    margin: 5px 0 0;

    font-size: 13px;

    opacity: .8;

}


/* =====================================
   CARD BODY
===================================== */

.form-card-body {

    padding: 30px;

}


/* =====================================
   FORM
===================================== */

.form-label {

    color: #343a40;

    font-size: 14px;

    font-weight: 600;

}


.form-control {

    min-height: 45px;

    border: 1px solid #d9dee5;

    border-radius: 8px;

    font-size: 14px;

}


.form-control:focus {

    border-color: #0A3D91;

    box-shadow:
        0 0 0 .2rem
        rgba(10,61,145,.12);

}


textarea.form-control {

    min-height: 180px;

    resize: vertical;

}


/* =====================================
   BUTTONS
===================================== */

.btn {

    min-height: 42px;

    padding: 9px 18px;

    border-radius: 8px;

    font-size: 14px;

}


.btn-primary {

    background: #0A3D91;

    border-color: #0A3D91;

}


.btn-primary:hover {

    background: #002147;

    border-color: #002147;

}


/* =====================================
   ALERT
===================================== */

.alert {

    border-radius: 9px;

    font-size: 14px;

}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 768px) {

    .sidebar {

        width: 230px;

        transform: translateX(-100%);

        transition: .3s;

    }


    .sidebar.show {

        transform: translateX(0);

    }


    .main {

        margin-left: 0;

        padding: 20px;

    }


    .mobile-header {

        display: flex;

    }


    .page-header h2 {

        font-size: 23px;

    }


    .form-card-body {

        padding: 20px;

    }

}

</style>

</head>


<body>


<!-- =====================================
     SIDEBAR
===================================== -->

<div
    class="sidebar"
    id="sidebar"
>


    <!-- BRAND -->

    <div class="brand-header">

        <img
            src="../assets/images/prmsu-logo.png"
            alt="PRMSU Logo"
        >

        <div class="brand-text">

            <h4>
                PRMSU Guidance
            </h4>

            <span>
                Admin Panel
            </span>

        </div>

    </div>



    <!-- NAVIGATION -->

    <div class="nav-menu">


        <a
            href="dashboard.php"
            class="nav-link-item"
        >

            <i class="bi bi-speedometer2"></i>

            <span>
                Dashboard
            </span>

        </a>


        <a
            href="students.php"
            class="nav-link-item"
        >

            <i class="bi bi-people-fill"></i>

            <span>
                Students
            </span>

        </a>


        <a
            href="counselors.php"
            class="nav-link-item"
        >

            <i class="bi bi-person-workspace"></i>

            <span>
                Counselors
            </span>

        </a>


        <a
            href="announcements.php"
            class="nav-link-item active"
        >

            <i class="bi bi-megaphone-fill"></i>

            <span>
                Announcements
            </span>

        </a>


        <a
            href="../logout.php"
            class="nav-link-item logout-link"
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


    <!-- MOBILE HEADER -->

    <div class="mobile-header">

        <strong>
            PRMSU Guidance
        </strong>

        <button
            type="button"
            onclick="toggleSidebar()"
        >

            <i class="bi bi-list"></i>

        </button>

    </div>



    <!-- PAGE HEADER -->

    <div class="page-header">

        <h2>
            Add Announcement
        </h2>

        <p>
            Create and publish an announcement for students.
        </p>

    </div>



    <!-- ANNOUNCEMENT CARD -->

    <div class="form-card">


        <!-- CARD HEADER -->

        <div class="form-card-header">

            <h4>

                <i class="bi bi-megaphone-fill"></i>

                Add Announcement

            </h4>

            <p>
                Enter the announcement details below.
            </p>

        </div>



        <!-- CARD BODY -->

        <div class="form-card-body">


            <!-- MESSAGE -->

            <?= $message; ?>



            <form method="POST">


                <!-- TITLE -->

                <div class="mb-3">

                    <label class="form-label">

                        Title

                    </label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        placeholder="Enter announcement title"
                        value="<?= htmlspecialchars($_POST['title'] ?? ''); ?>"
                        required
                    >

                </div>



                <!-- CONTENT -->

                <div class="mb-4">

                    <label class="form-label">

                        Content

                    </label>

                    <textarea
                        name="content"
                        class="form-control"
                        rows="7"
                        placeholder="Write your announcement..."
                        required
                    ><?= htmlspecialchars($_POST['content'] ?? ''); ?></textarea>

                </div>



                <!-- BUTTONS -->

                <button
                    type="submit"
                    name="save"
                    class="btn btn-primary"
                >

                    <i class="bi bi-send-fill"></i>

                    Post Announcement

                </button>


                <a
                    href="announcements.php"
                    class="btn btn-secondary"
                >

                    <i class="bi bi-arrow-left"></i>

                    Back

                </a>


            </form>


        </div>

    </div>

</div>



<script>

function toggleSidebar() {

    const sidebar =
        document.getElementById("sidebar");

    sidebar.classList.toggle("show");

}

</script>


</body>

</html>