```php
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
| CHECK STUDENT ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: students.php");
    exit();
}

$id = intval($_GET['id']);


/*
|--------------------------------------------------------------------------
| GET STUDENT DATA
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        users.id,
        users.fullname,
        users.email,
        students.student_number,
        students.year_level,
        students.course,
        students.gender
    FROM users
    INNER JOIN students
        ON users.id = students.user_id
    WHERE users.id = ?
      AND users.role = 'student'
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    header("Location: students.php");
    exit();
}

$row = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| UPDATE STUDENT
|--------------------------------------------------------------------------
*/

if (isset($_POST['update'])) {

    $fullname = trim($_POST['fullname'] ?? '');
    $student_number = trim($_POST['student_number'] ?? '');
    $year_level = trim($_POST['year_level'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $email = trim($_POST['email'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($fullname) ||
        empty($student_number) ||
        empty($year_level) ||
        empty($course) ||
        empty($gender) ||
        empty($email)
    ) {

        $message = "
        <div class='alert alert-danger'>
            Please complete all required fields.
        </div>
        ";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "
        <div class='alert alert-danger'>
            Please enter a valid email address.
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
              AND id != ?
            LIMIT 1
        ");

        $check_email->bind_param(
            "si",
            $email,
            $id
        );

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
              AND user_id != ?
            LIMIT 1
        ");

        $check_student->bind_param(
            "si",
            $student_number,
            $id
        );

        $check_student->execute();

        $student_result = $check_student->get_result();


        if ($email_result->num_rows > 0) {

            $message = "
            <div class='alert alert-danger'>
                Email address is already registered.
            </div>
            ";

        } elseif ($student_result->num_rows > 0) {

            $message = "
            <div class='alert alert-danger'>
                Student number is already registered.
            </div>
            ";

        } else {


            /*
            |--------------------------------------------------------------------------
            | UPDATE USERS
            |--------------------------------------------------------------------------
            */

            $update_user = $conn->prepare("
                UPDATE users
                SET
                    fullname = ?,
                    email = ?
                WHERE id = ?
                  AND role = 'student'
            ");

            $update_user->bind_param(
                "ssi",
                $fullname,
                $email,
                $id
            );


            /*
            |--------------------------------------------------------------------------
            | UPDATE STUDENTS
            |--------------------------------------------------------------------------
            */

            $update_student = $conn->prepare("
                UPDATE students
                SET
                    student_number = ?,
                    year_level = ?,
                    course = ?,
                    gender = ?
                WHERE user_id = ?
            ");

            $update_student->bind_param(
                "ssssi",
                $student_number,
                $year_level,
                $course,
                $gender,
                $id
            );


            /*
            |--------------------------------------------------------------------------
            | EXECUTE
            |--------------------------------------------------------------------------
            */

            if ($update_user->execute()) {

                if ($update_student->execute()) {

                    $message = "
                    <div class='alert alert-success'>
                        <i class='bi bi-check-circle-fill'></i>
                        Student updated successfully.
                    </div>
                    ";


                    /*
                    |--------------------------------------------------------------------------
                    | GET UPDATED DATA
                    |--------------------------------------------------------------------------
                    */

                    $row['fullname'] = $fullname;
                    $row['email'] = $email;
                    $row['student_number'] = $student_number;
                    $row['year_level'] = $year_level;
                    $row['course'] = $course;
                    $row['gender'] = $gender;

                } else {

                    $message = "
                    <div class='alert alert-danger'>
                        Failed to update student information.
                        <br>
                        " .
                        htmlspecialchars($update_student->error)
                        .
                        "
                    </div>
                    ";

                }

            } else {

                $message = "
                <div class='alert alert-danger'>
                    Failed to update student account.
                    <br>
                    " .
                    htmlspecialchars($update_user->error)
                    .
                    "
                </div>
                ";

            }


            $update_user->close();
            $update_student->close();
        }


        $check_email->close();
        $check_student->close();
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

<title>Edit Student | PRMSU Guidance</title>


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


.form-label {

    font-weight: 600;

    color: #343a40;

    font-size: 14px;

}


.form-control,
.form-select {

    min-height: 45px;

    border-radius: 8px;

    border: 1px solid #d9dee5;

}


.form-control:focus,
.form-select:focus {

    border-color: #0A3D91;

    box-shadow:
        0 0 0 .2rem
        rgba(10,61,145,.12);

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
   MOBILE
========================= */

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


@media (min-width: 769px) {

    .mobile-header {

        display: none;

    }

}


/* =========================
   MOBILE HEADER
========================= */

.mobile-header {

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
        >

            <i class="bi bi-list"></i>

        </button>

    </div>



    <!-- PAGE HEADER -->

    <div class="page-header">

        <h2>
            Edit Student
        </h2>

        <p>
            Update the student's account and student information.
        </p>

    </div>



    <!-- FORM CARD -->

    <div class="form-card">


        <div class="form-card-header">

            <h4>

                <i class="bi bi-pencil-square"></i>

                Edit Student

            </h4>

            <p>
                Update the student's information below.
            </p>

        </div>



        <div class="form-card-body">


            <?= $message ?>



            <form method="POST">


                <!-- FULL NAME -->

                <div class="mb-3">

                    <label class="form-label">
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="fullname"
                        class="form-control"
                        placeholder="Enter full name"
                        value="<?= htmlspecialchars(
                            $row['fullname'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                        required
                    >

                </div>



                <!-- STUDENT NUMBER -->

                <div class="mb-3">

                    <label class="form-label">
                        Student Number
                    </label>

                    <input
                        type="text"
                        name="student_number"
                        class="form-control"
                        placeholder="Enter student number"
                        value="<?= htmlspecialchars(
                            $row['student_number'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                        required
                    >

                </div>



                <!-- YEAR -->

                <div class="mb-3">

                    <label class="form-label">
                        Year
                    </label>

                    <select
                        name="year_level"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Select Year
                        </option>

                        <option
                            value="1st Year"
                            <?= ($row['year_level'] ?? '') == '1st Year' ? 'selected' : ''; ?>
                        >
                            1st Year
                        </option>

                        <option
                            value="2nd Year"
                            <?= ($row['year_level'] ?? '') == '2nd Year' ? 'selected' : ''; ?>
                        >
                            2nd Year
                        </option>

                        <option
                            value="3rd Year"
                            <?= ($row['year_level'] ?? '') == '3rd Year' ? 'selected' : ''; ?>
                        >
                            3rd Year
                        </option>

                        <option
                            value="4th Year"
                            <?= ($row['year_level'] ?? '') == '4th Year' ? 'selected' : ''; ?>
                        >
                            4th Year
                        </option>

                    </select>

                </div>



                <!-- COURSE -->

                <div class="mb-3">

                    <label class="form-label">
                        Course
                    </label>

                    <input
                        type="text"
                        name="course"
                        class="form-control"
                        placeholder="Enter course"
                        value="<?= htmlspecialchars(
                            $row['course'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                        required
                    >

                </div>



                <!-- GENDER -->

                <div class="mb-3">

                    <label class="form-label">
                        Gender
                    </label>

                    <select
                        name="gender"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Select Gender
                        </option>

                        <option
                            value="Male"
                            <?= ($row['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>
                        >
                            Male
                        </option>

                        <option
                            value="Female"
                            <?= ($row['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>
                        >
                            Female
                        </option>

                    </select>

                </div>



                <!-- EMAIL -->

                <div class="mb-4">

                    <label class="form-label">
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter email address"
                        value="<?= htmlspecialchars(
                            $row['email'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                        required
                    >

                </div>



                <!-- BUTTONS -->

                <button
                    type="submit"
                    name="update"
                    class="btn btn-primary"
                >

                    <i class="bi bi-save"></i>

                    Update Student

                </button>


                <a
                    href="students.php"
                    class="btn btn-secondary"
                >

                    <i class="bi bi-arrow-left"></i>

                    Cancel

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
```
