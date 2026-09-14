
<?php

session_start();

include("../includes/db.php");


/*
|--------------------------------------------------------------------------
| CHECK ADMIN SESSION
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] != 'admin'
) {
    header("Location: login.php");
    exit();
}


$message = "";


/*
|--------------------------------------------------------------------------
| SAVE STUDENT
|--------------------------------------------------------------------------
*/

if (isset($_POST['save'])) {

    $fullname = trim($_POST['fullname'] ?? '');
    $student_number = trim($_POST['student_number'] ?? '');
    $year_level = trim($_POST['year_level'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | VALIDATE REQUIRED FIELDS
    |--------------------------------------------------------------------------
    */

    if (
        empty($fullname) ||
        empty($student_number) ||
        empty($year_level) ||
        empty($course) ||
        empty($gender) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-exclamation-circle-fill me-2'></i>
            Please complete all required fields.
        </div>
        ";

    } elseif (strlen($password) < 8) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-shield-lock-fill me-2'></i>
            Password must be at least 8 characters long.
        </div>
        ";

    } elseif (!preg_match('/[A-Z]/', $password)) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-shield-lock-fill me-2'></i>
            Password must contain at least one uppercase letter.
        </div>
        ";

    } elseif (!preg_match('/[a-z]/', $password)) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-shield-lock-fill me-2'></i>
            Password must contain at least one lowercase letter.
        </div>
        ";

    } elseif (!preg_match('/[0-9]/', $password)) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-shield-lock-fill me-2'></i>
            Password must contain at least one number.
        </div>
        ";

    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-shield-lock-fill me-2'></i>
            Password must contain at least one special character.
        </div>
        ";

    } elseif ($password !== $confirm_password) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-shield-x-fill me-2'></i>
            Passwords do not match.
        </div>
        ";

    } else {


        /*
        |--------------------------------------------------------------------------
        | CHECK EXISTING EMAIL
        |--------------------------------------------------------------------------
        */

        $check_email = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        if (!$check_email) {

            $message = "
            <div class='alert alert-danger'>
                <i class='bi bi-exclamation-circle-fill me-2'></i>
                Database Error:
                " . htmlspecialchars($conn->error) . "
            </div>
            ";

        } else {

            $check_email->bind_param("s", $email);
            $check_email->execute();

            $email_result = $check_email->get_result();


            /*
            |--------------------------------------------------------------------------
            | CHECK EXISTING STUDENT NUMBER
            |--------------------------------------------------------------------------
            */

            $check_student = $conn->prepare("
                SELECT student_id
                FROM students
                WHERE student_number = ?
                LIMIT 1
            ");

            if (!$check_student) {

                $message = "
                <div class='alert alert-danger'>
                    <i class='bi bi-exclamation-circle-fill me-2'></i>
                    Database Error:
                    " . htmlspecialchars($conn->error) . "
                </div>
                ";

            } else {

                $check_student->bind_param("s", $student_number);
                $check_student->execute();

                $student_result = $check_student->get_result();


                /*
                |--------------------------------------------------------------------------
                | DUPLICATE CHECK
                |--------------------------------------------------------------------------
                */

                if ($email_result->num_rows > 0) {

                    $message = "
                    <div class='alert alert-danger'>
                        <i class='bi bi-envelope-exclamation-fill me-2'></i>
                        Email address is already registered.
                    </div>
                    ";

                } elseif ($student_result->num_rows > 0) {

                    $message = "
                    <div class='alert alert-danger'>
                        <i class='bi bi-person-x-fill me-2'></i>
                        Student number is already registered.
                    </div>
                    ";

                } else {


                    /*
                    |--------------------------------------------------------------------------
                    | HASH PASSWORD
                    |--------------------------------------------------------------------------
                    */

                    $hashed_password = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | INSERT USER ACCOUNT
                    |--------------------------------------------------------------------------
                    */

                    $role = "student";

                    $insert_user = $conn->prepare("
                        INSERT INTO users
                        (
                            fullname,
                            email,
                            password,
                            role
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?
                        )
                    ");

                    if (!$insert_user) {

                        $message = "
                        <div class='alert alert-danger'>
                            <i class='bi bi-exclamation-circle-fill me-2'></i>
                            Failed to create student account.
                            <br>
                            " . htmlspecialchars($conn->error) . "
                        </div>
                        ";

                    } else {

                        $insert_user->bind_param(
                            "ssss",
                            $fullname,
                            $email,
                            $hashed_password,
                            $role
                        );


                        if ($insert_user->execute()) {

                            $user_id = $conn->insert_id;


                            /*
                            |--------------------------------------------------------------------------
                            | INSERT STUDENT INFORMATION
                            |--------------------------------------------------------------------------
                            */

                            $insert_student = $conn->prepare("
                                INSERT INTO students
                                (
                                    user_id,
                                    student_number,
                                    year_level,
                                    course,
                                    gender
                                )
                                VALUES
                                (
                                    ?,
                                    ?,
                                    ?,
                                    ?,
                                    ?
                                )
                            ");

                            if (!$insert_student) {

                                $conn->query("
                                    DELETE FROM users
                                    WHERE id = " . (int)$user_id
                                );

                                $message = "
                                <div class='alert alert-danger'>
                                    <i class='bi bi-exclamation-circle-fill me-2'></i>
                                    Failed to save student information.
                                    <br>
                                    " . htmlspecialchars($conn->error) . "
                                </div>
                                ";

                            } else {

                                $insert_student->bind_param(
                                    "issss",
                                    $user_id,
                                    $student_number,
                                    $year_level,
                                    $course,
                                    $gender
                                );


                                if ($insert_student->execute()) {

                                    $message = "
                                    <div class='alert alert-success'>
                                        <i class='bi bi-check-circle-fill me-2'></i>
                                        Student added successfully.
                                    </div>
                                    ";

                                } else {

                                    $conn->query("
                                        DELETE FROM users
                                        WHERE id = " . (int)$user_id
                                    );

                                    $message = "
                                    <div class='alert alert-danger'>
                                        <i class='bi bi-exclamation-circle-fill me-2'></i>
                                        Failed to save student information.
                                        <br>
                                        " .
                                        htmlspecialchars($insert_student->error)
                                        .
                                        "
                                    </div>
                                    ";
                                }


                                $insert_student->close();
                            }

                        } else {

                            $message = "
                            <div class='alert alert-danger'>
                                <i class='bi bi-exclamation-circle-fill me-2'></i>
                                Failed to create student account.
                                <br>
                                " .
                                htmlspecialchars($insert_user->error)
                                .
                                "
                            </div>
                            ";
                        }


                        $insert_user->close();
                    }
                }


                $check_student->close();
            }


            $check_email->close();
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

<title>Add Student | PRMSU Guidance</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    rel="stylesheet"
>


<style>

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


/* =========================
   SIDEBAR
========================= */

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

    box-shadow: 4px 0 15px rgba(0,0,0,.12);

}


/* =========================
   BRAND
========================= */

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

    font-size: 17px;

    margin: 0;

    font-weight: 600;

}


.brand-text span {

    font-size: 12px;

    opacity: .75;

}


/* =========================
   NAVIGATION
========================= */

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

    transition: .25s;

    font-size: 14px;

}


.nav-link-item i {

    font-size: 18px;

    width: 22px;

}


.nav-link-item:hover {

    background: rgba(255,255,255,.12);

    color: white;

}


.nav-link-item.active {

    background: rgba(255,255,255,.18);

    color: white;

    font-weight: 600;

}


.logout-link {

    margin-top: 20px;

}


/* =========================
   MAIN CONTENT
========================= */

.main {

    margin-left: 250px;

    min-height: 100vh;

    padding: 35px;

}


/* =========================
   PAGE HEADER
========================= */

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

    margin: 0;

    font-size: 14px;

}


/* =========================
   FORM CARD
========================= */

.form-card {

    width: 100%;

    max-width: 850px;

    margin: 0 auto;

    background: white;

    border: none;

    border-radius: 16px;

    box-shadow:
        0 8px 30px rgba(0,0,0,.08);

    overflow: hidden;

}


/* =========================
   CARD HEADER
========================= */

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


/* =========================
   CARD BODY
========================= */

.form-card-body {

    padding: 30px;

}


/* =========================
   LABEL
========================= */

.form-label {

    font-weight: 600;

    color: #343a40;

    font-size: 14px;

}


/* =========================
   INPUT
========================= */

.form-control {

    min-height: 45px;

    border-radius: 8px;

    border: 1px solid #d9dee5;

}


.form-control:focus {

    border-color: #0A3D91;

    box-shadow:
        0 0 0 .2rem
        rgba(10,61,145,.12);

}


/* =========================
   SELECT
========================= */

.form-select {

    min-height: 45px;

    border-radius: 8px;

    border: 1px solid #d9dee5;

}


.form-select:focus {

    border-color: #0A3D91;

    box-shadow:
        0 0 0 .2rem
        rgba(10,61,145,.12);

}


/* =========================
   PASSWORD WRAPPER
========================= */

.password-wrapper {

    position: relative;

}


.password-wrapper .form-control {

    padding-right: 48px;

}


.password-toggle {

    position: absolute;

    top: 50%;
    right: 12px;

    transform: translateY(-50%);

    border: none;

    background: transparent;

    color: #64748b;

    font-size: 18px;

    padding: 4px;

    cursor: pointer;

    display: flex;

    align-items: center;

    justify-content: center;

}


.password-toggle:hover {

    color: #0A3D91;

}


.password-toggle:focus {

    outline: none;

    color: #0A3D91;

}


/* =========================
   PASSWORD REQUIREMENTS
========================= */

.password-requirements {

    margin-top: 8px;

    padding: 10px 12px;

    background: #f8fafc;

    border: 1px solid #e2e8f0;

    border-radius: 8px;

    color: #64748b;

    font-size: 12px;

    line-height: 1.8;

}


.password-requirements-title {

    display: block;

    color: #475569;

    font-size: 12px;

    font-weight: 600;

    margin-bottom: 2px;

}


.password-check {

    display: flex;

    align-items: center;

    gap: 6px;

}


.password-check i {

    font-size: 11px;

}


.password-check.valid {

    color: #198754;

}


.password-check.valid i {

    color: #198754;

}


/* =========================
   PASSWORD MATCH
========================= */

#matchMessage {

    display: block;

    margin-top: 7px;

    font-size: 12px;

}


#matchMessage.match {

    color: #198754;

}


#matchMessage.no-match {

    color: #dc3545;

}


/* =========================
   BUTTONS
========================= */

.btn {

    border-radius: 8px;

    padding: 10px 18px;

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


/* =========================
   ALERT
========================= */

.alert {

    border-radius: 9px;

    font-size: 14px;

}


/* =========================
   MOBILE HEADER
========================= */

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


.mobile-header button {

    border: none;

    background: transparent;

    color: white;

    font-size: 25px;

}


/* =========================
   MOBILE
========================= */

@media (max-width: 768px) {

    .sidebar {

        width: 230px;

        transform: translateX(-100%);

        transition: .3s;

        z-index: 1050;

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


    .form-card-header {

        padding: 18px 20px;

    }


    .form-card-header h4 {

        font-size: 19px;

    }


    .form-card-header p {

        font-size: 13px;

    }


    .password-requirements {

        font-size: 11.5px;

    }

}


/* =========================
   DESKTOP
========================= */

@media (min-width: 769px) {

    .mobile-header {

        display: none;

    }

}


/* =========================
   SMALL PHONE
========================= */

@media (max-width: 480px) {

    .main {

        padding: 15px;

    }


    .form-card {

        border-radius: 14px;

    }


    .form-card-body {

        padding: 18px;

    }


    .form-card-header {

        padding: 17px 18px;

    }


    .form-card-header h4 {

        font-size: 18px;

    }


    .form-card-header p {

        font-size: 13px;

    }


    .password-toggle {

        right: 10px;

        font-size: 17px;

    }

}

</style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar" id="sidebar">


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


    <div class="nav-menu">


        <a
            href="dashboard.php"
            class="nav-link-item"
        >

            <i class="bi bi-speedometer2"></i>

            <span>Dashboard</span>

        </a>


        <a
            href="students.php"
            class="nav-link-item active"
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



<!-- =========================
     MAIN
========================= -->

<div class="main">


    <!-- MOBILE HEADER -->

    <div class="mobile-header">

        <strong>
            PRMSU Guidance
        </strong>

        <button
            type="button"
            onclick="toggleSidebar()"
            aria-label="Open menu"
        >

            <i class="bi bi-list"></i>

        </button>

    </div>



    <!-- PAGE HEADER -->

    <div class="page-header">

        <h2>
            Add Student
        </h2>

        <p>
            Create a new student account and student record.
        </p>

    </div>



    <!-- FORM CARD -->

    <div class="form-card">


        <div class="form-card-header">

            <h4>

                <i class="bi bi-person-plus-fill me-1"></i>

                Add New Student

            </h4>

            <p>
                Enter the student's information below.
            </p>

        </div>



        <div class="form-card-body">


            <?= $message ?>



            <form method="POST" id="studentForm">


                <!-- FULL NAME -->

                <div class="mb-3">

                    <label
                        class="form-label"
                        for="fullname"
                    >
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="fullname"
                        id="fullname"
                        class="form-control"
                        placeholder="Enter full name"
                        value="<?= htmlspecialchars($_POST['fullname'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >

                </div>



                <!-- STUDENT NUMBER -->

                <div class="mb-3">

                    <label
                        class="form-label"
                        for="student_number"
                    >
                        Student Number
                    </label>

                    <input
                        type="text"
                        name="student_number"
                        id="student_number"
                        class="form-control"
                        placeholder="Enter student number"
                        value="<?= htmlspecialchars($_POST['student_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >

                </div>



                <!-- YEAR -->

                <div class="mb-3">

                    <label
                        class="form-label"
                        for="year_level"
                    >
                        Year
                    </label>

                    <select
                        name="year_level"
                        id="year_level"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Select Year
                        </option>

                        <option
                            value="1st Year"
                            <?= (($_POST['year_level'] ?? '') == '1st Year') ? 'selected' : '' ?>
                        >
                            1st Year
                        </option>

                        <option
                            value="2nd Year"
                            <?= (($_POST['year_level'] ?? '') == '2nd Year') ? 'selected' : '' ?>
                        >
                            2nd Year
                        </option>

                        <option
                            value="3rd Year"
                            <?= (($_POST['year_level'] ?? '') == '3rd Year') ? 'selected' : '' ?>
                        >
                            3rd Year
                        </option>

                        <option
                            value="4th Year"
                            <?= (($_POST['year_level'] ?? '') == '4th Year') ? 'selected' : '' ?>
                        >
                            4th Year
                        </option>

                    </select>

                </div>



                <!-- COURSE -->

                <div class="mb-3">

                    <label
                        class="form-label"
                        for="course"
                    >
                        Course
                    </label>

                    <input
                        type="text"
                        name="course"
                        id="course"
                        class="form-control"
                        placeholder="Enter course"
                        value="<?= htmlspecialchars($_POST['course'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >

                </div>



                <!-- GENDER -->

                <div class="mb-3">

                    <label
                        class="form-label"
                        for="gender"
                    >
                        Gender
                    </label>

                    <select
                        name="gender"
                        id="gender"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Select Gender
                        </option>

                        <option
                            value="Male"
                            <?= (($_POST['gender'] ?? '') == 'Male') ? 'selected' : '' ?>
                        >
                            Male
                        </option>

                        <option
                            value="Female"
                            <?= (($_POST['gender'] ?? '') == 'Female') ? 'selected' : '' ?>
                        >
                            Female
                        </option>

                    </select>

                </div>



                <!-- EMAIL -->

                <div class="mb-3">

                    <label
                        class="form-label"
                        for="email"
                    >
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        placeholder="Enter email address"
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >

                </div>



                <!-- PASSWORD -->

                <div class="mb-3">

                    <label
                        class="form-label"
                        for="password"
                    >
                        Password
                    </label>


                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Enter password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >


                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword('password', 'passwordIcon')"
                            aria-label="Show password"
                        >

                            <i
                                class="bi bi-eye"
                                id="passwordIcon"
                            ></i>

                        </button>

                    </div>


                    <div class="password-requirements">

                        <span class="password-requirements-title">
                            Password must contain:
                        </span>


                        <div
                            class="password-check"
                            id="lengthCheck"
                        >

                            <i class="bi bi-circle"></i>

                            <span>
                                At least 8 characters
                            </span>

                        </div>


                        <div
                            class="password-check"
                            id="uppercaseCheck"
                        >

                            <i class="bi bi-circle"></i>

                            <span>
                                At least one uppercase letter
                            </span>

                        </div>


                        <div
                            class="password-check"
                            id="lowercaseCheck"
                        >

                            <i class="bi bi-circle"></i>

                            <span>
                                At least one lowercase letter
                            </span>

                        </div>


                        <div
                            class="password-check"
                            id="numberCheck"
                        >

                            <i class="bi bi-circle"></i>

                            <span>
                                At least one number
                            </span>

                        </div>


                        <div
                            class="password-check"
                            id="specialCheck"
                        >

                            <i class="bi bi-circle"></i>

                            <span>
                                At least one special character
                            </span>

                        </div>

                    </div>

                </div>



                <!-- CONFIRM PASSWORD -->

                <div class="mb-4">

                    <label
                        class="form-label"
                        for="confirm_password"
                    >
                        Confirm Password
                    </label>


                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="confirm_password"
                            id="confirm_password"
                            class="form-control"
                            placeholder="Re-enter password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >


                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword('confirm_password', 'confirmPasswordIcon')"
                            aria-label="Show confirm password"
                        >

                            <i
                                class="bi bi-eye"
                                id="confirmPasswordIcon"
                            ></i>

                        </button>

                    </div>


                    <small id="matchMessage"></small>

                </div>



                <!-- BUTTONS -->

                <button
                    type="submit"
                    name="save"
                    class="btn btn-primary"
                >

                    <i class="bi bi-save me-1"></i>

                    Save Student

                </button>


                <a
                    href="students.php"
                    class="btn btn-secondary"
                >

                    <i class="bi bi-arrow-left me-1"></i>

                    Cancel

                </a>


            </form>


        </div>

    </div>

</div>



<script>

/*
|--------------------------------------------------------------------------
| SIDEBAR
|--------------------------------------------------------------------------
*/

function toggleSidebar() {

    const sidebar =
        document.getElementById("sidebar");

    sidebar.classList.toggle("show");

}


/*
|--------------------------------------------------------------------------
| SHOW / HIDE PASSWORD
|--------------------------------------------------------------------------
*/

function togglePassword(inputId, iconId) {

    const input =
        document.getElementById(inputId);

    const icon =
        document.getElementById(iconId);


    if (input.type === "password") {

        input.type = "text";

        icon.classList.remove("bi-eye");

        icon.classList.add("bi-eye-slash");

    } else {

        input.type = "password";

        icon.classList.remove("bi-eye-slash");

        icon.classList.add("bi-eye");

    }

}


/*
|--------------------------------------------------------------------------
| PASSWORD VALIDATION
|--------------------------------------------------------------------------
*/

const password =
    document.getElementById("password");

const confirmPassword =
    document.getElementById("confirm_password");

const lengthCheck =
    document.getElementById("lengthCheck");

const uppercaseCheck =
    document.getElementById("uppercaseCheck");

const lowercaseCheck =
    document.getElementById("lowercaseCheck");

const numberCheck =
    document.getElementById("numberCheck");

const specialCheck =
    document.getElementById("specialCheck");

const matchMessage =
    document.getElementById("matchMessage");


function updateCheck(element, valid) {

    const icon =
        element.querySelector("i");


    if (valid) {

        element.classList.add("valid");

        icon.className =
            "bi bi-check-circle-fill";

    } else {

        element.classList.remove("valid");

        icon.className =
            "bi bi-circle";

    }

}


function validatePassword() {

    const value =
        password.value;


    updateCheck(
        lengthCheck,
        value.length >= 8
    );


    updateCheck(
        uppercaseCheck,
        /[A-Z]/.test(value)
    );


    updateCheck(
        lowercaseCheck,
        /[a-z]/.test(value)
    );


    updateCheck(
        numberCheck,
        /[0-9]/.test(value)
    );


    updateCheck(
        specialCheck,
        /[^A-Za-z0-9]/.test(value)
    );


    checkPasswordMatch();

}


function checkPasswordMatch() {

    if (confirmPassword.value === "") {

        matchMessage.textContent = "";

        matchMessage.className = "";

        return;

    }


    if (
        password.value ===
        confirmPassword.value
    ) {

        matchMessage.textContent =
            "Passwords match.";

        matchMessage.className =
            "match";

    } else {

        matchMessage.textContent =
            "Passwords do not match.";

        matchMessage.className =
            "no-match";

    }

}


password.addEventListener(
    "input",
    validatePassword
);


confirmPassword.addEventListener(
    "input",
    checkPasswordMatch
);


/*
|--------------------------------------------------------------------------
| PREVENT INVALID SUBMISSION
|--------------------------------------------------------------------------
*/

document
    .getElementById("studentForm")
    .addEventListener(
        "submit",
        function(event) {

            const value =
                password.value;


            const validPassword =
                value.length >= 8 &&
                /[A-Z]/.test(value) &&
                /[a-z]/.test(value) &&
                /[0-9]/.test(value) &&
                /[^A-Za-z0-9]/.test(value);


            const passwordsMatch =
                value === confirmPassword.value;


            if (
                !validPassword ||
                !passwordsMatch
            ) {

                event.preventDefault();

                password.focus();

            }

        }
    );

</script>


</body>

</html>

