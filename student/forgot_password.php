<?php

session_start();

include("../includes/db.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "../PHPMailer/src/Exception.php";
require "../PHPMailer/src/PHPMailer.php";
require "../PHPMailer/src/SMTP.php";

$error = "";

if (isset($_POST['send_code'])) {

    $student_number = trim($_POST['student_number'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($student_number) || empty($email)) {

        $error = "Please enter your Student Number and Email Address.";

    } else {

        $stmt = $conn->prepare("
            SELECT
                users.id,
                users.fullname,
                users.email,
                students.student_number
            FROM users
            INNER JOIN students
                ON users.id = students.user_id
            WHERE students.student_number = ?
            AND users.email = ?
            AND users.role = 'student'
            LIMIT 1
        ");

        $stmt->bind_param(
            "ss",
            $student_number,
            $email
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $row = $result->fetch_assoc();

            $verification_code = random_int(100000, 999999);

            $_SESSION['student_reset_user_id'] = $row['id'];
            $_SESSION['student_reset_email'] = $row['email'];
            $_SESSION['student_reset_code'] = $verification_code;
            $_SESSION['student_reset_code_time'] = time();

            $mail = new PHPMailer(true);

            try {

                $mail->isSMTP();

                $mail->Host = 'smtp.gmail.com';

                $mail->SMTPAuth = true;

                /*
                |--------------------------------------------------------------------------
                | YOUR GMAIL
                |--------------------------------------------------------------------------
                */

                $mail->Username = 'markneilarayata27@gmail.com';

                /*
                |--------------------------------------------------------------------------
                | YOUR GMAIL APP PASSWORD
                |--------------------------------------------------------------------------
                */

                $mail->Password = 'jggbxdrmyozrihla';

                $mail->SMTPSecure =
                    PHPMailer::ENCRYPTION_STARTTLS;

                $mail->Port = 587;

                $mail->setFrom(
                    'markneilarayata27@gmail.com',
                    'PRMSU Guidance Counseling System'
                );

                $mail->addAddress(
                    $row['email'],
                    $row['fullname']
                );

                $mail->isHTML(true);

                $mail->Subject =
                    'PRMSU Student Password Reset Verification Code';

                $mail->Body = "

                <div style='
                    font-family:Arial,sans-serif;
                    max-width:600px;
                    margin:auto;
                    padding:30px;
                    border:1px solid #ddd;
                    border-radius:10px;
                '>

                    <h2 style='color:#002147;'>
                        PRMSU Mental Health and
                        Guidance Counseling System
                    </h2>

                    <p>
                        Hello
                        <strong>" .
                        htmlspecialchars($row['fullname']) .
                        "</strong>,
                    </p>

                    <p>
                        You requested to reset your
                        student account password.
                    </p>

                    <p>
                        Your verification code is:
                    </p>

                    <div style='
                        font-size:32px;
                        font-weight:bold;
                        letter-spacing:8px;
                        color:#002147;
                        padding:20px;
                        background:#f4f6f9;
                        text-align:center;
                        border-radius:10px;
                    '>

                        {$verification_code}

                    </div>

                    <p style='margin-top:20px;'>

                        This verification code will
                        expire in
                        <strong>10 minutes</strong>.

                    </p>

                    <p>

                        If you did not request a
                        password reset, please ignore
                        this email.

                    </p>

                    <hr>

                    <p style='
                        font-size:12px;
                        color:#777;
                    '>

                        PRMSU Mental Health and
                        Guidance Counseling System

                    </p>

                </div>

                ";

                $mail->AltBody =
                    "Your PRMSU verification code is: "
                    . $verification_code
                    . ". This code expires in 10 minutes.";

                $mail->send();

                header("Location: verify_code.php");

                exit();

            } catch (Exception $e) {

                $error =
                    "Mailer Error: " .
                    $mail->ErrorInfo;

            }

        } else {

            $error =
                "Student Number and Email Address do not match.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Student Forgot Password</title>

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

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Poppins,sans-serif;
}

body{

    background:
    linear-gradient(
        rgba(0,33,71,.75),
        rgba(0,33,71,.75)
    ),
    url("../assets/images/dashboard-bg.jpg");

    background-size:cover;

    background-position:center;

    background-repeat:no-repeat;

    min-height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;
}

.forgot-container{

    width:430px;

    padding:35px;

    border-radius:20px;

    background:rgba(255,255,255,.12);

    backdrop-filter:blur(15px);

    -webkit-backdrop-filter:blur(15px);

    border:1px solid rgba(255,255,255,.25);

    box-shadow:
        0 15px 40px rgba(0,0,0,.35);
}

.logo{

    width:90px;
}

h3,
p,
label{

    color:white;
}

.form-control{

    height:48px;

    background:rgba(255,255,255,.15);

    border:1px solid rgba(255,255,255,.30);

    color:white;
}

.form-control::placeholder{

    color:#ddd;
}

.form-control:focus{

    background:rgba(255,255,255,.20);

    border-color:white;

    color:white;

    box-shadow:none;
}

.btn-submit{

    height:48px;

    background:#ffc107;

    color:#002147;

    font-weight:bold;

    border:none;
}

.btn-submit:hover{

    background:#ffca2c;

    color:#002147;
}

.btn-secondary{

    background:rgba(255,255,255,.20);

    border:1px solid rgba(255,255,255,.30);

    color:white;
}

.btn-secondary:hover{

    background:rgba(255,255,255,.35);

    color:white;
}

.alert{

    border:none;
}

</style>

</head>

<body>

<div class="forgot-container">

    <div class="text-center">

        <img
            src="../assets/images/prmsu-logo.png"
            class="logo mb-3"
        >

        <h3>
            Forgot Password
        </h3>

        <p>
            PRMSU Mental Health and
            Guidance Counseling System
        </p>

        <p>
            <strong>
                Student Account
            </strong>
        </p>

    </div>

    <?php if (!empty($error)) { ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>

    <form method="POST">

        <div class="mb-3">

            <label class="form-label">
                Student Number
            </label>

            <input
                type="text"
                name="student_number"
                class="form-control"
                placeholder="Enter Student Number"
                required
            >

        </div>

        <div class="mb-3">

            <label class="form-label">
                Email Address
            </label>

            <input
                type="email"
                name="email"
                class="form-control"
                placeholder="Enter Email Address"
                required
            >

        </div>

        <button
            type="submit"
            name="send_code"
            class="btn btn-submit w-100"
        >

            <i class="bi bi-envelope"></i>

            Send Verification Code

        </button>

    </form>

    <br>

    <a
        href="login.php"
        class="btn btn-secondary w-100"
    >

        <i class="bi bi-arrow-left"></i>

        Back to Student Login

    </a>

</div>

</body>

</html>