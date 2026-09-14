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
// SAVE COUNSELOR
// =====================================
if (isset($_POST['save'])) {

    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_input = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // =====================================
    // VALIDATE REQUIRED FIELDS
    // =====================================
    if (
        empty($fullname) ||
        empty($email) ||
        empty($password_input) ||
        empty($confirm_password)
    ) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-exclamation-circle me-2'></i>
            Please complete all required fields.
        </div>
        ";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-exclamation-circle me-2'></i>
            Please enter a valid email address.
        </div>
        ";

    // =====================================
    // PASSWORD VALIDATION
    // =====================================
    } elseif (strlen($password_input) < 8) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-shield-lock me-2'></i>
            Password must be at least 8 characters long.
        </div>
        ";

    } elseif (!preg_match('/[A-Z]/', $password_input)) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-shield-lock me-2'></i>
            Password must contain at least one uppercase letter.
        </div>
        ";

    } elseif (!preg_match('/[a-z]/', $password_input)) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-shield-lock me-2'></i>
            Password must contain at least one lowercase letter.
        </div>
        ";

    } elseif (!preg_match('/[0-9]/', $password_input)) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-shield-lock me-2'></i>
            Password must contain at least one number.
        </div>
        ";

    } elseif (!preg_match('/[^A-Za-z0-9]/', $password_input)) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-shield-lock me-2'></i>
            Password must contain at least one special character.
        </div>
        ";

    } elseif ($password_input !== $confirm_password) {

        $message = "
        <div class='alert alert-danger'>
            <i class='bi bi-shield-x me-2'></i>
            Passwords do not match.
        </div>
        ";

    } else {

        // =====================================
        // CHECK EMAIL
        // =====================================
        $check = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        if ($check === false) {

            $message = "
            <div class='alert alert-danger'>
                <i class='bi bi-exclamation-circle me-2'></i>
                Database Error:
                " . htmlspecialchars($conn->error) . "
            </div>
            ";

        } else {

            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {

                $message = "
                <div class='alert alert-danger'>
                    <i class='bi bi-exclamation-circle me-2'></i>
                    Email already exists.
                </div>
                ";

                $check->close();

            } else {

                $check->close();

                // =====================================
                // HASH PASSWORD
                // =====================================
                $password = password_hash(
                    $password_input,
                    PASSWORD_DEFAULT
                );

                $role = "counselor";

                // =====================================
                // INSERT USER
                // =====================================
                $stmt = $conn->prepare("
                    INSERT INTO users
                    (
                        fullname,
                        email,
                        password,
                        role
                    )
                    VALUES
                    (?, ?, ?, ?)
                ");

                if ($stmt === false) {

                    $message = "
                    <div class='alert alert-danger'>
                        <i class='bi bi-exclamation-circle me-2'></i>
                        Database Error:
                        " . htmlspecialchars($conn->error) . "
                    </div>
                    ";

                } else {

                    $stmt->bind_param(
                        "ssss",
                        $fullname,
                        $email,
                        $password,
                        $role
                    );

                    if ($stmt->execute()) {

                        $new_user_id = $conn->insert_id;
                        $stmt->close();

                        // =====================================
                        // CREATE COUNSELOR RECORD
                        // =====================================
                        $counselor_stmt = $conn->prepare("
                            INSERT INTO counselors (user_id)
                            VALUES (?)
                        ");

                        if ($counselor_stmt === false) {

                            $delete_user = $conn->prepare("
                                DELETE FROM users
                                WHERE id = ?
                            ");

                            if ($delete_user) {
                                $delete_user->bind_param("i", $new_user_id);
                                $delete_user->execute();
                                $delete_user->close();
                            }

                            $message = "
                            <div class='alert alert-danger'>
                                <i class='bi bi-exclamation-circle me-2'></i>
                                Database Error:
                                " . htmlspecialchars($conn->error) . "
                            </div>
                            ";

                        } else {

                            $counselor_stmt->bind_param(
                                "i",
                                $new_user_id
                            );

                            if ($counselor_stmt->execute()) {

                                $message = "
                                <div class='alert alert-success'>
                                    <i class='bi bi-check-circle-fill me-2'></i>
                                    Counselor added successfully.
                                </div>
                                ";

                                $fullname = "";
                                $email = "";

                            } else {

                                $delete_user = $conn->prepare("
                                    DELETE FROM users
                                    WHERE id = ?
                                ");

                                if ($delete_user) {
                                    $delete_user->bind_param("i", $new_user_id);
                                    $delete_user->execute();
                                    $delete_user->close();
                                }

                                $message = "
                                <div class='alert alert-danger'>
                                    <i class='bi bi-exclamation-circle me-2'></i>
                                    " . htmlspecialchars($counselor_stmt->error) . "
                                </div>
                                ";
                            }

                            $counselor_stmt->close();
                        }

                    } else {

                        $message = "
                        <div class='alert alert-danger'>
                            <i class='bi bi-exclamation-circle me-2'></i>
                            " . htmlspecialchars($stmt->error) . "
                        </div>
                        ";

                        $stmt->close();
                    }
                }
            }
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

<title>
    Add Counselor | PRMSU Guidance
</title>

<link
    rel="icon"
    href="../assets/images/prmsu-logo.png"
>

<link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    rel="stylesheet"
>

<style>

:root {
    --primary-navy: #002147;
    --secondary-navy: #073b78;
    --accent-blue: #0d6efd;
    --bg-light: #f4f7fb;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
}

body {
    min-height: 100vh;
    background-color: var(--bg-light);
    background-image:
        linear-gradient(
            rgba(244, 247, 251, 0.92),
            rgba(244, 247, 251, 0.92)
        ),
        url("../assets/images/dashboard-bg.jpg");
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    color: #2c3e50;
}

/* =====================================
   SIDEBAR
===================================== */

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 260px;
    height: 100vh;

    background:
        linear-gradient(
            180deg,
            var(--primary-navy) 0%,
            var(--secondary-navy) 100%
        );

    z-index: 1040;

    box-shadow:
        4px 0 20px
        rgba(0, 0, 0, 0.08);

    overflow-y: auto;

    display: flex;
    flex-direction: column;
}

.brand-header {
    padding: 20px;

    display: flex;
    align-items: center;

    gap: 12px;

    border-bottom:
        1px solid
        rgba(255, 255, 255, 0.1);
}

.brand-header img {
    width: 45px;
    height: 45px;
    object-fit: contain;
}

.brand-text h4 {
    color: white;
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
}

.brand-text span {
    color: rgba(255, 255, 255, 0.6);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* =====================================
   NAVIGATION
===================================== */

.nav-menu {
    padding: 20px 12px;
    flex: 1;
}

.nav-link-item {
    display: flex;
    align-items: center;

    gap: 12px;

    color: rgba(255, 255, 255, 0.8);

    text-decoration: none;

    padding: 12px 16px;

    border-radius: 10px;

    font-size: 13.5px;
    font-weight: 500;

    margin-bottom: 6px;

    transition:
        background 0.25s ease,
        color 0.25s ease;
}

.nav-link-item i {
    width: 22px;
    font-size: 18px;
    text-align: center;
}

.nav-link-item:hover {
    color: white;
    background: rgba(255, 255, 255, 0.12);
}

.nav-link-item.active {
    color: white;
    background: var(--accent-blue);

    box-shadow:
        0 4px 12px
        rgba(13, 110, 253, 0.3);
}

.nav-link-item.logout-link {
    color: #ff8e8e;
    background: rgba(220, 53, 69, 0.1);
    margin-top: 20px;
}

.nav-link-item.logout-link:hover {
    color: white;
    background: rgba(220, 53, 69, 0.25);
}

/* =====================================
   MAIN
===================================== */

.main {
    margin-left: 260px;
    min-height: 100vh;
    padding: 30px;
}

/* =====================================
   TOP HEADER
===================================== */

.top-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin-bottom: 25px;
    padding: 18px 25px;

    background: rgba(255, 255, 255, 0.75);

    backdrop-filter: blur(12px);

    border:
        1px solid
        rgba(255, 255, 255, 0.8);

    border-radius: 16px;

    box-shadow:
        0 4px 15px
        rgba(0, 0, 0, 0.03);
}

.page-title {
    min-width: 0;
}

.page-title h2 {
    margin: 0;
    color: var(--primary-navy);

    font-size: 23px;
    font-weight: 700;
}

.page-title p {
    margin: 4px 0 0;
    color: #6c757d;
    font-size: 13px;
}

/* =====================================
   FORM CARD
===================================== */

.form-card {
    max-width: 850px;

    margin: 0 auto;

    background: rgba(255, 255, 255, 0.95);

    border:
        1px solid
        #e9eef5;

    border-radius: 16px;

    box-shadow:
        0 8px 25px
        rgba(0, 0, 0, 0.05);

    overflow: hidden;
}

/* =====================================
   FORM CARD HEADER
===================================== */

.form-card-header {
    padding: 20px 24px;

    background:
        linear-gradient(
            135deg,
            #002147,
            #0d6efd
        );

    color: white;
}

.form-card-header h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.form-card-header p {
    margin: 4px 0 0;
    font-size: 12px;
    opacity: .8;
}

/* =====================================
   FORM BODY
===================================== */

.form-card-body {
    padding: 28px;
}

.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #334155;
}

.form-control {
    min-height: 43px;

    border-radius: 9px;

    border-color: #dce3ec;

    font-size: 13px;
}

.form-control:focus {
    border-color: #0d6efd;

    box-shadow:
        0 0 0 .2rem
        rgba(13, 110, 253, .12);
}

/* =====================================
   PASSWORD
===================================== */

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

.password-rules {
    margin-top: 10px;

    padding: 10px 12px;

    background: #f8fafc;

    border:
        1px solid
        #e5e7eb;

    border-radius: 8px;
}

.password-rules-title {
    font-size: 12px;
    font-weight: 600;

    color: #334155;

    margin-bottom: 6px;
}

.password-rule {
    display: flex;
    align-items: center;

    gap: 7px;

    font-size: 11.5px;

    color: #64748b;

    margin-bottom: 3px;
}

.password-rule:last-child {
    margin-bottom: 0;
}

.password-rule i {
    font-size: 12px;
}

.password-rule.valid {
    color: #198754;
}

.password-rule.invalid {
    color: #dc3545;
}

/* =====================================
   BUTTONS
===================================== */

.form-buttons {
    display: flex;

    gap: 10px;

    margin-top: 25px;
}

.form-buttons .btn {
    font-size: 13px;

    padding: 9px 18px;

    border-radius: 8px;
}

/* =====================================
   MOBILE NAVBAR
===================================== */

.mobile-navbar {
    display: none;
}

/* =====================================
   SIDEBAR CLOSE
===================================== */

.sidebar-close {
    display: none;
}

/* =====================================
   MOBILE RESPONSIVE
===================================== */

@media (max-width: 991.98px) {

    /* TOP MOBILE BAR */

    .mobile-navbar {
        position: fixed;

        top: 0;
        left: 0;
        right: 0;

        height: 62px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        padding: 0 14px 0 16px;

        background: var(--primary-navy);

        z-index: 1035;

        box-shadow:
            0 3px 12px
            rgba(0, 0, 0, 0.15);
    }

    /* LEFT SIDE */

    .brand-mini {
        display: flex;

        align-items: center;

        gap: 9px;

        color: white;

        min-width: 0;
    }

    .brand-mini img {
        width: 34px;
        height: 34px;

        object-fit: contain;

        flex-shrink: 0;
    }

    .brand-mini span {
        font-size: 15px;

        font-weight: 600;

        white-space: nowrap;
    }

    /* RIGHT SIDE BURGER */

    .mobile-menu-btn {
        width: 42px;
        height: 40px;

        flex-shrink: 0;

        display: flex;

        align-items: center;
        justify-content: center;

        padding: 0;

        margin-left: auto;

        border-radius: 8px;

        font-size: 22px;
    }

    /* SIDEBAR */

    .sidebar {
        position: fixed;

        top: 0;
        left: 0;

        width: 270px;
        max-width: 85vw;

        height: 100vh;

        border: 0;

        z-index: 1050;

        -webkit-overflow-scrolling: touch;
    }

    .sidebar-close {
        display: flex;

        position: absolute;

        top: 15px;
        right: 15px;

        width: 36px;
        height: 36px;

        border: none;

        border-radius: 8px;

        background:
            rgba(255, 255, 255, 0.1);

        color: white;

        align-items: center;
        justify-content: center;

        font-size: 15px;

        z-index: 2;
    }

    .sidebar-close:hover {
        background:
            rgba(255, 255, 255, 0.18);

        color: white;
    }

    .brand-header {
        padding: 20px;

        padding-right: 60px;
    }

    /* MAIN */

    .main {
        margin-left: 0;

        padding:
            82px
            20px
            25px;
    }

    .form-card {
        max-width: 100%;
    }
}

/* =====================================
   TABLET / PHONE
===================================== */

@media (max-width: 767.98px) {

    .mobile-navbar {
        height: 60px;
    }

    .brand-mini img {
        width: 32px;
        height: 32px;
    }

    .brand-mini span {
        font-size: 14px;
    }

    .mobile-menu-btn {
        width: 40px;
        height: 38px;
        font-size: 21px;
    }

    .main {
        padding:
            78px
            16px
            22px;
    }

    .top-header {
        padding: 15px;

        margin-bottom: 18px;

        border-radius: 14px;
    }

    .page-title h2 {
        font-size: 19px;
    }

    .page-title p {
        font-size: 11px;

        line-height: 1.5;
    }

    .form-card-header {
        padding: 18px 20px;
    }

    .form-card-header h4 {
        font-size: 18px;
    }

    .form-card-header p {
        font-size: 12px;
    }

    .form-card-body {
        padding: 20px;
    }

    .form-buttons {
        flex-direction: column;
    }

    .form-buttons .btn {
        width: 100%;
    }
}

/* =====================================
   SMALL PHONES
===================================== */

@media (max-width: 480px) {

    .mobile-navbar {
        padding:
            0
            10px
            0
            13px;
    }

    .brand-mini {
        gap: 7px;
    }

    .brand-mini img {
        width: 30px;
        height: 30px;
    }

    .brand-mini span {
        font-size: 13.5px;
    }

    .mobile-menu-btn {
        width: 38px;
        height: 36px;

        font-size: 20px;
    }

    .main {
        padding:
            76px
            14px
            20px;
    }

    .top-header {
        padding: 14px;

        margin-bottom: 16px;
    }

    .page-title h2 {
        font-size: 18px;
    }

    .page-title p {
        font-size: 10.5px;
    }

    .form-card-header {
        padding: 17px 18px;
    }

    .form-card-header h4 {
        font-size: 17px;
    }

    .form-card-header p {
        font-size: 11.5px;
    }

    .form-card-body {
        padding: 18px 16px;
    }
}

/* =====================================
   VERY SMALL PHONES
===================================== */

@media (max-width: 360px) {

    .brand-mini span {
        font-size: 13px;
    }

    .mobile-menu-btn {
        width: 36px;
        height: 35px;

        font-size: 19px;
    }

    .main {
        padding:
            74px
            12px
            18px;
    }

    .page-title h2 {
        font-size: 17px;
    }

    .page-title p {
        font-size: 10px;
    }

    .form-card-header h4 {
        font-size: 16px;
    }

    .form-card-header p {
        font-size: 11px;
    }

    .form-card-body {
        padding: 16px 14px;
    }
}

</style>

</head>

<body>

<!-- =====================================
     MOBILE NAVBAR
===================================== -->

<div class="mobile-navbar">

```
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
```

</div>

<!-- =====================================
     SIDEBAR
===================================== -->

<div
    class="offcanvas-lg offcanvas-start sidebar"
    tabindex="-1"
    id="sidebarMenu"
    aria-label="Admin navigation"
>

```
<button
    type="button"
    class="sidebar-close"
    data-bs-dismiss="offcanvas"
    aria-label="Close menu"
>
    <i class="bi bi-x-lg"></i>
</button>

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
        class="nav-link-item active"
    >
        <i class="bi bi-person-workspace"></i>
        <span>
            Counselors
        </span>
    </a>

    <a
        href="announcements.php"
        class="nav-link-item"
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
```

</div>

<!-- =====================================
     MAIN CONTENT
===================================== -->

<div class="main">

```
<!-- TOP HEADER -->

<div class="top-header">

    <div class="page-title">

        <h2>
            Add Counselor
        </h2>

        <p>
            Create a new counselor account for the PRMSU Guidance System.
        </p>

    </div>

    <div>

        <i
            class="bi bi-person-circle"
            style="
                font-size:35px;
                color:#002147;
            "
        ></i>

    </div>

</div>

<!-- FORM CARD -->

<div class="form-card">

    <!-- CARD HEADER -->

    <div class="form-card-header">

        <h4>

            <i class="bi bi-person-workspace me-2"></i>

            Counselor Information

        </h4>

        <p>
            Enter the counselor's account details below.
        </p>

    </div>

    <!-- CARD BODY -->

    <div class="form-card-body">

        <?= $message; ?>

        <form
            method="POST"
            id="counselorForm"
        >

            <!-- FULL NAME -->

            <div class="mb-3">

                <label class="form-label">
                    Full Name
                </label>

                <input
                    type="text"
                    name="fullname"
                    class="form-control"
                    placeholder="Enter counselor full name"
                    value="<?= htmlspecialchars($fullname ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >

            </div>

            <!-- EMAIL -->

            <div class="mb-3">

                <label class="form-label">
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter counselor email"
                    value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >

            </div>

            <!-- PASSWORD -->

            <div class="mb-3">

                <label class="form-label">
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
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword('password', 'passwordIcon', this)"
                        aria-label="Show password"
                    >

                        <i
                            class="bi bi-eye"
                            id="passwordIcon"
                        ></i>

                    </button>

                </div>

                <!-- PASSWORD RULES -->

                <div class="password-rules">

                    <div class="password-rules-title">
                        Password must contain:
                    </div>

                    <div
                        class="password-rule"
                        id="ruleLength"
                    >
                        <i class="bi bi-circle"></i>
                        At least 8 characters
                    </div>

                    <div
                        class="password-rule"
                        id="ruleUpper"
                    >
                        <i class="bi bi-circle"></i>
                        At least one uppercase letter
                    </div>

                    <div
                        class="password-rule"
                        id="ruleLower"
                    >
                        <i class="bi bi-circle"></i>
                        At least one lowercase letter
                    </div>

                    <div
                        class="password-rule"
                        id="ruleNumber"
                    >
                        <i class="bi bi-circle"></i>
                        At least one number
                    </div>

                    <div
                        class="password-rule"
                        id="ruleSpecial"
                    >
                        <i class="bi bi-circle"></i>
                        At least one special character
                    </div>

                </div>

            </div>

            <!-- CONFIRM PASSWORD -->

            <div class="mb-3">

                <label class="form-label">
                    Confirm Password
                </label>

                <div class="password-wrapper">

                    <input
                        type="password"
                        name="confirm_password"
                        id="confirm_password"
                        class="form-control"
                        placeholder="Confirm password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword('confirm_password', 'confirmPasswordIcon', this)"
                        aria-label="Show confirm password"
                    >

                        <i
                            class="bi bi-eye"
                            id="confirmPasswordIcon"
                        ></i>

                    </button>

                </div>

                <div
                    id="passwordMatch"
                    style="
                        font-size:11.5px;
                        margin-top:6px;
                    "
                ></div>

            </div>

            <!-- BUTTONS -->

            <div class="form-buttons">

                <button
                    type="submit"
                    name="save"
                    class="btn btn-primary"
                    id="saveButton"
                >

                    <i class="bi bi-save me-1"></i>

                    Save Counselor

                </button>

                <a
                    href="counselors.php"
                    class="btn btn-secondary"
                >

                    <i class="bi bi-arrow-left me-1"></i>

                    Back

                </a>

            </div>

        </form>

    </div>

</div>
```

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script>

// =====================================
// SHOW / HIDE PASSWORD
// =====================================

function togglePassword(inputId, iconId, button) {

    const input =
        document.getElementById(inputId);

    const icon =
        document.getElementById(iconId);

    if (input.type === "password") {

        input.type = "text";

        icon.classList.remove("bi-eye");

        icon.classList.add("bi-eye-slash");

        button.setAttribute(
            "aria-label",
            "Hide password"
        );

    } else {

        input.type = "password";

        icon.classList.remove("bi-eye-slash");

        icon.classList.add("bi-eye");

        button.setAttribute(
            "aria-label",
            "Show password"
        );
    }
}

// =====================================
// PASSWORD RULE CHECKER
// =====================================

const passwordInput =
    document.getElementById("password");

const rules = {

    ruleLength:
        value => value.length >= 8,

    ruleUpper:
        value => /[A-Z]/.test(value),

    ruleLower:
        value => /[a-z]/.test(value),

    ruleNumber:
        value => /[0-9]/.test(value),

    ruleSpecial:
        value => /[^A-Za-z0-9]/.test(value)

};

function updateRule(ruleId, valid) {

    const rule =
        document.getElementById(ruleId);

    const icon =
        rule.querySelector("i");

    rule.classList.remove(
        "valid",
        "invalid"
    );

    if (valid) {

        rule.classList.add("valid");

        icon.classList.remove(
            "bi-circle"
        );

        icon.classList.add(
            "bi-check-circle-fill"
        );

    } else {

        rule.classList.add("invalid");

        icon.classList.remove(
            "bi-check-circle-fill"
        );

        icon.classList.add(
            "bi-circle"
        );
    }
}

passwordInput.addEventListener(
    "input",
    function () {

        const value = this.value;

        Object.entries(rules).forEach(
            ([ruleId, check]) => {

                updateRule(
                    ruleId,
                    check(value)
                );

            }
        );

        checkPasswordMatch();

    }
);

// =====================================
// CONFIRM PASSWORD CHECK
// =====================================

const confirmPasswordInput =
    document.getElementById(
        "confirm_password"
    );

const passwordMatch =
    document.getElementById(
        "passwordMatch"
    );

function checkPasswordMatch() {

    const password =
        passwordInput.value;

    const confirm =
        confirmPasswordInput.value;

    if (confirm === "") {

        passwordMatch.textContent = "";

        return;
    }

    if (password === confirm) {

        passwordMatch.innerHTML =
            '<i class="bi bi-check-circle-fill me-1"></i> Passwords match.';

        passwordMatch.style.color =
            "#198754";

    } else {

        passwordMatch.innerHTML =
            '<i class="bi bi-x-circle-fill me-1"></i> Passwords do not match.';

        passwordMatch.style.color =
            "#dc3545";
    }
}

confirmPasswordInput.addEventListener(
    "input",
    checkPasswordMatch
);

// =====================================
// PREVENT INVALID PASSWORD SUBMIT
// =====================================

document
    .getElementById("counselorForm")
    .addEventListener(
        "submit",
        function (event) {

            const password =
                passwordInput.value;

            const confirm =
                confirmPasswordInput.value;

            const validPassword =

                password.length >= 8 &&

                /[A-Z]/.test(password) &&

                /[a-z]/.test(password) &&

                /[0-9]/.test(password) &&

                /[^A-Za-z0-9]/.test(password);

            if (!validPassword) {

                event.preventDefault();

                alert(
                    "Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number, and one special character."
                );

                return;
            }

            if (password !== confirm) {

                event.preventDefault();

                alert(
                    "Passwords do not match."
                );
            }

        }
    );

</script>

</body>

</html>
